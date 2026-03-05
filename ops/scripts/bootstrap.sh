#!/usr/bin/env bash
set -euo pipefail

wp core install --url=http://localhost:8080 --title='Velocity PT' --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
wp plugin install advanced-custom-fields --activate
wp theme install hello-elementor --activate
wp theme activate carbon-hello-child
wp plugin activate carbon-core
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard
