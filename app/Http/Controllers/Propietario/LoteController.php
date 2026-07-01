<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LoteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('propietario/lotes/Index', [
            'lotes' => Lote::query()
                ->select(['id', 'codigo_lote', 'fecha_ingreso', 'fecha_vencimiento', 'created_at', 'updated_at'])
                ->withCount('inventarios')
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propietario/lotes/Form', [
            'mode' => 'create',
            'lote' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Lote::create($this->validateLote($request));

        return redirect()->route('propietario.lotes.index');
    }

    public function edit(Lote $lote): Response
    {
        return Inertia::render('propietario/lotes/Form', [
            'mode' => 'edit',
            'lote' => [
                'id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
                'fecha_ingreso' => $lote->fecha_ingreso?->toDateString(),
                'fecha_vencimiento' => $lote->fecha_vencimiento?->toDateString(),
            ],
        ]);
    }

    public function update(Request $request, Lote $lote): RedirectResponse
    {
        $lote->update($this->validateLote($request, $lote));

        return redirect()->route('propietario.lotes.index');
    }

    /** @return array{codigo_lote: string, fecha_ingreso: string, fecha_vencimiento: string} */
    private function validateLote(Request $request, ?Lote $lote = null): array
    {
        return $request->validate([
            'codigo_lote' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('lote', 'codigo_lote')
                    ->ignore($lote?->id)
                    ->whereNull('deleted_at'),
            ],
            'fecha_ingreso' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_ingreso'],
        ], [
            'codigo_lote.required' => 'El codigo de lote es obligatorio.',
            'codigo_lote.min' => 'El codigo de lote debe tener al menos 3 caracteres.',
            'codigo_lote.unique' => 'Ya existe un lote con ese codigo.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso no es valida.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de ingreso.',
        ]);
    }
}
