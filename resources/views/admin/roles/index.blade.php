@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#542D3D]">Kelola Role</h1>
    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="border-b border-[#F1D5E0] bg-[#FCE7F0] text-xs font-bold uppercase text-[#542D3D]">
                <tr>
                    <th class="p-3">Nama Role</th>
                    <th class="p-3">Permission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr class="border-b border-slate-100">
                    <td class="p-3 font-semibold text-[#542D3D]">{{ $role->name }}</td>
                    <td class="p-3">{{ $role->permissions->pluck('name')->join(', ') ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection