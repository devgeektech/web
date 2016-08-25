<?php


/**
 * Description of request_status
 *
 * @author philippe
 */
class Payment_Status {
    //put your code here
    
    var $statuses = array( 'pending', 'paid', 'refund_pending', 'refunded' );
    
    var $status;
    
    var $post_meta = 'payment_status';
    
    
    
    public function __construct( $status = 'pending' ) {
        if ( in_array( $status, $statuses ) ) :
            $this->status = $status;
        else :
            $this->status = 'pending';
        endif;
    }
    
    
    
    public static function update_status( $request_id, $status = 'pending' ) {
        return update_post_meta( $request_id, 'payment_status', $status );
    }
    
    
    public static function get_payment_status( $request_id ) {
        return get_post_meta( $request_id, 'payment_status', true );
    }
    
    public static function get_amount( $project_id ) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'setlr_payments';
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT amount, currency FROM $table WHERE project_id = %d", $project_id ));
        
        if ( $results ) :
            return $results[0]->currency . ' ' . $results[0]->amount;
        endif;
        return '';
        
    }
    
    /**
     * verify that status is allowed
     * @param string $status status
     * @return boolean true if accepted | false otherwise
     */
    public static function verify_status( $status ) {
        if ( in_array( $status, $this->statuses ) ) :
            return true;
        else :
            return false;
        endif;
    }
}
