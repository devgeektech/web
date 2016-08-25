<?php

class Braintree_Status {
    var $statuses = array( 'none','submitted_for_settlement', 'settled', 'void', 'refunded' );
    
    var $status;
        
    
    public function __construct( $status = 'none' ) {
        if ( in_array( $status, $statuses ) ) :
            $this->status = $status;
        else :
            $this->status = 'none';
        endif;
    }
    
    
    
    public static function update_status( $project_id, $result ) {
        global $wpdb;
        
        $project = get_post( $project_id );
        
        if ( $project instanceof WP_Post ) :
            
            $customer_id = $project->post_author;
        
            $table = $wpdb->prefix . 'setlr_settlements';
            //INSERT INTO ON DUPLICATE KEY UPDATE subs_name     = VALUES(subs_name), subs_birthday = VALUES(subs_birthday)
            $sql = "INSERT INTO $table (id, status, currency, amount, merchantAccountId, createdAt_date, createdAt_timezone, updatedAt_date, updatedAt_timezone, project_id, customer_id) "
                    . "VALUES (%s, %s, %s, %f, %s, %s, %s,%s, %s, %d, %d) ON DUPLICATE KEY UPDATE status = %s, updatedAt_date = %s, updatedAt_timezone = %s"; 

            $update = $wpdb->query( $wpdb->prepare( $sql,
                    $result->transaction->id,
                    $result->transaction->status,
                    $result->transaction->currencyIsoCode,
                    $result->transaction->amount,
                    $result->transaction->merchantAccountId,
                    $result->transaction->createdAt->date,
                    $result->transaction->createdAt->timezone,
                    $result->transaction->updatedAt->date,
                    $result->transaction->updatedAt->timezone,
                    $project_id,
                    $customer_id,
                    $result->transaction->status,
                    $result->transaction->updatedAt->date,
                    $result->transaction->updatedAt->timezone
                    ));
            return $update;
        else :
            return new WP_Error( 'braintree', 'settlement_update project not found');
        endif;
    }
    
    
    public static function get_settlement_status( $project_id ) {
        global $wpdb;
        
        $sql = "SELECT status FROM $table WHERE project_id = %d ORDER BY updatedAt_date DESC";
        
        $status = $wpdb->get_var( $wpdb->prepare( $sql, $project_id));
        
        return $status;
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
    
    
    
    public static function install_settlement_table() {
        global $wpdb;
        write_log( 'install_settlement_table');
        $table = $wpdb->prefix . 'setlr_settlements';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
          id varchar(12) NOT NULL,
          status varchar(50) NOT NULL,
          currency varchar(3) NOT NULL,
          amount DECIMAL(15,2) DEFAULT 0.00,
          merchantAccountId varchar(20) NOT NULL,
          createdAt_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          createdAt_timezone varchar(5) NOT NULL,
          updatedAt_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          updatedAt_timezone varchar(5) NOT NULL,
          project_id BIGINT(20) UNSIGNED NOT NULL,
          customer_id BIGINT(20) UNSIGNED NOT NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

