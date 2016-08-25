<?php

/**
 * Helps administrators in assigning revisions to selected helpers
 * 
 * Admins can thus notify one helper or a group of helpers that a revision task is waiting for them
 */
class Revision_Assign {
    
    var $revision_id;
    
    var $user_ids;
    
    
    
    public function __construct( $revision_id, $user_ids = array() ) {
        
        if ( empty( $user_ids ) ) :
            //select suitable helpers
            
        elseif ( count( $user_ids ) === 1 ) :
            //notify single helper
            
        else :
            //show revision to all selected users
            
        endif;
        
        $this->revision_id = $revision_id;
    }
    
    
    public function list_possible_users() {
        $ids = Setlr_Revision_Request::get_rev_request_information( $this->revision_id );
        
        $project_id = $ids['project_id'];
        //get lang pairs and locale
        
        //list users with matching lang pairs and locale
        
        //substract translator from list
        
        //return array of WP_User ids
        
        
    }
}

