<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{
    /**
     * Индекс — список всех созданных документов пользователя
     */
    public function index()
    {
        $contracts = \App\Models\Contract::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('contracts.index', ['contracts' => $contracts]);
    }

  public function create()
{
    // Та же логика — только свои документы
    $contracts = \App\Models\Contract::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    return view('contracts.create', ['contracts' => $contracts]);
}

    /**
     * Генерация (обычная форма, fallback)
     */
public function generate(Request $request)
{
    set_time_limit(300);
    $validated = $this->validateForm($request);
    $validated = $this->attachFile($request, $validated);

    $result = $this->runPythonGenerator($validated);

    if (!$result['success']) {
        return back()->with('error', $result['error']);
    }

    $contract = $this->saveContract($result);

    $format = $request->input('output_format', 'word');

    // Если выбран PDF и Python не создал — создаём через dompdf
    if ($format === 'pdf' && empty($contract->pdf_filepath)) {
        $this->ensurePdf($contract);
    }

    // ✅ ВСЕГДА идём на preview (и для PDF, и для Word)
    return redirect()->route('contracts.preview', ['id' => $contract->id])
        ->with('success', 'Документ создан!');
}

public function generateAjax(Request $request)
{
    set_time_limit(300);

    try {
        $validated = $this->validateForm($request);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'error'   => 'Заполните обязательные поля',
            'errors'  => $e->errors(),
        ], 422);
    }

    try {
        $validated = $this->attachFile($request, $validated);
        $result = $this->runPythonGenerator($validated);

        if (!$result['success']) {
            throw new \Exception('Python: ' . $result['error']);
        }

        $contract = $this->saveContract($result);

        $format = $request->input('output_format', 'word');

        if ($format === 'pdf' && empty($contract->pdf_filepath)) {
            $this->ensurePdf($contract);
            $contract->refresh();
        }

        // ✅ ВСЕГДА редирект на preview
        return response()->json([
            'success'  => true,
            'redirect' => route('contracts.preview', ['id' => $contract->id]),
            'has_pdf'  => !empty($contract->pdf_filepath),
        ]);

    } catch (\Throwable $e) {
        Log::error('generateAjax crashed', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
        ]);
        return response()->json([
            'success' => false,
            'error'   => '❌ ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * 📎 Сохраняет прикреплённый файл и добавляет путь в данные для Python
     */
    private function attachFile(Request $request, array $validated): array
    {
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $dir = storage_path('app/temp_uploads');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $absPath = $dir . '/' . time() . '_' . $file->getClientOriginalName();
            $file->move($dir, basename($absPath));

            $validated['attachment_path'] = $absPath; // Python может использовать как образец
        }

        return $validated;
    }

    /**
     * 📕 Создаёт PDF, если Python его не создал
     */
    private function ensurePdf($contract): void
    {
        try {
            $dir = storage_path('app/contracts_pdf');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $absPath = $dir . '/contract_' . $contract->id . '.pdf';

            $html = '<html><head><meta charset="utf-8">
                <style>
                    body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.7; padding: 20px; color: #000; }
                </style>
                </head><body>' . nl2br(e($contract->text_content)) . '</body></html>';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            File::put($absPath, $pdf->output());

            $contract->update(['pdf_filepath' => $absPath]);
        } catch (\Throwable $e) {
            // Если dompdf не установлен — пишем в лог и работаем дальше (Word)
            Log::error('PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Общая валидация анкеты
     */
    private function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'doc_type'        => 'required|string',
            'doc_type_custom' => 'nullable|string',
            'to_whom'         => 'required|string',
            'from_whom'       => 'required|string',
            'from_details'    => 'nullable|string',
            'city'            => 'nullable|string',
            'subject'         => 'required|string|min:20',
            'extra'           => 'nullable|string',
            'answers'         => 'nullable|array',
            'answers.*'       => 'nullable|string',
        ], [
            'subject.min' => 'Опишите суть подробнее (минимум 20 символов)',
        ]);

        if ($validated['doc_type'] === 'Другой' && !empty($validated['doc_type_custom'])) {
            $validated['doc_type'] = $validated['doc_type_custom'];
        }

        $now = now();
        $validated['day']   = $now->format('d');
        $validated['month'] = $this->ruMonth((int)$now->format('n'));
        $validated['year']  = $now->format('Y');

        return $validated;
    }

    /**
     * Запуск Python с обработкой кодировки Windows
     */
    private function runPythonGenerator(array $validated): array
    {
        $jsonPath = base_path('python/temp_input.json');
        File::put($jsonPath, json_encode($validated, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $command = sprintf(
            'chcp 65001 >nul && set GROQ_API_KEY=%s && %s %s %s 2>&1',
            env('GROQ_API_KEY', ''),
            $this->quotePath(env('PYTHON_PATH', 'python')),
            $this->quotePath(base_path('python/generate_contract.py')),
            $this->quotePath($jsonPath)
        );

        Log::info('🐍 Command: ' . $command);

        $output = [];
        $returnVar = 0;
        @exec($command, $output, $returnVar);

        $lines = [];
        foreach ($output as $line) {
            $line = trim($this->toUtf8($line));
            if ($line !== '') $lines[] = $line;
        }

        Log::info('🐍 Output: ' . implode(' | ', $lines));

        if (empty($lines)) {
            return ['success' => false, 'error' => 'Python ничего не вернул. Проверьте PYTHON_PATH в .env'];
        }

        $jsonLine = $this->findJsonLine($lines);
        if (!$jsonLine) {
            return ['success' => false, 'error' => 'Python не вернул JSON: ' . end($lines)];
        }

        $result = json_decode($jsonLine, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result = json_decode($this->cleanInvalidUtf8($jsonLine), true);
        }
        if (!$result) {
            return ['success' => false, 'error' => 'Ошибка парсинга: ' . json_last_error_msg()];
        }
        if (empty($result['success'])) {
            return ['success' => false, 'error' => $result['error'] ?? 'Ошибка Python'];
        }

        return $result;
    }

    private function saveContract(array $result)
    {
        return \App\Models\Contract::create([
            'user_id'       => auth()->id(),
            'filename'      => $result['filename'],
            'filepath'      => $result['filepath'],
            'pdf_filepath'  => $result['pdf_filepath'] ?? null,
            'detected_type' => $result['detected_type'] ?? null,
            'text_content'  => $result['text'],
        ]);
    }

    public function preview($id)
    {
        $contract = \App\Models\Contract::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('contracts.preview', [
            'text'     => $contract->text_content,
            'contract' => $contract,
        ]);
    }

    public function download($id)
    {
        $contract = \App\Models\Contract::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!File::exists($contract->filepath)) {
            return redirect()->route('contracts.create')->with('error', 'Файл не найден');
        }

        return response()->download($contract->filepath, $contract->filename);
    }

    public function downloadPdf($id)
    {
        $contract = \App\Models\Contract::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!$contract->pdf_filepath || !File::exists($contract->pdf_filepath)) {
            return redirect()->route('contracts.preview', ['id' => $id])->with('error', 'PDF не создан');
        }

        return response()->download($contract->pdf_filepath);
    }

 private function toUtf8(string $str): string
{
    if (mb_check_encoding($str, 'UTF-8') && !preg_match('/[\x80-\xFF]/', $str)) return $str;
    
    // ✅ ИСПРАВЛЕНО: ISO-8859-5 (было ISO-8851-5)
    $utf8 = @mb_convert_encoding($str, 'UTF-8', 'UTF-8,Windows-1251,CP866,ISO-8859-5');
    if ($utf8 !== false && mb_check_encoding($utf8, 'UTF-8')) return $utf8;
    
    $cp = @iconv('CP866', 'UTF-8//IGNORE', $str);
    if ($cp !== false) return $cp;
    $win = @iconv('Windows-1251', 'UTF-8//IGNORE', $str);
    if ($win !== false) return $win;
    return preg_replace('/[^\x20-\x7E]/', '', $str);
}

    private function findJsonLine(array $lines): ?string
    {
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if ($line === '' || ($line[0] !== '{' && $line[0] !== '[')) continue;
            $test = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($test)) return $line;
            $clean = $this->cleanInvalidUtf8($line);
            $test = json_decode($clean, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($test)) return $clean;
        }
        return null;
    }

    private function cleanInvalidUtf8(string $str): string
    {
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
        return @iconv('UTF-8', 'UTF-8//IGNORE', $str) ?: '';
    }

    private function quotePath(string $path): string
    {
        return '"' . str_replace('/', '\\', $path) . '"';
    }

    private function ruMonth(int $num): string
    {
        $m = [1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',
              7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'];
        return $m[$num] ?? '';
    }

    /**
     * ШАГ 1: ИИ анализирует анкету и задаёт вопросы
     */
    public function analyzeAjax(Request $request)
    {
        set_time_limit(300);
        $data = $request->only(['from_details', 'doc_type', 'subject', 'from_whom', 'to_whom', 'city']);
        $data['mode'] = 'analyze';
        $result = $this->runPythonGenerator($data);
        return response()->json($result);
    }

    public function chat()
    {
        $dir = base_path('python/my_documents');
        $files = is_dir($dir)
            ? array_values(array_diff(scandir($dir), ['.', '..']))
            : [];
        return view('contracts.chat', ['files' => $files]);
    }

    public function chatAsk(Request $request)
    {
        set_time_limit(300);
        $request->validate([
            'filename' => 'required|string',
            'question' => 'required|string|min:5',
        ]);
        $data = [
            'mode'     => 'chat',
            'filename' => $request->filename,
            'question' => $request->question,
        ];
        $result = $this->runPythonGenerator($data);
        return response()->json($result);
    }

    public function analyzePage()
    {
        return view('contracts.analyze');
    }

    public function analyzeUpload(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'doc' => 'required|file|mimes:docx,pdf,doc,txt|max:15360',
        ], [
            'doc.required' => 'Выберите файл',
            'doc.mimes'    => 'Только Word или PDF',
        ]);

        $file = $request->file('doc');

        $uploadDir = storage_path('app/temp_uploads');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $savedName = time() . '_' . $file->getClientOriginalName();
        $absPath = $uploadDir . '/' . $savedName;

        $file->move($uploadDir, $savedName);

        $result = $this->runPythonGenerator([
            'mode'     => 'summarize',
            'filepath' => $absPath,
        ]);

        if (File::exists($absPath)) {
            File::delete($absPath);
        }

        return response()->json($result);
    }
}