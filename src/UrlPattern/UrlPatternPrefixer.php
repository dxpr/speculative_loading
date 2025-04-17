<?php

namespace Drupal\speculative_loading\UrlPattern;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;

/**
 * Service for managing URL path patterns.
 */
class UrlPatternPrefixer {

  /**
   * Map of context string to base path pairs.
   *
   * @var array
   */
  protected $contexts;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a UrlPatternPrefixer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   * @param array $contexts
   *   Optional contexts to use instead of the defaults.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    FileUrlGeneratorInterface $file_url_generator,
    array $contexts = []
  ) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->fileUrlGenerator = $file_url_generator;
    
    if (!empty($contexts)) {
      $this->contexts = array_map(
        function (string $path): string {
          return $this->escapePatternString(rtrim($path, '/') . '/');
        },
        $contexts
      );
    }
    else {
      $this->contexts = $this->getDefaultContexts();
    }
  }

  /**
   * Prefixes a path pattern with the base path for the given context.
   *
   * @param string $path_pattern
   *   URL pattern starting with the path segment.
   * @param string $context
   *   Context to use. Default 'site'.
   *
   * @return string
   *   URL pattern with appropriate prefix applied.
   */
  public function prefixPathPattern(string $path_pattern, string $context = 'site'): string {
    // Check if the context exists.
    if (!isset($this->contexts[$context])) {
      trigger_error(
        sprintf('Invalid context %s.', $context),
        E_USER_WARNING
      );
      return $path_pattern;
    }

    // Handle special characters in context path.
    $context_path = $this->contexts[$context];
    $escaped_context_path = $context_path;
    
    if (strcspn($context_path, ':?#') !== strlen($context_path)) {
      $escaped_context_path = '{' . substr($context_path, 0, -1) . '}/';
    }

    // If path already starts with context path, remove it.
    if (strpos($path_pattern, $context_path) === 0) {
      $path_pattern = substr($path_pattern, strlen($context_path));
    }

    return $escaped_context_path . ltrim($path_pattern, '/');
  }

  /**
   * Gets the default contexts for path prefixing.
   *
   * @return array
   *   Map of context string to base path pairs.
   */
  protected function getDefaultContexts(): array {
    $base_path = $this->escapePatternString(base_path());
    
    // Get base URLs for various path types.
    $site_config = $this->configFactory->get('system.site');
    $front_url = $site_config->get('page.front');
    
    // Get file paths.
    $public_path = $this->escapePatternString($this->fileUrlGenerator->generateString('public://'));
    
    // Module and theme paths.
    $module_path = $this->escapePatternString($base_path . $this->getRelativePath('module'));
    $theme_path = $this->escapePatternString($base_path . $this->getRelativePath('theme'));
    
    return [
      'site' => $base_path,
      'files' => $public_path,
      'modules' => $module_path,
      'themes' => $theme_path,
    ];
  }

  /**
   * Gets a relative path for a specific extension type.
   *
   * @param string $type
   *   Extension type (module or theme).
   *
   * @return string
   *   Relative path.
   */
  protected function getRelativePath(string $type): string {
    $directory = 'module' === $type ? 'modules' : 'themes';
    return $directory;
  }

  /**
   * Escapes a string for use in a URL pattern component.
   *
   * @param string $str
   *   String to be escaped.
   *
   * @return string
   *   Escaped string.
   */
  protected function escapePatternString(string $str): string {
    return addcslashes($str, '+*?:{}()\\');
  }

} 