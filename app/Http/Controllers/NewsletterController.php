<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class NewsletterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'consent' => ['accepted'],
        ]);

        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages([
                'email' => 'Newsletter delivery is not configured yet. Please contact hello@processarc.net.',
            ]);
        }

        Mail::raw('A visitor opted in to receive ProcessArc process-improvement tips. Email: '.$data['email'], function ($message) use ($data) {
            $message->to(config('services.processarc.newsletter_email'))
                ->subject('ProcessArc newsletter opt-in')
                ->replyTo($data['email']);
        });

        return response()->json(['message' => 'Thanks. Your opt-in request has been sent to ProcessArc. Reply to a ProcessArc email if you would like to opt out.']);
    }
}
