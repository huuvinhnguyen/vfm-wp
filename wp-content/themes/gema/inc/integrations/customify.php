<?php
/**
 * Gema Customizer Options Config
 *
 * @package Gema
 * @since 1.0.0
 */

/**
 * Hook into the Customify's fields and settings.
 *
 * The config can turn to be complex so is better to visit:
 * https://github.com/pixelgrade/customify
 *
 * @param $options array - Contains the plugin's options array right before they are used, so edit with care
 *
 * @return mixed The return of options is required, if you don't need options return an empty array
 *
 */

/* =============
 * For customizing the components Customify options you need to use the /inc/components.php file.
 * Also there you will find the example code for making changes.
 * ============= */

add_filter( 'customify_filter_fields', 'gema_add_customify_options', 10, 1 );
add_filter( 'customify_filter_fields', 'gema_add_customify_presets_options', 20, 1 );
add_filter( 'customify_filter_fields', 'gema_add_customify_blog_grid_options', 30, 1 );
add_filter( 'customify_filter_fields', 'gema_add_customify_header_options', 40, 1 );
add_filter( 'customify_filter_fields', 'gema_add_customify_main_content_options', 50, 1 );
add_filter( 'customify_filter_fields', 'gema_add_customify_footer_options', 60, 1 );
add_filter( 'customify_filter_fields', 'pixelgrade_add_customify_style_manager_section', 12, 1 );

function gema_add_customify_options( $options ) {
	$options['opt-name'] = 'gema_options';

	$options['sections'] = array();

	return $options;
}

define( 'SM_COLOR_PRIMARY', '#e03a3a' );
define( 'SM_COLOR_SECONDARY', '#f75034' );
define( 'SM_COLOR_TERTIARY', '#ad2d2d' );

define( 'SM_DARK_PRIMARY', '#000000' );
define( 'SM_DARK_SECONDARY', '#000000' );
define( 'SM_DARK_TERTIARY', '#a3a3a1' );

define( 'SM_LIGHT_PRIMARY', '#ffffff' );
define( 'SM_LIGHT_SECONDARY', '#f7f5f5' );
define( 'SM_LIGHT_TERTIARY', '#f7f2f2' );

/**
 * Add the Style Manager cross-theme Customizer section.
 *
 * @param array $options
 *
 * @return array
 */
function pixelgrade_add_customify_style_manager_section( $options ) {
	// If the theme hasn't declared support for style manager, bail.
	if ( ! current_theme_supports( 'customizer_style_manager' ) ) {
		return $options;
	}


	if ( ! isset( $options['sections']['style_manager_section'] ) ) {
		$options['sections']['style_manager_section'] = array();
	}

	// The section might be already defined, thus we merge, not replace the entire section config.
	$options['sections']['style_manager_section'] = array_replace_recursive( $options['sections']['style_manager_section'], array(
		'options' => array(

			// Font Palettes Assignment.
			'sm_font_palette'    => array(
				'default' => 'gema',
			),
			'sm_font_primary' => array(
				'connected_fields' => array(
					'header_site_title_font',
					'blog_item_title_font',
					'main_content_page_title_font',
					'main_content_heading_1_font',
					'main_content_heading_2_font',
					'main_content_heading_3_font',
					'blog_item_meta_primary_font',
				),
			),
			'sm_font_secondary' => array(
				'connected_fields' => array(
					'header_page_title_font2',
					'footer_body_font',
					'main_content_quote_block_font',
					'main_content_heading_4_font',
					'main_content_heading_5_font',
					'main_content_heading_6_font',
					'blog_item_meta_secondary_font',
				),
			),
			'sm_font_body' => array(
				'connected_fields' => array(
					'main_content_body_text_font',
				),
			),

			// Color Palettes Assignment.
			'sm_color_primary' => array(
				'default' => SM_COLOR_PRIMARY,
			),
			'sm_color_secondary' => array(
				'default' => SM_COLOR_SECONDARY,
			),
			'sm_color_tertiary' => array(
				'default' => SM_COLOR_TERTIARY,
			),
			'sm_dark_primary' => array(
				'default' => SM_DARK_PRIMARY,
				'connected_fields' => array(
					// medium
					'main_content_page_title_color',
					'header_links_active_color',

					// high
					'blog_item_title_color',
					'pxg_nothing1',

					// striking
					'header_navigation_links_color',
					'pxg_nothing2',

					// always dark
					'pxg_nothing3',
					'pxg_nothing4',
				),
			),
			'sm_dark_secondary' => array(
				'default' => SM_DARK_SECONDARY,
				'connected_fields' => array(
					// medium
					'footer_body_text_color',
					'main_content_heading_1_color',

					// high
					'main_content_heading_2_color',
					'main_content_heading_3_color',

					// striking
					'main_content_heading_4_color',
					'main_content_heading_5_color',

					// always dark
					'main_content_heading_6_color',
					'main_content_body_text_color',
				),
			),
			'sm_dark_tertiary' => array(
				'default' => SM_DARK_TERTIARY,
				'connected_fields' => array(
					// medium
					'main_content_body_link_color',

					// high
					'blog_item_meta_secondary_color',
					'main_content_body_link_active_color',

					// striking
					'footer_links_color',
					'blog_item_meta_primary_color',
				),
			),
			'sm_light_primary' => array(
				'default' => SM_LIGHT_PRIMARY,
				'connected_fields' => array(
					'main_content_background_color',
					'blog_item_background_color',
					'footer_background',
				),
			),
			'sm_light_secondary' => array(
				'connected_fields' => array(
					'main_content_border_color',
				),
				'default' => SM_LIGHT_SECONDARY,
			),
			'sm_light_tertiary' => array(
				'default' => SM_LIGHT_TERTIARY,
			),
		),
	) );

	return $options;
}

function gema_add_customify_presets_options( $options ) {

	$presets_section = array(
		'presets_section' => array(
			'title'   => esc_html__( 'Style Presets', 'gema' ),
			'options' => array(
				'theme_style' => array(
					'type'         => 'preset',
					'label'        => esc_html__( 'Select a style:', 'gema' ),
					'desc'         => esc_html__( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', 'gema' ),
					'default'      => 'gema_default',
					'choices_type' => 'awesome',
					'choices'      => array(
						'gema_default' => array(
							'label'   => esc_html__( 'Gema', 'gema' ),
							'preview' => array(
								'color-text'       => '#ffffff',
								'background-card'  => '#000000',
								'background-label' => '#ffffff',
								'font-main'        => 'Butler',
								'font-alt'         => 'Montserrat',
							),
							'options' => array(
//								'accent_color'     => '#A3A3A1',
								'header_navigation_links_color' => '#000000',
								'header_links_active_color' => '#000000',
								'blog_item_title_color' => '#000000',
								'blog_item_meta_primary_color' => '#A3A3A1',
								'blog_item_meta_secondary_color' => '#A3A3A1',
								'main_content_page_title_color' => '#000000',
								'main_content_body_text_color' => '#000000',
								'main_content_body_link_color' => '#A3A3A1',
								'main_content_body_link_active_color' => '#A3A3A1',
								'main_content_heading_1_color' => '#000000',
								'main_content_heading_2_color' => '#000000',
								'main_content_heading_3_color' => '#000000',
								'main_content_heading_4_color' => '#000000',
								'main_content_heading_5_color' => '#000000',
								'main_content_heading_6_color' => '#000000',
								'header_site_title_font' => 'Butler',
								'header_page_title_font2' => 'Montserrat',
								'blog_item_title_font' => 'Montserrat',
								'blog_item_meta_primary_font' => 'Montserrat',
								'blog_item_meta_secondary_font' => 'Montserrat',
								'main_content_page_title_font' => 'Montserrat',
								'main_content_body_text_font' => 'Montserrat',
								'main_content_quote_block_font' => 'Montserrat',
								'main_content_heading_1_font' => 'Montserrat',
								'main_content_heading_2_font' => 'Montserrat',
								'main_content_heading_3_font' => 'Montserrat',
								'main_content_heading_4_font' => 'Montserrat',
								'main_content_heading_5_font' => 'Montserrat',
								'main_content_heading_6_font' => 'Montserrat',
								'main_content_background_color' => '#FFFFFF',
								'blog_item_background_color' => '#FFFFFF',
								'footer_body_text_color' => '#000000',
								'footer_links_color' => '#A3A3A1',
								'footer_background' => '#FFFFFF'
							)
						),
						'royal'        => array(
							'label'   => esc_html__( 'Royal navy', 'gema' ),
							'preview' => array(
								'color-text'       => '#f0f0f0',
								'background-card'  => '#001b4f',
								'background-label' => '#360B0B',
								'font-main'        => 'Vidaloka',
								'font-alt'         => 'PT Sans',
							),
							'options' => array(
//								'accent_color'     => '#9e9e9e',
								'header_navigation_links_color' => '#15213f',
								'header_links_active_color' => '#15213f',
								'blog_item_title_color' => '#15213f',
								'blog_item_meta_primary_color' => '#9e9e9e',
								'blog_item_meta_secondary_color' => '#9e9e9e',
								'main_content_page_title_color' => '#15213f',
								'main_content_body_text_color' => '#15213f',
								'main_content_body_link_color' => '#9e9e9e',
								'main_content_body_link_active_color' => '#9e9e9e',
								'main_content_heading_1_color' => '#001b4f',
								'main_content_heading_2_color' => '#001b4f',
								'main_content_heading_3_color' => '#001b4f',
								'main_content_heading_4_color' => '#001b4f',
								'main_content_heading_5_color' => '#001b4f',
								'main_content_heading_6_color' => '#001b4f',
								'header_site_title_font' => 'Vidaloka',
								'header_page_title_font2' => 'PT Sans',
								'blog_item_title_font' => 'Vidaloka',
								'blog_item_meta_primary_font' => 'PT Sans',
								'blog_item_meta_secondary_font' => 'PT Sans',
								'main_content_page_title_font' => 'Vidaloka',
								'main_content_body_text_font' => 'PT Sans',
								'main_content_quote_block_font' => 'PT Sans',
								'main_content_heading_1_font' => 'Vidaloka',
								'main_content_heading_2_font' => 'Vidaloka',
								'main_content_heading_3_font' => 'Vidaloka',
								'main_content_heading_4_font' => 'Vidaloka',
								'main_content_heading_5_font' => 'Vidaloka',
								'main_content_heading_6_font' => 'Vidaloka',
								'main_content_background_color' => '#f0f0f0',
								'blog_item_background_color' => '#f0f0f0',
								'footer_body_text_color' => '#15213f',
								'footer_links_color' => '#9e9e9e',
								'footer_background' => '#f0f0f0',
							)
						),

						'caramel' => array(
							'label'   => esc_html__( 'Caramel', 'gema' ),
							'preview' => array(
								'color-text'       => '#F3E5D4',
								'background-card'  => '#6A0000',
								'background-label' => '#000000',
								'font-main'        => 'Playfair Display',
								'font-alt'         => 'Muli',
							),
							'options' => array(
//								'accent_color'     => '#ceb173',
								'header_navigation_links_color' => '#ceb173',
								'header_links_active_color' => '#ceb173',
								'blog_item_title_color' => '#ceb173',
								'blog_item_meta_primary_color' => '#ceb173',
								'blog_item_meta_secondary_color' => '#ceb173',
								'main_content_page_title_color' => '#F3E5D4',
								'main_content_body_text_color' => '#ceb173',
								'main_content_body_link_color' => '#ceb173',
								'main_content_body_link_active_color' => '#ceb173',
								'main_content_heading_1_color' => '#F3E5D4',
								'main_content_heading_2_color' => '#F3E5D4',
								'main_content_heading_3_color' => '#F3E5D4',
								'main_content_heading_4_color' => '#F3E5D4',
								'main_content_heading_5_color' => '#F3E5D4',
								'main_content_heading_6_color' => '#F3E5D4',
								'header_site_title_font' => 'Playfair Display',
								'header_page_title_font2' => 'Playfair Display',
								'blog_item_title_font' => 'Playfair Display',
								'blog_item_meta_primary_font' => 'Muli',
								'blog_item_meta_secondary_font' => 'Muli',
								'main_content_page_title_font' => 'Playfair Display',
								'main_content_body_text_font' => 'Muli',
								'main_content_quote_block_font' => 'Muli',
								'main_content_heading_1_font' => 'Playfair Display',
								'main_content_heading_2_font' => 'Playfair Display',
								'main_content_heading_3_font' => 'Playfair Display',
								'main_content_heading_4_font' => 'Playfair Display',
								'main_content_heading_5_font' => 'Playfair Display',
								'main_content_heading_6_font' => 'Playfair Display',
								'main_content_background_color' => '#6A0000',
								'blog_item_background_color' => '#6A0000',
								'footer_body_text_color' => '#ceb173',
								'footer_links_color' => '#ceb173',
								'footer_background' => '#6A0000',
							)
						),

						'typewritter' => array(
							'label'   => esc_html__( 'Typewritter', 'gema' ),
							'preview' => array(
								'color-text'       => '##ffebd6',
								'background-card'  => '#3c2a15',
								'background-label' => '#ffebd6',
								'font-main'        => 'Cutive Mono',
								'font-alt'         => 'Gentium Book Basic',
							),
							'options' => array(
//								'accent_color'     => '#dd7e2a',
								'header_navigation_links_color' => '#dd7e2a',
								'header_links_active_color' => '#dd7e2a',
								'blog_item_title_color' => '#dd7e2a',
								'blog_item_meta_primary_color' => '#dd7e2a',
								'blog_item_meta_secondary_color' => '#dd7e2a',
								'main_content_page_title_color' => '#3c2a15',
								'main_content_body_text_color' => '#3c2a15',
								'main_content_body_link_color' => '#dd7e2a',
								'main_content_body_link_active_color' => '#dd7e2a',
								'main_content_heading_1_color' => '#3c2a15',
								'main_content_heading_2_color' => '#3c2a15',
								'main_content_heading_3_color' => '#3c2a15',
								'main_content_heading_4_color' => '#3c2a15',
								'main_content_heading_5_color' => '#3c2a15',
								'main_content_heading_6_color' => '#3c2a15',
								'header_site_title_font' => 'Cutive Mono',
								'header_page_title_font2' => 'Cutive Mono',
								'blog_item_title_font' => 'Cutive Mono',
								'blog_item_meta_primary_font' => 'Gentium Book Basic',
								'blog_item_meta_secondary_font' => 'Gentium Book Basic',
								'main_content_page_title_font' => 'Cutive Mono',
								'main_content_body_text_font' => 'Gentium Book Basic',
								'main_content_quote_block_font' => 'Gentium Book Basic',
								'main_content_heading_1_font' => 'Cutive Mono',
								'main_content_heading_2_font' => 'Cutive Mono',
								'main_content_heading_3_font' => 'Cutive Mono',
								'main_content_heading_4_font' => 'Cutive Mono',
								'main_content_heading_5_font' => 'Cutive Mono',
								'main_content_heading_6_font' => 'Cutive Mono',
								'main_content_background_color' => '#ffebd6',
								'blog_item_background_color' => '#FFFFFF',
								'footer_body_text_color' => '#3c2a15',
								'footer_links_color' => '#dd7e2a',
								'footer_background' => '#ffebd6',
							)
						),

						'rubik' => array(
							'label'   => esc_html__( 'Rubik', 'gema' ),
							'preview' => array(
								'color-text'       => '#fff',
								'background-card'  => '#dd3333',
								'background-label' => '#fcd200',
								'font-main'        => 'Josefin Sans',
								'font-alt'         => 'Palanquin',
							),
							'options' => array(
//								'accent_color'     => '#fcd200',
								'header_navigation_links_color' => '#fcd200',
								'header_links_active_color' => '#fcd200',
								'blog_item_title_color' => '#fcd200',
								'blog_item_meta_primary_color' => '#fcd200',
								'blog_item_meta_secondary_color' => '#fcd200',
								'main_content_page_title_color' => '#dd3333',
								'main_content_body_text_color' => '#1e73be',
								'main_content_body_link_color' => '#fcd200',
								'main_content_body_link_active_color' => '#fcd200',
								'main_content_heading_1_color' => '#dd3333',
								'main_content_heading_2_color' => '#dd3333',
								'main_content_heading_3_color' => '#dd3333',
								'main_content_heading_4_color' => '#dd3333',
								'main_content_heading_5_color' => '#dd3333',
								'main_content_heading_6_color' => '#dd3333',
								'header_site_title_font' => 'Josefin Sans',
								'header_page_title_font2' => 'Josefin Sans',
								'blog_item_title_font' => 'Josefin Sans',
								'blog_item_meta_primary_font' => 'Palanquin',
								'blog_item_meta_secondary_font' => 'Palanquin',
								'main_content_page_title_font' => 'Josefin Sans',
								'main_content_body_text_font' => 'Palanquin',
								'main_content_quote_block_font' => 'Palanquin',
								'main_content_heading_1_font' => 'Josefin Sans',
								'main_content_heading_2_font' => 'Josefin Sans',
								'main_content_heading_3_font' => 'Josefin Sans',
								'main_content_heading_4_font' => 'Josefin Sans',
								'main_content_heading_5_font' => 'Josefin Sans',
								'main_content_heading_6_font' => 'Josefin Sans',
								'main_content_background_color' => '#FFFFFF',
								'blog_item_background_color' => '#FFFFFF',
								'footer_body_text_color' => '#1e73be',
								'footer_links_color' => '#fcd200',
								'footer_background' => '#FFFFFF',
							)
						),

						'montpierre' => array(
							'label'   => esc_html__( 'Montpierre', 'gema' ),
							'preview' => array(
								'color-text'       => '#ffffff',
								'background-card'  => '#1a4940',
								'background-label' => '#faffe8',
								'font-main'        => 'Josefin Slab',
								'font-alt'         => 'Montserrat',
							),
							'options' => array(
//								'accent_color'     => '#b5a6a9',
								'header_navigation_links_color' => '#b5a6a9',
								'header_links_active_color' => '#b5a6a9',
								'blog_item_title_color' => '#b5a6a9',
								'blog_item_meta_primary_color' => '#b5a6a9',
								'blog_item_meta_secondary_color' => '#b5a6a9',
								'main_content_page_title_color' => '#faffe8',
								'main_content_body_text_color' => '#ffffff',
								'main_content_body_link_color' => '#b5a6a9',
								'main_content_body_link_active_color' => '#b5a6a9',
								'main_content_heading_1_color' => '#faffe8',
								'main_content_heading_2_color' => '#faffe8',
								'main_content_heading_3_color' => '#faffe8',
								'main_content_heading_4_color' => '#faffe8',
								'main_content_heading_5_color' => '#faffe8',
								'main_content_heading_6_color' => '#faffe8',
								'header_site_title_font' => 'Josefin Slab',
								'header_page_title_font2' => 'Josefin Slab',
								'blog_item_title_font' => 'Josefin Slab',
								'blog_item_meta_primary_font' => 'Montserrat',
								'blog_item_meta_secondary_font' => 'Montserrat',
								'main_content_page_title_font' => 'Josefin Slab',
								'main_content_body_text_font' => 'Montserrat',
								'main_content_quote_block_font' => 'Montserrat',
								'main_content_heading_1_font' => 'Josefin Slab',
								'main_content_heading_2_font' => 'Josefin Slab',
								'main_content_heading_3_font' => 'Josefin Slab',
								'main_content_heading_4_font' => 'Josefin Slab',
								'main_content_heading_5_font' => 'Josefin Slab',
								'main_content_heading_6_font' => 'Josefin Slab',
								'main_content_background_color' => '#1a4940',
								'blog_item_background_color' => '#1a4940',
								'footer_body_text_color' => '#ffffff',
								'footer_links_color' => '#b5a6a9',
								'footer_background' => '#1a4940',
							)
						),

					)
				),
			)
		),
	);

	$presets_section = apply_filters( 'pixelgrade_customify_presets_section_options', $presets_section, $options );

	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	$options['sections'] = $options['sections'] + $presets_section;

	return $options;
}

function gema_add_customify_theme_options( $options ) {

	$theme_options_section = array(
		'gema_theme_options' => array(
			'title'    => esc_html__( 'Gema Options', 'gema' ),
			'priority' => 30,
			'options'  => array(
				'gema_footer_copyright' => array(
					'type'              => 'textarea',
					'label'             => esc_html__( 'Additional Copyright Text', 'gema' ),
					'default'           => '© ' . get_bloginfo( 'name' ) . ' –',
					'sanitize_callback' => 'wp_kses_post',
					'live'              => array( '.site-info .site-info-additional-text' )
				)
			)
		),
	);

	$theme_options_section = apply_filters( 'pixelgrade_customify_theme_options_section_options', $theme_options_section, $options );

	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	$options['sections'] = $options['sections'] + $theme_options_section;

	return $options;
}

function gema_add_customify_header_options( $options ) {

	$recommended_body_fonts = apply_filters( 'customify_theme_recommended_body_fonts', array() );
	$recommended_site_title_fonts = apply_filters( 'customify_theme_recommended_site_title_fonts', array() );

	$header_section = array(
		// Header
		'header_section' => array(
			'title'   => esc_html__( 'Header', 'gema' ),
			'options' => array(
				'header_options_customizer_tabs'        => array(
					'type' => 'html',
					'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-header-layout">' . esc_html__( 'Layout', 'gema' ) . '</a>
							<a href="#section-title-header-colors">' . esc_html__( 'Colors', 'gema' ) . '</a>
							<a href="#section-title-header-fonts">' . esc_html__( 'Fonts', 'gema' ) . '</a>
							</nav>',
				),
				// [Section] Layout
				'header_title_layout_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-header-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'gema' ) . '</span>',
				),
				'header_logo_height'              => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Logo Height', 'gema' ),
					'desc'        => esc_html__( 'Adjust the max height of your logo container.', 'gema' ),
					'live'        => true,
					'default'     => 190,
					'input_attrs' => array(
						'min'          => 20,
						'max'          => 200,
						'step'         => 1,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property' => 'max-height',
							'selector' => '.site-logo img, .custom-logo-link img',
							'unit'     => 'px',
						),
						array(
							'property' => 'font-size',
							'selector' => '.site-title',
							'unit'     => 'px',
						),
					),
				),
				'search_button' => array(
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Hide search button in Social Menu.', 'patch' ),
					'default' => false,
				),

				// [Section] COLORS
				'header_title_colors_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-header-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'gema' ) . '</span>',
				),
				'header_navigation_links_color' => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Navigation Links Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_PRIMARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.main-navigation li a, .main-navigation li:after',
						),
						array(
							'property' => 'background-color',
							'selector' => '.nav-menu ul li.hover > a',
							'media'    => 'only screen and (min-width: 900px)',
						),

						array(
							'property' => 'border-color',
							'selector' => '.nav-menu ul',
							'media'    => 'only screen and (min-width: 900px)',
						),
					),
				),
				'header_links_active_color'     => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Links Active Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_PRIMARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '
								.main-navigation .nav-menu > li[class*="current-menu"] > a, 
								.main-navigation .nav-menu > li[class*="current-menu"]:after,
								.jetpack-social-navigation li[class*="current-menu"] > a',
						),
						array(
							'property' => 'border-bottom-color',
							'selector' => '.main-navigation.main-navigation .nav-menu > li > a:after',
						),
					),
				),

				// [Section] FONTS
				'header_title_fonts_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-header-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', 'gema' ) . '</span>',
				),

				'header_site_title_font' => array(
					'type'     			=> 'font',
					'label'            => esc_html__( 'Site Title', 'gema' ),
					'desc'             => esc_html__( '', 'gema' ),
					'selector'         => '.site-title',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Butler',
						'text-transform' => 'none',
						'font-weight'    => '900'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_site_title_fonts,
					// Sub Fields Configuration (optional)
					'fields'   => array(
						'text-transform'  => true,
					)
				),

				'header_page_title_font2' => array(
					'type'     			=> 'font',
					'label'            => esc_html__( 'Navigation Text', 'gema' ),
					'desc'             => esc_html__( '', 'gema' ),
					'selector'         => '.main-navigation',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,
					// Sub Fields Configuration (optional)
					'fields'   => array(
						'text-transform'  => true,
					)
				),
			),
		),
	);

	//Allow others to make changes
	$blog_grid_section = apply_filters( 'pixelgrade_customify_header_section_options', $header_section, $options );

	//make sure we are in good working order
	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	//append the blog grid section
	$options['sections'] = $options['sections'] + $header_section;

	return $options;
}

function gema_add_customify_blog_grid_options( $options ) {
	// Body
	$recommended_body_fonts = apply_filters( 'customify_theme_recommended_body_fonts', array(
		// here we can have custom fonts keys for this Section
	) );

	$blog_grid_section = array(
		// Blog Grid
		'blog_grid' => array(
			'title'   => esc_html__( 'Blog Grid Items', 'gema' ),
			'options' => array(
				'blog_grid_options_customizer_tabs'          => array(
					'type' => 'html',
					'html' => '<nav class="section-navigation  js-section-navigation">
								<a href="#section-title-blog-layout">' . esc_html__( 'Layout', 'gema' ) . '</a>
								<a href="#section-title-blog-colors">' . esc_html__( 'Colors', 'gema' ) . '</a>
								<a href="#section-title-blog-fonts">' . esc_html__( 'Fonts', 'gema' ) . '</a>
								</nav>',
				),
				// [Section] Layout
				'blog_grid_title_layout_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'gema' ) . '</span>',
				),
				'blog_grid_width'                     => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Blog Grid Max Width', 'gema' ),
					'desc'        => esc_html__( 'Adjust the max width of the blog area.', 'gema' ),
					'live'        => true,
					'default'     => 1980,
					'input_attrs' => array(
						'min'          => 500,
						'max'          => 2600,
						'step'         => 10,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property' => 'max-width',
							'media'    => 'only screen and (min-width: 1650px)',
							'selector' => '.u-blog-grid-width[class]',
							'unit'     => 'px',
						),
					),
				),
				'blog_container_sides_spacing'        => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Container Sides Spacing', 'gema' ),
					'desc'        => esc_html__( 'Adjust the space separating the site content and the sides of the browser.', 'gema' ),
					'live'        => true,
					'default'     => 36,
					'input_attrs' => array(
						'min'          => 0,
						'max'          => 140,
						'step'         => 10,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property'        => 'padding-left',
							'selector'        => '.u-blog-sides-spacing',
							'callback_filter' => 'typeline_spacing_cb',
							'unit'            => 'px',
						),
						array(
							'property'        => 'padding-right',
							'selector'        => '.u-blog-sides-spacing',
							'callback_filter' => 'typeline_spacing_cb',
							'unit'            => 'px',
						),
					),
				),
				'blog_grid_title_items_grid_section'             => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Items Grid', 'gema' ) . '</span>',
				),
				'blog_items_per_row'                  => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Items per Row', 'gema' ),
					'desc'        => esc_html__( 'Set the desktop-based number of columns you want and we automatically make it right for other screen sizes.', 'gema' ),
					'live'        => false,
					'default'     => 4,
					'input_attrs' => array(
						'min'  => 2,
						'max'  => 4,
						'step' => 1,
					),
				),

				// [Sub Section] Items Meta
				'blog_grid_title_items_meta_section'          => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Items Meta', 'gema' ) . '</span>',
				),

				'blog_items_primary_meta' => array(
					'type'    => 'select',
					'label'   => esc_html__( 'Primary Meta Section', 'gema' ),
					'desc'    => esc_html__( 'Set the meta info that display around the title. ', 'gema' ),
					'default' => 'category',
					'choices' => array(
						'none'     => esc_html__( 'None', 'gema' ),
						'category' => esc_html__( 'Category + Author', 'gema' ),
						'date'     => esc_html__( 'Date', 'gema' ),
						'tags'     => esc_html__( 'Tags', 'gema' ),
						'comments' => esc_html__( 'Comments', 'gema' ),
					),
				),

				'blog_items_secondary_meta'         => array(
					'type'    => 'select',
					'label'   => esc_html__( 'Secondary Meta Section', 'gema' ),
					'desc'    => esc_html__( '', 'gema' ),
					'default' => 'date',
					'choices' => array(
						'none'     => esc_html__( 'None', 'gema' ),
						'category' => esc_html__( 'Category + Author', 'gema' ),
						'date'     => esc_html__( 'Date', 'gema' ),
						'tags'     => esc_html__( 'Tags', 'gema' ),
						'comments' => esc_html__( 'Comments', 'gema' ),
					),
				),

				// [Section] COLORS
				'blog_grid_title_colors_section'        => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'gema' ) . '</span>',
				),
				'blog_item_title_color'             => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Item Title Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_PRIMARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.card h2',
						),
					),
				),
				'blog_item_meta_primary_color'      => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Meta Primary', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_TERTIARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.card__meta-primary',
						),
					),
				),
				'blog_item_meta_secondary_color'    => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Meta Secondary', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_TERTIARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.card__meta-secondary',
						),
					),
				),
				// [Sub Section] Headings Color
				'blog_items_backgrounds_section' => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Backgrounds', 'gema' ) . '</span>',
				),
				'blog_item_background_color' => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Card Background Color', 'gema' ),
					'live'    => true,
					'default' => SM_LIGHT_PRIMARY,
					'css'     => array(
						array(
							'property' => 'background-color',
							'selector' => '
							#sb_instagram #sbi_images .sbi_photo,
							.content-quote:before,
							.singular .entry-header, 
							.attachment .entry-header,
							.widget,
							.card--text .card__wrap,
							.card--text .card__wrap:before,
							.card__image,
							.card__title,
							.sticky.card--text .card__wrap:before,
                            .jetpack_subscription_widget.widget:before,
                            .widget_blog_subscription.widget:before,
                            .card--text .card__meta,
                            input[type="text"],
							input[type="password"],
							input[type="datetime"],
							input[type="datetime-local"],
							input[type="date"],
							input[type="month"],
							input[type="time"],
							input[type="week"],
							input[type="number"],
							input[type="email"],
							input[type="url"],
							input[type="search"],
							input[type="tel"],
							input[type="color"],
							select,
							textarea',
						),
						array(
							'property'        => 'box-shadow',
							'selector'        => '.sticky .card__title',
							'callback_filter' => 'gema_sticky_boxshadow'
						)
					),
				),

				// [Section] FONTS
				'blog_grid_title_fonts_section'          => array(
					'type' => 'html',
					'html' => '<span id="section-title-blog-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', 'gema' ) . '</span>',
				),

				'blog_item_title_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Item Title Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.card h2',
//					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 20,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'blog_item_meta_primary_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Item Meta Primary Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.card__meta-primary, .card__meta-primary a',
//					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 10,
						'line-height'    => 1.625,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),
				'blog_item_meta_secondary_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Item Meta Secondary Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.card__meta-secondary, .card__meta-secondary a',
//					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 10,
						'line-height'    => 1.625,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),
			),
		),
	);

	//Allow others to make changes
	$blog_grid_section = apply_filters( 'pixelgrade_customify_blog_grid_section_options', $blog_grid_section, $options );

	//make sure we are in good working order
	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	//append the blog grid section
	$options['sections'] = $options['sections'] + $blog_grid_section;

	return $options;
}

function gema_add_customify_main_content_options( $options ) {

	$recommended_body_fonts = apply_filters( 'customify_theme_recommended_body_fonts', array() );

	$main_content_section = array(
		'main_content' => array(
			'title'   => esc_html__( 'Main Content', 'gema' ),
			'options' => array(
				'main_content_options_customizer_tabs' => array(
					'type' => 'html',
					'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-main-layout">' . esc_html__( 'Layout', 'gema' ) . '</a>
							<a href="#section-title-main-colors">' . esc_html__( 'Colors', 'gema' ) . '</a>
							<a href="#section-title-main-fonts">' . esc_html__( 'Fonts', 'gema' ) . '</a>
							</nav>',
				),
				// [Section] Layout
				'main_content_title_layout_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-main-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'gema' ) . '</span>',
				),
				'main_content_container_width'         => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Site Container Max Width', 'gema' ),
					'desc'        => esc_html__( 'Adjust the max width of your site content area.', 'gema' ),
					'live'        => true,
					'default'     => 1650,
					'input_attrs' => array(
						'min'          => 1000,
						'max'          => 2600,
						'step'         => 10,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property' => 'max-width',
							'selector' => '.u-container-width',
							'unit'     => 'px',
						),
					),
				),
				'main_content_content_width'           => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Content Width', 'gema' ),
					'desc'        => esc_html__( 'Decrease the width of your content to create an inset area for your text. The inset size will be the space between Site Container and Content.', 'gema' ),
					'live'        => true,
					'default'     => 1060,
					'input_attrs' => array(
						'min'          => 800,
						'max'          => 1600,
						'step'         => 1,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property' => 'max-width',
							'selector' => '
							.singular .content-area, 
							.attachment .content-area',
							'unit'     => 'px',
						),
					),
				),
				'main_content_border_width'            => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Site Border Width', 'gema' ),
					'desc'        => esc_html__( '', 'gema' ),
					'live'        => true,
					'default'     => 0,
					'input_attrs' => array(
						'min'          => 0,
						'max'          => 120,
						'step'         => 1,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property' => 'margin',
							'selector' => 'body',
							'unit'     => 'px',
//							'callback_filter' => 'typeline_spacing_cb'
						),
					),
				),
				'main_content_border_color'            => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Site Border Color', 'gema' ),
					'live'    => true,
					'default' => SM_LIGHT_SECONDARY,
					'css'     => array(
						array(
							'property' => 'background-color',
							'selector' => 'html',
						),
					),
				),

				// [Section] COLORS
				'main_content_title_colors_section' => array(
					'type' => 'html',
					'html' => '<span id="section-title-main-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'gema' ) . '</span>',
				),
				'main_content_page_title_color'         => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Page Title Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_PRIMARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.entry-header .entry-title',
						),
					),
				),
				'main_content_body_text_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Body Text Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'body',
						),
						array(
							'property' => 'background-color',
							'selector' => 'input[type="submit"], .btn, .search-submit, div#infinite-handle span button, div#infinite-handle span button:hover, .more-link,
								.sticky.card--text .card__wrap:after,
								.sticky.card--text .card__meta,
								body div.sharedaddy div.sd-social-icon div.sd-content ul li[class*="share-"] a.sd-button:hover',
						),
						array(
							'property' => 'border-color',
							'selector' => 'input[type="text"], 
								input[type="password"], 
								input[type="datetime"], 
								input[type="datetime-local"], 
								input[type="date"], 
								input[type="month"], 
								input[type="time"], 
								input[type="week"], 
								input[type="number"], 
								input[type="email"], 
								input[type="url"], 
								input[type="search"], 
								input[type="tel"], 
								input[type="color"], 
								select, 
								textarea',
							'callback_filter' => 'gema_color_opacity_adjust_cb'
						),
						array(
							'property' => 'border-color',
							'selector' => 'body div.sharedaddy div.sd-social-icon div.sd-content ul li[class*="share-"] a.sd-button:hover',
						),
						array(
							'property' => 'border-bottom-color',
							'selector' => '.singular .site-header, .attachment .site-header',
							'callback_filter' => 'gema_color_opacity_adjust_cb'
						),
					),
				),
				'main_content_body_link_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Body Link Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_TERTIARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '
								.entry-content a, 
								.comment__content a',
						),
					),
				),
				'main_content_body_link_active_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Body Link Active Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_TERTIARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '
								.entry-content a:hover, 
								.entry-content a:focus, 
								.comment__content a:hover, 
								.comment__content a:focus',
						),
					),
				),
				'main_content_underlined_body_links'    => array(
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Underlined Body Links', 'gema' ),
					'default' => 0,
				),
				// [Sub Section] Headings Color
				'main_content_title_headings_color_section'              => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Headings Color', 'gema' ) . '</span>',
				),
				'main_content_heading_1_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 1', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h1',
						),
					),
				),
				'main_content_heading_2_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 2', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h2',
						),
					),
				),
				'main_content_heading_3_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 3', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h3'
						),
					),
				),
				'main_content_heading_4_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 4', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h4',
						),
					),
				),
				'main_content_heading_5_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 5', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h5',
						),
					),
				),
				'main_content_heading_6_color'          => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Heading 6', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => 'h6',
						),
					),
				),

				// [Sub Section] Headings Color
				'main_content_backgrounds_section' => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Backgrounds', 'gema' ) . '</span>',
				),
				'main_content_background_color' => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Content Background Color', 'gema' ),
					'live'    => true,
					'default' => SM_LIGHT_PRIMARY,
					'css'     => array(
						array(
							'selector' => '
	                            .comment__avatar, 
	                            .sticky, 
	                            body.singular .nav-menu > li > a:before, 
                                .bypostauthor > .comment__article .comment__avatar,
                                .overlay-shadow',
							'property' => 'background-color',
						),
						array(
							'selector' => '
								body, 
	                            .mobile-header-wrapper, 
	                            .main-navigation',
							'property' => 'background'
						),
						array(
							'selector' => '.nav-menu ul',
							'media'    => 'only screen and (min-width: 900px)',
							'property' => 'background'
						),
						array(
							'selector' =>
								'.widget-area',
							'media'    => 'not screen and (min-width: 900px)',
							'property' => 'background'
						),
						array(
//                            .sticky h2,
//                            .sticky.card--text .card__wrap > *:not(.card__meta),
//                            .sticky .card__meta .cat-links,
//                            .sticky .byline,
//                            .sticky.card--text .cat-links,
//                            .sticky.card--text .byline .author,
//                            .sticky.card--text .post-edit-link,
//                            .sticky .entry-date,
							'property' => 'color',
							'selector' => '
								input[type="submit"], 
	                            .btn, 
	                            div#infinite-handle span button, 
	                            div#infinite-handle span button:hover, 
	                            .more-link, 
	                            .comment__content a, 
	                            .nav-menu ul li.hover > a, 
	                            .nav-menu ul li.hover:after,
	                            div#subscribe-text p,
	                            .jetpack_subscription_widget .widget__title, 
	                            .widget_blog_subscription .widget__title,
	                            .jetpack_subscription_widget p,
	                            .jetpack_subscription_widget input[type="submit"],
	                            .widget_blog_subscription input[type="submit"],
	                            .sticky.card--text .card__wrap,
	                            .sticky.card--text .card__meta,
	                            .sticky.card--text .cat-links, .sticky.card--text .byline .author, .sticky.card--text .post-edit-link',
						),
//						.sticky.card--text .btn,
//                        .sticky.card--text .search-submit,
//                        .sticky.card--text div#infinite-handle span button,
//                        div#infinite-handle span .sticky.card--text button,
//                        .sticky.card--text .more-link,
//                        .sticky.card--text .card__wrap:before,
//                        .sticky.card--text .card__wrap:after,
						array(
							'property' => 'border-color',
							'selector' => '
                            .jetpack_subscription_widget input[type=\'submit\'],
                            .sticky.card--text .btn, 
                            .sticky.card--text .search-submit, 
                            .sticky.card--text div#infinite-handle span button, 
                            div#infinite-handle span .sticky.card--text button, 
                            .sticky.card--text .more-link'
						),
						array(
							'property' => 'border-right-color',
							'selector' => '.nav-menu:before'
						),
						array(
							'property' => 'border-left-color',
							'selector' => '.nav-menu:before'
						),
						array(
							'property' => 'color',
							'selector' => 'body div.sharedaddy div.sd-social-icon div.sd-content ul li[class*="share-"] a.sd-button:hover',
							'callback_filter' => 'gema_important_rule'
						),
						array(
							'selector' => '.is--webkit .dropcap',
							'property' => 'background-image',
							'callback_filter' => 'gema_dropcap_style'
						),
					),
				),

				// [Section] FONTS
				'main_content_title_fonts_section'             => array(
					'type' => 'html',
					'html' => '<span id="section-title-main-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', 'gema' ) . '</span>',
				),

				'main_content_page_title_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Page Title Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.page .entry-title',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 32,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,


					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_body_text_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Body Text Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => 'body',
					'media'    => 'only screen and (min-width: 740px)',
//					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'font-size'      => 16,
						'line-height'    => 1.75,
						'letter-spacing' => 0,
						'text-transform' => 'none'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_quote_block_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Quote Block Font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content blockquote p',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '700',
						'font-size'      => 17,
						'line-height'    => 1.75,
						'letter-spacing' => 0,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				// [Sub Section] Headings Fonts
				'main_content_title_headings_fonts_section'     => array(
					'type' => 'html',
					'html' => '<span class="separator sub-section label">' . esc_html__( 'Headings Fonts', 'gema' ) . '</span>',
				),

				'main_content_heading_1_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 1', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h1, h1, .h1',
					'callback' => 'gema_dropcap_typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 32,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,
					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_heading_2_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 2', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h2, h2, .h2',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
                      'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 20,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_heading_3_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 3', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h3, h3, .h3',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 18,
						'line-height'    => 1.5,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_heading_4_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 4', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h4, h4, .h4',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'font-size'      => 14,
						'line-height'    => 1.5,
						'letter-spacing' => 0.029,
						'text-transform' => 'none'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_heading_5_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 5', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h5, h5, .h5, .entry-content blockquote cite',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'font-size'      => 12,
						'line-height'    => 1.5,
						'letter-spacing' => 0.029,
						'text-transform' => 'none'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),

				'main_content_heading_6_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Heading 6', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.entry-content h6, h6, .h6',
					'callback' => 'typeline_font_cb',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'font-size'      => 12,
						'line-height'    => 1.5,
						'letter-spacing' => 0.029,
						'text-transform' => 'none'
					),

					// List of recommended fonts defined by theme
					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => array(                           // Set custom values for a range slider
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
						'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
						'text-align'      => false,                           // Disable sub-field (False by default)
						'text-transform'  => true,
						'text-decoration' => false
					)
				),
			)
		),
	);

	$main_content_section = apply_filters( 'pixelgrade_customify_main_content_section_options', $main_content_section, $options );

	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	$options['sections'] = $options['sections'] + $main_content_section;

	return $options;
}

function gema_add_customify_footer_options( $options ) {
	$footer_section = array(
		// Footer
		'footer_section' => array(
			'title'   => esc_html__( 'Footer', 'gema' ),
			'options' => array(
				'footer_options_customizer_tabs'    => array(
					'type' => 'html',
					'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-footer-layout">' . esc_html__( 'Layout', 'gema' ) . '</a>
							<a href="#section-title-footer-colors">' . esc_html__( 'Colors', 'gema' ) . '</a>
							<a href="#section-title-footer-fonts">' . esc_html__( 'Fonts', 'gema' ) . '</a>
							</nav>',
				),
				// [Section] Layout
				'footer_title_layout_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-footer-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'gema' ) . '</span>',
				),
				'copyright_text'               => array(
					'type'              => 'textarea',
					'label'             => esc_html__( 'Copyright Text', 'gema' ),
					'desc'              => esc_html__( 'Set the text that will appear in the footer area. Use %year% to display the current year.', 'gema' ),
					'default'           => __( '<a href="https://pixelgrade.com/themes/gema/" title="'. esc_attr__( 'GEMA - A Journal-Inspired WordPress Theme', 'gema' ) .'" rel="theme">'. esc_html__( 'Gema Theme', 'gema' ) .'</a> <span>by</span> <a href="https://pixelgrade.com" title="'. esc_attr__( 'The Pixelgrade Website', 'gema' ) .'" rel="designer">Pixelgrade</a>', 'gema' ),
					'sanitize_callback' => 'wp_kses_post',
					'live'              => array( '.c-footer__copyright-text' ),
				),
				'footer_top_spacing'           => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Top Spacing', 'gema' ),
					'desc'        => '',
					'live'        => true,
					'default'     => 40,
					'input_attrs' => array(
						'min'          => 0,
						'max'          => 200,
						'step'         => 1,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property'        => 'padding-top',
							'selector'        => '.site-footer',
							'unit'            => 'px',
							'callback_filter' => 'typeline_spacing_cb',
						),
					),
				),
				'footer_bottom_spacing'        => array(
					'type'        => 'range',
					'label'       => esc_html__( 'Bottom Spacing', 'gema' ),
					'desc'        => '',
					'live'        => true,
					'default'     => 40,
					'input_attrs' => array(
						'min'          => 0,
						'max'          => 200,
						'step'         => 1,
						'data-preview' => true,
					),
					'css'         => array(
						array(
							'property'        => 'padding-bottom',
							'selector'        => '.site-footer',
							'unit'            => 'px',
						),
					),
				),

				// [Section] COLORS
				'footer_title_colors_section'    => array(
					'type' => 'html',
					'html' => '<span id="section-title-footer-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'gema' ) . '</span>',
				),
				'footer_body_text_color'       => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Body Text Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_SECONDARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.site-footer',
						),
					),
				),
				'footer_links_color'           => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Links Color', 'gema' ),
					'live'    => true,
					'default' => SM_DARK_TERTIARY,
					'css'     => array(
						array(
							'property' => 'color',
							'selector' => '.site-footer a',
						),
					),
				),
				'footer_background'            => array(
					'type'    => 'color',
					'label'   => esc_html__( 'Footer Background', 'gema' ),
					'live'    => true,
					'default' => SM_LIGHT_PRIMARY,
					'css'     => array(
						array(
							'property' => 'background',
							'selector' => '.site-footer',
						),
					),
				),

				// [Section] FONTS
				'footer_fonts_section'          => array(
					'type' => 'html',
					'html' => '<span id="section-title-footer-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', 'gema' ) . '</span>',
				),

				'footer_body_font' => array(
					'type'     => 'font',
					'label'    => esc_html__( 'Footer font', 'gema' ),
					'desc'     => esc_html__( '', 'gema' ),
					'selector' => '.site-footer',

					// Set the defaults
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '200',
						'text-transform' => 'none'
					),

					// List of recommended fonts defined by theme
//					'recommended' => $recommended_body_fonts,

					// Sub Fields Configuration (optional)
					'fields'   => array(
						'font-size'       => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => true,
						'text-decoration' => false
					)
				),
			)
		),
	);

	$footer_section = apply_filters( 'pixelgrade_customify_main_content_section_options', $footer_section, $options );

	if ( empty( $options['sections'] ) ) {
		$options['sections'] = array();
	}

	$options['sections'] = $options['sections'] + $footer_section;

	return $options;
}

if ( ! function_exists('gema_sticky_boxshadow') ) {
	function gema_sticky_boxshadow( $value, $selector, $property, $unit ) {

		$border_color = pixelgrade_option( 'main_content_body_text_color' );

		$output = $selector . '{
			box-shadow: 0 0 0 8px '. $value .', 0 0 0 9px '. $border_color . ";\n".
		          "}\n";
		return $output;
	}
}

/**
 * Adds the default fonts list in the recommended area of each body font
 * @param $custom_fonts
 *
 * @return array()
 */
function gema_set_default_recommended_body_fonts( $custom_fonts ){

	return $custom_fonts + array(
			'Montserrat',
			'Playfair Display',
			'Oswald',
			'Lato',
			'Open Sans',
			'Exo',
			'PT Sans',
			'Ubuntu',
			'Vollkorn',
			'Lora',
			'Arvo',
			'Josefin Slab',
			'Crete Round',
			'Kreon',
			'Bubblegum Sans',
			'The Girl Next Door',
			'Pacifico',
			'Handlee',
			'Satify',
			'Pompiere'
		);
}
add_filter( 'customify_theme_recommended_body_fonts', 'gema_set_default_recommended_body_fonts' );

/**
 * Adds the default fonts list in the recommended area of each title font
 * @param $custom_fonts
 *
 * @return array()
 */
function gema_set_default_recommended_site_title_fonts( $custom_fonts ) {

	return $custom_fonts + array(
		'Butler',
		'Montserrat',
		'Playfair Display',
		'Oswald',
		'Lato',
	);
}
add_filter( 'customify_theme_recommended_site_title_fonts', 'gema_set_default_recommended_site_title_fonts' );

function gema_negative_value( $value, $selector, $property, $unit ) {

	$output = $selector . '{
		' . $property . ': -' . $value . '' . $unit . ";\n" . "}\n";

	return $output;
}

if ( ! function_exists('gema_dropcap_style') ) {

	function gema_dropcap_style( $value, $selector, $property, $unit ) {
		$output = '';

		$output .= $selector . ' {' . PHP_EOL .
		           $property . ': linear-gradient(45deg, currentColor 0%, currentColor 10%, ' . $value . ' 10%, ' . $value . ' 40%, currentColor 40%, currentColor 60%, ' . $value . ' 60%, ' . $value . ' 90%, currentColor 90%, currentColor 100%)' .
		           '}' . PHP_EOL;

		return $output;
	}
}

if ( ! function_exists('gema_dropcap_style_customizer_preview') ) {

	function gema_dropcap_style_customizer_preview() {

		$js = "
        function gema_dropcap_style( value, selector, property, unit ) {
        
            var css = '',
                style = document.getElementById('gema_dropcap_style_style_tag'),
                head = document.head || document.getElementsByTagName('head')[0];
        
            css += selector + ' {' +
                property + ': linear-gradient(45deg, currentColor 0%, currentColor 10%, ' + value + ' 10%, ' + value + ' 40%, currentColor 40%, currentColor 60%, ' + value + ' 60%, ' + value + ' 90%, currentColor 90%, currentColor 100%)' +
            '}';
        
            if ( style !== null ) {
                style.innerHTML = css;
            } else {
                style = document.createElement('style');
                style.setAttribute('id', 'gema_dropcap_style_style_tag');
        
                style.type = 'text/css';
                if ( style.styleSheet ) {
                    style.styleSheet.cssText = css;
                } else {
                    style.appendChild(document.createTextNode(css));
                }
        
                head.appendChild(style);
            }
        }" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}
}

if ( ! function_exists('gema_important_rule') ) {

	function gema_important_rule( $value, $selector, $property, $unit ) {
		$output = '';

		$output .= $selector . ' {' . PHP_EOL .
		           $property . ': ' . $value . $unit . ' !important;' .
		           '}' . PHP_EOL;

		return $output;
	}
}

if ( ! function_exists('gema_important_rule_customizer_preview') ) {

	function gema_important_rule_customizer_preview() {

		$js = "
        function gema_important_rule( value, selector, property, unit ) {
        
            var css = '',
                style = document.getElementById('gema_important_rule_style_tag'),
                head = document.head || document.getElementsByTagName('head')[0];
        
            css += selector + ' {' +
                property + ': ' + value + unit + ' !important;' +
            '}';
        
            if ( style !== null ) {
                style.innerHTML = css;
            } else {
                style = document.createElement('style');
                style.setAttribute('id', 'gema_important_rule_style_tag');
        
                style.type = 'text/css';
                if ( style.styleSheet ) {
                    style.styleSheet.cssText = css;
                } else {
                    style.appendChild(document.createTextNode(css));
                }
        
                head.appendChild(style);
            }
        }" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}
}

add_action( 'customize_preview_init', 'gema_important_rule_customizer_preview', 20 );

if ( ! function_exists('gema_color_opacity_adjust_cb') ) {
	function gema_color_opacity_adjust_cb( $hex, $selector, $property, $unit ) {

		// Get our color
		if ( empty( $hex ) || ! preg_match( '/^#[a-f0-9]{6}$/i', $hex ) ) {
			return '';
		}

		// Format the hex color string.
		$hex = str_replace( '#', '', $hex );

		if ( 3 == strlen( $hex ) ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Get decimal values.
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		$output = $selector . ' {' .
		          $property . ': rgba(' . $r .',' . $g . ',' . $b .', 0.25);
		}';

		return $output;
	}
}

if ( ! function_exists('gema_color_opacity_adjust_cb_customizer_preview') ) {

	function gema_color_opacity_adjust_cb_customizer_preview() {
	    $js = "
        function hexdec(hexString) {
            hexString = (hexString + '').replace(/[^a-f0-9]/gi, '');
            return parseInt(hexString, 16)
        }

        function gema_color_opacity_adjust_cb( value, selector, property, unit ) {
            var css = '',
                style = document.getElementById('gema_color_opacity_adjust_cb_style_tag'),
                head = document.head || document.getElementsByTagName('head')[0],
                r = hexdec(value[1] + '' + value[2]),
                g = hexdec(value[3] + '' + value[4]),
                b = hexdec(value[5] + '' + value[6]);

            css += selector + ' { ' + property + ': rgba(' + r + ',' + g + ',' + b + ',0.2); } ';
            
            if ( style !== null ) {
                style.innerHTML = css;
            } else {
                style = document.createElement('style');
                style.setAttribute('id', 'gema_color_opacity_adjust_cb_style_tag');

                style.type = 'text/css';
                if ( style.styleSheet ) {
                    style.styleSheet.cssText = css;
                } else {
                    style.appendChild(document.createTextNode(css));
                }

                head.appendChild(style);
            }
        }" . PHP_EOL;

	    wp_add_inline_script( 'customify-previewer-scripts', $js );
    }

	add_action( 'customize_preview_init', 'gema_color_opacity_adjust_cb_customizer_preview' );
}

function gema_add_default_color_palette( $color_palettes ) {

	$color_palettes = array_merge(array(
		'default' => array(
			'label' => 'Theme Default',
			'preview' => array(
				'background_image_url' => 'https://cloud.pixelgrade.com/wp-content/uploads/2018/05/gema-theme-palette.jpg',
			),
			'options' => array(
				'sm_color_primary' => SM_COLOR_PRIMARY,
				'sm_color_secondary' => SM_COLOR_SECONDARY,
				'sm_color_tertiary' => SM_COLOR_TERTIARY,
				'sm_dark_primary' => SM_DARK_PRIMARY,
				'sm_dark_secondary' => SM_DARK_SECONDARY,
				'sm_dark_tertiary' => SM_DARK_TERTIARY,
				'sm_light_primary' => SM_LIGHT_PRIMARY,
				'sm_light_secondary' => SM_LIGHT_SECONDARY,
				'sm_light_tertiary' => SM_LIGHT_TERTIARY,
			),
		),
	), $color_palettes);

	return $color_palettes;
}
add_filter( 'customify_get_color_palettes', 'gema_add_default_color_palette' );
