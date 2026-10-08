<?php

use App\Models\User;
use App\Services\ArchivistService;
use App\Services\DocumentIndexer;
use App\Services\GroqClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);
    DB::purge('sqlite');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->boolean('is_admin')->default(false);
        $table->unsignedInteger('level')->default(2);
        $table->softDeletes();
    });

    Schema::create('documents', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('number')->nullable();
        $table->string('type');
        $table->string('file_path')->nullable();
        $table->string('status')->default('active');
        $table->unsignedBigInteger('created_by');
        $table->unsignedBigInteger('receiver_id')->nullable();
        $table->timestamp('deadline')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('document_indexes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('document_id');
        $table->text('extracted_text')->nullable();
        $table->text('ai_summary')->nullable();
        $table->text('people')->nullable();
        $table->timestamp('indexed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('document_chunks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('document_id');
        $table->unsignedInteger('chunk_index')->default(0);
        $table->text('content');
        $table->timestamps();
    });

    Schema::create('document_signatures', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('document_id');
        $table->unsignedBigInteger('user_id');
        $table->text('signature')->nullable();
        $table->timestamp('signed_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });

    DB::table('users')->insert([
        ['id' => 1, 'name' => 'Current User', 'email' => 'current@example.test', 'is_admin' => false, 'level' => 2],
        ['id' => 2, 'name' => 'Other User', 'email' => 'other@example.test', 'is_admin' => false, 'level' => 2],
    ]);
    Auth::setUser(User::findOrFail(1));
});

afterEach(function () {
    Auth::logout();
    DB::purge('sqlite');
});

test('searches indexed file contents and excludes documents the user cannot see', function () {
    foreach ([1 => 'Мой документ', 2 => 'Чужой документ'] as $ownerId => $title) {
        $documentId = DB::table('documents')->insertGetId([
            'title' => $title,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/contract.pdf',
            'status' => 'active',
            'created_by' => $ownerId,
            'receiver_id' => null,
            'deadline' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('document_indexes')->insert([
            'document_id' => $documentId,
            'extracted_text' => 'В договоре описан порядок расторжения соглашения.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('document_chunks')->insert([
            'document_id' => $documentId,
            'chunk_index' => 0,
            'content' => 'Порядок расторжения соглашения.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andReturn(['keywords' => ['расторжении']]);
    $groq->shouldReceive('json')->once()->andReturn(['answer' => 'Нашёл документ с нужным условием.']);

    $result = (new ArchivistService($groq))->ask('Найди договоры, где говорится о расторжении', 1);

    expect($result['meta']['found_count'])->toBe(1)
        ->and($result['documents'])->toHaveCount(1)
        ->and($result['documents'][0]['title'])->toBe('Мой документ')
        ->and($result['documents'][0]['quote'])->toContain('расторжения');
});

test('finds only documents with a stored QR signature for QR-code questions', function () {
    $signedDocumentId = DB::table('documents')->insertGetId([
        'title' => 'Подписанный документ с QR',
        'number' => null,
        'type' => 'справка',
        'file_path' => 'documents/signed.pdf',
        'status' => 'completed',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $unsignedDocumentId = DB::table('documents')->insertGetId([
        'title' => 'Документ без подписи',
        'number' => null,
        'type' => 'справка',
        'file_path' => 'documents/unsigned.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('document_signatures')->insert([
        'document_id' => $signedDocumentId,
        'user_id' => 1,
        'signature' => 'signatures/qr_signed.png',
        'signed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask('Найди документ, внутри которого есть QR-код', 1);

    expect($result['meta']['found_count'])->toBe(1)
        ->and($result['documents'])->toHaveCount(1)
        ->and($result['documents'][0]['id'])->toBe($signedDocumentId)
        ->and($result['documents'][0]['id'])->not->toBe($unsignedDocumentId)
        ->and($result['answer'])->toContain('с QR-кодом');
});

test('does not treat an amount as a date and matches grouped numbers', function () {
    $documentId = DB::table('documents')->insertGetId([
        'title' => 'Счёт за услуги',
        'number' => null,
        'type' => 'счёт',
        'file_path' => 'documents/invoice.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('document_indexes')->insert([
        'document_id' => $documentId,
        'extracted_text' => 'Итого к оплате: 50 000 сомони.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('document_chunks')->insert([
        'document_id' => $documentId,
        'chunk_index' => 0,
        'content' => 'Итого к оплате: 50 000 сомони.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andReturn([
        'date_from' => '1950-05-01',
        'date_to' => '1950-05-31',
        'keywords' => ['50 000'],
    ]);
    $groq->shouldReceive('json')->once()->andReturn(['answer' => 'Сумма указана в документе.']);

    $result = (new ArchivistService($groq))->ask('В каких документах указана сумма 50 000', 1);

    expect($result['meta']['found_count'])->toBe(1)
        ->and($result['documents'])->toHaveCount(1)
        ->and($result['documents'][0]['id'])->toBe($documentId)
        ->and($result['answer'])->not->toContain('1950')
        ->and($result['documents'][0]['quote'])->toContain('50 000');
});

test('searches the full date range in a natural-language question', function () {
    foreach (['2026-09-27 10:00:00', '2026-09-29 10:00:00', '2026-10-01 10:00:00', '2026-10-02 10:00:00'] as $createdAt) {
        DB::table('documents')->insert([
            'title' => 'Range test '.$createdAt,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/range.pdf',
            'status' => 'active',
            'created_by' => 1,
            'receiver_id' => null,
            'deadline' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask(
        'найди мне документы, созданные от 27.09.26 до 01.10.26',
        1
    );

    expect($result['meta']['found_count'])->toBe(3)
        ->and($result['documents'])->toHaveCount(3)
        ->and($result['answer'])->toContain('27.09.2026')
        ->and($result['answer'])->toContain('01.10.2026')
        ->and($result['answer'])->toContain('по дате создания');
});

test('unqualified date ranges require both creation date and deadline to be in range', function () {
    $expectedIds = [];
    foreach ([
        ['2026-09-27 10:00:00', '2026-09-27 12:00:00', true],
        ['2026-09-29 10:00:00', '2026-09-30 12:00:00', true],
        ['2026-10-01 10:00:00', '2026-10-01 12:00:00', true],
        ['2026-09-29 10:00:00', '2026-09-25 12:00:00', false],
        ['2026-09-25 10:00:00', '2026-09-30 12:00:00', false],
        ['2026-10-02 10:00:00', '2026-10-01 12:00:00', false],
    ] as [$createdAt, $deadline, $shouldMatch]) {
        $id = DB::table('documents')->insertGetId([
            'title' => 'Both date range test '.$createdAt.' '.$deadline,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/both-dates.pdf',
            'status' => 'active',
            'created_by' => 1,
            'receiver_id' => null,
            'deadline' => $deadline,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        if ($shouldMatch) $expectedIds[] = $id;
    }

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask(
        'Найди документы от 01.10.26 до 27.09.26',
        1
    );

    expect($result['meta']['found_count'])->toBe(3)
        ->and(collect($result['documents'])->pluck('id')->all())->toBe($expectedIds)
        ->and($result['answer'])->toContain('по дате создания и сроку')
        ->and($result['answer'])->toContain('с 27.09.2026 по 01.10.2026');
});

test('date range mentioning creation and deadline applies both fields', function () {
    $matches = DB::table('documents')->insertGetId([
        'title' => 'Both explicit dates in range',
        'number' => null,
        'type' => 'договор',
        'file_path' => 'documents/both-explicit.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => '2026-09-30 12:00:00',
        'created_at' => '2026-09-29 10:00:00',
        'updated_at' => '2026-09-29 10:00:00',
    ]);
    DB::table('documents')->insert([
        'title' => 'Creation date outside range',
        'number' => null,
        'type' => 'договор',
        'file_path' => 'documents/outside.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => '2026-09-30 12:00:00',
        'created_at' => '2026-09-25 10:00:00',
        'updated_at' => '2026-09-25 10:00:00',
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask(
        'Найди документы по дате создания и сроку от 27.09.26 до 01.10.26',
        1
    );

    expect($result['meta']['found_count'])->toBe(1)
        ->and($result['documents'][0]['id'])->toBe($matches)
        ->and($result['answer'])->toContain('по дате создания и сроку');
});

test('searches the deadline field for a date range when asked about the deadline', function () {
    foreach (['2026-09-27', '2026-10-01', '2026-10-02'] as $deadline) {
        DB::table('documents')->insert([
            'title' => 'Deadline range test '.$deadline,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/deadline.pdf',
            'status' => 'active',
            'created_by' => 1,
            'receiver_id' => null,
            'deadline' => $deadline.' 12:00:00',
            'created_at' => '2026-08-01 10:00:00',
            'updated_at' => '2026-08-01 10:00:00',
        ]);
    }

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask(
        'Найди документы со сроком от 27.09.26 до 01.10.26',
        1
    );

    expect($result['meta']['found_count'])->toBe(2)
        ->and($result['documents'])->toHaveCount(2)
        ->and($result['answer'])->toContain('по сроку')
        ->and($result['answer'])->toContain('с 27.09.2026 по 01.10.2026');
});

test('filter-only questions do not gain accidental content keywords', function () {
    $documentIds = [];
    foreach ([1 => 1, 2 => 1] as $ownerId => $receiverId) {
        $documentIds[] = DB::table('documents')->insertGetId([
            'title' => 'Договор PDF '.$ownerId,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/contract.pdf',
            'status' => 'active',
            'created_by' => $ownerId,
            'receiver_id' => $receiverId,
            'deadline' => now()->startOfDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('document_signatures')->insert([
        'document_id' => $documentIds[1],
        'user_id' => 1,
        'signature' => 'signed.png',
        'signed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $countResult = (new ArchivistService($groq))->ask('Сколько у меня документов в формате PDF?', 1);

    expect($countResult['meta']['found_count'])->toBe(2)
        ->and($countResult['answer'])->toContain('2 документа')
        ->and($countResult['answer'])->not->toContain('май');

    $signGroq = Mockery::mock(GroqClient::class);
    $signGroq->shouldNotReceive('json');

    $signResult = (new ArchivistService($signGroq))->ask('Какие документы я должен подписать сегодня?', 1);

    expect($signResult['meta']['found_count'])->toBe(1)
        ->and($signResult['documents'])->toHaveCount(1)
        ->and($signResult['documents'][0]['id'])->toBe($documentIds[0]);
});

test('extracts searchable text from Excel files', function () {
    $tempBase = tempnam(sys_get_temp_dir(), 'docsign-index-');
    expect($tempBase)->not->toBeFalse();
    unlink($tempBase);
    $path = $tempBase.'.xlsx';

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->setTitle('Счета');
    $spreadsheet->getActiveSheet()->fromArray([
        ['Номер', 'Сумма'],
        ['INV-42', 50000],
    ]);
    SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    $spreadsheet->disconnectWorksheets();

    try {
        $indexer = new DocumentIndexer(Mockery::mock(GroqClient::class));
        $text = $indexer->extractText($path);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }

    expect($text)->toContain('Счета')
        ->and($text)->toContain('INV-42')
        ->and($text)->toContain('50000');
});

test('extracts searchable text from text-based PDFs', function () {
    $tempBase = tempnam(sys_get_temp_dir(), 'docsign-pdf-');
    expect($tempBase)->not->toBeFalse();
    unlink($tempBase);
    $path = $tempBase.'.pdf';

    $pdf = new \TCPDF;
    $pdf->setCompression(false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, 'Searchable PDF agreement clause.');
    $pdf->Output($path, 'F');

    try {
        $text = (new DocumentIndexer(Mockery::mock(GroqClient::class)))->extractText($path);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }

    expect($text)->toContain('Searchable PDF agreement clause');
});

test('extracts searchable text from DOCX and RTF files', function () {
    $tempBase = tempnam(sys_get_temp_dir(), 'docsign-word-');
    expect($tempBase)->not->toBeFalse();
    unlink($tempBase);
    $docxPath = $tempBase.'.docx';
    $rtfPath = $tempBase.'.rtf';

    $word = new PhpWord;
    $word->addSection()->addText('Searchable agreement clause.');
    IOFactory::createWriter($word, 'Word2007')->save($docxPath);
    file_put_contents($rtfPath, '{\rtf1\ansi Searchable RTF clause.\par}');

    try {
        $indexer = new DocumentIndexer(Mockery::mock(GroqClient::class));
        $docxText = $indexer->extractText($docxPath);
        $rtfText = $indexer->extractText($rtfPath);
    } finally {
        if (is_file($docxPath)) {
            unlink($docxPath);
        }
        if (is_file($rtfPath)) {
            unlink($rtfPath);
        }
    }

    expect($docxText)->toContain('Searchable agreement clause')
        ->and($rtfText)->toContain('Searchable RTF clause');
});

test('searches documents with a saved signature as signed even when status is active', function () {
    $documentId = DB::table('documents')->insertGetId([
        'title' => 'Подписанный договор',
        'number' => null,
        'type' => 'договор',
        'file_path' => 'documents/signed.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('document_signatures')->insert([
        'document_id' => $documentId,
        'user_id' => 1,
        'signature' => 'signed.png',
        'signed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andReturn(['answer' => 'Найден подписанный договор.']);

    $result = (new ArchivistService($groq))->ask('Покажи подписанные документы', 1);

    expect($result['meta']['found_count'])->toBe(1)
        ->and($result['documents'])->toHaveCount(1)
        ->and($result['documents'][0]['signed'])->toBeTrue()
        ->and($result['documents'][0]['signature_state'])->toBe('signed');
});

test('does not let the generated answer reference a document missing from the result cards', function () {
    DB::table('documents')->insert([
        'title' => 'Документ для поиска',
        'number' => null,
        'type' => 'договор',
        'file_path' => 'documents/search.pdf',
        'status' => 'active',
        'created_by' => 1,
        'receiver_id' => null,
        'deadline' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andReturn(['answer' => 'В документе №999 есть нужные сведения.']);

    $result = (new ArchivistService($groq))->ask('Покажи договор', 1);

    expect($result['documents'])->toHaveCount(1)
        ->and($result['answer'])->not->toContain('999');
});

test('sends the model only the documents returned as visible cards', function () {
    for ($i = 1; $i <= 16; $i++) {
        DB::table('documents')->insert([
            'title' => 'Project match '.$i,
            'number' => null,
            'type' => 'договор',
            'file_path' => 'documents/project.pdf',
            'status' => 'active',
            'created_by' => 1,
            'receiver_id' => null,
            'deadline' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andReturn(['keywords' => ['project']]);
    $groq->shouldReceive('json')
        ->once()
        ->withArgs(fn ($system, $user) => str_contains($user, 'DOC #15') && ! str_contains($user, 'DOC #16'))
        ->andReturn(['answer' => 'Найдены документы по вашему запросу.']);

    $result = (new ArchivistService($groq))->ask('Найди файл project', 1);

    expect($result['meta']['found_count'])->toBe(16)
        ->and($result['documents'])->toHaveCount(15);
});

test('returns a safe error message without exposing exception details', function () {
    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldReceive('json')->once()->andThrow(new RuntimeException('private database path'));

    $result = (new ArchivistService($groq))->ask('Покажи документы по неизвестному слову', 1);

    expect($result['answer'])->toContain('Не удалось выполнить поиск')
        ->and($result['answer'])->not->toContain('private database path');
});

test('asks for a real value instead of guessing a bracketed example placeholder', function () {
    $groq = Mockery::mock(GroqClient::class);
    $groq->shouldNotReceive('json');

    $result = (new ArchivistService($groq))->ask(
        'Найди документ, где упоминается [имя или название компании из вашего архива]',
        1
    );

    expect($result['answer'])->toContain('Замените текст в квадратных скобках')
        ->and($result['documents'])->toBeEmpty();
});
