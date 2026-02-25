<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rellenar birth_year/month/day y death_year/month/day desde birth_date/death_date
     * para personas importadas via GEDCOM que solo tenian la fecha compuesta.
     */
    public function up(): void
    {
        $this->backfillComponents('birth');
        $this->backfillComponents('death');
    }

    protected function backfillComponents(string $prefix): void
    {
        $persons = DB::table('persons')
            ->whereNotNull("{$prefix}_date")
            ->whereNull("{$prefix}_year")
            ->select(['id', "{$prefix}_date"])
            ->get();

        foreach ($persons as $person) {
            $date = $person->{"{$prefix}_date"};
            if (!$date || !preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $parts)) {
                continue;
            }

            $year = (int) $parts[1];
            $month = (int) $parts[2];
            $day = (int) $parts[3];

            $update = ["{$prefix}_year" => $year];

            // El parser GEDCOM usa 01 como placeholder cuando falta mes o dia.
            if ($month === 1 && $day === 1) {
                // Solo año conocido
                $update["{$prefix}_date_approx"] = true;
            } elseif ($day === 1) {
                // Mes y año conocidos, dia desconocido
                $update["{$prefix}_month"] = $month;
                $update["{$prefix}_date_approx"] = true;
            } else {
                // Fecha completa
                $update["{$prefix}_month"] = $month;
                $update["{$prefix}_day"] = $day;
            }

            DB::table('persons')->where('id', $person->id)->update($update);
        }
    }

    public function down(): void
    {
        // Los datos eran correctos, solo faltaban componentes
    }
};
