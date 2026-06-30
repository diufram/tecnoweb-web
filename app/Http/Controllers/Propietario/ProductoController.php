<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/productos/Index', [
            'productos' => Producto::query()
                ->select(['id', 'nombre_comercial', 'stock_actual', 'created_at', 'updated_at'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/productos/Form', [
            'mode' => 'create',
            'producto' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProducto($request);

        Producto::create($validated);

        return redirect()->route('propietario.productos.index');
    }

    public function edit(Producto $producto): Response
    {
        return Inertia::render('propietario/productos/Form', [
            'mode' => 'edit',
            'producto' => $producto->only(['id', 'nombre_comercial', 'stock_actual']),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $this->validateProducto($request, $producto);

        $producto->update($validated);

        return redirect()->route('propietario.productos.index');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('propietario.productos.index');
    }

    /**
     * @return array{nombre_comercial: string, stock_actual: int}
     */
    private function validateProducto(Request $request, ?Producto $producto = null): array
    {
        $validated = $request->validate([
            'nombre_comercial' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('producto', 'nombre_comercial')
                    ->ignore($producto?->id)
                    ->whereNull('deleted_at'),
            ],
            'stock_actual' => ['required', 'integer', 'min:0', 'max:1000000'],
        ], [
            'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
            'nombre_comercial.min' => 'El nombre comercial debe tener al menos 3 caracteres.',
            'nombre_comercial.max' => 'El nombre comercial no debe superar 255 caracteres.',
            'nombre_comercial.unique' => 'Ya existe un producto con ese nombre comercial.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_actual.integer' => 'El stock actual debe ser un numero entero.',
            'stock_actual.min' => 'El stock actual no puede ser negativo.',
            'stock_actual.max' => 'El stock actual no puede superar 1.000.000 unidades.',
        ]);

        $validated['nombre_comercial'] = trim($validated['nombre_comercial']);
        $validated['stock_actual'] = (int) $validated['stock_actual'];

        return $validated;
    }
}
