<?php
/**
* Pdc Geocode Settings admin page
*
* @author      Philippe de Chabot
* @package     pdcgeocode/admin
* @version     1.0
*/
global $pdcgeocode;
global $current_user;
//get_currentuserinfo();

// Set class property


$action = '';
$location = "options-general.php?page=pdcgeocode_settings"; // based on the location of your sub-menu page


$title = __( 'Geocode Setting', 'pdcgeocode' );
?>
<div class="wrap">
	<?php screen_icon(); ?>
    <?php settings_errors(); ?> 
	<h2>
		<?php echo esc_html( $title ); ?>
	</h2>
<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
    <input type="hidden" name="action" value="pdcgeocode_update_users" />
    <input type="submit" class="button-secondary" value="<?php _e( 'Update all users geotags', 'pdcgeocode' ); ?>" />
</form>
    <form method="post" action="options.php"> 
   		<?php
			settings_fields( 'geocode_options' );
            do_settings_sections( 'geocode_options' );
            ?>   
         

        <?php submit_button(); ?>
    </form>
</div>
