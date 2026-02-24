<?php



class WPSSL_INIT{

    protected static  $instance = null ;


    function __construct(){
        self::include_dependancy();
        self::register_endpoints();
        
        //add_action( 'admin_notices',  [$this,'wp_ssl_notices'] );
    
    }


    function include_dependancy(){
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEClient.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEOrder.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEAccount.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEConnector.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEFunctions.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/LEAuthorization.php';

        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEException.php';
        
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEConnectorException.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEOrderException.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEFunctionsException.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEClientException.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEAuthorizationException.php';
        include_once WPSSL_DIR . 'lib/classes/LEClient/Exceptions/LEAccountException.php';

        foreach (glob(WPSSL_DIR . 'lib/classes/Log/*.php') as $filename)
        {
            include_once $filename;
        }

    }

    
    
    function wp_ssl_notices(){
            return;
            if(get_option('wpssl_cert_expired',"0")=="1"){
                echo '<div class="notice notice-error is-dismissible"><p>Your certificate is expired please renew it</p></div>';
            }
            else{
                echo '<div class="notice notice-success  is-dismissible"><p>Your certificate valid</p></div>';
            }
        
            if(get_option('wpssl_cert_renewed',"0")=="1"){
                echo '<div class="notice notice-success is-dismissible"><p>Great news! Your certificate is renewed in background successfully</p></div>';
            }
            else{
                echo '<div class="notice notice-error is-dismissible"><p>Failed to renew SSL! Your certificate is not renewed in background successfully</p></div>';
            }
        
        
            if(get_option('wpssl_no_renew_yet',"0")=="1"){
                echo '<div class="notice notice-success is-dismissible"><p>Not renewing yet</p></div>';
            }
            else{
                echo '<div class="notice notice-error is-dismissible"><p>Will be renewing</p></div>';
            }
        
            if(get_option('wpssl_scheduled_autorenew',"0")=="1"){
                echo '<div class="notice notice-success is-dismissible"><p>SSL Scheduled</p></div>';
            }
            else{
                echo '<div class="notice notice-error is-dismissible"><p>Not Scheduled</p></div>';
            }
    
        
    }
    
    

    


    function register_endpoints(){
        
    }

    

    public static function instance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}



?>