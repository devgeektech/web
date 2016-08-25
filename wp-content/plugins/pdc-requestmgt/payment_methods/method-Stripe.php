<?php

//require Stripe API wrapper
require 'stripe/init.php';

/**
 * Description of methodStripe
 *
 * @author philippe
 */
class methodStripe  {
    //put your code here
    const SECRETKEY = "sk_test_2DK63cqecCFOGDRPI8raNMEs";

    const PUBLISHABLEKEY = "pk_test_lzHfvuw9rh140aMkmb98lIxD";
    
    public function __construct() {
        $this->secretkey = self::SECRETKEY;
        $this->publishablekey = self::PUBLISHABLEKEY; 
    }
    
    public static function collect_cc_form() {
           
        $html = '<form action="' . admin_url( 'admin.php' ) . '" method="POST" id="payment-form">';
        $html .= '<div class="payment-errors"></div>';

        $html .= '    <div class="form-row">';
        $html .= '      <label>';
        $html .= '        <span>' . __( 'Card Number', 'pdcrequest' ) . '</span>';
        $html .= '        <input class="card-number" type="text" size="20" data-stripe="number"/>';
        $html .= '      </label>';
        $html .= '    </div>';

        $html .= '    <div class="form-row">';
        $html .= '      <label>';
        $html .= '        <span>' . __( 'CVC', 'pdcrequest' ) . '</span>';
        $html .= '        <input class="card-cvc" type="text" size="4" data-stripe="cvc"/>';
        $html .= '      </label>';
        $html .= '    </div>';

        $html .= '    <div class="form-row">';
        $html .= '      <label>';
        $html .= '        <span>' . __( 'Expiration (MM/YYYY)', 'pdcrequest' ) . '</span>';
        $html .= '        <input class="card-expiry-month" type="text" size="2" data-stripe="exp-month"/>';
        $html .= '      </label>';
        $html .= '      <span> / </span>';
        $html .= '      <input class="card-expiry-year" type="text" size="4" data-stripe="exp-year"/>';
        $html .= '    </div>';
        $html .= '<input type="hidden" name="user_id" id="user_id" value="' . get_current_user_id() . '">';
        $html .= '<input type="hidden" name="action" value="process_stripe_token">';
        $html .= '<input type="hidden" name="stripeToken" id="stripeToken" value="">';
        $html .= '    <button type="submit">' . __( 'Submit', 'pdcrequest' ) . '</button>';
        $html .= '</form>';
       
        return $html;
        
    }
    
    /** toggle the Stripe credit card form
     * 
     * @param int $user_id
     * @return string
     */
    public static function toggle_cc_form( $user_id = null ) {
        /* check if we have a stripe customer_id and suscription */
        $has_stripe_set_up = self::get_customer_id( $user_id );
        
        $html = '<a href="#" id="toggle-cc-form" title="cc-form">';
        if ( $has_stripe_set_up ) :
            $html .= __('Update your payment information', 'pdcrequest' );
        else :
            $html .=  __( 'Enter your payment information', 'pdcrequest' );
        endif;
        $html .= '</a>';
        
        return $html;
    }
    
    
    /** create customer using Stripe token
     * 
     * @param string $token
     * @param string $description
     * @return string
     */
    public static function create_customer() {
        if ( isset($_POST['stripeToken']) ):
    
            $user_id = absint($_POST['user_id']);
            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey(self::SECRETKEY);

            // Get the credit card details submitted by the form
            $token = $_POST['stripeToken'];

            // Create the charge on Stripe's servers - this will charge the user's card
            try {
               
                /*
                 * Plan should be created first (either by API call or in the dashboard)
                 */
            $customer = \Stripe\Customer::create(array(
                "source" => $token,
                "plan" => "free",
                "description" => $user_id)
            );
            } catch(\Stripe\Error\Card $e) {
                // The card has been declined
                write_log($e);
                $message = $e->getMessage();

            }

            if ( isset($customer->id )):
                write_log( 'customer_id = '.$customer->id);
                self::save_customer_id($user_id, $customer->id);
            endif;
        else:
            write_log( 'no token yet');
        endif;
        
        wp_safe_redirect(self::dashboard_permalink());
    }
    
    
    public static function charge_customer( int $user_id, string $customer_id, int $amount, string $description ) {
        \Stripe\Stripe::setApiKey(self::SECRETKEY);
        try {
            /**
             * Will add an invoice item to the upcoming scheduled invoice of the customer
             */
            $charge = \Stripe\InvoiceItem::create(array(
                "customer" => $customer_id,
                "amount" => $amount,
                "currency" => "gbp",
                "description" => esc_html($description)
            ));
        } catch (Exception $e) {
               write_log( $e );
               $message = $e->getMessage();
        }
        
        if (isset( $charge->id)) :
            write_log( 'charge_id = ' .$charge->id );
            $charge_id = self::save_charge_id( $user_id, $charge->id);
            
            //do something after a successufully adding an invoice item
            if ( absint( $charge_id)) :
                $args = array(
                    'user_id' => $user_id,
                    'charge_id' => $charge_id
                );
                do_action( 'charge_added', $args );
            endif;
        endif;
        
    }
    
    /** save Stripe customer id in DB
     * 
     * @param int $user_id
     * @param string $customer_id
     * @return Void
     */
    private static function save_customer_id( $user_id, $customer_id) {
        update_user_meta( $user_id, 'stripe_id', $customer_id );
    }
    
    
    /** get Stripe customer id
     * 
     * 
     * @param int $user_id
     * @return string
     */
    public static function get_customer_id( $user_id ){
        return get_user_meta( $user_id, 'stripe_id', true );
    }
    
    
    /** create plan
     * 
     * @param int $amount
     * @param string $interval
     * @param string $name
     * @param string $currency
     * @return string
     */
    public function create_plan( int $amount, string $interval, string $name, string $currency ){
        \Stripe\Stripe::setApiKey(self::SECRETKEY);

        \Stripe\Plan::create(["amount" => $amount,
          "interval" => $interval,
          "name" => $name,
          "currency" => $currency,
          "id" => urlencode($name)]
        );
        
        return urlencode( $name );
    }
    
    
    public static function add_js() {
      
        wp_enqueue_script( 'stripe', 'https://js.stripe.com/v2/', '', '1.0', false );
        //http://localhost/setlr/wp-content/plugins/pdc-requestmgt/payment-methods/js/method-stripe.js?ver=4.2.2
        wp_register_script( 'method-stripe', plugins_url( 'pdc-requestmgt/payment_methods/js/method-stripe.js'), array( 'stripe', 'jquery', 'simplemodal', 'stripe-apikey'), '1.0', true );
        wp_enqueue_script( 'simplemodal', plugins_url( 'pdc-requestmgt/payment_methods/js/jquery.simplemodal.1.4.4.min.js'), array( 'jquery' ), '1.4.4', true);
        wp_enqueue_script( 'stripe-apikey', plugins_url('pdc-requestmgt/payment_methods/js/stripe-apikey.js'), array('stripe','jquery','simplemodal'),'1.0', true);
        
        $localized_array = array( 'publishablekey' => self::PUBLISHABLEKEY, 'ajax_url' => admin_url( 'admin-ajax.php' ) );
        wp_localize_script('method-stripe', 'requestmgt', $localized_array );
        wp_enqueue_script('method-stripe');
        wp_enqueue_style('simplemodal', plugins_url('pdc-requestmgt/payment_methods/css/simplemodal.css'));
    }     
    
    public static function set_actions() {
        add_action( 'wp_ajax_get_stripe_token', array('methodStripe', 'create_ajax_customer'));
        //add_action( 'wp_ajax_get_stripe_token', array('methodStripe', 'create_customer'));
    }
    
    public static function dashboard_permalink(){
        $page = get_page_by_path('my-dashboard');
        return get_permalink($page->ID);
    }
    
   
}
