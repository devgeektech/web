<?php

/**
 * Description of setlr-dashboard
 *
 * @author philippe
 */
class Setlr_Dashboard {
//put your code here
    
    public function __construct() {
        
        
        /* 1) we check if current user logged in */
        if ( is_user_logged_in() ) :
            $user = wp_get_current_user();
            $kind = $user->role;
            
            switch ($kind ) :
                
                case 'customer' :
                    $html = '<a href="" class="button-primary">' . __( 'Edit Your Profile', 'pdcrequest' ) . '</a>';
                    do_action( 'information_for_customer' );
                    break;
                case 'helper' :
                    $html = '<a href="" class="button-primary">' . __( 'Edit Your Profile', 'pdcrequest' ) . '</a>';
                    do_action( 'information_for_helper' );
                    break;
                case 'senior_helper' :
                    $html = '<a href="" class="button-primary">' . __( 'Edit Your Profile', 'pdcrequest' ) . '</a>';
                    do_action( 'information_for_senior_helper' );
                    break;
                
            endswitch;
            return $html;
        endif;
    }
}
