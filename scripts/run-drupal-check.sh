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

MODULE_NAME=speculative_loading
if [ ! -L "web/modules/contrib/$MODULE_NAME" ]; then
  ln -s /src "web/modules/contrib/$MODULE_NAME"
fi

# drupal-check 1.x needs PHPStan 1, which clashes with the PHPStan 2 that
# drupal/core-dev installs, so run the same level 6 rules with PHPStan.
composer require --dev phpstan/phpstan mglaman/phpstan-drupal phpstan/phpstan-deprecation-rules --with-all-dependencies --no-interaction

cat <<EOF > phpstan.neon
parameters:
    paths:
        - web/modules/contrib/$MODULE_NAME
    level: 6
EOF

./vendor/bin/phpstan analyse --memory-limit=-1 -c phpstan.neon
