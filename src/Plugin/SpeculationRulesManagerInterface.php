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
   * @return array<string, list<array<string, mixed>>>
   *   The speculation rules, keyed by speculation mode.
   */
  public function getSpeculationRules(): array;

  /**
   * Gets paths to exclude from speculation rules.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return list<string>
   *   The array of paths to exclude.
   */
  public function getHrefExcludePaths(string $mode): array;

}
