<?php

namespace App\Http\Controllers;

use App\Models\GuestRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GuestRequestController extends Controller
{
    public function create()
    {
        return view('guest.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sender_who'        => 'required|string|max:255',
            'sender_email'      => 'required|email|max:255',
            'sender_phone'      => 'required|string|max:30',
            'doc_type'          => 'required|string|max:100',
            'doc_title'         => 'required|string|max:255',
            'doc_number'        => 'nullable|string|max:100',
            'doc_description'   => 'nullable|string',
            'doc_file'          => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:10240',
            'deadline'          => 'nullable|date|after_or_equal:today',
            'recipient_user_id' => 'required|exists:users,id',
        ]);

        $guest = User::firstOrCreate(
            ['email' => $data['sender_email']],
            [
                'name'     => $data['sender_who'],
                'phone'    => $data['sender_phone'],
                'role'     => 'guest',
                'password' => bcrypt(str()->random(32)),
            ]
        );

        if ($request->hasFile('doc_file')) {
            $data['doc_file'] = $request->file('doc_file')->store('guest_requests', 'public');
        } else {
            $data['doc_file'] = null;
        }

        $data['user_id'] = $guest->id;
        unset($data['sender_email'], $data['sender_phone']);
        $data['sender_email'] = $request->sender_email;
        $data['sender_phone'] = $request->sender_phone;

        $guestRequest = GuestRequest::create($data);

        return view('guest.success', compact('guestRequest'));
    }

    public function index(Request $request)
    {
        $query = GuestRequest::with(['sender', 'recipient']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('doc_title', 'like', "%{$s}%")
                  ->orWhere('sender_email', 'like', "%{$s}%")
                  ->orWhere('doc_number', 'like', "%{$s}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();
        return view('guest.index', compact('requests'));
    }

    public function show(GuestRequest $guestRequest)
    {
        $guestRequest->load(['sender', 'recipient']);
        return view('guest.show', compact('guestRequest'));
    }

    public function edit(GuestRequest $guestRequest)
    {
        $guestRequest->load(['sender', 'recipient']);
        return view('guest.edit', compact('guestRequest'));
    }

    public function update(Request $request, GuestRequest $guestRequest)
    {
        $data = $request->validate([
            'status'        => ['required', Rule::in(['new', 'in_progress', 'answered', 'closed'])],
            'admin_comment' => 'nullable|string',
        ]);

        $guestRequest->update($data);

        return redirect()
            ->route('guest.requests.show', $guestRequest)
            ->with('success', 'Заявка обновлена');
    }

    public function destroy(GuestRequest $guestRequest)
    {
        if ($guestRequest->doc_file) {
            Storage::disk('public')->delete($guestRequest->doc_file);
        }
        $guestRequest->delete();

        return redirect()
            ->route('guest.requests.index')
            ->with('success', 'Заявка удалена');
    }
}