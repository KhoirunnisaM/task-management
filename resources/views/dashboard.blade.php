@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
    <x-ui.back-button :fallback="route('dashboard')" class="mb-4" />

    @include('dashboard.'.$role->value)
@endsection
