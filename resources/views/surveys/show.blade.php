@extends('layouts.app')

@section('title', 'Encuesta')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Ejercicios</a></li>
            <li class="breadcrumb-item"><a href="{{ route('surveys.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item active">Resultados</li>
        </ol>
    </nav>

    <div class="col-lg-8">

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center gap-2">

                <h1 class="h5 mb-0">{{ $survey->question }}</h1>

                @if ($survey->is_open)
                    <span class="badge text-bg-success">Abierta</span>
                @else
                    <span class="badge text-bg-secondary">Cerrada</span>
                @endif

            </div>

            <div class="card-body">

                @if ($showResults)

                    {{-- Informe visual: una barra por opción con su porcentaje --}}
                    @foreach ($survey->options as $option)

                        @php
                            $votos = $tally[$option->id] ?? 0;
                            $porcentaje = $totalVotes > 0 ? round($votos * 100 / $totalVotes) : 0;
                            $esMio = $myVote && $myVote->survey_option_id === $option->id;
                        @endphp

                        <div class="mb-3">

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="{{ $esMio ? 'fw-bold' : '' }}">
                                    {{ $option->label }}
                                    @if ($esMio)
                                        <i class="bi bi-check-circle-fill text-success" title="Tu voto"></i>
                                    @endif
                                </span>
                                <span class="text-muted small">
                                    {{ $votos }} {{ $votos === 1 ? 'voto' : 'votos' }} · {{ $porcentaje }}%
                                </span>
                            </div>

                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $esMio ? 'bg-success' : '' }}"
                                     style="width: {{ $porcentaje }}%">
                                    @if ($porcentaje >= 10)
                                        {{ $porcentaje }}%
                                    @endif
                                </div>
                            </div>

                        </div>

                    @endforeach

                    <hr />

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Total: <strong>{{ $totalVotes }}</strong>
                            {{ $totalVotes === 1 ? 'voto' : 'votos' }}
                        </span>

                        @if ($myVote)
                            <span class="badge text-bg-light">Ya votaste en esta encuesta</span>
                        @elseif (!$survey->is_open)
                            <span class="badge text-bg-light">La encuesta está cerrada</span>
                        @endif
                    </div>

                @else

                    {{-- Todavía no ha votado y la encuesta está abierta --}}
                    <form action="{{ route('surveys.vote', $survey->id) }}" method="POST">
                        @csrf

                        @foreach ($survey->options as $option)
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="radio"
                                       name="survey_option_id"
                                       id="option{{ $option->id }}"
                                       value="{{ $option->id }}"
                                       @checked($loop->first) />
                                <label class="form-check-label" for="option{{ $option->id }}">
                                    {{ $option->label }}
                                </label>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="bi bi-check-lg"></i> Votar
                        </button>

                        <a href="{{ route('surveys.show', ['id' => $survey->id, 'results' => 1]) }}"
                           class="btn btn-link text-decoration-none mt-3">
                            Ver resultados sin votar
                        </a>

                    </form>

                @endif

            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">

                <a href="{{ route('surveys.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a las encuestas
                </a>

                <form action="{{ route('surveys.toggle', $survey->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-dark">
                        <i class="bi {{ $survey->is_open ? 'bi-lock' : 'bi-unlock' }}"></i>
                        {{ $survey->is_open ? 'Cerrar encuesta' : 'Reabrir encuesta' }}
                    </button>
                </form>

            </div>

        </div>

    </div>

@endsection
