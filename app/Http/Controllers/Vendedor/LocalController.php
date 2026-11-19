<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Vendedor\Local;
use App\Models\Vendedor\LocalImagen;
use App\Models\Vendedor\LocalHorario;
use App\Models\Vendedor\LocalZonaEntrega; 
use App\Models\ProductoVariante;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LocalController extends Controller
{
    public function index()
    {       
        DB::statement('CALL actualizar_estado_locales_vendedor(?)', [Auth::id()]);

        $locales = Local::where('vendedor_id', Auth::id())
            ->latest()
            ->get();

        return view('vendedor.locales.index', compact('locales'));
    }

    public function create()
    {
        return view('vendedor.locales.create');
    }
public function store(Request $request)
{
    $request->validate([
        'nombre' => ['required', 'string', 'max:150'],
        'descripcion' => ['nullable', 'string'],
        'telefono' => ['nullable', 'string', 'max:20'],
        'whatsapp' => ['nullable', 'string', 'max:20'],
        'correo' => ['nullable', 'email', 'max:191'],
        'direccion' => ['nullable', 'string'],
        'ciudad' => ['nullable', 'string', 'max:150'],
        'estado' => ['nullable', 'string', 'max:150'],
        
        // 📍 Validación de latitud y longitud agregada
        'latitud' => ['nullable', 'numeric', 'between:-90,90'],
        'longitud' => ['nullable', 'numeric', 'between:-180,180'],

        'minimo_pedido' => ['nullable', 'numeric', 'min:0'],
        'costo_envio' => ['nullable', 'numeric', 'min:0'],

        'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

        'horarios' => ['nullable', 'array'],
        'horarios.*.dia' => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
        'horarios.*.hora_apertura' => ['nullable', 'date_format:H:i'],
        'horarios.*.hora_cierre' => ['nullable', 'date_format:H:i'],

        'zonas_entrega' => ['nullable', 'array'],
        'zonas_entrega.*.ciudad' => ['nullable', 'string', 'max:150'],
        'zonas_entrega.*.estado' => ['nullable', 'string', 'max:150'],
        'zonas_entrega.*.costo_envio' => ['nullable', 'numeric', 'min:0'],
        'zonas_entrega.*.activo' => ['nullable'],
    ], [
        'nombre.required' => 'El nombre del local es obligatorio.',
        'nombre.max' => 'El nombre no debe pasar de 150 caracteres.',
        'correo.email' => 'Ingresa un correo válido.',
        'correo.max' => 'El correo no debe pasar de 191 caracteres.',
        'telefono.max' => 'El teléfono no debe pasar de 20 caracteres.',
        'whatsapp.max' => 'El WhatsApp no debe pasar de 20 caracteres.',
        'logo.image' => 'El logo debe ser una imagen.',
        'portada.image' => 'La portada debe ser una imagen.',
        'imagenes.*.image' => 'Las imágenes extras deben ser imágenes válidas.',
        'latitud.numeric' => 'La latitud debe ser un número válido.',
        'longitud.numeric' => 'La longitud debe ser un número válido.',
    ]);

    DB::transaction(function () use ($request) {
        $logoPath = null;
        $portadaPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('locales/logos', 'public');
        }

        if ($request->hasFile('portada')) {
            $portadaPath = $request->file('portada')->store('locales/portadas', 'public');
        }

        $permiteDelivery = $request->boolean('permite_delivery');

        $local = Local::create([
            'vendedor_id' => Auth::id(),
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'logo' => $logoPath,
            'portada' => $portadaPath,
            'telefono' => $request->telefono,
            'whatsapp' => $request->whatsapp,
            'correo' => $request->correo,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'estado' => $request->estado,
            
            // 📍 Asignación de coordenadas
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,

            'minimo_pedido' => $request->minimo_pedido ?? 0,
            'costo_envio' => $request->costo_envio ?? 0,
            'permite_delivery' => $permiteDelivery,
            'permite_recoger' => $request->boolean('permite_recoger'),
            'estado_local' => 'cerrado',
            'activo' => 1,
        ]);

        // Guardar zona principal si hay ciudad definida
        if (!empty($local->ciudad)) {
            LocalZonaEntrega::firstOrCreate(
                [
                    'local_id' => $local->id,
                    'ciudad' => $local->ciudad,
                ],
                [
                    'estado' => $local->estado,
                    'costo_envio' => $local->costo_envio ?? 0,
                    'activo' => $permiteDelivery ? 1 : 0,
                ]
            );
        }

        // Guardar zonas adicionales enviadas desde el formulario
        if ($request->has('zonas_entrega')) {
            foreach ($request->zonas_entrega as $zona) {
                if (empty($zona['ciudad'])) {
                    continue;
                }

                LocalZonaEntrega::create([
                    'local_id' => $local->id,
                    'ciudad' => $zona['ciudad'],
                    'estado' => $zona['estado'] ?? $local->estado,
                    'costo_envio' => $zona['costo_envio'] ?? 0,
                    'activo' => $permiteDelivery && isset($zona['activo']) ? 1 : 0,
                ]);
            }
        }

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $path = $imagen->store('locales/imagenes', 'public');

                LocalImagen::create([
                    'local_id' => $local->id,
                    'imagen' => $path,
                    'orden' => $index,
                ]);
            }
        }

        if ($request->has('horarios')) {
            foreach ($request->horarios as $horario) {
                $cerrado = isset($horario['cerrado']) ? 1 : 0;

                LocalHorario::create([
                    'local_id' => $local->id,
                    'dia' => $horario['dia'],
                    'hora_apertura' => $cerrado ? null : ($horario['hora_apertura'] ?? null),
                    'hora_cierre' => $cerrado ? null : ($horario['hora_cierre'] ?? null),
                    'cerrado' => $cerrado,
                ]);
            }
        }
    });

    return redirect()
        ->route('vendedor.dashboard.locales')
        ->with('success', 'Local creado correctamente.');
}
    public function show($id)
    {
        DB::statement('CALL actualizar_estado_locales_vendedor(?)', [Auth::id()]);

        $local = Local::with('zonasEntrega')
            ->where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        return view('vendedor.locales.show', compact('local'));
    }

    public function horarios($id)
    {
        $local = Local::with('horarios')
            ->where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        return view('vendedor.locales.horarios', compact('local'));
    }

    public function actualizarHorarios(Request $request, $id)
    {
        $local = Local::where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'horarios' => ['required', 'array'],
            'horarios.*.dia' => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'horarios.*.hora_apertura' => ['nullable', 'date_format:H:i'],
            'horarios.*.hora_cierre' => ['nullable', 'date_format:H:i'],
            'horarios.*.cerrado' => ['nullable'],
        ]);

        foreach ($request->horarios as $horario) {
            $cerrado = isset($horario['cerrado']) ? 1 : 0;

            $local->horarios()->updateOrCreate(
                [
                    'local_id' => $local->id,
                    'dia' => $horario['dia'],
                ],
                [
                    'hora_apertura' => $cerrado ? null : ($horario['hora_apertura'] ?? null),
                    'hora_cierre' => $cerrado ? null : ($horario['hora_cierre'] ?? null),
                    'cerrado' => $cerrado,
                ]
            );
        }

        return redirect()
            ->route('vendedor.locales.horarios', $local->id)
            ->with('success', 'Horarios actualizados correctamente.');
    }

    public function edit($id)
    {
        $local = Local::with('zonasEntrega')
            ->where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        return view('vendedor.locales.edit', compact('local'));
    }

    public function pausar($id)
    {
        $local = Local::where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($local) {
            if ($local->estado_local === 'pausado') {
                $local->update([
                    'estado_local' => 'abierto',
                    'activo' => 1,
                ]);

                Producto::where('local_id', $local->id)->update(['activo' => 1]);

                ProductoVariante::whereHas('producto', function ($query) use ($local) {
                    $query->where('local_id', $local->id);
                })->update(['activo' => 1]);
            } else {
                $local->update([
                    'estado_local' => 'pausado',
                    'activo' => 1,
                ]);

                Producto::where('local_id', $local->id)->update(['activo' => 0]);

                ProductoVariante::whereHas('producto', function ($query) use ($local) {
                    $query->where('local_id', $local->id);
                })->update(['activo' => 0]);
            }
        });

        $local->refresh();

        $mensaje = $local->estado_local === 'pausado'
            ? 'Local pausado correctamente. Sus productos y variantes ya no serán públicos.'
            : 'Local activado correctamente. Sus productos y variantes vuelven a estar públicos.';

        return redirect()
            ->route('vendedor.dashboard.locales')
            ->with('success', $mensaje);
    }

    public function destroy($id)
    {
        $local = Local::where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        DB::transaction(function () use ($local) {
            $local->update([
                'activo' => 0,
                'estado_local' => 'cerrado',
            ]);

            Producto::where('local_id', $local->id)->update(['activo' => 0]);
        });

        return redirect()
            ->route('vendedor.dashboard.locales')
            ->with('success', 'Local eliminado correctamente. Sus productos quedaron no públicos.');
    }

    public function update(Request $request, $id)
    {
        $local = Local::with('zonasEntrega')
            ->where('id', $id)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:191'],
            'direccion' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'string', 'max:150'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
            'minimo_pedido' => ['nullable', 'numeric', 'min:0'],
            'costo_envio' => ['nullable', 'numeric', 'min:0'],

            'activo' => ['nullable'],
            'permite_delivery' => ['nullable'],
            'permite_recoger' => ['nullable'],

            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'zonas_entrega' => ['nullable', 'array'],
            'zonas_entrega.*.id' => ['nullable', 'integer'],
            'zonas_entrega.*.ciudad' => ['nullable', 'string', 'max:150'],
            'zonas_entrega.*.estado' => ['nullable', 'string', 'max:150'],
            'zonas_entrega.*.costo_envio' => ['nullable', 'numeric', 'min:0'],
            'zonas_entrega.*.activo' => ['nullable'],
        ], [
            'nombre.required' => 'El nombre del local es obligatorio.',
            'nombre.max' => 'El nombre no debe pasar de 150 caracteres.',
            'correo.email' => 'Ingresa un correo válido.',
            'telefono.max' => 'El teléfono no debe pasar de 20 caracteres.',
            'whatsapp.max' => 'El WhatsApp no debe pasar de 20 caracteres.',
            'logo.image' => 'El logo debe ser una imagen válida.',
            'portada.image' => 'La portada debe ser una imagen válida.',
            'imagenes.*.image' => 'Las imágenes extras deben ser imágenes válidas.',
        ]);

        try {
            DB::transaction(function () use ($request, $local) {
                $logoPath = $local->logo;
                $portadaPath = $local->portada;

                if ($request->hasFile('logo')) {
                    if ($local->logo && Storage::disk('public')->exists($local->logo)) {
                        Storage::disk('public')->delete($local->logo);
                    }
                    $logoPath = $request->file('logo')->store('locales/logos', 'public');
                }

                if ($request->hasFile('portada')) {
                    if ($local->portada && Storage::disk('public')->exists($local->portada)) {
                        Storage::disk('public')->delete($local->portada);
                    }
                    $portadaPath = $request->file('portada')->store('locales/portadas', 'public');
                }

                $activo = $request->boolean('activo', true);
                $permiteDelivery = $request->boolean('permite_delivery');

                $local->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'logo' => $logoPath,
                    'portada' => $portadaPath,
                    'telefono' => $request->telefono,
                    'whatsapp' => $request->whatsapp,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud,
                    'minimo_pedido' => $request->minimo_pedido ?? 0,
                    'costo_envio' => $request->costo_envio ?? 0,
                    'permite_delivery' => $permiteDelivery,
                    'permite_recoger' => $request->boolean('permite_recoger'),
                    'activo' => $activo,
                ]);

                if (!$activo) {
                    Producto::where('local_id', $local->id)->update(['activo' => 0]);
                }

                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $index => $imagen) {
                        $path = $imagen->store('locales/imagenes', 'public');

                        LocalImagen::create([
                            'local_id' => $local->id,
                            'imagen' => $path,
                            'orden' => $index,
                        ]);
                    }
                }

                // Sincronización de zonas de entrega
                $zonasEnviadas = [];

                if ($request->has('zonas_entrega') && is_array($request->zonas_entrega)) {
                    foreach ($request->zonas_entrega as $zona) {
                        if (empty($zona['ciudad'])) {
                            continue;
                        }

                        $zonaId = $zona['id'] ?? null;
                        $estadoZona = $permiteDelivery && isset($zona['activo']) ? 1 : 0;

                        if ($zonaId) {
                            $zonaEntrega = LocalZonaEntrega::where('id', $zonaId)
                                ->where('local_id', $local->id)
                                ->first();

                            if ($zonaEntrega) {
                                $zonaEntrega->update([
                                    'ciudad' => $zona['ciudad'],
                                    'estado' => $zona['estado'] ?? $local->estado,
                                    'costo_envio' => $zona['costo_envio'] ?? 0,
                                    'activo' => $estadoZona,
                                ]);
                                $zonasEnviadas[] = $zonaEntrega->id;
                            }
                        } else {
                            $zonaEntrega = LocalZonaEntrega::create([
                                'local_id' => $local->id,
                                'ciudad' => $zona['ciudad'],
                                'estado' => $zona['estado'] ?? $local->estado,
                                'costo_envio' => $zona['costo_envio'] ?? 0,
                                'activo' => $estadoZona,
                            ]);
                            $zonasEnviadas[] = $zonaEntrega->id;
                        }
                    }
                }

                if (!$permiteDelivery) {
                    LocalZonaEntrega::where('local_id', $local->id)->update(['activo' => 0]);
                } else {
                    LocalZonaEntrega::where('local_id', $local->id)
                        ->whereNotIn('id', $zonasEnviadas)
                        ->delete();
                }
            });

            return redirect()
                ->route('vendedor.locales.show', $local->id)
                ->with('success', 'Local actualizado correctamente.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}