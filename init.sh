#!/bin/sh
chown -R www-data .
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
exec "$@"