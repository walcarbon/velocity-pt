# Ops pipeline

## Docker
1. `cd ops`
2. `docker compose up -d`
3. `docker compose exec wordpress bash`

## Bootstrap + seed
1. `bash /var/www/html/wp-content/ops/scripts/bootstrap.sh`
2. `bash /var/www/html/wp-content/ops/scripts/seed.sh`

## Elementor Kit CLI
- Export: `bash /var/www/html/wp-content/ops/scripts/elementor-kit.sh export`
- Import: `bash /var/www/html/wp-content/ops/scripts/elementor-kit.sh import`

> Paid plugins are not downloaded by scripts. Install Elementor Pro / ACF Pro from local zip in WP Admin when licensed.
