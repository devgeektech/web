<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Description of Admin_Braintree
 *
 * @author philippe
 */
class Admin_Braintree extends WP_List_Table {
    //put your code here
    
    
    public function __construct() {
        
    }
    
    public function get_settlements() {
        global $wpdb;
        
        $table = $table = $wpdb->prefix . 'setlr_settlements';
        
        return array();
    }
    
    function get_columns(){
        //id, status, currency, amount, merchantAccountId, createdAt_date, createdAt_timezone, updatedAt_date, updatedAt_timezone, project_id, customer_id
        $columns = array(
            'project_id'  => __( 'Project Id', 'pdcrequest' ),
            'status'      => __( 'Settlement Status', 'pdcrequest'),
            'amount'      => __( 'Amount', 'pdcrequest'),
            'date'        => __( 'Date', 'pdcrequest'),
            'customer'    => __( 'Customer', 'pdcrequest')
        );
        return $columns;
      }

      
    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array( 'project_id', 'status', 'date', 'customer');
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->get_settlements();
    }
    
    
    function column_default( $item, $column_name ) {
        switch( $column_name ) : 
            case 'project_id':
            case 'status':
            case 'amount':
            case 'date':
            case 'customer':    
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        endswitch;
    }
    
}
