<?php

class Setlr_Pricing {
    
    var $project_id;
    
    public function __construct( $project_id ) {
        
        $this->project_id = absint( $project_id );
    }
    
    
    public static function render_project_quote_form( $project_id ) {
        //echo $project_id;exit;
        $setlr_pricing = new Setlr_Pricing( $project_id );
        $total = $setlr_pricing->calculate_quote();
        $post = get_post( $project_id );
        if ( isset( $_GET['previous'] ) && is_numeric( $_GET['previous'] ) ) :
            
            $previous = (float)$_GET['previous'];
            
        else :
            $previous = (get_post_meta( $project_id, 'setlr_total_price', true ) ) ? get_post_meta( $project_id, 'setlr_total_price', true ) : 0;
        endif;    
        $due = $total - $previous;
        $customer_id = $post->post_author;
        $currency = 'GBP';
        $currency_symbol = Setlr_Payment::get_currency_symbol( $currency );
        $service = get_post_meta( $project_id, 'setlr_service', true );
        
        if ( is_wp_error($total ) ) :
            $message = $total->get_error_message();
        else : 
            $message = sprintf( __( 'Here is our best quote for your %s project', 'pdcrequest'), $service );
        endif;
        
        echo '<h2>' . $message . '</h2>';
        echo '<p class="seltr-total-quote">' . __('Total: ', 'pdcrequest') .$currency_symbol . $total . '</p>';
        
        if ( $previous > 0 ) :
            
            echo '<p class="setlr-previous-quote">' . __( 'Paid before: ', 'pdcrequest') . $currency_symbol . $previous . '</p>';
            echo '<p class="setlr-quote-due">' . __( 'Quote due: ', 'pdcrequest') . $currency_symbol . $due . '</p>';
        endif;
        
        if ( absint( $customer_id) && 0 !== $customer_id && is_float( $due) && isset( $currency ) && absint( $project_id )) :
            update_post_meta( $project_id, 'setlr_total_price', $total );
            update_post_meta( $project_id, 'setlr_currency', $currency );
            if ( $due > 0 ) :
                do_action( 'pdcrequest_payment_form', $customer_id, $due, $currency, $project_id );
            else :
                echo '<p>' . pdcrequest_link_to_dashboard( 'Update', 'button-primary button' ) . '</p>';
            endif;
        else :
           
            echo '<h2 class="error setlr-error">PAYMENT NOT READY</h2>';
            
        endif;
    }
    
    
    public static function get_price_for( $project_id ) {
        $currency = get_post_meta( $project_id, 'setlr_currency', true );
        $amount = get_post_meta( $project_id, 'setlr_total_price', true );
        
        $symbol = ( $currency ) ? Setlr_Payment::get_currency_symbol( $currency ) : '';
        
        if ( $amount > 0 ) :
            return $symbol . ' ' . number_format( $amount, 2, '.', ',' );
        else :
            return '';
        endif;
    }
    
    
    /**
     * claculate quote total
     * @return mixed success return float the final price in base currency | WP_Error no service defined
     */
    protected function calculate_quote() {
        $project_id = $this->project_id;
        
        //determine type of project
        $service = get_post_meta( $this->project_id, 'setlr_service', true );
        
        
        if ( $service ) :
            //get prices
            $tariff = $this->get_prices( $service );
            
            $price = $tariff['price'];
            $unit = $tariff['unit'];

            //initialize $total
            $total = 0;

            //calculate $total
            switch( $unit ) :
                case 'per_word' :
                    //get number of words
                    $word_count = get_post_meta( $project_id, 'setlr_word_count', true );
                    $total = $price * $word_count;
                    break;
                case 'fixed' :
                    $total = $price;
                    break;
            endswitch;

            return floatval( $total );  
        else :
            return new WP_Error( 'no_service', __( 'Service type is not set', 'pdcrequest' ) );
        endif;
    }
    
    
    /**
     * get current prices from database
     * @param string $type the type of project (translation, question, etc)
     * @return array price in base currency, units used
     */
    public function get_prices( $type ) {
        $options = get_option( 'pdcrequest_settings' );
        //print_r($options);exit;
        
        $price_name = 'pdcrequest_' . $type . '_price';
        $unit_name = $price_name . '_unit';
        
        
        $price = $options[$price_name];
        $unit = $options[$unit_name];
        
        
        return array( 'price' => $price, 'unit' => $unit );
    }
    
    
    public static function get_static_prices( $type ) {
        $options = get_option( 'pdcrequest_settings' );
        //print_r($options);exit;
        $price_name = 'pdcrequest_' . $type . '_price';
        $unit_name = $price_name . '_unit';
        
        
        $price = $options[$price_name];
        $unit = $options[$unit_name];
        
        
        return array( 'price' => $price, 'unit' => $unit );
    }
    
    
    public function admin_pricing_options() {
        
    }
    
    
    public static function show_quote_button( $project_id ) {
        // initialize
        $payment_button = '';
        
        // check we have a valid project
        if ( absint( $project_id ) && $project_id > 0 ) :
            $payment_status = Payment_Status::get_payment_status( $project_id );
            $setlr_status = request_status::get_request_status( $project_id );
            // check if we need payment
            if ( $payment_status === 'pending' && $setlr_status === 'open' ) :
                // get amount and currency
                $page = get_page_by_path('project-quote');
                $url = get_permalink($page->ID);
                $arr_params = array( 'request_id' => $project_id, 'previous' => 0 );
                
                $payment_button = '<a href="'. wp_nonce_url( add_query_arg( $arr_params, $url ), 'request-form', 'setlrnonce' ) .'" class="setlr-quote-button" ';
                
                $payment_button .= '>' . __( 'Pay Now', 'pdcrequest') . '</a>';
            
            endif;
        
        endif;
        
        return $payment_button;
    }
    
    
    public static function get_quote_request_total( $data ) {
        
        $service = ( $data['service'] ) ? sanitize_text_field( $data['service'] ) : '';
        
        if ( $service ) :
            //get prices
            $tariff = self::get_static_prices( $service );
            
            $price = $tariff['price'];
            $unit = $tariff['unit'];

            //initialize $total
            $total = 0;

            //calculate $total
            switch( $unit ) :
                case 'per_word' :
                    //get number of words
                    $word_count = ( isset( $data['setlr_word_count'] ) ) ? $data['setlr_word_count'] : 0;
                    if ( $word_count === 0 ) :
                        $word_count = ( isset( $data['word_count'] ) ) ? $data['word_count'] : 0;
                    endif;
                    write_log( 'word_count='. $word_count);
                    $total = $price * $word_count;
                    break;
                case 'fixed' :
                    $total = $price;
                    break;
            endswitch;
            write_log( 'total='. $total);
            return $total;  
        else :
            return new WP_Error( 'no_service', __( 'Service type is not set', 'pdcrequest' ) );
        endif;
    }
}
