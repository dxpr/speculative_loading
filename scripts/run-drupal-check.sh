#!/bin/sh

# Install required libs by drupal
GD_ENABLED=$(php -i | grep 'GD Support' | awk '{ print $4 }')

if [ "$GD_ENABLED" != 'enabled' ]; then
  echo "Installing GD library..."
  apk update && \
  apk add libpng libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev gd && docker-php-ext-install gd
fi

if [ -z "$DRUPAL_RECOMMENDED_PROJECT" ]; then
  DRUPAL_RECOMMENDED_PROJECT=11.x-dev
fi

# if drupal directory exists, don't re-create
if [ ! -d "/drupal" ]; then
  # --no-dev keeps drupal/core-dev's PHPStan 2 extensions out of the site.
  composer create-project drupal/recommended-project:$DRUPAL_RECOMMENDED_PROJECT drupal --no-interaction --stability=dev --no-dev
fi

cd drupal
mkdir -p web/modules/contrib/

MODULE_NAME=speculative_loading
if [ ! -L "web/modules/contrib/$MODULE_NAME" ]; then
  ln -s /src "web/modules/contrib/$MODULE_NAME"
fi

# drupal-check 1.x needs PHPStan 1, which clashes with the PHPStan 2 that
# drupal/core-dev installs, so it runs from its own project outside the site.
DRUPAL_CHECK_DIR=/drupal-check
mkdir -p "$DRUPAL_CHECK_DIR"
composer --working-dir="$DRUPAL_CHECK_DIR" init --name=dxpr/drupal-check-runner --no-interaction
composer --working-dir="$DRUPAL_CHECK_DIR" require mglaman/drupal-check --no-interaction

"$DRUPAL_CHECK_DIR/vendor/bin/drupal-check" --drupal-root . -ad "web/modules/contrib/$MODULE_NAME"
