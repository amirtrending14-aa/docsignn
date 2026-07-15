<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function update(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Удаляем старый аватар
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Загружаем новый
        $path = $request->file('avatar')->store('avatars');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Аватар обновлён!');
    }

    public function destroy(User $user)
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return back()->with('success', 'Аватар удалён!');
    }
}