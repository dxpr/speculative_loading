> **Speculative Loading** enables near-instant page navigation on Drupal sites by implementing the browser Speculation Rules API for intelligent prefetching and prerendering. Maintained by [DXPR](https://dxpr.com).
>
> [Getting Started](https://dxpr.com/c/getting-started) |
> [Pricing](https://dxpr.com/pricing) |
> [Try Free Demo](https://dxpr.com/try)

# Speculative Loading - Near-Instant Drupal Page Navigation with Prefetch and Prerender

Enables browsers to speculatively prerender or prefetch pages to achieve near-instant loads based on user interaction.

## Description

This module adds support for the [Speculation Rules API](https://developer.mozilla.org/en-US/docs/Web/API/Speculation_Rules_API), which allows defining rules by which certain URLs are dynamically prefetched or prerendered.

By default, the module is configured to prerender Drupal frontend URLs when the user interacts with a relevant link. This can be customized via the "Speculative Loading" section in the Performance settings.

A hook can be used to exclude certain URL paths from being eligible for prefetching and prerendering. Alternatively, you can add the `no-prerender` CSS class to any link that should not be prerendered.

## Browser support

The Speculation Rules API is a new web API, and the functionality used by the module is supported in Chromium-based browsers such as Chrome, Edge, or Opera using version 121 or above. Other browsers such as Safari and Firefox will ignore the functionality with no ill effects; they will simply not benefit from the speculative loading. Note that certain browser extensions may disable preloading by default.

**Wider browser support is underway:** Firefox is actively working on implementing the Speculation Rules API. You can track their progress here: [Firefox Bug #1969396](https://bugzilla.mozilla.org/show_bug.cgi?id=1969396)

* [Browser support for the Speculation Rules API in general](https://caniuse.com/mdn-html_elements_script_type_speculationrules)
* [Information on document rules syntax support used by the module](https://developer.chrome.com/docs/web-platform/prerender-pages)

## Installation

1. Install the module via Composer: `composer require drupal/speculative_loading`
2. Enable the module through the Drupal UI or Drush: `drush en speculative_loading`

## Configuration

1. Navigate to "Configuration" > "Development" > "Performance" > "Speculative Loading"
2. Choose between "Prefetch" or "Prerender" mode (prerendering is faster but may cause issues with some interactive content)
3. Select the eagerness level (Conservative, Moderate, or Eager)
4. Save your configuration

## Hooks and Extension

To exclude certain URL patterns from prefetching and prerendering, implement `hook_speculation_rules_href_exclude_paths_alter` in your module:

```php
/**
 * Implements hook_speculation_rules_href_exclude_paths_alter().
 */
function mymodule_speculation_rules_href_exclude_paths_alter(array &$exclude_paths, string $mode) {
  // Exclude cart pages from prerendering.
  if ($mode === 'prerender') {
    $exclude_paths[] = '/cart/*';
  }
  
  // Exclude certain paths from both prefetching and prerendering.
  $exclude_paths[] = '/checkout/*';
}
```

## Plugins

The module uses Drupal's Plugin API. You can create custom SpeculationRules plugins to add additional rules or exclusion patterns:

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

The module includes several code quality tools to ensure adherence to Drupal coding standards and best practices. These tools run locally within the module directory.

#### Setup

To set up the development tools:

1. Navigate to the module directory: `cd web/modules/contrib/speculative_loading`
2. Install the development dependencies: `composer install`

#### Available Tools

* **PHP CodeSniffer** - Checks coding standards
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
