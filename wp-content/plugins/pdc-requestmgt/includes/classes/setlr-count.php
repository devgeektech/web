<?php


/**
 * Class to handle the counting of words
 * It starts by sanitizing the text (eliminating html tags, punctuation marks, and stop words)
 * 
 * @author philippe
 */
class Setlr_Count {
    
    /**
     *
     * @var string language code 
     */
    var $lang;
    
    
    /**
     *
     * @var string thetext to count words from 
     */
    var $text;
    
    
    /**
     *
     * @var array stop words by language code 
     */
    var $stop_words = array(
        'en'    => array(
            
        ),
        'fr'    =>  array(
            
        )
    );
    
    
    /**
     *
     * @var array punctuation marks in all languages (almost)
     */
    var $punctuations = array( "' ", '"', ',', '.', '?', '¿');
    
    
    /**
     *
     * @var array 
     * @todo regex to eliminate content of ids and classes
     */
    var $html_tags = array( 
            '<img', 'class="', 'id="', '<a', 'href="', '<blockquote', '<ul', '<li', '<br>', '<br/>', '<p>', '</p>', '<div', '</div>',
            'src="', 'alt=', '/>', '<p');
    
    /**
     * 
     * @param string $text the text we need to count
     * @param string $lang the language code of the text
     */
    public function __construct( $text, $lang ) {
        
        $this->lang = $lang;
        $this->raw_text = $text;
        $this->text = $text;
    }
    
    
    /**
     * count the number of words after eliminating stop words
     * @return int the number of words in text
     */
    public function count() {
        $text = $this->sanitize_text();
        
        /* now we can count */
        $count = str_word_count( $text, 0 );
        
        return $count;
    }
    
    
    /**
     * sanitize text by eliminating html and stop words
     */
    public function sanitize_text() {
        $tags = ( ! empty( $this->html_tags ) ) ? array_merge( $this->html_tags, $this->punctuations ) : array();
        
        if ( ! empty( $tags ) ) :
            /* eliminate stop words from text */
            foreach ( $tags as $tag ) :
                $this->text = str_replace( $tag, "", $this->text );
            endforeach;
        endif;
        
        $stops = ( array_key_exists( $this->lang, $this->stop_words ) ) ? $this->stop_words[$this->lang] : array();
        
        if ( ! empty( $stops ) ) :
            /* eliminate stop words from text */
            foreach ( $stops as $stop ) :
                $this->text = str_replace( $stop, "", $this->text );
            endforeach;
        endif;
        
        return $this->text;
    }
}

