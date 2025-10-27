<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SupportTicketMail;
use App\Mail\SupportRequestMail;

class SupportController extends Controller
{
    public function create()
    {
        $title = 'Submit Ticket';
        return view('support.ticket', compact('title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'order_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:5120', // 5MB
        ]);

        $to = config('support.ticket_to') ?? env('SUPPORT_TICKET_TO') ?? (config('mail.from.address'));

        try {
            Mail::to($to)->send(new SupportTicketMail($data, $request->file('attachment')));
        } catch (\Throwable $e) {
            Log::error('Support ticket email failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to send your ticket. Please try again later.');
        }

        return redirect()->route('support.ticket.create')->with('success', 'Your ticket has been submitted successfully!');
    }

    public function requestCreate()
    {
        $title = 'Submit Request';
        return view('support.request', compact('title'));
    }

    public function requestStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'order_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $to = config('support.request_to') ?? env('SUPPORT_REQUEST_TO') ?? (config('mail.from.address'));

        try {
            Mail::to($to)->send(new SupportRequestMail($data, $request->file('attachment')));
        } catch (\Throwable $e) {
            Log::error('Support request email failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to send your request. Please try again later.');
        }

        return redirect()->route('support.request.create')->with('success', 'Your request has been submitted successfully!');
    }
}
