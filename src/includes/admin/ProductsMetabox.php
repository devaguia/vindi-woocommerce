<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Meta_Box_Coupon_Data Class updated with custom fields.
 */
class ProductsMetaBox 
{
    public function __construct()
    {
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'woocommerce_variable_subscription_custom_fields'], 10, 3 );
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'woocommerce_subscription_custom_fields'] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'filter_woocommerce_product_custom_fields' ] );
    }
    
    public function woocommerce_subscription_custom_fields ()
    {
        global $woocommerce, $post;

        if ( isset( $post->ID ) ) {
            $product = wc_get_product( $post->ID );

            if ( $product->is_type( 'subscription' ) || $post->post_status === 'auto-draft' ) {

                if( $this->check_credit_payment_active( $woocommerce ) ) {
                    $this->show_meta_custom_data( $post->ID );
                }
            }
        }
    }

    public function woocommerce_variable_subscription_custom_fields ( $loop, $variation_data, $variation )
    {
        global $woocommerce;

        if ( isset( $variation->ID ) ) {

            $product = wc_get_product( $variation->ID );
            if ( $product->is_type( 'subscription_variation' ) || $variation->post_status === 'auto-draft' ) {

                if( $this->check_credit_payment_active( $woocommerce ) ) {
                    $this->show_meta_custom_data( $variation->ID );
                }
            }
        }
    }

    private function show_meta_custom_data( $subscription_id )
    {
        echo '<div class="product_custom_field">';

        woocommerce_wp_text_input(
            array(
                'id'    => "vindi_max_credit_installments_$subscription_id",
                'value' => get_post_meta( $subscription_id, "vindi_max_credit_installments_$subscription_id", true ),
                'label' => __( 'Máximo de parcelas com cartão de crédito', 'woocommerce' ),
                'type'  => 'number',
                'description' => sprintf( 'Esse campo controla a quantidade máxima de parcelas para compras com cartão de crédito. <strong> %s </strong>',
                    '(Somente para assinaturas anuais!)'
                ),
                "desc_tip"    => true,
                'custom_attributes' => array(
                    'max' => '12',
                    'min' => '0'
                )
            )
        );
        
        echo '</div>';
    }

    public function filter_woocommerce_product_custom_fields( $post_id )
    {
        $product = wc_get_product( $post_id );

        if ( $product->is_type( 'variable-subscription' ) ) {
            $this->handle_saving_variable_subscription( $product );
        }

        if ( $product->is_type( 'subscription' ) ) {
            $this->handle_saving_simple_subscription( $product );
        }

    }

    private function handle_saving_variable_subscription( $product )
    {
        $variations = array_reverse( $product->get_children() );
        $periods    = isset( $_POST['variable_subscription_period'] ) ? array_filter( $_POST['variable_subscription_period'] ) : false;
        $intervals  = isset( $_POST['variable_subscription_period_interval'] ) ? array_filter( $_POST['variable_subscription_period_interval'] ) : 1;

        foreach( $variations as $key => $variation ) {
            if ( isset( $_POST["vindi_max_credit_installments_$variation"] ) ) {
                $installments = filter_var( $_POST["vindi_max_credit_installments_$variation"] );
                if ( isset( $periods[$key] ) && isset( $intervals[$key] ) ) {
                    $this->save_woocommerce_product_custom_fields( $variation, $installments, $periods[$key], $intervals[$key] );
                }
            }
        }
    }

    private function handle_saving_simple_subscription( $product )
    {
        $post_id = $product->get_id();
        
        $period        = isset( $_POST['_subscription_period'] ) ? sanitize_text_field( $_POST['_subscription_period'] ) : false;
        $interval      = isset( $_POST['_subscription_period_interval'] ) ? intval( $_POST['_subscription_period_interval'] ) : 1;
        $installments  = isset( $_POST["vindi_max_credit_installments_$post_id"] ) ? intval( $_POST["vindi_max_credit_installments_$post_id"] ) : 1;
        
        if ( $period && $interval && $installments ) {
            $this->save_woocommerce_product_custom_fields( $post_id, $installments, $period, $interval );
        }
    }

    private function save_woocommerce_product_custom_fields( $post_id, $installments, $period, $interval )
    {
        if ( $period ) {
            if ( $period === 'year' ) {
                if ( $installments > 12 ) $installments = 12;
            }

            if(  $period === 'month' ) {
                if ( $installments > $interval ) {
                    $installments = $interval;
                }
            }
        }

        update_post_meta( $post_id, "vindi_max_credit_installments_$post_id", $installments );
    }

    private function check_credit_payment_active( $wc )
    {
        if ( $wc ) {
            $gateways = $wc->payment_gateways->get_available_payment_gateways();

            foreach ( $gateways as $key => $gateway ) {
                if ( $key === 'vindi-credit-card' ) return true;
            }
        }
    }
}