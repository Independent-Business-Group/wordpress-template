<?php

require_once WPSSL_DIR . 'lib/classes/wpssl_ssl.php';

class WPSSL_SCHEDULE{
    
    function scheduleAutoinstall(){
         
    }

    function __construct(){
        //add_filter( 'cron_schedules', [ $this,'check_every_day'] );
        //add_action( 'check_every_day', [ $this,'check_ssl_cert'] );
        //update_option('wpssl_scheduled_autorenew',"0");
        //self::set_schedule();
        
    }

    public static function set_schedule(){
        
        if ( ! wp_next_scheduled( 'check_every_day' ) ) {
            wp_schedule_event( time(), 'every_day', 'check_every_day' );
            update_option('wpssl_scheduled_autorenew',"1");
        }
    }

    public static function checkAutoInstall(){
        $data = get_option('wpssl_autoinstall',['cert_date'=>date("Y/m/d"),'last_checked'=>date("Y/m/d"),'renew_ran'=>false]);
        $cert_date = strtotime($data['cert_date']);
        $today_date = date("Y/m/d");
        $today_date_stamp = strtotime($today_date);
        if($data['last_checked'] == $today_date){
            update_option('wpssl_no_renew_yet',"1");
            return;
        }
        else{
            update_option('wpssl_no_renew_yet',"0");
        }

        $diff = abs($cert_date - $today_date_stamp)/(24*60*60);
        if($diff>30){
            $this->check_ssl_cert();
        }

    }

    function check_every_day( $schedules ) {
        $schedules['every_day'] = array(
                'interval'  => 60*60*24,
                'display'   => __( 'Every 24 Hrs', 'textdomain' )
        );
        return $schedules;
    }


    function check_ssl_cert() {
        update_option("wpssl_renew_ran",get_option('wpssl_renew_ran',1)+1);
        $wpssl = new WPSSL_SSL;
        $certinfo = $wpssl->certificateInfofromDomain();
        if(!$certinfo){
            update_option('wpssl_cert_expired',"1");
        }
        if( $certinfo['validFrom_time_t'] > time() || $certinfo['validTo_time_t'] < time() ){
            update_option('wpssl_cert_expired',"1");
        }
        else{
            update_option('wpssl_cert_expired',"0");
        }
        if((abs($certinfo['validTo_time_t'] - time())/60/60/24)<30){
            if($wpssl->AutoInstall()){
                update_option('wpssl_cert_renewed',"1");
            }else{
                update_option('wpssl_cert_renewed',"0");
            }
        }
        else{
            
        }
        
    }

}



?>