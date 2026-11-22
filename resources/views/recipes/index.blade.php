@extends('layouts.app')

@section('title', 'Plataforma de Recetas')
@section('heading', '8 · Plataforma de Recetas')
@section('subheading', 'Buscar, guardar y compartir recetas filtrando por tipo e ingredientes')

@section('content')

    <div class="row g-4">

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Publicar receta</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('recipes.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Nombre</label>
                            <input type="text"
                                   name="title"
                                   id="title"
                                   class="form-control"
                                   value="{{ old('title') }}"
                                   placeholder="Arepas de queso..." />
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label for="meal_type" class="form-label">Tipo</label>
                                <select name="meal_type" id="meal_type" class="form-select">
                                    @foreach ($mealTypes as $option)
                                        <option value="{{ $option }}" @selected(old('meal_type') === $option)>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <label for="minutes" class="form-label">Minutos</label>
                                <input type="number"
                                       name="minutes"
                                       id="minutes"
                                       class="form-control"
                                       min="1"
                                       value="{{ old('minutes', 30) }}" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ingredients" class="form-label">
                                Ingredientes <span class="text-muted">(uno por línea)</span>
                            </label>
                            <textarea name="ingredients"
                                      id="ingredients"
                                      rows="5"
                                      class="form-control"
                                      placeholder="2 tazas de harina&#10;1 taza de queso&#10;Sal al gusto">{{ old('ingredients') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Preparación</label>
                            <textarea name="instructions"
                                      id="instructions"
                                      rows="5"
                                      class="form-control"
                                      placeholder="Mezclar la harina con el agua...">{{ old('instructions') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Publicar
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    {{-- Búsqueda y filtros viajan por GET: la URL queda compartible --}}
                    <form action="{{ route('recipes.index') }}" method="GET">

                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ $search }}"
                                   placeholder="Buscar por nombre o por ingrediente..." />
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>

                        {{-- Cada filtro conserva la búsqueda activa --}}
                        <div class="d-flex flex-wrap gap-2">

                            <a href="{{ route('recipes.index', ['search' => $search]) }}"
                               class="btn btn-sm {{ $type === null && $only === null ? 'btn-dark' : 'btn-outline-dark' }}">
                                Todas
                            </a>

                            @foreach ($mealTypes as $option)
                                <a href="{{ route('recipes.index', ['search' => $search, 'type' => $option]) }}"
                                   class="btn btn-sm {{ $type === $option ? 'btn-dark' : 'btn-outline-dark' }}">
                                    {{ $option }}
                                    <span class="badge text-bg-light">{{ $counts[$option] ?? 0 }}</span>
                                </a>
                            @endforeach

                            <a href="{{ route('recipes.index', ['search' => $search, 'only' => 'favorites']) }}"
                               class="btn btn-sm {{ $only === 'favorites' ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="bi bi-bookmark-fill"></i> Guardadas
                                <span class="badge text-bg-light">{{ $favorites }}</span>
                            </a>

                        </div>

                    </form>

                </div>

            </div>

            @if ($recipes->isEmpty())

                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-egg-fried fs-1 d-block mb-2"></i>
                        @if ($search !== '' || $type !== null || $only !== null)
                            <p class="mb-0">Ninguna receta coincide con la búsqueda.</p>
                        @else
                            <p class="mb-0">Todavía no hay recetas. Publica la primera.</p>
                        @endif
                    </div>
                </div>

            @else

                <div class="row row-cols-1 row-cols-md-2 g-3">

                    @foreach ($recipes as $recipe)
                        <div class="col">

                            <div class="card h-100 shadow-sm">

                                <div class="card-body d-flex flex-column">

                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge text-bg-secondary">{{ $recipe->meal_type }}</span>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $recipe->minutes }} min
                                        </small>
                                    </div>

                                    <h3 class="h6 fw-bold">{{ $recipe->title }}</h3>

                                    <p class="small text-muted flex-grow-1">
                                        {{ count($recipe->ingredientList()) }} ingredientes ·
                                        {{ \Illuminate\Support\Str::limit(implode(', ', $recipe->ingredientList()), 90) }}
                                    </p>

                                    <div class="d-flex gap-2">

                                        <a href="{{ route('recipes.show', $recipe->id) }}"
                                           class="btn btn-sm btn-outline-primary flex-grow-1">
                                            Ver receta
                                        </a>

                                        <form action="{{ route('recipes.favorite', $recipe->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $recipe->is_favorite ? 'btn-warning' : 'btn-outline-warning' }}"
                                                    title="{{ $recipe->is_favorite ? 'Quitar de guardadas' : 'Guardar' }}">
                                                <i class="bi {{ $recipe->is_favorite ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
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
