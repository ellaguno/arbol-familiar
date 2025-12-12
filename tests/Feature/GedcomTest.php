<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Person;
use App\Models\User;
use App\Services\GedcomExporter;
use App\Services\GedcomParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GedcomTest extends TestCase
{
    use RefreshDatabase;

    private string $sampleGedcom = <<<GEDCOM
0 HEAD
1 SOUR TEST
1 GEDC
2 VERS 5.5.1
2 FORM LINEAGE-LINKED
1 CHAR UTF-8
0 @I1@ INDI
1 NAME Juan /Garcia/
1 SEX M
1 BIRT
2 DATE 15 MAR 1950
2 PLAC Ciudad de Mexico
1 DEAT
2 DATE 20 DEC 2020
1 FAMS @F1@
0 @I2@ INDI
1 NAME Maria /Lopez/
1 SEX F
1 BIRT
2 DATE 8 JUL 1955
2 PLAC Monterrey
1 FAMS @F1@
0 @I3@ INDI
1 NAME Pedro /Garcia/
1 SEX M
1 BIRT
2 DATE 3 NOV 1980
2 PLAC Ciudad de Mexico
1 FAMC @F1@
0 @F1@ FAM
1 HUSB @I1@
1 WIFE @I2@
1 CHIL @I3@
1 MARR
2 DATE 12 JUN 1975
2 PLAC Ciudad de Mexico
0 TRLR
GEDCOM;

    private function createAuthenticatedUser(): User
    {
        $user = User::factory()->create();
        $person = Person::factory()->create([
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
        $user->update(['person_id' => $person->id]);
        return $user->fresh();
    }

    // --- Parser Unit Tests ---

    public function test_parser_parses_individuals(): void
    {
        $parser = new GedcomParser();
        $result = $parser->parse($this->sampleGedcom);

        $this->assertCount(3, $result['individuals']);
        $this->assertArrayHasKey('I1', $result['individuals']);
        $this->assertEquals('Juan /Garcia/', $result['individuals']['I1']['name']);
        $this->assertEquals('M', $result['individuals']['I1']['sex']);
    }

    public function test_parser_parses_families(): void
    {
        $parser = new GedcomParser();
        $result = $parser->parse($this->sampleGedcom);

        $this->assertCount(1, $result['families']);
        $this->assertArrayHasKey('F1', $result['families']);
        $this->assertEquals('I1', $result['families']['F1']['husb']);
        $this->assertEquals('I2', $result['families']['F1']['wife']);
        $this->assertContains('I3', $result['families']['F1']['children']);
    }

    public function test_parser_parses_dates(): void
    {
        $parser = new GedcomParser();
        $result = $parser->parse($this->sampleGedcom);

        $this->assertEquals('15 MAR 1950', $result['individuals']['I1']['birth']['date']);
        $this->assertEquals('Ciudad de Mexico', $result['individuals']['I1']['birth']['place']);
    }

    public function test_parser_preview(): void
    {
        $parser = new GedcomParser();
        $preview = $parser->getPreview($this->sampleGedcom);

        $this->assertEquals(3, $preview['total_individuals']);
        $this->assertEquals(1, $preview['total_families']);
        $this->assertCount(3, $preview['individuals']);
        $this->assertCount(1, $preview['families']);
    }

    public function test_parser_import_creates_records(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->actingAs($user);

        $parser = new GedcomParser();
        $result = $parser->import($this->sampleGedcom, [
            'privacy_level' => 'family',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['stats']['persons_created']);
        $this->assertEquals(1, $result['stats']['families_created']);

        // Verify database records
        $this->assertDatabaseHas('persons', ['first_name' => 'Juan', 'patronymic' => 'Garcia']);
        $this->assertDatabaseHas('persons', ['first_name' => 'Maria', 'patronymic' => 'Lopez']);
        $this->assertDatabaseHas('persons', ['first_name' => 'Pedro', 'patronymic' => 'Garcia']);
    }

    public function test_parser_handles_empty_content(): void
    {
        $parser = new GedcomParser();
        $result = $parser->parse('');

        $this->assertEmpty($result['individuals']);
        $this->assertEmpty($result['families']);
    }

    public function test_parser_detects_invalid_children(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
1 GEDC
2 VERS 5.5.1
0 @I1@ INDI
1 NAME Test /Person/
1 SEX M
0 @F1@ FAM
1 HUSB @I1@
1 CHIL @I1@
0 TRLR
GEDCOM;

        $parser = new GedcomParser();
        $result = $parser->parse($gedcom);

        // Child should be removed because it's the same as father
        $this->assertEmpty($result['families']['F1']['children']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_parser_handles_approximate_dates(): void
    {
        $gedcom = <<<GEDCOM
0 HEAD
1 GEDC
2 VERS 5.5.1
0 @I1@ INDI
1 NAME Test /Approx/
1 SEX M
1 BIRT
2 DATE ABT 1950
0 TRLR
GEDCOM;

        $parser = new GedcomParser();
        $result = $parser->parse($gedcom);

        $this->assertEquals('ABT 1950', $result['individuals']['I1']['birth']['date']);
    }

    // --- Exporter Tests ---

    public function test_exporter_generates_valid_gedcom(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->actingAs($user);

        $person = Person::factory()->create([
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'gender' => 'M',
            'birth_date' => '1985-05-15',
            'birth_place' => 'Zagreb',
            'is_living' => true,
            'created_by' => $user->id,
        ]);

        $exporter = new GedcomExporter();
        $content = $exporter->export(['include_living' => true]);

        $this->assertStringContainsString('0 HEAD', $content);
        $this->assertStringContainsString('GEDC', $content);
        $this->assertStringContainsString('Ivan /Horvat/', $content);
        $this->assertStringContainsString('SEX M', $content);
        $this->assertStringContainsString('0 TRLR', $content);
    }

    public function test_exporter_excludes_living_when_option_set(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->actingAs($user);

        Person::factory()->create([
            'first_name' => 'Living',
            'patronymic' => 'Person',
            'is_living' => true,
            'created_by' => $user->id,
        ]);

        Person::factory()->create([
            'first_name' => 'Deceased',
            'patronymic' => 'Person',
            'is_living' => false,
            'death_date' => '2020-01-01',
            'created_by' => $user->id,
        ]);

        $exporter = new GedcomExporter();
        $content = $exporter->export(['include_living' => false]);

        $this->assertStringNotContainsString('Living /Person/', $content);
        $this->assertStringContainsString('Deceased /Person/', $content);
    }

    public function test_exporter_includes_families(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->actingAs($user);

        $husband = Person::factory()->create([
            'first_name' => 'Juan',
            'patronymic' => 'Garcia',
            'gender' => 'M',
            'created_by' => $user->id,
        ]);

        $wife = Person::factory()->create([
            'first_name' => 'Maria',
            'patronymic' => 'Lopez',
            'gender' => 'F',
            'created_by' => $user->id,
        ]);

        Family::factory()->create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'marriage_date' => '1980-06-15',
            'marriage_place' => 'Mexico City',
            'created_by' => $user->id,
        ]);

        $exporter = new GedcomExporter();
        $content = $exporter->export();

        $this->assertStringContainsString('HUSB @I' . $husband->id . '@', $content);
        $this->assertStringContainsString('WIFE @I' . $wife->id . '@', $content);
        $this->assertStringContainsString('MARR', $content);
    }

    public function test_exporter_stats(): void
    {
        $user = $this->createAuthenticatedUser();
        $this->actingAs($user);

        Person::factory()->count(3)->create(['created_by' => $user->id]);

        $exporter = new GedcomExporter();
        $exporter->export();

        $stats = $exporter->getStats();
        // 3 created + 1 from createAuthenticatedUser
        $this->assertGreaterThanOrEqual(3, $stats['persons']);
    }

    // --- Controller Feature Tests ---

    public function test_import_page_requires_auth(): void
    {
        $response = $this->get('/gedcom/import');
        $response->assertRedirect('/login');
    }

    public function test_import_page_accessible(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/gedcom/import');
        $response->assertStatus(200);
    }

    public function test_export_page_accessible(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/gedcom/export');
        $response->assertStatus(200);
    }

    public function test_template_download(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/gedcom/template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('GEDCOM', $response->getContent());
    }

    public function test_preview_validates_file(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->post('/gedcom/preview', []);
        $response->assertSessionHasErrors('file');
    }

    public function test_preview_with_valid_file(): void
    {
        Storage::fake('local');
        $user = $this->createAuthenticatedUser();

        $file = UploadedFile::fake()->createWithContent(
            'test.ged',
            $this->sampleGedcom
        );

        $response = $this->actingAs($user)->post('/gedcom/preview', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('preview');
    }

    public function test_quick_export_downloads_file(): void
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->actingAs($user)->get('/gedcom/quick');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    }

    public function test_export_tree_for_person(): void
    {
        $user = $this->createAuthenticatedUser();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->get('/gedcom/tree/' . $person->id);
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
    }
}
