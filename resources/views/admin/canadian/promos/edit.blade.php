@extends('layouts.admin')
@section('title', 'Code ' . $promo->code)
@section('header_icon', 'fa-tags')
@section('header_title', 'Modifier le code ' . $promo->code)
@section('header_subtitle', 'Les réservations déjà payées ne sont pas modifiées')
@section('content')
<form method="POST" action="{{ route('canadian.promos.update', $promo->id) }}">
    @csrf
    @method('PUT')
    @include('admin.canadian.promos._form')
</form>
@endsection
