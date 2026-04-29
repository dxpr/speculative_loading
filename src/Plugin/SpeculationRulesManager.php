<?php

namespace Drupal\speculative_loading\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\speculative_loading\UrlPattern\UrlPatternPrefixer;

/**
 * Manages Speculation Rules plugins.
 */
class SpeculationRulesManager extends DefaultPluginManager implements SpeculationRulesManagerInterface {

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a SpeculationRulesManager object.
   *
   * @param \Drupal\speculative_loading\UrlPattern\UrlPatternPrefixer $url_pattern_prefixer
   *   The URL pattern prefixer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
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
    AccountInterface $current_user,
    \Traversable $namespaces,
    ?CacheBackendInterface $cache_backend = NULL,
    ?ModuleHandlerInterface $module_handler = NULL,
  ) {
    parent::__construct(
      'Plugin/SpeculationRules',
      $namespaces,
      $module_handler,
      'Drupal\speculative_loading\Plugin\SpeculationRulesInterface',
      'Drupal\speculative_loading\Annotation\SpeculationRules'
    );

    if ($cache_backend) {
      $this->setCacheBackend($cache_backend, 'speculation_rules_plugins');
    }

    $this->urlPatternPrefixer = $url_pattern_prefixer;
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpeculationRules(): array {
    $config = $this->configFactory->get('speculative_loading.settings');
    $mode = $config->get('mode');
    $eagerness = $config->get('eagerness');

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
              'href_matches' => $this->urlPatternPrefixer->prefixPathPattern('/*'),
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

    return [$mode => $rules];
  }

  /**
   * {@inheritdoc}
   */
  public function getHrefExcludePaths(string $mode): array {
    // Define base exclude paths.
    $base_href_exclude_paths = [
      $this->urlPatternPrefixer->prefixPathPattern('/admin/*', 'site'),
      $this->urlPatternPrefixer->prefixPathPattern('/user/login*', 'site'),
      $this->urlPatternPrefixer->prefixPathPattern('/user/logout*', 'site'),
      $this->urlPatternPrefixer->prefixPathPattern('/user/register*', 'site'),
      $this->urlPatternPrefixer->prefixPathPattern('/*', 'files'),
      $this->urlPatternPrefixer->prefixPathPattern('/*', 'modules'),
      $this->urlPatternPrefixer->prefixPathPattern('/*', 'themes'),
    ];

    // Always exclude URLs with query parameters.
    $base_href_exclude_paths[] = $this->urlPatternPrefixer->prefixPathPattern('/*\\?(.+)', 'site');

    // Let other modules alter the exclude paths.
    $href_exclude_paths = [];
    $this->moduleHandler->alter('speculation_rules_href_exclude_paths', $href_exclude_paths, $mode);

    // Combine and normalize the paths.
    $all_paths = array_values(
      array_unique(
        array_merge(
          $base_href_exclude_paths,
          array_map(
            function (string $path) {
              return $this->urlPatternPrefixer->prefixPathPattern($path);
            },
            $href_exclude_paths
          )
        )
      )
    );

    return $all_paths;
  }

}
