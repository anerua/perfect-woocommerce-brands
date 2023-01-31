<?php

namespace QuadLayers\Perfect_WooCommerce_Brands\Shortcodes;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

class Carousel {

	private static $atts;

	public static function carousel_shortcode( $atts ) {

		self::$atts = shortcode_atts(
			array(
				'items'           => '10',
				'items_to_show'   => '5',
				'items_to_scroll' => '1',
				'image_size'      => 'thumbnail',
				'autoplay'        => 'false',
				'arrows'          => 'false',
				'hide_empty'      => false,
				'order_by'        => 'name',
				'order'           => 'ASC',
			),
			$atts,
			'pwb-carousel'
		);

		/**
		 * Enqueue slick styles and scripts
		 */
		if ( ! wp_style_is( 'pwb-lib-slick' ) ) {
			wp_enqueue_style( 'pwb-lib-slick' );
		}
		if ( ! wp_script_is( 'pwb-lib-slick' ) ) {
			wp_enqueue_script( 'pwb-lib-slick' );
		}

		return \QuadLayers\Perfect_WooCommerce_Brands\WooCommerce::render_template(
			'carousel',
			'shortcodes',
			array(
				'slick_settings' => self::slick_settings(),
				'brands'         => self::brands_data(),
			),
			false
		);

	}

	private static function slick_settings() {

		$slick_settings = array(
			'slidesToShow'   => (int) self::$atts['items_to_show'],
			'slidesToScroll' => (int) self::$atts['items_to_scroll'],
			'autoplay'       => ( self::$atts['autoplay'] === 'true' ) ? true : false,
			'arrows'         => ( self::$atts['arrows'] === 'true' ) ? true : false,
		);

		return htmlspecialchars( json_encode( $slick_settings ), ENT_QUOTES, 'UTF-8' );

	}

	private static function brands_data() {

		$brands    = array();
		$foreach_i = 0;

		$hide_empty = ( self::$atts['hide_empty'] != 'true' ) ? false : true;

		if ( self::$atts['items'] == 'featured' ) {
			$brands_array = \QuadLayers\Perfect_WooCommerce_Brands\WooCommerce::get_brands( self::$atts['items'], 'name', 'ASC', true );
		} else {
			$brands_array = \QuadLayers\Perfect_WooCommerce_Brands\WooCommerce::get_brands( $hide_empty, self::$atts['order_by'], self::$atts['order'] );
		}
		foreach ( $brands_array as $brand ) {
			if ( self::$atts['items'] != 'featured' && $foreach_i >= (int) self::$atts['items'] ) {
				break;
			}

			$brand_id        = $brand->term_id;
			$brand_link      = get_term_link( $brand_id );
			$attachment_id   = get_term_meta( $brand_id, 'pwb_brand_image', 1 );
			$attachment_html = $brand->name;
			if ( $attachment_id != '' ) {
				$attachment_html = wp_get_attachment_image( $attachment_id, self::$atts['image_size'] );
			}

			$brands[] = array(
				'link'            => $brand_link,
				'attachment_html' => $attachment_html,
				'name'            => $brand->name,
			);

			$foreach_i++;
		}

		return $brands;

	}

}
