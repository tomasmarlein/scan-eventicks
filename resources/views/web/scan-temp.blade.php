@extends('template.template')

@section('main')
    <div class="container py-4">
        <h1 class="mb-3">QR-code scannen</h1>

        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
            <label class="me-2">Camera</label>
            <select id="cameraSelect" class="form-select" style="max-width:420px"></select>
            <button id="startBtn" class="btn btn-primary">Start</button>
            <button id="stopBtn" class="btn btn-outline-secondary" disabled>Stop</button>
            <button id="torchBtn" class="btn btn-outline-dark" disabled>🔦 Zaklamp</button>
        </div>

        <div class="ratio ratio-4x3" style="max-width:640px;">
            <video id="preview" autoplay muted playsinline
                   style="width:100%; height:100%; object-fit:cover; border-radius:.5rem; border:1px solid #ddd;"></video>
        </div>

        <form id="resultForm" class="d-none" method="POST" action="{{ route('scan.result') }}">
            @csrf
            <input type="hidden" name="qr" id="qrField">
        </form>

        <p class="text-muted mt-3" id="status">Status: klaar</p>
    </div>

    @vite(['resources/js/shared/scan.js'])
@endsection
