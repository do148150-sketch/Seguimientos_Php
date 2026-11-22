@extends('layouts.app')

@section('title', $recipe->title)

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Ejercicios</a></li>
            <li class="breadcrumb-item"><a href="{{ route('recipes.index') }}">Recetas</a></li>
            <li class="breadcrumb-item active">{{ $recipe->title }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <h1 class="h4 mb-1">{{ $recipe->title }}</h1>
                <span class="badge text-bg-secondary">{{ $recipe->meal_type }}</span>
                <small class="text-muted ms-2">
                    <i class="bi bi-clock"></i> {{ $recipe->minutes }} minutos
                </small>
            </div>

            <div class="d-flex gap-2">

                {{-- Compartir: copia al portapapeles la URL de esta misma ficha --}}
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnShare">
                    <i class="bi bi-share"></i> Compartir
                </button>

                <form action="{{ route('recipes.favorite', $recipe->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm {{ $recipe->is_favorite ? 'btn-warning' : 'btn-outline-warning' }}">
                        <i class="bi {{ $recipe->is_favorite ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                        {{ $recipe->is_favorite ? 'Guardada' : 'Guardar' }}
                    </button>
                </form>

            </div>

        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-4">

                    <h2 class="h6 fw-bold">Ingredientes</h2>

                    <ul class="list-group list-group-flush">
                        @foreach ($recipe->ingredientList() as $ingredient)
                            <li class="list-group-item px-0 py-1 border-0 small">
                                <i class="bi bi-dot"></i> {{ $ingredient }}
                            </li>
                        @endforeach
                    </ul>

                </div>

                <div class="col-md-8">

                    <h2 class="h6 fw-bold">Preparación</h2>

                    {{-- white-space: pre-line respeta los saltos de línea que escribió el usuario --}}
                    <p class="small" style="white-space: pre-line;">{{ $recipe->instructions }}</p>

                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">

            <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a las recetas
            </a>

            <form action="{{ route('recipes.delete', $recipe->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger btnDelete">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        document.getElementById('btnShare').addEventListener('click', function () {

            navigator.clipboard.writeText(window.location.href).then(function () {

                Swal.fire({
                    title: "Enlace copiado",
                    text: "Ya puedes pegarlo donde quieras compartir la receta.",
                    icon: "success",
                });
            });
        });

        document.querySelectorAll('.btnDelete').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea eliminar la receta?",
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
