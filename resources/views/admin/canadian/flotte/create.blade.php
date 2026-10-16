@extends('layouts.admin')
@section('title', 'Ajouter un avion')
@section('header_icon', 'fa-plane')
@section('header_title', 'Ajouter un avion')
@section('header_subtitle', 'Il pourra être choisi sur vos vols')
@section('content')
<form method="POST" action="{{ route('canadian.flotte.store') }}" class="space-y-6">
    @csrf
    @include('admin.canadian.flotte._form')
</form>
@endsection
