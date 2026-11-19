@extends('layouts.dashboard')

@section('title', 'Términos y Condiciones')
@section('page-title', 'Términos y Condiciones')
@section('page-subtitle', 'Condiciones de uso de la plataforma')

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
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 16px;
        padding: 14px 16px;
        color: #9a3412;
        margin-bottom: 22px;
    }
</style>

<div class="page-card legal-card">

    <h4>Términos y Condiciones</h4>

    <div class="legal-note">
        <strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}
    </div>

    <p>
        Al registrarte y usar esta plataforma, aceptas estos Términos y Condiciones.
        La plataforma funciona como un catálogo digital y medio de contacto entre clientes
        y locales registrados.
    </p>

    <h5>1. Uso de la plataforma</h5>
    <p>
        La plataforma permite a los usuarios consultar locales, productos, variantes,
        precios, zonas de entrega y realizar pedidos. El usuario se compromete a usarla
        de forma correcta, sin intentar dañar, alterar o acceder a información que no le corresponde.
    </p>

    <h5>2. Cuentas de usuario</h5>
    <p>
        El usuario es responsable de mantener segura su cuenta, correo y contraseña.
        Cualquier actividad realizada desde su cuenta será considerada responsabilidad del titular.
    </p>

    <h5>3. Locales y vendedores</h5>
    <p>
        Los vendedores son responsables de la información publicada en sus locales,
        incluyendo productos, precios, imágenes, horarios, stock, zonas de entrega,
        costos de envío y disponibilidad.
    </p>

    <h5>4. Productos y precios</h5>
    <p>
        Los precios, descripciones, imágenes y existencias pueden cambiar. La plataforma
        muestra la información registrada por cada vendedor, pero no garantiza que un producto
        esté disponible si el vendedor no actualiza su información.
    </p>

    <h5>5. Pedidos</h5>
    <p>
        Los pedidos están sujetos a disponibilidad de producto, stock, horario del local,
        zona de entrega, costo de envío y confirmación del vendedor. Un pedido puede ser
        rechazado o cancelado si no puede ser atendido.
    </p>

    <h5>6. Entrega a domicilio y recoger en local</h5>
    <p>
        Cada local decide si permite entrega a domicilio, recoger en local o ambas opciones.
        Las zonas de entrega y costos dependen de cada vendedor.
    </p>

    <h5>7. Cancelaciones</h5>
    <p>
        La cancelación de pedidos dependerá del estado del pedido. Si el pedido ya fue aceptado,
        preparado, enviado o entregado, puede que ya no sea posible cancelarlo.
    </p>

    <h5>8. Responsabilidad</h5>
    <p>
        La plataforma sirve como intermediario digital para facilitar el contacto entre clientes
        y vendedores. No se hace responsable por acuerdos externos, entregas fuera de la plataforma,
        productos defectuosos, información falsa publicada por vendedores o conflictos entre usuarios.
    </p>

    <h5>9. Uso indebido</h5>
    <p>
        Está prohibido usar la plataforma para publicar contenido falso, ofensivo, ilegal,
        engañoso o que afecte a otros usuarios. Las cuentas que incumplan estas reglas podrán
        ser suspendidas o eliminadas.
    </p>

    <h5>10. Cambios en los términos</h5>
    <p>
        Estos términos pueden cambiar conforme la plataforma agregue nuevas funciones.
        El uso continuo de la plataforma implica la aceptación de los cambios.
    </p>

    <hr class="my-4">

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary fw-bold">
        <i class="fa-solid fa-arrow-left"></i>
        Volver
    </a>

</div>

@endsection