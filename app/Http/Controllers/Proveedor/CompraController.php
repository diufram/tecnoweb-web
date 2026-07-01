<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    private const ESTADOS_RESPUESTA = ['APROBADO', 'RECHAZADO', 'CONTRA_OFERTA'];

    public function solicitudes(Request $request): Response
    {
        return $this->renderList($request, 'proveedor/Solicitudes', ['SOLICITUD']);
    }

    public function contraofertas(Request $request): Response
    {
        return $this->renderList($request, 'proveedor/Contraofertas', ['CONTRA_OFERTA']);
    }

    public function compras(Request $request): Response
    {
        return $this->renderList($request, 'proveedor/Compras', ['APROBADO', 'RECHAZADO']);
    }

    public function historial(Request $request): Response
    {
        return $this->renderList($request, 'proveedor/Historial', ['SOLICITUD', 'CONTRA_OFERTA', 'APROBADO', 'RECHAZADO']);
    }

    public function show(Request $request, Compra $compra): Response
    {
        $this->asegurarProveedor($request, $compra);
        $compra->load([
            'detalles.producto:id,nombre_comercial',
            'propietario.usuario:id,nombre,email',
        ]);
        $detalle = $compra->detalles->first();

        return Inertia::render('proveedor/Show', [
            'compra' => [
                'id' => $compra->id,
                'estado' => $compra->estado,
                'fecha_emision' => $compra->fecha_emision?->toDateString(),
                'observaciones' => $compra->observaciones,
                'monto_total' => $compra->monto_total,
                'id_proveedor' => $compra->id_proveedor,
                'propietario' => [
                    'nombre' => $compra->propietario?->usuario?->nombre,
                    'email' => $compra->propietario?->usuario?->email,
                ],
                'detalle' => $detalle ? [
                    'id' => $detalle->id,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                    'producto' => [
                        'id' => $detalle->producto->id,
                        'nombre_comercial' => $detalle->producto->nombre_comercial,
                    ],
                ] : null,
            ],
            'puede_contraofertar' => in_array($compra->estado, ['SOLICITUD', 'CONTRA_OFERTA'], true),
        ]);
    }

    public function responder(Request $request, Compra $compra): RedirectResponse
    {
        $this->asegurarProveedor($request, $compra);

        if (! in_array($compra->estado, ['SOLICITUD', 'CONTRA_OFERTA'], true)) {
            throw ValidationException::withMessages([
                'estado' => 'Esta compra ya no se puede responder.',
            ]);
        }

        $validated = $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS_RESPUESTA)],
            'observaciones' => ['required_if:estado,RECHAZADO', 'nullable', 'string', 'min:3', 'max:1000'],
            'cantidad' => ['required_if:estado,CONTRA_OFERTA', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'precio_unitario' => ['required_if:estado,CONTRA_OFERTA', 'nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ], [
            'estado.required' => 'Selecciona una respuesta.',
            'estado.in' => 'La respuesta seleccionada no es valida.',
            'observaciones.required_if' => 'Indica el motivo del rechazo.',
            'observaciones.min' => 'Las observaciones deben tener al menos 3 caracteres.',
            'cantidad.required_if' => 'Ingresa la cantidad propuesta.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'precio_unitario.required_if' => 'Ingresa el precio unitario propuesto.',
            'precio_unitario.numeric' => 'El precio unitario debe ser numerico.',
            'precio_unitario.min' => 'El precio unitario debe ser mayor a cero.',
        ]);

        DB::transaction(function () use ($compra, $validated) {
            $estado = $validated['estado'];

            if ($estado === 'CONTRA_OFERTA') {
                $cantidad = (int) $validated['cantidad'];
                $precioUnitario = round((float) $validated['precio_unitario'], 2);
                $subtotal = round($cantidad * $precioUnitario, 2);

                $detalleAnterior = DetalleCompra::withTrashed()
                    ->where('id_compra', $compra->id)
                    ->orderByDesc('id')
                    ->first();

                if (! $detalleAnterior) {
                    throw ValidationException::withMessages([
                        'compra' => 'La compra no tiene un detalle editable.',
                    ]);
                }

                $compra->update([
                    'estado' => 'CONTRA_OFERTA',
                    'monto_total' => $subtotal,
                ]);

                $compra->detalles()->delete();
                DetalleCompra::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $detalleAnterior->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                return;
            }

            $compra->update([
                'estado' => $estado,
                'observaciones' => $estado === 'RECHAZADO'
                    ? ($validated['observaciones'] ?? $compra->observaciones)
                    : $compra->observaciones,
            ]);
        });

        return redirect()->route('proveedor.historial')->with('flash', [
            'mensaje' => 'Tu respuesta se registro correctamente.',
        ]);
    }

    private function renderList(Request $request, string $component, array $estados): Response
    {
        $proveedorId = $this->proveedorId($request);

        $compras = $this->baseQuery($proveedorId)
            ->whereIn('estado', $estados)
            ->orderByDesc('fecha_emision')
            ->get()
            ->map(fn (Compra $compra) => $this->mapCompra($compra));

        return Inertia::render($component, [
            'compras' => $compras->values(),
        ]);
    }

    private function baseQuery(int $proveedorId): Builder
    {
        return Compra::query()
            ->with(['proveedor.usuario:id,nombre,email', 'detalles.producto:id,nombre_comercial'])
            ->where('id_proveedor', $proveedorId);
    }

    private function mapCompra(Compra $compra): array
    {
        return [
            'id' => $compra->id,
            'estado' => $compra->estado,
            'fecha_emision' => $compra->fecha_emision?->toDateString(),
            'monto_total' => $compra->monto_total,
            'observaciones' => $compra->observaciones,
            'proveedor' => [
                'empresa' => $compra->proveedor?->empresa,
                'usuario' => $compra->proveedor?->usuario?->nombre,
            ],
            'detalles' => $compra->detalles->map(fn (DetalleCompra $detalle) => [
                'id' => $detalle->id,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->subtotal,
                'producto' => [
                    'id' => $detalle->producto->id,
                    'nombre_comercial' => $detalle->producto->nombre_comercial,
                ],
            ])->values(),
        ];
    }

    private function asegurarProveedor(Request $request, Compra $compra): void
    {
        $proveedorId = $this->proveedorId($request);
        abort_unless($compra->id_proveedor === $proveedorId, 403);
    }

    private function proveedorId(Request $request): int
    {
        $proveedorId = $request->user()->proveedor?->id_usuario;
        abort_unless($proveedorId, 403);

        $proveedor = Proveedor::query()->whereKey($proveedorId)->first();
        abort_unless($proveedor, 403);

        return $proveedor->id_usuario;
    }
}
