@extends('layouts.app')

@section('title', 'Calendario de Eventos')
@section('heading', '7 · Calendario de Eventos')
@section('subheading', 'Agregar, editar y eliminar eventos, con recordatorios')

@section('content')

    @php
        $hoy = now()->format('Y-m-d');
        $mesAnterior = \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01')->subMonth()->format('Y-m');
        $mesSiguiente = \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01')->addMonth()->format('Y-m');
    @endphp

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <a href="{{ route('events.index', ['month' => $mesAnterior]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>

                    <h2 class="h6 mb-0 text-capitalize">
                        {{ $firstDay->translatedFormat('F Y') }}
                    </h2>

                    <a href="{{ route('events.index', ['month' => $mesSiguiente]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>

                </div>

                <div class="table-responsive">

                    {{-- table-layout fija las 7 columnas: los títulos largos se
                         recortan en vez de ensanchar el calendario --}}
                    <table class="table table-bordered mb-0 text-center align-top"
                           style="table-layout: fixed;">

                        <thead class="table-light">
                            <tr>
                                <th class="small">Lun</th>
                                <th class="small">Mar</th>
                                <th class="small">Mié</th>
                                <th class="small">Jue</th>
                                <th class="small">Vie</th>
                                <th class="small">Sáb</th>
                                <th class="small">Dom</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($weeks as $week)
                                <tr>
                                    @foreach ($week as $day)

                                        @php
                                            $clave = $day->format('Y-m-d');
                                            $delMes = $day->format('Y-m') === $month;
                                            $delDia = $eventsByDay[$clave] ?? collect();
                                        @endphp

                                        <td class="p-1 {{ $delMes ? '' : 'bg-body-secondary' }}"
                                            style="height: 90px; width: 14.28%;">

                                            {{-- Al hacer clic en un día, el formulario se abre con esa fecha --}}
                                            <a href="{{ route('events.index', ['month' => $month, 'day' => $clave]) }}"
                                               class="d-block text-decoration-none small
                                                      {{ $delMes ? 'text-body' : 'text-muted' }}">
                                                <span class="{{ $clave === $hoy ? 'badge text-bg-primary' : '' }}">
                                                    {{ $day->day }}
                                                </span>
                                            </a>

                                            @foreach ($delDia as $event)
                                                <a href="{{ route('events.index', ['month' => $month, 'edit' => $event->id]) }}"
                                                   class="d-block text-start text-truncate text-decoration-none
                                                          badge text-bg-primary w-100 mt-1"
                                                   title="{{ $event->starts_at->format('H:i') }} · {{ $event->title }}">
                                                    {{ $event->starts_at->format('H:i') }} {{ $event->title }}
                                                </a>
                                            @endforeach

                                        </td>

                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">{{ $editing ? 'Editar evento' : 'Nuevo evento' }}</h2>
                    @if ($editing)
                        <a href="{{ route('events.index', ['month' => $month]) }}"
                           class="btn btn-sm btn-outline-secondary">Cancelar</a>
                    @endif
                </div>

                <div class="card-body">

                    {{-- El mismo formulario crea y edita: cambia la acción y el verbo --}}
                    <form action="{{ $editing ? route('events.update', $editing->id) : route('events.store') }}"
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
                                   placeholder="Entrega del proyecto..." />
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label for="starts_at" class="form-label">Fecha</label>
                                <input type="date"
                                       name="starts_at"
                                       id="starts_at"
                                       class="form-control"
                                       value="{{ old('starts_at', $editing ? $editing->starts_at->format('Y-m-d') : $pickedDay) }}" />
                            </div>
                            <div class="col-5">
                                <label for="starts_time" class="form-label">Hora</label>
                                <input type="time"
                                       name="starts_time"
                                       id="starts_time"
                                       class="form-control"
                                       value="{{ old('starts_time', $editing ? $editing->starts_at->format('H:i') : '09:00') }}" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reminder_minutes" class="form-label">Recordatorio</label>
                            <select name="reminder_minutes" id="reminder_minutes" class="form-select">
                                @foreach ($reminders as $value => $label)
                                    <option value="{{ $value }}"
                                        @selected((string) old('reminder_minutes', $editing->reminder_minutes ?? '') === (string) $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción <span class="text-muted">(opcional)</span></label>
                            <textarea name="description"
                                      id="description"
                                      rows="3"
                                      class="form-control">{{ old('description', $editing->description ?? '') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi {{ $editing ? 'bi-save' : 'bi-plus-lg' }}"></i>
                            {{ $editing ? 'Guardar cambios' : 'Agregar evento' }}
                        </button>

                        @if ($editing)
                            <a href="{{ route('events.index', ['month' => $editing->starts_at->format('Y-m')]) }}"
                               class="btn btn-link btn-sm w-100 mt-2 text-decoration-none">
                                Ir al mes de este evento
                            </a>
                        @endif

                    </form>

                    @if ($editing)
                        <form action="{{ route('events.delete', $editing->id) }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 btnDelete">
                                <i class="bi bi-trash"></i> Eliminar evento
                            </button>
                        </form>
                    @endif

                </div>

            </div>

            <div class="card shadow-sm mt-4">

                <div class="card-header">
                    <h2 class="h6 mb-0"><i class="bi bi-bell"></i> Próximos eventos</h2>
                </div>

                @if ($upcoming->isEmpty())

                    <div class="card-body text-center text-muted py-4">
                        <p class="mb-0 small">No hay eventos próximos.</p>
                    </div>

                @else

                    <ul class="list-group list-group-flush">
                        @foreach ($upcoming as $event)

                            @php
                                // El recordatorio ya está activo si falta menos
                                // tiempo del que pidió el usuario
                                $faltanMinutos = now()->diffInMinutes($event->starts_at, false);
                                $avisando = $event->reminder_minutes !== null
                                         && $faltanMinutos <= $event->reminder_minutes;
                            @endphp

                            <li class="list-group-item {{ $avisando ? 'list-group-item-warning' : '' }}">

                                <div class="d-flex justify-content-between align-items-start">
                                    <a href="{{ route('events.index', ['month' => $event->starts_at->format('Y-m'), 'edit' => $event->id]) }}"
                                       class="text-decoration-none fw-bold small">
                                        {{ $event->title }}
                                    </a>
                                    @if ($avisando)
                                        <i class="bi bi-bell-fill text-warning" title="Recordatorio activo"></i>
                                    @endif
                                </div>

                                <small class="text-muted d-block">
                                    {{ $event->starts_at->translatedFormat('D d M · H:i') }}
                                    · {{ $event->starts_at->diffForHumans() }}
                                </small>

                                @if ($event->reminder_minutes !== null)
                                    <small class="text-muted d-block">
                                        <i class="bi bi-alarm"></i>
                                        {{ $reminders[(string) $event->reminder_minutes] ?? 'Con recordatorio' }}
                                    </small>
                                @endif

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
                    title: "¿Desea eliminar el evento?",
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
