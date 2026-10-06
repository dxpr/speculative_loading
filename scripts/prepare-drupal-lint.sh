#!/bin/bash

echo "$COMPOSER_HOME: $COMPOSER_HOME"

# Allow the plugin installer
composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# Install lint tooling in one step so versions resolve together. PHPCS 3 is
# required because php-compatibility does not support PHPCS 4 yet.
composer global require \
  squizlabs/php_codesniffer:^3.13 \
  drupal/coder \
  phpcompatibility/php-compatibility \
  dealerdirect/phpcodesniffer-composer-installer

export PATH="$PATH:$COMPOSER_HOME/vendor/bin"

composer global show -P
phpcs -i

# Configure PHPCS settings
phpcs --config-set colors 1
phpcs --config-set ignore_warnings_on_exit 1
phpcs --config-set drupal_core_version 11

phpcs --config-show
