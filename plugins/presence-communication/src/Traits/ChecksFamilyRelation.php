<?php

namespace Plugin\PresenceCommunication\Traits;

trait ChecksFamilyRelation
{
    /**
     * Determina si la otra persona es "familia" del usuario actual.
     * Familia = mismo creador, familia directa, o familia extendida.
     */
    protected function isFamilyOf($currentPerson, $otherPerson): bool
    {
        if (!$currentPerson || !$otherPerson) {
            return false;
        }

        if ($currentPerson->created_by === $otherPerson->created_by) {
            return true;
        }

        if (in_array($otherPerson->id, $currentPerson->directFamilyIds)) {
            return true;
        }

        if (in_array($otherPerson->id, $currentPerson->extendedFamilyIds)) {
            return true;
        }

        return false;
    }
}
