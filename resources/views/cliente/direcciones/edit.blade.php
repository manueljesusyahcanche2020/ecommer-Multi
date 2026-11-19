
@extends('layouts.dashboard')

@section('title', 'Editar dirección')
@section('page-title', 'Editar dirección')
@section('page-subtitle', 'Modifica tu dirección de entrega')

@section('content')

<style>
    #map {
        width: 100%;
        height: 380px;
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
    }

    .map-box {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 16px;
        background: #f9fafb;
    }

    .coords-box {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 10px 12px;
        font-size: 13px;
        color: #9a3412;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Editar dirección</h4>
            <p class="text-muted mb-0">
                Actualiza los datos y la ubicación en el mapa.
            </p>
        </div>

        <a href="{{ route('cliente.direcciones.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa los datos:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('cliente.direcciones.update', $direccion->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">

            <div class="col-12">
                <div class="map-box">
                    <label class="form-label fw-bold">
                        Buscar dirección en Google Maps
                    </label>

                    <input
                        type="text"
                        id="buscar_mapa"
                        class="form-control mb-3"
                        placeholder="Ej. Calle 30, Peto, Yucatán"
                    >

                    <div id="map"></div>

                    <div class="coords-box mt-3">
                        <i class="fa-solid fa-location-dot"></i>
                        Coordenadas seleccionadas:
                        <strong>Lat:</strong> <span id="lat_text">---</span>,
                        <strong>Lng:</strong> <span id="lng_text">---</span>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Puedes mover el marcador o tocar el mapa para seleccionar la ubicación exacta.
                    </small>
                </div>
            </div>

            <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud', $direccion->latitud) }}">
            <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud', $direccion->longitud) }}">

            <div class="col-md-6">
                <label class="form-label fw-bold">Nombre de quien recibe</label>
                <input
                    type="text"
                    name="nombre"
                    class="form-control"
                    placeholder="Ej. Manuel"
                    value="{{ old('nombre', $direccion->nombre_recibe) }}"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Teléfono de contacto</label>
                <input
                    type="text"
                    name="telefono"
                    class="form-control"
                    placeholder="999 123 4567"
                    value="{{ old('telefono', $direccion->telefono) }}"
                >
            </div>

            <div class="col-md-8">
                <label class="form-label fw-bold">Calle</label>
                <input
                    type="text"
                    name="calle"
                    id="calle"
                    class="form-control"
                    placeholder="Ej. Calle 30"
                    value="{{ old('calle', $direccion->calle) }}"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Número</label>
                <input
                    type="text"
                    name="numero"
                    id="numero"
                    class="form-control"
                    placeholder="Ej. 120"
                    value="{{ old('numero', $direccion->numero) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Cruzamientos</label>
                <input
                    type="text"
                    name="cruzamientos"
                    class="form-control"
                    placeholder="Ej. 41 x 43"
                    value="{{ old('cruzamientos', $direccion->cruzamientos) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Colonia</label>
                <input
                    type="text"
                    name="colonia"
                    id="colonia"
                    class="form-control"
                    placeholder="Ej. Centro"
                    value="{{ old('colonia', $direccion->colonia) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Código postal</label>
                <input
                    type="text"
                    name="codigo_postal"
                    id="codigo_postal"
                    class="form-control"
                    placeholder="Ej. 97930"
                    value="{{ old('codigo_postal', $direccion->codigo_postal) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Ciudad</label>
                <input
                    type="text"
                    name="ciudad"
                    id="ciudad"
                    class="form-control"
                    placeholder="Ej. Peto"
                    value="{{ old('ciudad', $direccion->ciudad) }}"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Estado</label>
                <input
                    type="text"
                    name="estado"
                    id="estado"
                    class="form-control"
                    placeholder="Ej. Yucatán"
                    value="{{ old('estado', $direccion->estado) }}"
                    required
                >
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">Referencia</label>
                <textarea
                    name="referencia"
                    class="form-control"
                    rows="3"
                    placeholder="Ej. Casa azul, frente a la tienda, portón negro..."
                >{{ old('referencia', $direccion->referencia) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-check">
                    <input
                        type="checkbox"
                        name="principal"
                        value="1"
                        class="form-check-input"
                        {{ old('principal', $direccion->principal) ? 'checked' : '' }}
                    >
                    <span class="form-check-label">
                        Usar como dirección principal
                    </span>
                </label>
            </div>

        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('cliente.direcciones.index') }}" class="btn btn-outline-secondary fw-bold">
                Cancelar
            </a>

            <button type="submit" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar cambios
            </button>
        </div>

    </form>

</div>

<script>
    let map;
    let marker;
    let autocomplete;
    let geocoder;

    function initMap() {
        const defaultPosition = {
            lat: Number(document.getElementById('latitud').value) || 20.1278,
            lng: Number(document.getElementById('longitud').value) || -88.9227
        };

        geocoder = new google.maps.Geocoder();

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultPosition,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
        });

        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true,
        });

        setLatLng(defaultPosition.lat, defaultPosition.lng);

        marker.addListener("dragend", function () {
            const position = marker.getPosition();
            setLatLng(position.lat(), position.lng());
            buscarDireccionPorCoordenadas(position);
        });

        map.addListener("click", function (event) {
            marker.setPosition(event.latLng);
            setLatLng(event.latLng.lat(), event.latLng.lng());
            buscarDireccionPorCoordenadas(event.latLng);
        });

        const input = document.getElementById("buscar_mapa");

        autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ["geometry", "address_components", "formatted_address"],
            componentRestrictions: { country: "mx" }
        });

        autocomplete.addListener("place_changed", function () {
            const place = autocomplete.getPlace();

            if (!place.geometry || !place.geometry.location) {
                return;
            }

            const location = place.geometry.location;

            map.setCenter(location);
            map.setZoom(17);
            marker.setPosition(location);

            setLatLng(location.lat(), location.lng());
            llenarCamposDireccion(place);
        });
    }

    function setLatLng(lat, lng) {
        document.getElementById("latitud").value = lat;
        document.getElementById("longitud").value = lng;

        document.getElementById("lat_text").innerText = Number(lat).toFixed(6);
        document.getElementById("lng_text").innerText = Number(lng).toFixed(6);
    }

    function buscarDireccionPorCoordenadas(latLng) {
        geocoder.geocode({ location: latLng }, function (results, status) {
            if (status === "OK" && results[0]) {
                llenarCamposDireccion(results[0]);

                if (results[0].formatted_address) {
                    document.getElementById("buscar_mapa").value = results[0].formatted_address;
                }
            }
        });
    }

    function llenarCamposDireccion(place) {
        let calle = "";
        let numero = "";
        let colonia = "";
        let ciudad = "";
        let estado = "";
        let codigoPostal = "";

        if (!place.address_components) {
            return;
        }

        place.address_components.forEach(function (component) {
            const types = component.types;

            if (types.includes("route")) {
                calle = component.long_name;
            }

            if (types.includes("street_number")) {
                numero = component.long_name;
            }

            if (
                types.includes("sublocality") ||
                types.includes("sublocality_level_1") ||
                types.includes("neighborhood")
            ) {
                if (!colonia) {
                    colonia = component.long_name;
                }
            }

            if (
                types.includes("locality") ||
                types.includes("administrative_area_level_2")
            ) {
                if (!ciudad) {
                    ciudad = component.long_name;
                }
            }

            if (types.includes("administrative_area_level_1")) {
                estado = component.long_name;
            }

            if (types.includes("postal_code")) {
                codigoPostal = component.long_name;
            }
        });

        if (calle) document.getElementById("calle").value = calle;
        if (numero) document.getElementById("numero").value = numero;
        if (colonia) document.getElementById("colonia").value = colonia;
        if (ciudad) document.getElementById("ciudad").value = ciudad;
        if (estado) document.getElementById("estado").value = estado;
        if (codigoPostal) document.getElementById("codigo_postal").value = codigoPostal;
    }
</script>

<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&loading=async&libraries=places&callback=initMap">
</script>

@endsection