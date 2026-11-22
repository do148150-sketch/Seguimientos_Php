@extends('layouts.app')

@section('title', 'Plataforma de Encuestas')
@section('heading', '10 · Plataforma de Encuestas')
@section('subheading', 'Crear y responder encuestas en línea, y analizar los resultados')

@section('content')

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Nueva encuesta</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('surveys.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="question" class="form-label">Pregunta</label>
                            <input type="text"
                                   name="question"
                                   id="question"
                                   class="form-control"
                                   value="{{ old('question') }}"
                                   placeholder="¿Cuál es el mejor lenguaje para aprender?" />
                        </div>

                        <label class="form-label">Opciones <span class="text-muted">(mínimo 2)</span></label>

                        <div id="opciones">
                            @for ($i = 0; $i < 4; $i++)
                                <div class="input-group mb-2">
                                    <span class="input-group-text">{{ $i + 1 }}</span>
                                    <input type="text"
                                           name="options[]"
                                           class="form-control"
                                           maxlength="80"
                                           value="{{ old('options.' . $i) }}"
                                           placeholder="{{ $i < 2 ? 'Obligatoria' : 'Opcional' }}" />
                                </div>
                            @endfor
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="btnAgregar">
                            <i class="bi bi-plus-lg"></i> Agregar opción
                        </button>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-bar-chart"></i> Crear encuesta
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-7">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">Encuestas</h2>
                    <span class="badge text-bg-secondary">{{ $surveys->count() }}</span>
                </div>

                @if ($surveys->isEmpty())

                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                        <p class="mb-0">Todavía no hay encuestas. Crea la primera.</p>
                    </div>

                @else

                    <ul class="list-group list-group-flush">

                        @foreach ($surveys as $survey)
                            <li class="list-group-item">

                                <div class="d-flex justify-content-between align-items-start gap-2">

                                    <div class="flex-grow-1">

                                        <a href="{{ route('surveys.show', $survey->id) }}"
                                           class="text-decoration-none fw-bold">
                                            {{ $survey->question }}
                                        </a>

                                        <small class="text-muted d-block">
                                            {{ $survey->options_count }} opciones ·
                                            {{ $survey->votes_count }}
                                            {{ $survey->votes_count === 1 ? 'voto' : 'votos' }}
                                        </small>

                                    </div>

                                    <div class="d-flex align-items-center gap-2">

                                        @if ($survey->is_open)
                                            <span class="badge text-bg-success">Abierta</span>
                                        @else
                                            <span class="badge text-bg-secondary">Cerrada</span>
                                        @endif

                                        <a href="{{ route('surveys.show', $survey->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Ver
                                        </a>

                                        <form action="{{ route('surveys.delete', $survey->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btnDelete" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>

                                </div>

                            </li>
                        @endforeach

                    </ul>

                @endif

            </div>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        // Hasta 6 opciones, que es el tope que valida el controlador
        document.getElementById('btnAgregar').addEventListener('click', function () {

            const contenedor = document.getElementById('opciones');
            const actuales   = contenedor.querySelectorAll('input').length;

            if (actuales >= 6) {

                Swal.fire({
                    title: "Máximo 6 opciones",
                    icon: "info",
                });
                return;
            }

            const fila = document.createElement('div');
            fila.className = 'input-group mb-2';
            fila.innerHTML =
                '<span class="input-group-text">' + (actuales + 1) + '</span>' +
                '<input type="text" name="options[]" class="form-control" maxlength="80" placeholder="Opcional" />';

            contenedor.appendChild(fila);
        });

        document.querySelectorAll('.btnDelete').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea eliminar la encuesta?",
                    text: "Se borrarán también sus opciones y sus votos",
                    icon: "question",
                    showCancelButton: true,
                }).then(function (result) {

                    if (result.isConfirmed) {

                        boton.closest('form').submit();
                    }
                });
            });
        });

    </script>

@endpush
