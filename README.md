# Velocity PT starter stack

1. Place this repo in `wp-content/` for a local WordPress install.
2. Start stack with `cd ops && docker compose up -d`.
3. Enter container: `docker compose exec wordpress bash`.
4. Run bootstrap: `bash /var/www/html/wp-content/ops/scripts/bootstrap.sh`.
5. Install licensed plugins from local zips (Elementor Pro, ACF Pro).
6. Run seed: `bash /var/www/html/wp-content/ops/scripts/seed.sh`.
7. Confirm CPT archives: `/service/` and `/location/`.
8. Build Theme Builder templates using `elementor-kit/README.md`.
9. Export/import kits with `wp elementor kit export|import elementor-kit/carbon-kit.zip`.
10. Do not create Pages with slug `service` or `location` (rewrite conflicts).
