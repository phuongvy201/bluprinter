@extends('layouts.app')

@section('title', $title ?? 'Submit a Request')

@section('content')
@include('support.partials.page', [
    'eyebrow' => 'Support',
    'title' => 'Submit a request',
    'subtitle' => 'Partnerships, bulk orders, custom projects, or general inquiries — share the details below.',
    'formAction' => route('support.request.store'),
    'submitLabel' => 'Submit request',
    'formId' => 'support-request-form',
])
@endsection
