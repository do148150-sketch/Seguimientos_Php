@extends('layouts.app')

@section('title', 'Calculadora de Propinas')
@section('heading', '2 · Calculadora de Propinas')
@section('subheading', 'Calcula la propina según el total de la cuenta y el porcentaje')

@section('content')

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card shadow-sm">

                <div class="card-header">
                    <h2 class="h6 mb-0">Datos de la cuenta</h2>
                </div>

                <div class="card-body">

                    <form action="{{ route('tips.calculate') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="amount" class="form-label">Total de la cuenta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01"
                                       name="amount" id="amount"
                                       class="form-control"
                                       value="{{ old('amount', $resultado['amount'] ?? '50000') }}" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="percentage" class="form-label">Porcentaje de propina</label>
                            <select name="percentage" id="percentage" class="form-select">
                                @foreach ([5, 10, 15, 20, 25] as $p)
                                    <option value="{{ $p }}"
                                        {{ (old('percentage', $resultado['percentage'] ?? 10) == $p) ? 'selected' : '' }}>
                                        {{ $p }}%
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="people" class="form-label">Dividir entre</label>
                            <div class="input-group">
                                <input type="number" min="1" max="100"
                                       name="people" id="people"
                                       class="form-control"
                                       value="{{ old('people', $resultado['people'] ?? 1) }}" />
                                <span class="input-group-text">personas</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-calculator"></i> Calcular
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-7">

            @if ($resultado === null)

                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center text-center text-muted py-5">
                        <i class="bi bi-cash-coin fs-1 mb-2"></i>
                        <p class="mb-0">Ingresá los datos y presioná Calcular.</p>
                    </div>
                </div>

            @else

                <div class="card shadow-sm">

                    <div class="card-header">
                        <h2 class="h6 mb-0">Resultado</h2>
                    </div>

                    <div class="card-body">

                        <div class="row text-center mb-4">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block">Propina ({{ $resultado['percentage'] }}%)</small>
                                <span class="fs-3 fw-bold text-primary">
                                    ${{ number_format($resultado['tip'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Total a pagar</small>
                                <span class="fs-3 fw-bold">
                                    ${{ number_format($resultado['total'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td>Cuenta</td>
                                    <td class="text-end">${{ number_format($resultado['amount'], 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td>Propina</td>
                                    <td class="text-end">${{ number_format($resultado['tip'], 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-light fw-bold">
                                    <td>Total</td>
                                    <td class="text-end">${{ number_format($resultado['total'], 0, ',', '.') }}</td>
                                </tr>
                                @if ($resultado['people'] > 1)
                                    <tr>
                                        <td>Propina por persona</td>
                                        <td class="text-end">${{ number_format($resultado['tipPerPerson'], 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="table-primary fw-bold">
                                        <td>Paga cada uno ({{ $resultado['people'] }} personas)</td>
                                        <td class="text-end">${{ number_format($resultado['perPerson'], 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                    </div>

                </div>

            @endif

        </div>

    </div>

@endsection
