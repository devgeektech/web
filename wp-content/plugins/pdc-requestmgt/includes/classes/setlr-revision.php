<?php


/**
 * Sets up and manages project's revisions as asked by a customer
 *
 * 
 * @author philippe
 * @since version 0.9.5
 */
class Setlr_Revision {

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
            $this->update_revision( $revision_id );
        else :
            //create revision
            $revision_id = $this->create_revision();
        endif;
        $this->revision_id = $revision_id;
    }
    
    
    private function update_revision( $revision_id ) {
        global $post, $current_user;
        //do something
        $args = array(
            'ID'            => $revision_id,
            'post_author'   => $current_user->ID
        );
        wp_update_post($args);
        return $revision_id;
    }
    
    private function create_revision() {
        global $post;
        //do something
        
        
        $args = array(
            'post_title'    => '',
            'post_content'  => ''
        );
        
        $revision_id = wp_insert_post( $args );
        return $revision_id;
    }
    
    
    
    /**
     * create the setlr_revision custom post type for WordPress admin
     * 
     * @uses register_post_type function
     */
    public static function create_revision_post_type() {
        
		$labels = array(
			'name'               => _x( 'Revisions', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Revision', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Revisions', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Revision', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'setlr_revision', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Revision', 'pdcrequest' ),
			'new_item'           => __( 'New Revision', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Revision', 'pdcrequest' ),
			'view_item'          => __( 'View Revision', 'pdcrequest' ),
			'all_items'          => __( 'All Revisions', 'pdcrequest' ),
			'search_items'       => __( 'Search Revisions', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Revisions:', 'pdcrequest' ),
			'not_found'          => __( 'No revisions found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No revisions found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'setlr_revision' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author' )
		);
	
		register_post_type( 'setlr_revision', $args );
	
    }
    
    public static function show_dashboard( $revision_id ) {
        global $post, $current_user;
        
        //check current user capability
        //if ( ! current_user_can( 'can_review_translations' ) ) :
           // return '<p class="setlr-error no-capability">' . __( 'You don not have sufficient rights to view this page.', 'pcrequest') . '</p>';
       // else :
            //let's gather details
            //current post is the revision so we need the project id and the translation id
            $ids = self::get_ids( $post->ID );
            $ids_array = parse_str( $ids );
            $translation_id = $ids_array['translation_id'];
            $project_id = $ids_array['project_id'];
            
            if ( ! absint( $translation_id ) && ! absint( $project_id ) ) :
                $html = '<p class="error setlr-error no-data">' . __( 'Sorry, we can not find this revision', 'pdcrequest' ) . '</p>';
                $html .= get_the_content();
            else :
            $project = get_post( $project_id );
            $translation = get_post( $translation_id );
            //let's show original post, translation, reasons for rejection as well as reviewer workspace
            $html = '<form id="revision-id-' . absint( $revision_id ) . '" method="post" name="setlr-revision-update" action="' . admin_url() . '">';
            $html .= '<article class="single-setlr-revision hentry">';
            $html .= '<header class="post-header">';
            $html .= '<h1 class="hentry-title">' . esc_html( $project->post_title ) . '</h1>';
            $html .= '</header>';
            $html .= '<div class="post-content hentry-content setlr-original-text">' . wpautop( $project->post_content ) . '</div>';
            $html .= '<div class="post-content hentry-content setlr-translation-text">' . wpautop( $translation->post_content ) . '</div>';
            $html .= '<footer class="hentry-footer"></footer>';
            $html .= self::show_reviewer_action_buttons();
            $html .= '</article>';
            $html .= '<input type="hidden" name="action" value="update-revision">';
            $html .= '<input type="hidden" name="revision_id" value="' . absint( $revision_id ) . '>';
            $html .= wp_nonce_field('setlr-update-revision', 'setlr-nonce', true, false );
            $html .= '</form>';
            endif;
            //finally let's add reviewer's action buttons
       // endif;
        
        return $html;
    }
    
    
    public static function render_revision_form( $revision_id ) {
        echo  self::show_dashboard($revision_id);
    }
    
    
    /**
     * get the project id and the translation id of a given revision id
     * @param int $revision_id
     * @return string stringified array of project_id and Translation_id
     */
    public static function get_ids( $revision_id ) {
        return get_post_meta( $revision_id, 'setlr_project_translation_ids', true );
    }
    
    
    /**
     * save the project id and translation id of a given revision in a post meta
     * @param int $revision_id
     * @param int $project_id
     * @param int $translation_id
     * @return mixed int meta_id if success, boolean false otherwise
     */
    public static function save_ids( $revision_id, $project_id, $translation_id ) {
        return update_post_meta( $revision_id, 'setlr_project_translation_ids', array( 
                'project_id'        => $project_id,
                'translation_id'    => $translation_id
            ));
    }
    
    
    public static function show_reviewer_action_buttons() {
        
        $html = '<ul class="setlr-action">';
        $html .= '<li><a href="#" class="button setlr-action" data-type="">' . __( 'Save', 'pdcrequest') . '</a></li>';
        $html .= '<li><a href="#" class="button setlr-action" data-type="">' . __( 'Save', 'pdcrequest') . '</a></li>';
        $html .= '<li><a href="#" class="button setlr-action" data-type="">' . __( 'Save', 'pdcrequest') . '</a></li>';
        $html .= '</ul>';
        
        return $html;
        
    }
    
    
}
