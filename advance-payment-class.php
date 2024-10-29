<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Advance_Payment')) {

    class Advance_Payment {

        /**
         * Initialize the class
         */
        public static function init() {

           $isChecked = Advance_Payment_Settings::pluign_check_enabled();
           if ($isChecked) {

            add_action('woocommerce_checkout_create_order', array(__CLASS__, 'advance_payment_wc_to_order'), 10, 2);
            add_action('woocommerce_review_order_before_payment', array(__CLASS__, 'display_advance_payment_info'));
            add_action('woocommerce_admin_order_totals_after_total', array(__CLASS__, 'display_advance_payment_details'));
            add_action('woocommerce_email_order_meta', array(__CLASS__, 'advance_payment_wc_details_to_email'), 10, 3);
            }
        
        }

        /**
         * Add advance payment to the order
         *
         * @param WC_Order $order
         * 
         */

        public static function advance_payment_wc_to_order($order) {
            $isChecked = Advance_Payment_Settings::pluign_check_enabled();
            if ($isChecked) { 
                // Check if our nonce is set.
                if (!isset($_POST['payment_information_nonce'])) {
                    return;
                }
                // Verify that the nonce is valid.
                if (!wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['payment_information_nonce'] ) ), 'payment_information_action')) {
                    return;
                }

                // Save payment information to order meta
                $payment_information = isset($_POST['payment_information']) ? sanitize_textarea_field($_POST['payment_information']) : '';
                $order->update_meta_data('payment_information', $payment_information);
                $order->save();
            }
        }


        /**
        * Display advance payment information on the checkout page
        */
        public static function display_advance_payment_info() {

           $isChecked = Advance_Payment_Settings::pluign_check_enabled();
           if ($isChecked) {

                //Display payment information box
                woocommerce_form_field('payment_information', array(
                    'type' => 'textarea',
                    'class' => array('form-row-wide'),
                    'label' => esc_html_e('Payment Information', 'advance-payment-wc'),
                    'required' => true,
                ));

                echo '</div>';
        }
       } 

        /**
         * Display advance payment details in admin order page
         *
         * 
         */
        public static function display_advance_payment_details() {

           $isChecked = Advance_Payment_Settings::pluign_check_enabled();
           if ($isChecked) {

            global $post;
            // Check if we are on an order edit page
            if (is_admin() && get_post_type($post) === 'shop_order') {
                $order = wc_get_order($post);
                $payment_information = $order->get_meta('payment_information');
                echo '<div class="advance-payment-details" style="float: left; margin-left: 10px;">';
                echo '<p><strong>'. esc_html_e('Payment Information:', 'advance-payment-wc') . '</strong> ' . esc_html($payment_information) . '</p>';
               echo '</div>';
            }        
         }
         
        } 
        /**
         * Add advance payment details to order email
         *
         * @param WC_Order $order
         * @param bool     $sent_to_admin
         * @param bool     $plain_text
         */

        public static function advance_payment_wc_details_to_email($order, $sent_to_admin, $plain_text) {

          $isChecked = Advance_Payment_Settings::pluign_check_enabled();
          if ($isChecked) {

            $advance_payment = $order->get_meta('advance_payment');
            $payment_information = $order->get_meta('payment_information');

            echo '<h2>' . esc_html_e('Advance Payment Details', 'advance-payment-wc') . '</h2>';
            echo '<p><strong>' . esc_html_e('Advance Payment Amount:', 'advance-payment-wc') . '</strong> ' . esc_html(wc_price($advance_payment)) . '</p>';
            echo '<p><strong>' . esc_html_e('Payment Information:', 'advance-payment-wc') . '</strong> ' . esc_html($payment_information) . '</p>';
          }
        }   
   } 
    Advance_Payment::init();
    
}