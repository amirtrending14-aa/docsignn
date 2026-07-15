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

        // ✅ ПРОСРОЧЕННЫЕ ДОКУМЕНТЫ
        $overdueQuery = Document::where('status', 'pending')
            ->where(function($q) {
                $q->where('deadline', '<', now())
                    ->orWhere('created_at', '<', now()->subDays(7));
            });

        if (!$user->isAdmin()) {
            // 🔒 БЕЗОПАСНОСТЬ: Показываем только документы, связанные с текущим юзером
            $overdueQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id);
            });
        }

        $overdueCount = $overdueQuery->count();

        // ✅ ОСНОВНОЙ ЗАПРОС
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

        // 🔒 БЕЗОПАСНОСТЬ: Загружаем только документы, доступные текущему юзеру
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
            'document_id' => 'required|exists:documents,id',
        ], [
            'document_id.required' => 'Идентификатор документа обязателен.',
            'document_id.exists' => 'Выбранный документ не найден в базе данных.',
        ]);

        $document = Document::findOrFail($request->document_id);
        $signer = Auth::user();

        // 🔒 БЕЗОПАСНОСТЬ: Проверяем право на подписание документа
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
            DB::transaction(function () use ($document, $signer, $currentWorkflow, $qrData, $extension, $qrSize, &$redirectType, $signerName, $signedDate) {

                if ($extension === 'docx' || $extension === 'doc') {
                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['docx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);

                    // 📝 ИСТОРИЯ: Подписание DOCX
                    $this->logAction($document->id, 'подписание', "DOCX документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'docx';
                    return;
                }

                if ($extension === 'pdf') {
                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['pdf_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);

                    // 📝 ИСТОРИЯ: Подписание PDF
                    $this->logAction($document->id, 'подписание', "PDF документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'pdf';
                    return;
                }

                if ($extension === 'xlsx' || $extension === 'xls') {
                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['xlsx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);

                    // 📝 ИСТОРИЯ: Подписание XLSX
                    $this->logAction($document->id, 'подписание', "Excel документ подписан пользователем {$signer->name}. QR-код внедрён в файл.");
                    $redirectType = 'xlsx';
                    return;
                }

                if ($extension === 'rtf') {
                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $this->saveSignature($document, $signer, $result['qr_path']);
                    $this->processWorkflow($document, $currentWorkflow, $signer);
                    $document->update([
                        'file_path' => $result['docx_path'],
                        'status'    => ($this->isLastStep($document)) ? 'completed' : 'processing'
                    ]);

                    // 📝 ИСТОРИЯ: Конвертация RTF и подписание
                    $this->logAction($document->id, 'подписание', "RTF конвертирован в DOCX и подписан пользователем {$signer->name}. QR-код внедрён.");
                    $redirectType = 'docx';
                    return;
                }

                // Для других типов
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

                // 📝 ИСТОРИЯ: Подписание другого формата
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

            // 📝 ИСТОРИЯ: Ошибка подписания
            $this->logAction($document->id, 'ошибка', "Ошибка при подписании документа пользователем {$signer->name}: " . $e->getMessage());

            return back()->with('error', '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Сохраняет или обновляет подпись
     */
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
        // Явно загружаем связь document, чтобы избежать проблемы N+1 и null
        $signature->load('document');

        // 🔒 БЕЗОПАСНОСТЬ: Проверяем право на просмотр подписи
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
            DB::transaction(function () use ($qrData, $signature, $document, $extension, $qrSize, &$redirectType, $signerName, $signedDate) {

                if ($extension === 'docx' || $extension === 'doc') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processDocxSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $document->update(['file_path' => $result['docx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'docx';
                    return;
                }

                if ($extension === 'pdf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processPdfSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $document->update(['file_path' => $result['pdf_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'pdf';
                    return;
                }

                if ($extension === 'xlsx' || $extension === 'xls') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processXlsxSigning($document, $qrData, $qrSize, $signerName, $signedDate);
                    $document->update(['file_path' => $result['xlsx_path']]);
                    $signature->update(['signature' => $result['qr_path'], 'signed_at' => now()]);
                    $redirectType = 'xlsx';
                    return;
                }

                if ($extension === 'rtf') {
                    if ($signature->signature) Storage::disk('public')->delete($signature->signature);
                    if ($document->file_path) Storage::disk('public')->delete($document->file_path);

                    $result = $this->processRtfSigning($document, $qrData, $qrSize, $signerName, $signedDate);
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

            // 📝 ИСТОРИЯ: Обновление подписи
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

            // 📝 ИСТОРИЯ: Ошибка обновления
            $this->logAction($document->id, 'ошибка', "Ошибка при обновлении подписи пользователем {$signer->name}: " . $e->getMessage());

            return back()->with('error', 'Ошибка обновления: ' . $e->getMessage());
        }
    }

    public function destroy(DocumentSignature $signature) {
        if (!Auth::user()->isAdmin() && $signature->user_id !== Auth::id()) {
            abort(403);
        }

        // Загружаем документ
        $signature->load('document');

        $document = $signature->document;
        $signer = Auth::user();

        // 📝 ИСТОРИЯ: Удаление подписи (ДО удаления файлов)
        // Проверяем, существует ли документ, прежде чем писать его название в лог
        $docTitle = $document ? $document->title : 'Удаленный документ';
        $this->logAction($document?->id, 'удаление подписи', "Подпись пользователя {$signer->name} удалена из документа «{$docTitle}»");

        if ($document) {
            $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
            if (in_array($extension, ['pdf', 'docx', 'doc', 'xlsx', 'xls'])) {
                // Проверяем существование файла перед удалением
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }
        }

        if ($signature->signature) {
            // Проверяем существование файла подписи
            if (Storage::disk('public')->exists($signature->signature)) {
                Storage::disk('public')->delete($signature->signature);
            }
        }

        $signature->delete();

        return back()->with('success', 'Запись удалена');
    }

    /**
     * ✅ Генерирует штамп с QR-кодом ВЫСОКОГО качества
     */
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
        imagealphablending($stamp, false);
        imagesavealpha($stamp, true);

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
        imagealphablending($finalStamp, false);
        imagesavealpha($finalStamp, true);

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
     * ✅ PDF: QR на ПОСЛЕДНЕЙ странице
     */
    private function processPdfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '')
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
                $qr_size_mm = $qrSize * self::PIXEL_TO_MM_FACTOR;
                $stamp_height_mm = ($qrSize + 70) * self::PIXEL_TO_MM_FACTOR;

                $margin = 15;
                $qr_x_mm = $size['width'] - $qr_size_mm - $margin;
                $qr_y_mm = $size['height'] - $stamp_height_mm - $margin;

                $pdf->Image($stampPath, $qr_x_mm, $qr_y_mm, $qr_size_mm, $stamp_height_mm, 'PNG');
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
     * ✅ DOCX: QR на ПОСЛЕДНЕЙ странице
     */
    private function processDocxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '')
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

        $qr_size_mm = $qrSize * self::PIXEL_TO_MM_FACTOR;
        $stamp_height_mm = ($qrSize + 70) * self::PIXEL_TO_MM_FACTOR;

        $margin = 15;
        $pageWidthMm = self::A4_WIDTH_MM;
        $pageHeightMm = self::A4_HEIGHT_MM;

        $qr_x_mm = $pageWidthMm - $qr_size_mm - $margin;
        $qr_y_mm = $pageHeightMm - $stamp_height_mm - $margin;

        $qr_x_twips = round($qr_x_mm * self::MM_TO_TWIPS_FACTOR);
        $qr_y_twips = round($qr_y_mm * self::MM_TO_TWIPS_FACTOR);
        $qr_width_pt = round($qr_size_mm * self::MM_TO_PT_FACTOR);
        $qr_height_pt = round($stamp_height_mm * self::MM_TO_PT_FACTOR);

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
            'wrappingStyle'    => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_BEHIND,
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
     * ✅ XLSX: QR на ПОСЛЕДНЕМ листе
     */
    private function processXlsxSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '')
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

        $qrX = 700;
        $qrY = 500;

        $drawing = new Drawing();
        $drawing->setName('QR Code Stamp');
        $drawing->setDescription('Document Signature');
        $drawing->setPath($stampPath);
        $drawing->setHeight($stampHeight);
        $drawing->setWidth($qrSize);
        $drawing->setOffsetX($qrX);
        $drawing->setOffsetY($qrY);
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
     * ✅ RTF: конвертируем в DOCX и ставим QR
     */
    private function processRtfSigning($document, $qrPayload, $qrSize = 100, $signerName = '', $signedDate = '')
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

        $qr_size_mm = $qrSize * self::PIXEL_TO_MM_FACTOR;
        $stamp_height_mm = ($qrSize + 70) * self::PIXEL_TO_MM_FACTOR;

        $margin = 15;
        $pageWidthMm = self::A4_WIDTH_MM;
        $pageHeightMm = self::A4_HEIGHT_MM;

        $qr_x_mm = $pageWidthMm - $qr_size_mm - $margin;
        $qr_y_mm = $pageHeightMm - $stamp_height_mm - $margin;

        $qr_x_twips = round($qr_x_mm * self::MM_TO_TWIPS_FACTOR);
        $qr_y_twips = round($qr_y_mm * self::MM_TO_TWIPS_FACTOR);
        $qr_width_pt = round($qr_size_mm * self::MM_TO_PT_FACTOR);
        $qr_height_pt = round($stamp_height_mm * self::MM_TO_PT_FACTOR);

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
            'wrappingStyle'    => \PhpOffice\PhpWord\Style\Image::WRAPPING_STYLE_BEHIND,
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
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            '/usr/lib/libreoffice/program/soffice',
            '/opt/libreoffice/program/soffice',
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                return $path;
            }
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

    /**
     * ✅ УЛУЧШЕННЫЙ МЕТОД ЛОГИРОВАНИЯ
     */
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

    // 🔒 БЕЗОПАСНОСТЬ: Проверка права на просмотр подписи
    // 🔒 БЕЗОПАСНОСТЬ: Проверка права на просмотр подписи
    private function checkSignatureAccess($signature)
    {
        $user = Auth::user();

        // Админы имеют доступ ко всему
        if ($user->isAdmin()) {
            return;
        }

        // ВАЖНО: Проверяем, загружен ли документ
        if (!$signature->document) {
            abort(404, 'Документ, связанный с этой подписью, не найден.');
        }

        // Проверяем, что подпись принадлежит текущему юзеру ИЛИ юзер имеет доступ к документу
        $hasAccess = (
            $signature->user_id == $user->id ||
            $signature->document->created_by == $user->id ||
            $signature->document->sender_id == $user->id ||
            $signature->document->receiver_id == $user->id
        );

        if (!$hasAccess) {
            abort(403, 'У вас нет прав доступа к этой подписи');
        }
    }

    // 🔒 БЕЗОПАСНОСТЬ: Проверка права на подписание документа
    private function checkDocumentSigningAccess($document, $user)
    {
        // Админы могут подписывать всё
        if ($user->isAdmin()) {
            return;
        }

        // Проверка: юзер должен быть получателем документа или его создателем
        $hasAccess = (
            $document->receiver_id == $user->id ||
            $document->created_by == $user->id ||
            $document->sender_id == $user->id
        );

        if (!$hasAccess) {
            abort(403, 'У вас нет прав на подписание этого документа');
        }
    }
}