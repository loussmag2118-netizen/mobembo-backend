@extends('layouts.admin')

@section('content')

<div class="sm:flex sm:items-center sm:justify-between mb-8">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Vagues de départ
        </h1>

        <p class="text-slate-500 text-sm mt-1">
            Gérez vos différentes plages horaires de départ.
        </p>
    </div>



    <div class="flex gap-3 mt-4 sm:mt-0">


        <a href="{{ route('admin.vagues.trashed') }}"
           class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">

            🗑 Corbeille

        </a>




        <a href="{{ route('admin.vagues.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">

            + Ajouter une vague

        </a>


    </div>


</div>




@if(session('success'))

<div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg">

    {{ session('success') }}

</div>

@endif






<div class="bg-white shadow ring-1 ring-slate-200 rounded-xl overflow-hidden">


<table class="w-full text-left">


<thead class="bg-slate-50">

<tr>

<th class="p-4 text-xs font-bold uppercase text-slate-500">
Nom de la vague
</th>


<th class="p-4 text-xs font-bold uppercase text-slate-500">
Heure de départ
</th>


<th class="p-4 text-xs font-bold uppercase text-slate-500 text-right">
Actions
</th>


</tr>

</thead>





<tbody class="divide-y divide-slate-200">



@forelse($vagues as $v)



<tr class="hover:bg-slate-50">


<td class="p-4 font-semibold text-slate-900">

{{ $v->nom_vague }}

</td>




<td class="p-4 text-slate-600">

{{ \Carbon\Carbon::parse($v->heure_depart)->format('H:i') }}

</td>





<td class="p-4">


<div class="flex justify-end gap-4">


<a href="{{ route('admin.vagues.edit',$v->id) }}"
class="text-indigo-600 font-medium hover:underline">

Modifier

</a>





<form action="{{ route('admin.vagues.destroy',$v->id) }}"
method="POST"
onsubmit="return confirm('Envoyer cette vague dans la corbeille ?')">


@csrf

@method('DELETE')


<button type="submit"
class="text-red-600 font-medium hover:underline">

Supprimer

</button>


</form>


</div>


</td>


</tr>



@empty



<tr>

<td colspan="3" class="p-8 text-center text-slate-400">


Aucune vague enregistrée.


<br>


<a href="{{ route('admin.vagues.create') }}"
class="text-indigo-600 font-bold hover:underline">

Ajouter une vague

</a>


</td>

</tr>



@endforelse



</tbody>


</table>


</div>


@endsection