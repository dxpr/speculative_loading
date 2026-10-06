#!/bin/bash

source scripts/prepare-drupal-lint.sh

# Run the module ruleset (Drupal, DrupalPractice, PHPCompatibility).
echo "Checking coding standards..."
phpcs
