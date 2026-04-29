<?php

namespace Drupal\speculative_loading\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Interface for the SpeculationRules plugin manager.
 */
interface SpeculationRulesManagerInterface extends PluginManagerInterface {

  /**
   * Gets the speculation rules based on current configuration.
   *
   * @return array
   *   The array of speculation rules.
   */
  public function getSpeculationRules(): array;

  /**
   * Gets paths to exclude from speculation rules.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return array
   *   The array of paths to exclude.
   */
  public function getHrefExcludePaths(string $mode): array;

}
