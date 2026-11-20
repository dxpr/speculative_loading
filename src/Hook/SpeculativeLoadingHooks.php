<?php

namespace Drupal\speculative_loading\Hook;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\speculative_loading\Plugin\SpeculationRulesManagerInterface;

/**
 * Implements Speculative Loading hooks.
 */
final class SpeculativeLoadingHooks {

  use StringTranslationTrait;

  /**
   * Constructs the hook service.
   */
  public function __construct(
    private SpeculationRulesManagerInterface $rulesManager,
    private ConfigFactoryInterface $configFactory,
    private AdminContext $adminContext,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    if ($route_name === 'help.page.speculative_loading') {
      return '<p>' . $this->t('This module adds support for the Speculation Rules API, which allows defining rules by which certain URLs are dynamically prefetched or prerendered.') . '</p>';
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$attachments): void {
    // Tag every response so toggling the feature invalidates cached pages.
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheTags(['config:speculative_loading.settings']);
    $cacheability->applyTo($attachments);

    // Older releases had no enabled key and were always active.
    $enabled = $this->configFactory->get('speculative_loading.settings')->get('enabled') ?? TRUE;
    if (!$enabled || $this->adminContext->isAdminRoute()) {
      return;
    }

    $rules = $this->rulesManager->getSpeculationRules();
    if (empty($rules)) {
      return;
    }

    $attachments['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => json_encode($rules, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        '#attributes' => [
          'type' => 'speculationrules',
        ],
      ],
      'speculation-rules',
    ];
  }

}
