<?php

namespace Drupal\speculative_loading\Hook;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
/**
 * Hook implementations for speculative_loading.
 */
class SpeculativeLoadingHooks
{
    use StringTranslationTrait;
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
        switch ($route_name) {
            case 'help.page.speculative_loading':
                return '<p>' . $this->t('This module adds support for the Speculation Rules API, which allows defining rules by which certain URLs are dynamically prefetched or prerendered.') . '</p>';
        }
    }
    /**
     * Implements hook_page_attachments().
     */
    #[Hook('page_attachments')]
    public function pageAttachments(array &$attachments)
    {
        // Get speculation rules.
        /** @var \Drupal\speculative_loading\Plugin\SpeculationRulesManagerInterface $manager */
        $manager = \Drupal::service('plugin.manager.speculation_rules');
        $rules = $manager->getSpeculationRules();
        // Add the speculation rules as inline JavaScript.
        $attachments['#attached']['html_head'][] = [
            [
                '#type' => 'html_tag',
                '#tag' => 'script',
                '#value' => json_encode($rules),
                '#attributes' => [
                    'type' => 'speculationrules',
                ],
            ],
            'speculation-rules',
        ];
    }
}
