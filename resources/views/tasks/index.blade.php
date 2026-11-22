@extends('layouts.app')

@section('title', 'Lista de Tareas')
@section('heading', '1 · Lista de Tareas')
@section('subheading', 'Agregar, eliminar y marcar tareas como completadas')

@section('content')

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Nueva tarea</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">¿Qué hay que hacer?</label>
                            <input type="text"
                                   name="title"
                                   id="title"
                                   class="form-control"
                                   value="{{ old('title') }}"
                                   placeholder="Estudiar para el parcial..."
                                   autofocus />
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-7">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">Mis tareas</h2>
                    <span class="badge text-bg-warning">{{ $pending }} pendientes</span>
                </div>

                @if ($tasks->isEmpty())

                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <p class="mb-0">Todavía no hay tareas. Agregá la primera.</p>
                    </div>

                @else

                    <ul class="list-group list-group-flush">

                        @foreach ($tasks as $task)
                            <li class="list-group-item d-flex align-items-center gap-2">

                                {{-- Marcar o desmarcar cambia estado: formulario PUT, nunca un enlace --}}
                                <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="btn btn-sm {{ $task->is_completed ? 'btn-success' : 'btn-outline-secondary' }}"
                                            title="{{ $task->is_completed ? 'Marcar como pendiente' : 'Marcar como completada' }}">
                                        <i class="bi {{ $task->is_completed ? 'bi-check-lg' : 'bi-square' }}"></i>
                                    </button>
                                </form>

                                <span class="flex-grow-1 {{ $task->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </span>

                                <small class="text-muted d-none d-sm-block">
                                    {{ $task->created_at->format('d/m/Y') }}
                                </small>

                                <form action="{{ route('tasks.delete', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btnDelete" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

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

        document.querySelectorAll('.btnDelete').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea eliminar la tarea?",
                    text: "No podrá revertirlo",
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
