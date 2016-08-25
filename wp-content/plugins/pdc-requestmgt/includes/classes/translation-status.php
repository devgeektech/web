<?php


/**
 * Description of translation_status
 *
 * @author philippe
 */
class translation_status {
    //put your code here
    
    var $statuses = array( 'pending', 'in_progress', 'customer_review','in_revision', 'finished', 'accepted', 'rejected' );
    
    var $status;
    
    var $post_meta = 'setlr_status';
    
    
    
    public function __construct( $status = 'pending' ) {
        if ( in_array( $status, $statuses ) ) :
            $this->status = $status;
        else :
            $this->status = 'pending';
        endif;
    }
    
    
    
    public static function update_status( $post_id, $status = 'pending', $old_status = null ) {
        self::update_status_history($post_id, $status, $old_status );
        return update_post_meta( $post_id, 'setlr_status', $status, $old_status );
    }
    
    
    public static function get_translation_status( $post_id ) {
        return get_post_meta( $post_id, 'setlr_status', true );
    }
    
    
    /**
     * update the translation status history if status changed
     * @global Object $wpdb
     * @param int $post_id
     * @param string $status
     * @param string $old_status
     * @return int | boolean false returns insert id if successful, boolean false otherwise
     */
    public static function update_status_history( $post_id, $status, $old_status = null ) {
        global $wpdb;
        $data = array(
            'post_id'    => $post_id,
            'status'     => $status
        );
        $format = array( '%d', '%s' );
        
        if ( $status != $old_status ) :
            $insert = $wpdb->insert( $wpdb->prefix . 'setlr_status_history', $data, $format );
            return $wpdb->insert_id;
        else :
            return false;
        endif;
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
    
    
    
    public static function get_working_time( $translation_id ) {
        $current_status = self::get_translation_status($translation_id );
        $pending = self::get_translation_status_datetime($translation_id, 'pending');
        
        switch ( $current_status ) :
            case 'finished':
                $latest = self::get_translation_status_datetime($translation_id, $current_status );
                break;
            case 'in_progress' :
                $latest = self::get_translation_status_datetime($translation_id, $current_status );
                break;
            default :
                $latest = "now";
        endswitch;
        
        $timezone = get_option('timezone_string');
        $datetime_start = new DateTime( $pending, new DateTimeZone($timezone) );
        $datetime_end = new DateTime( $latest, new DateTimeZone($timezone) );
        $interval = $datetime_start->diff( $datetime_end );
        
        return sprintf( __( '%s minutes', 'pdcrequest' ), $interval->format( "%i" ) );
    }
    
    
    public static function get_translation_status_datetime( $translation_id, $status = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'setlr_status_history';
        
        if ( absint( $translation_id ) && ! is_null( $status ) ) :
            $datetime = $wpdb->get_var( $wpdb->prepare( "SELECT action_date FROM $table WHERE post_id = %d AND status = %s",
                    $translation_id,
                    $status ) );
            return $datetime;
        endif;
        
        return false;
    }
    
    
    
}
