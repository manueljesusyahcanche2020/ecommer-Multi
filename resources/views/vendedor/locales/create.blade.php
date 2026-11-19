@extends('layouts.dashboard')

@section('title', 'Crear local')
@section('page-title', 'Crear local')
@section('page-subtitle', 'Registra tu local de forma rápida y sencilla')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Nuevo local</h4>
            <p class="text-muted mb-0">Primero guarda lo básico. Lo demás lo puedes ajustar aquí mismo.</p>
        </div>

        <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa estos datos:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('vendedor.locales.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- DATOS PRINCIPALES --}}
        <div class="border rounded-4 p-3 mb-3">
            <h5 class="fw-bold mb-3">Datos principales</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre del local</label>
                    <input type="text"
                           name="nombre"
                           class="form-control"
                           placeholder="Ej. Taquería Don Juan"
                           value="{{ old('nombre') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">WhatsApp o teléfono</label>
                    <input type="text"
                           name="whatsapp"
                           class="form-control"
                           placeholder="997 123 4567"
                           value="{{ old('whatsapp', old('telefono')) }}">

                    <input type="hidden" name="telefono" value="{{ old('telefono') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Dirección del local</label>

                    <div class="input-group">
                        <input type="text"
                               name="direccion"
                               id="direccionLocal"
                               class="form-control"
                               placeholder="Escribe la calle, número o busca en Google Maps..."
                               value="{{ old('direccion') }}"
                               autocomplete="off">

                        <button type="button"
                                id="btnUsarCoordenadas"
                                class="btn btn-outline-dark fw-bold"
                                onclick="usarMiUbicacion()">
                            <i class="fa-solid fa-location-crosshairs"></i>
                            <span id="btnUsarCoordenadasTexto">Usar mi ubicación</span>
                        </button>
                    </div>

                    <small class="text-muted">Al seleccionar una dirección, la ciudad y el estado se autocompletarán.</small>
                    <small id="coordenadasStatus" class="d-block mt-1"></small>

                    <input type="hidden" name="latitud" id="latitudLocal" value="{{ old('latitud') }}">
                    <input type="hidden" name="longitud" id="longitudLocal" value="{{ old('longitud') }}">

                    <div id="mapaUbicacionWrapper" class="mt-3" style="display:none;">
                        <div id="mapaUbicacion" style="width:100%; height:280px; border-radius:12px; overflow:hidden;"></div>
                        <small class="text-muted d-block mt-1">
                            <i class="fa-solid fa-arrows-up-down-left-right me-1"></i>
                            Puedes arrastrar el marcador o hacer clic en el mapa para ajustar la ubicación exacta.
                        </small>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Ciudad / Pueblo</label>
                    <input type="text"
                           name="ciudad"
                           id="ciudadLocal"
                           class="form-control"
                           placeholder="Ej. Peto"
                           value="{{ old('ciudad') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Estado</label>
                    <input type="text"
                           name="estado"
                           id="estadoLocal"
                           class="form-control"
                           placeholder="Ej. Yucatán"
                           value="{{ old('estado') }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Descripción corta</label>
                    <textarea name="descripcion"
                              class="form-control"
                              rows="2"
                              placeholder="Ej. Comida casera, tacos, tortas y bebidas.">{{ old('descripcion') }}</textarea>
                </div>
            </div>
        </div>

        {{-- PEDIDOS --}}
        <div class="border rounded-4 p-3 mb-3">
            <h5 class="fw-bold mb-3">Pedidos</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mínimo de pedido</label>
                    <input type="number"
                           step="0.01"
                           name="minimo_pedido"
                           class="form-control"
                           placeholder="0.00"
                           value="{{ old('minimo_pedido', 0) }}"
                           min="0">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Costo de envío base</label>
                    <input type="number"
                           step="0.01"
                           name="costo_envio"
                           class="form-control"
                           placeholder="0.00"
                           value="{{ old('costo_envio', 0) }}"
                           min="0">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Forma de entrega</label>

                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <label class="form-check">
                            <input type="checkbox"
                                   name="permite_recoger"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('permite_recoger', 1) ? 'checked' : '' }}>
                            <span class="form-check-label">Recoger en local</span>
                        </label>

                        <label class="form-check">
                            <input type="checkbox"
                                   name="permite_delivery"
                                   id="permiteDelivery"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('permite_delivery') ? 'checked' : '' }}>
                            <span class="form-check-label">Entrega a domicilio</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ZONAS DE ENTREGA --}}
        <div class="border rounded-4 p-3 mb-3" id="zonasEntregaSection">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">Zonas de entrega</h5>
                    <p class="text-muted mb-0">Agrega solo las zonas donde sí puedes entregar.</p>
                </div>

                <button type="button" class="btn btn-outline-dark fw-bold" onclick="agregarZonaEntrega()">
                    <i class="fa-solid fa-plus"></i>
                    Agregar
                </button>
            </div>

            <div id="zonasEntregaBox">
                <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Ciudad / Pueblo</label>
                        <input type="text"
                               name="zonas_entrega[0][ciudad]"
                               class="form-control google-ciudad-zona"
                               placeholder="Ej. Peto"
                               value="{{ old('zonas_entrega.0.ciudad') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Estado</label>
                        <input type="text"
                               name="zonas_entrega[0][estado]"
                               class="form-control google-estado-zona"
                               placeholder="Ej. Yucatán"
                               value="{{ old('zonas_entrega.0.estado') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Envío</label>
                        <input type="number"
                               step="0.01"
                               name="zonas_entrega[0][costo_envio]"
                               class="form-control"
                               value="{{ old('zonas_entrega.0.costo_envio', 0) }}"
                               min="0">
                    </div>

                    <input type="hidden" name="zonas_entrega[0][activo]" value="1">
                </div>
            </div>
        </div>

        {{-- IMÁGENES Y HORARIOS --}}
        <div class="opciones-kiss mb-3">
            <details class="kiss-details border rounded-4 overflow-hidden mb-2">
                <summary class="kiss-summary fw-bold">
                    <span><i class="fa-solid fa-image me-1"></i> Imágenes del local</span>
                    <span class="kiss-arrow">⌄</span>
                </summary>

                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Recomendado: imagen cuadrada.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Portada</label>
                            <input type="file" name="portada" class="form-control" accept="image/*">
                            <small class="text-muted">Recomendado: imagen horizontal.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Imágenes extra</label>
                            <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>
                </div>
            </details>

            <details class="kiss-details border rounded-4 overflow-hidden mb-2">
                <summary class="kiss-summary fw-bold">
                    <span><i class="fa-solid fa-clock me-1"></i> Horarios</span>
                    <span class="kiss-arrow">⌄</span>
                </summary>

                <div class="p-3">
                    <p class="text-muted mb-3">Por defecto se usa 8:00 a 20:00. Cambia solo lo necesario.</p>

                    @php
                        $dias = [
                            'lunes' => 'Lunes',
                            'martes' => 'Martes',
                            'miercoles' => 'Miércoles',
                            'jueves' => 'Jueves',
                            'viernes' => 'Viernes',
                            'sabado' => 'Sábado',
                            'domingo' => 'Domingo',
                        ];
                    @endphp

                    @foreach($dias as $key => $dia)
                        <div class="row g-2 align-items-center border rounded-4 p-2 mb-2">
                            <input type="hidden" name="horarios[{{ $loop->index }}][dia]" value="{{ $key }}">

                            <div class="col-md-3 fw-bold">{{ $dia }}</div>

                            <div class="col-md-3">
                                <input type="time"
                                       name="horarios[{{ $loop->index }}][hora_apertura]"
                                       class="form-control"
                                       value="{{ old('horarios.'.$loop->index.'.hora_apertura', '08:00') }}">
                            </div>

                            <div class="col-md-3">
                                <input type="time"
                                       name="horarios[{{ $loop->index }}][hora_cierre]"
                                       class="form-control"
                                       value="{{ old('horarios.'.$loop->index.'.hora_cierre', '20:00') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-check mb-0">
                                    <input type="checkbox"
                                           name="horarios[{{ $loop->index }}][cerrado]"
                                           value="1"
                                           class="form-check-input"
                                           {{ old('horarios.'.$loop->index.'.cerrado') ? 'checked' : '' }}>
                                    <span class="form-check-label">Cerrado</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        </div>

        <style>
            .kiss-details { background: #fff; }
            .kiss-summary {
                cursor: pointer;
                list-style: none;
                padding: 14px 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
                user-select: none;
            }
            .kiss-summary::-webkit-details-marker { display: none; }
            .kiss-details[open] .kiss-summary { border-bottom: 1px solid #dee2e6; }
            .kiss-details[open] .kiss-arrow { transform: rotate(180deg); }
            .kiss-arrow { transition: transform .2s ease; font-size: 18px; }
        </style>

        <input type="hidden" name="correo" value="{{ old('correo') }}">

        <div class="d-flex gap-2 mt-4 flex-wrap">
            <button type="submit" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar local
            </button>

            <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-outline-secondary fw-bold">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
    let zonaIndex = 1;

    function agregarZonaEntrega() {
        const box = document.getElementById('zonasEntregaBox');

        const html = `
            <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Ciudad / Pueblo</label>
                    <input type="text"
                           name="zonas_entrega[${zonaIndex}][ciudad]"
                           class="form-control google-ciudad-zona"
                           placeholder="Ej. Peto">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Estado</label>
                    <input type="text"
                           name="zonas_entrega[${zonaIndex}][estado]"
                           class="form-control google-estado-zona"
                           placeholder="Ej. Yucatán">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Envío</label>
                    <input type="number"
                           step="0.01"
                           name="zonas_entrega[${zonaIndex}][costo_envio]"
                           class="form-control"
                           value="0"
                           min="0">
                </div>

                <input type="hidden" name="zonas_entrega[${zonaIndex}][activo]" value="1">

                <div class="col-md-1 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-outline-danger w-100 fw-bold"
                            onclick="this.closest('.zona-item').remove()">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        box.insertAdjacentHTML('beforeend', html);
        zonaIndex++;

        if (window.google && google.maps && google.maps.places) {
            iniciarAutocompletadoZonas();
        }
    }

    function controlarZonasEntrega() {
        const permiteDelivery = document.getElementById('permiteDelivery');
        const zonasSection = document.getElementById('zonasEntregaSection');

        if (!permiteDelivery || !zonasSection) return;

        zonasSection.style.display = permiteDelivery.checked ? 'block' : 'none';
    }

    function extraerDatosDireccion(place) {
        let calle = '';
        let numero = '';
        let colonia = '';
        let ciudad = '';
        let estado = '';

        if (!place || !place.address_components) {
            return { direccion: place?.name || '', ciudad, estado };
        }

        place.address_components.forEach(function (component) {
            const types = component.types;

            if (types.includes('route')) calle = component.long_name;
            if (types.includes('street_number')) numero = component.long_name;
            if (types.includes('sublocality') || types.includes('sublocality_level_1') || types.includes('neighborhood')) {
                colonia = component.long_name;
            }
            if (types.includes('locality') || types.includes('postal_town') || types.includes('administrative_area_level_2')) {
                if (!ciudad) ciudad = component.long_name;
            }
            if (types.includes('administrative_area_level_1')) estado = component.long_name;
        });

        let direccionCompleta = calle;
        if (numero) direccionCompleta += ' #' + numero;
        if (colonia) direccionCompleta += ', Col. ' + colonia;

        if (!direccionCompleta) {
            direccionCompleta = place.name || place.formatted_address || '';
        }

        return { direccion: direccionCompleta, ciudad, estado };
    }

    function extraerCiudadYEstado(place) {
        let ciudad = '';
        let estado = '';

        if (!place || !place.address_components) {
            return { ciudad, estado };
        }

        place.address_components.forEach(function (component) {
            const types = component.types;

            if (
                types.includes('locality') ||
                types.includes('postal_town') ||
                types.includes('administrative_area_level_2') ||
                types.includes('sublocality_level_1')
            ) {
                if (!ciudad) ciudad = component.long_name;
            }

            if (types.includes('administrative_area_level_1')) {
                estado = component.long_name;
            }
        });

        if (!ciudad && place.name) ciudad = place.name;

        return { ciudad, estado };
    }

    function iniciarAutocompletadoLocal() {
        const direccionLocal = document.getElementById('direccionLocal');
        const ciudadLocal = document.getElementById('ciudadLocal');
        const estadoLocal = document.getElementById('estadoLocal');

        if (direccionLocal && direccionLocal.dataset.autocomplete !== '1') {
            direccionLocal.dataset.autocomplete = '1';

            const acDireccion = new google.maps.places.Autocomplete(direccionLocal, {
                types: ['geocode', 'establishment'],
                componentRestrictions: { country: 'mx' },
                fields: ['address_components', 'formatted_address', 'name', 'geometry']
            });

            acDireccion.addListener('place_changed', function () {
                const place = acDireccion.getPlace();
                const data = extraerDatosDireccion(place);

                if (data.direccion) direccionLocal.value = data.direccion;
                if (ciudadLocal && data.ciudad) ciudadLocal.value = data.ciudad;
                if (estadoLocal && data.estado) estadoLocal.value = data.estado;

                // Si el resultado del autocompletado trae coordenadas, las guardamos también
                if (place && place.geometry && place.geometry.location) {
                    const lat = place.geometry.location.lat();
                    const lng = place.geometry.location.lng();

                    document.getElementById('latitudLocal').value = lat;
                    document.getElementById('longitudLocal').value = lng;
                    mostrarMapaUbicacion(lat, lng);
                    mostrarEstadoCoordenadas('Ubicación establecida desde la dirección seleccionada.', 'text-success');
                }
            });
        }

        if (ciudadLocal && ciudadLocal.dataset.autocomplete !== '1') {
            ciudadLocal.dataset.autocomplete = '1';

            const acCiudad = new google.maps.places.Autocomplete(ciudadLocal, {
                types: ['(cities)'],
                componentRestrictions: { country: 'mx' },
                fields: ['address_components', 'name']
            });

            acCiudad.addListener('place_changed', function () {
                const data = extraerCiudadYEstado(acCiudad.getPlace());

                if (data.ciudad) ciudadLocal.value = data.ciudad;
                if (estadoLocal && data.estado) estadoLocal.value = data.estado;
            });
        }
    }

    function iniciarAutocompletadoZonas() {
        const inputs = document.querySelectorAll('.google-ciudad-zona:not([data-autocomplete="1"])');

        inputs.forEach(function (input) {
            input.dataset.autocomplete = '1';

            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['(cities)'],
                componentRestrictions: { country: 'mx' },
                fields: ['address_components', 'name']
            });

            autocomplete.addListener('place_changed', function () {
                const data = extraerCiudadYEstado(autocomplete.getPlace());

                if (data.ciudad) input.value = data.ciudad;

                const zonaItem = input.closest('.zona-item');

                if (zonaItem && data.estado) {
                    const estadoInput = zonaItem.querySelector('.google-estado-zona');
                    if (estadoInput) estadoInput.value = data.estado;
                }
            });
        });
    }

    function mostrarEstadoCoordenadas(mensaje, claseCss) {
        const status = document.getElementById('coordenadasStatus');
        if (!status) return;

        status.textContent = mensaje;
        status.className = 'd-block mt-1 ' + claseCss;
    }

    // ---- MAPA DE UBICACIÓN ----
    let mapaUbicacion = null;
    let marcadorUbicacion = null;

    function mostrarMapaUbicacion(lat, lng) {
        const wrapper = document.getElementById('mapaUbicacionWrapper');
        const contenedorMapa = document.getElementById('mapaUbicacion');

        if (!wrapper || !contenedorMapa || !window.google || !google.maps) return;

        wrapper.style.display = 'block';

        const posicion = { lat: parseFloat(lat), lng: parseFloat(lng) };

        if (!mapaUbicacion) {
            mapaUbicacion = new google.maps.Map(contenedorMapa, {
                center: posicion,
                zoom: 16,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
            });

            marcadorUbicacion = new google.maps.Marker({
                position: posicion,
                map: mapaUbicacion,
                draggable: true,
            });

            // Al arrastrar el marcador, actualizamos los inputs ocultos y la dirección
            marcadorUbicacion.addListener('dragend', function () {
                const nuevaPos = marcadorUbicacion.getPosition();
                actualizarCoordenadasDesdeMapa(nuevaPos.lat(), nuevaPos.lng());
            });

            // Al hacer clic en el mapa, movemos el marcador ahí
            mapaUbicacion.addListener('click', function (evento) {
                const nuevaPos = evento.latLng;
                marcadorUbicacion.setPosition(nuevaPos);
                actualizarCoordenadasDesdeMapa(nuevaPos.lat(), nuevaPos.lng());
            });
        } else {
            mapaUbicacion.setCenter(posicion);
            marcadorUbicacion.setPosition(posicion);
        }
    }

    function actualizarCoordenadasDesdeMapa(lat, lng) {
        document.getElementById('latitudLocal').value = lat;
        document.getElementById('longitudLocal').value = lng;

        if (window.google && google.maps && google.maps.Geocoder) {
            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
                if (status === 'OK' && results && results[0]) {
                    const datosDireccion = extraerDatosDireccion(results[0]);
                    const datosCiudadEstado = extraerCiudadYEstado(results[0]);

                    const direccionLocal = document.getElementById('direccionLocal');
                    const ciudadLocal = document.getElementById('ciudadLocal');
                    const estadoLocal = document.getElementById('estadoLocal');

                    if (direccionLocal) {
                        direccionLocal.value = datosDireccion.direccion || results[0].formatted_address;
                    }

                    if (ciudadLocal && datosCiudadEstado.ciudad) {
                        ciudadLocal.value = datosCiudadEstado.ciudad;
                    }

                    if (estadoLocal && datosCiudadEstado.estado) {
                        estadoLocal.value = datosCiudadEstado.estado;
                    }

                    mostrarEstadoCoordenadas('Ubicación ajustada manualmente en el mapa.', 'text-success');
                } else {
                    mostrarEstadoCoordenadas('Coordenadas actualizadas, pero no se pudo obtener la dirección exacta.', 'text-warning');
                }
            });
        }
    }

    function usarMiUbicacion() {
        const boton = document.getElementById('btnUsarCoordenadas');
        const botonTexto = document.getElementById('btnUsarCoordenadasTexto');

        if (!navigator.geolocation) {
            mostrarEstadoCoordenadas('Tu navegador no soporta geolocalización.', 'text-danger');
            return;
        }

        boton.disabled = true;
        botonTexto.textContent = 'Obteniendo ubicación...';
        mostrarEstadoCoordenadas('Solicitando permiso de ubicación...', 'text-muted');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('latitudLocal').value = lat;
                document.getElementById('longitudLocal').value = lng;
                mostrarMapaUbicacion(lat, lng);

                // Si Google Maps está disponible, convertimos las coordenadas en una dirección legible
                if (window.google && google.maps && google.maps.Geocoder) {
                    const geocoder = new google.maps.Geocoder();

                    geocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
                        boton.disabled = false;
                        botonTexto.textContent = 'Usar mi ubicación';

                        if (status === 'OK' && results && results[0]) {
                            const datosDireccion = extraerDatosDireccion(results[0]);
                            const datosCiudadEstado = extraerCiudadYEstado(results[0]);

                            const direccionLocal = document.getElementById('direccionLocal');
                            const ciudadLocal = document.getElementById('ciudadLocal');
                            const estadoLocal = document.getElementById('estadoLocal');

                            if (direccionLocal) {
                                direccionLocal.value = datosDireccion.direccion || results[0].formatted_address;
                            }

                            if (ciudadLocal && datosCiudadEstado.ciudad) {
                                ciudadLocal.value = datosCiudadEstado.ciudad;
                            }

                            if (estadoLocal && datosCiudadEstado.estado) {
                                estadoLocal.value = datosCiudadEstado.estado;
                            }

                            mostrarEstadoCoordenadas('Ubicación detectada y dirección completada automáticamente.', 'text-success');
                        } else {
                            mostrarEstadoCoordenadas('Coordenadas guardadas, pero no se pudo obtener la dirección exacta.', 'text-warning');
                        }
                    });
                } else {
                    boton.disabled = false;
                    botonTexto.textContent = 'Usar mi ubicación';
                    mostrarEstadoCoordenadas('Coordenadas guardadas correctamente.', 'text-success');
                }
            },
            function (error) {
                boton.disabled = false;
                botonTexto.textContent = 'Usar mi ubicación';

                let mensaje = 'No se pudo obtener tu ubicación.';

                if (error.code === error.PERMISSION_DENIED) {
                    mensaje = 'Debes permitir el acceso a tu ubicación para usar esta opción.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    mensaje = 'No se pudo determinar tu ubicación actual.';
                } else if (error.code === error.TIMEOUT) {
                    mensaje = 'La solicitud de ubicación tardó demasiado. Intenta de nuevo.';
                }

                mostrarEstadoCoordenadas(mensaje, 'text-danger');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    window.initGoogleMapsZonas = function () {
        iniciarAutocompletadoLocal();
        iniciarAutocompletadoZonas();

        // Si ya había coordenadas guardadas (ej. tras un error de validación), mostramos el mapa
        const latPrevia = document.getElementById('latitudLocal').value;
        const lngPrevia = document.getElementById('longitudLocal').value;

        if (latPrevia && lngPrevia) {
            mostrarMapaUbicacion(latPrevia, lngPrevia);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const permiteDelivery = document.getElementById('permiteDelivery');

        if (permiteDelivery) {
            permiteDelivery.addEventListener('change', controlarZonasEntrega);
        }

        controlarZonasEntrega();
    });
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initGoogleMapsZonas"
    async
    defer>
</script>

@endsection