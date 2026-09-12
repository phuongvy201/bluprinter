@extends('layouts.admin')

@section('title', 'Add Review')

@section('content')
@include('admin.reviews.partials.form-fields', [
    'review' => null,
    'products' => $products,
    'submitLabel' => 'Create Review',
])
@endsection
