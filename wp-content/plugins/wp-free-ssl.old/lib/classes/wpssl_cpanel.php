<?php

require_once WPSSL_DIR . 'lib/vendor/autoload.php';
use  Defuse\Crypto\Key ;
use  Defuse\Crypto\Crypto ;
class WPSSL_CPANEL
{
    private  $cpanel ;
    private  $keyPath ;
    function __construct( $cred = array() )
    {
    }
    
    function genKey()
    {
    }
    
    function retriveCpanelCred()
    {
    }
    
    function safeStoreCpanelCred( $host, $user, $pwd )
    {
    }
    
    function loadEncryptionKeyFromConfig()
    {
    }
    
    function defuseDirectory()
    {
    }
    
    function addDNSRecord( $dns_data )
    {
    }
    
    function installSSL(
        $domain,
        $cert,
        $key,
        $cabundle
    )
    {
        $cert_data = [
            'domain'   => $domain,
            'cert'     => $cert,
            'key'      => $key,
            'cabundle' => $cabundle,
        ];
        $data = $this->cpanel->execute_action(
            '3',
            'SSL',
            'install_ssl',
            $this->cpanel->getUsername(),
            $cert_data
        );
    }
    
    function verifyDNS( $domain )
    {
        return dns_get_record( $domain );
    }

}