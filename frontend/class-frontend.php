<?php
/**
 * The front end specific functionality of the plugin.
 *
 * @package Simplified_Font_Manager
 * @subpackage Frontend
 * @since 1.0.0
 */

namespace Simplified_Font_Manager;

/**
 * The front-end specific functionality of the plugin.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    object
	 */
	protected static $instance = null;

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {}

	/**
	 * Register hooked functions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {

		add_action( 'customize_save_after', [ self::get_instance(), 'customize_save_after' ] );
		add_action( 'wp_head', [ self::get_instance(), 'frontend_css' ], 102 );
		add_action( 'wp_enqueue_scripts', [ self::get_instance(), 'frontend_fonts' ] );
		add_filter( 'wp_resource_hints', [ self::get_instance(), 'resource_hints' ], 10, 2 );
	}

	/**
	 * Enqueue front-end css.
	 *
	 * @since  1.0.0
	 */
	public function frontend_css() {

		$front = $this->get_frontend_data();
		if ( false === $front ) {
			return;
		}

		$css = $front['style'];
		if ( ! $css ) {
			return;
		}

		?>
		<style type="text/css" id="simplyfm-font-css">
			<?php echo wp_strip_all_tags( $css, true ); ?>
		</style>
		<?php
	}

	/**
	 * Enqueue fonts to the front-end.
	 *
	 * @since  1.0.0
	 */
	public function frontend_fonts() {

		$front = $this->get_frontend_data();
		if ( false === $front ) {
			return;
		}

		$fonts = $front['fonts'];
		if ( ! $fonts ) {
			return;
		}

		// Google fonts.
		$gfonts     = [];
		$gfonts_url = '';
		$typekit    = false;
		foreach ( $fonts as $font ) {
			if ( false === strpos( $font['category'], 'goo' ) ) {
				if ( false !== strpos( $font['category'], 'typekit' ) ) {
					$typekit = true;
				}
				continue;
			}

			if ( isset( $font['weights'] ) && $font['weights'] && is_array( $font['weights'] ) ) {
				$gfonts[] = $font['name'] . ':' . implode( ',', $font['weights'] );
			} else {
				$gfonts[] = $font['name'];
			}
		}

		if ( ! empty( $gfonts ) ) {
			$gfonts_url = add_query_arg(
				[ 'family' => rawurlencode( implode( '|', $gfonts ) ) ],
				'https://fonts.googleapis.com/css'
			);
		}

		if ( $gfonts_url ) {
			wp_enqueue_style( 'simplyfm-google-fonts', esc_url( $gfonts_url ), [], SIMPLIFIED_FONT_MANAGER_VERSION );
		}

		if ( $typekit ) {
			$adv = get_option( 'simplfied_font_manager_adv_options' );
			if ( $adv && isset( $adv['typekit'] ) ) {
				$typekit_uri = 'https://use.typekit.net/' . $adv['typekit'] . '.css';
				wp_enqueue_style( 'simplyfm-typekit-fonts', esc_url( $typekit_uri ), [], SIMPLIFIED_FONT_MANAGER_VERSION );
			}
		}
	}

	/**
	 * Generate data for front-end.
	 *
	 * @since  1.0.0
	 *
	 * @return array|false.
	 */
	public function get_frontend_data() {

		$data = get_option( 'simplified_font_manager_front_end' );
		if ( $data ) {
			return $data;
		}

		$options       = get_option( 'simplfied_font_manager_options' );
		$theme_options = apply_filters( 'simplified_font_manager_theme_options', false );
		$theme_options = $this->prepare_theme_options( $theme_options );
		$use_defaults  = get_option( 'simplfied_font_manager_theme_first_time' );
		if ( false === $use_defaults ) {
			if ( false === $options && false !== $theme_options ) {
				update_option( 'simplfied_font_manager_options', $theme_options );
				update_option( 'simplfied_font_manager_theme_first_time', true );
				$options = get_option( 'simplfied_font_manager_options' );
			} elseif ( false !== $options ) {
				update_option( 'simplfied_font_manager_theme_first_time', true );
			}
		}
		if ( false === $options ) {
			return false;
		}

		$options_data = [];

		$style = $this->get_styles( $options );
		$fonts = $this->get_fonts( $options );

		$options_data['style'] = $style ? $style : false;
		$options_data['fonts'] = $fonts ? $fonts : false;

		update_option( 'simplified_font_manager_front_end', $options_data );
		return $options_data;
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
	 * Generate data for front-end.
	 *
	 * @since  1.0.0
	 *
	 * @param array $options Saved customizer options.
	 * @return string
	 */
	public function get_styles( $options ) {

		$css    = '';
		$mobile = 768;
		$tablet = 1140;

		foreach ( $options as $key => $settings ) {
			if ( ! isset( $settings['selectors'] ) || ! $settings['selectors'] ) {
				continue;
			}

			$imp = isset( $settings['enforcestyle'] ) && $settings['enforcestyle'] ? '!important' : '';

			$css .= sprintf( '%s {', wp_strip_all_tags( $settings['selectors'] ) );

			$keys = [
				'family'        => 'font-family',
				'fontweight'    => 'font-weight',
				'fontstyle'     => 'font-style',
				'texttransform' => 'text-transform',
				'lineheight'    => 'line-height',
			];
			foreach ( $keys as $val => $rule ) {
				if ( isset( $settings[ $val ] ) && $settings[ $val ] ) {
					if ( 'lineheight' === $val ) {
						$css .= sprintf( '%s: %s%s;', $rule, abs( floatval( $settings[ $val ] ) ), $imp );
					} elseif ( 'family' === $val ) {
						$font_data = $settings[ $val ];
						$font_name = $settings[ $val ][1];
						$css      .= sprintf( '%s: "%s", sans-serif %s;', $rule, wp_strip_all_tags( $font_name ), $imp );
					} else {
						$css .= sprintf( '%s: %s%s;', $rule, esc_html( $settings[ $val ] ), $imp );
					}
				}
			}

			if ( isset( $settings['letterspacing'] ) && $settings['letterspacing'] ) {
				$css .= sprintf( 'letter-spacing: %s%s%s;', floatval( $settings['letterspacing'] ), 'px', $imp );
			}

			if ( isset( $settings['fsdesktop'] ) && $settings['fsdesktop'] ) {
				$unit = isset( $settings['fudesktop'] ) && $settings['fudesktop'] ? esc_html( $settings['fudesktop'] ) : 'px';
				$css .= sprintf( 'font-size: %s%s%s;', absint( $settings['fsdesktop'] ), $unit, $imp );
			}

			$css .= '}';

			if ( isset( $settings['fstablet'] ) && $settings['fstablet'] ) {
				$unit  = isset( $settings['futablet'] ) && $settings['futablet'] ? esc_html( $settings['futablet'] ) : 'px';
				$csst  = sprintf( '%s {', wp_strip_all_tags( $settings['selectors'] ) );
				$csst .= sprintf( 'font-size: %s%s%s;', absint( $settings['fstablet'] ), $unit, $imp );
				$csst .= '}';
				$csst  = sprintf( '@media only screen and (max-width: %spx){%s}', $tablet, $csst );
				$css  .= $csst;
			}

			if ( isset( $settings['fsmobile'] ) && $settings['fsmobile'] ) {
				$unit  = isset( $settings['fumobile'] ) && $settings['fumobile'] ? esc_html( $settings['fumobile'] ) : 'px';
				$cssm  = sprintf( '%s {', wp_strip_all_tags( $settings['selectors'] ) );
				$cssm .= sprintf( 'font-size: %s%s%s;', absint( $settings['fsmobile'] ), $unit, $imp );
				$cssm .= '}';
				$cssm  = sprintf( '@media only screen and (max-width: %spx){%s}', $mobile, $cssm );
				$css  .= $cssm;
			}
		}

		return $css;
	}

	/**
	 * Generate fonts data for front-end.
	 *
	 * @since  1.0.0
	 *
	 * @param array $options Saved customizer options.
	 * @return string
	 */
	public function get_fonts( $options ) {

		$font_stack = [];

		foreach ( $options as $key => $settings ) {

			if ( ! isset( $settings['family'] ) || ! $settings['family'] ) {
				continue;
			}

			$font_data = $settings['family'];
			$font_id   = $font_data[0];
			$font_name = $font_data[1];
			$font_cat  = $font_data[2];
			if ( isset( $font_stack[ $font_id ] ) ) {
				if ( isset( $settings['weights'] ) && $settings['weights'] ) {
					$font_stack[ $font_id ]['weights'] = array_unique( array_merge( $font_stack[ $font_id ]['weights'], array_map( 'esc_html', $settings['weights'] ) ) );
				}
			} else {
				$font_stack[ $font_id ]['name']     = esc_html( $font_name );
				$font_stack[ $font_id ]['category'] = esc_html( $font_cat );
				if ( isset( $settings['weights'] ) && $settings['weights'] ) {
					$font_stack[ $font_id ]['weights'] = array_map( 'esc_html', $settings['weights'] );
				}
			}
		}

		return $font_stack;
	}

	/**
	 * Delete front-end controls.
	 *
	 * @since 1.0.0
	 */
	public function customize_save_after() {

		delete_option( 'simplified_font_manager_front_end' );
	}

	/**
	 * Add preconnect for Google Fonts.
	 *
	 * This function incorporates code from Twenty Seventeen WordPress Theme,
	 * Copyright 2016-2017 WordPress.org. Twenty Seventeen is distributed
	 * under the terms of the GNU GPL.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $urls           URLs to print for resource hints.
	 * @param string $relation_type  The relation type the URLs are printed.
	 * @return array $urls           URLs to print for resource hints.
	 */
	public function resource_hints( $urls, $relation_type ) {
		if ( wp_style_is( 'simplyfm-google-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
			$urls[] = array(
				'href' => 'https://fonts.gstatic.com',
				'crossorigin',
			);
		}

		return $urls;
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

Frontend::init();
