<?php

namespace Drupal\speculative_loading\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Speculation Rules plugin annotation object.
 *
 * @Annotation
 */
class SpeculationRules extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
