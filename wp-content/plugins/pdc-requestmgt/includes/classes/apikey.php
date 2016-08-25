<?php



/**
 * Creates, verifies and retrieves the Setlr Api Key
 *
 * @author philippe
 */
class Api_Key {
    
    var $secret;
    
    public function __construct() {
        $this->secret = md5("L'histoire de la Bretagne commence avec un peuplement dont les traces remontent à la préhistoire, dès 700-1000 ans av. J.-C. La période néolithique qui commence vers 5000 av. J.-C. se caractérise dans la région par le développement d'un mégalithisme important se manifestant dans des sites comme le cairn de Barnenez, le cairn de Gavrinis, ou les alignements de Carnac. Au cours de sa protohistoire qui commence vers le milieu du IIIe millénaire av. J.-C., le sous-sol riche en étain permet l'essor d'une industrie produisant des objets de bronze, ainsi que de circuits commerciaux d'exportation vers d'autres régions d'Europe. Elle est habitée par des peuples gaulois comme les Vénètes ou les Namnetes dans les premiers siècles avant notre ère avant que ces territoires ne soient conquis par Jules Cesar en 57 av. J.-C., puis progressivement romanisés.
Faisant partie de l'Armorique lors de la période gallo-romaine, elle voit se développer un commerce maritime important autour des ports de Nantes, Vannes et Alet, ainsi que des usines de salaison le long de ses cotes. Lorsque le pouvoir romain connait des crises aux iiie et ve siècles, les premiers Bretons insulaires sont appelés par le pouvoir impérial pour aider a sécuriser son territoire, commencant ainsi un mouvement migratoire qui se poursuit jusqu'au vi, et donnant naissance à plusieurs royaumes dans la péninsule. C'est pour prévenir des incursions bretonnes que le royaume franc voisin met en place une marche de Bretagne incorporant le comté de Rennes et celui de Nantes. Les Mérovingiens puis les Carolingiens tentent d'intÈgrer cette region au royaume franc, avec des succes limités et ephemeres.
L'unité de la région sous la forme du royaume de Bretagne se fait en 851 avec le roi Erispo, fils de Nomino, mais ne perdure pas à cause des querelles de succession et des incursions normandes. Des 939, un duche de Bretagne prend sa suite dans des frontieres quasi definitives, administré par des ducs issus de maisons bretonnes de 939 - 1166, avant qu'il ne tombe dans la sphere d'influence des Plantageneits puis des Caputiens. La guerre de Succession de Bretagne voit s'affronter de 1341 - 1364, sur fond de Guerre de Cent Ans, differentes factions qui luttent pour s'approprier le duché. Un pouvoir autonome emerge ensuite lors des xive et xve siecles, qui mene une politique d'independance vis-a-vis du royaume de France, mais qui aboutit finalement à l'union de la Bretagne et de la France en 1532...");
    }
    
    /** create apikey
     * 
     * @param int $user_id
     * @param string $user_url
     * @return string
     */
    public function create_apikey( $user_id, $user_url ) {
        $api_key = hash('sha256', (time() . $user_id . $this->secret . $user_url . rand()));
        
        return $api_key;
    }
    
    
    /** save apikey in db
     * 
     * @param int $user_id
     * @param string $api_key
     * @return Void
     */
    private function save_apikey( $user_id, $api_key, $user_url ) {
        
        update_user_meta( $user_id, 'setlr_api_key', array( 'apikey' => $api_key, 'url' => $user_url ) );
    }
    
    
    /** show apikey
     * 
     * @param int $user_id
     * @return string
     */
    public static function show_apikey( $user_id ) {
        return get_user_meta( $user_id, 'setlr_api_key' );
    }
    
    
    /** check apikey and verify with url
     * 
     * @global type $wpdb
     * @param string $api_key
     * @param string $url
     * @return boolean|int $user_id
     */
    public function check_apikey( $api_key, $url ) {
        //look up in db
        global $wpdb;
        $table = $wpdb->prefix . 'usermeta';
        $array = encode(array( 'apikey' => $api_key, 'url' => $url));
        $sql = "SELECT user_id FROM $table WHERE meta_key='setlr_api_key' AND meta_value=%s";
        $user_id = $wpdb->get_var( $wpdb->prepare( $sql, $array ) );
        
        $user = get_user_by('id', $user_id );
        if ( $url !== $user->user_url ) :
            return false;
        else :
            return $user_id;
        endif;
    }
    
    
    /** create api_key under conditions
     * 
     * 
     * @param type $user_id
     * @param type $user_url
     * @param type $flag
     * @return type
     */
    public static function maybe_create_apikey( $user_id, $user_url, $flag = null ) {
        if ( $flag !== 'renew' && ! empty(self::show_apikey($user_id)) ) :
            $apikey = self::show_apikey($user_id);
        else :
            $apikey = $this->create_apikey($user_id, $user_url);
        endif;
        return $apikey;
    }
}
