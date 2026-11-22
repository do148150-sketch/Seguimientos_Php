<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Seguimientos') · Seguimientos PHP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="bg-body-tertiary">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">

            <a class="navbar-brand fw-bold" href="{{ route('home.index') }}">
                <i class="bi bi-code-square"></i> Seguimientos
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home.index') }}">Todos los ejercicios</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <main class="container pb-5">

        @hasSection('heading')
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">@yield('heading')</h1>
                    <small class="text-muted">@yield('subheading')</small>
                </div>
                <a href="{{ route('home.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        @endif

        @yield('content')

    </main>

    <footer class="border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            Seguimientos · Arquitectura MVC con Laravel
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>

    {{-- Mensajes flash y errores de validacion, igual que en GolBet --}}
    @if (session()->has('message'))
        <script>
            const message = @json(session('message'));

            Swal.fire({
                title: message.content,
                icon: message.type,
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: "Error",
                icon: "error",
                html: `<ul class="list-unstyled mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>
