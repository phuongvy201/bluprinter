<?php

namespace App\Http\Controllers;

use App\Mail\SupportRequestMail;
use App\Mail\SupportTicketMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    private const MIN_SUBMIT_SECONDS = 3;

    private const MAX_SUBMIT_SECONDS = 7200;

    public function create()
    {
        $title = 'Submit Ticket';

        return view('support.ticket', compact('title'));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->handleSubmission(
            $request,
            SupportTicketMail::class,
            config('support.ticket_to') ?? env('SUPPORT_TICKET_TO') ?? config('mail.from.address'),
            'support.ticket.create',
            'Your ticket has been submitted successfully!',
            'Support ticket'
        );
    }

    public function requestCreate()
    {
        $title = 'Submit Request';

        return view('support.request', compact('title'));
    }

    public function requestStore(Request $request): RedirectResponse
    {
        return $this->handleSubmission(
            $request,
            SupportRequestMail::class,
            config('support.request_to') ?? env('SUPPORT_REQUEST_TO') ?? config('mail.from.address'),
            'support.request.create',
            'Your request has been submitted successfully!',
            'Support request'
        );
    }

    private function handleSubmission(
        Request $request,
        string $mailClass,
        ?string $recipient,
        string $redirectRoute,
        string $successMessage,
        string $logContext,
    ): RedirectResponse {
        if ($this->isBotSubmission($request)) {
            Log::warning("{$logContext} blocked by anti-bot guard", [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route($redirectRoute)->with('success', $successMessage);
        }

        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        if (empty($recipient)) {
            Log::error("{$logContext} recipient missing");

            return back()->withInput()->with('error', 'Support email is not configured. Please try again later.');
        }

        try {
            Mail::to($recipient)->send(new $mailClass($data, $request->file('attachment')));
        } catch (\Throwable $e) {
            Log::error("{$logContext} email failed", ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to send your message. Please try again later.');
        }

        return redirect()->route($redirectRoute)->with('success', $successMessage);
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'order_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt',
            'website_url' => 'nullable|string|max:0',
            'fax_number' => 'nullable|string|max:0',
            'form_started_at' => 'nullable|integer',
        ];
    }

    private function isBotSubmission(Request $request): bool
    {
        foreach (['website_url', 'fax_number'] as $trapField) {
            if (trim((string) $request->input($trapField, '')) !== '') {
                return true;
            }
        }

        $formStartedAt = (int) $request->input('form_started_at', 0);
        if ($formStartedAt > 0) {
            $secondsToSubmit = now()->timestamp - $formStartedAt;

            if ($secondsToSubmit < self::MIN_SUBMIT_SECONDS || $secondsToSubmit > self::MAX_SUBMIT_SECONDS) {
                return true;
            }
        }

        return false;
    }
}
