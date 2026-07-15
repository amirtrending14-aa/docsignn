<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Общий метод для логирования любого действия
     */
    public static function log(string $action, $model = null, string $description = null, array $properties = [])
    {
        Activity::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'Unknown',
        ]);
    }

    /**
     * Логирование входа в систему
     */
    public static function login()
    {
        self::log('login', null, 'Пользователь вошел в систему');
    }

    /**
     * Логирование выхода из системы
     */
    public static function logout()
    {
        self::log('logout', null, 'Пользователь вышел из системы');
    }

    /**
     * Логирование создания документа
     */
    public static function documentCreated($document)
    {
        self::log('document_created', $document, "Создан документ: {$document->title}", [
            'title' => $document->title,
            'status' => $document->status ?? 'draft',
        ]);
    }

    /**
     * Логирование обновления документа
     */
    public static function documentUpdated($document)
    {
        self::log('document_updated', $document, "Обновлен документ: {$document->title}", [
            'title' => $document->title,
        ]);
    }

    /**
     * Логирование удаления документа
     */
    public static function documentDeleted($document)
    {
        self::log('document_deleted', null, "Удален документ: {$document->title}", [
            'document_id' => $document->id,
        ]);
    }

    /**
     * Логирование подписания документа
     */
    public static function documentSigned($document, $signature = null)
    {
        self::log('document_signed', $document, "Подписан документ: {$document->title}", [
            'signature_id' => $signature ? $signature->id : null,
        ]);
    }

    /**
     * Логирование создания пользователя
     */
    public static function userCreated($user)
    {
        self::log('user_created', $user, "Создан пользователь: {$user->email}", [
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'is_super_admin' => $user->is_super_admin,
        ]);
    }

    /**
     * Логирование обновления пользователя
     */
    public static function userUpdated($user, array $changes = [])
    {
        self::log('user_updated', $user, "Обновлены данные пользователя: {$user->email}", [
            'changes' => $changes,
        ]);
    }

    /**
     * Логирование удаления пользователя
     */
    public static function userDeleted($user)
    {
        self::log('user_deleted', null, "Удален пользователь: {$user->email}", [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}