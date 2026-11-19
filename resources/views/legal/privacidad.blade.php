@extends('layouts.dashboard')

@section('title', 'Política de Privacidad')
@section('page-title', 'Política de Privacidad')
@section('page-subtitle', 'Uso y protección de datos personales')

@section('content')

<style>
    .legal-card {
        max-width: 950px;
        margin: 0 auto;
    }

    .legal-card h4 {
        font-weight: 800;
        margin-bottom: 12px;
    }

    .legal-card h5 {
        font-weight: 800;
        margin-top: 28px;
        margin-bottom: 10px;
    }

    .legal-card p,
    .legal-card li {
        color: #4b5563;
        line-height: 1.7;
    }

    .legal-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 14px 16px;
        color: #1e40af;
        margin-bottom: 22px;
    }
</style>

<div class="page-card legal-card">

    <h4>Política de Privacidad</h4>

    <div class="legal-note">
        <strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}
    </div>

    <p>
        Esta Política de Privacidad explica cómo se recopila, usa y protege la información
        de los usuarios que utilizan la plataforma como clientes o vendedores.
    </p>

    <h5>1. Información que podemos recopilar</h5>
    <p>
        Podemos recopilar datos como nombre, correo electrónico, teléfono, contraseña cifrada,
        tipo de cuenta, direcciones de entrega, ciudad, estado, información de pedidos,
        productos favoritos y actividad dentro de la plataforma.
    </p>

    <h5>2. Información de vendedores</h5>
    <p>
        Si el usuario registra un local, podemos almacenar información como nombre del local,
        descripción, teléfono, WhatsApp, correo, dirección, ciudad, estado, horarios,
        productos, imágenes, zonas de entrega y costos de envío.
    </p>

    <h5>3. Uso de la información</h5>
    <p>
        La información se usa para crear cuentas, iniciar sesión, mostrar locales disponibles
        por zona, procesar pedidos, calcular costos de envío, administrar productos,
        guardar direcciones y permitir la comunicación básica entre cliente y vendedor.
    </p>

    <h5>4. Direcciones y zonas de entrega</h5>
    <p>
        Las direcciones del cliente se usan para determinar qué locales pueden atender su zona
        y para entregar pedidos a domicilio cuando el usuario elige esa opción.
    </p>

    <h5>5. Ubicación y Google Maps</h5>
    <p>
        La plataforma puede usar servicios de Google Maps o Google Places para ayudar a completar
        ciudades, estados, direcciones o zonas de entrega. El uso de estos servicios también puede
        estar sujeto a las políticas de Google.
    </p>

    <h5>6. Contraseñas</h5>
    <p>
        Las contraseñas deben almacenarse cifradas. El usuario es responsable de elegir una
        contraseña segura y no compartirla con terceros.
    </p>

    <h5>7. Con quién se comparte la información</h5>
    <p>
        Los datos necesarios para completar un pedido pueden compartirse entre cliente y vendedor,
        por ejemplo nombre, teléfono, dirección de entrega, notas del pedido y productos solicitados.
        No se debe compartir información innecesaria fuera del proceso de compra.
    </p>

    <h5>8. Seguridad</h5>
    <p>
        Se deben aplicar medidas razonables para proteger las cuentas, pedidos y datos personales.
        Sin embargo, ningún sistema es completamente infalible, por lo que se recomienda al usuario
        cuidar sus datos de acceso.
    </p>

    <h5>9. Eliminación o corrección de datos</h5>
    <p>
        El usuario puede solicitar la corrección o eliminación de información cuando sea necesario,
        dependiendo de las funciones disponibles dentro de la plataforma y de obligaciones técnicas
        o legales de conservación.
    </p>

    <h5>10. Cambios en esta política</h5>
    <p>
        Esta Política de Privacidad puede actualizarse cuando se agreguen nuevas funciones o cambie
        el funcionamiento de la plataforma.
    </p>

    <hr class="my-4">

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary fw-bold">
        <i class="fa-solid fa-arrow-left"></i>
        Volver
    </a>

</div>

@endsection