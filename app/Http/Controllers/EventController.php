<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Media;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function store(Request $request, Person $person)
    {
        $this->authorizeEdit($person);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50', Rule::in(array_keys(Event::manualTypes()))],
            'date' => ['nullable', 'date'],
            'date_approx' => ['boolean'],
            'place' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['date_approx'] = $request->boolean('date_approx');

        $event = $person->events()->create($validated);

        ActivityLog::log('event_created', auth()->user(), $person, [
            'event_id' => $event->id,
            'type' => $event->type,
        ]);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Evento agregado correctamente.'));
    }

    public function update(Request $request, Person $person, Event $event)
    {
        $this->authorizeEdit($person);
        $this->ensureEventBelongsToPerson($event, $person);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50', Rule::in(array_keys(Event::manualTypes()))],
            'date' => ['nullable', 'date'],
            'date_approx' => ['boolean'],
            'place' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['date_approx'] = $request->boolean('date_approx');

        $event->update($validated);

        return redirect()->route('persons.show', $person)
            ->with('success', __('Evento actualizado correctamente.'));
    }

    public function destroy(Person $person, Event $event)
    {
        $this->authorizeEdit($person);
        $this->ensureEventBelongsToPerson($event, $person);

        // Desvincular documentos asociados sin eliminarlos
        Media::where('event_id', $event->id)->update(['event_id' => null]);

        $event->delete();

        return redirect()->route('persons.show', $person)
            ->with('success', __('Evento eliminado correctamente.'));
    }

    protected function authorizeEdit(Person $person): void
    {
        $user = auth()->user();
        if (!$person->canBeEditedBy($user->id) && !$user->is_admin) {
            abort(403, __('No tienes permiso para editar esta persona.'));
        }
    }

    protected function ensureEventBelongsToPerson(Event $event, Person $person): void
    {
        if ($event->person_id !== $person->id) {
            abort(404);
        }
    }
}
