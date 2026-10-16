@extends('layouts.admin')

@section('title', 'Vol ' . $vol->numero_vol)
@section('header_icon', 'fa-plane-departure')
@section('header_title', 'Modifier le vol ' . $vol->numero_vol)
@section('header_subtitle', 'Les clients déjà réservés gardent leur place')

@section('content')
<form method="POST" action="{{ route('canadian.vols.update', $vol->id) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.canadian.vols._form')
</form>
@endsection
