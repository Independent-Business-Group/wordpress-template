<?php

/*
Plugin Name: WP FREE SSL - Free SSL Certificate for WordPress and force HTTPS
Plugin URI: http://wordpress.org/plugins/wp-free-ssl/
Description: Get FREE SSL for wordpress
Author: Prasad Kirpekar
Version: 1.2.7
Author URI: http://paypal.me/prasadkirpekar
License: GPL v2
Copyright: Prasad Kirpekar
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define( "WPSSL_DIR", plugin_dir_path( __FILE__ ) );
define( "WPSSL_WPHOME", ABSPATH );
define( "WPSSL_VER", '1.2.5' );
register_activation_hook( __FILE__, 'wpfreessl_init_options' );
include_once 'lib/classes/wpssl_https.php';
include_once 'lib/classes/wpssl_ssl.php';
include_once 'lib/classes/wpssl_init.php';
include_once 'lib/classes/wpssl_schedule.php';
//WPSSL_SCHEDULE::checkAutoInstall();
WPSSL_INIT::instance();
define( 'WPFSSL_DIR', plugin_dir_path( __FILE__ ) );

if ( !function_exists( 'wfs_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wfs_fs()
    {
        global  $wfs_fs ;
        
        if ( !isset( $wfs_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wfs_fs = fs_dynamic_init( array(
                'id'             => '6781',
                'slug'           => 'wp-free-ssl',
                'type'           => 'plugin',
                'public_key'     => 'pk_6e40ba8ddbab7ded2dc5031aa2231',
                'is_premium'     => false,
                'premium_suffix' => 'WP SSL Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'    => 'wp-free-ssl.php',
                'support' => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wfs_fs;
    }
    
    // Init Freemius.
    wfs_fs();
    // Signal that SDK was initiated.
    do_action( 'wfs_fs_loaded' );
}

function wpfreessl_init_options()
{
    $domain = get_site_url();
    $domain = parse_url( $domain );
    $domain = str_replace( 'www.', '', $domain['host'] );
    update_option( 'wpssl_basedomain', $domain );
    update_option( 'wpssl_basedomainwww', 'www.' . $domain );
}

function wpfreessl_admin_settings()
{
    $page = add_menu_page(
        'WP Free SSL',
        'WP Free SSL',
        'manage_options',
        basename( __FILE__ ),
        'wpfreessl_setting_page',
        plugins_url( 'wp-free-ssl/admin/assets/img/icon.png' )
    );
    add_action( 'admin_print_scripts-' . $page, 'wpfreessl_enqueue_scripts' );
    $cert = add_submenu_page(
        basename( __FILE__ ),
        'My Certificate',
        'My Certificate',
        'manage_options',
        'certificate',
        'wpfreessl_certicate_page'
    );
    $https = add_submenu_page(
        basename( __FILE__ ),
        'Force HTTPS',
        'Force HTTPS',
        'manage_options',
        'enablehttps',
        'wpfreessl_enablehttps_page'
    );
    add_action( 'admin_print_scripts-' . $cert, 'wpfreessl_enqueue_scripts' );
    add_action( 'admin_print_scripts-' . $https, 'wpfreessl_enqueue_scripts' );
}

function wpfreessl_setting_page()
{
    include_once 'lib/classes/wpssl_help.php';
    $issubdomain = WPSSL_HELPER::isSubdomain( get_option( 'wpssl_basedomain' ) );
    if ( current_user_can( 'manage_options' ) ) {
        if ( isset( $_REQUEST['planselected'] ) ) {
            update_option( 'wpssl_planselected', "1" );
        }
    }
    include_once "admin/wpssl-admin.php";
}

function wpfreessl_certicate_page()
{
    $action_url = $_SERVER['REQUEST_URI'];
    include_once 'lib/classes/wpssl_help.php';
    $domain = get_option( 'wpssl_basedomain', '' );
    if ( get_option( 'wppssl_ssl_activated', "0" ) == "0" ) {
        $havessl = WPSSL_HELPER::verifySSL( $domain );
    }
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }
    
    if ( isset( $_REQUEST['certdownload'] ) ) {
        header( 'Content-Description: File Transfer' );
        $cert_number = $_REQUEST['certnumber'];
        switch ( $cert_number ) {
            case '1':
                $file = uniqid() . '-cert.txt';
                file_put_contents( $file, file_get_contents( WPSSL_DIR . 'keys/certificate.crt' ) );
                break;
            case '2':
                $file = uniqid() . '-key.txt';
                file_put_contents( $file, file_get_contents( WPSSL_DIR . 'keys/private.pem' ) );
                break;
            case '3':
                $file = uniqid() . '-cabundle.txt';
                file_put_contents( $file, file_get_contents( WPFSSL_DIR . 'cabundle/ca.crt' ) );
                break;
        }
        header( 'Content-Type: text/plain' );
        header( 'Content-Length: ' . filesize( $file ) );
        header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
        ob_clean();
        readfile( $file );
        die;
    } else {
        
        if ( isset( $_REQUEST['certdelete'] ) ) {
            unlink( WPFSSL_DIR . "keys/certificate.crt" );
            unlink( WPFSSL_DIR . "keys/private.pem" );
        }
    
    }
    
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $canInstallSSL = false;
    if ( $wpssl->checkCPanelCommandLineApi() ) {
        $canInstallSSL = true;
    }
    $isCPanelActive = get_option( 'wpssl_cpanel_connected', false );
    $certExpiry = date( 'd M Y', $wpssl->certificateInfofromFile()['validTo_time_t'] );
    include_once "admin/pages/certificate.php";
}

function wpfreessl_autoinstall_page()
{
}

function wpfreessl_enablehttps_page()
{
    include_once 'lib/classes/wpssl_help.php';
    $domain = get_option( 'wpssl_basedomain', '' );
    $havessl = WPSSL_HELPER::verifySSL( $domain );
    $action_url = $_SERVER['REQUEST_URI'];
    if ( !current_user_can( 'manage_options' ) ) {
        return;
    }
    
    if ( isset( $_REQUEST['enablehttps'] ) ) {
        update_option( 'wppssl_ssl_activated', "1" );
    } else {
        if ( isset( $_REQUEST['disablehttps'] ) ) {
            update_option( 'wppssl_ssl_activated', "0" );
        }
    }
    
    include_once "admin/pages/enablehttps.php";
}

function wpfreessl_enqueue_scripts()
{
    wp_enqueue_style( 'wpfreessl_bootstrap', plugin_dir_url( __FILE__ ) . '/admin/assets/css/tailwind.min.css' );
    wp_enqueue_style( 'wpfreessl_appcss', plugin_dir_url( __FILE__ ) . '/admin/assets/css/app.css' );
    wp_register_script( "wpssl_plugin_js", plugin_dir_url( __FILE__ ) . '/admin/assets/js/app.js?v=' . rand(), array( 'jquery' ) );
    wp_localize_script( 'jquery', 'ajax_url', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'wpssl_plugin_js' );
    wp_enqueue_script( 'wpssl_ajax_url' );
}

add_action( 'admin_menu', 'wpfreessl_admin_settings' );
add_action( 'wp_ajax_wpssl_get_challenge', 'wpssl_challenge' );
add_action( 'wp_ajax_wpssl_get_order', 'wpssl_ssl_order' );
add_action( 'wp_ajax_wpssl_get_certificate', 'wpssl_get_certificate' );
add_action( 'wp_ajax_wpssl_verify_challenge', 'wpssl_verify_challenge' );
add_action( 'wp_ajax_wpssl_complete_challenge', 'wpssl_complete_challenge' );
add_action( 'wp_ajax_wpssl_install_ssl', 'wpssl_install_ssl' );
add_action( 'wp_ajax_wpssl_debug_letsencrypt', 'wpssl_debug_letsencrypt' );
add_action( 'wp_ajax_wpssl_complete_dns', 'wpssl_complete_dns' );
add_action( 'wp_ajax_wpssl_fetch_cert', 'wpssl_fetch_cert' );
function wpssl_fetch_cert()
{
    $cert_number = $_REQUEST['certnumber'];
    $cert_data = "";
    switch ( $cert_number ) {
        case '1':
            $cert_data = file_get_contents( WPSSL_DIR . 'keys/certificate.crt' );
            break;
        case '2':
            $cert_data = file_get_contents( WPSSL_DIR . 'keys/private.pem' );
            break;
        case '3':
            $cert_data = file_get_contents( WPFSSL_DIR . 'cabundle/ca.crt' );
            break;
    }
    echo  $cert_data ;
    wp_die();
}

function wpssl_autoinstall_testrun()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $wpssl->AutoInstall();
    wp_die();
}

function wpssl_complete_dns()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $wpssl->completeDNSChallenge();
}

function wpssl_cpanel_check()
{
}

function wpssl_set_wildcard()
{
}

function wpssl_enable_https()
{
    $site_url = get_site_url();
    if ( strpos( $site_url, 'https' ) !== false ) {
        return;
    }
    $home_url = get_home_url();
    if ( strpos( $home_url, 'https' ) !== false ) {
        return;
    }
    $site_url = str_replace( 'http', 'https', $site_url );
    $home_url = str_replace( 'http', 'https', $home_url );
    update_option( 'siteurl', $site_url );
    update_option( 'home', $home_url );
}

function wpssl_debug_letsencrypt()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $http = $wpssl->debugLetsEncrypt();
    $dns = $wpssl->debugLetsEncrypt( 'dns-01' );
    $data = [
        'data'   => [
        'http' => $http,
        'dns'  => $dns,
    ],
        'status' => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_install_ssl()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $domain = get_option( 'wpssl_basedomain' );
    $installed = false;
    $viaapi = false;
    
    if ( isset( $_POST['viaapi'] ) ) {
        $viaapi = filter_var( $_POST['viaapi'], FILTER_VALIDATE_BOOLEAN );
        //wp_die($viaapi);
    }
    
    
    if ( $viaapi === "false" ) {
        wp_die( $viaapi );
        $wpssl->installSslWithAPI( $domain, WPSSL_DIR );
    } else {
        
        if ( $wpssl->checkCPanelCommandLineApi() ) {
            $wpssl->installSslWithCommandline( $domain, WPSSL_DIR );
            //wpssl_enable_https();
        }
    
    }
    
    //sleep(10);
    //$installed = $wpssl->verifySSL($domain);
    $data = [
        'challenge' => $installed,
        'status'    => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_complete_challenge()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    
    if ( isset( $_POST['type'] ) ) {
        $type = $_POST['type'];
    } else {
        $data = [
            'challenge' => false,
            'status'    => false,
        ];
        echo  json_encode( $data ) ;
        return;
    }
    
    $challenge = $wpssl->completeHTTPChallenge();
    $data = [
        'challenge' => $challenge,
        'method'    => $type,
        'status'    => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_ssl_order()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    
    if ( !isset( $_REQUEST['ssl_email'] ) ) {
        wpfreessl_response( "Please specify email", 'incompletedata', 304 );
        return;
    }
    
    $email = $_REQUEST['ssl_email'];
    update_option( 'wpssl_email', $email );
    $ssl_domain = $_REQUEST['ssl_domain'];
    update_option( 'wpssl_basedomain', $ssl_domain );
    $client = $wpssl->generateOrder();
    $data = [
        'data'   => $client,
        'status' => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_verify_challenge()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $type = "http-01";
    
    if ( isset( $_POST['type'] ) ) {
        $type = $_POST['type'];
    } else {
        $data = [
            'challenge' => false,
            'method'    => $type,
            'status'    => false,
        ];
        echo  json_encode( $data ) ;
        return;
    }
    
    $challenge = $wpssl->validateVerification( $type );
    $data = [
        'challenge' => $challenge,
        'method'    => $type,
        'status'    => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_get_certificate()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    $cert = $wpssl->generateSSL();
    $data = [
        'certificate' => $cert,
        'method'      => $type,
        'status'      => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpssl_challenge()
{
    include_once 'lib/classes/wpssl_ssl.php';
    include_once 'lib/classes/wpssl_init.php';
    $wpssl_init = new WPSSL_INIT();
    $wpssl = new WPSSL_SSL();
    
    if ( isset( $_POST['type'] ) ) {
        $type = $_POST['type'];
    } else {
        echo  "No type specified" ;
        return;
    }
    
    $challenge = [];
    
    if ( $type == 'http-01' ) {
        $challenge = $wpssl->getHttpChallenge();
    } else {
        $challenge = $wpssl->getDNSChallenge();
    }
    
    $data = [
        'challenge' => $challenge,
        'method'    => $type,
        'status'    => true,
    ];
    echo  json_encode( $data ) ;
    wp_die();
}

function wpfreessl_response( $msg, $action = "none", $code )
{
    status_header( $code );
    echo  json_encode( [
        'msg'    => $msg,
        'action' => $action,
    ] ) ;
    wp_die();
}
