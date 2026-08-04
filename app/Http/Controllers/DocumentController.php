<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentJob;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentLog;
use App\Models\DocumentSend;
use App\Models\DocumentSignature;
use App\Models\DocumentWorkflow;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\ActivityLogger;
use App\Services\RateLimitService; // <-- ДОБАВЛЕНО: Сервис лимитов
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentController extends Controller
{
    /**
     * AJAX: Временная загрузка файла (до отправки формы)
     * Файл сохраняется на сервер СРАЗУ, чтобы не потерять при переходе на страницу региона
     */
    // Показать страницу выбора по отделам
public function selectByDepartment(Request $request)
{
    // Получаем все отделы с количеством пользователей
    $departments = \App\Models\Department::withCount('users')->get();
    
    // Получаем выбранные отделы из сессии
    $selectedDepartmentIds = session('selected_department_ids', []);
    
    // URL для возврата (по умолчанию - страница создания документа)
    $returnUrl = $request->get('return_url', route('documents.create')); // или 'document.create', если у тебя так
    
    // ✅ ВАЖНО: здесь указано 'document.select_by_department' (единственное число)
    return view('document.select_by_department', compact(
        'departments',
        'selectedDepartmentIds',
        'returnUrl'
    ));
}

// Сохранить выбор отделов
public function storeDepartmentSelection(Request $request)
{
    $request->validate([
        'selected_departments' => 'required|string',
    ]);
    
    // Преобразуем строку "1,2,3" в массив чисел [1, 2, 3]
    $departmentIds = array_map('intval', array_filter(explode(',', $request->selected_departments)));
    
    // Сохраняем в сессию
    session(['selected_department_ids' => $departmentIds]);
    
    $returnUrl = $request->input('return_url', route('documents.create'));
    
    return redirect($returnUrl)->with('success', 'Отделы успешно выбраны');
}
    public function uploadTemp(Request $request)
    {
        $request->validate([
            'file_path' => 'required|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/rtf|max:51200',
        ], [
            'file_path.required' => 'Файл не получен',
            'file_path.mimetypes' => 'Недопустимый формат. Разрешены: PDF, DOC, DOCX, XLS, XLSX, RTF',
            'file_path.max' => 'Максимум 50 МБ',
        ]);

        $path = $request->file('file_path')->store('documents/temp', 'public');

        return response()->json([
            'success' => true,
            'temp_path' => $path,
            'file_name' => $request->file('file_path')->getClientOriginalName(),
        ]);
    }
    public function indexSignatures()
    {
        $user = Auth::user();
        $query = DocumentSignature::with(['document.createdBy', 'users']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $signatures = $query->latest()->paginate(12);
        return view('signatures.index', compact('signatures'));
    }

    public function downloadWord($id)
    {
        $document = Document::with(['createdBy', 'receiver', 'signatures.users'])->findOrFail($id);

        // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
        $this->checkDocumentAccess($document);

        // 🛡️ ЛИМИТ: Экспорт в Word (10 раз, далее 15 мин, 20 раз -> 1 час)
        $check = RateLimitService::check('export_word:' . Auth::id(), 10, [20 => 60]);
        if ($check['blocked']) {
            return back()->with('error', $check['message']);
        }

        // 📝 ИСТОРИЯ: Экспорт в Word
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'экспорт',
            'description' => 'Документ экспортирован в формат Word (.docx) пользователем ' . Auth::user()->name
        ]);

        ActivityLogger::log(
            'document_exported',
            "Экспортирован документ в Word: «{$document->title}»",
            Auth::id()
        );

        $phpWord = new PhpWord();
        $properties = $phpWord->getDocInfo();
        $properties->setTitle($document->title ?? 'Документ');
        $properties->setDescription('Сгенерировано в системе ЭДО');

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginLeft' => 1134, 'marginRight' => 1134, 'marginTop' => 1134, 'marginBottom' => 1134,
        ]);

        $phpWord->addTitleStyle(1, ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '1A365D'], ['spaceAfter' => 240]);
        $bodyStyle = ['name' => 'Arial', 'size' => 11, 'color' => '2D3748'];
        $metaStyle = ['name' => 'Arial', 'size' => 10, 'italic' => true, 'color' => '718096'];

        $section->addTitle($document->title, 1);
        $section->addText('Номер документа: ' . ($document->number ?? 'Б/Н'), ['bold' => true] + $bodyStyle);
        $section->addText('Дата создания: ' . ($document->created_at ? $document->created_at->format('d.m.Y H:i') : now()->format('d.m.Y')), $metaStyle);
        $section->addText('Отправитель: ' . optional($document->createdBy)->name, $bodyStyle);
        $section->addText('Получатель: ' . optional($document->receiver)->name, $bodyStyle);

        $section->addTextBreak(2);
        $section->addText('ОСНОВНОЙ ТЕКСТ ДОКУМЕНТА:', ['bold' => true, 'size' => 12]);

        if (!empty($document->content)) {
            Html::addHtml($section, $document->content, false, false);
        } else {
            $section->addText('Содержимое документа отсутствует.', ['italic' => true]);
        }

        $section->addTextBreak(3);
        $section->addText('СТАТУС ЭЛЕКТРОННЫХ ПОДПИСЕЙ:', ['bold' => true, 'size' => 12]);

        $tableStyle = ['borderSize' => 6, 'borderColor' => 'CBD5E0', 'cellMargin' => 100];
        $phpWord->addTableStyle('SigTable', $tableStyle);
        $table = $section->addTable('SigTable');

        $table->addRow();
        $table->addCell(3000, ['bgColor' => 'EBF8FF'])->addText('Участник', ['bold' => true]);
        $table->addCell(3000, ['bgColor' => 'EBF8FF'])->addText('Роль', ['bold' => true]);
        $table->addCell(4000, ['bgColor' => 'EBF8FF'])->addText('Статус / Дата', ['bold' => true]);

        $table->addRow();
        $table->addCell(3000)->addText(optional($document->createdBy)->name);
        $table->addCell(3000)->addText('Автор / Отправитель');
        $table->addCell(4000)->addText('Создано ' . ($document->created_at ? $document->created_at->format('d.m.Y') : ''));

        foreach ($document->signatures as $sig) {
            $table->addRow();
            $table->addCell(3000)->addText(optional($sig->user)->name);
            $table->addCell(3000)->addText('Получатель / Подписант');

            if (!empty($sig->signature)) {
                $statusText = 'ПОДПИСАНО (' . ($sig->signed_at ? \Carbon\Carbon::parse($sig->signed_at)->format('d.m.Y H:i') : '') . ')';
                $table->addCell(4000)->addText($statusText, ['color' => '2F855A', 'bold' => true]);
            } else {
                $table->addCell(4000)->addText('Ожидает подписи', ['color' => 'C53030', 'italic' => true]);
            }
        }

        $fileName = 'document_' . ($document->number ?? $id) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function downloadPdf($id)
    {
        $document = Document::with(['createdBy', 'receiver', 'signatures'])->findOrFail($id);

        // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
        $this->checkDocumentAccess($document);

        // 🛡️ ЛИМИТ: Экспорт в PDF (10 раз, далее 15 мин, 20 раз -> 1 час)
        $check = RateLimitService::check('export_pdf:' . Auth::id(), 10, [20 => 60]);
        if ($check['blocked']) {
            return back()->with('error', $check['message']);
        }

        // 📝 ИСТОРИЯ: Экспорт в PDF
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'экспорт',
            'description' => 'Документ экспортирован в PDF пользователем ' . Auth::user()->name
        ]);

        ActivityLogger::log(
            'document_exported',
            "Экспортирован документ в PDF: «{$document->title}»",
            Auth::id()
        );

        $verifyUrl = route('documents.show', $document->id);
        $qrCodePng = QrCode::format('png')
            ->size(120)
            ->margin(1)
            ->color(31, 41, 55)
            ->generate($verifyUrl);

        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodePng);

        $pdf = Pdf::loadView('pdf.document', compact('document', 'qrCodeBase64'));
        return $pdf->download('document_' . ($document->number ?? $id) . '.pdf');
    }

    public function storeFromPdf(Request $request)
    {
        // 🛡️ ЛИМИТ: ИИ-анализ (3 раза, 6 раз -> 30 мин, 9 раз -> 2 часа)
        $check = RateLimitService::check('ai_parse:' . Auth::id(), 3, [6 => 30, 9 => 120]);
        if ($check['blocked']) {
            return response()->json(['status' => 'error', 'message' => $check['message']], 429);
        }

        $request->validate(['pdf_file' => 'required|mimes:pdf,docx,rtf|max:51200']);

        $file = $request->file('pdf_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $fullText = '';

        if ($extension === 'pdf') {
            if (class_exists('\\Smalot\\PdfParser\\Parser')) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->path());
                $fullText = $pdf->getText();
            } else {
                return response()->json(['status' => 'error', 'messages' => 'Библиотека Smalot/PdfParser не установлена.'], 500);
            }
        } elseif ($extension === 'docx') {
            $zip = new \ZipArchive();
            if ($zip->open($file->path()) === true) {
                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    $data = $zip->getFromIndex($index);
                    $fullText = strip_tags($data);
                }
                $zip->close();
            }
        } elseif ($extension === 'rtf') {
            $rtfContent = file_get_contents($file->path());
            $fullText = strip_tags(preg_replace('/\\{[^}]+\\}/', '', $rtfContent));
        }

        $response = Http::post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты помощник системы ЭДО. Твоя задача: прочитать текст документа и вернуть JSON с полями: title (название), content (основной текст в HTML), summary (краткое описание).'
                ],
                ['role' => 'user', 'content' => "Текст из документа:\n" . $fullText], // Исправлено 'users' на 'user' для корректной работы API
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        $aiResult = $response->json()['choices'][0]['message']['content'] ?? '{}';
        $data = json_decode($aiResult, true);

        // 📝 ИСТОРИЯ: Анализ ИИ
        DocumentLog::create([
            'document_id' => null,
            'user_id' => Auth::id(),
            'action' => 'анализ ИИ',
            'description' => 'Документ проанализирован ИИ: ' . $file->getClientOriginalName()
        ]);

        ActivityLogger::log(
            'document_ai_parsed',
            "Документ проанализирован ИИ: " . ($file->getClientOriginalName()),
            Auth::id()
        );

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function sign(Request $request, $id)
    {
        $document = Document::with('createdBy')->findOrFail($id);

        // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
        $this->checkDocumentAccess($document);

        // 🛡️ ЛИМИТ: Подписание (5 раз, 10 раз -> 1 час, 15 раз -> 6 часов)
        $check = RateLimitService::check('doc_sign:' . Auth::id(), 5, [10 => 60, 15 => 360]);
        if ($check['blocked']) {
            return back()->with('error', $check['message']);
        }

        $signatureData = $request->input('signature');
        $fullPathToFile = storage_path('app/public/' . $document->file_path);
        $extension = strtolower(pathinfo($fullPathToFile, PATHINFO_EXTENSION));

        $signer = Auth::user();

        $currentWorkflow = DocumentWorkflow::where('document_id', $document->id)
            ->where('status', 'pending')
            ->orderBy('step_order', 'asc')
            ->first();

        if ($currentWorkflow && (int)$signer->id !== (int)$currentWorkflow->user_id) {
            return back()->with('error', 'Сейчас очередь другого пользователя!');
        }

        try {
            if (in_array($extension, ['docx', 'xlsx', 'rtf'])) {
                DocumentSignature::updateOrCreate(
                    ['document_id' => $id, 'user_id' => $signer->id],
                    ['signature' => $signatureData ?? 'Скрипт-подпись', 'signed_at' => now()]
                );

                $document->update([
                    'status' => ($this->isLastStep($document)) ? 'completed' : 'processing'
                ]);

                $this->processWorkflow($document, $currentWorkflow);

                // 📝 ИСТОРИЯ: Подписание (не PDF)
                DocumentLog::create([
                    'document_id' => $id,
                    'user_id' => $signer->id,
                    'action' => 'подписание',
                    'description' => strtoupper($extension) . ' документ подписан пользователем ' . $signer->name
                ]);

                ActivityLogger::log(
                    'document_signed',
                    "Подписан документ «{$document->title}» (формат " . strtoupper($extension) . ")",
                    $signer->id
                );

                return redirect()->route('documents.show', $id)->with('success', strtoupper($extension) . ' успешно подписан!');
            }

            return DB::transaction(function () use ($document, $signer, $currentWorkflow, $signatureData, $fullPathToFile, $request, $id) {

                if ($request->filled('qr_payload')) {
                    $qrPayload = $request->input('qr_payload');
                } else {
                    $creator = $document->createdBy;
                    $senderName = $creator->name ?? 'System';
                    $senderEmail = $creator->email ?? '-';
                    $sentDate = $document->created_at ? $document->created_at->format('d.m.Y H:i') : now()->format('d.m.Y H:i');

                    $qrPayload = "DocSign | DOC: {$document->title} | SENDER: {$senderName} | SIGNED BY: {$signer->name} | SIGNED AT: " . now()->format('d.m.Y H:i:s');
                }

                $tempDir = storage_path('app/temp_sigs');
                if (!File::exists($tempDir)) File::makeDirectory($tempDir, 0755, true);
                $tempQrImgPath = $tempDir . '/' . uniqid() . '.png';

                $qrCodePng = QrCode::format('png')->size(300)->margin(1)->generate($qrPayload);
                File::put($tempQrImgPath, $qrCodePng);

                $pdf = new Fpdi();
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetAutoPageBreak(false);

                $pageCount = $pdf->setSourceFile($fullPathToFile);
                $targetPage = $request->input('target_page', $pageCount);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);

                    if ($pageNo == $targetPage) {
                        $pdf->SetFillColor(255, 255, 255);

                        if ($signatureData) {
                            $sigImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
                            $tempSigPath = $tempDir . '/sig_' . uniqid() . '.png';
                            File::put($tempSigPath, $sigImage);

                            $sigX = $request->filled('sig_x') ? (float)$request->input('sig_x') : ($size['width'] - 65);
                            $sigY = $request->filled('sig_y') ? (float)$request->input('sig_y') : ($size['height'] - 45);

                            $pdf->Rect($sigX - 2, $sigY - 2, 54, 24, 'F');
                            $pdf->Image($tempSigPath, $sigX, $sigY, 50, 20, 'PNG');
                            @unlink($tempSigPath);
                        }

                        $stampW = 35; $stampH = 35; $qrSize = 25;

                        if ($request->filled('qr_x') && $request->filled('qr_y')) {
                            $pctX = (float)$request->input('qr_x');
                            $pctY = (float)$request->input('qr_y');

                            $x = (($pctX / 100) * $size['width']) - ($stampW / 2);
                            $y = (($pctY / 100) * $size['height']) - ($stampH / 2);
                        } else {
                            $margin = 15;
                            $x = $size['width'] - $stampW - $margin;
                            $y = $size['height'] - $stampH - $margin;
                        }

                        if ($x > ($size['width'] - $stampW)) $x = $size['width'] - $stampW;
                        if ($y > ($size['height'] - $stampH)) $y = $size['height'] - $stampH;
                        if ($x < 0) $x = 0;
                        if ($y < 0) $y = 0;

                        $pdf->Rect($x, $y, $stampW, $stampH, 'F');
                        $pdf->Rect($x, $y, $stampW, $stampH, 'D');
                        $pdf->Image($tempQrImgPath, $x + 5, $y + 2, $qrSize, $qrSize, 'PNG');

                        $pdf->SetFont('helvetica', 'B', 4.5);
                        $pdf->SetXY($x, $y + $qrSize + 3);
                        $pdf->Cell($stampW, 2.5, "VERIFIED DOCSIGN", 0, 0, 'C');
                    }
                }

                $newPdfContent = $pdf->Output('', 'S');
                Storage::disk('public')->put($document->file_path, $newPdfContent);

                $permanentQrName = 'signatures/qr_' . time() . '.png';
                if (!File::exists(storage_path('app/public/signatures'))) {
                    File::makeDirectory(storage_path('app/public/signatures'), 0755, true);
                }
                File::move($tempQrImgPath, storage_path('app/public/' . $permanentQrName));

                DocumentSignature::updateOrCreate(
                    ['document_id' => $document->id, 'user_id' => $signer->id],
                    ['signature' => $permanentQrName, 'signed_at' => now()]
                );

                $document->update([
                    'status' => ($this->isLastStep($document)) ? 'completed' : 'processing'
                ]);

                // 📝 ИСТОРИЯ: Подписание PDF
                DocumentLog::create([
                    'document_id' => $document->id,
                    'user_id' => $signer->id,
                    'action' => 'подписание',
                    'description' => "PDF-документ подписан и штампован пользователем: {$signer->name}"
                ]);

                $this->processWorkflow($document, $currentWorkflow);

                ActivityLogger::log(
                    'document_signed',
                    "Подписан PDF-документ «{$document->title}» с QR-штампом",
                    $signer->id
                );

                return redirect()->route('documents.show', $id)->with('success', 'Документ успешно подписан!');
            });

        } catch (\Exception $e) {
            if (isset($tempQrImgPath) && File::exists($tempQrImgPath)) @unlink($tempQrImgPath);
            return back()->with('error', 'Ошибка системы DocSign: ' . $e->getMessage());
        }
    }

    private function isLastStep($document) {
        return !DocumentWorkflow::where('document_id', $document->id)->where('status', 'pending')->exists();
    }

    private function processWorkflow($document, $currentWorkflow) {
        $hasWorkflow = DocumentWorkflow::where('document_id', $document->id)->exists();
        if (!$hasWorkflow) {
            $document->update(['status' => 'completed']);
            return;
        }

        if ($currentWorkflow) {
            $currentWorkflow->update(['status' => 'approved']);
            $next = DocumentWorkflow::where('document_id', $document->id)
                ->where('step_order', '>', $currentWorkflow->step_order)
                ->orderBy('step_order')
                ->first();

            if ($next) {
                $next->update(['status' => 'pending']);
            } else {
                $document->update(['status' => 'completed']);
            }
        }
    }
    /**
     * ✅ ДОБАВЛЕНО: Отображение списка документов (обязательно для Route::resource)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Базовый запрос с подгрузкой связей
        $query = Document::with(['createdBy', 'receiver', 'signatures.user']);

        // Если пользователь не админ, показываем только его документы
        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        }

        // Сортировка по дате (новые сверху) и пагинация по 15 штук
        $documents = $query->latest()->paginate(15);

        // ВАЖНО: Если твоя папка с видами называется 'documents' (с буквой s на конце), 
        // поменяй 'document.index' на 'documents.index'
        return view('document.index', compact('documents'));
    } 
    public function create()
    {
        $user = auth()->user();

        // Данные для режима "По отделам"
        $selectedDepartmentIds = session('selected_department_ids', []);
        $selectedDepartments = \App\Models\Department::whereIn('id', $selectedDepartmentIds)->with('users')->get();

        // ✅ ДОБАВЛЕНО: Данные для режима "Из дерева компаний"
        $selectedCompanyId = session('selected_company_id');
        $selectedCompanyUsers = session('selected_company_users', []);

        $teamUsers = \App\Models\User::where('company_id', $user->company_id)->where('id', '!=', $user->id)->get();

        $otherUsers = \App\Models\User::where(function ($query) use ($user) {
                $query->where('company_id', '!=', $user->company_id)->orWhereNull('company_id');
            })->where('id', '!=', $user->id)->get();

        $teamUsersArray = $teamUsers->map(function ($u) {
            return [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role,
                'phone' => $u->phone ?? null, 'company' => $u->company?->name ?? null,
            ];
        })->values()->toArray();

        $otherUsersArray = $otherUsers->map(function ($u) {
            return [
                'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role,
                'company' => $u->company?->name ?? ($u->company ?? null), 'phone' => $u->phone ?? null,
            ];
        })->values()->toArray();

        return view('document.create', compact(
            'selectedDepartments',
            'selectedDepartmentIds',
            'selectedCompanyId',       // ✅ ДОБАВЛЕНО
            'selectedCompanyUsers',    // ✅ ДОБАВЛЕНО
            'teamUsers',
            'otherUsers',
            'teamUsersArray',
            'otherUsersArray'
        ));
    }

public function store(Request $request)
{
    // 🛡️ ЛИМИТ
    $check = RateLimitService::check('doc_store:' . Auth::id(), 10, [20 => 60, 30 => 360]);
    if ($check['blocked']) {
        return back()->withErrors(['file_path' => $check['message']]);
    }

    $data = $request->validate([
        'number'            => 'required|string|max:255',
        'type'              => 'required|string|max:255',
        'title'             => 'required|string|max:255',
        'content'           => 'nullable|string',
        'deadline'          => 'nullable|date',
        'status'            => 'required|in:draft,active',
        'file_path'         => 'nullable|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/rtf|max:51200',
        'temp_file_path'    => 'nullable|string|max:500',
        'receiver_mode'     => 'nullable|in:all_team,select_team,other_company,by_region,by_department,by_company',
        'team_receivers'    => 'nullable|string',
        'other_receiver_id' => 'nullable|integer|exists:users,id',
    ], [
        'number.required'   => 'Номер документа обязателен',
        'type.required'     => 'Тип документа обязателен',
        'title.required'    => 'Заголовок обязателен',
        'status.required'   => 'Статус обязателен',
    ]);

    $authUser = auth()->user();

    // ═══════════════════════════════════════════════════════════
    // ✅ ЛОГИКА ФАЙЛА
    // ═══════════════════════════════════════════════════════════
    if ($request->hasFile('file_path')) {
        $filePath = $request->file('file_path')->store('documents', 'public');
    } elseif (!empty($data['temp_file_path'])) {
        $filePath = $data['temp_file_path'];
        if (!\Storage::disk('public')->exists($filePath)) {
            return back()->withErrors(['file_path' => 'Файл не найден на сервере. Пожалуйста, загрузите его снова.']);
        }
    } else {
        return back()->withErrors(['file_path' => 'Необходимо прикрепить файл']);
    }

    // ═══════════════════════════════════════════════════════════
    // ЕСЛИ ЧЕРНОВИК
    // ═══════════════════════════════════════════════════════════
    if ($data['status'] === 'draft') {
        $document = Document::create([
            'number'      => $data['number'],
            'type'        => $data['type'],
            'title'       => $data['title'],
            'content'     => $data['content'] ?? null,
            'deadline'    => $data['deadline'] ?? null,
            'status'      => 'draft',
            'file_path'   => $filePath,
            'sender_id'   => $authUser->id,
            'receiver_id' => null,
            'created_by'  => $authUser->id,
        ]);

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $authUser->id,
            'action'      => 'создание',
            'description' => "Создан черновик документа «{$data['title']}» (№{$data['number']})"
        ]);

        ActivityLogger::log(
            'document_created',
            "Создан черновик документа «{$data['title']}» (№{$data['number']}) — сохранён для себя",
            $authUser->id
        );

        // Очистка сессий
        session()->forget(['selected_recipients', 'selected_department_ids', 'selected_company_id', 'selected_company_users']);

        return redirect()->route('documents.index')->with('success', 'Черновик успешно сохранён');
    }

    // ═══════════════════════════════════════════════════════════
    // ЕСЛИ АКТИВНЫЙ — определяем получателей
    // ═══════════════════════════════════════════════════════════
    $receivers = [];

    if (empty($data['receiver_mode'])) {
        return back()->withErrors(['receiver_mode' => 'Выберите способ отправки для активного документа']);
    }

    if ($data['receiver_mode'] === 'all_team') {
        $receivers = User::where('company_id', $authUser->company_id)
            ->where('id', '!=', $authUser->id)
            ->pluck('id')
            ->toArray();

        if (empty($receivers)) {
            return back()->withErrors(['receiver_mode' => 'В вашей компании нет других сотрудников']);
        }

    } elseif ($data['receiver_mode'] === 'select_team') {
        if (empty($data['team_receivers'])) {
            return back()->withErrors(['team_receivers' => 'Выберите хотя бы одного получателя']);
        }
        $receiverIds = array_map('intval', explode(',', $data['team_receivers']));
        $receivers = User::whereIn('id', $receiverIds)
            ->where('id', '!=', $authUser->id)
            ->pluck('id')
            ->toArray();

        if (empty($receivers)) {
            return back()->withErrors(['team_receivers' => 'Выбранные пользователи не найдены']);
        }

    } elseif ($data['receiver_mode'] === 'other_company') {
        if (empty($data['other_receiver_id'])) {
            return back()->withErrors(['other_receiver_id' => 'Выберите получателя']);
        }
        // Проверяем что пользователь существует
        $otherUser = User::find($data['other_receiver_id']);
        if (!$otherUser) {
            return back()->withErrors(['other_receiver_id' => 'Выбранный пользователь не найден']);
        }
        $receivers = [$data['other_receiver_id']];

    // ═══════════════════════════════════════════════════════════
    // ✅ РЕЖИМ "ПО РЕГИОНУ" — ИСПРАВЛЕННАЯ ВЕРСИЯ
    // ═══════════════════════════════════════════════════════════
    } elseif ($data['receiver_mode'] === 'by_region') {
        $recipientIds = session('selected_recipients', []);
        
        // Приводим к массиву чисел на всякий случай
        if (!is_array($recipientIds)) {
            $recipientIds = [];
        }
        $recipientIds = array_map('intval', $recipientIds);
        $recipientIds = array_filter($recipientIds, fn($id) => $id > 0);

        if (empty($recipientIds)) {
            return back()->withErrors(['receiver_mode' => 'Сессия получателей пуста. Выберите получателей по региону заново.']);
        }

        // Находим всех существующих пользователей (без фильтра)
        $foundUsers = User::whereIn('id', $recipientIds)->get();
        
        if ($foundUsers->isEmpty()) {
            // Сбрасываем невалидную сессию
            session()->forget('selected_recipients');
            return back()->withErrors(['receiver_mode' => 'Выбранные пользователи не найдены в системе (возможно удалены). Выберите заново.']);
        }

        // ✅ ВАЖНО: НЕ фильтруем по `!= authUser` — разрешаем отправку себе
        // Это нормально когда документ нужен и автору тоже
        $receivers = $foundUsers->pluck('id')->toArray();

        // Если выбрал ТОЛЬКО себя — предупреждаем (но не блокируем)
        if (count($receivers) === 1 && in_array($authUser->id, $receivers)) {
            // Всё равно создаём документ — пользователь сам себе отправляет
            \Log::info('DocSign: Пользователь отправляет документ сам себе (by_region)');
        }

    // ═══════════════════════════════════════════════════════════
    // ✅ РЕЖИМ "ПО ОТДЕЛАМ"
    // ═══════════════════════════════════════════════════════════
    } elseif ($data['receiver_mode'] === 'by_department') {
        $deptIds = session('selected_department_ids', []);
        
        if (!is_array($deptIds)) $deptIds = [];
        $deptIds = array_map('intval', $deptIds);
        $deptIds = array_filter($deptIds, fn($id) => $id > 0);

        if (empty($deptIds)) {
            return back()->withErrors(['receiver_mode' => 'Выберите хотя бы один отдел']);
        }

        $receivers = User::whereIn('department_id', $deptIds)
            ->where('id', '!=', $authUser->id)
            ->pluck('id')
            ->toArray();

        if (empty($receivers)) {
            return back()->withErrors(['receiver_mode' => 'В выбранных отделах нет доступных пользователей (кроме вас)']);
        }

    // ═══════════════════════════════════════════════════════════
    // ✅ РЕЖИМ "ИЗ ДЕРЕВА КОМПАНИЙ"
    // ═══════════════════════════════════════════════════════════
    } elseif ($data['receiver_mode'] === 'by_company') {
        $companyId = session('selected_company_id');
        $userIds = session('selected_company_users', []);
        
        if (!is_array($userIds)) $userIds = [];
        $userIds = array_map('intval', $userIds);
        $userIds = array_filter($userIds, fn($id) => $id > 0);

        if (empty($companyId) || empty($userIds)) {
            return back()->withErrors(['receiver_mode' => 'Выберите компанию и хотя бы одного сотрудника']);
        }

        $receivers = User::whereIn('id', $userIds)
            ->where('company_id', $companyId)
            ->where('id', '!=', $authUser->id)
            ->pluck('id')
            ->toArray();

        if (empty($receivers)) {
            return back()->withErrors(['receiver_mode' => 'В выбранной компании нет доступных сотрудников (кроме вас)']);
        }
    }

    if (empty($receivers)) {
        return back()->withErrors(['receiver_mode' => 'Не удалось определить получателей']);
    }

    // Убираем дубликаты (на случай если кто-то попал в несколько категорий)
    $receivers = array_unique($receivers);

    // ═══════════════════════════════════════════════════════════
    // Создаём документы для каждого получателя
    // ═══════════════════════════════════════════════════════════
    $createdCount = 0;
    foreach ($receivers as $receiverId) {
        $receiver = User::find($receiverId);
        if (!$receiver) continue;

        $document = Document::create([
            'number'      => $data['number'],
            'type'        => $data['type'],
            'title'       => $data['title'],
            'content'     => $data['content'] ?? null,
            'deadline'    => $data['deadline'] ?? null,
            'status'      => 'active',
            'file_path'   => $filePath,
            'sender_id'   => $authUser->id,
            'receiver_id' => $receiverId,
            'created_by'  => $authUser->id,
        ]);

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $authUser->id,
            'action'      => 'создание',
            'description' => "Создан документ «{$data['title']}» (№{$data['number']}) и отправлен пользователю: {$receiver->name}"
        ]);

        DocumentSignature::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $receiverId],
            ['signature' => '']
        );

        // Не отправляем уведомление самому себе
        if ($receiverId != $authUser->id) {
            Notification::create([
                'user_id'         => $receiverId,
                'type'            => 'assigned',
                'messages'        => 'Вам назначен документ на подпись: ' . $document->title,
                'notifiable_type' => User::class,
                'notifiable_id'   => $receiverId,
                'is_read'         => false,
                'data'            => [
                    'document_id'    => $document->id,
                    'type'           => 'assigned',
                    'user_name'      => $authUser->name,
                    'user_email'     => $authUser->email,
                    'document_title' => $document->title,
                    'message'        => 'Новый документ на подпись: ' . $document->title,
                ],
            ]);
        }

        $createdCount++;
    }

    if ($createdCount === 0) {
        return back()->withErrors(['receiver_mode' => 'Не удалось создать ни одного документа. Проверьте получателей.']);
    }

    // ✅ Очищаем ВСЕ сессии после успешной отправки
    session()->forget([
        'selected_recipients',
        'selected_department_ids',
        'selected_company_id',
        'selected_company_users'
    ]);

    ActivityLogger::log(
        'document_created',
        "Создан и отправлен документ «{$data['title']}» (№{$data['number']}) — отправлен {$createdCount} получателю(ям)",
        $authUser->id
    );

    return redirect()->route('documents.index')
        ->with('success', "Документ успешно отправлен {$createdCount} получателю(ям)");
}

    public function sendToSign($id)
    {
        $document = Document::findOrFail($id);

        if ((int)$document->created_by !== (int)Auth::id() && !Auth::user()->isAdmin()) {
            return back()->with('error', 'У вас нет прав на отправку этого документа');
        }

        if ($document->status !== 'draft') {
            return back()->with('error', 'Можно отправить только документ в статусе "Черновик"');
        }

        if (!$document->receiver_id) {
            return back()->with('error', 'Не указан получатель документа');
        }

        $receiver = User::findOrFail($document->receiver_id);

        $document->update(['status' => 'active']);

        DocumentSignature::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $receiver->id],
            ['signature' => '']
        );

        Notification::create([
            'user_id'         => $receiver->id,
            'type'            => 'assigned',
            'messages'        => 'Вам отправлен документ на подпись: ' . $document->title,
            'notifiable_type' => User::class,
            'notifiable_id'   => $receiver->id,
            'is_read'         => false,
            'data'            => [
                'document_id'    => $document->id,
                'type'           => 'assigned',
                'user_name'      => Auth::user()->name,
                'user_email'     => Auth::user()->email,
                'document_title' => $document->title,
                'message'        => 'Документ отправлен на подпись: ' . $document->title,
            ],
        ]);

        // 📝 ИСТОРИЯ: Отправка на подпись
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'отправка',
            'description' => "Документ «{$document->title}» отправлен на подпись пользователю: {$receiver->name}"
        ]);

        ActivityLogger::log(
            'document_sent',
            "Документ «{$document->title}» отправлен на подпись: {$receiver->name}",
            Auth::id()
        );

        return redirect()->route('documents.show', $id)->with('success', 'Документ успешно отправлен на подпись!');
    }

    public function pdf($id)
    {
        $document = Document::findOrFail($id);

        // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
        $this->checkDocumentAccess($document);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);

            // 📝 ИСТОРИЯ: Скачивание файла
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'action' => 'скачивание',
                'description' => 'Скачан исходный файл документа пользователем ' . Auth::user()->name
            ]);

            ActivityLogger::log(
                'document_downloaded',
                "Скачан исходный файл документа «{$document->title}»",
                Auth::id()
            );

            return Storage::disk('public')->download(
                $document->file_path,
                $document->title . '.' . $extension
            );
        }

        return back()->with('error', 'Файл не найден');
    }

    public function show($id)
    {
        $document = Document::with(['createdBy', 'receiver', 'logs', 'signatures.user'])->findOrFail($id);

        // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
        $this->checkDocumentAccess($document);

        $comments = DocumentComment::with('user')->where('document_id', $id)->latest()->get();

        $verifyUrl = route('documents.show', $document->id);
        $qrCodeSvg = QrCode::size(130)
            ->backgroundColor(255, 255, 255, 0)
            ->color(31, 41, 55)
            ->margin(0)
            ->generate($verifyUrl);

        return view('document.show', compact('document', 'comments', 'qrCodeSvg'));
    }

  public function edit($id)
{
    $document = Document::findOrFail($id);

    // 🔒 БЕЗОПАСНОСТЬ: Проверка прав доступа
    $this->checkDocumentAccess($document);

    $authUser = auth()->user();

    // ═══════════════════════════════════════════════════════════
    // ✅ ДОБАВЛЕНО: Данные для режима "По отделам"
    // ═══════════════════════════════════════════════════════════
    $selectedDepartmentIds = session('selected_department_ids', []);
    $selectedDepartments = \App\Models\Department::whereIn('id', $selectedDepartmentIds)->with('users')->get();

    // ═══════════════════════════════════════════════════════════
    // ✅ ДОБАВЛЕНО: Данные для режима "Из дерева компаний"
    // ═══════════════════════════════════════════════════════════
    $selectedCompanyId = session('selected_company_id');
    $selectedCompanyUsers = session('selected_company_users', []);

    $teamUsers = User::where('company_id', $authUser->company_id)
        ->where('id', '!=', $authUser->id)
        ->get();

    $otherUsers = User::where('company_id', '!=', $authUser->company_id)
        ->orWhereNull('company_id')
        ->where('id', '!=', $authUser->id)
        ->get();

    $teamUsersArray = $teamUsers->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'phone' => $u->phone ?? null,
            'company' => $u->company?->name ?? null, // ✅ ДОБАВЛЕНО для поиска
        ];
    })->values()->toArray();

    $otherUsersArray = $otherUsers->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'company' => $u->company?->name ?? ($u->company ?? null), // ✅ ДОБАВЛЕНО
            'phone' => $u->phone ?? null,
        ];
    })->values()->toArray();

    $currentReceiver = $document->receiver_id ? User::find($document->receiver_id) : null;

    return view('document.edit', compact(
        'document',
        'selectedDepartments',      // ✅ ДОБАВЛЕНО
        'selectedDepartmentIds',    // ✅ ДОБАВЛЕНО
        'selectedCompanyId',        // ✅ ДОБАВЛЕНО
        'selectedCompanyUsers',     // ✅ ДОБАВЛЕНО
        'teamUsers',
        'otherUsers',
        'teamUsersArray',
        'otherUsersArray',
        'currentReceiver'
    ));
}

public function update(Request $request, Document $document)
{
    $isAdmin = auth()->user()->isAdmin();
    $isOwner = (int)$document->created_by === (int)auth()->id();

    if (!$isOwner && !$isAdmin) {
        abort(403, 'У вас нет прав на изменение этого документа.');
    }

    $request->validate([
        'number'              => 'nullable|string|max:255',
        'type'                => 'required|string|max:255',
        'title'               => 'required|string|max:255',
        'content'             => 'nullable|string',
        'deadline'            => 'nullable|date',
        'status'              => 'required|in:draft,active,completed',
        'file_path'           => 'nullable|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/rtf|max:51200',
        'temp_file_path'      => 'nullable|string|max:500',
        'remove_existing_file' => 'nullable|in:0,1',
        'receiver_mode'       => 'nullable|in:all_team,select_team,other_company,by_region,by_department,by_company',
        'team_receivers'      => 'nullable|string',
        'other_receiver_id'   => 'nullable|integer|exists:users,id',
    ], [
        'type.required'  => 'Тип документа обязателен',
        'title.required' => 'Заголовок обязателен',
        'status.required' => 'Статус обязателен',
    ]);

    $authUser = auth()->user();
    $oldStatus = $document->status;
    $newStatus = $request->input('status');

    $data = $request->only(['number', 'type', 'title', 'content', 'status', 'deadline']);

    // ═══════════════════════════════════════════════════════════
    // ✅ ЛОГИКА ФАЙЛА (как в store)
    // ═══════════════════════════════════════════════════════════
    if ($request->hasFile('file_path')) {
        // Загружен новый файл — удаляем старый
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $data['file_path'] = $request->file('file_path')->store('documents', 'public');
    } elseif (!empty($request->input('temp_file_path'))) {
        // AJAX-загруженный файл
        $tempPath = $request->input('temp_file_path');
        if (Storage::disk('public')->exists($tempPath)) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $tempPath;
        } else {
            return back()->withErrors(['file_path' => 'Файл не найден на сервере. Загрузите его снова.']);
        }
    } elseif ($request->input('remove_existing_file') === '1') {
        // Пользователь нажал "Удалить файл"
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $data['file_path'] = null;
    }
    // Иначе — оставляем старый файл как есть

    // ═══════════════════════════════════════════════════════════
    // ЕСЛИ СТАТУС МЕНЯЕТСЯ С DRAFT НА ACTIVE — ОТПРАВКА ВСЕМ
    // ═══════════════════════════════════════════════════════════
    $receivers = [];
    $isActivating = ($oldStatus === 'draft' && $newStatus === 'active');

    if ($isActivating) {
        if (empty($request->receiver_mode)) {
            return back()->withErrors(['receiver_mode' => 'Выберите способ отправки для активного документа']);
        }

        if ($request->receiver_mode === 'all_team') {
            $receivers = User::where('company_id', $authUser->company_id)
                ->where('id', '!=', $authUser->id)
                ->pluck('id')
                ->toArray();

            if (empty($receivers)) {
                return back()->withErrors(['receiver_mode' => 'В вашей компании нет других сотрудников']);
            }

        } elseif ($request->receiver_mode === 'select_team') {
            if (empty($request->team_receivers)) {
                return back()->withErrors(['team_receivers' => 'Выберите хотя бы одного получателя']);
            }
            $receiverIds = array_map('intval', explode(',', $request->team_receivers));
            $receivers = User::whereIn('id', $receiverIds)
                ->where('id', '!=', $authUser->id)
                ->pluck('id')
                ->toArray();

            if (empty($receivers)) {
                return back()->withErrors(['team_receivers' => 'Выбранные пользователи не найдены']);
            }

        } elseif ($request->receiver_mode === 'other_company') {
            if (empty($request->other_receiver_id)) {
                return back()->withErrors(['other_receiver_id' => 'Выберите получателя']);
            }
            $otherUser = User::find($request->other_receiver_id);
            if (!$otherUser) {
                return back()->withErrors(['other_receiver_id' => 'Выбранный пользователь не найден']);
            }
            $receivers = [$request->other_receiver_id];

        // ═══════════════════════════════════════════════════════════
        // ✅ РЕЖИМ "ПО РЕГИОНУ" (как в store)
        // ═══════════════════════════════════════════════════════════
        } elseif ($request->receiver_mode === 'by_region') {
            $recipientIds = session('selected_recipients', []);

            if (!is_array($recipientIds)) $recipientIds = [];
            $recipientIds = array_map('intval', $recipientIds);
            $recipientIds = array_filter($recipientIds, fn($id) => $id > 0);

            if (empty($recipientIds)) {
                return back()->withErrors(['receiver_mode' => 'Сессия получателей пуста. Выберите получателей по региону заново.']);
            }

            $foundUsers = User::whereIn('id', $recipientIds)->get();

            if ($foundUsers->isEmpty()) {
                session()->forget('selected_recipients');
                return back()->withErrors(['receiver_mode' => 'Выбранные пользователи не найдены в системе. Выберите заново.']);
            }

            // ✅ Разрешаем отправку себе
            $receivers = $foundUsers->pluck('id')->toArray();

        // ═══════════════════════════════════════════════════════════
        // ✅ РЕЖИМ "ПО ОТДЕЛАМ"
        // ═══════════════════════════════════════════════════════════
        } elseif ($request->receiver_mode === 'by_department') {
            $deptIds = session('selected_department_ids', []);

            if (!is_array($deptIds)) $deptIds = [];
            $deptIds = array_map('intval', $deptIds);
            $deptIds = array_filter($deptIds, fn($id) => $id > 0);

            if (empty($deptIds)) {
                return back()->withErrors(['receiver_mode' => 'Выберите хотя бы один отдел']);
            }

            $receivers = User::whereIn('department_id', $deptIds)
                ->where('id', '!=', $authUser->id)
                ->pluck('id')
                ->toArray();

            if (empty($receivers)) {
                return back()->withErrors(['receiver_mode' => 'В выбранных отделах нет доступных пользователей (кроме вас)']);
            }

        // ═══════════════════════════════════════════════════════════
        // ✅ РЕЖИМ "ИЗ ДЕРЕВА КОМПАНИЙ"
        // ═══════════════════════════════════════════════════════════
        } elseif ($request->receiver_mode === 'by_company') {
            $companyId = session('selected_company_id');
            $userIds = session('selected_company_users', []);

            if (!is_array($userIds)) $userIds = [];
            $userIds = array_map('intval', $userIds);
            $userIds = array_filter($userIds, fn($id) => $id > 0);

            if (empty($companyId) || empty($userIds)) {
                return back()->withErrors(['receiver_mode' => 'Выберите компанию и хотя бы одного сотрудника']);
            }

            $receivers = User::whereIn('id', $userIds)
                ->where('company_id', $companyId)
                ->where('id', '!=', $authUser->id)
                ->pluck('id')
                ->toArray();

            if (empty($receivers)) {
                return back()->withErrors(['receiver_mode' => 'В выбранной компании нет доступных сотрудников (кроме вас)']);
            }
        }

        if (empty($receivers)) {
            return back()->withErrors(['receiver_mode' => 'Не удалось определить получателей']);
        }

        $receivers = array_unique($receivers);
    }

    // ═══════════════════════════════════════════════════════════
    // ОБНОВЛЯЕМ ОСНОВНОЙ ДОКУМЕНТ
    // ═══════════════════════════════════════════════════════════
    // Первый получатель (или текущий) становится receiver_id основного документа
    if ($isActivating && !empty($receivers)) {
        $data['receiver_id'] = $receivers[0];
    } elseif ($newStatus === 'draft') {
        $data['receiver_id'] = null;
    }

    $document->update($data);

    // 📝 ИСТОРИЯ: Обновление документа
    DocumentLog::create([
        'document_id' => $document->id,
        'user_id'     => Auth::id(),
        'action'      => 'обновление',
        'description' => "Обновлён документ «{$document->title}» (статус: {$newStatus})"
    ]);

    ActivityLogger::log(
        'document_updated',
        "Обновлён документ «{$document->title}» (статус: {$newStatus})",
        Auth::id()
    );

    // ═══════════════════════════════════════════════════════════
    // ЕСЛИ АКТИВИРОВАЛИ — СОЗДАЁМ КОПИИ ДЛЯ ВСЕХ ПОЛУЧАТЕЛЕЙ
    // ═══════════════════════════════════════════════════════════
    $sentCount = 0;

    if ($isActivating && !empty($receivers)) {
        // Подпись и уведомление для первого получателя (основной документ)
        $firstReceiverId = $receivers[0];
        
        DocumentSignature::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => $firstReceiverId],
            ['signature' => '']
        );

        if ($firstReceiverId != $authUser->id) {
            Notification::create([
                'user_id'         => $firstReceiverId,
                'type'            => 'assigned',
                'messages'        => 'Вам отправлен документ на подпись: ' . $document->title,
                'notifiable_type' => User::class,
                'notifiable_id'   => $firstReceiverId,
                'is_read'         => false,
                'data'            => [
                    'document_id'    => $document->id,
                    'type'           => 'assigned',
                    'user_name'      => $authUser->name,
                    'user_email'     => $authUser->email,
                    'document_title' => $document->title,
                    'message'        => 'Документ отправлен на подпись: ' . $document->title,
                ],
            ]);
        }

        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => Auth::id(),
            'action'      => 'отправка',
            'description' => "Черновик «{$document->title}» отправлен на подпись пользователю: " . User::find($firstReceiverId)?->name,
        ]);

        $sentCount++;

        // Создаём КОПИИ для остальных получателей (со 2-го)
        for ($i = 1; $i < count($receivers); $i++) {
            $receiverId = $receivers[$i];
            $receiver = User::find($receiverId);
            if (!$receiver) continue;

            $copy = Document::create([
                'number'      => $document->number,
                'type'        => $document->type,
                'title'       => $document->title,
                'content'     => $document->content,
                'deadline'    => $document->deadline,
                'status'      => 'active',
                'file_path'   => $document->file_path,
                'sender_id'   => $authUser->id,
                'receiver_id' => $receiverId,
                'created_by'  => $authUser->id,
            ]);

            DocumentLog::create([
                'document_id' => $copy->id,
                'user_id'     => Auth::id(),
                'action'      => 'создание',
                'description' => "Создана копия документа «{$document->title}» для пользователя: {$receiver->name}",
            ]);

            DocumentSignature::updateOrCreate(
                ['document_id' => $copy->id, 'user_id' => $receiverId],
                ['signature' => '']
            );

            if ($receiverId != $authUser->id) {
                Notification::create([
                    'user_id'         => $receiverId,
                    'type'            => 'assigned',
                    'messages'        => 'Вам отправлен документ на подпись: ' . $document->title,
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $receiverId,
                    'is_read'         => false,
                    'data'            => [
                        'document_id'    => $copy->id,
                        'type'           => 'assigned',
                        'user_name'      => $authUser->name,
                        'user_email'     => $authUser->email,
                        'document_title' => $document->title,
                        'message'        => 'Документ отправлен на подпись: ' . $document->title,
                    ],
                ]);
            }

            $sentCount++;
        }

        ActivityLogger::log(
            'document_sent',
            "Черновик «{$document->title}» отправлен на подпись {$sentCount} получателю(ям)",
            Auth::id()
        );
    }

    // ═══════════════════════════════════════════════════════════
    // ✅ ОЧИЩАЕМ ВСЕ СЕССИИ ПОСЛЕ УСПЕШНОГО ОБНОВЛЕНИЯ
    // ═══════════════════════════════════════════════════════════
    session()->forget([
        'selected_recipients',       // ← by_region
        'selected_department_ids',   // ← by_department
        'selected_company_id',       // ← by_company
        'selected_company_users'     // ← by_company
    ]);

    // Формируем сообщение
    $message = 'Документ успешно обновлён!';
    if ($isActivating && $sentCount > 0) {
        $message = "Документ обновлён и отправлен {$sentCount} получателю(ям)!";
    }

    return redirect()->route('documents.index')->with('success', $message);
}

    public function destroy(Document $document)
    {
        if ($document->created_by !== Auth::id() && !Auth::user()->isAdmin()) {
            return back()->with('error', 'У вас нет прав на удаление этого документа');
        }

        // 📝 ИСТОРИЯ: Удаление документа (ДО удаления файла)
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'удаление',
            'description' => 'Удалён документ "' . $document->title . '" (№' . $document->number . ')'
        ]);

        ActivityLogger::log(
            'document_deleted',
            "Удалён документ «{$document->title}» (№{$document->number})",
            Auth::id()
        );

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Документ удален!');
    }

    public function generateWithAI(Request $request)
    {
        // 🛡️ ЛИМИТ: ИИ-генерация (3 раза, 6 раз -> 30 мин, 9 раз -> 2 часа)
        $check = RateLimitService::check('ai_generate:' . Auth::id(), 3, [6 => 30, 9 => 120]);
        if ($check['blocked']) {
            return response()->json(['message' => $check['message']], 429);
        }

        $validated = $request->validate([
            'type'      => 'required|in:contract,invoice,act,nda',
            'recipient' => 'required|string|max:255',
            'format'    => 'required|in:pdf,docx',
            'details'   => 'nullable|array',
        ]);

        $document = Document::create([
            'user_id'    => auth()->id(),
            'created_by' => auth()->id(),
            'type'       => $validated['type'],
            'title'      => 'Генерируется...',
            'status'     => 'processing',
            'receiver_id'=> null,
        ]);

        GenerateDocumentJob::dispatch(
            $document,
            $validated['type'],
            $validated['recipient'],
            $validated['details'] ?? [],
            $validated['format']
        );

        // 📝 ИСТОРИЯ: Генерация через ИИ
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => 'генерация ИИ',
            'description' => "Запущена генерация документа через ИИ: тип={$validated['type']}, получатель={$validated['recipient']}"
        ]);

        ActivityLogger::log(
            'document_ai_generated',
            "Запущена генерация документа через ИИ: тип={$validated['type']}, получатель={$validated['recipient']}",
            auth()->id()
        );

        return response()->json([
            'message'     => 'Документ генерируется с помощью ИИ',
            'document_id' => $document->id,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if ((int)$document->receiver_id !== (int)$user->id && !$user->isAdmin()) {
            return back()->with('error', 'У вас нет прав на отклонение этого документа');
        }

        if ($document->status === 'rejected') {
            return back()->with('error', 'Документ уже отклонён');
        }

        if (in_array($document->status, ['completed', 'approved'])) {
            return back()->with('error', 'Нельзя отклонить уже подписанный документ');
        }

        $request->validate([
            'reject_reason' => 'required|string|min:5|max:1000'
        ], [
            'reject_reason.required' => 'Необходимо указать причину отказа',
            'reject_reason.min' => 'Причина должна содержать минимум 5 символов',
            'reject_reason.max' => 'Причина не должна превышать 1000 символов'
        ]);

        $document->update(['status' => 'rejected']);

        // 📝 ИСТОРИЯ: Отклонение документа
        DocumentLog::create([
            'document_id' => $document->id,
            'user_id'     => $user->id,
            'action'      => 'отклонение',
            'description' => "Документ отклонён пользователем {$user->name}. Причина: " . $request->input('reject_reason')
        ]);

        DocumentComment::create([
            'document_id' => $document->id,
            'user_id'     => $user->id,
            'comment'     => '❌ ОТКАЗ: ' . $request->input('reject_reason')
        ]);

        if ($document->created_by) {
            Notification::create([
                'user_id'         => $document->created_by,
                'type'            => 'rejected',
                'messages'        => 'Ваш документ отклонён: ' . $document->title,
                'notifiable_type' => User::class,
                'notifiable_id'   => $document->created_by,
                'is_read'         => false,
                'data'            => [
                    'document_id'    => $document->id,
                    'type'           => 'rejected',
                    'user_name'      => $user->name,
                    'user_email'     => $user->email,
                    'document_title' => $document->title,
                    'message'        => 'Получатель отклонил документ: ' . $document->title . '. Причина: ' . $request->input('reject_reason'),
                ],
            ]);
        }

        ActivityLogger::log(
            'document_rejected',
            "Отклонён документ «{$document->title}». Причина: " . $request->input('reject_reason'),
            $user->id
        );

        return redirect()->route('documents.show', $id)->with('success', 'Документ успешно отклонён');
    }

    // 🔒 БЕЗОПАСНОСТЬ: Метод проверки прав доступа к документу
    private function checkDocumentAccess($document)
    {
        $user = Auth::user();

        // Админы имеют доступ ко всему
        if ($user->isAdmin()) {
            return;
        }

        // Проверяем, что пользователь является создателем, отправителем или получателем
        $hasAccess = (
            $document->created_by == $user->id ||
            $document->sender_id == $user->id ||
            $document->receiver_id == $user->id
        );

        if (!$hasAccess) {
            abort(403, 'У вас нет прав доступа к этому документу');
        }
    }
    public function previewPdf($id)
    {
        $document = Document::findOrFail($id);
        $this->checkDocumentAccess($document);

        if (empty($document->file_path)) {
            return back()->with('error', 'Путь к файлу не указан в базе данных.');
        }

        $relativePath = $document->file_path;

        if (!Storage::disk('public')->exists($relativePath)) {
            $expectedPath = storage_path('app/public/' . $relativePath);
            return back()->with('error',
                'Файл не найден. Ожидаемый путь: ' . $expectedPath
            );
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        // Определяем Content-Type по расширению
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Для PDF и изображений открываем в браузере (inline)
        // Для Word/Excel — предлагаем скачать (attachment)
        $disposition = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])
            ? 'inline'
            : 'attachment';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $document->title . '.' . $extension . '"',
        ]);

    }
    public function   debugFilePath($id)
    {
        $document = Document::findOrFail($id);

        $allPaths = [
            'DB file_path' => $document->file_path,
            'storage/app/public/documents' => storage_path('app/public/documents'),
            'public/storage/documents' => public_path('storage/documents'),
            'storage/documents' => base_path('storage/documents'),
            'public/documents' => public_path('documents'),
        ];

        $files = [];

        foreach ($allPaths as $label => $path) {
            if (is_dir($path)) {
                $dirFiles = scandir($path);
                $files[$label] = $dirFiles;
            }
        }

        return response()->json([
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'file_path' => $document->file_path,
            ],
            'directories' => $allPaths,
            'files_found' => $files,
        ]);
    }
    public function sendDocumentForm(Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        // Сотрудники этого отдела (кому можно отправить)
        $members = $department->users()->orderBy('name')->get();

        // Документы компании (что можно отправить)
        $documents = Document::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('departments.send-document', compact('department', 'members', 'documents'));
    }

// =========================================================
//  ОТПРАВКА ДОКУМЕНТА
// =========================================================
    public function sendDocument(Request $request, Department $department)
    {
        $user = auth()->user();
        $this->authorizeCompany($user, $department);

        $validated = $request->validate([
            'document_id'  => 'required|exists:documents,id',
            'recipient_id' => 'required|exists:users,id',
            'message'      => 'nullable|string|max:1000',
        ]);

        // Получатель должен быть из ЭТОГО отдела
        $recipient = $department->users()->where('users.id', $validated['recipient_id'])->first();

        if (!$recipient) {
            return back()->withErrors(['recipient_id' => 'Получатель не принадлежит этому отделу.'])->withInput();
        }

        // Документ должен быть из компании админа
        $document = Document::where('id', $validated['document_id'])
            ->where('company_id', $user->company_id)
            ->first();

        if (!$document) {
            return back()->withErrors(['document_id' => 'Документ не найден или недоступен.'])->withInput();
        }

        DocumentSend::create([
            'document_id'   => $document->id,
            'department_id' => $department->id,
            'sender_id'     => $user->id,
            'recipient_id'  => $recipient->id,
            'message'       => $validated['message'] ?? null,
            'status'        => 'sent',
        ]);

        return redirect()
            ->route('departments.show', $department)
            ->with('success', "Документ «{$document->name}» отправлен сотруднику {$recipient->name}.");
    }
        /**
     * Показать страницу выбора компании и сотрудников из дерева
     */
    public function selectByCompany(Request $request)
    {
        $user = auth()->user();

        // 1. Получаем компании, которые пользователь имеет право видеть
        // Если у пользователя есть метод managedCompanies (из CompanyTreeController), используем его.
        // Иначе (для супер-админа) берем все компании.
        if (method_exists($user, 'managedCompanies')) {
            $companies = $user->managedCompanies();
            // Связи region, city, users уже подгружены в методе managedCompanies()
        } else {
            $companies = \App\Models\Company::with(['region', 'city', 'users'])->get();
        }
        // 2. Строим вложенное дерево (рекурсивно, поддерживает любую глубину)
        $nestedTree = $this->buildNestedTree($companies);

        // 3. Формируем плоский массив для JavaScript (для модального окна выбора)
        $companiesData = $companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'users' => $company->users->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email ?? 'Нет email',
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        // URL, куда вернуться после выбора (по умолчанию - создание документа)
        $returnUrl = $request->get('return_url', route('documents.create'));

        // ВАЖНО: Убедись, что файл лежит в resources/views/document/select_by_company.blade.php
        return view('document.select_by_company', compact('nestedTree', 'companiesData', 'returnUrl'));
    }

    /**
     * Сохранить выбор компании и сотрудников в сессию
     */
    public function storeCompanySelection(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'user_ids'   => 'required|string',
            'return_url' => 'nullable|url',
        ]);

        // Преобразуем строку "1,2,3" в массив [1, 2, 3]
        $userIds = array_map('intval', array_filter(explode(',', $request->user_ids)));

        if (empty($userIds)) {
            return back()->withErrors(['user_ids' => 'Выберите хотя бы одного сотрудника']);
        }

        // Сохраняем в сессию. Метод store() в этом же контроллере уже умеет их читать!
        session([
            'selected_company_id' => $request->company_id,
            'selected_company_users' => $userIds
        ]);

        $returnUrl = $request->input('return_url', route('documents.create'));

        return redirect($returnUrl)->with('success', 'Сотрудники из компании успешно добавлены как получатели');
    }
 private function buildNestedTree($companies, $parentId = null)
    {
        return $companies->where('parent_id', $parentId)
            ->sortBy('name') // Сортируем по имени для красивого отображения
            ->values()
            ->map(function ($company) use ($companies) {
                // Рекурсивный вызов для дочерних элементов
                $company->nestedChildren = $this->buildNestedTree($companies, $company->id);
                return $company;
            });
    }
   
}