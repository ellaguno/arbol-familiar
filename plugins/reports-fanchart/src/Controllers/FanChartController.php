<?php

namespace Plugin\ReportsFanchart\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class FanChartController extends Controller
{
    /**
     * Mostrar grafico de abanico en HTML.
     */
    public function show(Request $request, Person $person)
    {
        if (!$person->canBeViewedBy(auth()->user())) {
            abort(403, __('No tienes permiso para ver esta persona.'));
        }

        $generations = $request->get('generations', 5);
        $plugin = app(\App\Plugins\PluginManager::class)->getLoaded()['reports-fanchart'] ?? null;

        if (!$plugin) {
            abort(404);
        }

        return $plugin->generate($person, 'html', ['generations' => $generations]);
    }

    /**
     * Descargar grafico de abanico como SVG.
     */
    public function svg(Request $request, Person $person)
    {
        if (!$person->canBeViewedBy(auth()->user())) {
            abort(403, __('No tienes permiso para ver esta persona.'));
        }

        $generations = $request->get('generations', 5);
        $plugin = app(\App\Plugins\PluginManager::class)->getLoaded()['reports-fanchart'] ?? null;

        if (!$plugin) {
            abort(404);
        }

        return $plugin->generate($person, 'svg', ['generations' => $generations]);
    }

    /**
     * Descargar grafico de abanico como PDF.
     */
    public function pdf(Request $request, Person $person)
    {
        if (!$person->canBeViewedBy(auth()->user())) {
            abort(403, __('No tienes permiso para ver esta persona.'));
        }

        $generations = $request->get('generations', 5);
        $plugin = app(\App\Plugins\PluginManager::class)->getLoaded()['reports-fanchart'] ?? null;

        if (!$plugin) {
            abort(404);
        }

        return $plugin->generate($person, 'pdf', ['generations' => $generations]);
    }
}
