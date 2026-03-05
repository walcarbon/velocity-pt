# Elementor Theme Builder playbook

## 1) Prerequisites
- Activate Elementor + Elementor Pro.
- In **Elementor > Settings > Integrations**, set Google Maps API key only if using native Google Maps widget (optional).

## 2) Header template (site-wide)
1. Go to **Templates > Theme Builder > Header > Add New**.
2. Build mobile-first layout with logo, nav, and menu toggle.
3. Add WP Menu widget and choose `Primary`.
4. For mega menu content:
   - Services column: add Shortcode widget with `[carbon_services_grid]`.
   - Locations column: add Shortcode widget with `[carbon_locations_grid]`.
5. Publish with Display Condition: **Entire Site**.

## 3) Footer template (site-wide)
1. Build footer in Theme Builder.
2. Include contact summary, quick links, and CTA.
3. Publish with Display Condition: **Entire Site**.

## 4) Single Service template
1. Theme Builder > Single Post > add for post type `Service`.
2. Use Dynamic tags for title/content + ACF fields (`hero_title`, `intro`, etc.).
3. Publish with Display Condition: **All Services**.

## 5) Single Location template
1. Theme Builder > Single Post > add for post type `Location`.
2. Build sections:
   - Hero: Post Title + CTAs (Call / Request Appointment / Get Directions).
   - Intro block: dynamic ACF `location_intro`.
   - Photos: Shortcode `[carbon_location_gallery]`.
   - Services at this location: Shortcode `[carbon_location_services]`.
   - Map: Shortcode `[carbon_location_map show_directions_button="1" directions_label="Get Directions"]`.
3. Publish with Display Condition: **All Locations**.

## 6) Optional native Elementor Google Maps widget path
- Replace `[carbon_location_map]` with Google Maps widget in Single Location template.
- Ensure API key is configured in Elementor Integrations.
- Keep `Get Directions` button linked to ACF `directions_url` dynamic field.

## 7) Kit export/import (official only)
- Export: `wp elementor kit export elementor-kit/carbon-kit.zip`
- Import: `wp elementor kit import elementor-kit/carbon-kit.zip`
