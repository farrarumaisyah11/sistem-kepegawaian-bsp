@extends('layouts.app')

@section('content')
@php
  $pegawaiPrefix = auth()->user()->role;
@endphp

<div class="container py-4">
    <div class="card shadow-sm rounded-4">
        <div class="card-body p-4">
            <h4 class="mb-3">Import Data Pegawai</h4>
            <p class="text-muted">Upload file Excel, lalu sistem akan menampilkan preview sebelum disimpan.</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route($pegawaiPrefix.'.pegawai.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route($pegawaiPrefix.'.pegawai.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Preview Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection