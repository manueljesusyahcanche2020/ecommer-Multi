@extends('layouts.dashboard')

@section('title', 'Crear producto')
@section('page-title', 'Crear producto')
@section('page-subtitle', 'Agrega un producto rápido al catálogo')

@section('content')

<style>
    .simple-box { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:18px; margin-bottom:16px; }
    .simple-title { font-weight:800; margin-bottom:14px; }
    .help-text { color:#6b7280; font-size:.85rem; }
    .advanced-toggle { width:100%; border:1px solid #e5e7eb; background:#f9fafb; border-radius:14px; padding:14px 16px; font-weight:800; display:flex; justify-content:space-between; align-items:center; }
    .variante-item { background:#f9fafb; }
    .sticky-actions { position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb; padding-top:14px; margin-top:20px; z-index:5; }
</style>

@php
    // Variantes previas (si falla la validación) o una fila vacía
    $variantesOld = old('variantes', [[]]);
    $siguienteIndice = collect(array_keys($variantesOld))->max() + 1;
@endphp

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Nuevo producto</h4>
            <p class="text-muted mb-0">{{ $local->nombre }}</p>
        </div>

        <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
           class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>

    {{-- ERRORES --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
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
          action="{{ route('vendedor.locales.productos.store', $local->id) }}"
          enctype="multipart/form-data">
        @csrf

        {{-- INFORMACIÓN BÁSICA (tabla productos + producto_imagenes) --}}
        <div class="simple-box">
            <h5 class="simple-title">
                <i class="fa-solid fa-box text-warning"></i> Información básica
            </h5>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre del producto</label>
                    <input type="text" name="nombre" class="form-control"
                           placeholder="Ej. Pizza grande" value="{{ old('nombre') }}" required maxlength="200">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Solo productos simples: se guardan en una variante "Estándar" --}}
                <div class="col-md-4 solo-simple">
                    <label class="form-label fw-bold">Precio</label>
                    <input type="number" step="0.01" min="0" name="precio" class="form-control"
                           placeholder="0.00" value="{{ old('precio') }}" required>
                </div>

                <div class="col-md-4 solo-simple">
                    <label class="form-label fw-bold">Precio costo</label>
                    <input type="number" step="0.01" min="0" name="precio_costo" class="form-control"
                           placeholder="0.00" value="{{ old('precio_costo') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Imagen principal</label>
                    <input type="file" name="imagen_principal" class="form-control" accept="image/*">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"
                              placeholder="Describe brevemente el producto">{{ old('descripcion') }}</textarea>
                </div>

            </div>
        </div>

        {{-- CONFIGURACIÓN RÁPIDA --}}
        <div class="simple-box">
            <h5 class="simple-title">
                <i class="fa-solid fa-sliders text-warning"></i> Configuración rápida
            </h5>

            <div class="row g-3 align-items-end">

                <div class="col-12">
                    <label class="form-label fw-bold">Estado del producto</label>

                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <label class="form-check">
                            <input type="checkbox" name="maneja_stock" value="1" class="form-check-input"
                                   {{ old('maneja_stock', 1) ? 'checked' : '' }}>
                            <span class="form-check-label">Maneja stock</span>
                        </label>

                        <label class="form-check">
                            <input type="checkbox" name="activo" value="1" class="form-check-input"
                                   {{ old('activo', 1) ? 'checked' : '' }}>
                            <span class="form-check-label">Público</span>
                        </label>

                        <label class="form-check">
                            <input type="checkbox" name="destacado" value="1" class="form-check-input"
                                   {{ old('destacado') ? 'checked' : '' }}>
                            <span class="form-check-label">Destacado</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- VARIANTES Y STOCK (producto_variantes + producto_stocks) --}}
        <div class="simple-box" id="variantesSection">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="simple-title mb-1">
                        <i class="fa-solid fa-layer-group text-warning"></i> Variantes y stock
                    </h5>
                    <p class="help-text mb-0">
                        Sin variantes: indica solo el stock. Con variantes: captura cada una con su precio y stock.
                    </p>
                </div>

                {{-- Envía 0 (sin variantes) o 1 (con variantes) --}}
                <div class="form-check form-switch">
                    <input type="hidden" name="tiene_variantes" value="0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="tiene_variantes" name="tiene_variantes" value="1"
                           {{ old('tiene_variantes', 0) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tiene_variantes">Este producto tiene variantes</label>
                </div>
            </div>

            {{-- SIN VARIANTES: solo stock --}}
            <div class="row g-3 solo-simple">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0"
                           value="{{ old('stock', 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stock mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control" min="0"
                           value="{{ old('stock_minimo', 0) }}">
                </div>
            </div>

            {{-- CON VARIANTES: datos de cada variante --}}
            <div id="variantesConBox">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-dark fw-bold" onclick="agregarVariante()">
                    <i class="fa-solid fa-plus"></i> Agregar variante
                </button>
            </div>

            <div id="variantesBox">
                @foreach($variantesOld as $i => $v)
                    <div class="row g-3 variante-item border rounded-4 p-3 mb-3">

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" name="variantes[{{ $i }}][nombre]" class="form-control"
                                   placeholder="Ej. Tamaño" value="{{ $v['nombre'] ?? '' }}" maxlength="150" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Valor</label>
                            <input type="text" name="variantes[{{ $i }}][valor]" class="form-control"
                                   placeholder="Ej. Grande" value="{{ $v['valor'] ?? '' }}" maxlength="150">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio de venta</label>
                            <input type="number" step="0.01" min="0" name="variantes[{{ $i }}][precio_venta]"
                                   class="form-control" placeholder="0.00" value="{{ $v['precio_venta'] ?? '' }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Precio costo</label>
                            <input type="number" step="0.01" min="0" name="variantes[{{ $i }}][precio_costo]"
                                   class="form-control" placeholder="0.00" value="{{ $v['precio_costo'] ?? 0 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Precio oferta</label>
                            <input type="number" step="0.01" min="0" name="variantes[{{ $i }}][precio_oferta]"
                                   class="form-control" placeholder="Opcional" value="{{ $v['precio_oferta'] ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" min="0" name="variantes[{{ $i }}][stock]"
                                   class="form-control" value="{{ $v['stock'] ?? 0 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Stock mínimo</label>
                            <input type="number" min="0" name="variantes[{{ $i }}][stock_minimo]"
                                   class="form-control" value="{{ $v['stock_minimo'] ?? 0 }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">SKU variante</label>
                            <input type="text" name="variantes[{{ $i }}][sku]" class="form-control"
                                   placeholder="Opcional" value="{{ $v['sku'] ?? '' }}" maxlength="100">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Imagen de variante</label>
                            <input type="file" name="variantes[{{ $i }}][imagen]" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <label class="form-check mb-2">
                                <input type="checkbox" name="variantes[{{ $i }}][activo]" value="1"
                                       class="form-check-input"
                                       {{ empty(old('variantes')) || isset($v['activo']) ? 'checked' : '' }}>
                                <span class="form-check-label">Activa</span>
                            </label>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            @unless($loop->first)
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold"
                                        onclick="this.closest('.variante-item').remove()">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            @endunless
                        </div>

                    </div>
                @endforeach
            </div>
            </div>{{-- /variantesConBox --}}
        </div>

        {{-- OPCIONES AVANZADAS --}}
        <button class="advanced-toggle mb-3" type="button"
                data-bs-toggle="collapse" data-bs-target="#opcionesAvanzadas"
                aria-expanded="false" aria-controls="opcionesAvanzadas">
            <span><i class="fa-solid fa-gear text-warning"></i> Opciones avanzadas</span>
            <i class="fa-solid fa-chevron-down"></i>
        </button>

        <div class="collapse" id="opcionesAvanzadas">
            <div class="simple-box">
                <div class="row g-3">

                    {{-- Solo simples (en variables cada variante tiene lo suyo) --}}
                    <div class="col-md-3 solo-simple">
                        <label class="form-label fw-bold">Precio oferta</label>
                        <input type="number" step="0.01" min="0" name="precio_oferta" class="form-control"
                               placeholder="Opcional" value="{{ old('precio_oferta') }}">
                    </div>

                    <div class="col-md-3 solo-simple">
                        <label class="form-label fw-bold">SKU</label>
                        <input type="text" name="sku" class="form-control" maxlength="100"
                               placeholder="Código interno opcional" value="{{ old('sku') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Imágenes extra</label>
                        <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
                        <small class="help-text">Puedes seleccionar varias imágenes.</small>
                    </div>

                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="sticky-actions d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
               class="btn btn-outline-secondary fw-bold">Cancelar</a>

            <button type="submit" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-floppy-disk"></i> Guardar producto
            </button>
        </div>

    </form>

</div>

<script>
    let varianteIndex = {{ $siguienteIndice }};

    // Muestra/oculta un bloque y deshabilita sus campos para que no se envíen ni se validen
    function toggleBloque(el, activo) {
        el.style.display = activo ? '' : 'none';
        el.querySelectorAll('input, select, textarea').forEach(i => i.disabled = !activo);
    }

    function actualizarTipoProducto() {
        const tiene = document.getElementById('tiene_variantes').checked;

        // Sin variantes: precio, stock, sku... / Con variantes: cada variante lleva lo suyo
        document.querySelectorAll('.solo-simple').forEach(el => toggleBloque(el, !tiene));
        toggleBloque(document.getElementById('variantesConBox'), tiene);
    }

    function agregarVariante() {
        const i = varianteIndex;
        const html = `
            <div class="row g-3 variante-item border rounded-4 p-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="variantes[${i}][nombre]" class="form-control" placeholder="Ej. Tamaño" maxlength="150" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Valor</label>
                    <input type="text" name="variantes[${i}][valor]" class="form-control" placeholder="Ej. Grande" maxlength="150">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Precio de venta</label>
                    <input type="number" step="0.01" min="0" name="variantes[${i}][precio_venta]" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Precio costo</label>
                    <input type="number" step="0.01" min="0" name="variantes[${i}][precio_costo]" class="form-control" placeholder="0.00" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Precio oferta</label>
                    <input type="number" step="0.01" min="0" name="variantes[${i}][precio_oferta]" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Stock</label>
                    <input type="number" min="0" name="variantes[${i}][stock]" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Stock mínimo</label>
                    <input type="number" min="0" name="variantes[${i}][stock_minimo]" class="form-control" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">SKU variante</label>
                    <input type="text" name="variantes[${i}][sku]" class="form-control" placeholder="Opcional" maxlength="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Imagen de variante</label>
                    <input type="file" name="variantes[${i}][imagen]" class="form-control" accept="image/*">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <label class="form-check mb-2">
                        <input type="checkbox" name="variantes[${i}][activo]" value="1" class="form-check-input" checked>
                        <span class="form-check-label">Activa</span>
                    </label>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger w-100 fw-bold"
                            onclick="this.closest('.variante-item').remove()">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

        document.getElementById('variantesBox').insertAdjacentHTML('beforeend', html);
        varianteIndex++;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('tiene_variantes').addEventListener('change', actualizarTipoProducto);
        actualizarTipoProducto();
    });
</script>

@endsection