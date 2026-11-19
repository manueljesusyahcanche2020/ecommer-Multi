<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ff7a00">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Catálogo Virtual">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon_192.png') }}">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8fafc;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #111827;
        }

        .dashboard-shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 270px;
            background: #111827;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 22px 18px;
            overflow-y: auto;
            transition: all .25s ease;
            z-index: 1000;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            padding: 8px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .brand-text strong {
            display: block;
            font-size: 17px;
        }

        .brand-text span {
            font-size: 12px;
            color: #9ca3af;
        }

        .user-box {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 18px;
            padding: 14px;
            margin-bottom: 24px;
        }

        .user-box small {
            color: #9ca3af;
        }

        .user-box strong {
            display: block;
            margin-top: 3px;
            font-size: 15px;
        }

        .role-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(249, 115, 22, .15);
            color: #fdba74;
            font-size: 12px;
            font-weight: 700;
        }

        .menu-title {
            font-size: 12px;
            color: #9ca3af;
            margin: 18px 10px 8px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .nav-link-dashboard {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #d1d5db;
            padding: 12px 13px;
            border-radius: 14px;
            text-decoration: none;
            margin-bottom: 6px;
            transition: all .2s ease;
            font-size: 15px;
        }

        .nav-link-dashboard:hover {
            background: #1f2937;
            color: white;
        }

        .nav-link-dashboard.active {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        .nav-link-dashboard i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 270px;
            width: calc(100% - 270px);
            min-height: 100vh;
        }

        .topbar {
            height: 72px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar-title {
            flex: 1;
            min-width: 0;
        }

        .topbar h1 {
            font-size: 22px;
            margin: 0;
            font-weight: 800;
        }

        .topbar p {
            margin: 2px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-notification {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .btn-logout {
            border: none;
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            transition: all .2s ease;
        }

        .btn-logout:hover {
            background: #fecaca;
        }

        .btn-menu-mobile {
            display: none;
            border: none;
            background: #f3f4f6;
            border-radius: 12px;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
        }

        .content-wrapper {
            padding: 28px;
        }

        .page-card {
            background: white;
            border-radius: 22px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            padding: 24px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .05);
        }

        .stat-card .icon {
            width: 46px;
            height: 46px;
            border-radius: 15px;
            background: #fff7ed;
            color: #f97316;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            margin-bottom: 14px;
        }

        .stat-card h3 {
            font-size: 26px;
            margin: 0;
            font-weight: 800;
        }

        .stat-card p {
            color: #6b7280;
            margin: 4px 0 0;
        }

        /* CAPA OSCURA DE FONDO (BACKDROP) */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(14, 19, 34, 0.98); /* Oscurece eficientemente el fondo */
            z-index: 998; /* Debajo del sidebar (1000) pero encima del contenido */
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000; /* Asegura que el menú esté por encima del backdrop */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .topbar {
                height: 68px;
                padding: 0 14px;
                gap: 12px;
            }

            .btn-notification {
                order: 1;
            }

            .topbar-title {
                order: 2;
                flex: 1;
                min-width: 0;
            }

            .topbar h1 {
                font-size: 20px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .topbar p {
                display: none;
            }

            .topbar-actions {
                display: none;
            }

            .btn-menu-mobile {
                display: flex;
                align-items: center;
                justify-content: center;
                order: 3;
            }

            .content-wrapper {
                padding: 18px;
            }

            .page-card {
                padding: 16px;
                border-radius: 18px;
            }

            .topbar-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn-logout {
                border: none;
                background: #fee2e2;
                color: #991b1b;
                font-weight: 700;
                border-radius: 14px;
                padding: 10px 16px;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: 0.2s ease;
            }

            .btn-logout:hover {
                background: #fecaca;
                color: #7f1d1d;
            }
        }

        @media (max-width: 768px) {
            .topbar-actions {
                margin-left: auto;
            }

            .btn-logout {
                width: 42px;
                height: 42px;
                padding: 0;
                justify-content: center;
                border-radius: 12px;
            }

            .btn-logout span {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

@auth
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/service-worker.js')
                .then(function () {
                    console.log('Service Worker registrado correctamente.');
                })
                .catch(function (error) {
                    console.log('Error al registrar Service Worker:', error);
                });
        });
    }
</script>

<script>
    let notificacionesAnteriores = null;
    let sonidoPermitido = false;

    const sonidoNotificacion = new Audio("{{ asset('sounds/notificacion.mp3') }}");
    sonidoNotificacion.volume = 0.7;

    document.addEventListener('click', function () {
        sonidoPermitido = true;

        sonidoNotificacion.play()
            .then(() => {
                sonidoNotificacion.pause();
                sonidoNotificacion.currentTime = 0;
            })
            .catch(() => {});
    }, { once: true });

    async function revisarNotificaciones() {
        try {
            const response = await fetch("{{ route('notificaciones.contador') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            const total = Number(data.total || 0);

            if (notificacionesAnteriores === null) {
                notificacionesAnteriores = total;
                actualizarBadgeNotificaciones(total);
                return;
            }

            if (total > notificacionesAnteriores) {
                actualizarBadgeNotificaciones(total);

                if (sonidoPermitido) {
                    sonidoNotificacion.currentTime = 0;
                    sonidoNotificacion.play().catch(() => {});
                }
            }

            notificacionesAnteriores = total;

        } catch (error) {
            console.log('Error revisando notificaciones:', error);
        }
    }

    function actualizarBadgeNotificaciones(total) {
        const badge = document.getElementById('badgeNotificaciones');

        if (!badge) return;

        if (total > 0) {
            badge.textContent = total;
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '';
            badge.classList.add('d-none');
        }
    }

    revisarNotificaciones();
    setInterval(revisarNotificaciones, 15000);
</script>
@endauth

<body>

@php
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();

    $menus = collect();

    if ($user && $user->role) {
        $menus = $user->role->menus()
            ->wherePivot('permitido', 1)
            ->where('menus.estado', 1)
            ->orderBy('orden')
            ->get();
    }

    if (!function_exists('resolverRutaMenu')) {
        function resolverRutaMenu($ruta, $roleId) {
            if ($ruta === 'dashboard') {
                if ($roleId == 1 && Route::has('admin.dashboard')) {
                    return route('admin.dashboard');
                }

                if ($roleId == 2 && Route::has('vendedor.dashboard')) {
                    return route('vendedor.dashboard');
                }

                if ($roleId == 3 && Route::has('cliente.dashboard')) {
                    return route('cliente.dashboard');
                }

                return '#';
            }

            if (Route::has($ruta)) {
                return route($ruta);
            }

            if ($roleId == 1 && Route::has('admin.' . $ruta)) {
                return route('admin.' . $ruta);
            }

            if ($roleId == 2 && Route::has('vendedor.' . $ruta)) {
                return route('vendedor.' . $ruta);
            }

            if ($roleId == 3 && Route::has('cliente.' . $ruta)) {
                return route('cliente.' . $ruta);
            }

            return '#';
        }
    }

    if (!function_exists('menuActivo')) {
        function menuActivo($ruta, $roleId) {
            if ($ruta === 'dashboard') {
                if ($roleId == 1) {
                    return request()->routeIs('admin.dashboard');
                }

                if ($roleId == 2) {
                    return request()->routeIs('vendedor.dashboard');
                }

                if ($roleId == 3) {
                    return request()->routeIs('cliente.dashboard');
                }
            }

            return request()->routeIs($ruta)
                || request()->routeIs($ruta . '.*')
                || request()->routeIs('admin.' . $ruta)
                || request()->routeIs('admin.' . $ruta . '.*')
                || request()->routeIs('vendedor.' . $ruta)
                || request()->routeIs('vendedor.' . $ruta . '.*')
                || request()->routeIs('cliente.' . $ruta)
                || request()->routeIs('cliente.' . $ruta . '.*');
        }
    }

    $rolNombre = 'Cliente';

    if ($user && $user->role_id == 1) {
        $rolNombre = 'Administrador';
    } elseif ($user && $user->role_id == 2) {
        $rolNombre = 'Vendedor';
    }
@endphp

<!-- CAPA OSCURA DE FONDO (Fondo negro semitransparente) -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

<div class="dashboard-shell">

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-store"></i>
            </div>

            <div class="brand-text">
                <strong>Catálogo Virtual</strong>
                <span>Panel</span>
            </div>
        </div>

        @auth
            <div class="user-box">
                <small>Sesión iniciada como</small>
                <strong>{{ Auth::user()->name }}</strong>
                <span class="role-badge">{{ $rolNombre }}</span>
            </div>
        @endauth

        <div class="menu-title">Menú</div>

        @forelse($menus as $menu)
            <a href="{{ resolverRutaMenu($menu->ruta, $user->role_id) }}"
               class="nav-link-dashboard {{ menuActivo($menu->ruta, $user->role_id) ? 'active' : '' }}">

                @if($menu->icono)
                    <i class="{{ $menu->icono }}"></i>
                @else
                    <i class="fa-solid fa-circle"></i>
                @endif

                {{ $menu->nombre }}
            </a>
        @empty
            <div class="text-secondary px-3">
                No tienes menús asignados.
            </div>
        @endforelse
    </aside>

    <main class="main-content">

        <header class="topbar">

            {{-- IZQUIERDA: NOTIFICACIONES --}}
            @auth
                @php
                    $notificacionesNoLeidas = \App\Models\Notificacion::where('user_id', Auth::id())
                        ->where('leida', 0)
                        ->count();
                @endphp

                <a href="{{ route('notificaciones.index') }}"
                   class="btn btn-outline-dark fw-bold position-relative btn-notification">
                    <i class="fa-solid fa-bell"></i>

                    <span id="badgeNotificaciones"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $notificacionesNoLeidas > 0 ? '' : 'd-none' }}">
                        {{ $notificacionesNoLeidas > 0 ? $notificacionesNoLeidas : '' }}
                    </span>
                </a>
            @endauth

            {{-- CENTRO: TÍTULO --}}
            <div class="topbar-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Bienvenido al panel de control')</p>
            </div>

            {{-- DERECHA: SALIR EN ESCRITORIO --}}
            <div class="topbar-actions">
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf

                        <button class="btn-logout" type="submit">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Salir</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark fw-bold">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Iniciar sesión</span>
                    </a>
                @endauth
            </div>

            {{-- DERECHA: MENÚ EN MÓVIL --}}
            <button class="btn-menu-mobile" type="button" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>

        </header>

        <section class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </section>

    </main>

</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }
</script>

@stack('scripts')

</body>
</html>