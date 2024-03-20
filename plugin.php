<?php
/*
Plugin Name: WooCommerce Auto Order
Plugin URI: https://github.com/Lonsdale201/woocommerce-auto-order
Description: Automates order placements within WooCommerce
Version: 1.6
Author: Soczó Kristóf - HelloWP!
Author URI: https://github.com/Lonsdale201?tab=repositories
*/


if (!defined('ABSPATH')) {
    exit;
}

final class Auto_Order_Plugin {
    const MINIMUM_WP_VERSION = '5.4'; 
    const MINIMUM_WOOCOMMERCE_VERSION = '8.0';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_action('plugins_loaded', [$this, 'init'], 20);
    }

    public function init() {
        if (!$this->check_dependencies()) {
            return;
        }
        include_once plugin_dir_path(__FILE__) . 'wc-auto-order.php';
        new Auto_Order();
    }

    private function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_woocommerce_missing']);
            return false;
        }

        if (version_compare(WC_VERSION, self::MINIMUM_WOOCOMMERCE_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_woocommerce_version']);
            return false;
        }

        return true;
    }

    public function admin_notice_woocommerce_missing() {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>' . esc_html__('HelloWP! | WooCommerce Auto Order requires WooCommerce to be installed and active. Please install WooCommerce to continue.', 'auto-order') . '</p>';
        echo '</div>';
    }
    

    public function admin_notice_minimum_woocommerce_version() {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>' . sprintf(esc_html__('HelloWP! | WooCommerce Auto Order requires WooCommerce version %s or greater. Please update WooCommerce to continue.', 'auto-order'), self::MINIMUM_WOOCOMMERCE_VERSION) . '</p>';
        echo '</div>';
    }
    

}

Auto_Order_Plugin::instance();

add_action('before_woocommerce_init', function(){
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

