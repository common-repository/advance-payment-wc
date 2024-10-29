<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Advance_Payment_Settings')) {

   class Advance_Payment_Settings {

        private static $advance_payment = 0; // Declare advance_payment as a class property
        private static $duepayment = 0;   // Declare Due payment as a class property
        private static $advance_payment_enabled = false;
            
        /**
         * Initialize the settings
         */
      
        public static function init() {

            self::$advance_payment_enabled = get_option('enable_advanced_payment', 'no') === 'yes';
            
            add_action('admin_notices', array(__CLASS__, 'check_cash_on_delivery_enabled'));

            add_action( 'woocommerce_cart_calculate_fees', array(__CLASS__, 'advance_payment_on_cart_total'), 10, 1 );    
            add_filter('woocommerce_settings_tabs_array', array(__CLASS__, 'settings_tab_add'), 50);
            add_action('woocommerce_settings_advanced_payment', array(__CLASS__, 'output_settings'));
            add_action('woocommerce_update_options_advanced_payment', array(__CLASS__, 'save_settings'));
            add_action('woocommerce_checkout_order_processed', array(__CLASS__,'save_checout_processing_fee'));
            add_filter('manage_edit-shop_order_columns', array(__CLASS__,'custom_add_order_columns'));
            add_action('manage_shop_order_posts_custom_column', array(__CLASS__,'custom_populate_order_columns'));
         } 

        /**
         * 
         * plugin check enable 
         * 
         * 
         */

        public static function pluign_check_enabled() {

           return self::$advance_payment_enabled;
        }
 
        /**
         * 
         * Cart page Advance payment show
         * @param $cart_object 
         * 
         */
        
        public static function advance_payment_on_cart_total( $cart_object ) {
        if (!self::$advance_payment_enabled) {
            return;
          } 
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
            
        $advance_payment_type = get_option('advance_payment_type', 'fixed');
        $advance_payment_value = get_option('advance_payment_value', 0);
    
        // Get the cart subtotal
        $cart_subtotal = WC()->cart->subtotal;
        if ($advance_payment_type === 'percentage') {
            self::$advance_payment = $cart_subtotal * $advance_payment_value / 100;
            } else {
            self::$advance_payment = $advance_payment_value;
        }
        // Calculate the advance payment fee
        $advance_payment_fee = -self::$advance_payment;

        // Add the advance payment fee to the cart
            if ($advance_payment_type === 'percentage') {
                WC()->cart->add_fee('Advance Payment', $advance_payment_fee);
            } else {
                WC()->cart->add_fee('Advance Payment', $advance_payment_fee);
            }
        }
        
       /**
         * Cash on delivery deactivate 
         * 
        */

       public static function check_cash_on_delivery_enabled() {

            $payment_gateways = WC()->payment_gateways->payment_gateways();

            if (isset($payment_gateways['cod']) && $payment_gateways['cod']->enabled === 'yes') {
                echo '<p><div class="notice notice-error">'. esc_html('Please deactivate the "Cash on Delivery" payment gateway').'</p></div>';
            }
        }
        
        /*public static function check_cash_on_delivery_enabled() {

            $payment_gateways = WC()->payment_gateways->payment_gateways();
            if (isset($payment_gateways['cod']) && $payment_gateways['cod']->enabled === 'yes') {
                $wcmessage = esc_html_e('Please deactivate the "Cash on Delivery" payment gateway.', 'advance-payment-wc');
                echo '<div class="notice notice-error"><p>' . $wcmessage . '</p></div>';
            }
        }*/


        /**
         * save data from checkout page 
         * @param $order_id
         *
        */

        public static function  save_checout_processing_fee($order_id) {
          if (!self::$advance_payment_enabled) {
            return;
          } 
            $advance_payment_fee = self::$advance_payment;
            // Add the advance payment fee to the cart
            if ($advance_payment_type === 'percentage') {
                WC()->cart->add_fee('Advance Payment', $advance_payment_fee);
                update_post_meta($order_id, '_advance_payment', $advance_payment_fee);
            } else {
                WC()->cart->add_fee('Advance Payment', $advance_payment_fee);
                update_post_meta($order_id, '_advance_payment', $advance_payment_fee);
            }
        }


         /**
         * Custom colmun create in order page
         * @param $columns
         * 
        */
        
        public static function custom_add_order_columns($columns) {
            if (!self::$advance_payment_enabled) {
            return $columns;
           } 
            $new_columns = array();
            
            foreach ($columns as $key => $column) {
                $new_columns[$key] = $column;
        
                if ($key === 'order_status') {
                    $new_columns['sub_total'] = esc_html_e('Subtotal', 'advance-payment-wc');
                    $new_columns['advance_payment'] = esc_html_e('Advance Payment', 'advance-payment-wc');
                    
                }
            }
        
            return $new_columns;
        }

        /**
         * Custom colmun in order page advacne payment and subtotal show. 
         * @param $column
         * 
        */
        public static function custom_populate_order_columns($column) {
                
          if (!self::$advance_payment_enabled) {
            return;
           }    
            global $post;

            if ($column === 'sub_total') {
                $order = wc_get_order( $post->ID);
                $subtotal = $order->get_subtotal();
                //echo wc_price( $subtotal );
                echo esc_html( wc_price( $subtotal ) );
            } 

            if ($column === 'advance_payment') {
                $advnacefee = get_post_meta($post->ID, '_advance_payment', true);
                echo esc_html( wc_price($advnacefee));    
            }            
        }

        /**
         * wooCommerce setting Add the settings tab
         *
         * @param array $tabs
         * @return array
         */
        public static function settings_tab_add($tabs) {
            $tabs['advanced_payment'] = __('Advanced Payment', 'advance-payment-wc');
            return $tabs;
        }

        /**
         * Output the settings
         */
        public static function output_settings() {
            woocommerce_admin_fields(self::get_settings());
        }

        /**
         * Save the settings
         */
        public static function save_settings() {
            woocommerce_update_options(self::get_settings());
        }

        /**
         * Get the settings fields
         *
         * @return array
         */
        public static function get_settings() {
            $enabled = get_option('enable_advanced_payment', 'no');
            $settings = array(

              'section_title' => array(
                    'name' => __('Advanced Payment Settings', 'advance-payment-wc'),
                    'type' => 'title',
                    'desc' => 'Enable this plugin',
                    'id'   => 'advance_payment_section_title'
                ),
                  
               'enable_advanced_payment' => array(
                    'name'     => __('Enable Advanced Payment', 'advance-payment-wc'),
                    'type'     => 'checkbox',
                    'desc'     => __('Enable Advance Payment Settings.', 'advance-payment-wc'),
                    'id'       => 'enable_advanced_payment',
                    'default'  => 'no',
                    'value'    => $enabled, // Set the initial value from the option
                ),

                'advance_payment_type' => array(
                    'name'     => __('Advanced Payment Type', 'advance-payment-wc'),
                    'type'     => 'select',
                    'desc'     => __('Choose whether the advanced payment amount is fixed or percentage-based.', 'advance-payment-wc'),
                    'id'       => 'advance_payment_type',
                    'options'  => array(
                        'fixed'     => __('Fixed Amount', 'advance-payment-wc'),
                        'percentage' => __('Percentage', 'advance-payment-wc'),
                    ),
    
                ),
                'advance_payment_value' => array(
                    'name'     => __('Advanced Payment Value', 'advance-payment-wc'),
                    'type'     => 'number',
                    'desc'     => __('Enter the advanced payment value.', 'advance-payment-wc'),
                    'id'       => 'advance_payment_value',
                    'class'    => 'wc_input_price',
                ),
                'section_end' => array(
                    'type' => 'sectionend',
                    'id'   => 'advance_payment_section_end'
                ),
            );

            return apply_filters('woocommerce_advance_payment_settings', $settings);
        }
    }    
    Advance_Payment_Settings::init();
}