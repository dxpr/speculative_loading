<?php

namespace Drupal\speculative_loading\Plugin\SpeculationRules;

use Drupal\Core\Plugin\PluginBase;
use Drupal\speculative_loading\Plugin\SpeculationRulesInterface;

/**
 * Default implementation of the SpeculationRules plugin.
 *
 * @SpeculationRules(
 *   id = "default",
 *   title = @Translation("Default Speculation Rules"),
 *   description = @Translation("Default implementation for speculation rules.")
 * )
 */
class DefaultSpeculationRules extends PluginBase implements SpeculationRulesInterface {

  /**
   * {@inheritdoc}
   */
  public function getRules(string $mode): array {
    // The default plugin doesn't add any rules beyond those in the manager.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExcludePaths(string $mode): array {
    // List of default paths to exclude from speculation loading.
    $exclude_paths = [];

    // Add specific excluded paths based on mode.
    if ($mode === 'prerender') {
      // More sensitive paths to exclude from prerendering.
      $exclude_paths[] = '/node/add/*';
      $exclude_paths[] = '/node/*/edit';
      $exclude_paths[] = '/node/*/delete';
      $exclude_paths[] = '/admin/*';
      $exclude_paths[] = '/user/*/edit';
    }

    // Paths to exclude from both prefetch and prerender.
    $exclude_paths = array_merge($exclude_paths, [
      '/user/reset/*',
      '/confirm/*',
    ]);

    return $exclude_paths;
  }

} 