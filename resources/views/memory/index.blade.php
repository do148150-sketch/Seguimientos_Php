@extends('layouts.app')

@section('title', 'Juego de Memoria')
@section('heading', '9 · Juego de Memoria')
@section('subheading', 'Emparejar cartas, con niveles de dificultad y puntajes')

@section('content')

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div class="btn-group btn-group-sm">
                        @foreach ($levels as $name => $config)
                            <a href="{{ route('memory.index', ['difficulty' => $name]) }}"
                               class="btn {{ $difficulty === $name ? 'btn-dark' : 'btn-outline-dark' }}">
                                {{ $name }}
                                <span class="badge text-bg-light">{{ $config['pairs'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="d-flex gap-3 small">
                        <span>Jugadas: <strong id="moves">0</strong></span>
                        <span>Tiempo: <strong id="timer">0</strong>s</span>
                        <span>Parejas: <strong id="found">0</strong>/{{ $level['pairs'] }}</span>
                    </div>

                </div>

                <div class="card-body">

                    {{-- El mazo viene ya barajado desde el controlador --}}
                    <div class="row row-cols-{{ $level['columns'] }} g-2" id="board">

                        @foreach ($deck as $index => $face)
                            <div class="col">
                                <button type="button"
                                        class="btn btn-primary w-100 card-memoria"
                                        data-index="{{ $index }}"
                                        data-face="{{ $face }}"
                                        style="height: 70px; font-size: 1.8rem; line-height: 1;">
                                    <span class="cara" style="visibility: hidden;">{{ $face }}</span>
                                </button>
                            </div>
                        @endforeach

                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReiniciar">
                            <i class="bi bi-arrow-clockwise"></i> Reiniciar
                        </button>
                        <span class="text-muted small align-self-center">
                            Destapa dos cartas: si son iguales se quedan, si no se vuelven a tapar.
                        </span>
                    </div>

                </div>

            </div>

            {{-- Al ganar se muestra este formulario para guardar el puntaje --}}
            <div class="card shadow-sm mt-4 d-none" id="cardGuardar">

                <div class="card-header bg-success text-white">
                    <h2 class="h6 mb-0"><i class="bi bi-trophy"></i> ¡Ganaste!</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('memory.store') }}" method="POST" class="row g-2 align-items-end">
                        @csrf

                        <input type="hidden" name="difficulty" value="{{ $difficulty }}" />
                        <input type="hidden" name="moves" id="inputMoves" />
                        <input type="hidden" name="seconds" id="inputSeconds" />

                        <div class="col-sm-7">
                            <label for="player_name" class="form-label">Tu nombre</label>
                            <input type="text"
                                   name="player_name"
                                   id="player_name"
                                   class="form-control"
                                   maxlength="60"
                                   placeholder="Diego" />
                        </div>

                        <div class="col-sm-5">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-save"></i> Guardar puntaje
                            </button>
                        </div>

                    </form>

                    <p class="text-muted small mb-0 mt-2" id="resumenPartida"></p>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Mejores puntajes · {{ $difficulty }}</h2>
                </div>

                @if ($ranking->isEmpty())

                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-trophy fs-1 d-block mb-2"></i>
                        <p class="mb-0 small">Todavía nadie ha ganado en este nivel.</p>
                    </div>

                @else

                    <ol class="list-group list-group-numbered list-group-flush">
                        @foreach ($ranking as $score)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold small">{{ $score->player_name }}</div>
                                    <small class="text-muted">
                                        {{ $score->moves }} jugadas · {{ $score->seconds }}s
                                    </small>
                                </div>
                                <span class="badge text-bg-primary rounded-pill">{{ $score->points }}</span>
                            </li>
                        @endforeach
                    </ol>

                @endif

                <div class="card-footer small text-muted">
                    Puntaje = {{ $level['pairs'] }} × 100 − 5 por jugada de más − 2 por segundo.
                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        const totalParejas = {{ $level['pairs'] }};

        let primera     = null;   // la primera carta destapada del turno
        let bloqueado   = false;  // mientras se comparan dos cartas no se puede clicar
        let jugadas     = 0;
        let encontradas = 0;
        let segundos    = 0;
        let cronometro  = null;

        const elMoves = document.getElementById('moves');
        const elTimer = document.getElementById('timer');
        const elFound = document.getElementById('found');

        function arrancarCronometro() {

            if (cronometro !== null) return;   // ya estaba corriendo

            cronometro = setInterval(function () {
                segundos++;
                elTimer.textContent = segundos;
            }, 1000);
        }

        function destapar(carta) {

            carta.querySelector('.cara').style.visibility = 'visible';
            carta.classList.remove('btn-primary');
            carta.classList.add('btn-light', 'border');
        }

        function tapar(carta) {

            carta.querySelector('.cara').style.visibility = 'hidden';
            carta.classList.remove('btn-light', 'border');
            carta.classList.add('btn-primary');
        }

        function fijar(carta) {

            carta.classList.remove('btn-light', 'border');
            carta.classList.add('btn-success');
            carta.disabled = true;
        }

        function ganar() {

            clearInterval(cronometro);

            document.getElementById('inputMoves').value   = jugadas;
            document.getElementById('inputSeconds').value = segundos;
            document.getElementById('resumenPartida').textContent =
                `Terminaste en ${jugadas} jugadas y ${segundos} segundos.`;

            document.getElementById('cardGuardar').classList.remove('d-none');
            document.getElementById('player_name').focus();
        }

        document.querySelectorAll('.card-memoria').forEach(function (carta) {

            carta.addEventListener('click', function () {

                if (bloqueado || carta.disabled) return;
                if (primera === carta) return;   // clic repetido sobre la misma

                arrancarCronometro();
                destapar(carta);

                if (primera === null) {
                    primera = carta;
                    return;
                }

                // Ya hay dos cartas destapadas: cuenta como una jugada
                jugadas++;
                elMoves.textContent = jugadas;

                if (primera.dataset.face === carta.dataset.face) {

                    fijar(primera);
                    fijar(carta);
                    primera = null;

                    encontradas++;
                    elFound.textContent = encontradas;

                    if (encontradas === totalParejas) ganar();

                } else {

                    // No coinciden: se dejan ver un momento y se vuelven a tapar
                    bloqueado = true;
                    const anterior = primera;
                    primera = null;

                    setTimeout(function () {
                        tapar(anterior);
                        tapar(carta);
                        bloqueado = false;
                    }, 700);
                }
            });
        });

        // Reiniciar pide un reparto nuevo al servidor
        document.getElementById('btnReiniciar').addEventListener('click', function () {
            window.location.reload();
        });

    </script>

@endpush
