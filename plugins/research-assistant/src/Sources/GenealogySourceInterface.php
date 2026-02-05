<?php

namespace Plugin\ResearchAssistant\Sources;

interface GenealogySourceInterface
{
    /**
     * Get the source name for display.
     */
    public function getName(): string;

    /**
     * Get the source identifier.
     */
    public function getId(): string;

    /**
     * Get the icon name or SVG for this source.
     */
    public function getIcon(): string;

    /**
     * Search the source with the given query and filters.
     *
     * @param string $query The search query
     * @param array $filters Optional filters (given_name, surname, birth_place, etc.)
     * @return array Array of search results
     */
    public function search(string $query, array $filters = []): array;

    /**
     * Check if this source is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Set whether this source is enabled.
     */
    public function setEnabled(bool $enabled): void;
}
