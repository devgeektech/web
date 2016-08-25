<?php


/**
 * Description of Mailrequest
 * Get requests from email
 *
 * @author philippe
 */
class Mailrequest {
    //put your code here
    
    
    var $stream;
    
    var $server;
    
    var $port;
    
    var $flags;
    
    var $username;
    
    var $password;
    
    
    public function __contruct( $stream ) {
        $this->stream = $stream;
    } 
    
    
    public function get_resource( $server, $port, $flags, $username, $password ) {
        $authserver = "{" . $server . ":" . $port . $flags . "}";
        $resource = imap_open( $authserver, $username, $password );
        
        return $resource;
    }
    
    
    /*
     * Steps:
     * 1) open stream
     * 2) get messages
     * 3) check sender
     * 4) if sender is a customer then get requestlang from header
     * 5) get body
     * 6) extract body sample to determine lang
     * 6) determine doclang using lang class ( lang::determine_lang( $string ) );
     * 7) create job using request class
     * 8) reply to email with job_id or error
     */
    
    public static function extract_sample( $string, $length = 300 ) {
        return mb_substr($string, 0, $length);
    }
    
}
