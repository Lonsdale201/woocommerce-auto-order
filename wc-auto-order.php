<?php
/*
Plugin Name: WooCommerce Auto Order
Plugin URI: https://github.com/Lonsdale201/woocommerce-auto-order
Description: Automates order placements within WooCommerce
Version: 1.0
Author: Soczó Kristóf - HelloWP!
Author URI: https://hellowp.io/hu/
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}


class Auto_Order {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_auto_order_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
        add_action('wp_ajax_search_users', array( $this, 'search_users' ));  

        $this->check_woocommerce_dependency();

        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;
        }
    }

    public function enqueue_scripts_styles($hook) {

        if ($hook != 'woocommerce_page_auto-order') {
            return;  
        }

        wp_enqueue_script( 'auto-order-js', plugin_dir_url( __FILE__ ) . 'assets/select-fields.js', array('jquery', 'select2'), null, true );
        wp_enqueue_style( 'auto-order-css', plugin_dir_url( __FILE__ ) . 'assets/auto-order.css' );          
        wp_enqueue_style('woocommerce_admin_styles');
        wp_enqueue_style('select2', WC()->plugin_url() . '/assets/css/select2.css');

         // Loading WooCommerce admin JavaScript files
         wp_enqueue_script('wc-admin');
         wp_enqueue_script('wc-enhanced-select'); 
    
    }

    public function check_woocommerce_dependency() {
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_dependency_notice' ) );
        }
    }

    public function woocommerce_dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e( 'WooCommerce Auto Order plugin requires WooCommerce to be activated.', 'auto-order' ); ?></p>
        </div>
        <?php
    }

    public function register_auto_order_menu() {
        if ( ! current_user_can('administrator') ) {
            return;
        }
        add_submenu_page(
            'woocommerce',
            'Auto Order',
            'Auto Order',
            'manage_woocommerce',
            'auto-order',
            array( $this, 'auto_order_page_callback' )
        );
    }
    
    public function auto_order_page_callback() {
        $message = '';  // Initialize message variable
        if ( ! current_user_can('administrator') ) {
            wp_die( __( 'Nincs megfelelő jogosultságod az oldal megtekintéséhez.', 'auto-order' ) );
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle the form submission
            // $this->handle_order_submission();
            $message = $this->handle_order_submission();
        }
    
        // Output the header and description
        echo '<div class="auto-order-header">';
        echo '<h1>WooCommerce Auto Order</h1>';
        echo '<p>By providing the information, you can place an order on behalf of the selected user.</p>';
        echo '</div>';  // Header div close

        // Output the form
        echo '<div class="auto-order-form-wrapper">';
        echo '<form method="post">';
        wp_nonce_field('auto_order_nonce_action', 'auto_order_nonce');
          
        // Product search field
        echo '<div class="form-group">';
        echo '<label for="product-select">' . __('Product:', 'auto-order') . '</label><br>';
        echo '<select class="wc-product-search" name="product_id" data-placeholder="' . esc_attr__('Please choose product', 'auto-order') . '" data-action="woocommerce_json_search_products_and_variations" data-allow_clear="true"></select><br>';
        echo '</div>';
          
        // User search field 
        echo '<div class="form-group">';
        echo '<label for="user-select">' . __('User:', 'auto-order') . '</label><br>';
        echo '<select id="user-select" name="user_id" style="width: 100%;"></select><br>';
        echo '</div>';
        
        // Quanty field
        echo '<div class="form-group">';
        echo '<label for="quantity">' . __('Quantity:', 'auto-order') . '</label><br>';
        echo '<input type="number" id="quantity" name="quantity" min="1" value="1"><br>';
        echo '</div>';

        // Private note field
        echo '<div class="form-group">';
        echo '<label for="private-note">' . __('Private Note:', 'auto-order') . '</label><br>';
        echo '<textarea id="private-note" name="private_note" rows="4" cols="50"></textarea><br>';
        echo '</div>';
    
        // Order status
        echo '<div class="form-group">';
        echo '<label for="order-status">' . __('Order Status:', 'auto-order') . '</label><br>';
        echo '<select id="order-status" name="order_status">';

        $order_statuses = wc_get_order_statuses();
        foreach ($order_statuses as $status_slug => $status_name) {
            $status_slug = str_replace('wc-', '', $status_slug);  
            echo '<option value="' . esc_attr($status_slug) . '">' . esc_html($status_name) . '</option>';
        }

        echo '</select><br>';
        echo '</div>';
        
        // send button
        echo '<div class="form-group">';
        echo '<input type="submit" value="' . __('Order', 'auto-order') . '" class="button button-primary">';
        echo '</div>';
        
        echo '</form>';
        echo $message;  
        echo '</div>';  // wrap close
    }

    private function handle_order_submission() {
        $message = '';  // Initialize message variable
        if ( ! current_user_can('administrator') ) {
            wp_die( __( 'Nincs megfelelő jogosultságod a rendelés feldolgozásához.', 'auto-order' ) );
        }

        if (!wp_verify_nonce($_POST['auto_order_nonce'], 'auto_order_nonce_action')) {
            die('Invalid request.');  // Invalid request if nonce can't be verified
        }
        $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
        $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $order_status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : '';
    
        // Ensure the user and product exist
        if ( ! get_user_by( 'id', $user_id ) || ! wc_get_product( $product_id ) ) {
            echo 'Hibás felhasználó vagy termék ID.';
            return;
        }

        // Get user data
        $user = get_userdata($user_id);
        $user_meta = get_user_meta($user_id);
    
        // Prepare address data
        $address = array(
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'company'    => '',
            'email'      => $user->user_email,
            'phone'      => isset($user_meta['billing_phone'][0]) ? $user_meta['billing_phone'][0] : '',
            'address_1'  => isset($user_meta['billing_address_1'][0]) ? $user_meta['billing_address_1'][0] : '',
            'address_2'  => isset($user_meta['billing_address_2'][0]) ? $user_meta['billing_address_2'][0] : '',
            'city'       => isset($user_meta['billing_city'][0]) ? $user_meta['billing_city'][0] : '',
            'state'      => isset($user_meta['billing_state'][0]) ? $user_meta['billing_state'][0] : '',
            'postcode'   => isset($user_meta['billing_postcode'][0]) ? $user_meta['billing_postcode'][0] : '',
            'country'    => isset($user_meta['billing_country'][0]) ? $user_meta['billing_country'][0] : ''
        );
        // Create a new order
        $order = wc_create_order();
    
        // Set order customer
        $order->set_customer_id( $user_id );

        // Add a spec note for the order (always)
        $order->add_order_note( __('Ezt a terméket az Auto Order bővítmény által lett megrendelve.', 'auto-order') );

        // Private note adding if exist
        if ( ! empty($_POST['private_note']) ) {
            $note_text = 'Private Note: ' . sanitize_textarea_field( $_POST['private_note'] );
            $order->add_order_note( $note_text, false, true );
        }
        
        // Add product to the order
        $order->add_product( wc_get_product( $product_id ), $quantity );

        $order->set_status( $order_status );
        // Set address for billing and shipping
        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );
    
        // Calculate totals
        $order->calculate_totals();
    
        if ( $order->save() ) {
            $message = '<div class="success-message">A rendelés sikeresen leadva!</div>';
        } else {
            $message = '<div class="error-message">Hiba történt a rendelés leadása során.</div>';
        }
    
        return $message;  // Return the message
    }

    public function search_users() {
        if ( ! current_user_can('administrator') ) {
            wp_die( __( 'Nincs megfelelő jogosultságod a felhasználókeresés végrehajtásához.', 'auto-order' ) );
        }
        $search_term = $_GET['q'];
    
        $user_query = new WP_User_Query(array(
            'search' => '*' . esc_attr($search_term) . '*',
            'search_columns' => array('user_login', 'user_nicename', 'user_email'),
        ));
    
        $users = $user_query->get_results();
        $user_data = array();
    
        foreach ($users as $user) {
            $user_data[] = array(
                'id' => $user->ID,
                'text' => $user->display_name . ' (' . $user->user_email . ')'  
            );
        }
    
        echo json_encode($user_data);
        wp_die();
    }

    
}


new Auto_Order();



