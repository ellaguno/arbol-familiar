<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Media;
use App\Models\Person;
use App\Models\User;
use App\Plugins\Support\TreeTraversal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /** Ruta /storage/{path}: respeta la privacidad del dueño del archivo. */
    public function test_storage_route_enforces_privacy(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        // Persona privada (direct_family) creada por owner; el stranger NO es familia.
        $rel = 'photos/profiles/tmp_verify_photo.jpg';
        $priv = Person::factory()->create([
            'first_name' => 'Privada', 'patronymic' => 'Test',
            'created_by' => $owner->id, 'privacy_level' => 'direct_family',
            'photo_path' => $rel,
        ]);

        // Archivo real en storage/app/public
        $abs = storage_path('app/public/' . $rel);
        File::ensureDirectoryExists(dirname($abs));
        File::put($abs, 'jpegdata');

        // Archivo de contenido de sitio (no ligado a persona/media)
        $siteRel = 'content/tmp_verify_banner.jpg';
        $siteAbs = storage_path('app/public/' . $siteRel);
        File::ensureDirectoryExists(dirname($siteAbs));
        File::put($siteAbs, 'jpegdata');

        try {
            // Stranger NO puede ver la foto de la persona privada. El Handler
            // convierte el abort(403) en un redirect (302) con flash de error
            // para peticiones web: lo importante es que NO recibe el archivo.
            $denied = $this->actingAs($stranger)->get('/storage/' . $rel);
            $denied->assertStatus(302)->assertSessionHas('error');
            $this->assertNotEquals(200, $denied->getStatusCode());
            // Owner (creador) SÍ puede
            $this->actingAs($owner)->get('/storage/' . $rel)->assertStatus(200);
            // Contenido de sitio (no ligado a persona): accesible a autenticados
            $this->actingAs($stranger)->get('/storage/' . $siteRel)->assertStatus(200);
        } finally {
            File::delete([$abs, $siteAbs]);
        }
    }

    /** TreeTraversal termina ante datos ciclicos (no recursion infinita). */
    public function test_tree_traversal_handles_cycles(): void
    {
        $u = User::factory()->create();
        $a = Person::factory()->create(['first_name' => 'A', 'patronymic' => 'X', 'gender' => 'M', 'created_by' => $u->id, 'privacy_level' => 'community']);
        $b = Person::factory()->create(['first_name' => 'B', 'patronymic' => 'X', 'gender' => 'M', 'created_by' => $u->id, 'privacy_level' => 'community']);

        // Ciclo: B es padre de A, y A es padre de B.
        $f1 = Family::create(['husband_id' => $b->id, 'created_by' => $u->id]);
        FamilyChild::create(['family_id' => $f1->id, 'person_id' => $a->id]);
        $f2 = Family::create(['husband_id' => $a->id, 'created_by' => $u->id]);
        FamilyChild::create(['family_id' => $f2->id, 'person_id' => $b->id]);

        $tt = new TreeTraversal();
        // Con 20 generaciones y ciclo, sin guardia esto explotaria; debe terminar.
        $ancestors = $tt->getAncestors($a, 20);
        $this->assertNotEmpty($ancestors, 'Debe devolver al menos el primer ancestro');

        // La coleccion plana tampoco debe crecer sin control.
        $flat = $tt->ancestors($a, 20);
        $this->assertLessThan(10, $flat->count(), 'La guardia de ciclos limita el numero de nodos');
    }
}
