<?php

namespace Drupal\speculative_loading\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for Speculation Rules plugins.
 */
interface SpeculationRulesInterface extends PluginInspectionInterface {

  /**
   * Gets the plugin's rules for a specific mode.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return array
   *   Array of rules specific to this plugin.
   */
  public function getRules(string $mode): array;

  /**
   * Gets paths that should be excluded from speculation rules.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return array
   *   Array of paths to exclude.
   */
  public function getExcludePaths(string $mode): array;

}
