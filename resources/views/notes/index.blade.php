@extends('layouts.app')

@section('title', 'Gestor de Notas')
@section('heading', '6 · Gestor de Notas')
@section('subheading', 'Escribir y guardar notas, organizarlas por categoría y buscarlas')

@section('content')

    <div class="row g-4">

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">{{ $editing ? 'Editar nota' : 'Nueva nota' }}</h2>
                    @if ($editing)
                        <a href="{{ route('notes.index') }}" class="btn btn-sm btn-outline-secondary">
                            Cancelar
                        </a>
                    @endif
                </div>

                <div class="card-body">

                    {{-- El mismo formulario crea y edita: cambia la acción y el verbo --}}
                    <form action="{{ $editing ? route('notes.update', $editing->id) : route('notes.store') }}"
                          method="POST">
                        @csrf
                        @if ($editing)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">Título</label>
                            <input type="text"
                                   name="title"
                                   id="title"
                                   class="form-control"
                                   value="{{ old('title', $editing->title ?? '') }}"
                                   placeholder="Resumen del parcial..." />
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Categoría</label>
                            <select name="category" id="category" class="form-select">
                                @foreach ($categories as $option)
                                    <option value="{{ $option }}"
                                        @selected(old('category', $editing->category ?? '') === $option)>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Contenido</label>
                            <textarea name="content"
                                      id="content"
                                      rows="8"
                                      class="form-control"
                                      placeholder="Escribe aquí...">{{ old('content', $editing->content ?? '') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi {{ $editing ? 'bi-save' : 'bi-plus-lg' }}"></i>
                            {{ $editing ? 'Guardar cambios' : 'Guardar nota' }}
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    {{-- Búsqueda y filtro viajan por GET: la URL queda compartible --}}
                    <form action="{{ route('notes.index') }}" method="GET">

                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ $search }}"
                                   placeholder="Buscar en el título y el contenido..." />
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>

                        {{-- El filtro de categoría conserva la búsqueda activa --}}
                        <div class="d-flex flex-wrap gap-2">

                            <a href="{{ route('notes.index', ['search' => $search]) }}"
                               class="btn btn-sm {{ $category === null ? 'btn-dark' : 'btn-outline-dark' }}">
                                Todas
                            </a>

                            @foreach ($categories as $option)
                                <a href="{{ route('notes.index', ['search' => $search, 'category' => $option]) }}"
                                   class="btn btn-sm {{ $category === $option ? 'btn-dark' : 'btn-outline-dark' }}">
                                    {{ $option }}
                                    <span class="badge text-bg-light">{{ $counts[$option] ?? 0 }}</span>
                                </a>
                            @endforeach

                        </div>

                    </form>

                </div>

            </div>

            @if ($notes->isEmpty())

                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                        @if ($search !== '' || $category !== null)
                            <p class="mb-0">Ninguna nota coincide con la búsqueda.</p>
                        @else
                            <p class="mb-0">Todavía no hay notas. Escribe la primera.</p>
                        @endif
                    </div>
                </div>

            @else

                <div class="row row-cols-1 row-cols-md-2 g-3">

                    @foreach ($notes as $note)
                        <div class="col">

                            <div class="card h-100 shadow-sm">

                                <div class="card-body d-flex flex-column">

                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge text-bg-secondary">{{ $note->category }}</span>
                                        <small class="text-muted">{{ $note->updated_at->format('d/m/Y') }}</small>
                                    </div>

                                    <h3 class="h6 fw-bold">{{ $note->title }}</h3>

                                    {{-- Solo un adelanto: la nota completa se ve al editarla --}}
                                    <p class="small text-muted flex-grow-1" style="white-space: pre-line;">{{ \Illuminate\Support\Str::limit($note->content, 180) }}</p>

                                    <div class="d-flex gap-2">

                                        <a href="{{ route('notes.index', ['edit' => $note->id]) }}"
                                           class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>

                                        <form action="{{ route('notes.delete', $note->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btnDelete" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>

            @endif

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        document.querySelectorAll('.btnDelete').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea eliminar la nota?",
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
