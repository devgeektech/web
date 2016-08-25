<?php


/**
 * Sets up and manages project's revisions as asked by a customer
 *
 * 
 * @author philippe
 * @since version 0.9.5
 */
class Setlr_Revision_Request {

    var $project_id;
    
    var $translation_id;
    
    var $revision_id;
    
    
    /**
     * construction for Setlr_Revision
     * @param int $project_id
     * @param int $translation_id
     * @param int $revision_id
     */
    public function __construct( $project_id, $translation_id, $revision_id = null ) {
        $this->project_id = $project_id;
        $this->translation_id = $$translation_id;
        
        if ( isset( $revision_id) && absint( $revision_id ) ) :
            //update revision
            $this->update_revision_request( $revision_id );
        else :
            //create revision
            $revision_id = $this->create_revision_request();
        endif;
        $this->revision_id = $revision_id;
    }
    
    
    private function update_revision_request( $revision_id ) {
        global $post, $current_user;
        //do something
        $args = array(
            'ID'            => $revision_id,
            'post_author'   => $current_user->ID
        );
        wp_update_post($args);
        return $revision_id;
    }
    
    private function create_revision_request() {
        global $post;
        //do something
        $project = get_post( $this->project_id );
        
        if ( $project instanceof WP_Post ) :
        
            $args = array(
                'post_title'    => $project->post_title,
                'post_content'  => '',
                'post_type'     => 'setlr_rev_request'
            );

            $revision_id = wp_insert_post( $args );
            return $revision_id;
        
        else :
            return new WP_Error( 'no_project_id', __( 'We can not find the project this refers to', 'pdcrequest'));
        endif;
    }
    
    
    
    /**
     * create the setlr_revision custom post type for WordPress admin
     * 
     * @uses register_post_type function
     */
    public static function create_revision_request_post_type() {
        
		$labels = array(
			'name'               => _x( 'Revision Requests', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Revision Request', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Revision Requests', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Revision Request', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'setlr_rev_request', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Revision Request', 'pdcrequest' ),
			'new_item'           => __( 'New Revision Request', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Revision Request', 'pdcrequest' ),
			'view_item'          => __( 'View Revision Request', 'pdcrequest' ),
			'all_items'          => __( 'All Revision Requests', 'pdcrequest' ),
			'search_items'       => __( 'Search Revision Requests', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Revision Requests:', 'pdcrequest' ),
			'not_found'          => __( 'No revision requests found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No revision requests found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'setlr_rev_request' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author' )
		);
	
		register_post_type( 'setlr_rev_request', $args );
	
    }
    
    
    public static function show_revision_request_form() {
        
        if ( ! current_user_can('customer') ) :
            $html = '<p class="error setlr-error no-rights">' . __( 'Sorry you do not have enough rights to access this page.', 'pdcrequest' ) . '</p>';
        else :
            $html = '<form name="new-revision-request" method="post" action="' . admin_url('admin.php') . '">';
            $html .= '<p class="setlr-excuse">' . esc_html( __( "We're really sorry to hear you might have a problem. Don't worry we'll do everything we can to help youget it sorted quickly.", 'pdcrequest' ) ) . '</p>';
            $html .= self::list_reasons_form();
            $html .= self::get_description_form();
            $html .= '<p class="setlr-action"><input type="submit" name="submit" value="' . __( 'Submit', 'pdcrequest' ) . '"></p>';
            $html .= wp_nonce_field('setlr-revision-request', 'setlr-nonce', true, false );
            $html .= '<input type="hidden" name="project_id" value="' . absint( $_GET['project_id'] ) . '">';
            $html .= '<input type="hidden" name="action" value="validate_revision_request">';
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
    
    public static function show_revision_request( $revision_request_id ) {
        
        $html = '<article id="revision-request-' . absint( $revision_request_id ) . '" class="setlr-revision-request post">';
        $html .= '<header class="entry-header">';
        $html .= '</header>';
        $html .= '<div class="entry-content">';
        $html .= self::list_reasons( $revision_request_id );
        $html .= self::get_description( $revision_request_id );
        $html .= '</div>';
        $html .= '<footer class="entry-footer">';
        
        $html .= '</footer>';
        $html .= '</article>';
        
        return $html;
    }
    
    
    public static function validate_revision_request() {
        global $current_user;
        
        //check security 
        $nonce = $_POST['setlr-nonce'];
        if( ! wp_verify_nonce($nonce, 'setlr-revision-request') ) die('Busted!');
       
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
                    'post_type'      => 'setlr_rev_request',
                    'post_status'    => 'pending' //important as it needs confirmation by admin
                );

                $post_id = wp_insert_post( $arg );
                write_log( 'ok');
            endif;

            if ( ! isset( $post_id) || ! absint( $post_id ) ) :
                //empty form redirect to revision_request and show errors
                $message = '<p class="error setlr-error empty-form">' . __('You need to fill this form.','pdcrequest') . '</p>';
                $page_id = pdcrequest_send_review_page();

            else :
                //put the data in DB and redirect to dashboard
                 if ( isset( $_POST['reasons'])) :

                     update_post_meta( $post_id, 'setlr_reasons', $_POST['reasons']);
                 endif;

                 $message = __('We have your revision request and will start the revision process immediately.','pdcrequest');
                $page_id = pdcrequest_dashboard();
            endif;
        endif;
        write_log( 'page_id= '. $page_id );
        Pdc_Requestmgt::redirect_to_page($page_id, $message);
    }
    
    
    /**
     * get the project and translation ids for a given revision request
     * @param int $post_id the ID of the revision request
     * @return array 'project_id' => int, 'translation_id' => int
     */
    public static function get_rev_request_information( $post_id ) {
        $meta = get_post_meta( $post_id, 'revision_request_ids', true );
        
        if ( is_array( $meta )) :
            $info = $meta;
        else :
            parse_str( $meta, $info );
        endif;
        
        return $info;
    }
    
}
