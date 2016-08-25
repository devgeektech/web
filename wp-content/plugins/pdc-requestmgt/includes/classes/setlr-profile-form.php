<?php

class Setlr_Profile_Form {
    
    var $user_id;
    
    
    public function __construct( $user_id = '' ) {
        if ( absint( $user_id ) ) $this->user_id = $user_id;
    }
    
    
    /**
     * show the log in registration forms
     * @global type $current_user
     * @return boolean|int|string boolean false if user logged in  and no customer capability, if user logged in returns user_id, else show HTML forms
     */
    public function show_registration_form() {
        global $current_user;
        
        if ( is_user_logged_in() ) :
            if ( current_user_can( 'customer' ) ) :
                return get_current_user_id();
            endif;
            
            return false;
        else :
            //show log in or register form
            $redirect = site_url( 'add-a-new-project' );
            $html = '<div id="registration-login">';
            //$html .= '<a href="' . wp_registration_url() . '" title="' . __( 'Register', 'pdcrrequest' ) . '">' . __( 'Register', 'pdcrrequest' ) . '</a>';
            $html .= self::registration_form();
            $html .= self::login_form();
            
            $html .= '</div>';
            
            return $html;
        endif;
        
    }
    
    public static function registration_form() {
        $html = '<fieldset class="setlr-register">';
        $html .= '<legend>' . __('New customer? Please register', 'pdcrequest') . '</legend>';
        $html .= '<label for="email">' . __( 'Email', 'pdcrequest' );
        $html .= '<input type="email" id="user_email" name="user_email" required>';
        $html .= '</label>';
        $html .= '<label for="nickname">' . __( 'Nickname', 'pdcrequest' );
        $html .= '<input type="text" id="nickname" name="nickname">';
        $html .= '</label>';
        $html .= '<label for="user_pass">' . __( 'Password', 'pdcrequest' );
        $html .= '<input type="password" id="user_pass" name="user_pass" required>';
        $html .= '</label>';
        $html .= '<label for="user_pass2">' . __( 'Password Repeat', 'pdcrequest' );
        $html .= '<input type="password" id="user_pass2" name="use_pass2" required>';
        $html .= '</label>';
        $html .= '<button id="register" class="setlr-button button-primary">' . __( 'Register', 'pdcrequest' ) . '</button>';
        $html .= '<input type="hidden" name="user_role" value="customer">';
        $html .= wp_nonce_field( 'register', 'registernonce', false, false );
        $html .= '</fieldset>';
        
        return $html;
    }
    
    
    public static function login_form() {
        $html = '<fieldset class="setlr-login">';
        $html .= '<legend>' . __( 'Already a customer? Please login', 'pdcrequest' ) . '</legend>';
        
        $html .= '<label>' . __( 'Login Email', 'pdcrequest');
        $html .= '<input type="email" id="user_login" name="user_login"></label>';
        $html .= '<label>' . __( 'Password', 'pdcrequest');
        $html .= '<input type="password" id="user_pwd" name="user_pwd"></label>';
        $html .= '<button id="login" class="setlr-button button-primary">' . __( 'Log In', 'pdcrequest' ) . '</button>';
        $html .= '<input type="hidden" name="user_role" value="customer">';
        $html .= wp_nonce_field( 'login', 'loginnonce', false, false );
        $html .= '</fieldset>';
        
        return $html;
    }
    
    
    public static function ajax_register_customer() {
        $data = $_POST;
        write_log( 'Setlr_Profile_Form ajax_register_customer');
        write_log( $data );
        if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'register' ) ) :
            write_log( 'ajax_register_customer nonce did not validate');
            exit();
	endif;
        
        if ( isset( $data['nickname'] ) && isset( $data['user_email']) && isset( $data['user_pass']) && isset( $data['user_pass2'])) :
            //verify email
            $user_email = ( is_email($data['user_email'] ) ) ? $data['user_email'] : '';
            if ( ! $user_email ) :
                write-log( 'not user_email' );
                echo 0;
                exit();
            endif;
            //verify password
            if ( $data['user_pass'] != $data['user_pass2'] ) :
                write-log( 'non matching passwords' );
                echo 0;
                exit();
            endif;
            
            $total = (isset( $data['total'] ) && floatval( $data['total'] ) ) ? $data['total'] : 0;
            $user_id = self::insert_user($data);
            
            if ( is_wp_error( $user_id ) || $user_id == 0) :
                write_log( 'non valid user_id');
                write_log( $user_id );
                echo 0;
                exit();
            else :
                write_log( 'user_id='.$user_id );
                write_log( 'total='.$total);
                echo Setlr_Request::display_actions_fragment( $user_id, $total );
                exit();
            endif;
        else :
            echo 0;
            exit();
        endif;
    }
    
    /**
     * validate a customer in login form on setlr-request
     * @return mixed int|bool false
     */
    public static function ajax_validate_customer() {
        write_log( $_POST );
        $data = $_POST;
        write_log( 'Setlr_Profile_Form ajax_validate_customer');
        write_log( $data );
        
        if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'login' ) ) :
            write_log( 'ajax_validate_customer nonce did not validate');
            write_log( $data['nonce'] );
	endif;
        
        if ( isset( $data['user_login'] ) && isset( $data['user_pwd'] ) && !empty( $data['user_login'] ) && !empty( $data['user_pwd'] ) ) :
            $user = wp_authenticate_username_password( NULL, $data['user_login'], $data['user_pwd'] );
        endif;
        
        $total = (isset( $data['total'] ) && floatval( $data['total'] ) ) ? $data['total'] : 0;
        
        if ( !isset( $user ) || is_wp_error( $user ) ) :
                write_log( 'non valid user');
                write_log( $user );
                echo 0;
                exit();
            else :
                write_log( 'user='. $user->ID );
                write_log( 'total='.$total);
                
                if ( user_can( $user, 'customer' ) ) :
                    $args = array(
                        'user_email'    => $user->user_login,
                        'user_pass'     => $user->user_pass
                    );
                    self::login_customer( $args );
                    echo Setlr_Request::display_actions_fragment( $user->ID, $total );
                else :
                    _e( 'You are not a customer', 'pdcrequest' );
                endif;
                exit();
            endif;
    }
    
    
    public static function insert_user( $data ) {
        $args = array(
            'user_pass'     => $data['user_pass'],
            'user_login'    => $data['user_email'],
            'user_email'    => $data['user_email'],
            'nickname'      => $data['nickname'],
            'role'          => 'customer'
        );
        
        $user_id = wp_insert_user( $args );
        
        if ( absint( $user_id ) ) :
            self::login_customer($data);
        endif;
        
        return $user_id;
    }
    
    
    public static function login_customer( $data ) {
	$creds = array();
	$creds['user_login'] = $data['user_email'];
	$creds['user_password'] = $data['user_pass'];
	$creds['remember'] = true;
	$user = wp_signon( $creds, false );
        
	if ( is_wp_error( $user ) ) :
            write_log( $user->get_error_message() );
        endif;
        
    }

}

