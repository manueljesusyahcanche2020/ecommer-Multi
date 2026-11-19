@extends('layouts.dashboard')

@section('title', 'Editar producto')
@section('page-title', 'Editar producto')
@section('page-subtitle', 'Modifica los datos del producto seleccionado')

@section('content')

<style>
    .simple-box { background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:18px; margin-bottom:16px; }
    .simple-title { font-weight:800; margin-bottom:14px; }
    .help-text { color:#6b7280; font-size:.85rem; }
    .variante-item { background:#f9fafb; }
    .sticky-actions { position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb; padding-top:14px; margin-top:20px; z-index:5; }
</style>

@php
    $hayOld = session()->hasOldInput();

    // Stock/ajustes guardados. OJO: producto_variantes tiene columna `stock`, por eso
    // la relación `stock` se lee con getRelation() (->stock devolvería la columna).
    $relacionStock = function ($variante) {
        return $variante && $variante->relationLoaded('stock') ? $variante->getRelation('stock') : null;
    };

    $primera = $producto->variantes->first();
    $manejaActual = $relacionStock($primera) ? (bool) $relacionStock($primera)->maneja_stock : true;

    // Valores del producto sin variantes (variante "Estándar")
    $relSimple = $relacionStock($varianteSimple);
    $simple = [
        'precio_venta'  => $varianteSimple->precio_venta ?? '',
        'precio_costo'  => $varianteSimple->precio_costo ?? 0,
        'precio_oferta' => $varianteSimple->precio_oferta ?? '',
        'sku'           => $varianteSimple->sku ?? '',
        'stock'         => $relSimple ? $relSimple->stock : ($varianteSimple ? $varianteSimple->getAttribute('stock') : 0),
        'stock_minimo'  => $relSimple ? $relSimple->stock_minimo : 0,
    ];

    // Filas de variantes: old() si falló la validación, si no las de la BD
    $filaVacia = [
        'id' => '', 'nombre' => '', 'valor' => '', 'precio_venta' => '', 'precio_costo' => 0,
        'precio_oferta' => '', 'sku' => '', 'stock' => 0, 'stock_minimo' => 0, 'activo' => true, 'imagen' => null,
    ];

    $filas = [];

    if (old('variantes')) {
        foreach (old('variantes') as $k => $v) {
            $existente = !empty($v['id']) ? $producto->variantes->firstWhere('id', $v['id']) : null;

            $filas[$k] = [
                'id' => $v['id'] ?? '',
                'nombre' => $v['nombre'] ?? '',
                'valor' => $v['valor'] ?? '',
                'precio_venta' => $v['precio_venta'] ?? '',
                'precio_costo' => $v['precio_costo'] ?? 0,
                'precio_oferta' => $v['precio_oferta'] ?? '',
                'sku' => $v['sku'] ?? '',
                'stock' => $v['stock'] ?? 0,
                'stock_minimo' => $v['stock_minimo'] ?? 0,
                'activo' => isset($v['activo']),
                'imagen' => $existente ? $existente->imagen : null,
            ];
        }
    } elseif ($tieneVariantes) {
        foreach ($producto->variantes as $k => $v) {
            $rel = $relacionStock($v);

            $filas[$k] = [
                'id' => $v->id,
                'nombre' => $v->nombre,
                'valor' => $v->valor,
                'precio_venta' => $v->precio_venta,
                'precio_costo' => $v->precio_costo,
                'precio_oferta' => $v->precio_oferta,
                'sku' => $v->sku,
                'stock' => $rel ? $rel->stock : $v->getAttribute('stock'),
                'stock_minimo' => $rel ? $rel->stock_minimo : 0,
                'activo' => (bool) $v->activo,
                'imagen' => $v->imagen,
            ];
        }
    }

    if (empty($filas)) {
        $filas[0] = $filaVacia;
    }

    $siguienteIndice = collect(array_keys($filas))->max() + 1;
@endphp

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Editar producto</h4>
            <p class="text-muted mb-0">
                Producto de: <strong>{{ $local->nombre }}</strong>
            </p>
        </div>

        <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
           class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i> Volver
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
          action="{{ route('vendedor.locales.productos.update', [$local->id, $producto->id]) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- INFORMACIÓN BÁSICA --}}
        <div class="simple-box">
            <h5 class="simple-title">
                <i class="fa-solid fa-box text-warning"></i> Información básica
            </h5>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre del producto</label>
                    <input type="text" name="nombre" class="form-control" maxlength="200"
                           value="{{ old('nombre', $producto->nombre) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}"
                                {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Solo sin variantes (se guardan en la variante "Estándar") --}}
                <div class="col-md-6 solo-simple">
                    <label class="form-label fw-bold">Precio</label>
                    <input type="number" step="0.01" min="0" name="precio" class="form-control"
                           placeholder="0.00" value="{{ old('precio', $simple['precio_venta']) }}" required>
                </div>

                <div class="col-md-6 solo-simple">
                    <label class="form-label fw-bold">Precio costo</label>
                    <input type="number" step="0.01" min="0" name="precio_costo" class="form-control"
                           placeholder="0.00" value="{{ old('precio_costo', $simple['precio_costo']) }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

            </div>
        </div>

        {{-- CONFIGURACIÓN RÁPIDA --}}
        <div class="simple-box">
            <h5 class="simple-title">
                <i class="fa-solid fa-sliders text-warning"></i> Configuración rápida
            </h5>

            <div class="d-flex flex-wrap gap-3">
                <label class="form-check">
                    <input type="checkbox" name="maneja_stock" value="1" class="form-check-input"
                           {{ ($hayOld ? old('maneja_stock') : $manejaActual) ? 'checked' : '' }}>
                    <span class="form-check-label">Maneja stock</span>
                </label>

                <label class="form-check">
                    <input type="checkbox" name="activo" value="1" class="form-check-input"
                           {{ ($hayOld ? old('activo') : $producto->activo) ? 'checked' : '' }}>
                    <span class="form-check-label">Público</span>
                </label>

                <label class="form-check">
                    <input type="checkbox" name="destacado" value="1" class="form-check-input"
                           {{ ($hayOld ? old('destacado') : $producto->destacado) ? 'checked' : '' }}>
                    <span class="form-check-label">Destacado</span>
                </label>
            </div>
        </div>

        {{-- VARIANTES Y STOCK --}}
        <div class="simple-box" id="variantesSection">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="simple-title mb-1">
                        <i class="fa-solid fa-layer-group text-warning"></i> Variantes y stock
                    </h5>
                    <p class="help-text mb-0">
                        Sin variantes: solo el stock. Con variantes: precio y stock de cada una.
                    </p>
                </div>

                {{-- Envía 0 (sin variantes) o 1 (con variantes) --}}
                <div class="form-check form-switch">
                    <input type="hidden" name="tiene_variantes" value="0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="tiene_variantes" name="tiene_variantes" value="1"
                           {{ old('tiene_variantes', $tieneVariantes ? 1 : 0) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tiene_variantes">Este producto tiene variantes</label>
                </div>
            </div>

            {{-- SIN VARIANTES --}}
            <div class="row g-3 solo-simple">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0"
                           value="{{ old('stock', $simple['stock']) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Stock mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control" min="0"
                           value="{{ old('stock_minimo', $simple['stock_minimo']) }}">
                </div>
            </div>

            {{-- CON VARIANTES --}}
            <div id="variantesConBox">
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-outline-dark fw-bold" onclick="agregarVariante()">
                        <i class="fa-solid fa-plus"></i> Agregar variante
                    </button>
                </div>

                <div id="variantesBox">
                    @foreach($filas as $i => $fila)
                        @include('vendedor.productos.partials.variante-edit', ['i' => $i, 'v' => $fila])
                    @endforeach
                </div>
            </div>
        </div>

        {{-- OPCIONES AVANZADAS (solo sin variantes) --}}
        <div class="simple-box solo-simple">
            <h5 class="simple-title">
                <i class="fa-solid fa-gear text-warning"></i> Precio de oferta y SKU
            </h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Precio oferta</label>
                    <input type="number" step="0.01" min="0" name="precio_oferta" class="form-control"
                           placeholder="Opcional" value="{{ old('precio_oferta', $simple['precio_oferta']) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">SKU</label>
                    <input type="text" name="sku" class="form-control" maxlength="100"
                           placeholder="Código interno opcional" value="{{ old('sku', $simple['sku']) }}">
                </div>
            </div>
        </div>

        {{-- IMÁGENES --}}
        <div class="simple-box">
            <h5 class="simple-title">
                <i class="fa-solid fa-image text-warning"></i> Imágenes
            </h5>

            <div class="d-flex flex-wrap gap-3 mb-3">
                @forelse($producto->imagenes as $imagen)
                    <div class="border rounded-4 p-2 text-center">
                        <img src="{{ asset('storage/' . $imagen->imagen) }}"
                             style="width:120px;height:120px;object-fit:cover;border-radius:14px;">
                        <div class="mt-2">
                            @if($imagen->principal)
                                <span class="badge bg-success">Principal</span>
                            @else
                                <span class="badge bg-secondary">Extra</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Este producto no tiene imágenes.</p>
                @endforelse
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nueva imagen principal</label>
                    <input type="file" name="imagen_principal" class="form-control" accept="image/*">
                    <small class="help-text">Si subes una nueva, reemplazará la principal.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Agregar imágenes extra</label>
                    <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="sticky-actions d-flex gap-2 justify-content-end flex-wrap">
            <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
               class="btn btn-outline-secondary fw-bold">Cancelar</a>

            <button type="submit" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
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

        document.querySelectorAll('.solo-simple').forEach(el => toggleBloque(el, !tiene));
        toggleBloque(document.getElementById('variantesConBox'), tiene);
    }

    function agregarVariante() {
        const i = varianteIndex;
        const html = `
            <div class="row g-3 variante-item border rounded-4 p-3 mb-3">
                <input type="hidden" name="variantes[${i}][id]" value="">

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