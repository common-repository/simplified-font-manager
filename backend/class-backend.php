<?php
/**
 * The back-end specific functionality of the plugin.
 *
 * @package Simplified_Font_Manager
 * @since 1.0.0
 */

namespace Simplified_Font_Manager;

/**
 * The back-end specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class Backend {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected static $instance = null;

	/**
	 * Holds the main option name.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected $option;

	/**
	 * Holds setting keys.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $setting_keys;

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		$this->option       = 'simplfied_font_manager_options';
		$this->setting_keys = [
			'delete'        => 'bool',
			'family'        => 'array-text',
			'weights'       => 'array-text',
			'selectors'     => 'css-selectors',
			'highlight'     => 'temporary',
			'fsdesktop'     => 'integer',
			'fudesktop'     => 'text',
			'fstablet'      => 'integer',
			'futablet'      => 'text',
			'fsmobile'      => 'integer',
			'fumobile'      => 'text',
			'fontweight'    => 'text',
			'fontstyle'     => 'text',
			'texttransform' => 'text',
			'lineheight'    => 'float',
			'letterspacing' => 'float',
			'enforcestyle'  => 'checkbox',
		];

	}

	/**
	 * Register hooked functions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {

		add_action( 'customize_preview_init', [ self::get_instance(), 'customize_preview_scripts' ] );
		add_action( 'customize_controls_print_footer_scripts', [ self::get_instance(), 'load_control_template' ] );
		add_action( 'customize_controls_print_styles', [ self::get_instance(), 'customize_controls_styles' ] );
		add_action( 'customize_controls_enqueue_scripts', [ self::get_instance(), 'customize_controls_scripts' ] );
		add_action( 'customize_save_after', [ self::get_instance(), 'customize_save_after' ] );
		add_action( 'wp_ajax_simplyfm_typekit', [ self::get_instance(), 'ajax_fetch_typekit' ] );
		add_action( 'wp_ajax_nopriv_simplyfm_typekit', [ self::get_instance(), 'ajax_fetch_typekit' ] );
		add_action( 'after_switch_theme', [ self::get_instance(), 'after_switch_theme' ] );
		add_action( 'admin_notices', [ self::get_instance(), 'admin_notices' ] );
		add_filter( 'customize_dynamic_setting_args', [ self::get_instance(), 'filter_customize_dynamic_setting_args' ], 10, 2 );
		add_filter( 'simplified_font_manager_controls_data', [ self::get_instance(), 'filter_dynamic_controls_data' ] );
	}

	/**
	 * Register the custom Widget.
	 *
	 * @since 1.0.0
	 */
	public function load_control_template() {

		/**
		 * Font Manager customizer control template.
		 */
		require_once SIMPLIFIED_FONT_MANAGER_DIR . 'backend/inc/control-template.php';
	}

	/**
	 * Enqueue customizer controls styles for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function customize_controls_styles() {

		wp_enqueue_style(
			'simplified-font-manager-customize-controls',
			plugin_dir_url( __FILE__ ) . 'css/customize-controls.css',
			[],
			SIMPLIFIED_FONT_MANAGER_VERSION,
			'all'
		);
	}

	/**
	 * Enqueue customizer controls scripts for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function customize_controls_scripts() {

		$typekit = false;
		$adv     = get_option( 'simplfied_font_manager_adv_options' );
		if ( $adv && isset( $adv['typekit'] ) ) {
			$typekit = get_option( 'simplfied_font_manager_typekit_fonts' );
			if ( false === $typekit || $adv['typekit'] !== $typekit['projectid'] ) {
				$typekit = $this->get_typekit_fonts( $adv['typekit'] );
			} else {
				$typekit = $typekit['fonts'];
			}
		}

		if ( $typekit ) {
			foreach ( $typekit as $key => $font ) {
				$typekit[ $key ]['family']   = wp_strip_all_tags( $font['family'] );
				$typekit[ $key ]['stack']    = wp_strip_all_tags( $font['stack'] );
				$typekit[ $key ]['variants'] = array_map( 'wp_strip_all_tags', $font['variants'] );
			}
		} else {
			$typekit = [];
		}

		/**
		 * Send Google fonts object in javascript.
		 */
		wp_enqueue_script(
			'simplified-font-manager-google-fonts',
			plugin_dir_url( __FILE__ ) . 'js/simply-gf.js',
			[],
			SIMPLIFIED_FONT_MANAGER_VERSION,
			true
		);

		// Scripts data.
		wp_localize_script(
			'simplified-font-manager-google-fonts',
			'simplyfmFontsData',
			$typekit
		);

		/**
		 * Register customizer controls javascripts.
		 */
		wp_enqueue_script(
			'simplified-font-manager-customize-controls',
			plugin_dir_url( __FILE__ ) . 'js/customize-controls.js',
			[ 'customize-controls', 'jquery', 'simplified-font-manager-google-fonts' ],
			SIMPLIFIED_FONT_MANAGER_VERSION,
			true
		);

		// Scripts data.
		wp_localize_script(
			'simplified-font-manager-customize-controls',
			'simplyfmControlsData',
			apply_filters( 'simplified_font_manager_controls_data', [] )
		);
	}

	/**
	 * Binds JS handlers to make Customizer preview reload changes asynchronously.
	 *
	 * @since 1.0.0
	 */
	public function customize_preview_scripts() {

		$options = get_option( $this->option );
		$options = $options ? $options : [];
		$advops  = get_option( 'simplfied_font_manager_adv_options' );
		$advops  = $advops ? $advops : [];
		$length  = $options ? count( $options ) : 0;

		/**
		 * Register customizer preview javascripts.
		 */
		wp_enqueue_script(
			'simplified-font-manager-customize-preview',
			plugin_dir_url( __FILE__ ) . 'js/customize-preview.js',
			[ 'customize-preview', 'jquery' ],
			SIMPLIFIED_FONT_MANAGER_VERSION,
			true
		);

		// Scripts data.
		wp_localize_script(
			'simplified-font-manager-customize-preview',
			'simplyfmPreviewData',
			apply_filters(
				'simplified_font_manager_preview_data',
				[
					'options' => $options,
					'olength' => $length,
					'advops'  => $advops,
				]
			)
		);
	}

	/**
	 * Determine the arguments for a dynamically-created setting.
	 *
	 * @since 1.0.0
	 *
	 * @param false|array $args The arguments to the WP_Customize_Setting constructor.
	 * @param string      $setting_id ID for dynamic setting.
	 * @return false|array Setting arguments, false otherwise.
	 */
	public function filter_customize_dynamic_setting_args( $args, $setting_id ) {

		if ( false !== strpos( $setting_id, $this->option ) ) {
			$args = [
				'type'              => 'option',
				'default'           => $this->get_default_value( $setting_id ),
				'sanitize_callback' => [ $this, 'sanitization' ],
				'transport'         => 'postMessage',
			];
		} elseif ( false !== strpos( $setting_id, 'simplfied_font_manager_adv_options' ) ) {
			$args = [
				'type'              => 'option',
				'default'           => $this->get_default_value( $setting_id ),
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			];
		}

		return $args;
	}

	/**
	 * Controls data to be sent to customizer as JSON object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Controls data.
	 * @return false|array Setting arguments, false otherwise.
	 */
	public function filter_dynamic_controls_data( $data ) {

		$all_options = $this->get_options();
		$options     = $all_options['options'];
		$theme_ops   = $all_options['theme_options'];
		$advoptions  = get_option( 'simplfied_font_manager_adv_options' );
		$tlength     = false === $theme_ops ? 0 : count( $theme_ops );
		$length      = false === $options ? 0 : count( $options );

		// Typekit Documentation.
		$link1 = 'https://fonts.adobe.com/';
		$link2 = 'https://fonts.adobe.com/fonts';
		$link3 = 'https://fonts.adobe.com/my_fonts?browse_mode=all#web_projects-section';

		$typekit_docs_p1 = sprintf( '%s <a target="_blank" href="%s">%s</a>', esc_html__( 'Create a new Adobe ID or Sign In with your existing ID at', 'simplified-font-manager' ), esc_url( $link1 ), esc_html__( 'Adobe Fonts', 'simplified-font-manager' ) );
		$typekit_docs_p2 = sprintf( '%s <a target="_blank" href="%s">%s</a> %s', esc_html__( 'Search or Browse ', 'simplified-font-manager' ), esc_url( $link2 ), esc_html__( 'Adobe Fonts.', 'simplified-font-manager' ), esc_html__( 'Click on "</>" icon to add fonts to your existing or new web project.', 'simplified-font-manager' ) );
		$typekit_docs_p3 = sprintf( '%s <a target="_blank" href="%s">%s</a> %s', esc_html__( 'Go to your', 'simplified-font-manager' ), esc_url( $link3 ), esc_html__( 'Web Projects', 'simplified-font-manager' ), esc_html__( '. Find and Copy "Project ID", which can be found next to the Project name.', 'simplified-font-manager' ) );
		$typekit_docs_p4 = esc_html__( 'Paste Project ID here ( in the field below ) and hit arrow icon', 'simplified-font-manager' );

		$data = [
			'l10n'         => [
				'section_title' => esc_html__( 'Font Manager', 'simplified-font-manager' ),
				'control_label' => esc_html__( 'Font Control', 'simplified-font-manager' ),
				'adv_ctrl_labl' => esc_html__( 'Avdanced Options', 'simplified-font-manager' ),
				'loadmore'      => esc_html__( 'Create Font Control', 'simplified-font-manager' ),
				'noresultstext' => esc_html__( 'Sorry! Font not available.', 'simplified-font-manager' ),
				'ctmpl'         => [
					'flabel'  => esc_html__( 'Select a Font', 'simplified-font-manager' ),
					'sfont'   => esc_html__( 'Search Font', 'simplified-font-manager' ),
					'ffilter' => esc_html__( 'Filter Font Categories', 'simplified-font-manager' ),
					'slabel'  => esc_attr_x( 'Type target elements here, e.g., body, h1, .site-title, #main', 'Placeholder text for css selectors', 'simplified-font-manager' ),
					'sdesc'   => esc_html__( 'Comma separated css selectors.', 'simplified-font-manager' ),
					'weights' => esc_html__( 'Add Font Weights', 'simplified-font-manager' ),
					'delete'  => esc_html__( 'Delete control', 'simplified-font-manager' ),
					'advset'  => esc_html__( 'Advanced Font settings', 'simplified-font-manager' ),
					'glabel'  => esc_attr_x( 'Google Fonts', 'Font optgroup label attribute', 'simplified-font-manager' ),
					'selph'   => esc_attr_x( 'example: .site-title, #main', 'Placeholder text for css selectors', 'simplified-font-manager' ),
					'fsize'   => esc_html__( 'Font size', 'simplified-font-manager' ),
					'fweight' => esc_html__( 'Font Weight', 'simplified-font-manager' ),
					'fstyle'  => esc_html__( 'Font Style', 'simplified-font-manager' ),
					'ttform'  => esc_html__( 'Text Transform', 'simplified-font-manager' ),
					'lheight' => esc_html__( 'Line Height', 'simplified-font-manager' ),
					'lspace'  => esc_html__( 'Letter Spacing', 'simplified-font-manager' ),
					'enforce' => esc_html__( 'Enforce Styling', 'simplified-font-manager' ),
					'hlight'  => esc_html__( 'Temporarily Highlight Elements', 'simplified-font-manager' ),
					'dbtn'    => esc_html__( 'Show desktop font-size', 'simplified-font-manager' ),
					'tbtn'    => esc_html__( 'Show tablet font-size', 'simplified-font-manager' ),
					'mbtn'    => esc_html__( 'Show mobile font-size', 'simplified-font-manager' ),
					'back'    => esc_html__( 'Font List', 'simplified-font-manager' ),
					'next'    => esc_html__( 'Next Font', 'simplified-font-manager' ),
					'prev'    => esc_html__( 'Previous Font', 'simplified-font-manager' ),
					'afont'   => esc_html__( 'Add Font Data', 'simplified-font-manager' ),
					'sfwei'   => esc_html__( 'Select required font weights (optional).', 'simplified-font-manager' ),
					'italic'  => esc_html__( 'Add italic font style (if any).', 'simplified-font-manager' ),
					'stack'   => esc_html__( 'Font stack', 'simplified-font-manager' ),
				],
				'advanced'      => [
					'adv_label' => esc_html__( 'Plugin Advanced Options', 'simplified-font-manager' ),
					'reset'     => esc_html__( 'Reset to theme defaults', 'simplified-font-manager' ),
					'add_adobe' => esc_html__( 'Add Adobe Fonts (Typekit)', 'simplified-font-manager' ),
					'pro_id'    => esc_html__( 'Enter Project ID', 'simplified-font-manager' ),
					'submit'    => esc_html__( 'Submit Adobe Fonts Project ID', 'simplified-font-manager' ),
					'aj_fetch'  => esc_html__( 'Fetching...', 'simplified-font-manager' ),
					'aj_none'   => esc_html__( 'Please Enter Project ID', 'simplified-font-manager' ),
					'aj_delete' => esc_html__( 'Fonts Deleted Successfully', 'simplified-font-manager' ),
					'aj_pass'   => esc_html__( 'Fonts Added Successfully', 'simplified-font-manager' ),
					'aj_fail'   => esc_html__( 'Fonts could not be added. Please check Project ID', 'simplified-font-manager' ),
					'docstitle' => esc_html__( 'Enter Adobe Font\'s Project ID', 'simplified-font-manager' ),
					'typedocs'  => [
						'p1' => $typekit_docs_p1,
						'p2' => $typekit_docs_p2,
						'p3' => $typekit_docs_p3,
						'p4' => $typekit_docs_p4,
						'p5' => esc_html__( 'Close', 'simplified-font-manager' ),
					],
				],
			],
			'fdata'        => [
				'ffilter' => [
					'typekit' => [
						'title' => esc_html__( 'Adobe Fonts (typekit)', 'simplified-font-manager' ),
						'list'  => [
							'typekit' => esc_html__( 'All Adobe Fonts', 'simplified-font-manager' ),
						],
					],
					'google'  => [
						'title' => esc_html__( 'Google Fonts', 'simplified-font-manager' ),
						'list'  => [
							'goo-sans-serif'  => esc_html__( 'Sans-Serif', 'simplified-font-manager' ),
							'goo-serif'       => esc_html__( 'Serif', 'simplified-font-manager' ),
							'goo-monospace'   => esc_html__( 'Monospace', 'simplified-font-manager' ),
							'goo-display'     => esc_html__( 'Display', 'simplified-font-manager' ),
							'goo-handwriting' => esc_html__( 'Handwriting', 'simplified-font-manager' ),
						],
					],
					'websafe' => [
						'title' => esc_html__( 'Websafe Fonts', 'simplified-font-manager' ),
						'list'  => [
							'websafe-sans-serif' => esc_html__( 'Sans-Serif', 'simplified-font-manager' ),
							'websafe-serif'      => esc_html__( 'Serif', 'simplified-font-manager' ),
							'websafe-monospace'  => esc_html__( 'Monospace', 'simplified-font-manager' ),
						],
					],
				],
				'funit'   => [
					''    => esc_html__( 'px', 'simplified-font-manager' ),
					'rem' => esc_html__( 'rem', 'simplified-font-manager' ),
					'em'  => esc_html__( 'em', 'simplified-font-manager' ),
				],
				'fstyle'  => [
					''       => esc_html__( 'Default', 'simplified-font-manager' ),
					'italic' => esc_html__( 'Italic', 'simplified-font-manager' ),
					'normal' => esc_html__( 'Normal', 'simplified-font-manager' ),
				],
				'fweight' => [
					''    => esc_html__( 'Default', 'simplified-font-manager' ),
					'100' => esc_html__( 'Thin', 'simplified-font-manager' ),
					'200' => esc_html__( 'Extra Light', 'simplified-font-manager' ),
					'300' => esc_html__( 'Light', 'simplified-font-manager' ),
					'400' => esc_html__( 'Normal', 'simplified-font-manager' ),
					'500' => esc_html__( 'Medium', 'simplified-font-manager' ),
					'600' => esc_html__( 'Semi Bold', 'simplified-font-manager' ),
					'700' => esc_html__( 'Bold', 'simplified-font-manager' ),
					'800' => esc_html__( 'Extra Bold', 'simplified-font-manager' ),
					'900' => esc_html__( 'Ultra Bold', 'simplified-font-manager' ),
				],
				'ftt'     => [
					''           => esc_html__( 'Default', 'simplified-font-manager' ),
					'uppercase'  => esc_html__( 'Upper Case', 'simplified-font-manager' ),
					'lowercase'  => esc_html__( 'Lower Case', 'simplified-font-manager' ),
					'capitalize' => esc_html__( 'Capital Case', 'simplified-font-manager' ),
				],
				'scrn'    => [
					'desktop' => esc_html__( 'Desktop', 'simplified-font-manager' ),
					'tablet'  => esc_html__( 'Tablet', 'simplified-font-manager' ),
					'mobile'  => esc_html__( 'Mobile', 'simplified-font-manager' ),
				],
			],
			'ajax_info'    => [
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'simplyfm-ajax-nonce' ),
			],
			'options'      => $options,
			'themeoptions' => $theme_ops,
			'advops'       => $advoptions,
			'tolength'     => $tlength,
			'olength'      => $length,
		];
		return $data;
	}

	/**
	 * Delete controls which are seet to be deleted.
	 *
	 * @since 1.0.0
	 */
	public function customize_save_after() {

		$options = get_option( $this->option );
		if ( false === $options ) {
			return;
		}

		foreach ( $options as $key => $option ) {
			if ( true === $option['delete'] ) {
				unset( $options[ $key ] );
			}
		}

		if ( empty( $options ) ) {
			delete_option( $this->option );
		} else {
			update_option( $this->option, array_values( $options ) );
		}
	}

	/**
	 * Set default values for theme customization settings.
	 *
	 * @since 1.0.0
	 *
	 * @param str $setting_id Current setting's ID.
	 * @return mixed Returns integer, string or array option values.
	 */
	public function get_default_value( $setting_id ) {

		return '';
	}

	/**
	 * Get customizer options and theme specific settings.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Returns integer, string or array option values.
	 */
	public function get_options() {

		$options       = get_option( $this->option );
		$theme_options = apply_filters( 'simplified_font_manager_theme_options', false );
		$theme_options = $this->prepare_theme_options( $theme_options );
		$use_defaults  = get_option( 'simplfied_font_manager_theme_first_time' );
		if ( false === $use_defaults ) {
			if ( false === $options && false !== $theme_options ) {
				update_option( $this->option, $theme_options );
				update_option( 'simplfied_font_manager_theme_first_time', true );
				$options = get_option( $this->option );
			} elseif ( false !== $options ) {
				update_option( 'simplfied_font_manager_theme_first_time', true );
			}
		}

		return array(
			'options'       => $options,
			'theme_options' => $theme_options,
		);
	}

	/**
	 * Prepare theme supported fonts array.
	 *
	 * @param array $tos theme supported fonts options.
	 *
	 * @since 1.0.0
	 */
	public function prepare_theme_options( $tos ) {
		if ( false === $tos || ! is_array( $tos ) ) {
			return false;
		}

		$options = [];
		foreach ( $tos as $key => $option ) {

			if ( ! is_array( $option ) || ! isset( $option['family'], $option['weights'], $option['selectors'] ) ) {
				continue;
			}

			if ( ! is_array( $option['family'] ) || ! is_array( $option['weights'] ) || ! is_string( $option['selectors'] ) ) {
				continue;
			}

			// IF condition for legacy implementation in Cambay and Bayleaf.
			// Should be removed after theme updates.
			if ( ! isset( $option['family'][2], $option['family'][3] ) ) {
				$fontname = $option['family'][0];
				$category = $option['family'][1];
				$category = 'goo-' . strtolower( str_replace( ' ', '-', $category ) );
				$fontslug = strtolower( str_replace( ' ', '', $fontname ) );

				$option['family'] = [ $fontslug, $fontname, $category, $fontname ];
			}
			$options[ $key ] = $option;
		}

		return $options;
	}

	/**
	 * Get customizer options and theme specific settings.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Returns integer, string or array option values.
	 */
	public function after_switch_theme() {
		delete_option( 'simplfied_font_manager_theme_first_time' );
	}

	/**
	 * Get Adobe fonts for Ajax calls.
	 *
	 * @since 1.0.0
	 */
	public function ajax_fetch_typekit() {

		check_ajax_referer( 'simplyfm-ajax-nonce', 'security' );

		// Get project ID from Ajax request.
		$project_id = isset( $_POST['kitid'] ) ? sanitize_text_field( wp_unslash( $_POST['kitid'] ) ) : '';

		if ( ! $project_id ) {
			delete_option( 'simplfied_font_manager_typekit_fonts' );
			echo wp_json_encode( [] );
			wp_die();
		}

		// Fetch fonts from adobe and process it for further use.
		$fonts = $this->get_typekit_fonts( $project_id );

		if ( ! $fonts ) {
			delete_option( 'simplfied_font_manager_typekit_fonts' );
			echo wp_json_encode( [] );
			wp_die();
		}

		foreach ( $fonts as $key => $font ) {
			$fonts[ $key ]['family']   = wp_strip_all_tags( $font['family'] );
			$fonts[ $key ]['stack']    = wp_strip_all_tags( $font['stack'] );
			$fonts[ $key ]['variants'] = array_map( 'wp_strip_all_tags', $font['variants'] );
		}

		echo wp_json_encode( $fonts );
		wp_die();
	}

	/**
	 * Get properly processed Adobe fonts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $project_id Adobe font's project ID.
	 * @return object Adobe fonts
	 */
	public function get_typekit_fonts( $project_id ) {

		$typekit_uri = 'https://typekit.com/api/v1/json/kits/' . $project_id . '/published';
		$response    = wp_remote_get( $typekit_uri, array( 'timeout' => '30' ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		$data     = json_decode( wp_remote_retrieve_body( $response ), true );
		$families = $data['kit']['families'];
		$fonts    = array();

		foreach ( $families as $family ) {
			$id           = sanitize_text_field( strtolower( str_replace( '-', '', $family['slug'] ) ) );
			$weights      = array();
			$fonts[ $id ] = [
				'family' => sanitize_text_field( $family['name'] ),
				'stack'  => sanitize_text_field( $family['css_stack'] ),
			];

			foreach ( $family['variations'] as $variant ) {
				$f = substr( $variant, 0, 1 );
				$l = sanitize_text_field( substr( $variant, 1 ) );
				if ( 'n' === $f ) {
					$weights[] = $l . '00';
				} elseif ( 'i' === $f ) {
					$weights[] = $l . '00i';
				}
			}
			$fonts[ $id ]['variants'] = $weights;
		}

		$typekit_fonts = [
			'projectid' => $project_id,
			'fonts'     => $fonts,
		];

		update_option( 'simplfied_font_manager_typekit_fonts', $typekit_fonts );
		return $fonts;
	}

	/**
	 * Returns sanitized customizer options.
	 *
	 * @since 1.0.0
	 *
	 * @param  Mixed                $option  Selected customizer setting value.
	 * @param  WP_Customize_Setting $setting Setting instance.
	 * @return Mixed Returns sanitized value of customizer option.
	 */
	public function sanitization( $option, $setting ) {

		$setting_id = $setting->id;
		$data_type  = '';

		foreach ( $this->setting_keys as $key => $type ) {
			if ( false !== strpos( $setting_id, $key, true ) ) {
				$data_type = $type;
				break;
			}
		}

		switch ( $type ) {
			case 'checkbox':
				$sanitized_value = $option ? 1 : '';
				break;

			case 'bool':
				$sanitized_value = true === $option ? true : false;
				break;

			case 'text':
				$sanitized_value = sanitize_text_field( $option );
				break;

			case 'array-text':
				$sanitized_value = array_map( 'sanitize_text_field', $option );
				break;

			case 'integer':
				$sanitized_value = absint( $option );
				break;

			case 'float':
				$sanitized_value = floatval( $option );
				break;

			case 'css-selectors':
				$sanitized_value = wp_strip_all_tags( str_replace( [ "'", '@' ], '', $option ), true );
				break;

			default:
				$sanitized_value = '';
				break;
		} // End switch.

		return $sanitized_value;
	}

	/**
	 * Display message on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public function admin_notices() {
		if ( SIMPLIFIED_FONT_MANAGER_VERSION !== get_option( 'simplyfm-admin-notice' ) ) {
			printf(
				'<div class="updated notice is-dismissible pp-welcome-notice">
					<p>%s <a href="%s">%s</a> %s</p>
				</div>',
				esc_html__( 'Thanks for trying/updating Simplified Font Manager. Visit', 'simplified-font-manager' ),
				esc_url( admin_url( '/customize.php?autofocus[section]=simplyfm_section' ) ),
				esc_html__( 'WordPress Customizer', 'simplified-font-manager' ),
				esc_html__( 'for fonts customization.', 'simplified-font-manager' )
			);

			/* Delete transient, only display this notice once. */
			update_option( 'simplyfm-admin-notice', SIMPLIFIED_FONT_MANAGER_VERSION );
		}
	}

	/**
	 * Returns the instance of this class.
	 *
	 * @since  1.0.0
	 *
	 * @return object Instance of this class.
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Backend::init();
