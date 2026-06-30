<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/clientes/Index', [
            'clientes' => Cliente::query()
                ->with('usuario:id,ci_nit,nombre,email,direccion,telefono')
                ->latest()
                ->get()
                ->map(fn (Cliente $cliente) => [
                    'id_usuario' => $cliente->id_usuario,
                    'linea_credito' => $cliente->linea_credito,
                    'nit_facturacion' => $cliente->nit_facturacion,
                    'saldo_actual' => $cliente->saldo_actual,
                    'usuario' => $cliente->usuario,
                    'updated_at' => $cliente->updated_at,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/clientes/Form', [
            'mode' => 'create',
            'cliente' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCliente($request);

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

            Cliente::create([
                'id_usuario' => $usuario->id,
                'linea_credito' => $validated['linea_credito'],
                'nit_facturacion' => $validated['nit_facturacion'],
                'saldo_actual' => $validated['saldo_actual'],
            ]);
        });

        return redirect()->route('propietario.clientes.index');
    }

    public function edit(Cliente $cliente): Response
    {
        $cliente->load('usuario:id,ci_nit,nombre,email,direccion,telefono');

        return Inertia::render('propietario/clientes/Form', [
            'mode' => 'edit',
            'cliente' => [
                'id_usuario' => $cliente->id_usuario,
                'linea_credito' => $cliente->linea_credito,
                'nit_facturacion' => $cliente->nit_facturacion,
                'saldo_actual' => $cliente->saldo_actual,
                'usuario' => $cliente->usuario,
            ],
        ]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $this->validateCliente($request, $cliente->usuario);

        DB::transaction(function () use ($validated, $cliente) {
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

            $cliente->usuario->update($usuarioData);
            $cliente->update([
                'linea_credito' => $validated['linea_credito'],
                'nit_facturacion' => $validated['nit_facturacion'],
                'saldo_actual' => $validated['saldo_actual'],
            ]);
        });

        return redirect()->route('propietario.clientes.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCliente(Request $request, ?Usuario $usuario = null): array
    {
        $isCreating = $usuario === null;

        return $request->validate([
            'ci_nit' => ['required', 'string', 'max:50', Rule::unique('usuario', 'ci_nit')->ignore($usuario?->id)],
            'nombre' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuario', 'email')->ignore($usuario?->id)],
            'direccion' => ['required', 'string', 'min:3', 'max:255'],
            'telefono' => ['required', 'string', 'min:5', 'max:50'],
            'password' => [$isCreating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'linea_credito' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'nit_facturacion' => ['required', 'string', 'max:50'],
            'saldo_actual' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ], $this->messages());
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
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
            'linea_credito.required' => 'La linea de credito es obligatoria.',
            'linea_credito.numeric' => 'La linea de credito debe ser numerica.',
            'linea_credito.min' => 'La linea de credito no puede ser negativa.',
            'nit_facturacion.required' => 'El NIT de facturacion es obligatorio.',
            'saldo_actual.required' => 'El saldo actual es obligatorio.',
            'saldo_actual.numeric' => 'El saldo actual debe ser numerico.',
            'saldo_actual.min' => 'El saldo actual no puede ser negativo.',
        ];
    }
}
