@extends('layouts.app')

@section('title', 'Ejercicios')

@section('content')

    <div class="text-center mb-5">
        <h1 class="h2">Seguimientos</h1>
        <p class="text-muted mb-0">Diez ejercicios resueltos con la arquitectura MVC</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">

        @foreach ($ejercicios as $ejercicio)
            <div class="col">

                <div class="card h-100 shadow-sm">

                    <div class="card-body d-flex flex-column">

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge text-bg-dark">{{ $ejercicio['n'] }}</span>
                            <i class="bi {{ $ejercicio['icono'] }} fs-4 text-primary"></i>
                        </div>

                        <h2 class="h6 fw-bold">{{ $ejercicio['titulo'] }}</h2>

                        <p class="small text-muted flex-grow-1">{{ $ejercicio['descripcion'] }}</p>

                        <a href="{{ route($ejercicio['ruta']) }}" class="btn btn-sm btn-primary">
                            Abrir
                        </a>

                    </div>

                </div>

            </div>
        @endforeach

    </div>

@endsection
