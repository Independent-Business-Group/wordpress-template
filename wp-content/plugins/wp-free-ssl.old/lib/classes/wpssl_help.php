<?php 



class WPSSL_HELPER{


    public static function verifySSL( $domain ) {
        $res    = false;
        $stream = @stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
        $socket = @stream_socket_client( 'ssl://' . $domain . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream );

        if ( $socket ) {

            $cont = stream_context_get_params( $socket );

            $cert_ressource = $cont['options']['ssl']['peer_certificate'];
            $cert           = openssl_x509_parse( $cert_ressource );

            $namepart = explode( '=', $cert['name'] );

            if ( count( $namepart ) == 2 ) {
                $cert_domain  = trim( $namepart[1], '*. ' );
                $check_domain = substr( $domain, - strlen( $cert_domain ) );
                $res          = ( $cert_domain == $check_domain );
            }
        }

        return $res;
    }

    public static function isSubdomain($url) {
        
        return preg_match("/^([a-z]+\:\/{2})?([\w-]+\.[\w-]+\.\w+)$/",$url)==1?true:false;
    
    }
}

?>