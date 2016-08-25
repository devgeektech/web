<?php



/**
 * Description of newPHPClass
 *
 * @author philippe
 */
class Revision_Calculator {
    //put your code here
    var $revision_id;
    
    var $translation_id;
    
    
    public function __construct( $revision_id, $translation_id ) {
        $this->revision_id = $revision_id;
        $this->translation_id = $translation_id;
        $this->revision_score = $this->calculate_score( $revision_id, $translation_id );
        
    }
    
    
    /**
     * calculate the revision score
     * 
     * @param int $revision_id
     * @param int $translation_id
     * @return float
     * 
     * @uses php similar_text function
     * @notes the order of variables is important as similar_text can vary
     */
    private function calculate_score( $revision_id, $translation_id ) {
        $revision = get_post( $revision_id );
        $translation = get_post( $translation_id );
        
        $revision_content = $revision->post_content;
        $translation_content = $translation->post_content;
        $revision_length = mb_strlen( $revision_content, 'utf-8');
        $translation_length = mb_strlen( $translation_content, 'utf-8');
        
        $score = get_post_meta( $revision_id, 'setlr_revision_score', true );
        
        /* if score has already been calculated and saved we retrieve it, else we calculate it */
        if ( ! $score ) :
            //over 20000 characters, similar_text is really slow and can bug
            $limit = 20000;
            if ( max( $revision_length, $translation_length ) < $limit ) :
                //use similar_text
                similar_text($translation_content, $revision_content, $score);
            else :
                //split each text is chunks that can be worked with similar_text
                $revision_parts = str_split( $revision_content, $limit );
                $translation_parts = str_split( $translation_content, $limit );
                
                $count = min( count( $revision_parts), count( $translation_parts ) );
                
                for ( $i = 0; $i < $count; $i++ ) :
                    similar_text( $translation_parts[$i], $revision_parts[$i], $score_parts );
                    $score_array[] = $score_parts; 
                endfor;
                $score = array_sum( $score_array ) / count( $score_array );
            endif;
        endif;
        
        return $score;
    }
    
    
    public function save_revision_score() {
        $update = update_post_meta( $this->revision_id, 'setlr_revision_score', $this->revision_score );
        
        return $update;
    }
    
    
}
