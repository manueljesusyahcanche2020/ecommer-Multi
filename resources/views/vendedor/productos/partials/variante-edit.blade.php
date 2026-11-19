{{-- Fila de variante para el formulario de editar.
     Recibe: $i (índice) y $v (array con id, nombre, valor, precio_venta, precio_costo,
     precio_oferta, stock, stock_minimo, sku, activo, imagen) --}}
<div class="row g-3 variante-item border rounded-4 p-3 mb-3">
    <input type="hidden" name="variantes[{{ $i }}][id]" value="{{ $v['id'] ?? '' }}">

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

        @if(!empty($v['imagen']))
            <div class="d-flex align-items-center gap-2 mt-2">
                <img src="{{ asset('storage/' . $v['imagen']) }}"
                     style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                <small class="help-text">Actual. Sube otra solo si quieres reemplazarla.</small>
            </div>
        @endif
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <label class="form-check mb-2">
            <input type="checkbox" name="variantes[{{ $i }}][activo]" value="1" class="form-check-input"
                   {{ !empty($v['activo']) ? 'checked' : '' }}>
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