<?php

namespace Drupal\speculative_loading\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\speculative_loading\UrlPattern\UrlPatternPrefixer;

/**
 * Manages Speculation Rules plugins.
 *
 * Collects URL exclusions and extra rules from every SpeculationRules plugin,
 * merges them with the built-in denylist, and exposes the result through
 * ::getSpeculationRules().
 */
class SpeculationRulesManager extends DefaultPluginManager implements SpeculationRulesManagerInterface {

  /**
   * Valid speculation modes.
   */
  protected const VALID_MODES = ['prefetch', 'prerender'];

  /**
   * Valid eagerness levels.
   */
  protected const VALID_EAGERNESS = ['conservative', 'moderate', 'eager'];

  /**
   * The URL pattern prefixer service.
   *
   * @var \Drupal\speculative_loading\UrlPattern\UrlPatternPrefixer
   */
  protected $urlPatternPrefixer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a SpeculationRulesManager object.
   *
   * @param \Drupal\speculative_loading\UrlPattern\UrlPatternPrefixer $url_pattern_prefixer
   *   The URL pattern prefixer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    UrlPatternPrefixer $url_pattern_prefixer,
    ConfigFactoryInterface $config_factory,
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      'Plugin/SpeculationRules',
      $namespaces,
      $module_handler,
      'Drupal\speculative_loading\Plugin\SpeculationRulesInterface',
      'Drupal\speculative_loading\Annotation\SpeculationRules'
    );

    $this->setCacheBackend($cache_backend, 'speculation_rules_plugins');
    $this->urlPatternPrefixer = $url_pattern_prefixer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpeculationRules(): array {
    $config = $this->configFactory->get('speculative_loading.settings');
    $enabled = $config->get('enabled');
    // Beta releases had no 'enabled' key and were always active.
    if ($enabled === NULL) {
      $enabled = TRUE;
    }
    if (!$enabled) {
      return [];
    }
    $mode = $this->normalizeMode($config->get('mode'));
    $eagerness = $this->normalizeEagerness($config->get('eagerness'));
    $prefixer = $this->urlPatternPrefixer;

    // Get exclude paths for the current mode.
    $href_exclude_paths = $this->getHrefExcludePaths($mode);

    // Create the rules array.
    $rules = [
      [
        'source' => 'document',
        'where' => [
          'and' => [
            // Include any URLs within the same site.
            [
              'href_matches' => $prefixer->prefixPathPattern('/*'),
            ],
            // Exclude specific paths.
            [
              'not' => [
                'href_matches' => $href_exclude_paths,
              ],
            ],
            // Exclude nofollow links.
            [
              'not' => [
                'selector_matches' => 'a[rel~="nofollow"]',
              ],
            ],
          ],
        ],
        'eagerness' => $eagerness,
      ],
    ];

    // For prerender mode, also exclude links with no-prerender class.
    if ('prerender' === $mode) {
      $rules[0]['where']['and'][] = [
        'not' => [
          'selector_matches' => '.no-prerender',
        ],
      ];
    }

    // Append extra rules contributed by plugins.
    foreach ($this->getDefinitions() as $definition) {
      /** @var \Drupal\speculative_loading\Plugin\SpeculationRulesInterface $plugin */
      $plugin = $this->createInstance($definition['id']);
      foreach ($plugin->getRules($mode) as $rule) {
        $rules[] = $rule;
      }
    }

    return [$mode => $rules];
  }

  /**
   * {@inheritdoc}
   */
  public function getHrefExcludePaths(string $mode): array {
    $mode = $this->normalizeMode($mode);
    $prefixer = $this->urlPatternPrefixer;

    // Raw site-relative paths, prefixed after the alter hook. Start with the
    // built-in denylist for interactive or sensitive site paths.
    $site_paths = [
      '/admin/*',
      '/user/login*',
      '/user/logout*',
      '/user/register*',
      '/user/password*',
    ];

    // Merge exclusions contributed by plugins.
    foreach ($this->getDefinitions() as $definition) {
      /** @var \Drupal\speculative_loading\Plugin\SpeculationRulesInterface $plugin */
      $plugin = $this->createInstance($definition['id']);
      $site_paths = array_merge($site_paths, $plugin->getExcludePaths($mode));
    }

    // Let other modules add or remove site paths.
    $this->moduleHandler->alter(
      'speculation_rules_href_exclude_paths',
      $site_paths,
      $mode
    );
    $site_paths = array_values(array_unique($site_paths));

    // Prefix the alterable site paths.
    $exclude_paths = [];
    foreach ($site_paths as $path) {
      $exclude_paths[] = $prefixer->prefixPathPattern($path, 'site');
    }

    // Mandatory exclusions that hooks cannot remove: URLs with query
    // parameters, plus file, module and theme assets.
    $mandatory = [
      ['/*\\?(.+)', 'site'],
      ['/*', 'files'],
      ['/*', 'modules'],
      ['/*', 'themes'],
    ];
    foreach ($mandatory as [$pattern, $context]) {
      $exclude_paths[] = $prefixer->prefixPathPattern($pattern, $context);
    }

    return array_values(array_unique($exclude_paths));
  }

  /**
   * Normalizes a configured speculation mode.
   *
   * @param mixed $mode
   *   The configured value.
   *
   * @return string
   *   A valid mode, defaulting to 'prerender'.
   */
  protected function normalizeMode($mode): string {
    if (!in_array($mode, self::VALID_MODES, TRUE)) {
      return 'prerender';
    }
    return $mode;
  }

  /**
   * Normalizes a configured eagerness level.
   *
   * @param mixed $eagerness
   *   The configured value.
   *
   * @return string
   *   A valid eagerness level, defaulting to 'moderate'.
   */
  protected function normalizeEagerness($eagerness): string {
    if (!in_array($eagerness, self::VALID_EAGERNESS, TRUE)) {
      return 'moderate';
    }
    return $eagerness;
  }

}
