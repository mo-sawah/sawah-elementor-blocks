<?php
/**
 * Plugin Name: Sawah Elementor Blocks
 * Description: Custom Elementor blocks for Smart Mag Theme.
 * Version: 1.0.5
 * Author: Mohamed Sawah
 * Text Domain: sawah-blocks
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class Sawah_Elementor_Blocks {

    // Instance handling
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        // Check if Elementor is installed and active
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // 1. Register the Category "Sawah Blocks"
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_category' ] );

        // 2. Register Widgets
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // 3. Enqueue Global Scripts (Swiper)
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_global_scripts' ] );
    }

    /**
     * Create the "Sawah Blocks" Section in Elementor Panel
     */
    public function add_elementor_category( $elements_manager ) {
        $elements_manager->add_category(
            'sawah_blocks', // Slug
            [
                'title' => esc_html__( 'Sawah Blocks', 'sawah-blocks' ),
                'icon'  => 'eicon-code',
            ]
        );
    }

    /**
     * Include and Register Widgets
     */
    public function register_widgets( $widgets_manager ) {
        // 1. Slider Block
        require_once( __DIR__ . '/widget-slider.php' );
        $widgets_manager->register( new \Elementor_Sawah_Slider() );
        
        // 2. Mobile Feature Block (NEW)
        require_once( __DIR__ . '/widget-mobile-feature.php' );
        $widgets_manager->register( new \Elementor_Sawah_Mobile_Feature() );
    }

    /**
     * Enqueue Swiper (since multiple blocks might use it)
     */
    public function enqueue_global_scripts() {
        if ( ! wp_script_is( 'swiper', 'registered' ) ) {
            wp_register_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0' );
            wp_register_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true );
        }
        // We register it here, but enqueue it inside the widget only when used
    }
}

Sawah_Elementor_Blocks::instance();