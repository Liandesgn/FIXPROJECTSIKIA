@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-[#542D3D]">Kelola Permission</h1>
    <form action="{{ route('admin.permissions.update') }}" method="POST" class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-[#F1D5E0] bg-[#FCE7F0] text-xs font-bold uppercase text-[#542D3D]">
                    <tr>
                        <th class="p-3">Permission</th>
                        @foreach($roles as $role)
                            <th class="p-3 text-center">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $p)
                    <tr class="border-b border-slate-100">
                        <td class="p-3 font-semibold text-[#542D3D]">{{ $p->name }}</td>
                        @foreach($roles as $role)
                            <td class="p-3 text-center">
                                <input type="checkbox" name="permissions[{{ $role->id }}][]" value="{{ $p->name }}"
                                    {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]">
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="rounded-xl bg-[#C9517E] px-6 py-2.5 font-bold text-white transition hover:bg-[#B7436D]">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection