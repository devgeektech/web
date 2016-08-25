<?php
function call_Setlr_Payment() {
    new Setlr_Payment();
}

class Setlr_Payment {
    
    private $customer_id;
    
    protected $environment;

    protected $sandbox_public_key;

    protected $sandbox_private_key;
    
    protected $production_public_key;
    
    protected $production_private_key;

    protected $merchant_id;

    private $amount;
    
    protected $currency;
    
    public $currency_symbol;
    
    public $project_id;


    public function __construct() {
        $options = get_option( 'pdcrequest_settings' );
        
        $environment = ( $options['braintree_environment'] ) ? $options['braintree_environment'] : 'production';
        
        
        $merchant_id = 'pdcrequest_' . $environment . '_merchant_id';
        $public_key = 'pdcrequest_' . $environment . '_publickey';
        $private_key = 'pdcrequest_' . $environment . '_privatekey';
        
            $this->merchant_id = $options[$merchant_id];
            $this->public_key = $options[$public_key];
            $this->private_key = $options[$private_key];
        
        
        Braintree_Configuration::environment($environment);
        Braintree_Configuration::merchantId($this->merchant_id);
        Braintree_Configuration::publicKey($this->public_key);
        Braintree_Configuration::privateKey($this->private_key);
    }
    
    
    public static function render_payment_form( $customer_id, $amount, $currency, $project_id ) {
        write_log( 'Setlr_Payment render_payment_form');
        $currency_symbol = self::get_currency_symbol( $currency );
        
        $onclick = 'onClick="ga(\'send\', \'event\', \'project-pay\', \'submit-finished\')"';
        ?>
            <form id="checkout" method="post" action="<?php echo admin_url('admin.php'); ?>">
                <div id="payment-method"></div>
                <input type="submit" <?php echo $onclick; ?> value="<?php printf( __( 'Pay %s', 'pdcrequest' ), $currency_symbol . $amount ); ?>">
                <input type="hidden" name="action" value="make_payment">
                <input type="hidden" name="setlr_total" value="<?php echo $amount; ?>">
                <input type="hidden" name="currency" value="<?php echo $currency; ?>">
                <input type="hidden" name="customer_id" value="<?php echo absint( $customer_id); ?>">
                <input type="hidden" name="project_id" value="<?php echo absint( $project_id ); ?>">
            </form>
    <?php
    }
    
    
    public static function get_currency_symbol( $currency ) {
        
        switch ( $currency ) :
            case 'USD' :
                $currency_symbol = "$";
                break;
            case 'GBP' :
                $currency_symbol = "£";
                break;
            case 'EUR' :
                $currency_symbol = "€";
                break;
        endswitch;
        
        return $currency_symbol;
    }
    
    
    
    public static function enqueue_scripts() {
        write_log( 'Setlr_Payment enqueue_scripts' );
        wp_register_script( 'braintree', 'https://js.braintreegateway.com/v2/braintree.js', array(), 'v2' );
        wp_register_script( 'setlr-braintree-payment', plugins_url('pdc-requestmgt/assets/js/setlr-braintree-payment.js'), array( 'jquery', 'braintree'), '0.9' );
        wp_localize_script( 'setlr-braintree-payment', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
        
        if ( is_page( array('project-quote', 'add-a-new-project' ) ) ) :
            wp_enqueue_script( 'braintree');
            wp_enqueue_script( 'setlr-braintree-payment'); 
        endif;
    }
    
    
    public static function generate_client_token() {
        //$environment = $_POST['environment'];
        
        $payment = new Setlr_Payment();
        $clientToken = Braintree_ClientToken::generate();
        echo $clientToken;
        //write_log('clientoken=' . $clientToken);
        exit();
    }
    
    public static function do_payment() {
        
        $nonce = $_POST["payment_method_nonce"];
        $total = floatval( $_POST['setlr_total'] );
        $currency = (isset( $_POST['currency'] ) ) ? esc_attr( $_POST['currency'] ) : 'GBP';
        $customer_id = absint( $_POST['customer_id'] );
        $project_id = absint( $_POST['project_id'] );
        
        $result = Braintree_Transaction::sale([
            'amount' => $total,
            'paymentMethodNonce' => $nonce
          ]);
        self::save_transaction( $result, $project_id, $customer_id );
        self::redirect_after_payment( $result->transaction->processorResponseCode, $project_id, $total, $currency );
    }
    
    
    public static function redirect_after_payment( $payment_result, $project_id, $total = 0, $currency ="GBP" ) {
        write_log( 'payment_result=' . $payment_result );
        switch ( $payment_result ) :
            case 1000 :
                //success
                $type = 'success';
                break;
            case 1001 :
                //success but verify id
                $type = 'success';
                $extra = 'verify_id';
                break;
            case 2000 :
                //refused by bank
                $type ='soft';
                break;
            case 2001 :
                //insufficient funds
                $type ='soft';
                break;
            case 2002 :
                //limit exceeeded
                $type ='soft';
                break;
            case 2003 :
            case 2016 :
            case 2024 :
            case 2025 :
            case 2026 :
            case 2027 :
            case 2028 :
            case 2029 :
            case 2030 :
            case 2035 :
            case 2038 :
            case 2040 :
            case 2042 :
            case 2045 :
            case 2048 :
            case 2055 :
            case 2056 :
            case 2057 :
            case 2078 :
            case 2080 :
                $type ='soft';
                break;
            case 3000 :
                //Processor Network Unavailable - Try Again
                $type = 'network';
                $extra = 'network_unavailable';
                break;
            default:
                //unsuccessful
                $type = 'hard';
                break;
        endswitch;
        
        write_log( 'type=' . $type );
        switch ( $type ) :
            case 'success' :
                $message = __( 'Your payment has been approved, thank you. We have published your project.', 'pdcrequest' );
                $redirect = 'dashboard';
                
                if ( $total > 0 ) :
                    write_log( 'total=' . $total );
                    do_action( 'pdcrequest_update_project_payment', $project_id, $total, $currency );
                endif;
                
                //update status to paid
                Payment_Status::update_status($project_id, 'paid' );
                break;
            case 'soft' :
                $message = __( 'There was a problem processing your credit card; please double check your payment information and try again', 'pdcrequest' );
                $redirect = '';
                break;
            case 'hard':
                $message = __( 'There is a problem with your credit card. Please enquire with your bank', 'pdcrequest');
                $redirect = 'dashboard';
                break;
            case 'network' :
                $message = __( 'Our credit card processor network is unavailable. Please try again in a few minutes.', 'pdcrequest' );
                $redirect = '';
        endswitch;
        
        switch ( $redirect ) :
            case 'dashboard':
                write_log( 'redirect to dashboard' );
                Pdc_Requestmgt::redirect_to_dashboard( $message );
                break;
            case 'retry' :
                $page_id = get_page_by_path( 'project-quote' );
                $data = array( 'request_id' => $project_id );
                Pdc_Requestmgt::redirect_to_page( $page_id, $message, $data = array() );
                break;
        endswitch;
        
    }
    
    private static function save_transaction( $result, $project_id, $customer_id ) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'setlr_payments';
        $insert = $wpdb->insert( 
                $table, 
                array( 
                        'id' => $result->transaction->id, 
                        'code' => $result->transaction->processorResponseCode,
                        'type'  => $result->transaction->type,
                        'currency'   => $result->transaction->currencyIsoCode,
                        'amount'    => $result->transaction->amount,
                        'merchantAccountId' => $result->transaction->merchantAccountId,
                        'createdAt_date'    => $result->transaction->createdAt->date,
                        'createdAt_timezone'    => $result->transaction->createdAt->timezone,
                        'project_id'    => $project_id,
                        'customer_id'   => $customer_id
                ), 
                array( 
                        '%s', 
                        '%d',
                        '%s',
                        '%s',
                        '%f',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d'
                ) 
        );
    }
    
    
    public static function install_payment_table() {
        global $wpdb;
        $columns = array( 'id', 'code', 'type', 'currency', 'amount', 'merchantAccountId', 'createdAt_date', 'createdAt_timezone', 'project_id', 'customer_id');
        
        $table = $wpdb->prefix . 'setlr_payments';
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
    
    
    public static function settle_transaction( $project_id ) {
        $transactions = self::get_transaction_for($project_id);
        
        if ( $transactions ) :
            foreach( $transactions as $transaction ) :
                $result = Braintree_Transaction::submitForSettlement($transaction->id );  
                do_action( 'settlement_update_status', $project_id, $result );
            endforeach;
        endif;
        
    }
    
    
    public static function get_transaction_for( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'setlr_payments';
        
        $sql = "SELECT id, currency, amount FROM $table WHERE project_id = %d";
        
        $transactions = $wpdb->get_results( $wpdb->prepare( $sql, $project_id ));
        
        return $transactions;
    }
    
    
    /**
     * void a transaction before it is settled
     * @param int $project_id the project id
     */
    public static function void_transaction( $project_id ) {
        $transactions = self::get_transaction_for($project_id);
        
        if ( $transactions ) :
            foreach( $transactions as $transaction ) :       
                $result = Braintree_Transaction::void($transaction->id );
                do_action( 'settlement_update_status', $project_id, $result );
            endforeach;
        endif;
    }
    
    
    /**
     * refund a transaction after it has been settled
     * @param int $project_id the project id
     * @param float $fee the fee amount that Setlr want to keep
     */
    public static function refund_transaction( $project_id, $fee = 0 ) {
        $transactions = self::get_transaction_for($project_id);
        
        if ( $transactions ) :
            foreach( $transactions as $transaction ) :  
                if( $fee > 0 ) :
                    $result = $result = Braintree_Transaction::refund( $transaction->id );
                else :
                    $result = $result = Braintree_Transaction::refund( $transaction->id );
                endif;
                if ($result->success) :
                    // Transaction refunded voided do something
                    do_action( 'settlement_update_status', $project_id, $result );
                  else :
                    write_log($result->errors);
                  endif;
            endforeach;
        endif;
    }
    
    
    
    
}
