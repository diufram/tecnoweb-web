<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoFacilCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $pedidoId = trim((string) $request->input('PedidoID'));
        $estado = (int) $request->input('Estado', -1);

        if ($pedidoId === '') {
            return response()->json(['ok' => false, 'message' => 'PedidoID es obligatorio'], 400);
        }

        $encontrado = DB::transaction(function () use ($pedidoId, $estado) {
            $cuota = Cuota::query()
                ->with(['planPago.venta.cliente'])
                ->where('id_transaccion_pago_facil', $pedidoId)
                ->lockForUpdate()
                ->first();

            if (! $cuota) {
                return false;
            }

            if ($estado === 2 && strtoupper($cuota->estado_cuota) !== 'PAGADO') {
                $cuota->update(['estado_cuota' => 'PAGADO']);

                if ($cuota->planPago->tipo_pago === 'CREDITO') {
                    $cuota->planPago->venta->cliente->decrement('saldo_actual', (float) $cuota->monto);
                }
            }

            $plan = $cuota->planPago()->with('cuotas')->first();
            $todasPagadas = $plan->cuotas->isNotEmpty()
                && $plan->cuotas->every(fn (Cuota $cuota) => strtoupper($cuota->estado_cuota) === 'PAGADO');

            if ($todasPagadas) {
                $plan->update(['estado_plan' => 'PAGADO']);
                $plan->venta->update(['estado_venta' => 'PAGADO']);
            } elseif ($estado === 2) {
                $plan->update(['estado_plan' => 'PENDIENTE']);
                $plan->venta->update(['estado_venta' => 'PENDIENTE_PAGO']);
            }

            return true;
        });

        if (! $encontrado) {
            return response()->json(['ok' => false, 'message' => 'PedidoID no encontrado'], 404);
        }

        return response()->json([
            'ok' => true,
            'message' => $estado === 2 ? 'Pago procesado' : 'Callback recibido sin marcar pago',
        ]);
    }
}
