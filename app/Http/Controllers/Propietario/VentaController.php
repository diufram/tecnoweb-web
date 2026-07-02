<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Inertia\Inertia;
use Inertia\Response;

class VentaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/ventas/Index', [
            'ventas' => Venta::query()
                ->with([
                    'cliente.usuario:id,nombre,email',
                    'detalles.inventario.producto:id,nombre_comercial',
                ])
                ->latest('fecha')
                ->latest('id')
                ->get()
                ->map(fn (Venta $venta) => $this->mapVenta($venta)),
        ]);
    }

    public function show(Venta $venta): Response
    {
        $venta->load([
            'cliente.usuario:id,nombre,email,ci_nit,direccion,telefono',
            'detalles.inventario.producto:id,nombre_comercial',
            'planPago.cuotas',
        ]);

        return Inertia::render('propietario/ventas/Show', [
            'venta' => $this->mapVentaDetallada($venta),
        ]);
    }

    /** @return array<string, mixed> */
    private function mapVenta(Venta $venta): array
    {
        return [
            'id'           => $venta->id,
            'estado_venta' => $venta->estado_venta,
            'fecha'        => $venta->fecha?->toDateString(),
            'total'        => $venta->total,
            'cliente'      => [
                'usuario' => [
                    'nombre' => $venta->cliente?->usuario?->nombre,
                    'email'  => $venta->cliente?->usuario?->email,
                ],
            ],
            'detalles' => $venta->detalles->map(fn (VentaDetalle $d) => [
                'producto' => [
                    'nombre_comercial' => $d->inventario?->producto?->nombre_comercial,
                ],
            ])->values(),
        ];
    }

    /** @return array<string, mixed> */
    private function mapVentaDetallada(Venta $venta): array
    {
        return [
            'id'           => $venta->id,
            'estado_venta' => $venta->estado_venta,
            'fecha'        => $venta->fecha?->toDateString(),
            'total'        => $venta->total,
            'cliente'      => [
                'linea_credito'  => $venta->cliente?->linea_credito,
                'nit_facturacion' => $venta->cliente?->nit_facturacion,
                'saldo_actual'   => $venta->cliente?->saldo_actual,
                'usuario'        => [
                    'nombre'    => $venta->cliente?->usuario?->nombre,
                    'email'     => $venta->cliente?->usuario?->email,
                    'ci_nit'    => $venta->cliente?->usuario?->ci_nit,
                    'direccion' => $venta->cliente?->usuario?->direccion,
                    'telefono'  => $venta->cliente?->usuario?->telefono,
                ],
            ],
            'detalles' => $venta->detalles->map(fn (VentaDetalle $d) => [
                'id'              => $d->id,
                'cantidad'        => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'subtotal'        => $d->subtotal,
                'producto'        => [
                    'nombre_comercial' => $d->inventario?->producto?->nombre_comercial,
                ],
            ])->values(),
            'plan_pago' => $venta->planPago ? [
                'id'          => $venta->planPago->id,
                'estado_plan' => $venta->planPago->estado_plan,
                'tipo_pago'   => $venta->planPago->tipo_pago,
                'cuotas'      => $venta->planPago->cuotas->map(fn ($cuota) => [
                    'id'                           => $cuota->id,
                    'nro_cuota'                    => $cuota->nro_cuota,
                    'fecha_vencimiento'            => $cuota->fecha_vencimiento,
                    'monto'                        => $cuota->monto,
                    'estado_cuota'                 => $cuota->estado_cuota,
                    'id_transaccion_pago_facil'    => $cuota->id_transaccion_pago_facil,
                ])->values(),
            ] : null,
        ];
    }
}
