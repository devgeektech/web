<?php


/**
 * Description of Paypal_Payment
 *
 * @author philippe
 */
class Paypal_Payment {
    //put your code here
    
    var $account;
    var $client_id;
    var $secret;
    var $environment;
    
    public function __construct() {
        /* we synchronize PayPal and Braintree environments (sandbox || production) */
        $options = get_option('pdcrequest_settings');
        $this->environment = $options['braintree_environment'];
        
        if ( $this->environment === 'sandbox') :
            $account = 'pdcrequest_paypal_sandbox_account';
            $client_id = 'pdcrequest_paypal_sandbox_client_id';
            $secret = 'pdcrequest_paypal_sandbox_secret';
            $api_address = 'https://api.sandbox.paypal.com';
        else :
            $account = 'pdcrequest_paypal_production_account';
            $client_id = 'pdcrequest_paypal_production_client_id';
            $secret = 'pdcrequest_paypal_production_secret';
            $api_address = 'https://api.paypal.com';
        endif;
        $this->account = $options[$account];
        $this->client_id = $options[$client_id];
        $this->secret = $options[$secret];
        $this->api_address = $api_address;
        //pdcrequest_paypal_sandbox_account
        
    }
    
    
    /**
     * show form extract used in Helper profile
     * @param int $user_id the current user id 
     * @return string html fieldset with label and input
     */
    public static function show_account_form( $user_id ) {
        $paypal_email = self::get_paypal_email($user_id);
        $paypal_id = self::get_paypal_id($user_id);
        
        $html =  '<fieldset><legend>' . __( 'Paypal Account to receive payments', 'pdcrequest' ) . '</legend>';
        $html .= '<p><label for="PAYPAL_ID">' . __( 'Your PayPal ID', 'pdcrequest' ) . '</label>';
        $html .= '<input type="text" name="PAYPAL_ID" value="' . esc_attr( $paypal_id ) . '"></p>';
        $html .= '<p>' . __( 'Or fill in your email for PayPal', 'pdcrequest' ) . '</p>';
        $html .= '<p><label for="paypal_email">' . __( 'The email you registered to PayPal', 'pdcrequest' ) . '</label>';
        $html .= '<input type="email" name="paypal_email" value="' . esc_attr( $paypal_email ) . '"></p>';
        $html .= '</fieldset>';
        
        return $html;
    }
    
    
    public static function get_paypal_email( $user_id ) {
        return get_user_meta( $user_id, 'paypal_email', true );
    }
    
    
    public static function get_paypal_id( $user_id ) {
        return get_user_meta( $user_id, 'paypal_id', true );
    }
    
    
    public static function save_paypal_email( $user_id, $email ) {
        if ( absint( $user_id) && is_email( $email ) ) :
            return update_user_meta( $user_id, 'paypal_email', $email );
        else :
            return false;
        endif;
    }
    
    
    public static function save_paypal_id( $user_id, $paypal_id ) {
        if ( absint( $user_id) &&  $paypal_id ) :
            return update_user_meta( $user_id, 'paypal_id', $paypal_id );
        else :
            return false;
        endif;
    }
    
    
    public static function install_paypal_table() {
        global $wpdb;
        $columns = array( 'id', 'code', 'type', 'currency', 'amount', 'merchantAccountId', 'createdAt_date', 'createdAt_timezone', 'project_id', 'customer_id');
        
        $table = self::get_table();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
          id varchar(12) NOT NULL,
          code int(4) NOT NULL,
          type varchar(20) NOT NULL,
          currency varchar(3) NOT NULL,
          amount DECIMAL(15,2) DEFAULT 0.00,
          merchantAccountId varchar(20),
          createdAt_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          createdAt_timezone varchar(5),
          project_id BIGINT(20) UNSIGNED NOT NULL,
          customer_id BIGINT(20) UNSIGNED NOT NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    
    public static function get_table() {
        return $wpdb->prefix . 'setlr_paypal_payments';
    }
    
    /**
     * get the PayPal access token
     * @return string the access token
     * @uses cURL
     */
    public function get_token() {
        $api = "/v1/oauth2/token";
        
        $url = $this->api_address . $api;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id.":".$this->secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $token_request = curl_exec($ch);
        if(empty($token_request)) :
            write_log("Error: Paypal_Payment::get_token No response.");
        else :
        
            $json = json_decode($token_request);
            return $json->access_token;
        endif;

        curl_close($ch);
        
    }
    
    public function make_payment( $user_id, $project_id, $token) {
        $api = "/v1/payments/payouts?sync_mode=true";
        $url = $this->api_address . $api;

        $amount = get_post_meta( $project_id, 'setlr_total_price', true );

        $currency = get_post_meta( $project_id, 'setlr_currency', true );

        $user_paypal_email = get_user_meta( $user_id, 'paypal_email', true );

        $items = array(
            "recipient_type"    => "EMAIL",
            "amount"            => array( 
                                        "value" => $amount,
                                        "currency" => $currency
                                    ),
            "receiver"          => $user_paypal_email,
            "note"              => "Payment for your work",
            "sender_item_id"    => $project_id
        );
        $data = array( "sender_batch_header" => array("email_subject" => "You have a payment"), "items" => $items);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization:" => "Bearer " . $token, "Content-Type:" => "application/json" ) );
       $payment = curl_exec($ch);
//write result to debug_log
        write_log( $payment );
}

/*
public function make_payment( $user_id, $project_id, $token) {
        $api = "/v1/payments/payouts?sync_mode=true";
        $url = $this->api_address . $api;
        
        $amount = get_post_meta( $project_id, 'setlr_total_price', true );
        
        $currency = get_post_meta( $project_id, 'setlr_currency', true );
       
        $user_paypal_email = get_user_meta( $user_id, 'paypal_email', true );
        
     
        $items = array(
            "recipient_type"    => "EMAIL",
            "amount"            => array( 
                                        "value" => $amount,
                                        "currency" => $currency
                                    ),
            "receiver"          => $user_paypal_email,
            "note"              => "Payment for your work",
            "sender_item_id"    => $project_id
        );
        $data = array( "sender_batch_header" => array("email_subject" => "You have a payment"), "items" => $items);
        write_log($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization:" => "Bearer " . $token, "Content-Type:" => "application/json" ) );
        $payment = curl_exec($ch);
        write_log( $payment );
        /*
        curl -v https://api.sandbox.paypal.com/v1/payments/payouts?sync_mode=true \
        
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer <Access-Token>" \
        -d "{
        "sender_batch_header": {
                "email_subject": "You have a payment"
            },
            "items": [
                {
                    "recipient_type": "EMAIL",
                    "amount": {
                        "value": 12.34,
                        "currency": "USD"
                    },
                    "receiver": "shirt-supplier-one@mail.com",
                    "note": "Payment for recent T-Shirt delivery",
                    "sender_item_id": "A123"
                }
            ]
        }"
         * 
         */
    
    
}
