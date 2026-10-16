@extends('layouts.admin')
@section('title', 'Nouveau code promo')
@section('header_icon', 'fa-tags')
@section('header_title', 'Nouveau code promo')
@section('header_subtitle', 'Le client saisit le code au moment de payer')
@section('content')
<form method="POST" action="{{ route('canadian.promos.store') }}">
    @csrf
    @include('admin.canadian.promos._form')
</form>
@endsection
