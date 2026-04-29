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
  composer create-project drupal/recommended-project:$DRUPAL_RECOMMENDED_PROJECT drupal --no-interaction --stability=dev
fi

cd drupal
mkdir -p web/modules/contrib/

MODULE_NAME=$(basename $(dirname $(dirname "$0")))
if [ ! -L "web/modules/contrib/$MODULE_NAME" ]; then
  ln -s /src "web/modules/contrib/$MODULE_NAME"
fi

composer require mglaman/drupal-check --dev
./vendor/bin/drupal-check --drupal-root . -ad "web/modules/contrib/$MODULE_NAME"
