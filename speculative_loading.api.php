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
 * Alter the list of paths excluded from speculation rules.
 *
 * This hook allows modules to add or remove paths from the list of paths that
 * are excluded from speculation rules.
 *
 * @param array $exclude_paths
 *   An array of path patterns to exclude from speculation rules.
 * @param string $mode
 *   The speculation mode, either 'prefetch' or 'prerender'.
 *
 * @ingroup speculative_loading_api
 */
function hook_speculation_rules_href_exclude_paths_alter(array &$exclude_paths, string $mode) {
  // Exclude cart pages from prerendering.
  if ($mode === 'prerender') {
    $exclude_paths[] = '/cart/*';
  }
  
  // Exclude checkout from both prefetching and prerendering.
  $exclude_paths[] = '/checkout/*';
}

/**
 * @} End of "addtogroup hooks". 