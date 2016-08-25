<?php
/*
Plugin Name: PdC-Login
Plugin URI: http://ispectors.com.com/plugins/pdc-login/
Description: Customization of WP login-register functions for Setlr.com Version from test.setlr.com
Version: 0.7.0
Author: pdc
Author URI: http://ispectors.com/philippe-de-chabot/
Text Domain: pdclogin
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	_e( 'Hi there!  I\'m just a plugin, not much I can do when called directly.', 'pdclogin' );
	exit;
}


/* 1) add new fields to registration_form */
add_action( 'register_form', 'pdclogin_register_form' );

if ( !function_exists( 'pdclogin_register_form' ) ) :
function pdclogin_register_form() {
	//$real_name = ( ! empty( $_POST['real_name'] ) ) ? trim( $_POST['real_name'] ) : '';
        write_log( 'pdclogin_register_form' );
        ?>
        <!--<p>
            <label for="real_name"><?php //_e( 'Real Name', 'pdclogin' ) ?><br />
                <input type="text" name="real_name" id="real_name" class="input" value="<?php echo esc_attr( wp_unslash( $real_name ) ); ?>" size="25" />
            </label>
        </p>-->
        <p>
            <label class="screen-reader-text" for="main_role"><?php _e( 'Choose a role', 'pdclogin' ) ?></label><br />
                <select id="main_role" name="main_role">
                	<option value="helper"><?php _e( 'I am a translator', 'pdclogin' ); ?></option>
                    <option value="customer"><?php _e( 'I am a client', 'pdclogin' ); ?></option>
                </select>
            
        </p>
        <?php
}
endif;

/** 1bis) hide username field */
//add_action('login_head', 'hide_username' );
function hide_username(){
?>
    <style>
        #registerform > p:first-child{
            display:none;
        }
    </style>
<?php
};


/* 2) verify new fields of registration_form */

add_filter( 'registration_errors', 'pdclogin_registration_errors', 10, 3 );

if ( !function_exists( 'pdclogin_registration_errors' ) ) :
function pdclogin_registration_errors( $wp_error, $sanitized_user_login, $user_email ) {
	write_log( 'pdclogin_registration_errors' );
	/*
	 if ( empty( $_POST['real_name'] ) || ! empty( $_POST['real_name'] ) && trim( $_POST['real_name'] ) == '' ) :
            $errors->add( 'real_name_error', __( '<strong>ERROR</strong>: You must include your real name.', 'pdclogin' ) );
     endif;
	 */
	 if(isset($wp_error->errors['empty_username'])){
        $wp_error->remove('empty_username');
    }    
    if(isset($wp_error->errors['username_exists'])){
        $wp_error->remove('username_exists');
    }
	 $white_list = array( 'helper', 'customer' );
	 if ( empty( $_POST['main_role'] ) || ! in_array( $_POST['main_role'], $white_list ) ) :
            $wp_error->add( 'main_role_error', __( '<strong>ERROR</strong>: You must select a main role.', 'pdclogin' ) );
     endif;

     return $wp_error;
}
endif;

/** 3) use email for user_login */
add_action('login_form_register', 'copy_email_to_username');

function copy_email_to_username(){
	global $wp_error;
	write_log( 'copy_email_to_username' );
    if ( isset($_POST['user_email']) && !empty($_POST['user_email']) && is_email($_POST['user_email']) ) {
        $_POST['user_login'] = $_POST['user_email'];
    } else {
		//$wp_error->add( 'user_email_error', __( '<strong>ERROR</strong>: You must enter a valid email.', 'pdclogin' ) );
	}
}


/* 3bis) update db with content of new registration fields */
add_action( 'user_register', 'pdclogin_user_register' );

if ( !function_exists( 'pdclogin_user_register' ) ) :
function pdclogin_user_register( $user_id ) {
	write_log( 'pdclogin_user_register' );
	$whitelist = array( 'helper', 'customer' );
	if ( !empty( $_POST['real_name'] ) ) :
		update_user_meta( $user_id, 'real_name', trim( $_POST['real_name'] ) );
	endif;	
	if ( !empty( $_POST['main_role'] ) && in_array( trim( $_POST['main_role'] ), $whitelist ) ) :
		update_user_meta( $user_id, 'main_role', trim( $_POST['main_role'] ) );
	endif;	

}
endif;

/* 4) redirect setlers to my-dashboard after registration */
//add_filter( 'registration_redirect', 'pdclogin_registration_redirect' );
//add_filter( 'jobify_registeration_redirect', 'pdclogin_registration_redirect' );

if ( !function_exists( 'pdclogin_registration_redirect' ) ) :
function pdclogin_registration_redirect() {
	write_log( 'pdclogin_registration_redirect' );
    return home_url( 'email-confirmed' );
}
endif;


/* 5) redirect normal users to my-dashboard page and administrators to where they want */
add_filter( 'login_redirect', 'pdclogin_login_redirect', 10, 3 );

if ( !function_exists( 'pdclogin_login_redirect' ) ) :
function pdclogin_login_redirect( $redirect_to, $request, $user ) {
	global $user;
	write_log( 'pdclogin_login_redirect' );
	if ( isset( $user->roles ) && is_array( $user->roles ) ) :
		if ( in_array( 'administrator', $user->roles ) ) :
			return $redirect_to;
		else :
			return esc_url( get_permalink( get_page_by_title( 'My Dashboard' ) ) );
		endif;
	else :
		return $redirect_to;
	endif;
}
endif;


/* 6) use email or username for login */
// Remove the default authentication function
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );

// Add the custom authentication function
add_filter( 'authenticate', 'pdclogin_authenticate_username_password', 20, 3 );

/**
 * Filter the WP login system
 *
 * @param object $user
 * @param string $username
 * @param string password
 * @return function
 */
if ( !function_exists( 'pdclogin_authenticate_username_password' ) ) :
function pdclogin_authenticate_username_password( $user, $username, $password ) {
	write_log( 'pdclogin_authenticate_username_password' );
	
	
		// Get the WP_User object based on the email address
		if ( ! empty( $username ) ) :
			
			$username = str_replace( 'mailto:', '', $username );
			
			if ( is_email( $username ) ) :
				
				$user = get_user_by( 'email', $username );
				
			else :
				$user = get_user_by( 'login', $username );
				
			endif;
		endif;
		
		write_log( '---------------------------------' );
		write_log( $user );
		write_log( '---------------------------------' );
		
		// Return a customized WP_Error object if a WP_User object was not be returned (i.e. The email doesn't exist or a regular username was provided)
		if ( ! $user ) {
			//return new WP_Error( 'invalid_username_email', sprintf( __( '<strong>ERROR</strong>: Invalid username. Please log in with your email address. <a href="%s" title="Password Lost and Found">Lost your password</a>?' ), wp_lostpassword_url() ) );
		}
		
		// Hand authentication back over to the default handler now that we a have a valid WP_User object based on the email address
			return wp_authenticate_username_password( NULL, $user->user_login, $password );
		
	
}
endif;


/* 6) tidy use of email for login form label */
//add_filter( 'gettext', 'pdclogin_user_email_login_text', 20, 3 );

  
if ( !function_exists( 'pdclogin_user_email_login_text' ) ) :
function pdclogin_user_email_login_text( $translated_text, $text, $domain ) {
	global $pagenow; 
	write_log( 'pdclogin_user_email_login_text' );
	if ( $pagenow === 'wp-login.php' ) :
		
		$pos = 7;
		
		if ( $pos > 0  ) :
			switch ( $translated_text ) :
				case 'Username' :
					$translated_text = __( 'Username' );
					break;
			endswitch;
		else :
			switch ( $translated_text ) :
				case 'Username' :
					$translated_text = __( 'Email' );
					break;
			endswitch;
		endif;
  	endif;
  
  	return $translated_text;
}
endif;

/** 
 * styling login/register 
 * @uses wp_enqueue_style
 */
function pdclogin_logo() { 
	wp_enqueue_style( 'login-css', plugins_url() . '/pdc-login/css/login.css', false, '0.7', 'all' ); 
}
add_action( 'login_enqueue_scripts', 'pdclogin_logo' );

function pdclogin_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'pdclogin_logo_url' );

function pdclogin_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'pdclogin_logo_url_title' );


add_shortcode( 'pdcrestrict', 'pdclogin_restrict_mgt' );

/** 
 * adds shortcode [pdcrestrict role="helper"] [/pdcrestrict]
 * to restrict content for helpers or setlrs
 * no limit but info on restriction for admin
 */
function pdclogin_restrict_mgt( $atts, $content = null ) {
	global $current_user;
	get_currentuserinfo();	
	$user_id = $current_user->ID;
	$role = get_user_meta( $user_id, 'main_role', true );
	$a = shortcode_atts( array(
        'role' => 'helper',
    ), $atts );
	
	if ( current_user_can( 'manage_options' ) ) :
		return sprintf( '[%s %s]', __( 'only for', 'pdclogin' ), esc_attr( $a['role'] ) ) . do_shortcode( $content ) . sprintf( '[%s]', __( 'end restriction', 'pdclogin' ) );
	else :
	
		if ( $a['role'] === $role ) :
			return do_shortcode( $content );
		else :
			return '';
		endif;
	endif;
}


if ( !function_exists('wp_new_user_notification') ) :
/**
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 *
 * @since 2.0.0
 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
 *
 * @param int    $user_id User ID.
 * @param string $notify  Whether admin and user should be notified ('both') or
 *                        only the admin ('admin' or empty).
 */
function wp_new_user_notification( $user_id, $notify = '' ) {
	global $wpdb;
	write_log( 'wp_new_user_notification' );
	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";

	wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);
	
	/*
	if ( 'admin' === $notify || empty( $notify ) ) {
		return;
	}
	*/
	// Generate something random for a password reset key.
	$key = wp_generate_password( 20, false );

	/** This action is documented in wp-login.php */
	do_action( 'retrieve_password_key', $user->user_login, $key );

	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
            
        
        $message = __( 'Hi, welcome to Setlr!', 'pdclogin') . "\r\n\r\n";
        $message .= __( "We can't wait to see how much you love using the site.", "pdclogin") . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= __('To set your password, visit the following address:') . "\r\n\r\n";
	$message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . "\r\n\r\n";

	//$message .= wp_login_url() . "\r\n";
        
        $message .= "Greg" . "\r\n\r\n";
        $message .= "Setlr setlr.com". "\r\n\r\n";

	$user_emailed = wp_mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), $message);
	if ( $user_emailed == false ) :
		write_log( 'wp_new_user_notification email failed' );
	else :
		write_log( 'wp_new_user_notification email success' );
	endif;
}
endif;


function pdclogin_enqueue_login_scripts() {
    wp_enqueue_script( 'setlrlogin', plugins_url('/pdc-login/js/pdclogin-setlrlogin.js'), array('jquery'), '1.0', true );
}
add_action('login_enqueue_scripts', 'pdclogin_enqueue_login_scripts');