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
use Illuminate\Validation\ValidationException;
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
                ->get()
                ->map(fn (Compra $compra) => $this->mapCompra($compra)),
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
            $montoTotal = collect($validated['detalles'])->sum(fn (array $detalle) => $detalle['cantidad'] * $detalle['precio_unitario']);

            $compra = Compra::create([
                'estado' => $validated['estado'],
                'fecha_emision' => $validated['fecha_emision'],
                'monto_total' => $montoTotal,
                'observaciones' => $validated['observaciones'],
                'id_propietario' => request()->user()->propietario->id_usuario,
                'id_proveedor' => $validated['id_proveedor'],
            ]);

            foreach ($validated['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                DetalleCompra::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }
        });

        return redirect()->route('propietario.compras.index');
    }

    public function edit(Compra $compra): Response
    {
        $compra->load(['detalles.producto:id,nombre_comercial']);

        return Inertia::render('propietario/compras/Form', [
            'mode' => 'edit',
            'compra' => [
                'id' => $compra->id,
                'estado' => $compra->estado,
                'fecha_emision' => $compra->fecha_emision?->toDateString(),
                'observaciones' => $compra->observaciones,
                'id_proveedor' => $compra->id_proveedor,
                'detalles' => $compra->detalles->map(fn (DetalleCompra $detalle) => [
                    'id_producto' => $detalle->id_producto,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                ])->values(),
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
            $montoTotal = collect($validated['detalles'])->sum(fn (array $detalle) => $detalle['cantidad'] * $detalle['precio_unitario']);

            $compra->update([
                'estado' => $validated['estado'],
                'fecha_emision' => $validated['fecha_emision'],
                'monto_total' => $montoTotal,
                'observaciones' => $validated['observaciones'],
                'id_proveedor' => $validated['id_proveedor'],
            ]);

            $compra->detalles()->delete();

            foreach ($validated['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];

                DetalleCompra::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }
        });

        return redirect()->route('propietario.compras.index');
    }

    public function show(Compra $compra): Response
    {
        $compra->load(['proveedor.usuario:id,nombre,email', 'detalles.producto:id,nombre_comercial']);

        return Inertia::render('propietario/compras/Show', [
            'compra' => $this->mapCompra($compra),
            'puede_resolver_contraoferta' => $compra->estado === 'CONTRA_OFERTA',
        ]);
    }

    public function resolverContraoferta(Request $request, Compra $compra): RedirectResponse
    {
        if ($compra->estado !== 'CONTRA_OFERTA') {
            throw ValidationException::withMessages([
                'estado' => 'Esta compra no tiene una contraoferta pendiente.',
            ]);
        }

        $validated = $request->validate([
            'estado' => ['required', Rule::in(['APROBADO', 'RECHAZADO'])],
            'observaciones' => ['required_if:estado,RECHAZADO', 'nullable', 'string', 'min:3', 'max:1000'],
        ], [
            'estado.required' => 'Selecciona una respuesta.',
            'estado.in' => 'La respuesta seleccionada no es valida.',
            'observaciones.required_if' => 'Indica el motivo del rechazo.',
            'observaciones.min' => 'Las observaciones deben tener al menos 3 caracteres.',
        ]);

        $compra->load('detalles');
        $montoSolicitado = $compra->detalles->sum(fn (DetalleCompra $detalle) => (float) $detalle->subtotal);
        $montoContraoferta = $compra->detalles->sum(fn (DetalleCompra $detalle) => (float) ($detalle->subtotal_contraoferta ?? $detalle->subtotal));

        $compra->update([
            'estado' => $validated['estado'],
            'monto_total' => $validated['estado'] === 'APROBADO' ? $montoContraoferta : $montoSolicitado,
            'observaciones' => $validated['estado'] === 'RECHAZADO'
                ? ($validated['observaciones'] ?? $compra->observaciones)
                : $compra->observaciones,
        ]);

        return redirect()->route('propietario.compras.show', $compra);
    }

    /** @return array<string, mixed> */
    private function validateCompra(Request $request): array
    {
        return $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS)],
            'fecha_emision' => ['required', 'date'],
            'observaciones' => ['required', 'string', 'min:3', 'max:1000'],
            'id_proveedor' => ['required', 'integer', Rule::exists('proveedor', 'id_usuario')->whereNull('deleted_at')],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id_producto' => ['required', 'integer', Rule::exists('producto', 'id')->whereNull('deleted_at')],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es valido.',
            'fecha_emision.required' => 'La fecha de emision es obligatoria.',
            'fecha_emision.date' => 'La fecha de emision no es valida.',
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.min' => 'Las observaciones deben tener al menos 3 caracteres.',
            'id_proveedor.required' => 'Selecciona un proveedor.',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe.',
            'detalles.required' => 'Agrega al menos un producto.',
            'detalles.min' => 'Agrega al menos un producto.',
            'detalles.*.id_producto.required' => 'Selecciona un producto.',
            'detalles.*.id_producto.exists' => 'El producto seleccionado no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser numerico.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor a cero.',
        ]);
    }

    private function mapCompra(Compra $compra): array
    {
        $totalSolicitado = $compra->detalles->sum(fn (DetalleCompra $detalle) => (float) $detalle->subtotal);
        $totalContraoferta = $compra->detalles->sum(fn (DetalleCompra $detalle) => (float) ($detalle->subtotal_contraoferta ?? 0));

        return [
            'id' => $compra->id,
            'estado' => $compra->estado,
            'fecha_emision' => $compra->fecha_emision?->toDateString(),
            'monto_total' => $compra->monto_total,
            'total_solicitado' => $totalSolicitado,
            'total_contraoferta' => $totalContraoferta ?: null,
            'observaciones' => $compra->observaciones,
            'proveedor' => [
                'empresa' => $compra->proveedor?->empresa,
                'usuario' => [
                    'nombre' => $compra->proveedor?->usuario?->nombre,
                    'email' => $compra->proveedor?->usuario?->email,
                ],
            ],
            'detalles' => $compra->detalles->map(fn (DetalleCompra $detalle) => [
                'id' => $detalle->id,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->subtotal,
                'cantidad_contraoferta' => $detalle->cantidad_contraoferta,
                'precio_unitario_contraoferta' => $detalle->precio_unitario_contraoferta,
                'subtotal_contraoferta' => $detalle->subtotal_contraoferta,
                'diferencia_subtotal' => $detalle->subtotal_contraoferta !== null
                    ? round((float) $detalle->subtotal_contraoferta - (float) $detalle->subtotal, 2)
                    : null,
                'producto' => [
                    'id' => $detalle->producto?->id,
                    'nombre_comercial' => $detalle->producto?->nombre_comercial,
                ],
            ])->values(),
        ];
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
