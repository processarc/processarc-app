<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ChatContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'consent' => ['accepted'],
        ]);

        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages([
                'email' => 'Email delivery is not configured on this site yet. Please write to hello@processarc.net directly.',
            ]);
        }

        Mail::raw('A ProcessArc website visitor asked for a follow-up. Reply to: '.$data['email'], function ($message) use ($data) {
            $message->to(config('services.processarc.contact_email'))
                ->subject('ProcessArc website follow-up')
                ->replyTo($data['email']);
        });

        return response()->json(['message' => 'Thanks. Your email has been sent to the ProcessArc team for a reply.']);
    }
}
