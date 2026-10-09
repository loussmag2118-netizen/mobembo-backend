@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Voyageurs</h1>
        <span class="badge bg-primary text-white fs-6 px-3 py-2">Agence : {{ $agenceAdmin ?? 'Centrale' }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des passagers inscrits (Mobile)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom & Prénom</th>
                            <th>Référence / Ticket</th>
                            <th>Trajet</th>
                            <th>Date de voyage</th>
                            <th>Statut Agents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voyageurs as $v)
                            <tr>
                                <td>{{ $v->id }}</td>
                                <td><strong>{{ $v->nom }} {{ $v->prenom ?? '' }}</strong></td>
                                <td><code>{{ $v->reference ?? $v->id }}</code></td>
                                <td>{{ $v->nom_trajet ?? 'N/A' }}</td>
                                <td>{{ $v->date_voyage ?? 'N/A' }}</td>
                                <td>
                                    @if(isset($v->envoye_aux_agents) && $v->envoye_aux_agents)
                                        <span class="badge bg-success">Transmis aux agents</span>
                                    @else
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.voyageurs.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce voyageur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucun voyageur enregistré pour cette agence pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
