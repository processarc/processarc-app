<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CompanyContactController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'company' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'consent' => ['accepted'],
        ]);

        if (config('mail.default') === 'log') {
            throw ValidationException::withMessages([
                'email' => 'Contact email delivery is not configured on this site yet. Please write to hello@processarc.net directly.',
            ]);
        }

        Mail::raw("Name: {$data['name']}\nCompany: ".($data['company'] ?: 'Not provided')."\nEmail: {$data['email']}\n\n{$data['message']}", function ($message) use ($data) {
            $message->to(config('services.processarc.contact_email'))
                ->replyTo($data['email'], $data['name'])
                ->subject('ProcessArc website inquiry');
        });

        return back()->with('success', 'Your message has been sent. The ProcessArc team will reply using the email address you provided.');
    }
}
