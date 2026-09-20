<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentWorkflow;
use App\Models\Notification;
use App\Models\DocumentLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Exception;

class DocumentSignatureController extends Controller
{
    const A4_WIDTH_MM = 210;
    const A4_HEIGHT_MM = 297;
    const PIXEL_TO_MM_FACTOR = (1 / 1.5) * 0.352778;
    const MM_TO_TWIPS_FACTOR = 56.6929;
    const MM_TO_PT_FACTOR = 2.83465;

    public function index()
    {
        $user = Auth::user();

        $overdueQuery = Document::where('status', 'pending')
            ->where(function($q) {
                $q->where('deadline', '<', now())
                    ->orWhere('created_at', '<', now()->subDays(7));
            });

        if (!$user->isAdmin()) {
            $overdueQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id);
            });
        }

        $overdueCount = $overdueQuery->count();

        $query = DocumentSignature::with(['document', 'user']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $signatures = $query->latest()->paginate(12);

        return view('signatures.index', compact('signatures', 'overdueCount'));
    }

    public function create(Request $request)
    {
        $documentId = $request->query('document_id');
        $user = Auth::user();

        $documentsQuery = Document::latest();

        if (!$user->isAdmin()) {
            $documentsQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id);
            });
        }

        $documents = $documentsQuery->get();
        $document = $documentId ? $documents->find($documentId) : null;

        return view('signatures.create', compact('document', 'documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_id'    => 'required|exists:documents,id',
            'position_mode'  => 'required|in:page,cell',
            'qr_x_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_y_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_cell'        => 'required_if:position_mode,cell|nullable|string|max:12',
            // офсет внутри ячейки Excel — теперь ПРОЦЕНТЫ (0-100), а не сырые пиксели
            'qr_offset_x'    => 'nullable|numeric|min:0|max:100',
            'qr_offset_y'    => 'nullable|numeric|min:0|max:100',
        ], [
            'document_id.required'   => 'Идентификатор документа обязателен.',
            'document_id.exists'     => 'Выбранный документ не найден в базе данных.',
            'position_mode.required' => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_x_percent.required_if' => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_y_percent.required_if' => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_cell.required_if'      => 'Укажите ячейку для QR-кода, кликнув по таблице.',
        ]);

        $position = [
            'mode'      => $request->input('position_mode'),
            'x_percent' => $request->input('qr_x_percent'),
            'y_percent' => $request->input('qr_y_percent'),
            'cell'      => $request->input('qr_cell'),
            'offset_x'  => $request->input('qr_offset_x'),
            'offset_y'  => $request->input('qr_offset_y'),
        ];

        $document = Document::findOrFail($request->document_id);
        $signer = Auth::user();

        $this->checkDocumentSigningAccess($document, $signer);

        $creator = $document->createdBy ?? $document->user;

        $currentWorkflow = DocumentWorkflow::where('document_id', $document->id)
            ->where('status', 'pending')
            ->orderBy('step_order', 'asc')
            ->first();

        if ($currentWorkflow && (int)$signer->id !== (int)$currentWorkflow->user_id) {
            return back()->with('error', 'Сейчас очередь другого пользователя!');
        }

        $senderName = $creator->name ?? 'System';
        $senderEmail = $creator->email ?? '-';
        $signerName = $signer->name ?? 'Unknown';
        $signerEmail = $signer->email ?? '-';

        $sentDate = $document->created_at ? $document->created_at->format('d.m.Y H:i') : now()->format('d.m.Y H:i');
        $signedDate = now()->format('d.m.Y H:i:s');

        $qrData = "DocSign | DOC: {$document->title} | SENDER: {$senderName} ({$senderEmail}) | SIGNED BY: {$signerName} ({$signerEmail}) | SENT AT: {$sentDate} | SIGNED AT: {$signedDate}";
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));

        $redirectType = 'other';
        $qrSize = 100;

        try {
            DB::transaction(function () use ($document, $signer, $currentWorkflow, $qrData, $extension, $qrSize, &$redirectType, $signerName, $signedDate, $position) {

                if ($extension === 'docx' || $extension === 'doc') {
                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['docx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);
                    $this->logAction($document->id, 'подписание', "DOCX документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'docx';
                    return;
                }

                if ($extension === 'pdf') {
                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['pdf_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);
                    $this->logAction($document->id, 'подписание', "PDF документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'pdf';
                    return;
                }

                if ($extension === 'xlsx' || $extension === 'xls') {
                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['xlsx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);
                    $this->logAction($document->id, 'подписание', "Excel документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'xlsx';
                    return;
                }

                if ($extension === 'rtf') {
                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['docx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);
                    $this->logAction($document->id, 'подписание', "RTF конвертирован в DOCX и подписан пользователем {$signer->name}. QR-код внедрён.");
                    $redirectType = 'docx';
                    return;
                }

                // Для других (неизвестных) типов — позиция не применяется, отдельный QR-штамп
                $permanentQrName = 'signatures/qr_' . time() . '_' . $document->id . '.png';
                $stampPath = $this->generateStamp($qrData, $signerName, $signedDate, $qrSize);

                $publicSigsPath = storage_path('app/public/signatures');
                if (!File::exists($publicSigsPath)) {
                    File::makeDirectory($publicSigsPath, 0755, true, true);
                }

                File::move($stampPath, storage_path('app/public/' . $permanentQrName));

                $this->saveSignature($document, $signer, $permanentQrName);
                $this->processWorkflow($document, $currentWorkflow, $signer);
                $document->update(['status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);

                $this->logAction($document->id, 'подписание', "Документ формата {$extension} подписан пользователем {$signer->name}. Создан QR-штамп.");
            });

            if ($redirectType === 'docx') {
                return redirect()->route('signatures.index')->with('success', '✅ Документ Word успешно подписан!');
            } elseif ($redirectType === 'pdf') {
                return redirect()->route('signatures.index')->with('success', '✅ Документ PDF успешно подписан!');
            } elseif ($redirectType === 'xlsx') {
                return redirect()->route('signatures.index')->with('success', '✅ Документ Excel успешно подписан!');
            }

            return redirect()->route('signatures.index')->with('success', '✅ Документ успешно подписан!');

        } catch (Exception $e) {
            \Log::error("Ошибка подписи: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->logAction($document->id, 'ошибка', "Ошибка при подписании документа пользователем {$signer->name}: " . $e->getMessage());
            return back()->with('error', '❌ Ошибка: ' . $e->getMessage());
        }
    }

    private function saveSignature($document, $signer, $qrPath)
    {
        $signature = DocumentSignature::where('document_id', $document->id)
            ->where('user_id', $signer->id)
            ->first();

        if ($signature) {
            $signature->update([
                'signature' => $qrPath,
                'signed_at' => now(),
            ]);
            \Log::info("✅ Подпись ОБНОВЛЕНА: doc_id={$document->id}, user_id={$signer->id}");
        } else {
            DocumentSignature::create([
                'document_id' => $document->id,
                'user_id'     => $signer->id,
                'signature'   => $qrPath,
                'signed_at'   => now(),
            ]);
            \Log::info("✅ Подпись СОЗДАНА: doc_id={$document->id}, user_id={$signer->id}");
        }
    }

    public function show(DocumentSignature $signature) {
        $signature->load('document');
        $this->checkSignatureAccess($signature);
        return view('signatures.show', compact('signature'));
    }

    public function edit(DocumentSignature $signature) {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) {
            abort(403);
        }
        return view('signatures.edit', compact('signature'));
    }

    public function update(Request $request, DocumentSignature $signature)
    {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'position_mode'  => 'required|in:page,cell',
            'qr_x_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_y_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_cell'        => 'required_if:position_mode,cell|nullable|string|max:12',
            'qr_offset_x'    => 'nullable|numeric|min:0|max:100',
            'qr_offset_y'    => 'nullable|numeric|min:0|max:100',
        ], [
            'position_mode.required'   => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_x_percent.required_if' => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_y_percent.required_if' => 'Укажите место для QR-кода, кликнув по документу.',
            'qr_cell.required_if'      => 'Укажите ячейку для QR-кода, кликнув по таблице.',
        ]);

        $position = [
            'mode'      => $request->input('position_mode'),
            'x_percent' => $request->input('qr_x_percent'),
            'y_percent' => $request->input('qr_y_percent'),
            'cell'      => $request->input('qr_cell'),
            'offset_x'  => $request->input('qr_offset_x'),
            'offset_y'  => $request->input('qr_offset_y'),
        ];

        $document = $signature->document;
        $signer = Auth::user();
        $creator = $document->createdBy ?? $document->user;

        $senderName = $creator->name ?? 'System';
        $senderEmail = $creator->email ?? '-';
        $signerName = $signer->name ?? 'Unknown';
        $signerEmail = $signer->email ?? '-';

        $sentDate = $document->created_at ? $document->created_at->format('d.m.Y H:i') : now()->format('d.m.Y H:i');
        $signedDate = now()->format('d.m.Y H:i:s');

        $qrData = "DocSign (UPDATED) | DOC: {$document->title} | SENDER: {$senderName} ({$senderEmail}) | SIGNED BY: {$signerName} ({$signerEmail}) | SENT AT: {$sentDate} | SIGNED AT: {$signedDate}";
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));

        $redirectType = 'other';
        $qrSize = 100;

        try {
            DB::transaction(function () use ($qrData, $signature, $document, $extension, $qrSize, &$redirectType, $signerName, $signedDate, $position) {

                if ($extension === 'docx' || $extension === 'doc') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['docx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'docx';
                    return;
                }

                if ($extension === 'pdf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['pdf_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'pdf';
                    return;
                }

                if ($extension === 'xlsx' || $extension === 'xls') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['xlsx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'xlsx';
                    return;
                }

                if ($extension === 'rtf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['docx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'docx';
                    return;
                }

                if ($signature->signature) Storage::disk('public')->delete($signature->signature);

                $permanentQrName = 'signatures/qr_' . time() . '_' . $document->id . '.png';
                $stampPath = $this->generateStamp($qrData, $signerName, $signedDate, $qrSize);
                File::move($stampPath, storage_path('app/public/' . $permanentQrName));
                $signature->update(['signature' => $permanentQrName, 'signed_at' => now()]);
            });

            $this->logAction($document->id, 'обновление подписи', "Подпись обновлена пользователем {$signer->name}. Формат: " . strtoupper($extension));

            if ($redirectType === 'docx') {
                return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл Word переподписан!');
            } elseif ($redirectType === 'pdf') {
                return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл PDF обновлен!');
            } elseif ($redirectType === 'xlsx') {
                return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл Excel обновлен!');
            }

            return redirect()->route('signatures.show', $signature->id)->with('success', 'Данные подписи обновлены!');

        } catch (Exception $e) {
            \Log::error("Ошибка обновления: " . $e->getMessage());
            $this->logAction($document->id, 'ошибка', "Ошибка при обновлении подписи пользователем {$signer->name}: " . $e->getMessage());
            return back()->with('error', 'Ошибка обновления: ' . $e->getMessage());
        }
    }

    public function destroy(DocumentSignature $signature) {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) {
            abort(403);
        }

        $signature->load('document');
        $document = $signature->document;
        $signer = Auth::user();

        $docTitle = $document ? $document->title : 'Удаленный документ';
        $this->logAction($document?->id, 'удаление подписи', "Подпись пользователя {$signer->name} удалена из документа «{$docTitle}»");

        if ($document) {
            $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
            if (in_array($extension, ['pdf', 'docx', 'doc', 'xlsx', 'xls'])) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }
        }

        if ($signature->signature) {
            if (Storage::disk('public')->exists($signature->signature)) {
                Storage::disk('public')->delete($signature->signature);
            }
        }

        $signature->delete();

        return back()->with('success', 'Запись удалена');
    }

    /**
     * ✅ Рассчитывает координаты QR на "странице" (мм) по проценту клика.
     * Работает с реальными размерами страницы, которые передаются вызывающим кодом
     * (для PDF — из самого файла, для DOCX/RTF — из секции документа).
     */
    private function resolvePagePosition(array $position, float $pageWidthMm, float $pageHeightMm, float $qrSize)
    {
        $qrWidthMm  = $qrSize * self::PIXEL_TO_MM_FACTOR;
        $qrHeightMm = ($qrSize + 70) * self::PIXEL_TO_MM_FACTOR;

        $xPercent = isset($position['x_percent']) ? (float)$position['x_percent'] : 90;
        $yPercent = isset($position['y_percent']) ? (float)$position['y_percent'] : 90;
        $xPercent = max(0, min(100, $xPercent));
        $yPercent = max(0, min(100, $yPercent));

        // клик = центр QR-штампа
        $centerX = ($xPercent / 100) * $pageWidthMm;
        $centerY = ($yPercent / 100) * $pageHeightMm;

        $x = $centerX - ($qrWidthMm / 2);
        $y = $centerY - ($qrHeightMm / 2);

        // не даём вылезти за пределы страницы
        $x = max(0, min($pageWidthMm - $qrWidthMm, $x));
        $y = max(0, min($pageHeightMm - $qrHeightMm, $y));

        return [
            'x_mm'      => $x,
            'y_mm'      => $y,
            'width_mm'  => $qrWidthMm,
            'height_mm' => $qrHeightMm,
        ];
    }

    /**
     * ✅ ИСПРАВЛЕНО: реальный размер страницы Word-документа (в мм).
     * Читаем напрямую из XML файла (word/document.xml, тег w:pgSz) —
     * это ТОЧНО ТО ЖЕ значение, которое видит docx-preview.js в браузере,
     * поскольку оба читают один и тот же исходный XML документа.
     * API PhpWord (getPageSizeW/H) в некоторых версиях может возвращать
     * дефолтные значения вместо реальных, из-за чего клик по превью
     * "уезжал" не в то место — прямое чтение XML устраняет это расхождение.
     */
    private function getDocxPageSizeMm($filePath, $section = null)
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                if ($xml && preg_match('/<w:pgSz\b([^>]*)\/?>/', $xml, $tagMatch)) {
                    $attrs = $tagMatch[1];
                    preg_match('/w:w="(\d+)"/', $attrs, $wMatch);
                    preg_match('/w:h="(\d+)"/', $attrs, $hMatch);

                    $wTwips = isset($wMatch[1]) ? (int)$wMatch[1] : null;
                    $hTwips = isset($hMatch[1]) ? (int)$hMatch[1] : null;

                    if ($wTwips && $hTwips) {
                        return [
                            'width_mm'  => $wTwips / self::MM_TO_TWIPS_FACTOR,
                            'height_mm' => $hTwips / self::MM_TO_TWIPS_FACTOR,
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            \Log::warning("Не удалось прочитать размер страницы DOCX из XML: " . $e->getMessage());
        }

        // Фолбэк на API PhpWord, если прямое чтение XML не удалось
        if ($section) {
            try {
                $style = $section->getStyle();
                $wTwips = $style ? $style->getPageSizeW() : null;
                $hTwips = $style ? $style->getPageSizeH() : null;

                if ($wTwips && $hTwips) {
                    return [
                        'width_mm'  => $wTwips / self::MM_TO_TWIPS_FACTOR,
                        'height_mm' => $hTwips / self::MM_TO_TWIPS_FACTOR,
                    ];
                }
            } catch (Exception $e) {
            }
        }

        return ['width_mm' => self::A4_WIDTH_MM, 'height_mm' => self::A4_HEIGHT_MM];
    }

    /**
     * ✅ НОВОЕ: реальная ширина столбца Excel в пикселях (для правильного офсета QR).
     * Excel хранит ширину в "символьных" единицах — конвертируем по стандартной формуле.
     */
    private function getColumnWidthPx($sheet, $columnLetter)
    {
        try {
            $colDim = $sheet->getColumnDimension($columnLetter);
            $width = $colDim->getWidth();
        } catch (Exception $e) {
            $width = null;
        }

        if ($width === null || $width < 0) {
            $width = 8.43; // стандартная ширина столбца Excel по умолчанию
        }

        return (int) round(($width * 7) + 5);
    }

    /**
     * ✅ НОВОЕ: реальная высота строки Excel в пикселях.
     */
    private function getRowHeightPx($sheet, $rowNumber)
    {
        try {
            $rowDim = $sheet->getRowDimension($rowNumber);
            $height = $rowDim->getRowHeight();
        } catch (Exception $e) {
            $height = null;
        }

        if ($height === null || $height < 0) {
            $height = 15; // стандартная высота строки Excel в пунктах
        }

        return (int) round($height * 96 / 72);
    }

    private function generateStamp($qrPayload, $signerName, $signedDate, $qrSizePx = 100)
    {
        $tempDir = storage_path('app/temp_sigs');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $qrApiSize = 600;
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrApiSize}x{$qrApiSize}&margin=2&data=" . urlencode($qrPayload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $qrApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$content || $httpCode !== 200) {
            throw new Exception("Не удалось получить QR-код (HTTP {$httpCode}): {$curlError}");
        }

        $qrPngPath = $tempDir . '/' . uniqid('qr_', true) . '.png';
        File::put($qrPngPath, $content);

        if (!extension_loaded('gd')) {
            throw new Exception("Расширение GD не установлено.");
        }

        $qrImage = imagecreatefrompng($qrPngPath);
        if (!$qrImage) {
            throw new Exception("Не удалось загрузить QR-изображение.");
        }
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        $stampWidth = $qrSizePx;
        $stampHeight = $qrSizePx + 70;

        $scale = 2;
        $stampWidthHiRes = $stampWidth * $scale;
        $stampHeightHiRes = $stampHeight * $scale;

        $stamp = imagecreatetruecolor($stampWidthHiRes, $stampHeightHiRes);
        imageantialias($stamp, true);
        // ВАЖНО: раньше здесь сохранялся альфа-канал (imagesavealpha=true).
        // PNG с прозрачностью в связке с абсолютным позиционированием "за текстом"
        // в Word иногда даёт битую картинку (плейсхолдер "заблокировано").
        // Фон у штампа и так белый — прозрачность не нужна, делаем PNG непрозрачным.
        imagealphablending($stamp, true);
        imagesavealpha($stamp, false);

        $white = imagecolorallocate($stamp, 255, 255, 255);
        $black = imagecolorallocate($stamp, 0, 0, 0);
        $gray = imagecolorallocate($stamp, 200, 200, 200);

        imagefill($stamp, 0, 0, $white);

        imagecopyresampled(
            $stamp, $qrImage,
            0, 0, 0, 0,
            $qrSizePx * $scale, $qrSizePx * $scale,
            $qrWidth, $qrHeight
        );

        imageline($stamp, 5 * $scale, ($qrSizePx + 5) * $scale, ($stampWidthHiRes - 5 * $scale), ($qrSizePx + 5) * $scale, $gray);

        $displayName = mb_strlen($signerName) > 20 ? mb_substr($signerName, 0, 20) . '...' : $signerName;
        $nameFont = 5;
        $nameCharWidth = imagefontwidth($nameFont);
        $nameX = max(5 * $scale, ($stampWidthHiRes - (strlen($displayName) * $nameCharWidth)) / 2);
        imagestring($stamp, $nameFont, $nameX, ($qrSizePx + 15) * $scale, $displayName, $black);

        $dateCharWidth = imagefontwidth(3);
        $dateX = max(5 * $scale, ($stampWidthHiRes - (strlen($signedDate) * $dateCharWidth)) / 2);
        imagestring($stamp, 3, $dateX, ($qrSizePx + 40) * $scale, $signedDate, $black);

        imagerectangle($stamp, 0, 0, $stampWidthHiRes - 1, $stampHeightHiRes - 1, $black);

        $finalStamp = imagecreatetruecolor($stampWidth, $stampHeight);
        imageantialias($finalStamp, true);
        imagealphablending($finalStamp, true);
        imagesavealpha($finalStamp, false);

        imagecopyresampled(
            $finalStamp, $stamp,
            0, 0, 0, 0,
            $stampWidth, $stampHeight,
            $stampWidthHiRes, $stampHeightHiRes
        );

        $stampPath = $tempDir . '/' . uniqid('stamp_', true) . '.png';
        imagepng($finalStamp, $stampPath, 9);

        imagedestroy($qrImage);
        imagedestroy($stamp);
        imagedestroy($finalStamp);

        if (File::exists($qrPngPath)) {
            File::delete($qrPngPath);
        }

        return $stampPath;
    }

    /**
     * ✅ PDF: QR на ПОСЛЕДНЕЙ странице, позиция — по клику пользователя.
     * Здесь всегда используется реальный размер страницы из самого PDF ($size['width']/['height']),
     * поэтому позиционирование точное.
     */
    private function processPdfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = [])
    {
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);

        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) {
            throw new Exception("Файл PDF не найден.");
        }

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($originalPath);
        $targetPage = $pageCount;

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            if ($pageNo == $targetPage) {
                $pos = $this->resolvePagePosition($position, $size['width'], $size['height'], $qrSize);
                $pdf->Image($stampPath, $pos['x_mm'], $pos['y_mm'], $pos['width_mm'], $pos['height_mm'], 'PNG');
            }
        }

        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.pdf';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';

        $newFilePath = storage_path('app/public/' . $newFileName);
        $dir = dirname($newFilePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $pdf->Output($newFilePath, 'F');

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) {
            File::makeDirectory($publicSigsPath, 0755, true, true);
        }
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));

        return ['pdf_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    /**
     * ✅ DOCX: QR на ПОСЛЕДНЕЙ странице (секции), позиция — по клику пользователя.
     * ИСПРАВЛЕНО: размер страницы теперь берётся из реального документа
     * (getDocxPageSizeMm), а не считается всегда A4 — это устраняет смещение
     * между тем, где кликнул пользователь в превью, и тем, куда встаёт QR.
     */
    private function processDocxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = [])
    {
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) {
            throw new Exception("Файл Word не найден: {$originalPath}");
        }

        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);
        if (!File::exists($stampPath)) {
            throw new Exception("Штамп не создан.");
        }

        $phpWord = IOFactory::load($originalPath);
        $sections = $phpWord->getSections();

        $sectionIndex = count($sections) > 0 ? count($sections) - 1 : 0;
        $section = $sections[$sectionIndex] ?? $phpWord->addSection();

        $pageSize = $this->getDocxPageSizeMm($originalPath, $section);
        $pos = $this->resolvePagePosition($position, $pageSize['width_mm'], $pageSize['height_mm'], $qrSize);

        $qr_x_twips = round($pos['x_mm'] * self::MM_TO_TWIPS_FACTOR);
        $qr_y_twips = round($pos['y_mm'] * self::MM_TO_TWIPS_FACTOR);
        $qr_width_pt = round($pos['width_mm'] * self::MM_TO_PT_FACTOR);
        $qr_height_pt = round($pos['height_mm'] * self::MM_TO_PT_FACTOR);

        $section->addImage($stampPath, [
            'width'            => $qr_width_pt,
            'height'           => $qr_height_pt,
            'positioning'      => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_LEFT,
            'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
            'posVerticalRel'   => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'marginLeft'       => $qr_x_twips,
            'marginTop'        => $qr_y_twips,
            'wrappingStyle'    => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_SQUARE,
        ]);

        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.docx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';

        $newFilePath = storage_path('app/public/' . $newFileName);
        $dir = dirname($newFilePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($newFilePath);

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) {
            File::makeDirectory($publicSigsPath, 0755, true, true);
        }

        File::move($stampPath, storage_path('app/public/' . $permanentQrName));

        return ['docx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    /**
     * ✅ XLSX: QR на ПОСЛЕДНЕМ листе, позиция — по клику на ячейку.
     * ИСПРАВЛЕНО: offset_x/offset_y теперь приходят из формы как ПРОЦЕНТЫ (0-100)
     * положения клика внутри ячейки, а не сырые пиксели браузерной таблицы.
     * Здесь они пересчитываются в реальные пиксели по фактической ширине
     * столбца и высоте строки конкретно этой ячейки в самом файле —
     * поэтому QR больше не улетает за пределы видимой области.
     */
    private function processXlsxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = [])
    {
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);

        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) {
            throw new Exception("Файл Excel не найден.");
        }

        $spreadsheet = SpreadsheetIOFactory::load($originalPath);
        $sheetCount = $spreadsheet->getSheetCount();

        $sheetIndex = $sheetCount - 1;
        $sheet = $spreadsheet->getSheet($sheetIndex);

        $stampHeight = $qrSize + 70;

        $drawing = new Drawing();
        $drawing->setName('QR Code Stamp');
        $drawing->setDescription('Document Signature');
        $drawing->setPath($stampPath);
        $drawing->setHeight($stampHeight);
        $drawing->setWidth($qrSize);

        if (($position['mode'] ?? null) === 'cell' && !empty($position['cell'])) {
            // Санитизация ссылки на ячейку (только буквы+цифры, формат вида F20)
            $cell = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $position['cell']));
            if (!preg_match('/^[A-Z]{1,3}[0-9]{1,7}$/', $cell)) {
                $cell = 'A1';
            }

            preg_match('/^([A-Z]+)([0-9]+)$/', $cell, $m);
            $colLetter = $m[1] ?? 'A';
            $rowNum = isset($m[2]) ? (int)$m[2] : 1;

            $colWidthPx = $this->getColumnWidthPx($sheet, $colLetter);
            $rowHeightPx = $this->getRowHeightPx($sheet, $rowNum);

            // offset_x / offset_y приходят как проценты (0-100) положения клика внутри ячейки
            $offsetXPercent = isset($position['offset_x']) ? max(0, min(100, (float)$position['offset_x'])) : 10;
            $offsetYPercent = isset($position['offset_y']) ? max(0, min(100, (float)$position['offset_y'])) : 10;

            $offsetX = (int) round(($offsetXPercent / 100) * $colWidthPx);
            $offsetY = (int) round(($offsetYPercent / 100) * $rowHeightPx);

            $drawing->setCoordinates($cell);
            $drawing->setOffsetX($offsetX);
            $drawing->setOffsetY($offsetY);
        } else {
            // fallback — если позиция не пришла
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
        }

        $drawing->setWorksheet($sheet);

        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.xlsx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';

        $newFilePath = storage_path('app/public/' . $newFileName);
        $dir = dirname($newFilePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($newFilePath);

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) {
            File::makeDirectory($publicSigsPath, 0755, true, true);
        }
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));

        return ['xlsx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    /**
     * ✅ RTF: конвертируем в DOCX и ставим QR (позиция — реальный размер страницы
     * итогового DOCX, как и в processDocxSigning).
     */
    private function processRtfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = [])
    {
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) {
            throw new Exception("Файл RTF не найден.");
        }

        $tempDir = storage_path('app/temp_sigs');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $docxTempPath = $tempDir . '/' . uniqid('rtf_', true) . '.docx';
        $converted = false;

        try {
            $libreOfficePath = $this->findLibreOffice();
            if ($libreOfficePath) {
                $cmd = sprintf(
                    '%s --headless --convert-to docx --outdir %s %s 2>&1',
                    escapeshellarg($libreOfficePath),
                    escapeshellarg($tempDir),
                    escapeshellarg($originalPath)
                );
                shell_exec($cmd);

                $expectedPath = $tempDir . '/' . pathinfo($originalPath, PATHINFO_FILENAME) . '.docx';
                if (File::exists($expectedPath)) {
                    File::move($expectedPath, $docxTempPath);
                    $converted = true;
                }
            }
        } catch (Exception $e) {
            \Log::warning("LibreOffice failed: " . $e->getMessage());
        }

        if (!$converted) {
            try {
                $phpWord = IOFactory::load($originalPath, 'RTF');
                $writer = IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($docxTempPath);
                if (File::exists($docxTempPath) && filesize($docxTempPath) > 0) {
                    $converted = true;
                }
            } catch (Exception $e) {
                \Log::warning("PhpWord RTF failed: " . $e->getMessage());
            }
        }

        if (!$converted) {
            File::copy($originalPath, $docxTempPath);
        }

        if (!File::exists($docxTempPath)) {
            throw new Exception("Не удалось конвертировать RTF.");
        }

        $phpWord = IOFactory::load($docxTempPath);
        $sections = $phpWord->getSections();

        $sectionIndex = count($sections) > 0 ? count($sections) - 1 : 0;
        $section = $sections[$sectionIndex] ?? $phpWord->addSection();

        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);

        $pageSize = $this->getDocxPageSizeMm($docxTempPath, $section);
        $pos = $this->resolvePagePosition($position, $pageSize['width_mm'], $pageSize['height_mm'], $qrSize);

        $qr_x_twips = round($pos['x_mm'] * self::MM_TO_TWIPS_FACTOR);
        $qr_y_twips = round($pos['y_mm'] * self::MM_TO_TWIPS_FACTOR);
        $qr_width_pt = round($pos['width_mm'] * self::MM_TO_PT_FACTOR);
        $qr_height_pt = round($pos['height_mm'] * self::MM_TO_PT_FACTOR);

        $section->addImage($stampPath, [
            'width'            => $qr_width_pt,
            'height'           => $qr_height_pt,
            'positioning'      => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_LEFT,
            'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
            'posVerticalRel'   => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'marginLeft'       => $qr_x_twips,
            'marginTop'        => $qr_y_twips,
            'wrappingStyle'    => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_SQUARE,
        ]);

        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.docx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';

        $newFilePath = storage_path('app/public/' . $newFileName);
        $dir = dirname($newFilePath);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($newFilePath);

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) {
            File::makeDirectory($publicSigsPath, 0755, true, true);
        }

        File::move($stampPath, storage_path('app/public/' . $permanentQrName));

        if (File::exists($docxTempPath)) {
            File::delete($docxTempPath);
        }

        return ['docx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    private function findLibreOffice()
    {
        $paths = [
            // Linux / production
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            '/usr/lib/libreoffice/program/soffice',
            '/opt/libreoffice/program/soffice',
            // ✅ Windows (OSPanel/XAMPP/локальная разработка)
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        if (stripos(PHP_OS, 'WIN') === 0) {
            $which = trim(shell_exec('where soffice 2>NUL') ?: '');
            if ($which) {
                $firstLine = trim(strtok($which, "\r\n"));
                if ($firstLine && File::exists($firstLine)) return $firstLine;
            }
            return null;
        }

        $which = trim(shell_exec('which libreoffice 2>/dev/null') ?: '');
        if ($which && File::exists($which)) return $which;

        $which = trim(shell_exec('which soffice 2>/dev/null') ?: '');
        if ($which && File::exists($which)) return $which;

        return null;
    }

    private function isLastStep($document) {
        $hasWorkflow = DocumentWorkflow::where('document_id', $document->id)->exists();
        if (!$hasWorkflow) return true;
        return !DocumentWorkflow::where('document_id', $document->id)->where('status', 'pending')->exists();
    }

    private function logAction($docId, $action, $desc) {
        try {
            DocumentLog::create([
                'document_id' => $docId,
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $desc
            ]);
        } catch (\Exception $e) {
            \Log::error("Ошибка логирования: " . $e->getMessage());
        }
    }

    private function processWorkflow($document, $currentWorkflow, $signer) {
        if ($currentWorkflow) {
            $currentWorkflow->update(['status' => 'approved']);
            $next = DocumentWorkflow::where('document_id', $document->id)
                ->where('step_order', '>', $currentWorkflow->step_order)
                ->orderBy('step_order')
                ->first();
            if ($next) {
                $next->update(['status' => 'pending']);
            }
        }
    }

    private function checkSignatureAccess($signature)
    {
        $user = Auth::user();
        $userId = (int)$user->id;

        if ($user->isAdmin()) {
            return;
        }

        if (!$signature->relationLoaded('document')) {
            $signature->load('document');
        }

        $document = $signature->document;

        if (!$document) {
            abort(404, 'Документ для этой подписи не найден.');
        }

        $isOwner = (int)$signature->user_id === $userId;
        $isCreator = (int)$document->created_by === $userId;
        $isSender = (int)$document->sender_id === $userId;
        $isDirectReceiver = (int)$document->receiver_id === $userId;

        if ($isOwner || $isCreator || $isSender || $isDirectReceiver) {
            return;
        }

        $isInWorkflow = \App\Models\DocumentWorkflow::where('document_id', $document->id)
            ->where('user_id', $userId)
            ->exists();

        if ($isInWorkflow) {
            return;
        }

        try {
            $isRecipient = \App\Models\DocumentRecipient::where('document_id', $document->id)
                ->where('user_id', $userId)
                ->exists();

            if ($isRecipient) {
                return;
            }
        } catch (\Exception $e) {
        }

        \Log::warning("🚨 Попытка доступа к чужой подписи", [
            'user_id' => $userId,
            'signature_id' => $signature->id,
            'document_id' => $document->id
        ]);

        abort(403, 'У вас нет прав доступа к просмотру этой подписи.');
    }

    private function checkDocumentSigningAccess($document, $user)
    {
        if ($user->isAdmin()) {
            return;
        }

        $hasAccess = (
            $document->receiver_id == $user->id ||
            $document->created_by == $user->id ||
            $document->sender_id == $user->id
        );

        if (!$hasAccess) {
            abort(403, 'У вас нет прав на подписание этого документа');
        }
    }

    public function stream($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $hasAccess = (
                (int)$document->created_by === (int)$user->id ||
                (int)$document->receiver_id === (int)$user->id ||
                (int)$document->sender_id === (int)$user->id
            );

            if (!$hasAccess) {
                $hasAccess = DocumentWorkflow::where('document_id', $document->id)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            if (!$hasAccess) {
                try {
                    $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)
                        ->where('user_id', $user->id)
                        ->exists();
                } catch (\Exception $e) {
                }
            }

            if (!$hasAccess) {
                abort(403, 'У вас нет доступа к этому документу.');
            }
        }

        $path = $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Файл не найден на сервере.');
        }

        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $filename = basename($path);

        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'rtf'  => 'application/rtf',
            'txt'  => 'text/plain',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];

        $mime = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control'       => 'public, max-age=3600',
            'Accept-Ranges'       => 'bytes',
        ]);
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $hasAccess = (
                (int)$document->created_by === (int)$user->id ||
                (int)$document->receiver_id === (int)$user->id ||
                (int)$document->sender_id === (int)$user->id
            );

            if (!$hasAccess) {
                $hasAccess = DocumentWorkflow::where('document_id', $document->id)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            if (!$hasAccess) {
                try {
                    $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)
                        ->where('user_id', $user->id)
                        ->exists();
                } catch (\Exception $e) {}
            }

            if (!$hasAccess) {
                abort(403, 'У вас нет доступа к этому документу.');
            }
        }

        $path = $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Файл не найден на сервере.');
        }

        $fullPath = Storage::disk('public')->path($path);
        $filename = $document->title . '.' . strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return response()->download($fullPath, $filename);
    }

    /**
     * ✅ НОВОЕ: отдаёт документ в PDF специально для превью-виджета подписи.
     * Для PDF — просто отдаёт исходный файл. Для DOCX/DOC/RTF — конвертирует
     * в PDF через LibreOffice (headless) и отдаёт результат. Это позволяет
     * рисовать превью Word ТОЧНО ТЕМ ЖЕ pdf.js-кодом, что и обычный PDF —
     * клик мышкой получает ту же точность позиционирования, что и на PDF.
     * Результат конвертации кэшируется на диске и пересчитывается только
     * если исходный файл изменился (по времени модификации).
     * Роут: GET /documents/{id}/preview-pdf
     */
    public function previewPdf($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $hasAccess = (
                (int)$document->created_by === (int)$user->id ||
                (int)$document->receiver_id === (int)$user->id ||
                (int)$document->sender_id === (int)$user->id
            );

            if (!$hasAccess) {
                $hasAccess = DocumentWorkflow::where('document_id', $document->id)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            if (!$hasAccess) {
                try {
                    $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)
                        ->where('user_id', $user->id)
                        ->exists();
                } catch (\Exception $e) {}
            }

            if (!$hasAccess) {
                abort(403, 'У вас нет доступа к этому документу.');
            }
        }

        $path = $document->file_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Файл не найден на сервере.');
        }

        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // PDF отдаём как есть — конвертация не нужна
        if ($extension === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
                'Cache-Control'       => 'private, max-age=300',
            ]);
        }

        if (!in_array($extension, ['docx', 'doc', 'rtf'])) {
            abort(415, 'Предпросмотр в PDF недоступен для этого формата.');
        }

        $cacheDir = storage_path('app/preview_cache');
        if (!File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true, true);
        }

        $cachePdfPath = $cacheDir . '/doc_' . $document->id . '_' . md5($path) . '.pdf';

        $needsConvert = true;
        if (File::exists($cachePdfPath) && filemtime($cachePdfPath) >= filemtime($fullPath)) {
            $needsConvert = false;
        }

        if ($needsConvert) {
            $libreOfficePath = $this->findLibreOffice();
            if (!$libreOfficePath) {
                // ✅ 501 (а не голый 500) — фронтенд по этому коду понимает,
                // что LibreOffice недоступен, и сам переключается на
                // клиентский рендер docx-preview.js вместо показа ошибки.
                return response()->json([
                    'error'  => 'libreoffice_missing',
                    'message' => 'LibreOffice не найден на сервере — точный PDF-предпросмотр Word недоступен.',
                ], 501);
            }

            $cmd = sprintf(
                '%s --headless --convert-to pdf --outdir %s %s 2>&1',
                escapeshellarg($libreOfficePath),
                escapeshellarg($cacheDir),
                escapeshellarg($fullPath)
            );
            shell_exec($cmd);

            $expectedPdf = $cacheDir . '/' . pathinfo($fullPath, PATHINFO_FILENAME) . '.pdf';
            if (File::exists($expectedPdf)) {
                if (File::exists($cachePdfPath)) {
                    File::delete($cachePdfPath);
                }
                File::move($expectedPdf, $cachePdfPath);
            }
        }

        if (!File::exists($cachePdfPath)) {
            return response()->json([
                'error'   => 'conversion_failed',
                'message' => 'Не удалось сконвертировать документ для предпросмотра.',
            ], 501);
        }

        return response()->file($cachePdfPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
            'Cache-Control'       => 'private, max-age=300',
        ]);
    }

    public function qrImage($id)
    {
        $signature = DocumentSignature::findOrFail($id);
        $signature->load('document');

        $this->checkSignatureAccess($signature);

        if (!$signature->signature || !Storage::disk('public')->exists($signature->signature)) {
            abort(404, 'QR-код не найден.');
        }

        return response()->file(
            Storage::disk('public')->path($signature->signature),
            [
                'Content-Type'  => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}