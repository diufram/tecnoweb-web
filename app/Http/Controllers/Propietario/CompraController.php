<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    private const ESTADOS = ['SOLICITUD', 'CONTRA_OFERTA', 'APROBADO', 'RECHAZADO'];

    public function index(): Response
    {
        return Inertia::render('propietario/compras/Index', [
            'compras' => Compra::query()
                ->with(['proveedor.usuario:id,nombre,email', 'detalles.producto:id,nombre_comercial'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/compras/Form', [
            'mode' => 'create',
            'compra' => null,
            'proveedores' => $this->proveedoresOptions(),
            'productos' => $this->productosOptions(),
            'estados' => self::ESTADOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCompra($request);

        DB::transaction(function () use ($validated) {
            $subtotal = $validated['cantidad'] * $validated['precio_unitario'];

            $compra = Compra::create([
                'estado' => $validated['estado'],
                'fecha_emision' => $validated['fecha_emision'],
                'monto_total' => $subtotal,
                'observaciones' => $validated['observaciones'],
                'id_propietario' => request()->user()->propietario->id_usuario,
                'id_proveedor' => $validated['id_proveedor'],
            ]);

            DetalleCompra::create([
                'id_compra' => $compra->id,
                'id_producto' => $validated['id_producto'],
                'cantidad' => $validated['cantidad'],
                'precio_unitario' => $validated['precio_unitario'],
                'subtotal' => $subtotal,
            ]);
        });

        return redirect()->route('propietario.compras.index');
    }

    public function edit(Compra $compra): Response
    {
        $compra->load(['detalles.producto:id,nombre_comercial']);
        $detalle = $compra->detalles->first();

        return Inertia::render('propietario/compras/Form', [
            'mode' => 'edit',
            'compra' => [
                'id' => $compra->id,
                'estado' => $compra->estado,
                'fecha_emision' => $compra->fecha_emision?->toDateString(),
                'observaciones' => $compra->observaciones,
                'id_proveedor' => $compra->id_proveedor,
                'id_producto' => $detalle?->id_producto,
                'cantidad' => $detalle?->cantidad,
                'precio_unitario' => $detalle?->precio_unitario,
            ],
            'proveedores' => $this->proveedoresOptions(),
            'productos' => $this->productosOptions(),
            'estados' => self::ESTADOS,
        ]);
    }

    public function update(Request $request, Compra $compra): RedirectResponse
    {
        $validated = $this->validateCompra($request);

        DB::transaction(function () use ($validated, $compra) {
            $subtotal = $validated['cantidad'] * $validated['precio_unitario'];

            $compra->update([
                'estado' => $validated['estado'],
                'fecha_emision' => $validated['fecha_emision'],
                'monto_total' => $subtotal,
                'observaciones' => $validated['observaciones'],
                'id_proveedor' => $validated['id_proveedor'],
            ]);

            $compra->detalles()->delete();
            DetalleCompra::create([
                'id_compra' => $compra->id,
                'id_producto' => $validated['id_producto'],
                'cantidad' => $validated['cantidad'],
                'precio_unitario' => $validated['precio_unitario'],
                'subtotal' => $subtotal,
            ]);
        });

        return redirect()->route('propietario.compras.index');
    }

    /** @return array<string, mixed> */
    private function validateCompra(Request $request): array
    {
        return $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS)],
            'fecha_emision' => ['required', 'date'],
            'observaciones' => ['required', 'string', 'min:3', 'max:1000'],
            'id_proveedor' => ['required', 'integer', Rule::exists('proveedor', 'id_usuario')->whereNull('deleted_at')],
            'id_producto' => ['required', 'integer', Rule::exists('producto', 'id')->whereNull('deleted_at')],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es valido.',
            'fecha_emision.required' => 'La fecha de emision es obligatoria.',
            'fecha_emision.date' => 'La fecha de emision no es valida.',
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.min' => 'Las observaciones deben tener al menos 3 caracteres.',
            'id_proveedor.required' => 'Selecciona un proveedor.',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe.',
            'id_producto.required' => 'Selecciona un producto.',
            'id_producto.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio unitario debe ser numerico.',
            'precio_unitario.min' => 'El precio unitario debe ser mayor a cero.',
        ]);
    }

    private function proveedoresOptions(): mixed
    {
        return Proveedor::query()
            ->with('usuario:id,nombre')
            ->orderBy('empresa')
            ->get(['id_usuario', 'empresa'])
            ->map(fn (Proveedor $proveedor) => [
                'id' => $proveedor->id_usuario,
                'empresa' => $proveedor->empresa,
            ]);
    }

    private function productosOptions(): mixed
    {
        return Producto::query()
            ->orderBy('nombre_comercial')
            ->get(['id', 'nombre_comercial']);
    }
}
