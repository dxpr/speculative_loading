<?php

namespace Drupal\speculative_loading\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for Speculation Rules plugins.
 */
interface SpeculationRulesInterface extends PluginInspectionInterface {

  /**
   * Gets the plugin's extra rules for a specific mode.
   *
   * Each returned rule is appended to the mode's rule list built by the
   * manager. A rule is a Speculation Rules API rule array, for example a
   * document rule with its own 'where' conditions and 'eagerness'.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return array
   *   A list of rule arrays to append. Return an empty array to add nothing.
   */
  public function getRules(string $mode): array;

  /**
   * Gets site paths that should be excluded from speculation rules.
   *
   * Return raw site-relative patterns such as '/cart/*'. The manager merges
   * them with the built-in denylist, lets other modules alter the merged
   * list, and prefixes every path for the current site automatically.
   *
   * @param string $mode
   *   The speculation mode, either 'prefetch' or 'prerender'.
   *
   * @return array
   *   A list of site-relative path patterns to exclude.
   */
  public function getExcludePaths(string $mode): array;

}
