@extends('layouts.admin')
@section('title', 'Modifier un avion')
@section('header_icon', 'fa-plane')
@section('header_title', 'Modifier : ' . ($flotte->modele ?: $flotte->nom_appareil))
@section('header_subtitle', 'Un avion hors service ne peut plus être choisi sur un nouveau vol')
@section('content')
<form method="POST" action="{{ route('canadian.flotte.update', $flotte->id) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.canadian.flotte._form')
</form>
@endsection
