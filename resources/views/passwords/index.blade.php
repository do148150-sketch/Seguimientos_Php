@extends('layouts.app')

@section('title', 'Generador de Contraseñas')
@section('heading', '3 · Generador de Contraseñas Seguras')
@section('subheading', 'Contraseñas aleatorias con longitud y tipos de carácter ajustables')

@section('content')

    @php
        $opciones = $opciones ?? [];
        $marcado = function ($campo, $pordefecto) use ($opciones) {
            return empty($opciones) ? $pordefecto : !empty($opciones[$campo]);
        };
    @endphp

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Opciones</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('passwords.generate') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="length" class="form-label">
                                Longitud: <span class="fw-bold" id="lengthValue">{{ old('length', $opciones['length'] ?? 16) }}</span>
                            </label>
                            <input type="range" min="4" max="64"
                                   name="length" id="length"
                                   class="form-range"
                                   value="{{ old('length', $opciones['length'] ?? 16) }}"
                                   oninput="document.getElementById('lengthValue').textContent = this.value" />
                        </div>

                        <p class="form-label mb-2">Tipos de carácter</p>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="lower" id="lower" value="1"
                                   {{ $marcado('lower', true) ? 'checked' : '' }} />
                            <label class="form-check-label" for="lower">Minúsculas <code>a-z</code></label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="upper" id="upper" value="1"
                                   {{ $marcado('upper', true) ? 'checked' : '' }} />
                            <label class="form-check-label" for="upper">Mayúsculas <code>A-Z</code></label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="digits" id="digits" value="1"
                                   {{ $marcado('digits', true) ? 'checked' : '' }} />
                            <label class="form-check-label" for="digits">Números <code>2-9</code></label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="symbols" id="symbols" value="1"
                                   {{ $marcado('symbols', false) ? 'checked' : '' }} />
                            <label class="form-check-label" for="symbols">Símbolos <code>!@#$%&amp;</code></label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-repeat"></i> Generar
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-7">

            @if ($password === null)

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center text-muted py-5">
                        <i class="bi bi-shield-lock fs-1 mb-2"></i>
                        <p class="mb-0">Elegí las opciones y generá tu contraseña.</p>
                    </div>
                </div>

            @else

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h2 class="h6 mb-0">Tu contraseña</h2>
                    </div>

                    <div class="card-body">

                        <div class="input-group mb-3">
                            <input type="text" id="resultado" class="form-control font-monospace fs-5"
                                   value="{{ $password }}" readonly />
                            <button class="btn btn-outline-secondary" type="button" id="btnCopiar">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Fortaleza</small>
                            <small class="fw-bold text-{{ $fuerza['clase'] }}">{{ $fuerza['texto'] }}</small>
                        </div>

                        <div class="progress" style="height: 8px">
                            <div class="progress-bar bg-{{ $fuerza['clase'] }}"
                                 style="width: {{ $fuerza['porcentaje'] }}%"></div>
                        </div>

                        <p class="small text-muted mt-3 mb-0">
                            Entropía aproximada: <b>{{ number_format($fuerza['bits'], 0) }} bits</b>.
                            Se calcula como la longitud multiplicada por el logaritmo en base 2 del
                            tamaño del alfabeto: a más tipos de carácter y más longitud, más difícil de adivinar.
                        </p>

                        <p class="small text-muted mb-0 mt-2">
                            Se excluyen los caracteres ambiguos (<code>l</code>, <code>I</code>, <code>1</code>,
                            <code>O</code>, <code>0</code>) para que no haya confusión al leerla.
                        </p>

                    </div>

                </div>

            @endif

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        const btnCopiar = document.getElementById('btnCopiar');

        if (btnCopiar) {

            btnCopiar.addEventListener('click', function () {

                const campo = document.getElementById('resultado');

                campo.select();
                navigator.clipboard.writeText(campo.value);

                Swal.fire({
                    title: "Copiada al portapapeles",
                    icon: "success",
                    timer: 1200,
                    showConfirmButton: false
                });
            });
        }

    </script>

@endpush
