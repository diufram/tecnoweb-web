<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/proveedores/Index', [
            'proveedores' => Proveedor::query()
                ->with('usuario:id,ci_nit,nombre,email,direccion,telefono')
                ->latest()
                ->get()
                ->map(fn (Proveedor $proveedor) => [
                    'id_usuario' => $proveedor->id_usuario,
                    'empresa' => $proveedor->empresa,
                    'usuario' => $proveedor->usuario,
                    'updated_at' => $proveedor->updated_at,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/proveedores/Form', [
            'mode' => 'create',
            'proveedor' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProveedor($request);

        DB::transaction(function () use ($validated) {
            $usuario = Usuario::create([
                'ci_nit' => $validated['ci_nit'],
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'contrasena' => $validated['password'],
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'],
                'email_verified_at' => now(),
            ]);

            Proveedor::create([
                'id_usuario' => $usuario->id,
                'empresa' => $validated['empresa'],
            ]);
        });

        return redirect()->route('propietario.proveedores.index');
    }

    public function edit(Proveedor $proveedor): Response
    {
        $proveedor->load('usuario:id,ci_nit,nombre,email,direccion,telefono');

        return Inertia::render('propietario/proveedores/Form', [
            'mode' => 'edit',
            'proveedor' => [
                'id_usuario' => $proveedor->id_usuario,
                'empresa' => $proveedor->empresa,
                'usuario' => $proveedor->usuario,
            ],
        ]);
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $this->validateProveedor($request, $proveedor->usuario);

        DB::transaction(function () use ($validated, $proveedor) {
            $usuarioData = [
                'ci_nit' => $validated['ci_nit'],
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'],
            ];

            if (! empty($validated['password'])) {
                $usuarioData['contrasena'] = $validated['password'];
            }

            $proveedor->usuario->update($usuarioData);
            $proveedor->update(['empresa' => $validated['empresa']]);
        });

        return redirect()->route('propietario.proveedores.index');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        DB::transaction(function () use ($proveedor) {
            $proveedor->delete();
            $proveedor->usuario->delete();
        });

        return redirect()->route('propietario.proveedores.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProveedor(Request $request, ?Usuario $usuario = null): array
    {
        $isCreating = $usuario === null;

        return $request->validate([
            'ci_nit' => ['required', 'string', 'max:50', Rule::unique('usuario', 'ci_nit')->ignore($usuario?->id)],
            'nombre' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuario', 'email')->ignore($usuario?->id)],
            'direccion' => ['required', 'string', 'min:3', 'max:255'],
            'telefono' => ['required', 'string', 'min:5', 'max:50'],
            'password' => [$isCreating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'empresa' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'ci_nit.required' => 'El CI/NIT es obligatorio.',
            'ci_nit.unique' => 'Ya existe un usuario con ese CI/NIT.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'email.required' => 'El correo electronico es obligatorio.',
            'email.email' => 'Ingresa un correo electronico valido.',
            'email.unique' => 'Ya existe un usuario con ese correo electronico.',
            'direccion.required' => 'La direccion es obligatoria.',
            'direccion.min' => 'La direccion debe tener al menos 3 caracteres.',
            'telefono.required' => 'El telefono es obligatorio.',
            'telefono.min' => 'El telefono debe tener al menos 5 caracteres.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmacion de contrasena no coincide.',
            'empresa.required' => 'La empresa es obligatoria.',
            'empresa.min' => 'La empresa debe tener al menos 3 caracteres.',
        ]);
    }
}
