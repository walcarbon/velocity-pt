<?php
/**
 * Core plugin bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Carbon_Core {
	private static array $location_seed = array(
		'denton'       => array(
			'title'   => 'Denton',
			'phone'   => '(940) 387-3700',
			'fax'     => '(940) 488-4513',
			'address' => '3301 Sundown Blvd, Denton, TX 76210',
		),
		'north-denton' => array(
			'title'   => 'North Denton',
			'phone'   => '(940) 387-1101',
			'fax'     => '(940) 666-3150',
			'address' => '2004 Emery St., Denton, TX 76201',
		),
		'cross-roads'  => array(
			'title'   => 'Cross Roads',
			'phone'   => '(940) 365-9200',
			'fax'     => '(940) 222-5598',
			'address' => '8800 US HWY 380 Suite 100, Cross Roads, TX 76227',
		),
		'sanger'       => array(
			'title'   => 'Sanger',
			'phone'   => '(940) 387-7601',
			'fax'     => '(940) 257-6200',
			'address' => '212 Boliver Street, Suite 100, Sanger, TX 76266',
		),
	);

	private static array $service_seed  = array(
		'Manual Therapy',
		'Therapeutic Exercise',
		'Neuromuscular Re-education',
		'Gait Training',
		'Balance & Coordination Training',
		'Fall Prevention Program',
		'Vestibular Rehabilitation',
		'Strength Training',
		'Functional Training (Therapeutic Activities)',
		'Sports Rehabilitation',
		'Sports Performance & Injury Prevention',
		'Post-Surgical Rehabilitation',
		'Work Conditioning / Industrial Therapy',
		'Adaptive Equipment Training',
		'Biofeedback Training (EMG)',
		'Dry Needling',
		'Pain Management Modalities (heat/cold/e-stim/ultrasound)',
		'Soft Tissue / Myofascial Techniques',
		'Posture & Body Mechanics Training',
		'Home Exercise Program (HEP) & Patient Education',
	);

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_content_types' ) );
		add_action( 'init', array( __CLASS__, 'seed_taxonomies' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) );
		add_filter( 'acf/settings/save_json', array( __CLASS__, 'acf_save_json' ) );
		add_filter( 'acf/settings/load_json', array( __CLASS__, 'acf_load_json' ) );
		add_action( 'save_post_location', array( __CLASS__, 'ensure_directions_url' ), 20, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_post_carbon_run_seed', array( __CLASS__, 'handle_admin_seed' ) );
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'carbon seed', array( __CLASS__, 'cli_seed' ) );
		}
	}

	public static function register_content_types(): void {
		register_post_type(
			'service',
			array(
				'label'        => __( 'Services', 'carbon-core' ),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => 'service',
				'rewrite'      => array( 'slug' => 'service' ),
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			)
		);

		register_post_type(
			'location',
			array(
				'label'        => __( 'Locations', 'carbon-core' ),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => 'location',
				'rewrite'      => array( 'slug' => 'location' ),
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			)
		);

		register_taxonomy(
			'service_category',
			array( 'service' ),
			array(
				'label'        => __( 'Service Categories', 'carbon-core' ),
				'public'       => true,
				'show_in_rest' => true,
			)
		);

		register_taxonomy(
			'location_region',
			array( 'location' ),
			array(
				'label'        => __( 'Location Regions', 'carbon-core' ),
				'public'       => true,
				'show_in_rest' => true,
			)
		);
	}

	public static function seed_taxonomies(): void {
		foreach ( array( 'Orthopedics', 'Sports Medicine', 'Pelvic Health', 'Pain Relief' ) as $term ) {
			if ( ! term_exists( $term, 'service_category' ) ) {
				wp_insert_term( $term, 'service_category' );
			}
		}
		if ( ! term_exists( 'North Texas', 'location_region' ) ) {
			wp_insert_term( 'North Texas', 'location_region' );
		}
	}

	public static function acf_save_json( string $path ): string {
		return CARBON_CORE_PATH . 'acf-json';
	}

	public static function acf_load_json( array $paths ): array {
		$paths[] = CARBON_CORE_PATH . 'acf-json';
		return array_unique( $paths );
	}

	public static function register_acf_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_carbon_service_fields',
				'title'    => 'Service Fields',
				'fields'   => array(
					array( 'key' => 'field_service_hero_title', 'label' => 'Hero Title', 'name' => 'hero_title', 'type' => 'text' ),
					array( 'key' => 'field_service_intro', 'label' => 'Intro', 'name' => 'intro', 'type' => 'wysiwyg' ),
					array(
						'key' => 'field_service_highlights', 'label' => 'Highlights', 'name' => 'highlights', 'type' => 'repeater', 'sub_fields' => array(
							array( 'key' => 'field_service_highlight_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
							array( 'key' => 'field_service_highlight_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea' ),
							array( 'key' => 'field_service_highlight_icon', 'label' => 'Icon', 'name' => 'icon', 'type' => 'text' ),
						),
					),
					array( 'key' => 'field_service_icon', 'label' => 'Service Icon', 'name' => 'service_icon', 'type' => 'image', 'return_format' => 'id' ),
					array(
						'key' => 'field_service_faq', 'label' => 'FAQ', 'name' => 'faq', 'type' => 'repeater', 'sub_fields' => array(
							array( 'key' => 'field_service_faq_q', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ),
							array( 'key' => 'field_service_faq_a', 'label' => 'Answer', 'name' => 'answer', 'type' => 'textarea' ),
						),
					),
					array( 'key' => 'field_service_cta_title', 'label' => 'CTA Title', 'name' => 'cta_title', 'type' => 'text' ),
					array(
						'key' => 'field_service_cta_button', 'label' => 'CTA Button', 'name' => 'cta_button', 'type' => 'group', 'sub_fields' => array(
							array( 'key' => 'field_service_cta_button_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text' ),
							array( 'key' => 'field_service_cta_button_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
						),
					),
					array( 'key' => 'field_service_seo_summary', 'label' => 'SEO Summary', 'name' => 'seo_summary', 'type' => 'textarea' ),
					array( 'key' => 'field_service_featured_on_home', 'label' => 'Featured on Home', 'name' => 'featured_on_home', 'type' => 'true_false' ),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'service' ) ) ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_carbon_location_fields',
				'title'    => 'Location Fields',
				'fields'   => array(
					array( 'key' => 'field_location_intro', 'label' => 'Location Intro', 'name' => 'location_intro', 'type' => 'wysiwyg' ),
					array( 'key' => 'field_location_photos', 'label' => 'Location Photos', 'name' => 'location_photos', 'type' => 'gallery', 'return_format' => 'id' ),
					array( 'key' => 'field_location_services', 'label' => 'Services Offered', 'name' => 'services_offered', 'type' => 'relationship', 'post_type' => array( 'service' ), 'return_format' => 'id' ),
					array( 'key' => 'field_location_address', 'label' => 'Address', 'name' => 'address', 'type' => 'textarea' ),
					array( 'key' => 'field_location_phone', 'label' => 'Phone', 'name' => 'phone', 'type' => 'text' ),
					array( 'key' => 'field_location_fax', 'label' => 'Fax', 'name' => 'fax', 'type' => 'text' ),
					array( 'key' => 'field_location_map_query', 'label' => 'Map Query', 'name' => 'map_query', 'type' => 'text' ),
					array( 'key' => 'field_location_directions_url', 'label' => 'Directions URL', 'name' => 'directions_url', 'type' => 'url' ),
					array(
						'key' => 'field_location_hours', 'label' => 'Hours', 'name' => 'hours', 'type' => 'repeater', 'sub_fields' => array(
							array( 'key' => 'field_location_hours_day', 'label' => 'Day', 'name' => 'day', 'type' => 'text' ),
							array( 'key' => 'field_location_hours_open', 'label' => 'Open', 'name' => 'open', 'type' => 'text' ),
							array( 'key' => 'field_location_hours_close', 'label' => 'Close', 'name' => 'close', 'type' => 'text' ),
						),
					),
				),
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'location' ) ) ),
			)
		);
	}

	public static function build_directions_url( string $query ): string {
		return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $query );
	}

	public static function ensure_directions_url( int $post_id, \WP_Post $post ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'location' !== $post->post_type ) {
			return;
		}
		$query      = (string) get_field( 'map_query', $post_id );
		$address    = (string) get_field( 'address', $post_id );
		$directions = (string) get_field( 'directions_url', $post_id );
		if ( '' === $directions ) {
			$destination = '' !== $query ? $query : $address;
			if ( '' !== $destination ) {
				update_field( 'directions_url', self::build_directions_url( $destination ), $post_id );
			}
		}
	}

	public static function register_shortcodes(): void {
		add_shortcode( 'carbon_location_gallery', array( __CLASS__, 'shortcode_location_gallery' ) );
		add_shortcode( 'carbon_location_services', array( __CLASS__, 'shortcode_location_services' ) );
		add_shortcode( 'carbon_location_map', array( __CLASS__, 'shortcode_location_map' ) );
		add_shortcode( 'carbon_appointment_form', static fn() => '<form class="carbon-form"><p><label>Name <input type="text" required></label></p><p><label>Email <input type="email" required></label></p><p><label>Phone <input type="tel"></label></p><p><button type="submit">Request Appointment</button></p></form>' );
		add_shortcode( 'carbon_locations_grid', array( __CLASS__, 'shortcode_locations_grid' ) );
		add_shortcode( 'carbon_services_grid', array( __CLASS__, 'shortcode_services_grid' ) );
		add_shortcode( 'carbon_testimonials', static fn() => '<div class="carbon-testimonials"><p>“Staff was incredibly supportive and helped me get back to full activity.”</p></div>' );
		add_shortcode( 'carbon_quick_actions', static fn() => '<div class="carbon-quick-actions"><a href="/appointment-request/">Request Appointment</a> | <a href="tel:+19403873700">Call Now</a></div>' );
	}

	public static function shortcode_location_gallery(): string {
		$ids = (array) get_field( 'location_photos', get_the_ID() );
		if ( empty( $ids ) ) {
			return '';
		}
		$out = '<div class="carbon-gallery" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">';
		foreach ( $ids as $id ) {
			$out .= wp_get_attachment_image( (int) $id, 'large', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:auto;border-radius:8px;' ) );
		}
		$out .= '</div>';
		return $out;
	}

	public static function shortcode_location_services(): string {
		$services = (array) get_field( 'services_offered', get_the_ID() );
		if ( empty( $services ) ) {
			return '';
		}
		$out = '<ul class="carbon-location-services">';
		foreach ( $services as $service_id ) {
			$out .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $service_id ) ), esc_html( get_the_title( $service_id ) ) );
		}
		$out .= '</ul>';
		return $out;
	}

	public static function shortcode_location_map( array $atts ): string {
		$atts       = shortcode_atts(
			array(
				'show_directions_button' => '1',
				'directions_label'       => 'Get Directions',
			),
			$atts,
			'carbon_location_map'
		);
		$query      = (string) get_field( 'map_query', get_the_ID() );
		$address    = (string) get_field( 'address', get_the_ID() );
		$target     = '' !== $query ? $query : $address;
		$directions = (string) get_field( 'directions_url', get_the_ID() );
		if ( '' === $directions && '' !== $target ) {
			$directions = self::build_directions_url( $target );
		}
		$map_src = 'https://www.google.com/maps?q=' . rawurlencode( $target ) . '&output=embed';
		$out     = '<div class="carbon-map-wrap" style="position:relative;width:100%;aspect-ratio:16 / 9;"><iframe loading="lazy" src="' . esc_url( $map_src ) . '" style="border:0;width:100%;height:100%;" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
		if ( '1' === (string) $atts['show_directions_button'] && '' !== $directions ) {
			$out .= '<p><a class="carbon-directions-button" href="' . esc_url( $directions ) . '" target="_blank" rel="noopener">' . esc_html( $atts['directions_label'] ) . '</a></p>';
		}
		return $out;
	}

	public static function shortcode_locations_grid(): string {
		$posts = get_posts( array( 'post_type' => 'location', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
		$out   = '<div class="carbon-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">';
		foreach ( $posts as $post ) {
			$address = (string) get_field( 'address', $post->ID );
			$phone   = (string) get_field( 'phone', $post->ID );
			$dir     = (string) get_field( 'directions_url', $post->ID );
			$out    .= '<article><h3><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( $post->post_title ) . '</a></h3><p>' . esc_html( $address ) . '</p><p><a href="tel:' . esc_attr( preg_replace( '/\D+/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a></p><p><a href="' . esc_url( $dir ) . '" target="_blank" rel="noopener">Directions</a></p></article>';
		}
		$out .= '</div>';
		return $out;
	}

	public static function shortcode_services_grid(): string {
		$posts = get_posts( array( 'post_type' => 'service', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		$out   = '<div class="carbon-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">';
		foreach ( $posts as $post ) {
			$out .= '<article><h3><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( $post->post_title ) . '</a></h3></article>';
		}
		$out .= '</div>';
		return $out;
	}

	public static function cli_seed( array $args, array $assoc_args ): void {
		$run_all = isset( $assoc_args['all'] );
		if ( $run_all || isset( $assoc_args['services'] ) ) {
			self::seed_services();
		}
		if ( $run_all || isset( $assoc_args['locations'] ) ) {
			self::seed_locations();
		}
		if ( $run_all || isset( $assoc_args['pages'] ) ) {
			self::seed_pages();
		}
		if ( $run_all || isset( $assoc_args['menus'] ) ) {
			self::seed_menu();
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::success( 'Carbon seed complete.' );
		}
	}

	private static function upsert_post( string $post_type, string $slug, string $title, string $content = '' ): int {
		$existing = get_page_by_path( $slug, OBJECT, $post_type );
		$data     = array(
			'post_type'    => $post_type,
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_content' => $content,
		);
		if ( $existing ) {
			$data['ID'] = $existing->ID;
			return (int) wp_update_post( $data );
		}
		return (int) wp_insert_post( $data );
	}

	public static function seed_services(): void {
		foreach ( self::$service_seed as $index => $service ) {
			$slug = sanitize_title( $service );
			$id   = self::upsert_post( 'service', $slug, $service, 'Placeholder content for ' . $service . '.' );
			update_field( 'hero_title', $service, $id );
			update_field( 'seo_summary', $service . ' at Carbon PT.', $id );
			update_field( 'featured_on_home', $index < 6 ? 1 : 0, $id );
		}
	}

	public static function seed_locations(): void {
		$service_ids = get_posts( array( 'post_type' => 'service', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'title', 'order' => 'ASC' ) );
		$media_ids   = self::import_media();
		$offset      = 0;
		foreach ( self::$location_seed as $slug => $location ) {
			$id = self::upsert_post( 'location', $slug, $location['title'], 'Location page placeholder.' );
			update_field( 'location_intro', '<p>Visit our ' . esc_html( $location['title'] ) . ' clinic for personalized care.</p>', $id );
			update_field( 'address', $location['address'], $id );
			update_field( 'phone', $location['phone'], $id );
			update_field( 'fax', $location['fax'], $id );
			update_field( 'map_query', $location['address'], $id );
			update_field( 'directions_url', self::build_directions_url( $location['address'] ), $id );
			if ( ! empty( $media_ids ) ) {
				$location_media = array_slice( $media_ids, 0, 3 );
				update_field( 'location_photos', $location_media, $id );
			}
			if ( ! empty( $service_ids ) ) {
				$picked = array();
				for ( $i = 0; $i < 10; $i++ ) {
					$picked[] = $service_ids[ ( $offset + $i ) % count( $service_ids ) ];
				}
				$offset += 3;
				update_field( 'services_offered', $picked, $id );
			}
		}
	}

	private static function import_media(): array {
		$paths = glob( trailingslashit( ABSPATH ) . '../ops/media/*.svg' ) ?: array();
		if ( false === $paths ) {
			return array();
		}
		$ids = array();
		foreach ( $paths as $path ) {
			$name     = basename( $path );
			$existing = get_posts(
				array(
					'post_type'  => 'attachment',
					'name'       => sanitize_title( pathinfo( $name, PATHINFO_FILENAME ) ),
					'numberposts'=> 1,
					'fields'     => 'ids',
				)
			);
			if ( ! empty( $existing ) ) {
				$ids[] = (int) $existing[0];
				continue;
			}
			$filetype = wp_check_filetype( $name, null );
			$upload   = wp_upload_bits( $name, null, file_get_contents( $path ) );
			if ( ! empty( $upload['error'] ) ) {
				continue;
			}
			$attach_id = wp_insert_attachment(
				array(
					'guid'           => $upload['url'],
					'post_mime_type' => $filetype['type'],
					'post_title'     => sanitize_text_field( pathinfo( $name, PATHINFO_FILENAME ) ),
					'post_status'    => 'inherit',
				),
				$upload['file']
			);
			if ( ! is_wp_error( $attach_id ) ) {
				$ids[] = (int) $attach_id;
			}
		}
		return $ids;
	}

	public static function seed_pages(): void {
		$pages = array(
			'home'                => array( 'title' => 'Home', 'content' => '<p>Hero + CTA to appointment and call now.</p>[carbon_appointment_form][carbon_services_grid][carbon_locations_grid]<p><a href="/appointment-request/">Request Appointment</a> / <a href="tel:+19403873700">Call</a> / <a href="/location/">Directions</a></p>' ),
			'about'               => array( 'title' => 'About', 'content' => '<p>About placeholder content.</p>' ),
			'blog'                => array( 'title' => 'Blog', 'content' => '<p>Blog landing placeholder.</p>' ),
			'contact'             => array( 'title' => 'Contact', 'content' => '<p>Reach any of our clinics below.</p>[carbon_locations_grid]' ),
			'appointment-request' => array( 'title' => 'Appointment Request', 'content' => '<p>Complete the form and expect a call within 1 business day.</p>[carbon_appointment_form]' ),
			'patient-orientation' => array( 'title' => 'Patient Orientation', 'content' => '<p>Orientation placeholder.</p>' ),
			'insurance'           => array( 'title' => 'Insurance', 'content' => '<p>Insurance placeholder.</p>' ),
			'faq'                 => array( 'title' => 'FAQ', 'content' => '<p>FAQ placeholder.</p>' ),
			'testimonials'        => array( 'title' => 'Testimonials', 'content' => '[carbon_testimonials]' ),
		);
		$page_ids = array();
		foreach ( $pages as $slug => $page ) {
			$page_ids[ $slug ] = self::upsert_post( 'page', $slug, $page['title'], $page['content'] );
		}
		if ( isset( $page_ids['home'] ) ) {
			update_option( 'page_on_front', $page_ids['home'] );
			update_option( 'show_on_front', 'page' );
		}
		if ( isset( $page_ids['blog'] ) ) {
			update_option( 'page_for_posts', $page_ids['blog'] );
		}
	}

	public static function seed_menu(): void {
		$menu_name = 'Primary';
		$menu      = wp_get_nav_menu_object( $menu_name );
		$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );
		$items     = array(
			'Home'      => home_url( '/' ),
			'About'     => home_url( '/about/' ),
			'Services'  => home_url( '/service/' ),
			'Locations' => home_url( '/location/' ),
			'Blog'      => home_url( '/blog/' ),
			'Contact'   => home_url( '/contact/' ),
		);
		$existing = wp_get_nav_menu_items( $menu_id );
		$titles   = array_map( static fn( $item ) => $item->title, is_array( $existing ) ? $existing : array() );
		foreach ( $items as $title => $url ) {
			if ( in_array( $title, $titles, true ) ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-status' => 'publish' ) );
		}
		$locations                     = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary']          = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	public static function register_admin_page(): void {
		add_management_page( 'Carbon Setup', 'Carbon Setup', 'manage_options', 'carbon-setup', array( __CLASS__, 'render_admin_page' ) );
	}

	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$nonce = wp_create_nonce( 'carbon_seed' );
		echo '<div class="wrap"><h1>Carbon Setup</h1><p>Run idempotent seed tasks.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="carbon_run_seed" /><input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '" />';
		echo '<p><label><input type="checkbox" name="seed[]" value="pages" checked> Pages</label></p>';
		echo '<p><label><input type="checkbox" name="seed[]" value="menus" checked> Menu</label></p>';
		echo '<p><label><input type="checkbox" name="seed[]" value="services" checked> Services</label></p>';
		echo '<p><label><input type="checkbox" name="seed[]" value="locations" checked> Locations</label></p>';
		submit_button( 'Run Carbon Seed' );
		echo '</form><p><strong>Note:</strong> Do not create Pages with slug <code>service</code> or <code>location</code>.</p></div>';
	}

	public static function handle_admin_seed(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized.' );
		}
		check_admin_referer( 'carbon_seed' );
		$seed = isset( $_POST['seed'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['seed'] ) ) : array();
		if ( in_array( 'services', $seed, true ) ) {
			self::seed_services();
		}
		if ( in_array( 'locations', $seed, true ) ) {
			self::seed_locations();
		}
		if ( in_array( 'pages', $seed, true ) ) {
			self::seed_pages();
		}
		if ( in_array( 'menus', $seed, true ) ) {
			self::seed_menu();
		}
		wp_safe_redirect( admin_url( 'tools.php?page=carbon-setup&seeded=1' ) );
		exit;
	}
}
