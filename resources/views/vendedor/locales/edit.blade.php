@extends('layouts.dashboard')

@section('title', 'Editar local')
@section('page-title', 'Editar local')
@section('page-subtitle', 'Edita solo lo necesario')

@section('content')

<style>
    .box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .section-title {
        font-weight: 800;
        margin-bottom: 12px;
    }

    .help-text {
        color: #6b7280;
        font-size: .85rem;
    }

    .toggle-box {
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 14px;
        padding: 14px 16px;
        font-weight: 800;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .zona-item {
        background: #f9fafb;
    }

    .preview-img {
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
    }

    .actions-fixed {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding-top: 14px;
        margin-top: 18px;
        z-index: 5;
    }

    @media (max-width: 768px) {
        .actions-fixed .btn {
            width: 100%;
        }
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Editar local</h4>
            <p class="text-muted mb-0">{{ $local->nombre }}</p>
        </div>

        <a href="{{ route('vendedor.locales.show', $local->id) }}"
           class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    {{-- ERRORES --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

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

    <form method="POST"
          action="{{ route('vendedor.locales.update', $local->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- DATOS PRINCIPALES --}}
        <div class="box">
            <h5 class="section-title">
                <i class="fa-solid fa-store text-warning"></i>
                Datos principales
            </h5>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre del local</label>
                    <input type="text"
                           name="nombre"
                           class="form-control"
                           value="{{ old('nombre', $local->nombre) }}"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text"
                           name="telefono"
                           class="form-control"
                           value="{{ old('telefono', $local->telefono) }}"
                           placeholder="997 123 4567">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">WhatsApp</label>
                    <input type="text"
                           name="whatsapp"
                           class="form-control"
                           value="{{ old('whatsapp', $local->whatsapp) }}"
                           placeholder="997 123 4567">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Ciudad / Pueblo</label>
                    <input type="text"
                           name="ciudad"
                           id="ciudadLocal"
                           class="form-control google-ciudad-local"
                           value="{{ old('ciudad', $local->ciudad) }}"
                           placeholder="Ej. Peto">
                    <small class="help-text">Selecciona una sugerencia.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Estado</label>
                    <input type="text"
                           name="estado"
                           id="estadoLocal"
                           class="form-control"
                           value="{{ old('estado', $local->estado) }}"
                           placeholder="Ej. Yucatán">
                </div>

            </div>
        </div>

        {{-- ENTREGA --}}
        <div class="box">
            <h5 class="section-title">
                <i class="fa-solid fa-motorcycle text-warning"></i>
                Entrega
            </h5>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Mínimo de pedido</label>
                    <input type="number"
                           step="0.01"
                           name="minimo_pedido"
                           class="form-control"
                           value="{{ old('minimo_pedido', $local->minimo_pedido ?? 0) }}"
                           min="0">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Envío base</label>
                    <input type="number"
                           step="0.01"
                           name="costo_envio"
                           class="form-control"
                           value="{{ old('costo_envio', $local->costo_envio ?? 0) }}"
                           min="0">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Opciones</label>

                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <label class="form-check">
                            <input type="checkbox"
                                   name="permite_recoger"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('permite_recoger', $local->permite_recoger) ? 'checked' : '' }}>
                            <span class="form-check-label">Recoger en local</span>
                        </label>

                        <label class="form-check">
                            <input type="checkbox"
                                   name="permite_delivery"
                                   id="permiteDelivery"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('permite_delivery', $local->permite_delivery) ? 'checked' : '' }}>
                            <span class="form-check-label">Entrega a domicilio</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- ZONAS DE ENTREGA --}}
        <div class="box" id="zonasEntregaSection">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="section-title mb-1">
                        <i class="fa-solid fa-map-location-dot text-warning"></i>
                        Zonas de entrega
                    </h5>
                    <p class="help-text mb-0">Agrega pueblos o ciudades donde entregas.</p>
                </div>

                <button type="button"
                        class="btn btn-outline-dark fw-bold"
                        onclick="agregarZonaEntrega()">
                    <i class="fa-solid fa-plus"></i>
                    Agregar zona
                </button>
            </div>

            <div id="zonasEntregaBox">

                @php
                    $zonasOld = old('zonas_entrega');
                @endphp

                @if($zonasOld)
                    @foreach($zonasOld as $i => $zona)
                        <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                            <input type="hidden" name="zonas_entrega[{{ $i }}][id]" value="{{ $zona['id'] ?? '' }}">

                            <div class="col-md-4">
                                <input type="text"
                                       name="zonas_entrega[{{ $i }}][ciudad]"
                                       class="form-control google-ciudad-zona"
                                       value="{{ $zona['ciudad'] ?? '' }}"
                                       placeholder="Ciudad / Pueblo">
                            </div>

                            <div class="col-md-3">
                                <input type="text"
                                       name="zonas_entrega[{{ $i }}][estado]"
                                       class="form-control google-estado-zona"
                                       value="{{ $zona['estado'] ?? '' }}"
                                       placeholder="Estado">
                            </div>

                            <div class="col-md-3">
                                <input type="number"
                                       step="0.01"
                                       name="zonas_entrega[{{ $i }}][costo_envio]"
                                       class="form-control"
                                       value="{{ $zona['costo_envio'] ?? 0 }}"
                                       min="0"
                                       placeholder="Costo">
                            </div>

                            <div class="col-md-1 d-flex align-items-center">
                                <label class="form-check mb-0">
                                    <input type="checkbox"
                                           name="zonas_entrega[{{ $i }}][activo]"
                                           value="1"
                                           class="form-check-input"
                                           {{ isset($zona['activo']) ? 'checked' : '' }}>
                                </label>
                            </div>

                            <div class="col-md-1">
                                <button type="button"
                                        class="btn btn-outline-danger w-100 fw-bold"
                                        onclick="this.closest('.zona-item').remove()">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                @elseif($local->zonasEntrega && $local->zonasEntrega->count() > 0)
                    @foreach($local->zonasEntrega as $i => $zona)
                        <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                            <input type="hidden" name="zonas_entrega[{{ $i }}][id]" value="{{ $zona->id }}">

                            <div class="col-md-4">
                                <input type="text"
                                       name="zonas_entrega[{{ $i }}][ciudad]"
                                       class="form-control google-ciudad-zona"
                                       value="{{ $zona->ciudad }}"
                                       placeholder="Ciudad / Pueblo">
                            </div>

                            <div class="col-md-3">
                                <input type="text"
                                       name="zonas_entrega[{{ $i }}][estado]"
                                       class="form-control google-estado-zona"
                                       value="{{ $zona->estado }}"
                                       placeholder="Estado">
                            </div>

                            <div class="col-md-3">
                                <input type="number"
                                       step="0.01"
                                       name="zonas_entrega[{{ $i }}][costo_envio]"
                                       class="form-control"
                                       value="{{ $zona->costo_envio ?? 0 }}"
                                       min="0"
                                       placeholder="Costo">
                            </div>

                            <div class="col-md-1 d-flex align-items-center">
                                <label class="form-check mb-0">
                                    <input type="checkbox"
                                           name="zonas_entrega[{{ $i }}][activo]"
                                           value="1"
                                           class="form-check-input"
                                           {{ $zona->activo ? 'checked' : '' }}>
                                </label>
                            </div>

                            <div class="col-md-1">
                                <button type="button"
                                        class="btn btn-outline-danger w-100 fw-bold"
                                        onclick="this.closest('.zona-item').remove()">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                @else
                    <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                        <input type="hidden" name="zonas_entrega[0][id]" value="">

                        <div class="col-md-4">
                            <input type="text"
                                   name="zonas_entrega[0][ciudad]"
                                   class="form-control google-ciudad-zona"
                                   placeholder="Ciudad / Pueblo">
                        </div>

                        <div class="col-md-3">
                            <input type="text"
                                   name="zonas_entrega[0][estado]"
                                   class="form-control google-estado-zona"
                                   value="{{ $local->estado }}"
                                   placeholder="Estado">
                        </div>

                        <div class="col-md-3">
                            <input type="number"
                                   step="0.01"
                                   name="zonas_entrega[0][costo_envio]"
                                   class="form-control"
                                   value="0"
                                   min="0"
                                   placeholder="Costo">
                        </div>

                        <div class="col-md-1 d-flex align-items-center">
                            <label class="form-check mb-0">
                                <input type="checkbox"
                                       name="zonas_entrega[0][activo]"
                                       value="1"
                                       class="form-check-input"
                                       checked>
                            </label>
                        </div>

                        <div class="col-md-1">
                            <button type="button"
                                    class="btn btn-outline-danger w-100 fw-bold"
                                    onclick="this.closest('.zona-item').remove()">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- MÁS DATOS --}}
        <button class="toggle-box"
                type="button"
                onclick="toggleSection('masDatos', this)">
            <span>
                <i class="fa-solid fa-pen text-warning"></i>
                Más datos del local
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div id="masDatos" style="display: none;">
            <div class="box">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-bold">Dirección</label>
                        <textarea name="direccion"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Dirección del local">{{ old('direccion', $local->direccion) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Describe tu negocio">{{ old('descripcion', $local->descripcion) }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- IMÁGENES --}}
        <button class="toggle-box"
                type="button"
                onclick="toggleSection('imagenesLocal', this)">
            <span>
                <i class="fa-solid fa-image text-warning"></i>
                Imágenes
            </span>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div id="imagenesLocal" style="display: none;">
            <div class="box">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Logo actual</label>
                        <div class="mb-3">
                            @if($local->logo)
                                <img src="{{ asset('storage/' . $local->logo) }}"
                                     class="preview-img"
                                     style="width: 120px; height: 120px;">
                            @else
                                <p class="text-muted mb-0">Sin logo</p>
                            @endif
                        </div>

                        <label class="form-label fw-bold">Nuevo logo</label>
                        <input type="file"
                               name="logo"
                               class="form-control"
                               accept="image/*">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Portada actual</label>
                        <div class="mb-3">
                            @if($local->portada)
                                <img src="{{ asset('storage/' . $local->portada) }}"
                                     class="preview-img"
                                     style="width: 220px; height: 120px;">
                            @else
                                <p class="text-muted mb-0">Sin portada</p>
                            @endif
                        </div>

                        <label class="form-label fw-bold">Nueva portada</label>
                        <input type="file"
                               name="portada"
                               class="form-control"
                               accept="image/*">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Imágenes extra</label>
                        <input type="file"
                               name="imagenes[]"
                               class="form-control"
                               accept="image/*"
                               multiple>
                    </div>

                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="actions-fixed d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('vendedor.locales.show', $local->id) }}"
               class="btn btn-outline-secondary fw-bold">
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
    function toggleSection(id, button) {
        const section = document.getElementById(id);
        const icon = button.querySelector('.fa-chevron-down, .fa-chevron-up');

        if (!section) return;

        const isHidden = section.style.display === 'none' || section.style.display === '';

        section.style.display = isHidden ? 'block' : 'none';

        if (icon) {
            icon.classList.toggle('fa-chevron-down', !isHidden);
            icon.classList.toggle('fa-chevron-up', isHidden);
        }
    }

    let zonaIndex = {{ old('zonas_entrega') ? count(old('zonas_entrega')) : max($local->zonasEntrega->count(), 1) }};

    function agregarZonaEntrega() {
        const box = document.getElementById('zonasEntregaBox');

        const html = `
            <div class="row g-2 zona-item border rounded-4 p-3 mb-3">
                <input type="hidden" name="zonas_entrega[${zonaIndex}][id]" value="">

                <div class="col-md-4">
                    <input type="text"
                           name="zonas_entrega[${zonaIndex}][ciudad]"
                           class="form-control google-ciudad-zona"
                           placeholder="Ciudad / Pueblo">
                </div>

                <div class="col-md-3">
                    <input type="text"
                           name="zonas_entrega[${zonaIndex}][estado]"
                           class="form-control google-estado-zona"
                           placeholder="Estado">
                </div>

                <div class="col-md-3">
                    <input type="number"
                           step="0.01"
                           name="zonas_entrega[${zonaIndex}][costo_envio]"
                           class="form-control"
                           value="0"
                           min="0"
                           placeholder="Costo">
                </div>

                <div class="col-md-1 d-flex align-items-center">
                    <label class="form-check mb-0">
                        <input type="checkbox"
                               name="zonas_entrega[${zonaIndex}][activo]"
                               value="1"
                               class="form-check-input"
                               checked>
                    </label>
                </div>

                <div class="col-md-1">
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
                if (!ciudad) {
                    ciudad = component.long_name;
                }
            }

            if (types.includes('administrative_area_level_1')) {
                estado = component.long_name;
            }
        });

        if (!ciudad && place.name) {
            ciudad = place.name;
        }

        return { ciudad, estado };
    }

    function iniciarAutocompletadoLocal() {
        const ciudadLocal = document.getElementById('ciudadLocal');
        const estadoLocal = document.getElementById('estadoLocal');

        if (!ciudadLocal || ciudadLocal.dataset.autocomplete === '1') return;

        ciudadLocal.dataset.autocomplete = '1';

        const autocomplete = new google.maps.places.Autocomplete(ciudadLocal, {
            types: ['(cities)'],
            componentRestrictions: { country: 'mx' },
            fields: ['address_components', 'name']
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            const data = extraerCiudadYEstado(place);

            if (data.ciudad) {
                ciudadLocal.value = data.ciudad;
            }

            if (estadoLocal && data.estado) {
                estadoLocal.value = data.estado;
            }
        });
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
                const place = autocomplete.getPlace();
                const data = extraerCiudadYEstado(place);

                if (data.ciudad) {
                    input.value = data.ciudad;
                }

                const zonaItem = input.closest('.zona-item');

                if (zonaItem && data.estado) {
                    const estadoInput = zonaItem.querySelector('.google-estado-zona');

                    if (estadoInput) {
                        estadoInput.value = data.estado;
                    }
                }
            });
        });
    }

    window.initGoogleMapsZonas = function () {
        iniciarAutocompletadoLocal();
        iniciarAutocompletadoZonas();
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