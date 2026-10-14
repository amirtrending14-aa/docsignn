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
use Illuminate\Support\Str;

class DocumentSignatureController extends Controller
{
    const A4_WIDTH_MM = 210;
    const A4_HEIGHT_MM = 297;
    const PIXEL_TO_MM_FACTOR = (1 / 1.5) * 0.352778;
    const MM_TO_TWIPS_FACTOR = 56.6929;
    const MM_TO_PT_FACTOR = 2.83465;
    const MM_TO_EMU_FACTOR = 36000; // ✅ 1 мм = 36000 EMU (стандарт OOXML для точных координат)

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

        $verificationToken = (string) Str::uuid();
        $baseUrl = app()->environment('production') 
            ? rtrim(config('app.url'), '/') 
            : request()->getSchemeAndHttpHost();
            
        $qrData = "{$baseUrl}/verify/{$verificationToken}";
        
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $redirectType = 'other';
        $qrSize = 100;

        try {
            DB::transaction(function () use ($document, $signer, $currentWorkflow, $qrData, $extension, $qrSize, &$redirectType, $signerName, $signedDate, $position, $verificationToken) {

                if ($extension === 'docx' || $extension === 'doc') {
                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path'], $verificationToken);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update(['file_path' => $result['docx_path'], 'status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);
                    $this->logAction($document->id, 'подписание', "DOCX документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'docx';
                    return;
                }

                if ($extension === 'pdf') {
                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path'], $verificationToken);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update(['file_path' => $result['pdf_path'], 'status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);
                    $this->logAction($document->id, 'подписание', "PDF документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'pdf';
                    return;
                }

                if ($extension === 'xlsx' || $extension === 'xls') {
                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path'], $verificationToken);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update(['file_path' => $result['xlsx_path'], 'status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);
                    $this->logAction($document->id, 'подписание', "Excel документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'xlsx';
                    return;
                }

                if ($extension === 'rtf') {
                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $this->saveSignature($document, $signer, $result['qr_path'], $verificationToken);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update(['file_path' => $result['docx_path'], 'status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);
                    $this->logAction($document->id, 'подписание', "RTF конвертирован в DOCX и подписан пользователем {$signer->name}. QR-код внедрён.");
                    $redirectType = 'docx';
                    return;
                }

                $permanentQrName = 'signatures/qr_' . time() . '_' . $document->id . '.png';
                $stampPath = $this->generateStamp($qrData, $signerName, $signedDate, $qrSize);
                $publicSigsPath = storage_path('app/public/signatures');
                if (!File::exists($publicSigsPath)) File::makeDirectory($publicSigsPath, 0755, true, true);
                File::move($stampPath, storage_path('app/public/' . $permanentQrName));

                $this->saveSignature($document, $signer, $permanentQrName, $verificationToken);
                $this->processWorkflow($document, $currentWorkflow, $signer);
                $document->update(['status' => ($this->isLastStep($document)) ? 'completed' : 'processing']);
                $this->logAction($document->id, 'подписание', "Документ формата {$extension} подписан пользователем {$signer->name}. Создан QR-штамп.");
            });

            if ($redirectType === 'docx') return redirect()->route('signatures.index')->with('success', '✅ Документ Word успешно подписан!');
            if ($redirectType === 'pdf') return redirect()->route('signatures.index')->with('success', '✅ Документ PDF успешно подписан!');
            if ($redirectType === 'xlsx') return redirect()->route('signatures.index')->with('success', '✅ Документ Excel успешно подписан!');
            
            return redirect()->route('signatures.index')->with('success', '✅ Документ успешно подписан!');

        } catch (Exception $e) {
            \Log::error("Ошибка подписи: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->logAction($document->id, 'ошибка', "Ошибка при подписании документа пользователем {$signer->name}: " . $e->getMessage());
            return back()->with('error', '❌ Ошибка: ' . $e->getMessage());
        }
    }

    private function saveSignature($document, $signer, $qrPath, $verificationToken = null)
    {
        $signature = DocumentSignature::where('document_id', $document->id)->where('user_id', $signer->id)->first();
        $data = ['signature' => $qrPath, 'signed_at' => now()];
        
        if ($verificationToken) {
            $data['verification_token'] = $verificationToken;
        }

        if ($signature) {
            $signature->update($data);
            \Log::info("✅ Подпись ОБНОВЛЕНА: doc_id={$document->id}, user_id={$signer->id}");
        } else {
            $data['document_id'] = $document->id;
            $data['user_id'] = $signer->id;
            DocumentSignature::create($data);
            \Log::info("✅ Подпись СОЗДАНА: doc_id={$document->id}, user_id={$signer->id}");
        }
    }

    public function show(DocumentSignature $signature) {
        $signature->load('document');
        $this->checkSignatureAccess($signature);
        return view('signatures.show', compact('signature'));
    }

    public function edit(DocumentSignature $signature) {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) abort(403);
        return view('signatures.edit', compact('signature'));
    }

    public function update(Request $request, DocumentSignature $signature)
    {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) abort(403);

        $request->validate([
            'position_mode'  => 'required|in:page,cell',
            'qr_x_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_y_percent'   => 'required_if:position_mode,page|nullable|numeric|min:0|max:100',
            'qr_cell'        => 'required_if:position_mode,cell|nullable|string|max:12',
            'qr_offset_x'    => 'nullable|numeric|min:0|max:100',
            'qr_offset_y'    => 'nullable|numeric|min:0|max:100',
        ]);

        $position = [
            'mode' => $request->input('position_mode'), 'x_percent' => $request->input('qr_x_percent'),
            'y_percent' => $request->input('qr_y_percent'), 'cell' => $request->input('qr_cell'),
            'offset_x' => $request->input('qr_offset_x'), 'offset_y' => $request->input('qr_offset_y'),
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

        $verificationToken = (string) Str::uuid();
        $baseUrl = app()->environment('production') 
            ? rtrim(config('app.url'), '/') 
            : request()->getSchemeAndHttpHost();
            
        $qrData = "{$baseUrl}/verify/{$verificationToken}";
        
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $redirectType = 'other';
        $qrSize = 100;

        try {
            DB::transaction(function () use ($qrData, $signature, $document, $extension, $qrSize, &$redirectType, $signerName, $signedDate, $position, $verificationToken) {
                if ($extension === 'docx' || $extension === 'doc') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);
                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['docx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now(), 'verification_token' => $verificationToken]);
                    $redirectType = 'docx'; return;
                }
                if ($extension === 'pdf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);
                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['pdf_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now(), 'verification_token' => $verificationToken]);
                    $redirectType = 'pdf'; return;
                }
                if ($extension === 'xlsx' || $extension === 'xls') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);
                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['xlsx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now(), 'verification_token' => $verificationToken]);
                    $redirectType = 'xlsx'; return;
                }
                if ($extension === 'rtf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);
                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate, $position);
                    $document->update(['file_path' => $result['docx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now(), 'verification_token' => $verificationToken]);
                    $redirectType = 'docx'; return;
                }
                
                if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                $permanentQrName = 'signatures/qr_' . time() . '_' . $document->id . '.png';
                $stampPath = $this->generateStamp($qrData, $signerName, $signedDate, $qrSize);
                File::move($stampPath, storage_path('app/public/' . $permanentQrName));
                $signature->update(['signature' => $permanentQrName, 'signed_at' => now(), 'verification_token' => $verificationToken]);
            });

            $this->logAction($document->id, 'обновление подписи', "Подпись обновлена пользователем {$signer->name}. Формат: " . strtoupper($extension));
            if ($redirectType === 'docx') return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл Word переподписан!');
            if ($redirectType === 'pdf') return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл PDF обновлен!');
            if ($redirectType === 'xlsx') return redirect()->route('signatures.show', $signature->id)->with('success', 'Файл Excel обновлен!');
            
            return redirect()->route('signatures.show', $signature->id)->with('success', 'Данные подписи обновлены!');

        } catch (Exception $e) {
            \Log::error("Ошибка обновления: " . $e->getMessage());
            $this->logAction($document->id, 'ошибка', "Ошибка при обновлении подписи пользователем {$signer->name}: " . $e->getMessage());
            return back()->with('error', 'Ошибка обновления: ' . $e->getMessage());
        }
    }

    public function destroy(DocumentSignature $signature) {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) abort(403);
        $signature->load('document');
        $document = $signature->document;
        $signer = Auth::user();
        $docTitle = $document ? $document->title : 'Удаленный документ';
        $this->logAction($document?->id, 'удаление подписи', "Подпись пользователя {$signer->name} удалена из документа «{$docTitle}»");

        if ($document) {
            $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
            if (in_array($extension, ['pdf', 'docx', 'doc', 'xlsx', 'xls'])) {
                if (Storage::disk('public')->exists($document->file_path)) Storage::disk('public')->delete($document->file_path);
            }
        }
        if ($signature->signature && Storage::disk('public')->exists($signature->signature)) {
            Storage::disk('public')->delete($signature->signature);
        }
        $signature->delete();
        return back()->with('success', 'Запись удалена');
    }

    private function resolvePagePosition(array $position, float $pageWidthMm, float $pageHeightMm, float $qrSize) {
        $qrWidthMm  = $qrSize * self::PIXEL_TO_MM_FACTOR;
        $qrHeightMm = ($qrSize + 70) * self::PIXEL_TO_MM_FACTOR;
        $xPercent = max(0, min(100, (float)($position['x_percent'] ?? 90)));
        $yPercent = max(0, min(100, (float)($position['y_percent'] ?? 90)));
        $centerX = ($xPercent / 100) * $pageWidthMm;
        $centerY = ($yPercent / 100) * $pageHeightMm;
        $x = max(0, min($pageWidthMm - $qrWidthMm, $centerX - ($qrWidthMm / 2)));
        $y = max(0, min($pageHeightMm - $qrHeightMm, $centerY - ($qrHeightMm / 2)));
        return ['x_mm' => $x, 'y_mm' => $y, 'width_mm' => $qrWidthMm, 'height_mm' => $qrHeightMm];
    }

    private function getDocxPageSizeMm($filePath, $section = null) {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xml && preg_match('/<w:pgSz\b([^>]*)\/?>/', $xml, $tagMatch)) {
                    preg_match('/w:w="(\d+)"/', $tagMatch[1], $wMatch);
                    preg_match('/w:h="(\d+)"/', $tagMatch[1], $hMatch);
                    if (isset($wMatch[1]) && isset($hMatch[1])) {
                        return ['width_mm' => (int)$wMatch[1] / self::MM_TO_TWIPS_FACTOR, 'height_mm' => (int)$hMatch[1] / self::MM_TO_TWIPS_FACTOR];
                    }
                }
            }
        } catch (Exception $e) { \Log::warning("Не удалось прочитать размер страницы DOCX из XML: " . $e->getMessage()); }
        if ($section) {
            try {
                $style = $section->getStyle();
                $wTwips = $style ? $style->getPageSizeW() : null;
                $hTwips = $style ? $style->getPageSizeH() : null;
                if ($wTwips && $hTwips) return ['width_mm' => $wTwips / self::MM_TO_TWIPS_FACTOR, 'height_mm' => $hTwips / self::MM_TO_TWIPS_FACTOR];
            } catch (Exception $e) {}
        }
        return ['width_mm' => self::A4_WIDTH_MM, 'height_mm' => self::A4_HEIGHT_MM];
    }

    /**
     * ✅ АБСОЛЮТНО НАДЕЖНЫЙ МЕТОД ПОЗИЦИОНИРОВАНИЯ ДЛЯ WORD.
     * 
     * PhpWord по умолчанию ожидает marginLeft/marginTop в пунктах (pt), а не в твипах.
     * Если передать твипы, координаты увеличиваются в 20 раз, и штамп улетает за страницу.
     * Этот метод после сохранения файла находит ПОСЛЕДНИЙ добавленный якорь (<wp:anchor>)
     * и принудительно записывает туда точные координаты в EMU (1 мм = 36000 EMU),
     * удаляя любые конфликтующие теги выравнивания (<wp:align>).
     * Это гарантирует, что штамп встанет РОВНО туда, куда кликнул пользователь.
     */
    private function forceDocxAnchorPosition($docxPath, float $xMm, float $yMm)
    {
        try {
            if (!class_exists(\ZipArchive::class)) return;

            $zip = new \ZipArchive();
            if ($zip->open($docxPath) !== true) return;

            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) { $zip->close(); return; }

            // Находим ПОСЛЕДНИЙ якорь (наш штамп добавляется в конец)
            $lastAnchorPos = strrpos($xml, '<wp:anchor');
            if ($lastAnchorPos === false) { $zip->close(); return; }

            // Находим конец открывающего тега <wp:anchor ...>
            $tagEnd = strpos($xml, '>', $lastAnchorPos);
            if ($tagEnd === false) { $zip->close(); return; }

            // Находим закрывающий тег </wp:anchor>
            $anchorClose = strpos($xml, '</wp:anchor>', $tagEnd);
            if ($anchorClose === false) { $zip->close(); return; }

            $xEmu = (int) round($xMm * self::MM_TO_EMU_FACTOR);
            $yEmu = (int) round($yMm * self::MM_TO_EMU_FACTOR);

            // Формируем идеальные теги позиционирования относительно страницы
            $inject = '<wp:positionH relativeFrom="page"><wp:posOffset>' . $xEmu . '</wp:posOffset></wp:positionH>'
                    . '<wp:positionV relativeFrom="page"><wp:posOffset>' . $yEmu . '</wp:posOffset></wp:positionV>';

            // Берем внутренности якоря и чистим их от старых, конфликтующих тегов позиционирования
            $anchorContent = substr($xml, $tagEnd + 1, $anchorClose - ($tagEnd + 1));
            $anchorContent = preg_replace('/<wp:positionH\b[^>]*>.*?<\/wp:positionH>/s', '', $anchorContent);
            $anchorContent = preg_replace('/<wp:positionV\b[^>]*>.*?<\/wp:positionV>/s', '', $anchorContent);
            $anchorContent = preg_replace('/<wp:align\b[^>]*>.*?<\/wp:align>/s', '', $anchorContent);

            // Собираем XML обратно
            $newXml = substr($xml, 0, $tagEnd + 1) . $inject . $anchorContent . substr($xml, $anchorClose);

            $zip->addFromString('word/document.xml', $newXml);
            $zip->close();
        } catch (Exception $e) {
            \Log::warning("Не удалось точно спозиционировать QR в DOCX: " . $e->getMessage());
        }
    }

    private function getColumnWidthPx($sheet, $columnLetter) {
        try { $width = $sheet->getColumnDimension($columnLetter)->getWidth(); } catch (Exception $e) { $width = null; }
        if ($width === null || $width < 0) $width = 8.43;
        return (int) round(($width * 7) + 5);
    }

    private function getRowHeightPx($sheet, $rowNumber) {
        try { $height = $sheet->getRowDimension($rowNumber)->getRowHeight(); } catch (Exception $e) { $height = null; }
        if ($height === null || $height < 0) $height = 15;
        return (int) round($height * 96 / 72);
    }

    private function generateStamp($qrPayload, $signerName, $signedDate, $qrSizePx = 100) {
        $tempDir = storage_path('app/temp_sigs');
        if (!File::exists($tempDir)) File::makeDirectory($tempDir, 0755, true);
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=2&data=" . urlencode($qrPayload);
        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $qrApiUrl, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => false]);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$content || $httpCode !== 200) throw new Exception("Не удалось получить QR-код (HTTP {$httpCode})");
        
        $qrPngPath = $tempDir . '/' . uniqid('qr_', true) . '.png';
        File::put($qrPngPath, $content);
        if (!extension_loaded('gd')) throw new Exception("Расширение GD не установлено.");
        
        $qrImage = imagecreatefrompng($qrPngPath);
        if (!$qrImage) throw new Exception("Не удалось загрузить QR-изображение.");
        
        $stampWidth = $qrSizePx; $stampHeight = $qrSizePx + 70; $scale = 2;
        $stamp = imagecreatetruecolor($stampWidth * $scale, $stampHeight * $scale);
        imageantialias($stamp, true); imagealphablending($stamp, true); imagesavealpha($stamp, false);
        $white = imagecolorallocate($stamp, 255, 255, 255);
        $black = imagecolorallocate($stamp, 0, 0, 0);
        $gray = imagecolorallocate($stamp, 200, 200, 200);
        imagefill($stamp, 0, 0, $white);
        imagecopyresampled($stamp, $qrImage, 0, 0, 0, 0, $qrSizePx * $scale, $qrSizePx * $scale, imagesx($qrImage), imagesy($qrImage));
        imageline($stamp, 5 * $scale, ($qrSizePx + 5) * $scale, ($stampWidth * $scale - 5 * $scale), ($qrSizePx + 5) * $scale, $gray);
        
        $displayName = mb_strlen($signerName) > 20 ? mb_substr($signerName, 0, 20) . '...' : $signerName;
        $nameX = max(5 * $scale, ($stampWidth * $scale - (strlen($displayName) * imagefontwidth(5))) / 2);
        imagestring($stamp, 5, $nameX, ($qrSizePx + 15) * $scale, $displayName, $black);
        
        $dateX = max(5 * $scale, ($stampWidth * $scale - (strlen($signedDate) * imagefontwidth(3))) / 2);
        imagestring($stamp, 3, $dateX, ($qrSizePx + 40) * $scale, $signedDate, $black);
        imagerectangle($stamp, 0, 0, $stampWidth * $scale - 1, $stampHeight * $scale - 1, $black);
        
        $finalStamp = imagecreatetruecolor($stampWidth, $stampHeight);
        imageantialias($finalStamp, true); imagealphablending($finalStamp, true); imagesavealpha($finalStamp, false);
        imagecopyresampled($finalStamp, $stamp, 0, 0, 0, 0, $stampWidth, $stampHeight, $stampWidth * $scale, $stampHeight * $scale);
        
        $stampPath = $tempDir . '/' . uniqid('stamp_', true) . '.png';
        imagepng($finalStamp, $stampPath, 9);
        imagedestroy($qrImage); imagedestroy($stamp); imagedestroy($finalStamp);
        if (File::exists($qrPngPath)) File::delete($qrPngPath);
        return $stampPath;
    }

    private function processPdfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = []) {
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) throw new Exception("Файл PDF не найден.");
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($originalPath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            if ($pageNo == $pageCount) {
                $pos = $this->resolvePagePosition($position, $size['width'], $size['height'], $qrSize);
                $pdf->Image($stampPath, $pos['x_mm'], $pos['y_mm'], $pos['width_mm'], $pos['height_mm'], 'PNG');
            }
        }
        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.pdf';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';
        $newFilePath = storage_path('app/public/' . $newFileName);
        if (!File::exists(dirname($newFilePath))) File::makeDirectory(dirname($newFilePath), 0755, true, true);
        $pdf->Output($newFilePath, 'F');
        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) File::makeDirectory($publicSigsPath, 0755, true, true);
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));
        return ['pdf_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    private function processDocxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = []) {
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) throw new Exception("Файл Word не найден: {$originalPath}");
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);
        if (!File::exists($stampPath)) throw new Exception("Штамп не создан.");

        $phpWord = IOFactory::load($originalPath);
        $sections = $phpWord->getSections();
        $sectionIndex = count($sections) > 0 ? count($sections) - 1 : 0;
        $section = $sections[$sectionIndex] ?? $phpWord->addSection();
        $pageSize = $this->getDocxPageSizeMm($originalPath, $section);
        $pos = $this->resolvePagePosition($position, $pageSize['width_mm'], $pageSize['height_mm'], $qrSize);

        // ✅ ИСПРАВЛЕНО: marginLeft и marginTop должны быть в PT (пунктах), а не в TWIPS!
        // Иначе PhpWord умножает их на 20, и штамп улетает за пределы страницы.
        $section->addImage($stampPath, [
            'width'            => round($pos['width_mm'] * self::MM_TO_PT_FACTOR),
            'height'           => round($pos['height_mm'] * self::MM_TO_PT_FACTOR),
            'positioning'      => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posVerticalRel'   => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'marginLeft'       => round($pos['x_mm'] * self::MM_TO_PT_FACTOR), // <-- ИСПРАВЛЕНО
            'marginTop'        => round($pos['y_mm'] * self::MM_TO_PT_FACTOR), // <-- ИСПРАВЛЕНО
            'wrappingStyle'    => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_INFRONT,
        ]);

        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.docx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';
        $newFilePath = storage_path('app/public/' . $newFileName);
        if (!File::exists(dirname($newFilePath))) File::makeDirectory(dirname($newFilePath), 0755, true, true);
        
        // Сохраняем файл
        IOFactory::createWriter($phpWord, 'Word2007')->save($newFilePath);

        // ✅ ГАРАНТИРОВАННАЯ ТОЧНОСТЬ: патчим XML после сохранения, записывая координаты в EMU
        $this->forceDocxAnchorPosition($newFilePath, $pos['x_mm'], $pos['y_mm']);

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) File::makeDirectory($publicSigsPath, 0755, true, true);
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));
        return ['docx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    private function processXlsxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = []) {
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) throw new Exception("Файл Excel не найден.");
        $spreadsheet = SpreadsheetIOFactory::load($originalPath);
        $sheet = $spreadsheet->getSheet($spreadsheet->getSheetCount() - 1);
        $drawing = new Drawing();
        $drawing->setName('QR Code Stamp'); $drawing->setDescription('Document Signature');
        $drawing->setPath($stampPath); $drawing->setHeight($qrSize + 70); $drawing->setWidth($qrSize);
        if (($position['mode'] ?? null) === 'cell' && !empty($position['cell'])) {
            $cell = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $position['cell']));
            if (!preg_match('/^[A-Z]{1,3}[0-9]{1,7}$/', $cell)) $cell = 'A1';
            preg_match('/^([A-Z]+)([0-9]+)$/', $cell, $m);
            $colWidthPx = $this->getColumnWidthPx($sheet, $m[1] ?? 'A');
            $rowHeightPx = $this->getRowHeightPx($sheet, (int)($m[2] ?? 1));
            $offsetXPercent = max(0, min(100, (float)($position['offset_x'] ?? 10)));
            $offsetYPercent = max(0, min(100, (float)($position['offset_y'] ?? 10)));
            $drawing->setCoordinates($cell);
            $drawing->setOffsetX((int) round(($offsetXPercent / 100) * $colWidthPx));
            $drawing->setOffsetY((int) round(($offsetYPercent / 100) * $rowHeightPx));
        } else {
            $drawing->setCoordinates('A1'); $drawing->setOffsetX(5); $drawing->setOffsetY(5);
        }
        $drawing->setWorksheet($sheet);
        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.xlsx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';
        $newFilePath = storage_path('app/public/' . $newFileName);
        if (!File::exists(dirname($newFilePath))) File::makeDirectory(dirname($newFilePath), 0755, true, true);
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save($newFilePath);
        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) File::makeDirectory($publicSigsPath, 0755, true, true);
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));
        return ['xlsx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    private function processRtfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '', array $position = []) {
        $originalPath = storage_path('app/public/' . $document->file_path);
        if (!File::exists($originalPath)) throw new Exception("Файл RTF не найден.");
        $tempDir = storage_path('app/temp_sigs');
        if (!File::exists($tempDir)) File::makeDirectory($tempDir, 0755, true);
        $docxTempPath = $tempDir . '/' . uniqid('rtf_', true) . '.docx';
        $converted = false;
        try {
            $libreOfficePath = $this->findLibreOffice();
            if ($libreOfficePath) {
                shell_exec(sprintf('%s --headless --convert-to docx --outdir %s %s 2>&1', escapeshellarg($libreOfficePath), escapeshellarg($tempDir), escapeshellarg($originalPath)));
                $expectedPath = $tempDir . '/' . pathinfo($originalPath, PATHINFO_FILENAME) . '.docx';
                if (File::exists($expectedPath)) { File::move($expectedPath, $docxTempPath); $converted = true; }
            }
        } catch (Exception $e) { \Log::warning("LibreOffice failed: " . $e->getMessage()); }
        if (!$converted) {
            try {
                $phpWord = IOFactory::load($originalPath, 'RTF');
                IOFactory::createWriter($phpWord, 'Word2007')->save($docxTempPath);
                if (File::exists($docxTempPath) && filesize($docxTempPath) > 0) $converted = true;
            } catch (Exception $e) { \Log::warning("PhpWord RTF failed: " . $e->getMessage()); }
        }
        if (!$converted) File::copy($originalPath, $docxTempPath);
        if (!File::exists($docxTempPath)) throw new Exception("Не удалось конвертировать RTF.");
        
        $phpWord = IOFactory::load($docxTempPath);
        $sections = $phpWord->getSections();
        $sectionIndex = count($sections) > 0 ? count($sections) - 1 : 0;
        $section = $sections[$sectionIndex] ?? $phpWord->addSection();
        $stampPath = $this->generateStamp($qrPayload, $signerName, $signedDate, $qrSize);
        $pageSize = $this->getDocxPageSizeMm($docxTempPath, $section);
        $pos = $this->resolvePagePosition($position, $pageSize['width_mm'], $pageSize['height_mm'], $qrSize);
        
        // ✅ ИСПРАВЛЕНО: marginLeft и marginTop должны быть в PT (пунктах)
        $section->addImage($stampPath, [
            'width' => round($pos['width_mm'] * self::MM_TO_PT_FACTOR), 
            'height' => round($pos['height_mm'] * self::MM_TO_PT_FACTOR),
            'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
            'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE, 
            'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE, 
            'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
            'marginLeft' => round($pos['x_mm'] * self::MM_TO_PT_FACTOR), // <-- ИСПРАВЛЕНО
            'marginTop' => round($pos['y_mm'] * self::MM_TO_PT_FACTOR), // <-- ИСПРАВЛЕНО
            'wrappingStyle' => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_INFRONT,
        ]);
        
        $time = time();
        $newFileName = 'documents/signed_' . $time . '_' . $document->id . '.docx';
        $permanentQrName = 'signatures/qr_' . $time . '_' . $document->id . '.png';
        $newFilePath = storage_path('app/public/' . $newFileName);
        if (!File::exists(dirname($newFilePath))) File::makeDirectory(dirname($newFilePath), 0755, true, true);
        
        IOFactory::createWriter($phpWord, 'Word2007')->save($newFilePath);

        // ✅ ГАРАНТИРОВАННАЯ ТОЧНОСТЬ: патчим XML после сохранения
        $this->forceDocxAnchorPosition($newFilePath, $pos['x_mm'], $pos['y_mm']);

        $publicSigsPath = storage_path('app/public/signatures');
        if (!File::exists($publicSigsPath)) File::makeDirectory($publicSigsPath, 0755, true, true);
        File::move($stampPath, storage_path('app/public/' . $permanentQrName));
        if (File::exists($docxTempPath)) File::delete($docxTempPath);
        return ['docx_path' => $newFileName, 'qr_path' => $permanentQrName];
    }

    private function findLibreOffice() {
        $paths = [
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            '/usr/local/bin/soffice', 
            '/usr/lib/libreoffice/program/soffice',
            '/opt/libreoffice/program/soffice',
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ];
        foreach ($paths as $path) if (File::exists($path)) return $path;
        if (stripos(PHP_OS, 'WIN') === 0) {
            $which = trim(shell_exec('where soffice 2>NUL') ?: '');
            if ($which) { $firstLine = trim(strtok($which, "\r\n")); if ($firstLine && File::exists($firstLine)) return $firstLine; }
            return null;
        }
        $which = trim(shell_exec('which libreoffice 2>/dev/null') ?: ''); if ($which && File::exists($which)) return $which;
        $which = trim(shell_exec('which soffice 2>/dev/null') ?: ''); if ($which && File::exists($which)) return $which;
        return null;
    }

    private function isLastStep($document) {
        $hasWorkflow = DocumentWorkflow::where('document_id', $document->id)->exists();
        if (!$hasWorkflow) return true;
        return !DocumentWorkflow::where('document_id', $document->id)->where('status', 'pending')->exists();
    }

    private function logAction($docId, $action, $desc) {
        try { DocumentLog::create(['document_id' => $docId, 'user_id' => Auth::id(), 'action' => $action, 'description' => $desc]); } 
        catch (\Exception $e) { \Log::error("Ошибка логирования: " . $e->getMessage()); }
    }

    private function processWorkflow($document, $currentWorkflow, $signer) {
        if ($currentWorkflow) {
            $currentWorkflow->update(['status' => 'approved']);
            $next = DocumentWorkflow::where('document_id', $document->id)->where('step_order', '>', $currentWorkflow->step_order)->orderBy('step_order')->first();
            if ($next) $next->update(['status' => 'pending']);
        }
    }

    private function checkSignatureAccess($signature) {
        $user = Auth::user(); $userId = (int)$user->id;
        if ($user->isAdmin()) return;
        if (!$signature->relationLoaded('document')) $signature->load('document');
        $document = $signature->document;
        if (!$document) abort(404, 'Документ для этой подписи не найден.');
        if ((int)$signature->user_id === $userId || (int)$document->created_by === $userId || (int)$document->sender_id === $userId || (int)$document->receiver_id === $userId) return;
        if (\App\Models\DocumentWorkflow::where('document_id', $document->id)->where('user_id', $userId)->exists()) return;
        try { if (\App\Models\DocumentRecipient::where('document_id', $document->id)->where('user_id', $userId)->exists()) return; } catch (\Exception $e) {}
        \Log::warning("🚨 Попытка доступа к чужой подписи", ['user_id' => $userId, 'signature_id' => $signature->id, 'document_id' => $document->id]);
        abort(403, 'У вас нет прав доступа к просмотру этой подписи.');
    }

    private function checkDocumentSigningAccess($document, $user) {
        if ($user->isAdmin()) return;
        if ($document->receiver_id == $user->id || $document->created_by == $user->id || $document->sender_id == $user->id) return;
        abort(403, 'У вас нет прав на подписание этого документа');
    }

    public function stream($id) {
        $document = Document::findOrFail($id); $user = Auth::user();
        if (!$user->isAdmin()) {
            $hasAccess = ((int)$document->created_by === (int)$user->id || (int)$document->receiver_id === (int)$user->id || (int)$document->sender_id === (int)$user->id);
            if (!$hasAccess) $hasAccess = DocumentWorkflow::where('document_id', $document->id)->where('user_id', $user->id)->exists();
            if (!$hasAccess) { try { $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)->where('user_id', $user->id)->exists(); } catch (\Exception $e) {} }
            if (!$hasAccess) abort(403, 'У вас нет доступа к этому документу.');
        }
        $path = $document->file_path;
        if (!Storage::disk('public')->exists($path)) abort(404, 'Файл не найден на сервере.');
        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'rtf' => 'application/rtf', 'txt' => 'text/plain', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'];
        return response()->file($fullPath, ['Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream', 'Content-Disposition' => 'inline; filename="' . basename($path) . '"', 'Cache-Control' => 'public, max-age=3600', 'Accept-Ranges' => 'bytes']);
    }

    public function download($id) {
        $document = Document::findOrFail($id); $user = Auth::user();
        if ($user) {
            if (!$user->isAdmin()) {
                $hasAccess = ((int)$document->created_by === (int)$user->id || (int)$document->receiver_id === (int)$user->id || (int)$document->sender_id === (int)$user->id);
                if (!$hasAccess) $hasAccess = DocumentWorkflow::where('document_id', $document->id)->where('user_id', $user->id)->exists();
                if (!$hasAccess) { try { $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)->where('user_id', $user->id)->exists(); } catch (\Exception $e) {} }
                if (!$hasAccess) abort(403, 'У вас нет доступа к этому документу.');
            }
        }
        $path = $document->file_path;
        if (!Storage::disk('public')->exists($path)) abort(404, 'Файл не найден на сервере.');
        
        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $safeTitle = preg_replace('/[^A-Za-z0-9\-_а-яА-ЯёЁ]/u', '_', $document->title);
        $filename = $safeTitle . '.' . $extension;

        return response()->download($fullPath, $filename);
    }

    public function previewPdf($id) {
        $document = Document::findOrFail($id); $user = Auth::user();
        if (!$user->isAdmin()) {
            $hasAccess = ((int)$document->created_by === (int)$user->id || (int)$document->receiver_id === (int)$user->id || (int)$document->sender_id === (int)$user->id);
            if (!$hasAccess) $hasAccess = DocumentWorkflow::where('document_id', $document->id)->where('user_id', $user->id)->exists();
            if (!$hasAccess) { try { $hasAccess = \App\Models\DocumentRecipient::where('document_id', $document->id)->where('user_id', $user->id)->exists(); } catch (\Exception $e) {} }
            if (!$hasAccess) abort(403, 'У вас нет доступа к этому документу.');
        }
        $path = $document->file_path;
        if (!Storage::disk('public')->exists($path)) abort(404, 'Файл не найден на сервере.');
        $fullPath = Storage::disk('public')->path($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'pdf') return response()->file($fullPath, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="preview.pdf"', 'Cache-Control' => 'private, max-age=300']);
        if (!in_array($extension, ['docx', 'doc', 'rtf'])) abort(415, 'Предпросмотр в PDF недоступен для этого формата.');
        $cacheDir = storage_path('app/preview_cache');
        if (!File::exists($cacheDir)) File::makeDirectory($cacheDir, 0755, true, true);
        $cachePdfPath = $cacheDir . '/doc_' . $document->id . '_' . md5($path) . '.pdf';
        $needsConvert = true;
        if (File::exists($cachePdfPath) && filemtime($cachePdfPath) >= filemtime($fullPath)) $needsConvert = false;
        if ($needsConvert) {
            $libreOfficePath = $this->findLibreOffice();
            if (!$libreOfficePath) return response()->json(['error' => 'libreoffice_missing', 'message' => 'LibreOffice не найден на сервере.'], 501);
            shell_exec(sprintf('%s --headless --convert-to pdf --outdir %s %s 2>&1', escapeshellarg($libreOfficePath), escapeshellarg($cacheDir), escapeshellarg($fullPath)));
            $expectedPdf = $cacheDir . '/' . pathinfo($fullPath, PATHINFO_FILENAME) . '.pdf';
            if (File::exists($expectedPdf)) { if (File::exists($cachePdfPath)) File::delete($cachePdfPath); File::move($expectedPdf, $cachePdfPath); }
        }
        if (!File::exists($cachePdfPath)) return response()->json(['error' => 'conversion_failed', 'message' => 'Не удалось сконвертировать документ.'], 501);
        return response()->file($cachePdfPath, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="preview.pdf"', 'Cache-Control' => 'private, max-age=300']);
    }

    public function qrImage($id) {
        $signature = DocumentSignature::findOrFail($id);
        $signature->load('document');
        $this->checkSignatureAccess($signature);
        if (!$signature->signature || !Storage::disk('public')->exists($signature->signature)) abort(404, 'QR-код не найден.');
        return response()->file(Storage::disk('public')->path($signature->signature), ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
    }

    public function verify($code)
    {
        $signature = DocumentSignature::with(['document', 'user'])
            ->where('verification_token', $code)
            ->first();

        if (!$signature || !$signature->document) {
            abort(404, 'Документ не найден или ссылка проверки недействительна.');
        }

        $document = $signature->document;
        $signer = $signature->user;
        $creator = $document->createdBy ?? $document->user;

        return view('signatures.verify', compact('signature', 'document', 'signer', 'creator'));
    }
}          