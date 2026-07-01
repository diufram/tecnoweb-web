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
                        'id' => $detalle->producto->id,
                        'nombre_comercial' => $detalle->producto->nombre_comercial,
                    ],
                ])->values(),
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
            'detalles' => ['required_if:estado,CONTRA_OFERTA', 'array', 'min:1'],
            'detalles.*.id' => ['required_if:estado,CONTRA_OFERTA', 'integer', Rule::exists('detalle_compra', 'id')->whereNull('deleted_at')],
            'detalles.*.cantidad' => ['required_if:estado,CONTRA_OFERTA', 'integer', 'min:1', 'max:1000000'],
            'detalles.*.precio_unitario' => ['required_if:estado,CONTRA_OFERTA', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ], [
            'estado.required' => 'Selecciona una respuesta.',
            'estado.in' => 'La respuesta seleccionada no es valida.',
            'observaciones.required_if' => 'Indica el motivo del rechazo.',
            'observaciones.min' => 'Las observaciones deben tener al menos 3 caracteres.',
            'detalles.required_if' => 'Ingresa una contraoferta por producto.',
            'detalles.*.id.required_if' => 'La contraoferta tiene un producto invalido.',
            'detalles.*.id.exists' => 'La contraoferta tiene un producto invalido.',
            'detalles.*.cantidad.required_if' => 'Ingresa la cantidad propuesta.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'detalles.*.precio_unitario.required_if' => 'Ingresa el precio unitario propuesto.',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser numerico.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor a cero.',
        ]);

        DB::transaction(function () use ($compra, $validated) {
            $estado = $validated['estado'];

            if ($estado === 'CONTRA_OFERTA') {
                $detalles = $compra->detalles()->get()->keyBy('id');
                $montoTotal = 0;

                foreach ($validated['detalles'] as $detallePropuesto) {
                    $detalle = $detalles->get((int) $detallePropuesto['id']);

                    if (! $detalle) {
                        throw ValidationException::withMessages([
                            'compra' => 'La contraoferta contiene un producto que no pertenece a esta compra.',
                        ]);
                    }

                    $cantidad = (int) $detallePropuesto['cantidad'];
                    $precioUnitario = round((float) $detallePropuesto['precio_unitario'], 2);
                    $subtotal = round($cantidad * $precioUnitario, 2);
                    $montoTotal += $subtotal;

                    $detalle->update([
                        'cantidad_contraoferta' => $cantidad,
                        'precio_unitario_contraoferta' => $precioUnitario,
                        'subtotal_contraoferta' => $subtotal,
                    ]);
                }

                $compra->update([
                    'estado' => 'CONTRA_OFERTA',
                    'monto_total' => $montoTotal,
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
                'cantidad_contraoferta' => $detalle->cantidad_contraoferta,
                'precio_unitario_contraoferta' => $detalle->precio_unitario_contraoferta,
                'subtotal_contraoferta' => $detalle->subtotal_contraoferta,
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
