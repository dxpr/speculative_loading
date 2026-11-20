<?php

declare(strict_types=1);

namespace Drupal\speculative_loading\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Declares a SpeculationRules plugin.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class SpeculationRules extends Plugin {

  /**
   * Constructs a plugin definition.
   */
  public function __construct(
    string $id,
    public readonly ?TranslatableMarkup $title = NULL,
    public readonly ?TranslatableMarkup $description = NULL,
    ?string $deriver = NULL,
  ) {
    parent::__construct($id, $deriver);
  }

}
