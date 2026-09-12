@extends('layouts.app')

@section('title', $title ?? 'Submit a Support Ticket')

@section('content')
@include('support.partials.page', [
    'eyebrow' => 'Help',
    'title' => 'Submit a support ticket',
    'subtitle' => 'Order issue, delivery question, or account help — we usually reply within one business day.',
    'formAction' => route('support.ticket.store'),
    'submitLabel' => 'Submit ticket',
    'formId' => 'support-ticket-form',
])
@endsection
