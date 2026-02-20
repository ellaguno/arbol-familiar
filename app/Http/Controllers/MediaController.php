<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Media;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    /**
     * Muestra galeria general del usuario.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Media::where('created_by', $user->id);

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Busqueda por titulo
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        $media = $query->orderBy('created_at', 'desc')->paginate(24)->withQueryString();

        return view('media.index', compact('media'));
    }

    /**
     * Muestra formulario de subida.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Personas disponibles para asociar media
        $persons = Person::where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->person_id) {
                $q->orWhere('id', $user->person_id);
            }
        })->orderBy('first_name')->get();

        $personId = $request->get('person_id');

        // Eventos de la persona seleccionada para vincular documentos
        $events = collect();
        if ($personId) {
            $events = Event::where('person_id', $personId)->orderBy('date')->get();
        }

        return view('media.create', compact('persons', 'personId', 'events'));
    }

    /**
     * Almacena nuevo archivo media.
     */
    public function store(Request $request)
    {
        // Normalizar URL - agregar https:// si no tiene protocolo
        if ($request->filled('external_url')) {
            $url = trim($request->input('external_url'));
            if (!preg_match('~^https?://~i', $url)) {
                $request->merge(['external_url' => 'https://' . $url]);
            }
        }

        $validated = $request->validate([
            'type' => ['required', 'in:image,document,link'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'person_id' => ['nullable', 'exists:persons,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'file' => ['required_if:type,image,document', 'file', 'max:4096'], // 4MB max
            'external_url' => ['required_if:type,link', 'nullable', 'url'],
            'is_primary' => ['boolean'],
        ], [
            'file.required_if' => 'Selecciona un archivo.',
            'file.max' => 'El archivo no debe superar 4MB.',
            'external_url.required_if' => 'Ingresa una URL.',
            'external_url.url' => 'La URL no es valida. Ejemplo: www.ejemplo.com',
        ]);

        $user = auth()->user();

        // Validar tipo de archivo
        if (in_array($validated['type'], ['image', 'document']) && $request->hasFile('file')) {
            $file = $request->file('file');
            $mimeType = $file->getMimeType();

            if ($validated['type'] === 'image') {
                if (!str_starts_with($mimeType, 'image/')) {
                    return back()->withErrors(['file' => 'El archivo debe ser una imagen.'])->withInput();
                }
                $path = $file->store('media/images', 'public');
            } else {
                $allowedDocTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain',
                ];
                if (!in_array($mimeType, $allowedDocTypes)) {
                    return back()->withErrors(['file' => 'Tipo de documento no permitido.'])->withInput();
                }
                $path = $file->store('media/documents', 'public');
            }

            $media = Media::create([
                'mediable_type' => $validated['person_id'] ? Person::class : null,
                'mediable_id' => $validated['person_id'] ?? null,
                'event_id' => $validated['event_id'] ?? null,
                'type' => $validated['type'],
                'title' => $validated['title'] ?? $file->getClientOriginalName(),
                'description' => $validated['description'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $mimeType,
                'is_primary' => $validated['is_primary'] ?? false,
                'created_by' => $user->id,
            ]);
        } else {
            // Enlace externo
            $media = Media::create([
                'mediable_type' => $validated['person_id'] ? Person::class : null,
                'mediable_id' => $validated['person_id'] ?? null,
                'event_id' => $validated['event_id'] ?? null,
                'type' => 'link',
                'title' => $validated['title'] ?? $validated['external_url'],
                'description' => $validated['description'] ?? null,
                'external_url' => $validated['external_url'],
                'is_primary' => $validated['is_primary'] ?? false,
                'created_by' => $user->id,
            ]);
        }

        // Si es primary, quitar el flag de otros
        if ($media->is_primary && $media->mediable_id) {
            Media::where('mediable_type', $media->mediable_type)
                ->where('mediable_id', $media->mediable_id)
                ->where('id', '!=', $media->id)
                ->update(['is_primary' => false]);
        }

        ActivityLog::log('media_uploaded', $user, null, [
            'media_id' => $media->id,
            'type' => $media->type,
        ]);

        if ($validated['person_id']) {
            return redirect()->route('persons.show', $validated['person_id'])
                ->with('success', 'Archivo subido correctamente.');
        }

        return redirect()->route('media.index')
            ->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Muestra un archivo media.
     */
    public function show(Media $media)
    {
        $this->authorizeView($media);

        return view('media.show', compact('media'));
    }

    /**
     * Muestra formulario de edicion.
     */
    public function edit(Media $media)
    {
        $this->authorizeEdit($media);

        $user = auth()->user();

        $persons = Person::where(function ($q) use ($user) {
            $q->where('created_by', $user->id);
            if ($user->person_id) {
                $q->orWhere('id', $user->person_id);
            }
        })->orderBy('first_name')->get();

        // Eventos de la persona asociada para vincular documentos
        $events = collect();
        if ($media->mediable_id && $media->mediable_type === Person::class) {
            $events = Event::where('person_id', $media->mediable_id)->orderBy('date')->get();
        }

        return view('media.edit', compact('media', 'persons', 'events'));
    }

    /**
     * Actualiza un archivo media.
     */
    public function update(Request $request, Media $media)
    {
        $this->authorizeEdit($media);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'person_id' => ['nullable', 'exists:persons,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'is_primary' => ['boolean'],
        ]);

        $media->update([
            'mediable_type' => $validated['person_id'] ? Person::class : null,
            'mediable_id' => $validated['person_id'] ?? null,
            'event_id' => $validated['event_id'] ?? null,
            'title' => $validated['title'] ?? $media->title,
            'description' => $validated['description'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        if ($media->is_primary && $media->mediable_id) {
            Media::where('mediable_type', $media->mediable_type)
                ->where('mediable_id', $media->mediable_id)
                ->where('id', '!=', $media->id)
                ->update(['is_primary' => false]);
        }

        return redirect()->route('media.show', $media)
            ->with('success', 'Archivo actualizado.');
    }

    /**
     * Elimina un archivo media.
     */
    public function destroy(Media $media)
    {
        $this->authorizeEdit($media);

        $user = auth()->user();

        // Eliminar archivo fisico
        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        ActivityLog::log('media_deleted', $user, null, [
            'media_id' => $media->id,
            'type' => $media->type,
        ]);

        $personId = $media->mediable_id;
        $media->delete();

        if ($personId) {
            return redirect()->route('persons.show', $personId)
                ->with('success', 'Archivo eliminado.');
        }

        return redirect()->route('media.index')
            ->with('success', 'Archivo eliminado.');
    }

    /**
     * Descarga un archivo.
     */
    public function download(Media $media)
    {
        $this->authorizeView($media);

        if (!$media->file_path) {
            abort(404);
        }

        return Storage::disk('public')->download($media->file_path, $media->file_name);
    }

    /**
     * Marca/desmarca como principal.
     */
    public function togglePrimary(Media $media)
    {
        $this->authorizeEdit($media);

        if ($media->is_primary) {
            $media->update(['is_primary' => false]);
        } else {
            // Quitar primary de otros
            if ($media->mediable_id) {
                Media::where('mediable_type', $media->mediable_type)
                    ->where('mediable_id', $media->mediable_id)
                    ->update(['is_primary' => false]);
            }
            $media->update(['is_primary' => true]);
        }

        return back()->with('success', 'Foto principal actualizada.');
    }

    /**
     * Galeria de una persona.
     */
    public function personGallery(Person $person)
    {
        $this->authorizeViewPerson($person);

        $media = $person->media()->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('media.person-gallery', compact('person', 'media'));
    }

    /**
     * Verifica permiso de visualizacion.
     */
    protected function authorizeView(Media $media): void
    {
        $user = auth()->user();

        // El creador del media siempre puede verlo
        if ($media->created_by === $user->id) {
            return;
        }

        // Si el media esta asociado a una persona, usar la logica de privacidad de la persona
        if ($media->mediable_type === Person::class && $media->mediable_id) {
            $person = Person::find($media->mediable_id);
            if ($person && $person->canBeViewedBy($user)) {
                return;
            }
        }

        abort(403, 'No tienes permiso para ver este archivo.');
    }

    /**
     * Verifica permiso de edicion.
     */
    protected function authorizeEdit(Media $media): void
    {
        $user = auth()->user();

        if ($media->created_by !== $user->id) {
            abort(403, 'No tienes permiso para editar este archivo.');
        }
    }

    /**
     * Verifica permiso para ver galeria de persona.
     */
    protected function authorizeViewPerson(Person $person): void
    {
        $user = auth()->user();

        if (!$person->canBeViewedBy($user)) {
            abort(403, 'No tienes permiso para ver esta galeria.');
        }
    }
}
