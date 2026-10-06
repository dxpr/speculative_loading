<?php

/**
 * @file
 * Hooks provided by the Speculative Loading module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the list of site paths excluded from speculation rules.
 *
 * This hook allows modules to add or remove site-relative path patterns from
 * the list of paths excluded from speculation rules. Patterns are raw site
 * paths such as '/cart/*'; the module prefixes them automatically for the
 * current site base path.
 *
 * The list passed to this hook already contains the built-in denylist
 * (administrative paths, user login, logout, registration and password
 * pages) plus every path contributed by SpeculationRules plugins for the
 * current mode. URLs with query strings and file, module and theme assets
 * are always excluded and cannot be removed through this hook.
 *
 * @param array $exclude_paths
 *   An array of site-relative path patterns excluded from speculation rules.
 * @param string $mode
 *   The speculation mode, either 'prefetch' or 'prerender'.
 */
function hook_speculation_rules_href_exclude_paths_alter(
  array &$exclude_paths,
  string $mode,
) {
  // Exclude cart pages from prerendering.
  if ($mode === 'prerender') {
    $exclude_paths[] = '/cart/*';
  }

  // Exclude checkout from both prefetching and prerendering.
  $exclude_paths[] = '/checkout/*';
}

/**
 * @} End of "addtogroup hooks".
 */
