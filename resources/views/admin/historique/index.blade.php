@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Historique des opérations</h1>

    <p>Total opérations : {{ $operations->total() ?? $operations->count() }}</p>

    <table class="w-full border">
        <thead>
            <tr>
                <th>Date</th>
                <th>Agent</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($operations as $op)
                <tr>
                    <td>{{ $op->created_at }}</td>
                    <td>{{ $op->agent_id ?? '-' }}</td>
                    <td>{{ $op->action ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection