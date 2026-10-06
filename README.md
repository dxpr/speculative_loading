> **Speculative Loading** enables near-instant page navigation on Drupal sites by implementing the browser Speculation Rules API for intelligent prefetching and prerendering. Maintained by [DXPR](https://dxpr.com).
>
> [Getting Started](https://dxpr.com/c/marketing-cms) |
> [Pricing](https://dxpr.com/pricing) |
> [Try Free Demo](https://try.dxpr.com)

# Speculative Loading - Near-Instant Drupal Page Navigation with Prefetch and Prerender

Enables browsers to speculatively prerender or prefetch pages to achieve
near-instant loads based on user interaction.

## Description

This module adds support for the [Speculation Rules
API](https://developer.mozilla.org/en-US/docs/Web/API/Speculation_Rules_API),
which allows defining rules by which certain URLs are dynamically prefetched
or prerendered.

By default, the module is enabled and configured to prerender Drupal frontend
URLs when the user interacts with a relevant link. Rules are added to
frontend pages only, never to administration pages. You can change this in
the **Speculative Loading** tab of the Performance settings.

The module always excludes administrative paths, user login and account
paths, URLs with query strings, and file, module and theme assets. A hook
can add or remove excluded site paths. Alternatively, you can add the
`no-prerender` CSS class to any link that should not be prerendered.

Pages are tagged with the module configuration, so changing settings
invalidates page caches automatically.

## Browser support

The Speculation Rules API is a new web API, and the functionality used by the
module is supported in Chromium-based browsers such as Chrome, Edge, or Opera
using version 121 or above. Other browsers such as Safari and Firefox will
ignore the functionality with no ill effects; they will simply not benefit
from the speculative loading. Note that certain browser extensions may
disable preloading by default.

**Wider browser support is underway:** Firefox is actively working on
implementing the Speculation Rules API. You can track their progress here:
[Firefox Bug #1969396](https://bugzilla.mozilla.org/show_bug.cgi?id=1969396)

* [Browser support for the Speculation Rules API in
general](https://caniuse.com/mdn-html_elements_script_type_speculationrules)
* [Information on document rules syntax support used by the
module](https://developer.chrome.com/docs/web-platform/prerender-pages)

## Installation

1. Install the module via Composer:
`composer require drupal/speculative_loading`
2. Enable the module through the Drupal UI or Drush:
`drush en speculative_loading`

## Configuration

1. Go to **Configuration** > **Development** > **Performance** and open the
**Speculative Loading** tab.
2. Keep **Enable speculative loading** checked. Uncheck it to stop adding
rules without uninstalling the module.
3. Choose between **Prefetch** or **Prerender** mode (prerendering is faster
but may cause issues with some interactive content).
4. Select the eagerness level (**Conservative**, **Moderate**, or **Eager**).
5. Save your configuration.

## Hooks and Extension

To add or remove excluded site paths, implement
`hook_speculation_rules_href_exclude_paths_alter()` in your module. Patterns
are site-relative paths such as `/cart/*`; the module prefixes them for the
site automatically. URLs with query strings and file, module and theme
assets stay excluded and cannot be removed through this hook.

```php
/**
 * Implements hook_speculation_rules_href_exclude_paths_alter().
 */
function mymodule_speculation_rules_href_exclude_paths_alter(
  array &$exclude_paths,
  string $mode,
) {
  // Exclude cart pages from prerendering.
  if ($mode === 'prerender') {
    $exclude_paths[] = '/cart/*';
  }

  // Exclude certain paths from both prefetching and prerendering.
  $exclude_paths[] = '/checkout/*';
}
```

## Plugins

The module collects exclusions and extra rules from every
`SpeculationRules` plugin. For each plugin, the manager merges
`getExcludePaths()` into the denylist for the current mode and appends
`getRules()` to the mode rule list. In prerender mode, the default plugin
additionally excludes content editing paths (`/node/add/*`,
`/node/*/edit`, `/node/*/delete`) and user edit pages (`/user/*/edit`).
In both modes it excludes password reset paths (`/user/reset/*`) and
confirmation paths (`/confirm/*`).

```php
/**
 * @SpeculationRules(
 *   id = "my_custom_rules",
 *   title = @Translation("My Custom Rules"),
 *   description = @Translation("Custom speculation rules implementation.")
 * )
 */
class MyCustomRules extends PluginBase implements SpeculationRulesInterface {
  // Implement the interface methods...
}
```

## Development

### Code Quality Tools

The module includes several code quality tools to ensure adherence to Drupal
coding standards and best practices. These tools run locally within the
module directory.

#### Setup

To set up the development tools:

1. Navigate to the module directory:
`cd web/modules/contrib/speculative_loading`
2. Install the development dependencies: `composer install`

#### Available Tools

* **PHP CodeSniffer** - Checks coding standards using `phpcs.xml.dist`
  * Run: `composer phpcs`
  * Fix automatically: `composer phpcbf`

* **PHPStan** - Static analysis tool
  * Run: `composer phpstan`

#### CI Integration

For continuous integration, you can add these commands to your CI workflow:

```yaml
- name: Install dependencies
  run: cd web/modules/contrib/speculative_loading && composer install

- name: Run coding standards check
  run: cd web/modules/contrib/speculative_loading && composer phpcs

- name: Run static analysis
  run: cd web/modules/contrib/speculative_loading && composer phpstan
```

## Credits

This module is inspired by the WordPress Speculative Loading plugin.

---

## Related Modules

Modules that complement Speculative Loading for Drupal performance:

- [BigPipe](https://www.drupal.org/docs/core-modules-and-themes/core-modules/bigpipe) - Drupal core module for progressive page rendering; works alongside Speculative Loading to reduce both initial and subsequent page load times
- [Internal Page Cache](https://www.drupal.org/docs/administering-a-drupal-site/internal-page-cache) - Drupal core caching for anonymous users; caches the pages that Speculative Loading prerenders
- [DXPR Theme](https://www.drupal.org/project/dxpr_theme) - Responsive Drupal theme with built-in performance optimizations
- [DXPR Builder](https://www.drupal.org/project/dxpr_builder) - Drag-and-drop page builder for Drupal
