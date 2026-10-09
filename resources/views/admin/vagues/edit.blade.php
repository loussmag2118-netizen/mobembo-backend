@extends('layouts.admin')

@section('content')


<div class="max-w-2xl mx-auto">


<h2 class="text-2xl font-bold mb-6 text-slate-800">

Modifier la vague

</h2>





<form action="{{ route('admin.vagues.update',$vague->id) }}"
      method="POST"
      class="bg-white p-6 rounded-xl shadow">


@csrf

@method('PUT')




@if($errors->any())


<div class="mb-5 p-4 bg-red-100 border border-red-300 rounded-lg text-red-700">


<strong>Erreur :</strong>


<ul class="list-disc ml-5 mt-2">


@foreach($errors->all() as $error)


<li>{{ $error }}</li>


@endforeach


</ul>


</div>


@endif






<div class="mb-5">


<label class="block font-semibold mb-2">

Nom de la vague

</label>



<input

type="text"

name="nom_vague"

value="{{ old('nom_vague',$vague->nom_vague) }}"

required

class="w-full border rounded-lg p-3">


</div>







<div class="mb-6">


<label class="block font-semibold mb-2">

Heure de départ

</label>




<input

type="time"

name="heure_depart"

value="{{ old('heure_depart', \Carbon\Carbon::parse($vague->heure_depart)->format('H:i')) }}"

required

class="w-full border rounded-lg p-3">


</div>






<div class="flex gap-3">


<button type="submit"

class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">

Mettre à jour

</button>




<a href="{{ route('admin.vagues.index') }}"

class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg">

Annuler

</a>



</div>




</form>


</div>


@endsection