<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InventarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/inventario/Index', [
            'inventarios' => Inventario::query()
                ->with(['producto:id,nombre_comercial', 'lote:id,codigo_lote,fecha_ingreso,fecha_vencimiento'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/inventario/Form', [
            'mode' => 'create',
            'inventario' => null,
            'productos' => $this->productosOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInventario($request);

        DB::transaction(function () use ($validated) {
            $lote = Lote::create([
                'codigo_lote' => $validated['codigo_lote'],
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);

            Inventario::create([
                'id_producto' => $validated['id_producto'],
                'id_lote' => $lote->id,
                'cantidad_disponible' => $validated['cantidad_disponible'],
                'costo_unitario_lote' => $validated['costo_unitario_lote'],
            ]);

            $this->syncProductStock($validated['id_producto']);
        });

        return redirect()->route('propietario.inventario.index');
    }

    public function edit(Inventario $inventario): Response
    {
        $inventario->load(['producto:id,nombre_comercial', 'lote:id,codigo_lote,fecha_ingreso,fecha_vencimiento']);

        return Inertia::render('propietario/inventario/Form', [
            'mode' => 'edit',
            'inventario' => [
                'id' => $inventario->id,
                'id_producto' => $inventario->id_producto,
                'cantidad_disponible' => $inventario->cantidad_disponible,
                'costo_unitario_lote' => $inventario->costo_unitario_lote,
                'codigo_lote' => $inventario->lote->codigo_lote,
                'fecha_ingreso' => $inventario->lote->fecha_ingreso?->toDateString(),
                'fecha_vencimiento' => $inventario->lote->fecha_vencimiento?->toDateString(),
            ],
            'productos' => $this->productosOptions(),
        ]);
    }

    public function update(Request $request, Inventario $inventario): RedirectResponse
    {
        $validated = $this->validateInventario($request, $inventario);

        DB::transaction(function () use ($validated, $inventario) {
            $previousProductId = $inventario->id_producto;

            $inventario->lote->update([
                'codigo_lote' => $validated['codigo_lote'],
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);

            $inventario->update([
                'id_producto' => $validated['id_producto'],
                'cantidad_disponible' => $validated['cantidad_disponible'],
                'costo_unitario_lote' => $validated['costo_unitario_lote'],
            ]);

            $this->syncProductStock($previousProductId);
            $this->syncProductStock($validated['id_producto']);
        });

        return redirect()->route('propietario.inventario.index');
    }

    /** @return array<string, mixed> */
    private function validateInventario(Request $request, ?Inventario $inventario = null): array
    {
        return $request->validate([
            'id_producto' => ['required', 'integer', Rule::exists('producto', 'id')->whereNull('deleted_at')],
            'codigo_lote' => ['required', 'string', 'min:3', 'max:255', Rule::unique('lote', 'codigo_lote')->ignore($inventario?->id_lote)->whereNull('deleted_at')],
            'fecha_ingreso' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_ingreso'],
            'cantidad_disponible' => ['required', 'integer', 'min:0', 'max:1000000'],
            'costo_unitario_lote' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ], [
            'id_producto.required' => 'Selecciona un producto.',
            'id_producto.exists' => 'El producto seleccionado no existe.',
            'codigo_lote.required' => 'El codigo de lote es obligatorio.',
            'codigo_lote.min' => 'El codigo de lote debe tener al menos 3 caracteres.',
            'codigo_lote.unique' => 'Ya existe un lote con ese codigo.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso no es valida.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de ingreso.',
            'cantidad_disponible.required' => 'La cantidad disponible es obligatoria.',
            'cantidad_disponible.integer' => 'La cantidad disponible debe ser un numero entero.',
            'cantidad_disponible.min' => 'La cantidad disponible no puede ser negativa.',
            'costo_unitario_lote.required' => 'El costo unitario es obligatorio.',
            'costo_unitario_lote.numeric' => 'El costo unitario debe ser numerico.',
            'costo_unitario_lote.min' => 'El costo unitario no puede ser negativo.',
        ]);
    }

    private function productosOptions(): mixed
    {
        return Producto::query()
            ->orderBy('nombre_comercial')
            ->get(['id', 'nombre_comercial']);
    }

    private function syncProductStock(int $productoId): void
    {
        Producto::whereKey($productoId)->update([
            'stock_actual' => Inventario::where('id_producto', $productoId)->sum('cantidad_disponible'),
        ]);
    }
}
