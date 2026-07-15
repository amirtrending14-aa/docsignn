<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDocumentJob;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentLog;
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

    public function getStats()
    {
        // 🔒 БЕЗОПАСНОСТЬ: Считаем статистику только по документам текущего юзера
        $user = Auth::user();
        $userFilter = function($q) use ($user) {
            $q->where('created_by', $user->id)->orWhere('receiver_id', $user->id);
        };

        $totalDocs = Document::where($userFilter)->count();
        $previousDocsCount = Document::where($userFilter)->where('created_at', '<', now()->startOfMonth())->count();

        $docsGrowth = $previousDocsCount > 0
            ? round((($totalDocs - $previousDocsCount) / $previousDocsCount) * 100, 1)
            : ($totalDocs > 0 ? 100 : 0);

        return view('dashboard', compact('totalDocs', 'docsGrowth'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $userFilter = function($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('receiver_id', $user->id);
        };

        // 🔒 БЕЗОПАСНОСТЬ: Считаем юзеров только из своей компании
        $companyFilter = $user->company_id ? ['company_id' => $user->company_id] : [];

        $stats = [
            'total'     => Document::where($userFilter)->count(),
            'active'    => Document::where($userFilter)->where('status', 'active')->count(),
            'draft'     => Document::where($userFilter)->where('status', 'draft')->count(),
            'pending'   => Document::where($userFilter)->where('status', 'pending')->count(),
            'signed'    => Document::where($userFilter)->where('status', 'completed')->count(),
            'users'     => \App\Models\User::where($companyFilter)->count(),
            'new_users' => \App\Models\User::where($companyFilter)->whereMonth('created_at', now()->month)->count(),
            'pending_change' => 3,
        ];

        $query = Document::with(['createdBy', 'receiver', 'signatures']);

        $query->where(function($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('receiver_id', $user->id);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->type === 'incoming') {
            $query->where('receiver_id', $user->id);
        } elseif ($request->type === 'outgoing') {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'waiting') {
                $query->where('status', 'active')->whereDoesntHave('signatures', function ($sq) {
                    $sq->whereNotNull('signature')->where('signature', '!=', '');
                });
            } elseif ($status === 'signed') {
                $query->where('status', 'completed');
            } else {
                $query->where('status', $status);
            }
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        $docsGrowth = 12.5;
        $signedGrowth = 24.1;
        $usersCount = $stats['users'];

        return view('document.index', compact('documents', 'stats', 'docsGrowth', 'signedGrowth', 'usersCount'));
    }

    public function create()
    {
        $authUser = auth()->user();

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
                'phone' => $u->phone ?? null, // <-- ДОБАВЛЕНО: для поиска по телефону на фронтенде
            ];
        })->values()->toArray();

        $otherUsersArray = $otherUsers->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'company' => $u->company,
                'phone' => $u->phone ?? null, // <-- ДОБАВЛЕНО: для поиска по телефону на фронтенде
            ];
        })->values()->toArray();

        return view('document.create', compact('teamUsers', 'otherUsers', 'teamUsersArray', 'otherUsersArray'));
    }

    public function store(Request $request)
    {
        // 🛡️ ЛИМИТ: Создание/Загрузка документа (10 раз, 20 раз -> 1 час, 30 раз -> 6 часов)
        $check = RateLimitService::check('doc_store:' . Auth::id(), 10, [20 => 60, 30 => 360]);
        if ($check['blocked']) {
            return back()->withErrors(['file_path' => $check['message']]);
        }

        $data = $request->validate([
            'number' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'required|in:draft,active',
            'file_path' => 'required|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/rtf|max:51200',
            'receiver_mode' => 'nullable|in:all_team,select_team,other_company',
            'team_receivers' => 'nullable|string',
            'other_receiver_id' => 'nullable|integer|exists:users,id',
        ], [
            'file_path.required' => 'Необходимо прикрепить файл',
            'file_path.file' => 'Загруженный элемент не является файлом',
            'file_path.mimetypes' => 'Недопустимый формат файла. Разрешены: PDF, DOC, DOCX, XLS, XLSX, RTF',
            'file_path.max' => 'Размер файла не должен превышать 50 МБ',
            'number.required' => 'Номер документа обязателен',
            'type.required' => 'Тип документа обязателен',
            'title.required' => 'Заголовок обязателен',
            'status.required' => 'Статус обязателен',
        ]);

        $authUser = auth()->user();
        $filePath = $request->file('file_path')->store('documents', 'public');

        // ═══════════════════════════════════════════════════════════
        // ЕСЛИ ЧЕРНОВИК — сохраняем ТОЛЬКО для себя
        // ═══════════════════════════════════════════════════════════
        if ($data['status'] === 'draft') {
            $document = Document::create([
                'number' => $data['number'],
                'type' => $data['type'],
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'draft',
                'file_path' => $filePath,
                'sender_id' => $authUser->id,
                'receiver_id' => null,
                'created_by' => $authUser->id,
            ]);

            // 📝 ИСТОРИЯ: Создание черновика
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => $authUser->id,
                'action' => 'создание',
                'description' => "Создан черновик документа «{$data['title']}» (№{$data['number']})"
            ]);

            ActivityLogger::log(
                'document_created',
                "Создан черновик документа «{$data['title']}» (№{$data['number']}) — сохранён для себя",
                $authUser->id
            );

            return redirect()->route('documents.index')
                ->with('success', 'Черновик успешно сохранён');
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
        } elseif ($data['receiver_mode'] === 'select_team') {
            if (empty($data['team_receivers'])) {
                return back()->withErrors(['team_receivers' => 'Выберите хотя бы одного получателя']);
            }
            $receiverIds = array_map('intval', explode(',', $data['team_receivers']));
            $receivers = User::where('company_id', $authUser->company_id)
                ->whereIn('id', $receiverIds)
                ->pluck('id')
                ->toArray();
        } elseif ($data['receiver_mode'] === 'other_company') {
            if (empty($data['other_receiver_id'])) {
                return back()->withErrors(['other_receiver_id' => 'Выберите получателя']);
            }
            $receivers = [$data['other_receiver_id']];
        }

        if (empty($receivers)) {
            return back()->withErrors(['receiver_mode' => 'Не удалось определить получателей']);
        }

        // Создаём документы для каждого получателя
        foreach ($receivers as $receiverId) {
            $receiver = User::find($receiverId);

            $document = Document::create([
                'number' => $data['number'],
                'type' => $data['type'],
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'active',
                'file_path' => $filePath,
                'sender_id' => $authUser->id,
                'receiver_id' => $receiverId,
                'created_by' => $authUser->id,
            ]);

            // 📝 ИСТОРИЯ: Создание и отправка документа
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id' => $authUser->id,
                'action' => 'создание',
                'description' => "Создан документ «{$data['title']}» (№{$data['number']}) и отправлен пользователю: {$receiver->name}"
            ]);

            DocumentSignature::updateOrCreate(
                ['document_id' => $document->id, 'user_id' => $receiverId],
                ['signature' => '']
            );

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

        ActivityLogger::log(
            'document_created',
            "Создан и отправлен документ «{$data['title']}» (№{$data['number']}) — отправлен " . count($receivers) . " получателю(ям)",
            $authUser->id
        );

        return redirect()->route('documents.index')
            ->with('success', 'Документ успешно отправлен ' . count($receivers) . ' получателю(ям)');
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
                'phone' => $u->phone ?? null, // <-- ДОБАВЛЕНО: для поиска по телефону на фронтенде
            ];
        })->values()->toArray();

        $otherUsersArray = $otherUsers->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'company' => $u->company,
                'phone' => $u->phone ?? null, // <-- ДОБАВЛЕНО: для поиска по телефону на фронтенде
            ];
        })->values()->toArray();

        $currentReceiver = $document->receiver_id ? User::find($document->receiver_id) : null;

        return view('document.edit', compact(
            'document',
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
            'number'   => 'nullable|string|max:100',
            'type'     => 'required|string|max:255',
            'title'    => 'required|string|max:255',
            'content'  => 'nullable|string',
            'deadline' => 'nullable|date',
            'status'   => 'required|in:draft,active,completed',
            'file_path' => 'nullable|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/rtf|max:51200',
            'receiver_mode' => 'nullable|in:all_team,select_team,other_company',
            'team_receivers' => 'nullable|string',
            'other_receiver_id' => 'nullable|integer|exists:users,id',
        ]);

        $authUser = auth()->user();
        $oldStatus = $document->status;
        $newStatus = $request->input('status');

        $data = $request->only(['number', 'type', 'title', 'content', 'status', 'deadline']);

        if ($request->hasFile('file_path')) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $data['file_path'] = $request->file('file_path')->store('documents', 'public');
        }

        // Если статус меняется с draft на active — нужен получатель
        if ($oldStatus === 'draft' && $newStatus === 'active') {
            if (empty($request->receiver_mode)) {
                return back()->withErrors(['receiver_mode' => 'Выберите получателя для отправки документа']);
            }

            $newReceiverId = null;

            if ($request->receiver_mode === 'all_team') {
                $firstReceiver = User::where('company_id', $authUser->company_id)
                    ->where('id', '!=', $authUser->id)
                    ->first();
                $newReceiverId = $firstReceiver ? $firstReceiver->id : null;
            } elseif ($request->receiver_mode === 'select_team') {
                if ($request->team_receivers) {
                    $receiverIds = array_map('intval', explode(',', $request->team_receivers));
                    $newReceiverId = $receiverIds[0] ?? null;
                }
            } elseif ($request->receiver_mode === 'other_company') {
                $newReceiverId = $request->other_receiver_id;
            }

            if ($newReceiverId) {
                $data['receiver_id'] = $newReceiverId;
            } else {
                return back()->withErrors(['receiver_mode' => 'Не удалось определить получателя']);
            }
        }

        // Если остаётся черновиком — получатель не нужен
        if ($newStatus === 'draft') {
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

        // Если был черновик и стал активным — создаём подпись и уведомление
        if ($oldStatus === 'draft' && $newStatus === 'active' && $document->receiver_id) {
            DocumentSignature::updateOrCreate(
                ['document_id' => $document->id, 'user_id' => $document->receiver_id],
                ['signature' => '']
            );

            $receiver = User::find($document->receiver_id);

            Notification::create([
                'user_id'         => $document->receiver_id,
                'type'            => 'assigned',
                'messages'        => 'Документ отправлен на подпись: ' . $document->title,
                'notifiable_type' => User::class,
                'notifiable_id'   => $document->receiver_id,
                'is_read'         => false,
                'data'            => [
                    'document_id'    => $document->id,
                    'type'           => 'assigned',
                    'user_name'      => auth()->user()->name,
                    'user_email'     => auth()->user()->email,
                    'document_title' => $document->title,
                    'message'        => 'Документ отправлен на подпись: ' . $document->title,
                ],
            ]);

            // 📝 ИСТОРИЯ: Отправка черновика на подпись
            DocumentLog::create([
                'document_id' => $document->id,
                'user_id'     => Auth::id(),
                'action'      => 'отправка',
                'description' => "Черновик «{$document->title}» отправлен на подпись пользователю: {$receiver->name}"
            ]);

            ActivityLogger::log(
                'document_sent',
                "Черновик «{$document->title}» отправлен на подпись",
                Auth::id()
            );
        }

        return redirect()->route('documents.index')->with('success', 'Документ успешно обновлен!');
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
}