<?php

namespace Plugin\ReportsDescendants\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class DescendantReportController extends Controller
{
    /**
     * Mostrar reporte de descendientes en HTML.
     */
    public function show(Request $request, Person $person)
    {
        if (!$person->canBeViewedBy(auth()->user())) {
            abort(403, __('No tienes permiso para ver esta persona.'));
        }

        $generations = $request->get('generations', 10);
        $plugin = app(\App\Plugins\PluginManager::class)->getLoaded()['reports-descendants'] ?? null;

        if (!$plugin) {
            abort(404);
        }

        return $plugin->generate($person, 'html', ['generations' => $generations]);
    }

    /**
     * Descargar reporte de descendientes en PDF.
     */
    public function pdf(Request $request, Person $person)
    {
        if (!$person->canBeViewedBy(auth()->user())) {
            abort(403, __('No tienes permiso para ver esta persona.'));
        }

        $generations = $request->get('generations', 10);
        $plugin = app(\App\Plugins\PluginManager::class)->getLoaded()['reports-descendants'] ?? null;

        if (!$plugin) {
            abort(404);
        }

        return $plugin->generate($person, 'pdf', ['generations' => $generations]);
    }
}
