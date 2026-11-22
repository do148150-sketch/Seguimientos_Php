@extends('layouts.app')

@section('title', 'Gestor de Gastos')
@section('heading', '4 · Gestor de Gastos')
@section('subheading', 'Registrar gastos diarios, categorizarlos y ver el resumen mensual')

@section('content')

    <div class="row g-4">

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Registrar gasto</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('expenses.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="description" class="form-label">¿En qué gastaste?</label>
                            <input type="text"
                                   name="description"
                                   id="description"
                                   class="form-control"
                                   value="{{ old('description') }}"
                                   placeholder="Almuerzo en la universidad..."
                                   autofocus />
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number"
                                       name="amount"
                                       id="amount"
                                       class="form-control"
                                       value="{{ old('amount') }}"
                                       step="1"
                                       min="1"
                                       placeholder="15000" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Categoría</label>
                            <select name="category" id="category" class="form-select">
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="spent_on" class="form-label">Fecha</label>
                            {{-- Por defecto hoy: la mayoria de gastos se registran el mismo dia --}}
                            <input type="date"
                                   name="spent_on"
                                   id="spent_on"
                                   class="form-control"
                                   max="{{ date('Y-m-d') }}"
                                   value="{{ old('spent_on', date('Y-m-d')) }}" />
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Registrar
                        </button>

                    </form>

                </div>

            </div>

            @if ($summary->isNotEmpty())

                <div class="card shadow-sm mt-4">

                    <div class="card-header">
                        <h2 class="h6 mb-0">Resumen por categoría</h2>
                    </div>

                    <div class="card-body">

                        @foreach ($summary as $category => $subtotal)

                            {{-- Cuanto pesa esta categoria dentro del total del mes --}}
                            @php $percent = $total > 0 ? round($subtotal * 100 / $total) : 0; @endphp

                            <div class="mb-3">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ $category }}</span>
                                    <span class="text-muted">
                                        ${{ number_format($subtotal, 0, ',', '.') }} · {{ $percent }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>

                        @endforeach

                    </div>

                </div>

            @endif

            @if ($trend->count() > 1)

                <div class="card shadow-sm mt-4">

                    <div class="card-header">
                        <h2 class="h6 mb-0">Últimos meses</h2>
                    </div>

                    <ul class="list-group list-group-flush">
                        @foreach ($trend as $period => $periodTotal)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <a href="{{ route('expenses.index', ['month' => $period]) }}"
                                   class="small text-decoration-none {{ $period === $month ? 'fw-bold' : '' }}">
                                    {{ $period }}
                                </a>
                                <span class="small text-muted">
                                    ${{ number_format($periodTotal, 0, ',', '.') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                </div>

            @endif

        </div>

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <h2 class="h6 mb-0">Gastos del mes</h2>

                    {{-- El mes viaja por GET: la URL queda compartible y recargable --}}
                    <form action="{{ route('expenses.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <input type="month"
                               name="month"
                               class="form-control form-control-sm"
                               value="{{ $month }}"
                               onchange="this.form.submit()" />
                        <noscript><button type="submit" class="btn btn-sm btn-secondary">Ver</button></noscript>
                    </form>

                </div>

                <div class="card-body bg-body-secondary d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total gastado en {{ $month }}</span>
                    <span class="h4 mb-0">${{ number_format($total, 0, ',', '.') }}</span>
                </div>

                @if ($expenses->isEmpty())

                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-wallet2 fs-1 d-block mb-2"></i>
                        <p class="mb-0">No hay gastos registrados en este mes.</p>
                    </div>

                @else

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Valor</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td class="text-nowrap">{{ $expense->spent_on->format('d/m/Y') }}</td>
                                        <td>{{ $expense->description }}</td>
                                        <td><span class="badge text-bg-light">{{ $expense->category }}</span></td>
                                        <td class="text-end text-nowrap">
                                            ${{ number_format($expense->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('expenses.delete', $expense->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger btnDelete" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
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

@endsection

@push('scripts')

    <script>

        document.querySelectorAll('.btnDelete').forEach(function (boton) {

            boton.addEventListener('click', function (event) {

                event.preventDefault();

                Swal.fire({
                    title: "¿Desea eliminar el gasto?",
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
