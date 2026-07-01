<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Inventario;
use App\Models\PlanPago;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\PagoFacilService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CarritoController extends Controller
{
    private const SESSION_KEY = 'cliente.carrito';

    private const TIPOS_PAGO = ['CONTADO', 'CREDITO'];

    public function index(Request $request): Response
    {
        $items = $this->items($request, true);
        $cliente = $request->user()->cliente;

        return Inertia::render('cliente/Carrito', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'credito' => [
                'linea_credito' => $cliente->linea_credito,
                'saldo_actual' => $cliente->saldo_actual,
                'disponible' => (float) $cliente->linea_credito - (float) $cliente->saldo_actual,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_inventario' => ['required', 'integer', Rule::exists('inventario', 'id')->whereNull('deleted_at')],
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'id_inventario.required' => 'Selecciona un producto del catalogo.',
            'id_inventario.exists' => 'El producto seleccionado no esta disponible.',
            'cantidad.required' => 'Ingresa la cantidad que quieres agregar.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $inventario = Inventario::query()->whereKey($validated['id_inventario'])->firstOrFail();
        $cart = $this->cart($request);
        $id = (string) $inventario->id;
        $cantidad = (int) $validated['cantidad'];
        $nuevaCantidad = ($cart[$id] ?? 0) + $cantidad;

        if ($nuevaCantidad > $inventario->cantidad_disponible) {
            throw ValidationException::withMessages([
                'cantidad' => 'Solo hay '.$inventario->cantidad_disponible.' unidades disponibles.',
            ]);
        }

        $cart[$id] = $nuevaCantidad;
        $request->session()->put(self::SESSION_KEY, $cart);

        return back();
    }

    public function update(Request $request, Inventario $inventario): RedirectResponse
    {
        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'cantidad.required' => 'Ingresa la cantidad.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        if ((int) $validated['cantidad'] > $inventario->cantidad_disponible) {
            throw ValidationException::withMessages([
                'cantidad' => 'Solo hay '.$inventario->cantidad_disponible.' unidades disponibles.',
            ]);
        }

        $cart = $this->cart($request);
        $cart[(string) $inventario->id] = (int) $validated['cantidad'];
        $request->session()->put(self::SESSION_KEY, $cart);

        return back();
    }

    public function destroy(Request $request, Inventario $inventario): RedirectResponse
    {
        $cart = $this->cart($request);
        unset($cart[(string) $inventario->id]);
        $request->session()->put(self::SESSION_KEY, $cart);

        return back();
    }

    public function checkout(Request $request, PagoFacilService $pagoFacilService): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_pago' => ['required', Rule::in(self::TIPOS_PAGO)],
            'cuotas' => ['required_if:tipo_pago,CREDITO', 'integer', 'min:1', 'max:12'],
        ], [
            'tipo_pago.required' => 'Selecciona el tipo de pago.',
            'tipo_pago.in' => 'El tipo de pago seleccionado no es valido.',
            'cuotas.required_if' => 'Selecciona la cantidad de cuotas.',
            'cuotas.integer' => 'Las cuotas deben ser un numero entero.',
            'cuotas.min' => 'Debe existir al menos una cuota.',
            'cuotas.max' => 'No puedes superar 12 cuotas.',
        ]);

        $cart = $this->cart($request);

        if ($cart === []) {
            throw ValidationException::withMessages([
                'carrito' => 'Agrega al menos un producto al carrito.',
            ]);
        }

        $pagoQr = null;

        DB::transaction(function () use ($validated, $request, $cart, $pagoFacilService, &$pagoQr) {
            $cliente = Cliente::query()
                ->with('usuario')
                ->whereKey($request->user()->cliente?->id_usuario)
                ->lockForUpdate()
                ->firstOrFail();
            $propietario = Propietario::query()->orderBy('id_usuario')->first();

            if (! $propietario) {
                throw ValidationException::withMessages([
                    'carrito' => 'No existe un propietario para registrar la venta.',
                ]);
            }

            $inventarios = Inventario::query()
                ->with('producto')
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $detalles = collect($cart)->map(function (int $cantidad, string $inventarioId) use ($inventarios) {
                $inventario = $inventarios->get((int) $inventarioId);

                if (! $inventario) {
                    throw ValidationException::withMessages([
                        'carrito' => 'Uno de los productos ya no esta disponible.',
                    ]);
                }

                if ($cantidad > $inventario->cantidad_disponible) {
                    throw ValidationException::withMessages([
                        'carrito' => 'No hay stock suficiente para '.$inventario->producto->nombre_comercial.'.',
                    ]);
                }

                $precioUnitario = (float) $inventario->costo_unitario_lote;

                return [
                    'inventario' => $inventario,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => round($cantidad * $precioUnitario, 2),
                ];
            });

            $total = round($detalles->sum('subtotal'), 2);
            $tipoPago = $validated['tipo_pago'];

            if ($tipoPago === 'CREDITO') {
                $creditoDisponible = (float) $cliente->linea_credito - (float) $cliente->saldo_actual;

                if ($total > $creditoDisponible) {
                    throw ValidationException::withMessages([
                        'tipo_pago' => 'Tu credito disponible es insuficiente para esta compra.',
                    ]);
                }
            }

            $venta = Venta::create([
                'estado_venta' => 'PENDIENTE_PAGO',
                'fecha' => now()->toDateString(),
                'total' => $total,
                'id_cliente' => $cliente->id_usuario,
                'id_propietario' => $propietario->id_usuario,
            ]);

            $detalles->each(function (array $detalle) use ($venta) {
                VentaDetalle::create([
                    'id_venta' => $venta->id,
                    'id_inventario' => $detalle['inventario']->id,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                $detalle['inventario']->decrement('cantidad_disponible', $detalle['cantidad']);
                $this->syncProductStock($detalle['inventario']->id_producto);
            });

            $plan = PlanPago::create([
                'estado_plan' => 'PENDIENTE',
                'tipo_pago' => $tipoPago,
                'id_venta' => $venta->id,
            ]);

            $paymentNumber = PagoFacilService::generarPaymentNumber();
            $montoQr = $tipoPago === 'CONTADO'
                ? $total
                : $this->calcularMontoPrimeraCuota($total, (int) $validated['cuotas']);
            $concepto = $tipoPago === 'CONTADO'
                ? 'Venta SanaMed - '.$detalles->count().' productos'
                : 'Primera cuota venta SanaMed - '.$detalles->count().' productos';
            try {
                $pagoData = $pagoFacilService->generarQr(
                    $montoQr,
                    $concepto,
                    $cliente->usuario->nombre,
                    $cliente->usuario->ci_nit,
                    $cliente->usuario->telefono,
                    $cliente->usuario->email,
                    (string) $cliente->id_usuario,
                    $paymentNumber,
                );
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages([
                    'pago' => $exception->getMessage(),
                ]);
            }

            $cuotaQr = $tipoPago === 'CONTADO'
                ? $this->crearCuotaContado($plan, $total, $pagoData['paymentNumber'])
                : $this->crearCuotasCredito($plan, $total, (int) $validated['cuotas'], $pagoData['paymentNumber']);

            $pagoQr = [
                'qrBase64' => $pagoData['qrBase64'],
                'paymentNumber' => $pagoData['paymentNumber'],
                'transactionId' => $pagoData['transactionId'],
                'monto' => $montoQr,
                'venta_id' => $venta->id,
                'cuota_id' => $cuotaQr->id,
                'nro_cuota' => $cuotaQr->nro_cuota,
            ];

            if ($tipoPago === 'CREDITO') {
                $cliente->increment('saldo_actual', $total);
            }
        });

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('cliente.pagos')->with('pagoQr', $pagoQr);
    }

    /** @return array<string, int> */
    private function cart(Request $request): array
    {
        return collect($request->session()->get(self::SESSION_KEY, []))
            ->mapWithKeys(fn ($cantidad, $inventarioId) => [(string) $inventarioId => (int) $cantidad])
            ->filter(fn (int $cantidad) => $cantidad > 0)
            ->all();
    }

    private function items(Request $request, bool $sync = false): Collection
    {
        $cart = $this->cart($request);

        if ($cart === []) {
            return collect();
        }

        $inventarios = Inventario::query()
            ->with(['producto', 'lote'])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $syncedCart = [];

        $items = collect($cart)->map(function (int $cantidad, string $inventarioId) use ($inventarios, &$syncedCart) {
            $inventario = $inventarios->get((int) $inventarioId);

            if (! $inventario || $inventario->cantidad_disponible <= 0) {
                return null;
            }

            $cantidad = min($cantidad, $inventario->cantidad_disponible);
            $syncedCart[(string) $inventario->id] = $cantidad;
            $precioUnitario = (float) $inventario->costo_unitario_lote;

            return [
                'id' => $inventario->id,
                'cantidad' => $cantidad,
                'cantidad_disponible' => $inventario->cantidad_disponible,
                'precio_unitario' => $inventario->costo_unitario_lote,
                'subtotal' => round($cantidad * $precioUnitario, 2),
                'producto' => [
                    'id' => $inventario->producto->id,
                    'nombre_comercial' => $inventario->producto->nombre_comercial,
                ],
                'lote' => [
                    'id' => $inventario->lote->id,
                    'fecha_vencimiento' => $inventario->lote->fecha_vencimiento,
                ],
            ];
        })->filter()->values();

        if ($sync) {
            $request->session()->put(self::SESSION_KEY, $syncedCart);
        }

        return $items;
    }

    private function crearCuotaContado(PlanPago $plan, float $total, string $paymentNumber): Cuota
    {
        return Cuota::create([
            'id_plan_pago' => $plan->id,
            'nro_cuota' => 1,
            'fecha_vencimiento' => now()->addDay()->toDateString(),
            'monto' => $total,
            'estado_cuota' => 'PENDIENTE',
            'id_transaccion_pago_facil' => $paymentNumber,
        ]);
    }

    private function crearCuotasCredito(PlanPago $plan, float $total, int $cuotas, string $paymentNumber): Cuota
    {
        $montoBase = floor(($total / $cuotas) * 100) / 100;
        $acumulado = 0;
        $primeraCuota = null;

        for ($nroCuota = 1; $nroCuota <= $cuotas; $nroCuota++) {
            $monto = $nroCuota === $cuotas ? round($total - $acumulado, 2) : $montoBase;
            $acumulado += $monto;

            $cuota = Cuota::create([
                'id_plan_pago' => $plan->id,
                'nro_cuota' => $nroCuota,
                'fecha_vencimiento' => now()->addMonths($nroCuota)->toDateString(),
                'monto' => $monto,
                'estado_cuota' => 'PENDIENTE',
                'id_transaccion_pago_facil' => $nroCuota === 1 ? $paymentNumber : null,
            ]);

            $primeraCuota ??= $cuota;
        }

        return $primeraCuota;
    }

    private function calcularMontoPrimeraCuota(float $total, int $cuotas): float
    {
        if ($cuotas <= 1) {
            return round($total, 2);
        }

        return floor(($total / $cuotas) * 100) / 100;
    }

    private function syncProductStock(int $productoId): void
    {
        Producto::whereKey($productoId)->update([
            'stock_actual' => Inventario::where('id_producto', $productoId)->sum('cantidad_disponible'),
        ]);
    }
}
