@extends('layouts.app')

@section('title', 'Sistema de Reservas')
@section('heading', '5 · Sistema de Reservas')
@section('subheading', 'Reservar citas en línea mostrando disponibilidad y confirmación')

@section('content')

    @if ($services->isEmpty())

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            No hay servicios cargados. Ejecuta <code>php artisan db:seed</code> para cargar el catálogo.
        </div>

    @else

        <div class="row g-4">

            <div class="col-lg-7">

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h2 class="h6 mb-0">1 · Elige servicio y día</h2>
                    </div>

                    <div class="card-body">

                        {{-- Servicio y dia viajan por GET: la disponibilidad se puede recargar y compartir --}}
                        <form action="{{ route('reservations.index') }}" method="GET" class="row g-3">

                            <div class="col-sm-7">
                                <label for="service_id" class="form-label">Servicio</label>
                                <select name="service_id" id="service_id" class="form-select" onchange="this.form.submit()">
                                    @foreach ($services as $option)
                                        <option value="{{ $option->id }}" @selected($option->id === $service->id)>
                                            {{ $option->name }} · ${{ number_format($option->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-5">
                                <label for="date" class="form-label">Día</label>
                                <input type="date"
                                       name="date"
                                       id="date"
                                       class="form-control"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ $date }}"
                                       onchange="this.form.submit()" />
                            </div>

                            <noscript>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-secondary">Ver disponibilidad</button>
                                </div>
                            </noscript>

                        </form>

                        <p class="text-muted small mt-3 mb-0">
                            <i class="bi bi-info-circle"></i>
                            {{ $service->description }}
                            Duración: {{ $service->duration_minutes }} minutos.
                            Atención de lunes a sábado, de 8:00 a 17:00.
                        </p>

                    </div>

                </div>

                <div class="card shadow-sm mt-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">2 · Disponibilidad</h2>
                        <span class="small text-muted">
                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l d \d\e F') }}
                        </span>
                    </div>

                    <div class="card-body">

                        @if ($closed)

                            <div class="text-center text-muted py-4">
                                <i class="bi bi-door-closed fs-1 d-block mb-2"></i>
                                <p class="mb-0">Los domingos no hay atención. Elige otro día.</p>
                            </div>

                        @else

                            <div class="row row-cols-2 row-cols-sm-3 g-2">

                                @foreach ($slots as $slot)
                                    <div class="col">

                                        @if ($slot['available'])

                                            {{-- Libre: al hacer clic se carga la hora en el formulario de la derecha --}}
                                            <button type="button"
                                                    class="btn btn-outline-success w-100 btnSlot"
                                                    data-time="{{ $slot['time'] }}">
                                                <i class="bi bi-clock"></i> {{ $slot['time'] }}
                                            </button>

                                        @elseif ($slot['past'])

                                            <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                                <s>{{ $slot['time'] }}</s>
                                            </button>

                                        @else

                                            <button type="button"
                                                    class="btn btn-outline-danger w-100"
                                                    disabled
                                                    title="Reservado por {{ $slot['reservation']->customer_name }}">
                                                <i class="bi bi-x-lg"></i> {{ $slot['time'] }}
                                            </button>

                                        @endif

                                    </div>
                                @endforeach

                            </div>

                            <div class="d-flex gap-3 mt-3 small text-muted">
                                <span><i class="bi bi-square-fill text-success"></i> Disponible</span>
                                <span><i class="bi bi-square-fill text-danger"></i> Ocupado</span>
                                <span><i class="bi bi-square-fill text-secondary"></i> Ya pasó</span>
                            </div>

                        @endif

                    </div>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h2 class="h6 mb-0">3 · Tus datos</h2>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('reservations.store') }}" method="POST">
                            @csrf

                            {{-- El servicio y el dia ya quedaron elegidos arriba --}}
                            <input type="hidden" name="service_id" value="{{ $service->id }}" />
                            <input type="hidden" name="date" value="{{ $date }}" />

                            <div class="mb-3">
                                <label for="time" class="form-label">Hora elegida</label>
                                <input type="text"
                                       name="time"
                                       id="time"
                                       class="form-control"
                                       value="{{ old('time') }}"
                                       placeholder="Elige una franja verde"
                                       readonly />
                            </div>

                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nombre</label>
                                <input type="text"
                                       name="customer_name"
                                       id="customer_name"
                                       class="form-control"
                                       value="{{ old('customer_name') }}"
                                       placeholder="Diego Ospina" />
                            </div>

                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Correo</label>
                                <input type="email"
                                       name="customer_email"
                                       id="customer_email"
                                       class="form-control"
                                       value="{{ old('customer_email') }}"
                                       placeholder="correo@ejemplo.com" />
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-calendar-plus"></i> Reservar
                            </button>

                            <p class="text-muted small text-center mt-2 mb-0">
                                La reserva queda <strong>pendiente</strong> hasta que la confirmes.
                            </p>

                        </form>

                    </div>

                </div>

            </div>

            <div class="col-12">

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h2 class="h6 mb-0">Reservas registradas</h2>
                    </div>

                    @if ($reservations->isEmpty())

                        <div class="card-body text-center text-muted py-5">
                            <i class="bi bi-calendar-check fs-1 d-block mb-2"></i>
                            <p class="mb-0">Todavía no hay reservas.</p>
                        </div>

                    @else

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha y hora</th>
                                        <th>Servicio</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($reservations as $reservation)
                                        <tr>
                                            <td class="text-nowrap">
                                                {{ $reservation->reserved_at->format('d/m/Y · H:i') }}
                                            </td>
                                            <td>{{ $reservation->service->name }}</td>
                                            <td>
                                                {{ $reservation->customer_name }}
                                                <small class="text-muted d-block">{{ $reservation->customer_email }}</small>
                                            </td>
                                            <td>
                                                @if ($reservation->status === 'confirmada')
                                                    <span class="badge text-bg-success">Confirmada</span>
                                                @else
                                                    <span class="badge text-bg-warning">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">

                                                @if ($reservation->status === 'pendiente')
                                                    <form action="{{ route('reservations.confirm', $reservation->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm btn-success" title="Confirmar">
                                                            <i class="bi bi-check-lg"></i> Confirmar
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btnCancel" title="Cancelar">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                    @endif

                </div>

            </div>

        </div>

    @endif

@endsection

@push('scripts')

    <script>

        // Al elegir una franja verde se copia la hora al formulario de datos
        document.querySelectorAll('.btnSlot').forEach(function (boton) {

            boton.addEventListener('click', function () {

                document.querySelectorAll('.btnSlot').forEach(function (otro) {
                    otro.classList.remove('btn-success');
                    otro.classList.add('btn-outline-success');
                });

                boton.classList.remove('btn-outline-success');
                boton.classList.add('btn-success');

                document.getElementById('time').value = boton.dataset.time;
                document.getElementById('customer_name').focus();
            });
        });

        document.querySelectorAll('.btnCancel').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea cancelar la reserva?",
                    text: "La franja quedará libre de nuevo",
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
