<?php

namespace Plugin\ResearchAssistant;

use App\Plugins\PluginServiceProvider;

class ResearchAssistantPlugin extends PluginServiceProvider
{
    /**
     * Get default settings for this plugin.
     */
    public function getDefaultSettings(): array
    {
        return [
            'ai_provider' => 'openrouter',
            'ai_model' => 'anthropic/claude-3.5-sonnet',
            'openrouter_api_key' => '',
            'deepseek_api_key' => '',
            'openai_api_key' => '',
            'anthropic_api_key' => '',
            'familysearch_enabled' => true,
            'wikipedia_enabled' => true,
            'max_results_per_source' => 10,
        ];
    }

    /**
     * Define hooks for this plugin.
     * Returns array of hook_name => view_path
     */
    public function hooks(): array
    {
        return [
            'person.show.actions' => 'research-assistant::hooks.person-research-button',
        ];
    }
}
