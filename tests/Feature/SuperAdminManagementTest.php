<?php

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentLog;
use App\Models\DocumentSignature;
use App\Models\DocumentVersion;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows real activity actions and preserves filters in the super-admin activity log', function () {
    $admin = User::factory()->create([
        'role' => 'super_admin',
        'is_admin' => true,
        'is_super_admin' => true,
    ]);
    $document = Document::create([
        'title' => 'Отчёт о поставке',
        'number' => 'ACT-42',
        'type' => 'Отчёт',
        'status' => Document::STATUS_DRAFT,
        'created_by' => $admin->id,
    ]);
    DocumentLog::create([
        'document_id' => $document->id,
        'user_id' => $admin->id,
        'action' => 'создание',
        'description' => 'Создан тестовый документ',
    ]);
    DocumentLog::create([
        'document_id' => $document->id,
        'user_id' => $admin->id,
        'action' => 'скачивание',
        'description' => 'Скачан файл',
    ]);

    $this->actingAs($admin)
        ->get(route('superadmin.activity'))
        ->assertOk()
        ->assertSee('admin-light select option')
        ->assertSee('color-scheme: light')
        ->assertSee('История действий')
        ->assertSee('Создание')
        ->assertSee('Отчёт о поставке')
        ->assertSee('скачивание');

    $this->get(route('superadmin.activity', [
        'action' => 'создание',
        'search' => 'ACT-42',
    ]))
        ->assertOk()
        ->assertSee('Создан тестовый документ')
        ->assertDontSee('Скачан файл');
});

it('keeps the super-admin dashboard and user and company management operational', function () {
    $admin = User::factory()->create([
        'role' => 'super_admin',
        'is_admin' => true,
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('superadmin.dashboard'))
        ->assertOk()
        ->assertSee('Быстрые действия');

    $this->get(route('superadmin.users.index'))->assertOk();
    $this->get(route('superadmin.users.create'))->assertOk();
    $this->get(route('superadmin.companies.index'))->assertOk();
    $this->get(route('superadmin.companies.create'))->assertOk();

    $this->put(route('superadmin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => 'employee',
        'level' => 1,
    ])->assertSessionHasErrors('role');

    $this->post(route('superadmin.companies.store'), [
        'name' => 'Первая тестовая компания',
        'email' => 'first-company@example.test',
    ])->assertRedirect(route('superadmin.companies.index'));

    $company = Company::where('email', 'first-company@example.test')->firstOrFail();

    $this->put(route('superadmin.companies.update', $company), [
        'name' => 'Обновлённая тестовая компания',
        'email' => 'first-company@example.test',
        'phone' => '+992900000000',
        'address' => 'Душанбе',
    ])->assertRedirect(route('superadmin.companies.index'));

    expect($company->fresh()->name)->toBe('Обновлённая тестовая компания');

    $emptyCompany = Company::create([
        'name' => 'Компания для удаления',
        'email' => 'delete-company@example.test',
    ]);

    $this->delete(route('superadmin.companies.destroy', $emptyCompany))
        ->assertRedirect()
        ->assertSessionHas('success');
    $this->assertDatabaseMissing('companies', ['id' => $emptyCompany->id]);
});

it('allows super admins to create, edit and safely delete documents', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'super_admin',
        'is_admin' => true,
        'is_super_admin' => true,
    ]);
    $receiver = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('superadmin.documents.index'))
        ->assertOk()
        ->assertSee('Документы системы');

    $this->actingAs($admin)
        ->get(route('superadmin.documents.create'))
        ->assertOk()
        ->assertSee('Создание документа');

    $this->post(route('superadmin.documents.store'), [
        'title' => 'Договор поставки',
        'number' => 'SA-100',
        'type' => 'Договор',
        'status' => Document::STATUS_DRAFT,
        'created_by' => $admin->id,
        'receiver_id' => $receiver->id,
        'content' => 'Тестовое содержание',
    ])->assertRedirect(route('superadmin.documents.index'));

    $document = Document::where('number', 'SA-100')->firstOrFail();

    $this->put(route('superadmin.documents.update', $document), [
        'title' => 'Договор поставки — обновлён',
        'number' => 'SA-100',
        'type' => 'Договор',
        'status' => Document::STATUS_ACTIVE,
        'created_by' => $admin->id,
        'receiver_id' => $receiver->id,
        'content' => 'Обновлённое содержание',
    ])->assertRedirect(route('superadmin.documents.index'));

    expect($document->fresh()->title)->toBe('Договор поставки — обновлён');

    $documentPath = 'documents/super-admin-test.pdf';
    $versionPath = 'documents/super-admin-version.pdf';
    Storage::disk('public')->put($documentPath, 'document');
    Storage::disk('public')->put($versionPath, 'version');
    $document->forceFill(['file_path' => $documentPath])->saveQuietly();

    DocumentVersion::create([
        'document_id' => $document->id,
        'user_id' => $admin->id,
        'version' => 1,
        'file_path' => $versionPath,
        'original_name' => 'version.pdf',
    ]);
    DocumentSignature::create([
        'document_id' => $document->id,
        'user_id' => $receiver->id,
        'signature' => 'signed',
    ]);
    DocumentComment::create([
        'document_id' => $document->id,
        'user_id' => $receiver->id,
        'comment' => 'Проверено',
    ]);
    DocumentLog::create([
        'document_id' => $document->id,
        'user_id' => $admin->id,
        'action' => 'TEST',
        'description' => 'Связанная запись',
    ]);

    $this->delete(route('superadmin.documents.destroy', $document))
        ->assertRedirect(route('superadmin.documents.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    $this->assertDatabaseMissing('document_versions', ['document_id' => $document->id]);
    $this->assertDatabaseMissing('document_signatures', ['document_id' => $document->id]);
    $this->assertDatabaseMissing('document_comments', ['document_id' => $document->id]);
    $this->assertDatabaseMissing('document_logs', ['document_id' => $document->id]);
    Storage::disk('public')->assertMissing($documentPath);
    Storage::disk('public')->assertMissing($versionPath);
});
