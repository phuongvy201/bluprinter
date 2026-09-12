@extends('layouts.admin')

@section('title', 'Edit Review')

@section('content')
@include('admin.reviews.partials.form-fields', [
    'review' => $review,
    'products' => $products,
    'submitLabel' => 'Update Review',
])
@endsection
