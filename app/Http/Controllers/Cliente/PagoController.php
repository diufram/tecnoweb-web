<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Venta;
use App\Services\PagoFacilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PagoController extends Controller
{
    public function generarQr(Request $request, Venta $venta, PagoFacilService $pagoFacilService): RedirectResponse
    {
        $cliente = $request->user()->cliente;

        abort_unless($cliente && $venta->id_cliente === $cliente->id_usuario, 403);

        $venta->load(['cliente.usuario', 'planPago.cuotas']);
        $cuota = $venta->planPago?->cuotas
            ->filter(fn (Cuota $cuota) => strtoupper($cuota->estado_cuota) === 'PENDIENTE')
            ->sortBy('nro_cuota')
            ->first();

        if (! $cuota) {
            throw ValidationException::withMessages([
                'pago' => 'La venta no tiene cuotas pendientes.',
            ]);
        }

        $paymentNumber = PagoFacilService::generarPaymentNumber();
        $usuario = $venta->cliente->usuario;
        try {
            $pagoData = $pagoFacilService->generarQr(
                (float) $cuota->monto,
                'Pago cuota '.$cuota->nro_cuota.' venta '.$venta->id,
                $usuario->nombre,
                $usuario->ci_nit,
                $usuario->telefono,
                $usuario->email,
                (string) $usuario->id,
                $paymentNumber,
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'pago' => $exception->getMessage(),
            ]);
        }

        $cuota->update(['id_transaccion_pago_facil' => $pagoData['paymentNumber']]);

        return back()->with('pagoQr', [
            'qrBase64' => $pagoData['qrBase64'],
            'paymentNumber' => $pagoData['paymentNumber'],
            'transactionId' => $pagoData['transactionId'],
            'monto' => $cuota->monto,
            'venta_id' => $venta->id,
            'cuota_id' => $cuota->id,
            'nro_cuota' => $cuota->nro_cuota,
        ]);
    }
}
