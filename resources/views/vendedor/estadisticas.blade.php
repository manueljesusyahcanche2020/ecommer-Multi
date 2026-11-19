@extends('layouts.dashboard')

@section('title', 'Estadísticas del Vendedor')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Encabezado y Filtro de Fechas -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-chart-line text-primary me-2"></i>Panel de Estadísticas
            </h1>
            <p class="text-muted small mb-0">Resumen de ventas, rendimiento de pedidos e indicadores clave.</p>
        </div>

        <!-- Formulario para Filtrar Fechas -->
        <form method="GET" action="{{ route('vendedor.estadisticas') }}" class="card p-2 shadow-sm border-0">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="fecha_inicio" class="col-form-label col-form-label-sm fw-bold">Desde:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm" value="{{ $fechaInicio }}">
                </div>
                <div class="col-auto">
                    <label for="fecha_fin" class="col-form-label col-form-label-sm fw-bold">Hasta:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm" value="{{ $fechaFin }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fa-solid fa-filter me-1"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tarjetas de Métricas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- Total Ingresos -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold extra-small">Total Ingresos</span>
                            <h4 class="fw-bold mb-0 text-success">${{ number_format($totalIngresos, 2) }}</h4>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                            <i class="fa-solid fa-dollar-sign fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pedidos -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold extra-small">Total Pedidos</span>
                            <h4 class="fw-bold mb-0 text-primary">{{ number_format($totalPedidos) }}</h4>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                            <i class="fa-solid fa-receipt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pedidos Entregados -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold extra-small">Entregados</span>
                            <h4 class="fw-bold mb-0 text-info">{{ number_format($pedidosEntregados) }}</h4>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info">
                            <i class="fa-solid fa-circle-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pedidos Cancelados -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold extra-small">Cancelados</span>
                            <h4 class="fw-bold mb-0 text-danger">{{ number_format($pedidosCancelados) }}</h4>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger">
                            <i class="fa-solid fa-circle-xmark fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Promedio -->
        <div class="col-12 col-sm-6 col-xl-2-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold extra-small">Ticket Promedio</span>
                            <h4 class="fw-bold mb-0 text-warning">${{ number_format($ticketPromedio, 2) }}</h4>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning">
                            <i class="fa-solid fa-calculator fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficas Principales -->
    <div class="row g-4 mb-4">
        <!-- Gráfica de Ventas por Período -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-chart-area text-primary me-2"></i>Evolución de Ventas
                    </h5>
                    <div class="btn-group btn-group-sm" role="group" id="btnGroupDias">
                        <button type="button" class="btn btn-outline-secondary active" data-dias="7" onclick="cambiarPeriodoVentas(this, 7)">7 días</button>
                        <button type="button" class="btn btn-outline-secondary" data-dias="15" onclick="cambiarPeriodoVentas(this, 15)">15 días</button>
                        <button type="button" class="btn btn-outline-secondary" data-dias="30" onclick="cambiarPeriodoVentas(this, 30)">30 días</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 320px;">
                        <canvas id="chartVentas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Pedidos por Estado -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-pie-chart text-info me-2"></i>Distribución por Estado
                    </h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Top Productos -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-trophy text-warning me-2"></i>Top Productos Más Vendidos
                    </h5>
                    <span class="badge bg-light text-dark border">Top 5</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Producto</th>
                                    <th class="text-center">Unidades Vendidas</th>
                                    <th class="text-end pe-3">Total Recaudado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaTopProductos">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Cargando productos...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .extra-small {
        font-size: 0.75rem;
    }
    @media (min-width: 1200px) {
        .col-xl-2-4 {
            flex: 0 0 auto;
            width: 20%;
        }
    }
</style>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chartVentasInstance = null;
let chartEstadosInstance = null;

document.addEventListener("DOMContentLoaded", function () {
    cargarGraficaVentas(7);
    cargarGraficaEstados();
    cargarTopProductos();
});

// Cambiar de período en la gráfica de ventas
function cambiarPeriodoVentas(btnElement, dias) {
    document.querySelectorAll('#btnGroupDias .btn').forEach(btn => btn.classList.remove('active'));
    if (btnElement) {
        btnElement.classList.add('active');
    }
    cargarGraficaVentas(dias);
}

// Cargar gráfica de ventas vía AJAX
function cargarGraficaVentas(dias) {
    fetch(`/vendedor/estadisticas/ventas-periodo?dias=${dias}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const labels = res.data.map(item => item.fecha);
                const totales = res.data.map(item => parseFloat(item.total_ventas));

                const ctx = document.getElementById('chartVentas').getContext('2d');

                if (chartVentasInstance) {
                    chartVentasInstance.destroy();
                }

                chartVentasInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas ($)',
                            data: totales,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#0d6efd'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return '$' + value; }
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => console.error("Error al cargar gráfica de ventas:", err));
}

// Cargar gráfica de estados de pedidos vía AJAX
function cargarGraficaEstados() {
    fetch('/vendedor/estadisticas/pedidos-estado')
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.length > 0) {
                const etiquetasFormateadas = {
                    'pendiente': 'Pendiente',
                    'aceptado': 'Aceptado',
                    'preparando': 'En Preparación',
                    'en_camino': 'En Camino',
                    'entregado': 'Entregado',
                    'cancelado': 'Cancelado'
                };

                const coloresEstados = {
                    'pendiente': '#ffc107',
                    'aceptado': '#0dcaf0',
                    'preparando': '#6f42c1',
                    'en_camino': '#fd7e14',
                    'entregado': '#198754',
                    'cancelado': '#dc3545'
                };

                const labels = res.data.map(item => etiquetasFormateadas[item.estado_pedido] || item.estado_pedido);
                const cantidades = res.data.map(item => item.cantidad);
                const bgColores = res.data.map(item => coloresEstados[item.estado_pedido] || '#6c757d');

                const ctx = document.getElementById('chartEstados').getContext('2d');

                if (chartEstadosInstance) {
                    chartEstadosInstance.destroy();
                }

                chartEstadosInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: cantidades,
                            backgroundColor: bgColores,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => console.error("Error al cargar pedidos por estado:", err));
}

// Cargar tabla de top productos vía AJAX
function cargarTopProductos() {
    fetch('/vendedor/estadisticas/top-productos?limite=5')
        .then(response => response.json())
        .then(res => {
            const tbody = document.getElementById('tablaTopProductos');
            tbody.innerHTML = '';

            if (res.success && res.data.length > 0) {
                res.data.forEach((item, index) => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-3 fw-bold text-muted">${index + 1}</td>
                            <td class="fw-semibold text-truncate" style="max-width: 250px;">
                                ${item.nombre_producto}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3 py-2">${item.total_vendido}</span>
                            </td>
                            <td class="text-end pe-3 fw-bold text-success">
                                $${parseFloat(item.total_recaudado).toFixed(2)}
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No hay datos de productos suficientes para mostrar.</td>
                    </tr>
                `;
            }
        })
        .catch(err => console.error("Error al cargar top productos:", err));
}
</script>
@endsection