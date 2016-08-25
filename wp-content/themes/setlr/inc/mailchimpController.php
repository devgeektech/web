<?php
add_action( 'user_register', 'mailchimp_hop_on_registration', 10, 1 );

function mailchimp_hop_on_registration( $user_id ) {

		$role  = get_user_meta( $user_id, 'main_role', true );
		$user_info = get_userdata($user_id);
		$email = $user_info->user_email;
		wp_update_user( array( 'ID' => $user_id, 'role' => $role ) );

    	$groupName = ($role == 'helper' ? 'Translator News' : 'Customer News');

    	 $MailChimp = new MailChimp('25323f760cd6ca9d4502a76eb96b9a8c-us9');
    	 $result = $MailChimp->call('lists/subscribe', array(
    	     'id'                => '5f13bc7863',
    	     'email'             => array('email'=> $email),
    	     'merge_vars'        => array(
    	     	'groupings' => array(
    	     	    array(
    	     	        'id' => 18965,
    	     	        'groups' => array($groupName)
    	     	    )
    	     	)
    	     ),
    	     'double_optin'      => false,
    	     'update_existing'   => true,
    	     'replace_interests' => false,
    	     'send_welcome'      => false
    	 ));

    	return $result;
}
?>