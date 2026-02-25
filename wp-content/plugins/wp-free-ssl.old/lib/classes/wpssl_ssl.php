<?php 

use LEClient\LEClient;
use LEClient\LEOrder;
use LEClient\LEFunctions;

use Psr\Log\LoggerInterface;

require_once WPSSL_DIR . 'lib/classes/wpssl_cpanel.php';
require_once WPSSL_DIR . 'lib/classes/wpssl_help.php';

class WPSSL_SSL{

    private $logger;
    private $baseDomain;
    function __construct(LoggerInterface $logger = null){
        $this->logger = $logger;
        $this->baseDomain = get_option('wpssl_basedomain','');
    }

    function getClient(){
        $emails = [get_option('wpssl_email')];
        
        //$client = new LEClient($emails,false,LEClient::LOG_STATUS, WPSSL_DIR . '/keys/');
        
        if ($this->logger) {
            $client = new LEClient($emails,false,$logger, WPSSL_DIR . '/keys/');
        }
        else{
            $client = new LEClient($emails,false,false, WPSSL_DIR . '/keys/');
        }
        
        
        
        return $client;
    }

    function generateOrder(){
        $client = self::getClient();
        $domains = self::getValidDomains();
        $basename = $this->baseDomain;
        try{
            return $client->getOrCreateOrder($basename, $domains);
        }
        catch(Exception $e){
            self::debugLetsEncrypt();
        }
    }

    function certificateInfofromDomain(){
        try{
            $url = "https://".get_option('wpssl_basedomain');
            $orignal_parse = parse_url($url, PHP_URL_HOST);
            $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
            $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
            $cert = stream_context_get_params($read);
            $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
            return $certinfo;
        }
        catch(Exception $e){
            return false;
        }
    }

    function certificateInfofromFile(){
        $cert = WPSSL_DIR . '/keys/certificate.crt';
        if(file_exists($cert)){
            
            $certinfo = openssl_x509_parse(file_get_contents($cert));
            
            return $certinfo;
        }
        else return false;
    }


    function getValidDomains(){
        $basename = get_option('wpssl_basedomain');
        $wildcard = get_option('wpssl_iswildcard',"0");
        if($wildcard=="1"){
            $domains = ["*.".$basename,$basename];
        }
        else{
            $domains = [$basename];
            if(self::checkWWW()){
                array_push($domains,'www.'.$basename);
            }
        }
        //var_dump($domains);
        return $domains;
    }

    function getChallenge($order,$type){

        return $order->getPendingAuthorizations($type); 
    }

    function getHttpChallenge(){
        $order = self::generateOrder();
        return self::getChallenge($order,LEOrder::CHALLENGE_TYPE_HTTP); 
    }

    function getDNSChallenge(){
        $order = self::generateOrder();
        return self::getChallenge($order,LEOrder::CHALLENGE_TYPE_DNS); 
    }
    

    function verifyChallenge($order,$type){
        $basename = get_option('wpssl_basedomain');
        return $order->verifyPendingOrderAuthorization($basename, $type);
    }
    


    function completeHTTPChallenge(){
        $order = self::generateOrder();
        $challenge = self::getHttpChallenge();
        $challenge_dir = get_home_path() . '/.well-known/acme-challenge';
        try{
            if (! is_dir($challenge_dir)) {
                mkdir( $challenge_dir, 0777,true );
            }
            foreach($challenge as $data){
                file_put_contents($challenge_dir."/".$data['filename'],$data['content']);
            }
            return true;
        }
        catch(Exception $e){
            return false;
        }
        
    }

    function completeDNSChallenge(){
        $challenge = self::getDNSChallenge();
        $cpanel = new WPSSL_CPANEL;
        foreach($challenge as $c){
            $dns_data = ['domain'=>$this->baseDomain,'name'=>"_acme-challenge",'type'=>'TXT','txtdata'=>$c['DNSDigest']];
            //var_dump($cpanel->addDNSRecord($dns_data));
        }
    }



    function generateSSL(){
        $order = self::generateOrder();
        $order->finalizeOrder();
        if($order->isFinalized()){
            return $order->getCertificate();
        }
        return false;
    }

    function validateVerification($type){
        $statuses = array();
        $domains = self::getValidDomains();
        if(get_option('wpssl_iswildcard',"0")=="1"){
            $basename = get_option('wpssl_basedomain');
            $domains = [$basename];
        }
        $order = self::generateOrder();
        foreach($domains as $domain){
            $status = $order->verifyPendingOrderAuthorization($domain, $type);
            array_push($statuses,$status);
        }
        //var_dump($statuses);
        if($order->allAuthorizationsValid()){
            return true;
        }
        else return false;
    }

    function checkDNS(){
        $challenges =  self::getDNSChallenge();
        //var_dump($challenges);
        if(!$challenges) return false;
        foreach($challenges as $challenge){
            $status = LEFunctions::checkDNSChallenge($challenge['identifier'], $challenge['DNSDigest']);  
            if(!$status) return false;
        }
        return true;
    }

    function checkHTTP(){
        $challenges =  self::getHttpChallenge();
        if(!$challenges) return false;
        foreach($challenges as $challenge){
            $status = LEFunctions::checkHTTPChallenge($challenge->identifier, $challenge->filename,$challenge->content);  
            if(!$status) return false;
        }
        return true;
    }

    function checkWWW(){
        $domain = get_option('wpssl_basedomain');
        $result = dns_get_record("www.".$domain,DNS_CNAME);
        if(count($result)>0) return true;
        else return false;
    }

    	
	function canExecuteShell() {
        return function_exists( 'shell_exec' );
    }

    function checkCPanelCommandLineApi() {
        return self::canExecuteShell() && ! empty( shell_exec( 'which uapi' ) );
    }

    function installSslWithCommandline( $domain, $keysPath ) {
        $cert     = urlencode( str_replace( '\r\n', '\n', file_get_contents( $keysPath . 'keys/certificate.crt' ) ) );
        $key      = urlencode( str_replace( '\r\n', '\n', file_get_contents( $keysPath . 'keys/private.pem' ) ) );
        $caBundle = urlencode( str_replace( '\r\n', '\n', file_get_contents( $keysPath . 'cabundle/ca.crt' ) ) );
        return shell_exec( "uapi SSL install_ssl domain=$domain cert=$cert key=$key cabundle=$caBundle" );
    }
    //Not compatilble yet
    function installSslWithAPI( $domain, $keysPath ) {
        $cert     = urlencode( str_replace( "\n", '\r\n'."\n", file_get_contents( $keysPath . 'keys/certificate.crt' ) ) );
        $key      = urlencode( str_replace( "\n", '\r\n'."\n", file_get_contents( $keysPath . 'keys/private.pem' ) ) );
        $caBundle = urlencode( str_replace( "\n", '\r\n'."\n", file_get_contents( $keysPath . 'cabundle/ca.crt' ) ) );
        $cpanel = new WPSSL_CPANEL;
        $cpanel->installSSL($domain,$cert,$key,$caBundle);
    }


    
    

    function AutoInstall(){

        $basename = get_option('wpssl_basedomain','');
        $order = $this->generateOrder();
        if($order->status=="valid"){
            echo ('cert valid');
            if(self::generateSSL()){
                echo ('cert generated');
                $domain = get_option('wpssl_basedomain','');
                if($domain=='') return false;
                if(self::checkCPanelCommandLineApi()){
                    //$wpssl->installSslWithAPI($domain,WPSSL_DIR);
                    echo ('installed');
                    return $wpssl->installSslWithCommandline($domain,WPSSL_DIR);
                }
                return false;
            }
        }
        if(WPSSL_HELPER::isSubdomain($basename)){
            update_option('wpssl_iswildcard',"0");
        }
        else{
            update_option('wpssl_iswildcard',"1");
        }
        
        $this->completeDNSChallenge();

        sleep(5);
        /*if(!self::checkDNS()){
            echo ('dns not verified');
            
            return false; 
        }*/
        
        
        if(self::validateVerification('dns-01')){
            echo ('dns verified');
            if(self::generateSSL()){
                echo ('cert generated');
                $domain = get_option('wpssl_basedomain','');
                if(self::checkCPanelCommandLineApi()){
                    //$wpssl->installSslWithAPI($domain,WPSSL_DIR);
                    echo ('installed');
                    return $wpssl->installSslWithCommandline($domain,WPSSL_DIR);
                }
                return false;
            }
        }else{
            echo ('dns unverifeid');
        }
        return false;

    }

    function debugLetsEncrypt(  $method = LEOrder::CHALLENGE_TYPE_HTTP ) {
        $baseDomain = get_option('wpssl_basedomain');
        $apiResponse = wp_remote_post( 'https://letsdebug.net', [
            'timeout'   => '15',
            'sslverify' => false,
            'headers'   => array(
                'content-type' => 'application/json'
            ),
            'body'      => json_encode( [
                'method' => $method,
                'domain' => $baseDomain
            ] )
        ] );

        if ( ! empty( $apiResponse ) && ! is_wp_error( $apiResponse ) ) {
            $bodyObj = ! empty( $apiResponse['body'] ) ? json_decode( $apiResponse['body'] ) : null;
            $id      = ! empty( $bodyObj ) && ! empty( $bodyObj->ID ) ? (int) $bodyObj->ID : null;
            // Fetch the id and
            if ( ! empty( $id ) ) {

                // Sleep in order to pass the processing status
                sleep(10);

                // Prepare get call
                $apiResponse = wp_remote_get( 'https://letsdebug.net/' . $baseDomain . '/' . $id, [
                    'timeout'   => '15',
                    'sslverify' => false,
                    'headers'   => array(
                        'Accept' => 'application/json'
                    )
                ] );

                if ( ! empty( $apiResponse ) && ! is_wp_error( $apiResponse ) ) {
                    $bodyObj = ! empty( $apiResponse['body'] ) ? json_decode( $apiResponse['body'] ) : null;
                    if ( ! empty( $bodyObj->result ) && ! empty( $bodyObj->result->problems ) ) {
                        $problems = $bodyObj->result->problems;
                        $error    = false;
                        foreach ( $problems as $problem ) {
                            if ( $problem->severity != 'debug' ) {
                                // So the domain has problem, then redirect with error
                                $error = true;
                                break;
                            }
                        }

                        if ( $error ) {
                            return $problem;
                            //wp_redirect( admin_url( 'admin.php?page=ssl_zen&tab=' . $currentSettingTab . '&info=lets_encrypt_error_' . strtolower( $problem->name ) ) );
                            exit;
                        }
                        
                    }

                }
            }
        }
        return null;
    }

    

}


?>