<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <script src="{{ asset('app.js') }}"></script>
    <meta name="theme-color" content="#ff7a00">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Catálogo Virtual">

    <link rel="apple-touch-icon" href="{{ asset('icons/icon_192.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon_192.png') }}">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #fff7ed, #f8fafc);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 580px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.14);
        }

        .login-brand {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            top: -80px;
            right: -80px;
        }

        .login-brand::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            bottom: -60px;
            left: -60px;
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .logo-box {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 25px;
        }

        .login-brand h1 {
            font-size: 36px;
            line-height: 1.1;
            margin-bottom: 18px;
        }

        .login-brand p {
            font-size: 16px;
            line-height: 1.7;
            opacity: 0.95;
            max-width: 380px;
        }

        .brand-card {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 22px;
            padding: 20px;
            backdrop-filter: blur(8px);
        }

        .brand-card strong {
            display: block;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .brand-card span {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-form-side {
            padding: 55px 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form-box {
            width: 100%;
            max-width: 380px;
        }

        .login-form-box .label {
            color: #f97316;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .login-form-box h2 {
            color: #111827;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .login-form-box .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-control {
            width: 100%;
            height: 50px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 0 15px;
            font-size: 15px;
            outline: none;
            background: #f9fafb;
            transition: all .2s ease;
        }

        .input-control:focus {
            border-color: #f97316;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0 24px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            font-size: 14px;
            cursor: pointer;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #f97316;
        }

        .forgot-link {
            color: #f97316;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 12px 24px rgba(249, 115, 22, 0.28);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(249, 115, 22, 0.35);
        }

        .btn-install-pwa {
            width: 100%;
            height: 50px;
            margin-top: 12px;
            border: 1px solid #f97316;
            background: #fff7ed;
            color: #ea580c;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btn-install-pwa:hover {
            background: #ffedd5;
            transform: translateY(-1px);
        }

        .register-text {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .register-text a {
            color: #f97316;
            font-weight: 700;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 850px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .login-brand {
                padding: 35px;
                min-height: 260px;
            }

            .login-brand h1 {
                font-size: 28px;
            }

            .brand-card {
                margin-top: 25px;
            }

            .login-form-side {
                padding: 35px 25px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .login-wrapper {
                border-radius: 22px;
            }

            .login-brand {
                padding: 28px;
            }

            .login-form-box h2 {
                font-size: 27px;
            }
        }
    </style>
</head>

<body>

<main class="login-wrapper">

    <section class="login-brand">
        <div class="brand-content">
            <div class="logo-box">
                🛒
            </div>

            <h1>Catálogo Virtual</h1>

            <p>
                Administra locales, productos, pedidos y clientes desde una sola plataforma.
            </p>
        </div>

        <div class="brand-card">
            <strong>Para negocios locales</strong>
            <span>Vende, organiza y muestra tus productos de forma fácil.</span>
        </div>
    </section>

    <section class="login-form-side">
        <div class="login-form-box">

            <p class="label">Bienvenido</p>
            <h2>Iniciar sesión</h2>
            <p class="subtitle">
                Entra a tu cuenta para continuar con tu panel.
            </p>

            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="input-group">
                    <label for="email">Correo electrónico</label>
                    <input
                        id="email"
                        class="input-control"
                        type="email"
                        name="email"
                        placeholder="ejemplo@gmail.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input
                        id="password"
                        class="input-control"
                        type="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Recordarme
                    </label>

                    {{-- Actívalo después si haces recuperación de contraseña --}}
                    {{-- <a href="#" class="forgot-link">Olvidé mi contraseña</a> --}}
                </div>

                <button type="submit" class="btn-login">
                    Entrar
                </button>

                <button type="button" id="btnInstalarPWA" class="btn-install-pwa" style="display: none;">
                    📲 Instalar app
                </button>
            </form>

            <p class="register-text">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}">Crear cuenta</a>
            </p>

        </div>
    </section>

</main>

{{-- Botón Instalar PWA --}}
<script>
    let eventoInstalacionPWA = null;
    const btnInstalarPWA = document.getElementById('btnInstalarPWA');

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        eventoInstalacionPWA = event;

        if (btnInstalarPWA) {
            btnInstalarPWA.style.display = 'block';
        }
    });

    if (btnInstalarPWA) {
        btnInstalarPWA.addEventListener('click', async function () {
            if (!eventoInstalacionPWA) {
                alert('La instalación aún no está disponible. Intenta desde Chrome o Edge.');
                return;
            }

            eventoInstalacionPWA.prompt();

            const resultado = await eventoInstalacionPWA.userChoice;

            if (resultado.outcome === 'accepted') {
                btnInstalarPWA.style.display = 'none';
            }

            eventoInstalacionPWA = null;
        });
    }

    window.addEventListener('appinstalled', function () {
        if (btnInstalarPWA) {
            btnInstalarPWA.style.display = 'none';
        }

        console.log('PWA instalada correctamente.');
    });
</script>

</body>
</html>