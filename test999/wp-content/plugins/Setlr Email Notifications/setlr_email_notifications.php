<?php
/**
* Plugin Name: Setlr Email Notifications
* Plugin URI: http://setlr.com
* Description: This plugin is for email notifications for http://setlr.com 
* Version: 1.0.0
* Author: guarav
* Author URI: http://setlr.com
*/

add_action( 'pdcrequest_update_project_payment', 'setler_email_notification_sent' );
function setler_email_notification_sent($project_id, $total, $currency) {
    
    $fromlang = get_post_meta( $project_id, 'from-lang' ,true);
    $tolang   = get_post_meta( $project_id, 'to-lang' ,true);
    $service  = get_post_meta( $project_id, 'setlr_service' ,true);
    $locale   = get_post_meta( $project_id, 'setlr-locale' ,true);
    $locale   = explode("-", $locale);
    $locale   = strtolower($locale[1]);


    $subject = "Help Notification";

/*---- Here You can Change the content of email ---- */ 
    $message = "There are matching tasks up for grabs, log in now so you don't miss them...";


    $blogusers = get_users( 'role=helper' );
    $email = array();

    foreach ( $blogusers as $user ) 
    {
        $user_langs = get_the_author_meta( 'requestlang', $user->ID );
        $native_lang = get_the_author_meta( 'nativelang', $user->ID );
        $native_locale = get_the_author_meta( 'locale', $user->ID );

    // ar --> sq_AL
        if($service == 'translation')
        {
            if($tolang == $native_lang && in_array($fromlang, $user_langs) || $native_locale == $native_lang)
            {
                $all =  array();
                $all['email'] = $user->user_email;
                $all['id'] = $user->ID;
                $all['type'] = "translation to f";
                $email[] = $all;    
                wp_mail( $user->user_email, $subject, $message );   
            }
        }

        if($service == 'question')
        {
            if(in_array($fromlang, $user_langs) || $native_locale == $native_lang || $tolang == $native_lang)
            {
                $all =  array();
                $all['email'] = $user->user_email;
                $all['id'] = $user->ID;
                $all['type'] = "question";
                $email[] = $all;    
                wp_mail( $user->user_email, $subject, $message );   
            }

        }
    }
}
