<?php


/**
 * Sets up and manages project's revisions as asked by a customer
 *
 * 
 * @author philippe
 * @since version 0.9.5
 */
class Setlr_Rejection_Request {

    var $project_id;
    
    var $translation_id;
    
    var $rejection_id;
    
    
    /**
     * construction for Setlr_Revision
     * @param int $project_id
     * @param int $translation_id
     * @param int $revision_id
     */
    public function __construct( $project_id, $translation_id, $rejection_id = null ) {
        $this->project_id = $project_id;
        $this->translation_id = $translation_id;
        
        if ( isset( $revision_id) && absint( $rejection_id ) ) :
            //update revision
            $this->update_rejection_request( $rejection_id );
        else :
            //create revision
            $rejection_id = $this->create_rejection_request();
        endif;
        $this->rejection_id = $rejection_id;
    }
    
    
    private function update_rejection_request( $rejection_id ) {
        global $post, $current_user;
        //do something
        $args = array(
            'ID'            => $rejection_id,
            'post_author'   => $current_user->ID
        );
        wp_update_post($args);
        
        return $rejection_id;
    }
    
    private function create_rejection_request() {
        global $post;
        //do something
        $project = get_post( $this->project_id );
        
        if ( $project instanceof WP_Post ) :
        
            $args = array(
                'post_title'    => $project->post_title,
                'post_content'  => '',
                'post_type'     => 'setlr_rejection'
            );

            $rejection_id = wp_insert_post( $args );
            return $rejection_id;
        
        else :
            return false;
        endif;
    }
    
    
    
    /**
     * create the setlr_revision custom post type for WordPress admin
     * 
     * @uses register_post_type function
     */
    public static function create_rejection_request_post_type() {
        
		$labels = array(
			'name'               => _x( 'Rejection Requests', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Rejection Request', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Rejection Requests', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Rejection Request', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'setlr_rejection', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Rejection Request', 'pdcrequest' ),
			'new_item'           => __( 'New Rejection Request', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Rejection Request', 'pdcrequest' ),
			'view_item'          => __( 'View Rejection Request', 'pdcrequest' ),
			'all_items'          => __( 'All Rejection Requests', 'pdcrequest' ),
			'search_items'       => __( 'Search Rejection Requests', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Rejection Requests:', 'pdcrequest' ),
			'not_found'          => __( 'No rejection requests found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No rejection requests found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'setlr_rejection' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author' )
		);
	
		register_post_type( 'setlr_rejection', $args );
	
    }
    
    
    public static function show_rejection_request_form() {
        
        if ( ! current_user_can('customer') ) :
            $html = '<p class="error setlr-error no-rights">' . __( 'Sorry you do not have enough rights to access this page.', 'pdcrequest' ) . '</p>';
        else :
            $html = '<form name="new-rejection-request" method="post" action="' . admin_url('admin.php') . '">';
            $html .= '<p class="setlr-excuse">' . esc_html( __( "We're really sorry to hear you might have a problem. You can reject your project but please consider it as a last resort. We'll do everything we can to help you to get the issue fixed and put a smile on your face.", 'pdcrequest' ) ) . '</p>';
            $html .= self::list_reasons_form();
            $html .= self::get_description_form();
            $html .= '<p class="setlr-action">';
            //allow to go to revision-request instead
            $html .= '<a href="#" class="button button-secondary" title="">' . __( 'Request Revision', 'pdcrequest') . '</a>';
            $html .= '<input type="submit" class="button button-primary" name="submit" value="' . __( 'Confirm Rejection', 'pdcrequest' ) . '">';
            $html .= '</p>';
            $html .= wp_nonce_field('setlr-rejection-request', 'setlr-nonce', true, false );
            $html .= '<input type="hidden" name="project_id" value="' . absint( $_GET['project_id'] ) . '">';
            $html .= '<input type="hidden" name="action" value="validate_rejection_request">';
            $html .= '</form>';
        endif;
        
        return $html;
    }
    
    
    public static function list_reasons_form() {
        $reasons = self::get_reasons_list();
        
        $html = '<h2>' . esc_html( __( 'Please check all reasons that apply.', 'pdcrequest' ) ) . '</h2>';
        $html .= '<ul>';
        foreach ( $reasons as $code => $name ) :
            $html .= '<li><label><input type="checkbox" name="reasons[]" value="' . esc_attr( $code ) . '">';
            $html .= esc_html( $name ) . '</label></li>';
        endforeach;
        $html .= '</ul>';
        
        return $html;
    }
    
    
    public static function list_reasons( $post_id ) {
        //get the reasons as an array
        $reason_string = get_post_meta( $post_id, 'setlr_reasons', true );
        // return the array as an escaped html unordered list
        parse_str( $reason_string, $reasons );
        
        $html = '';
        if ( !empty( $reason_string)) :
            $html = '<ul class="entry-reasons>';
        
            if ( isset( $reasons) && !empty($reasons )) :
                foreach ( $reasons as $reason):
                $html .= '<li>' . esc_html( $reason ) . '</li>';
                endforeach;
            endif;
            $html .= '</ul>';
        endif;
        
        return $html;
    }
    
    public static function get_reasons_list() {
        $reasons = array(
            'terminology'   => __( 'Terminology', 'pdcrequest' ),
            'context'   => __( 'Context', 'pdcrequest' ),
            'spelling'   => __( 'Spelling', 'pdcrequest' ),
            'grammar'   => __( 'Grammar', 'pdcrequest' ),
            'tense'   => __( 'Tense', 'pdcrequest' ),
            'cultural_inacurracy'   => __( 'Cultural Inacurracy', 'pdcrequest' ),
            'incomplete'   => __( 'Incomplete', 'pdcrequest' ),
            'other'   => __( 'Other reason', 'pdcrequest' ),
        );
        
        return $reasons;
    }
    
    public static function get_description_form() {
        $html = '<h2>' . esc_html( __( 'Please describe the problem.', 'pdcrequest' ) ) . '</h2>';
        $html .= '<textarea name="description"></textarea>';
        
        return $html;
    }
    
    
    public static function get_description( $post_id ) {
        //grab the description
        $post = get_post( $post_id);
        //return description as escaped html div
        $html = '<div class="entry-description">';
        $html .= wp_kses_post( wpautop( _($post->post_content ) ) );
        $html .= '</div>';
        
        return $html;
    }
    
    public static function show_rejection_request( $rejection_request_id ) {
        
        $html = '<article id="rejection-request-' . absint( $rejection_request_id ) . '" class="setlr-rejection-request post">';
        $html .= '<header class="entry-header">';
        $html .= '</header>';
        $html .= '<div class="entry-content">';
        $html .= self::list_reasons( $rejection_request_id );
        $html .= self::get_description( $rejection_request_id );
        $html .= '</div>';
        $html .= '<footer class="entry-footer">';
        
        $html .= '</footer>';
        $html .= '</article>';
        
        return $html;
    }
    
    
    public static function validate_rejection_request() {
        global $current_user;
        
        //check security 
        $nonce = $_POST['setlr-nonce'];
        if( ! wp_verify_nonce($nonce, 'setlr-rejection-request') ) die('Busted!');
       
        //check project_id
        if ( ! isset( $_POST['project_id']) || ! absint( $_POST['project_id'])) :
            die( 'Busted no project');
            write_log( 'pb with project' . $_POST['project_id']);
        else :
            //we can continue
            $project = get_post( $_POST['project_id'] );
            write_log( 'project = '. $project->ID );
            
            //check description and project
            if ( isset( $_POST['description'] ) && ! empty( $_POST['description']) && ( $project instanceof WP_Post ) ) :
                $arg = array(
                    'post_title'     => $project->post_title,
                    'post_content'   => $_POST['description'],
                    'post_author'    => $project->post_author,
                    'post_type'      => 'setlr_rejection',
                    'post_status'    => 'publish'
                );

                $post_id = wp_insert_post( $arg );
                write_log( 'ok');
            endif;

            if ( ! isset( $post_id) || ! absint( $post_id ) ) :
                //empty form redirect to revision_request and show errors
                $message = '<p class="error setlr-error empty-form">' . __('You need to fill this form.','pdcrequest') . '</p>';
                $page_id = pdcrequest_send_rejection_page();

            else :
                //put the data in DB and redirect to dashboard
                 if ( isset( $_POST['reasons'])) :

                     update_post_meta( $post_id, 'setlr_reasons', $_POST['reasons']);
                 endif;

                 $message = __('We have your rejection request and will process it immediately.','pdcrequest');
                $page_id = pdcrequest_dashboard();
            endif;
        endif;
        write_log( 'page_id= '. $page_id );
        Pdc_Requestmgt::redirect_to_page($page_id, $message);
    }
    
    
    /**
     * get the project and translation ids for a given revision request
     * @param int $post_id
     * @return array 'project_id' => int, 'translation_id' => int
     */
    public static function get_rejection_information( $post_id ) {
        $meta = get_post_meta( $post_id, 'revision_request_ids', true );
        
        parse_str( $meta, $info );
        
        return $info;
    }
    
}
