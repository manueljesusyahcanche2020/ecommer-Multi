<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>

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

        .register-wrapper {
            width: 100%;
            max-width: 1050px;
            min-height: 620px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.14);
        }

        .register-brand {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .register-brand::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            top: -80px;
            right: -80px;
        }

        .register-brand::after {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            bottom: -70px;
            left: -70px;
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

        .register-brand h1 {
            font-size: 36px;
            line-height: 1.1;
            margin-bottom: 18px;
        }

        .register-brand p {
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

        .register-form-side {
            padding: 45px 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-form-box {
            width: 100%;
            max-width: 460px;
        }

        .label {
            color: #f97316;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .register-form-box h2 {
            color: #111827;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 26px;
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

        .alert-error ul {
            padding-left: 18px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .input-group {
            margin-bottom: 2px;
        }

        .input-group.full {
            grid-column: 1 / -1;
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

        select.input-control {
            cursor: pointer;
        }

        .account-note {
            grid-column: 1 / -1;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.5;
        }
        .terms-box {
            grid-column: 1 / -1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.5;
            }

        .terms-box label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .terms-box input {
            margin-top: 3px;
            width: 16px;
            height: 16px;
        }

        .terms-box a {
            color: #ea580c;
            font-weight: 700;
            text-decoration: none;
        }

        .terms-box a:hover {
            text-decoration: underline;
        }

        .btn-register {
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
            margin-top: 22px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(249, 115, 22, 0.35);
        }

        .login-text {
            text-align: center;
            margin-top: 22px;
            color: #6b7280;
            font-size: 14px;
        }

        .login-text a {
            color: #f97316;
            font-weight: 700;
            text-decoration: none;
        }

        .login-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .register-wrapper {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .register-brand {
                padding: 35px;
                min-height: 260px;
            }

            .register-brand h1 {
                font-size: 28px;
            }

            .brand-card {
                margin-top: 25px;
            }

            .register-form-side {
                padding: 35px 25px;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 12px;
            }

            .register-wrapper {
                border-radius: 22px;
            }

            .register-brand {
                padding: 28px;
            }

            .register-form-box h2 {
                font-size: 27px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <main class="register-wrapper">

        <section class="register-brand">
            <div class="brand-content">
                <div class="logo-box">
                    🛍️
                </div>

                <h1>Crea tu cuenta</h1>

                <p>
                    Únete al catálogo virtual para comprar productos o administrar tu propio local.
                </p>
            </div>

            <div class="brand-card">
                <strong>Clientes y vendedores</strong>
                <span>Compra, vende y organiza pedidos desde una sola plataforma.</span>
            </div>
        </section>

        <section class="register-form-side">
            <div class="register-form-box">

                <p class="label">Registro</p>
                <h2>Crear cuenta</h2>
                <p class="subtitle">
                    Completa tus datos para comenzar a usar el sistema.
                </p>

                @if ($errors->any())
                    <div class="alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <div class="form-grid">

                        <div class="input-group full">
                            <label for="name">Nombre completo</label>
                            <input
                                id="name"
                                class="input-control"
                                type="text"
                                name="name"
                                placeholder="Ej. Manuel Yah"
                                value="{{ old('name') }}"
                                required
                                autofocus
                            >
                        </div>

                        <div class="input-group full">
                            <label for="email">Correo electrónico</label>
                            <input
                                id="email"
                                class="input-control"
                                type="email"
                                name="email"
                                placeholder="ejemplo@gmail.com"
                                value="{{ old('email') }}"
                                required
                            >
                        </div>

                        <div class="input-group">
                            <label for="telefono">Teléfono</label>
                            <input
                                id="telefono"
                                class="input-control"
                                type="text"
                                name="telefono"
                                placeholder="999 123 4567"
                                value="{{ old('telefono') }}"
                            >
                        </div>

                        <div class="input-group">
                            <label for="tipo_cuenta">Tipo de cuenta</label>
                            <select id="tipo_cuenta" class="input-control" name="tipo_cuenta" required>
                                <option value="">Selecciona una opción</option>
                                <option value="cliente" {{ old('tipo_cuenta') == 'cliente' ? 'selected' : '' }}>
                                    Cliente
                                </option>
                                <option value="vendedor" {{ old('tipo_cuenta') == 'vendedor' ? 'selected' : '' }}>
                                    Vendedor
                                </option>
                            </select>
                        </div>

                        <div class="account-note">
                            Si eliges vendedor, podrás administrar locales, productos, stock y pedidos.
                        </div>

                        <div class="input-group">
                            <label for="password">Contraseña</label>
                            <input
                                id="password"
                                class="input-control"
                                type="password"
                                name="password"
                                placeholder="Mínimo 8 caracteres"
                                required
                            >
                        </div>

                        <div class="input-group">
                            <label for="password_confirmation">Confirmar contraseña</label>
                            <input
                                id="password_confirmation"
                                class="input-control"
                                type="password"
                                name="password_confirmation"
                                placeholder="Repite tu contraseña"
                                required
                            >
                        </div>
                        <div class="terms-box">
                            <label>
                                <input
                                    type="checkbox"
                                    name="acepta_terminos"
                                    value="1"
                                    required
                                    {{ old('acepta_terminos') ? 'checked' : '' }}
                                >

                                <span>
                                    Acepto los
                                    <a href="{{ route('terminos') }}" target="_blank">
                                        Términos y Condiciones
                                    </a>
                                    y la
                                    <a href="{{ route('privacidad') }}" target="_blank">
                                        Política de Privacidad
                                    </a>.
                                </span>
                            </label>
                        </div>

                    </div>

                    <button type="submit" class="btn-register">
                        Crear cuenta
                    </button>
                </form>

                <p class="login-text">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                </p>

            </div>
        </section>

    </main>

</body>
</html>