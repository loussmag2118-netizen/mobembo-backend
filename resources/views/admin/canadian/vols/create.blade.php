@extends('layouts.admin')

@section('title', 'Nouveau vol')
@section('header_icon', 'fa-plane-departure')
@section('header_title', 'Programmer un vol')
@section('header_subtitle', 'Le vol est proposé dans l\'application dès qu\'il est enregistré avec le statut « Programmé »')

@section('content')
<form method="POST" action="{{ route('canadian.vols.store') }}" class="space-y-6">
    @csrf
    @include('admin.canadian.vols._form')
</form>
@endsection
