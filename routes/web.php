<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\Vendedor\LocalController;
use App\Http\Controllers\Vendedor\EstadisticasController;
use App\Http\Controllers\Vendedor\ProductoController;
use App\Http\Controllers\Vendedor\StockController;
use App\Http\Controllers\Vendedor\PedidoController as VendedorPedidoController;
use App\Http\Controllers\Vendedor\LocalZonaEntregaController;

use App\Http\Controllers\Cliente\DashboardController as ClienteDashboardController;
use App\Http\Controllers\Cliente\CatalogoController;
use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\DireccionController as ClienteDireccionController;
use App\Http\Controllers\Cliente\PedidoController as ClientePedidoController;
use App\Http\Controllers\Cliente\FavoritoController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::view('/terminos-y-condiciones', 'legal.terminos')
    ->name('terminos');

Route::view('/politica-de-privacidad', 'legal.privacidad')
    ->name('privacidad');

Route::post('/logout', [LogoutController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard general por rol
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\NotificacionController;

Route::middleware('auth')->group(function () {
    Route::get('/notificaciones', [NotificacionController::class, 'index'])
        ->name('notificaciones.index');

    Route::patch('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida'])
        ->name('notificaciones.leer');
});
Route::middleware('auth')->group(function () {
    Route::get('/notificaciones/contador', [NotificacionController::class, 'contador'])
        ->name('notificaciones.contador');
});
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role_id == 1) {
        return redirect()->route('cliente.catalogo.index');
    }

    if ($user->role_id == 2) {
        return redirect()->route('vendedor.dashboard.locales');
    }

    if ($user->role_id == 3) {
        return redirect()->route('admin.dashboard');
    }

    abort(403);
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'role:admin'])->name('admin.dashboard');

/*
|--------------------------------------------------------------------------
| Vendedor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:vendedor'])->group(function () {

    /*
     * Estadísticas.
     * Las URLs coinciden con las que consume resources/views/vendedor/estadisticas.blade.php
     * (fetch a /vendedor/estadisticas/ventas-periodo y /vendedor/estadisticas/top-productos).
     */
    Route::get('/vendedor/estadisticas', [EstadisticasController::class, 'index'])
        ->name('vendedor.estadisticas');

    Route::get('/vendedor/estadisticas/resumen', [EstadisticasController::class, 'resumenGeneral'])
        ->name('vendedor.estadisticas.resumen');

    Route::get('/vendedor/estadisticas/ventas-periodo', [EstadisticasController::class, 'ventasPorPeriodo'])
        ->name('vendedor.estadisticas.ventasPeriodo');

    Route::get('/vendedor/estadisticas/top-productos', [EstadisticasController::class, 'productosMasVendidos'])
        ->name('vendedor.estadisticas.topProductos');

    Route::get('/vendedor/estadisticas/pedidos-estado', [EstadisticasController::class, 'pedidosPorEstado'])
        ->name('vendedor.estadisticas.pedidosEstado');

    Route::get('/vendedor/dashboard', function () {
        return view('layouts.dashboard');
    })->name('vendedor.dashboard');

    Route::get('/vendedor/dashboardver', function () {
        return view('vendedor.dashboard');
    })->name('vendedor.dashboardVer');

    Route::get('/vendedor/locales', [LocalController::class, 'index'])
        ->name('vendedor.dashboard.locales');

    Route::get('/vendedor/locales/crear', [LocalController::class, 'create'])
        ->name('vendedor.locales.create');

    Route::post('/vendedor/locales', [LocalController::class, 'store'])
        ->name('vendedor.locales.store');

    Route::get('/vendedor/locales/{id}', [LocalController::class, 'show'])
        ->name('vendedor.locales.show');

    Route::get('/vendedor/locales/{id}/horarios', [LocalController::class, 'horarios'])
        ->name('vendedor.locales.horarios');

    Route::put('/vendedor/locales/{id}/horarios', [LocalController::class, 'actualizarHorarios'])
        ->name('vendedor.locales.horarios.update');

    Route::get('/vendedor/locales/{local}/productos', [ProductoController::class, 'index'])
        ->name('vendedor.locales.productos.index');

    Route::get('/vendedor/locales/{local}/productos/crear', [ProductoController::class, 'create'])
        ->name('vendedor.locales.productos.create');

    Route::post('/vendedor/locales/{local}/productos', [ProductoController::class, 'store'])
        ->name('vendedor.locales.productos.store');

    Route::get('/vendedor/locales/{local}/productos/{producto}/editar', [ProductoController::class, 'edit'])
        ->name('vendedor.locales.productos.edit');

    Route::put('/vendedor/locales/{local}/productos/{producto}', [ProductoController::class, 'update'])
        ->name('vendedor.locales.productos.update');

    Route::patch('/vendedor/locales/{local}/productos/{producto}/estado', [ProductoController::class, 'cambiarEstado'])
        ->name('vendedor.locales.productos.estado');

    Route::get('/vendedor/productos', [ProductoController::class, 'todos'])
        ->name('vendedor.productos.todos');

    Route::get('/vendedor/stock', [StockController::class, 'index'])
        ->name('vendedor.stock.index');

    Route::put('/vendedor/stock/{stock}', [StockController::class, 'update'])
        ->name('vendedor.stock.update');
    Route::get('vendedor/pedidos/enviar', [VendedorPedidoController::class, 'porEnviar'])
    ->name('vendedor.pedidos.enviar');
    Route::get('/vendedor/pedidos', [VendedorPedidoController::class, 'index'])
        ->name('vendedor.pedidos.index');

    Route::get('/vendedor/pedidos/{pedido}', [VendedorPedidoController::class, 'show'])
        ->name('vendedor.pedidos.show');

    Route::patch('/vendedor/pedidos/{pedido}/estado', [VendedorPedidoController::class, 'cambiarEstado'])
        ->name('vendedor.pedidos.estado');

    Route::get('/vendedor/locales/{local}/editar', [LocalController::class, 'edit'])
        ->name('vendedor.locales.edit');

    Route::put('/vendedor/locales/{local}', [LocalController::class, 'update'])
        ->name('vendedor.locales.update');

    Route::patch('/vendedor/locales/{local}/pausar', [LocalController::class, 'pausar'])
        ->name('vendedor.locales.pausar');

    Route::delete('/vendedor/locales/{local}', [LocalController::class, 'destroy'])
        ->name('vendedor.locales.destroy');

    Route::patch('/vendedor/locales/{local}/zonas/{zona}/estado', [LocalZonaEntregaController::class, 'cambiarEstado'])
        ->name('vendedor.locales.zonas.estado');

    Route::delete('/vendedor/locales/{local}/zonas/{zona}', [LocalZonaEntregaController::class, 'destroy'])
        ->name('vendedor.locales.zonas.destroy');
        Route::get('/pedidos/{pedido}/ticket', [VendedorPedidoController::class, 'ticket'])
    ->name('vendedor.pedidos.ticket');

});

/*
|--------------------------------------------------------------------------
| Cliente
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::post('/carrito/actualizar/{localId}/{itemKey}', [CarritoController::class, 'actualizarCantidad'])->name('cliente.carrito.actualizar');
    Route::delete('/carrito/eliminar/{localId}/{itemKey}', [CarritoController::class, 'eliminarItem'])->name('cliente.carrito.eliminar');
    Route::get('/cliente/dashboard', [ClienteDashboardController::class, 'index'])
        ->name('cliente.dashboard');

    Route::get('/cliente/catalogo', [CatalogoController::class, 'index'])
        ->name('cliente.catalogo.index');

    Route::get('/cliente/catalogo/local/{local}', [CatalogoController::class, 'local'])
        ->name('cliente.catalogo.local');

    Route::get('/cliente/catalogo/producto/{producto}', [CatalogoController::class, 'producto'])
        ->name('cliente.catalogo.producto');

    Route::get('/cliente/carrito', [CarritoController::class, 'index'])
        ->name('cliente.carrito.index');

    Route::post('/cliente/carrito/agregar/{producto}', [CarritoController::class, 'agregar'])
        ->name('cliente.carrito.agregar');

    Route::post('/cliente/carrito/confirmar/{local}', [CarritoController::class, 'confirmarLocal'])
        ->name('cliente.carrito.confirmarLocal');

    Route::post('/cliente/carrito/confirmar-todos', [CarritoController::class, 'confirmarTodos'])
        ->name('cliente.carrito.confirmarTodos');

    Route::get('/cliente/direcciones', [ClienteDireccionController::class, 'index'])
        ->name('cliente.direcciones.index');

    Route::get('/cliente/direcciones/crear', [ClienteDireccionController::class, 'create'])
        ->name('cliente.direcciones.create');

    Route::post('/cliente/direcciones', [ClienteDireccionController::class, 'store'])
        ->name('cliente.direcciones.store');

    Route::get('/cliente/direcciones/{direccion}', [ClienteDireccionController::class, 'show'])
        ->name('cliente.direcciones.show');

    Route::get('/cliente/direcciones/{direccion}/editar', [ClienteDireccionController::class, 'edit'])
        ->name('cliente.direcciones.edit');

    Route::put('/cliente/direcciones/{direccion}', [ClienteDireccionController::class, 'update'])
        ->name('cliente.direcciones.update');

    Route::delete('/cliente/direcciones/{direccion}', [ClienteDireccionController::class, 'destroy'])
        ->name('cliente.direcciones.destroy');

    /*
     * Mantengo los dos nombres porque ya los usas dentro del proyecto.
     * Ambos apuntan al mismo controlador correcto de cliente.
     */
    Route::get('/cliente/pedidos', [ClientePedidoController::class, 'index'])
        ->name('cliente.pedidos.ver');

    Route::get('/cliente/pedidos-lista', [ClientePedidoController::class, 'index'])
        ->name('cliente.pedidos.index');

    Route::get('/cliente/pedidos/{pedido}', [ClientePedidoController::class, 'show'])
        ->name('cliente.pedidos.show');

    Route::patch('/cliente/pedidos/{pedido}/cancelar', [ClientePedidoController::class, 'cancelar'])
        ->name('cliente.pedidos.cancelar');

    Route::post('/cliente/productos/{producto}/favorito', [FavoritoController::class, 'toggle'])
        ->name('cliente.productos.favorito');

    Route::get('/cliente/favoritos', [FavoritoController::class, 'index'])
        ->name('cliente.favoritos.index');
});

///admin enlaces privados
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminUsuarioController;
use App\Http\Controllers\Admin\AdminLocalController;
use App\Http\Controllers\Admin\AdminPedidoController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/usuarios', [AdminUsuarioController::class, 'index'])
        ->name('admin.usuarios.index');

    Route::patch('/usuarios/{usuario}/estado', [AdminUsuarioController::class, 'cambiarEstado'])
        ->name('admin.usuarios.estado');

    Route::get('/locales', [AdminLocalController::class, 'index'])
        ->name('admin.locales.index');

    Route::patch('/locales/{local}/estado', [AdminLocalController::class, 'cambiarEstado'])
        ->name('admin.locales.estado');

    Route::get('/pedidos', [AdminPedidoController::class, 'index'])
        ->name('admin.pedidos.index');

    Route::get('/pedidos/{pedido}', [AdminPedidoController::class, 'show'])
        ->name('admin.pedidos.show');
});