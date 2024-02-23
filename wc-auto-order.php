<?php

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Order {
    public function __construct() {
        add_action('admin_menu', array($this, 'register_auto_order_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
        add_action('wp_ajax_search_users', array($this, 'search_users'));  
    }
    

    public function enqueue_scripts_styles($hook) {
        if ($hook != 'woocommerce_page_auto-order') {
            return;
        }

        wp_enqueue_script('auto-order-js', plugin_dir_url(__FILE__) . 'assets/select-fields.js', array('jquery', 'select2'), null, true);
        wp_enqueue_style('auto-order-css', plugin_dir_url(__FILE__) . 'assets/auto-order.css');          
        wp_enqueue_style('woocommerce_admin_styles');
        wp_enqueue_style('select2', WC()->plugin_url() . '/assets/css/select2.css');

        wp_enqueue_script('wc-admin');
        wp_enqueue_script('wc-enhanced-select'); 
    }

   

    public function register_auto_order_menu() {
        if (!current_user_can('administrator')) {
            return;
        }
        add_submenu_page(
            'woocommerce',
            'Auto Order',
            'Auto Order',
            'manage_woocommerce',
            'auto-order',
            array($this, 'auto_order_page_callback')
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
          
      // Product search field with instructions
        echo '<div class="form-group">';
        echo '<label for="product-select">' . __('Product:', 'auto-order') . '</label><br>';
        echo '<select class="wc-product-search" name="product_id" data-placeholder="' . esc_attr__('Please choose product', 'auto-order') . '" data-action="woocommerce_json_search_products_and_variations" data-allow_clear="true"></select><br>';
        echo '<small style="display: block; margin-top: 8px;">' . __('Required to specify the product.', 'auto-order') . '</small>';
        echo '</div>';

        // User search field with instructions
        echo '<div class="form-group">';
        echo '<label for="user-select">' . __('User:', 'auto-order') . '</label><br>';
        echo '<select id="user-select" name="user_id" style="width: 100%;"></select><br>';
        echo '<small style="display: block; margin-top: 8px;">' . __('To place an order on behalf of an existing user, select from the list.', 'auto-order') . '</small>';
        echo '</div>';

       // New user email field with instructions
        echo '<div class="form-group">';
        echo '<label for="new-user-email">' . __('New User Email:', 'auto-order') . '</label><br>';
        echo '<input type="email" id="new-user-email" name="new_user_email" ><br>';
        echo '<small style="display: block; margin-top: 8px;">' . __('If you want to place an order for a non-existent user, you need to add their email. The plugin will create a new user before placing the order.', 'auto-order') . '</small>';
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

        // Note for the customers field
        echo '<div class="form-group">';
        echo '<label for="customer-note">' . __('Note for the Customers:', 'auto-order') . '</label><br>';
        echo '<textarea id="customer-note" name="customer_note" rows="4" cols="50"></textarea><br>';
        echo '</div>';
    
        // Order status
        echo '<div class="form-group">';
        echo '<label for="order-status">' . __('Order Status:', 'auto-order') . '</label><br>';
        echo '<select id="order-status" name="order_status">';

        $default_status = 'completed'; //default
        $order_statuses = wc_get_order_statuses();
        foreach ($order_statuses as $status_slug => $status_name) {
            $status_slug = str_replace('wc-', '', $status_slug);  
            $selected = ($status_slug == $default_status) ? 'selected' : '';
            echo '<option value="' . esc_attr($status_slug) . '" ' . $selected . '>' . esc_html($status_name) . '</option>';
        }
        
        echo '</select><br>';

        // Date field
        echo '<div class="form-group">';
        echo '<label for="order-date">' . __('Order Date:', 'auto-order') . '</label><br>';
        echo '<input type="date" id="order-date" name="order_date"><br>';
        echo '</div>';

        // Zero Price field
        echo '<div class="form-group" style="display: flex; align-items: center; margin-bottom: 0px;">';
        echo '<input type="checkbox" id="zero-price" name="zero_price" value="1" style="margin-right: 6px;">';
        echo '<label for="zero-price" style="flex-grow: 1;">' . __('Zero Price', 'auto-order') . '</label><br>';
        echo '</div>';
        echo '<span style="margin-left: 0px;">' . __('Check this box to place the order at zero cost.', 'auto-order') . '</span>';

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
        $message = '';  
        if (!current_user_can('administrator')) {
            wp_die(__('Nincs megfelelő jogosultságod a rendelés feldolgozásához.', 'auto-order'));
        }
    
        if (!wp_verify_nonce($_POST['auto_order_nonce'], 'auto_order_nonce_action')) {
            die('Invalid request.');
        }
    
        $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $order_status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : '';
        $order_date = isset($_POST['order_date']) ? sanitize_text_field($_POST['order_date']) : '';
        $zero_price = isset($_POST['zero_price']) && $_POST['zero_price'] == '1';

        $new_user_email = isset($_POST['new_user_email']) ? sanitize_email($_POST['new_user_email']) : '';
        $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';

        // meassges

        $product = wc_get_product($product_id);
        $product_name = $product ? $product->get_name() : 'Ismeretlen termék';
        $product_edit_link = get_edit_post_link($product_id);
    
        // Kezdeti ellenőrzés, hogy a termék létezik-e
        if (!wc_get_product($product_id)) {
            echo 'Hibás termék ID.';
            return;
        }
    
        // $user_id = '';
        // Ha az új felhasználó email címét megadták
        if (!empty($new_user_email)) {
            if (email_exists($new_user_email)) {
                $user_id = email_exists($new_user_email);
            } else {
                $user_id = wp_create_user($new_user_email, wp_generate_password(), $new_user_email);
                if (is_wp_error($user_id)) {
                    return 'Hiba történt az új felhasználó regisztrálásakor: ' . $user_id->get_error_message();
                }
                $user = new WP_User($user_id);
                $user->set_role('subscriber');
            }
        } else if (empty($user_id) || !get_user_by('id', $user_id)) {
            return 'Hibás vagy hiányzó felhasználói azonosító.';
        }

        // Verify if a user ID is available after all checks
        if (empty($user_id)) {
            echo 'Hibás felhasználó vagy email cím.';
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

        $order = wc_create_order();
        $order->set_customer_id($user_id);
        $order->add_order_note(__('This order Created by the Auto Order plugin.', 'auto-order'));

        if (!empty($_POST['private_note'])) {
            $note_text = 'Private Note: ' . sanitize_textarea_field($_POST['private_note']);
            $order->add_order_note($note_text, false, true);
        }

        if (!empty($_POST['customer_note'])) {
            $customer_note = sanitize_textarea_field($_POST['customer_note']);
            // Add the note to the order
            $order->add_order_note(
                $customer_note,
                1 
            );
        }

        if (!empty($order_date)) {
            $order->set_date_created($order_date);
        }

        $order->add_product(wc_get_product($product_id), $quantity);

        if ($zero_price) {
            foreach ($order->get_items() as $item_id => $item) {
                $item->set_subtotal(0);
                $item->set_total(0);
                $order->update_item($item_id, $item);
            }
            $order->set_total(0);
            $order->set_shipping_total(0);
            $order->set_cart_tax(0);
            $order->set_shipping_tax(0);
        }

        $order->set_status($order_status);
        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');
        $order->calculate_totals();

        $order->save();
        
        if ($order->save()) {
            // A rendelés szerkesztői linkjének lekérdezése
            $order_edit_link = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
        
            if (!empty($new_user_email) && $user_id) {
                $user_edit_link = esc_url(add_query_arg('user_id', $user_id, admin_url('user-edit.php')));
                $message = "<div class='success-message'>Order Successfully Created. Order: <a href='{$order_edit_link}' target='_blank'>#" . $order->get_id() . "</a><br>New user Successfully registered <a href='{$user_edit_link}' target='_blank'>{$new_user_email}</a></div>";
            } else if (!empty($user_id)) {
                $message = "<div class='success-message'>Order Successfully Created. Order: <a href='{$order_edit_link}' target='_blank'>#" . $order->get_id() . "</a></div>";
            } else {
                $message = "<div class='error-message'>There was an error in the order process.</div>";
            }
        } else {
            $message = "<div class='error-message'>An error occurred when placing the order.</div>";
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


// new Auto_Order();




