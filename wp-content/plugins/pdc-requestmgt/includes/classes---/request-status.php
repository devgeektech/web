<?php


/**
 * Description of request_status
 *
 * @author philippe
 */
class request_status {
    //put your code here
    
    var $statuses = array( 'open', 'waiting', 'customer_review', 'in_revision','cancelled', 'rejected', 'accepted' );
    
    var $status;
    
    var $post_meta = 'setlr_status';
    
    
    
    public function __construct( $status = 'open' ) {
        if ( in_array( $status, $statuses ) ) :
            $this->status = $status;
        else :
            $this->status = 'open';
        endif;
    }
    
    
    
    public static function update_status( $request_id, $status = 'open' ) {
        self::update_status_history($request_id, $status);
        return update_post_meta( $request_id, 'setlr_status', $status );
    }
    
    
    public static function get_request_status( $request_id ) {
        return get_post_meta( $request_id, 'setlr_status', true );
    }
    
    
    public static function update_status_history( $request_id, $status ) {
        global $wpdb;
        $data = array(
            'post_id'    => $request_id,
            'status'        => $status
        );
        $format = array( '%d', '%s' );
        
        $insert = $wpdb->insert( $wpdb->prefix . 'setlr_status_history', $data, $format );
        return $wpdb->insert_id;
    }
    
    
    public static function create_db_table() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'setlr_status_history';
	$db = DB_NAME;
        
		$tables = $wpdb->query( "SHOW TABLES FROM $db LIKE '$table'", ARRAY_A );
		
		if ( !$tables ) :
			// we need to update order options table
			if ( ! empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";
			
			$sql = "CREATE TABLE $table (
					  status_history_id bigint(20) NOT NULL AUTO_INCREMENT,
					  post_id bigint(20) NOT NULL,
                                          status varchar(10) NOT NULL,
					  action_date timestamp DEFAULT CURRENT_TIMESTAMP,
					  PRIMARY KEY (status_history_id)
				) $charset_collate;\n";
			
                    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                    dbDelta( $sql );
		
		endif;
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
