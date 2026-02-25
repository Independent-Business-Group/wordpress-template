var mProgress = 0;
var progressStart = true;

var httprecieved = false;
var dnsrecieved = false;
var isverified = false;
var iswildcard = false;




function checkpanel(){
  jQuery('#checkpanelbtn').html("Checking...")
  var host = jQuery('#cpanelhost').val();
  var username = jQuery('#cpaneluser').val();
  var pwd = jQuery('#cpanelpwd').val();
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_cpanel_check",'host':host,'username':username,'password':pwd},
    success: function(response) {
      var obj = JSON.parse(response);
      if(obj.status){
        alert("Your CPanel is working as expected. You can now update credentials using Update Credentials button")
        jQuery('#cpanelsavebtn').attr('disabled',false);
      }
      else{
        jQuery('#cpanelsavebtn').attr('disabled',true);
        alert("Failed to connect to CPanel. Check your host include full url like https://yourhost.com:2083 or 2082. Also make sure username, password is correct and this site domain is on that same cpanel account")
      }
      jQuery('#checkpanelbtn').html("Test Cpanel Again")
    },
    error:function(err){
      console.log(err)
      alert("Not able to connect to server")
    }
  });
}


function fetch_cert(number){
  jQuery('#checkpanelbtn').html("Checking...")
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_fetch_cert",'certnumber':number},
    success: function(response) {
      jQuery('#certcontent').val(response)
      jQuery('#certcontent').show()
    },
    error:function(err){
      console.log(err)
      alert("Not able to connect to server")
    }
  });
}

function showchallenge(data){

  data_str = "<table class='table-auto m-auto'>";
  data_str+="<thead><tr><th class='px-4 py-2'>Identifier</th><th class='px-4 py-2'>Record</th><th class='px-4 py-2'>Type</th></tr></thead><tbody>"
  for(var i =0;i<data.length;i++){
    var id = "_acme-challenge."+data[i].identifier;
    var dnsdigest = data[i].DNSDigest;
    data_str += "<tr><td class='border px-4 py-2'>"+id+"</td><td class='border px-4 py-2'>"+dnsdigest+"</td><td class='border px-4 py-2'>TXT</td></tr>"
  }
  data_str += "</tbody><table>"
  console.log(data_str)
  jQuery('#dnstables').html(data_str);
}

function showhttpchallenge(data){
  data_str="";
  for(var i =0;i<data.length;i++){
    var filename = data[i].filename;
    var content = data[i].content;
    data_str += "<button class='px-3 mr-2 mt-2 inline-flex  mt-4 rounded-md shadow-sm py-2 bg-yellow-700 text-gray-200' onclick=createanddownload('"+filename+"','"+content+"')>Download File "+i*1+1+"</button>"
  }
  console.log(data_str)
  jQuery('#httptable').html(data_str);
}

function showcertificate(data){
  jQuery('#certificate').html(data);
  alert("Your certificate is ready");
  location.reload(true);
}

function debug_letsencrypt(){

  
  jQuery('#inprogessdebug').show();
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_debug_letsencrypt"},
    success: function(response) {

      var obj = JSON.parse(response);
      
      if(obj.status){
        
        if(obj.data.http!=null){
          
          jQuery('#httpproblem').html(obj.data.http.detail);
          jQuery('#httpproblemexplaination').html(obj.data.http.explanation);
        }
        if(obj.data.dns!=null){

          jQuery('#dnspproblem').html(obj.data.dns.detail);
          jQuery('#dnsproblemexplaination').html(obj.data.dns.explanation);
        }
        
      }
      jQuery('#debuginfo').show();
      jQuery('#inprogessdebug').hide();
    },
    error:function(err){
      console.log(err)
      alert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
      
    }
  });
}

function get_order(){

  var ssl_email = jQuery('#sslemail').val()
  var ssl_domain = jQuery('#ssl_domain').val()
  jQuery('#inprogess').show();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_get_order",'ssl_email':ssl_email,'ssl_domain':ssl_domain},
    success: function(response) {

      var obj = JSON.parse(response);
      
      if(obj.status){
        if(obj.data.status=='ready'){
            isverified=true;
            alert("Your certificate is ready to download")
            switchstep('#step3')
        }
        
        else if(obj.data.status=='valid'){
          isverified=true;
          alert("Your certificate is ready to download")
          switchstep('#step3')
        }
        else{
          switchstep('#step2')
        }

      }
      jQuery('#inprogess').hide();
    },
    error:function(err){
      
      jalert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
      
    }
  });
}


function get_certificate(){
  jQuery('#fetchcertificate').show();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_get_certificate"},
    success: function(response) {

      var obj = JSON.parse(response);
      
      if(obj.status){
        if(obj.certificate){
            alert("Your certificate is saved")
            install_certificate(false);
            alert("We will try to install certificate on your server now. Click okay to proceed")
            jQuery('#certdownloadbox').show();
            jQuery('#fetchcertificate').hide();
        }
      }
      
    },
    error:function(err){
      
      alert("Something went wrong. Please disable any other SSL plugin before generating SSL")
      
    }
  });
}

function install_certificate(viaapi){
  viaapi=false
  jQuery('#installsslprogess').show();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_install_ssl",'viaapi':viaapi},
    success: function(response) {

      var obj = JSON.parse(response);
      
      if(obj.status){
        if(obj.challenge){
          jQuery('#installsslprogess').hide();
            alert("Your SSL certificate is installed successfully")
            location.reload()
        }
        else{
          jQuery('#installsslprogess').hide();
          alert("We have attempted to install SSL automatically. Please wait for 5 to 10 min and then enable Force SSL and open site in new tab to verify https. If site is not https please install manually")
          location.reload()
        }
      }
      
    },
    error:function(err){
      
      alert("Something went wrong. Please disable any other SSL plugin before generating SSL")
      
    }
  });
}



function test_run(){
  
  jQuery('#installsslprogess').show();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_autoinstall_testrun"},
    success: function(response) {

      var obj = JSON.parse(response);
      
      
      
    },
    error:function(err){
      
      alert("Something went wrong. Please disable any other SSL plugin before generating SSL")
      
    }
  });
}



function get_challenge(type){

  jQuery('.httpverifyinfo').show();
  jQuery('.httpverificationprogress').hide();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_get_challenge",'type':type},
    success: function(response) {

      var obj = JSON.parse(response);
      
      if(obj.status){
        
        if(obj.method=="dns-01"){
          dnsrecieved = true;
          showchallenge(obj.challenge)
        }
        else{
          httprecieved=true;
          showhttpchallenge(obj.challenge)
          
          complete_challenge('http-01')
        }
      }
      //alert("Check console")
      console.log(obj)
      
    },
    error:function(err){
      
      alert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
      
    }
  });
}

function set_wildcard(state){
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_set_wildcard",'iswildcard':state},
    success: function(response) {
      console.log(response)
      if(state){
        alert("You have selected wildcard certficate it will cover all the subdomain of your domain. It can be verified with DNS records only")
      }
      
    },
    error:function(err){
      console.log(err)
      jQuery('#wildcardcheckbox').prop('checked',false)
      alert("Something went wrong. Unable to set wildcard")
    }
  });
}

function complete_challenge(type){
  jQuery('.httpverifyinfo').hide();
  jQuery('.httpverificationprogress').show();
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_complete_challenge",'type':type},
    success: function(response) {

      var obj = JSON.parse(response);
      if(obj.status){
        if(obj.challenge){
          verify_challenge('http-01')
        }
        else{
          isverified=false;
          alert("Automated verification was not successful follow the instuctions below to continue")
          jQuery('#httpmethodmanual').show();
          jQuery('#completehttp').hide();
        }
      }
      
    },
    error:function(err){
      
      alert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
      
    }
  });
}

function complete_challenge_dns(type){
  
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_complete_dns",'type':type},
    success: function(response) {

      var obj = JSON.parse(response);

      
    },
    error:function(err){
      
      alert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
      
    }
  });
}

function verify_challenge(type){
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_verify_challenge",'type':type},
    success: function(response) {

      var obj = JSON.parse(response);
      if(obj.status){
        if(obj.challenge){
          isverified=true;
          alert("Verification successful. Going to next step")
          switchstep("#step3")
        }
        else{
          isverified=false;
          jQuery('#httpmethodmanual').show();
          jQuery('#completehttp').hide();
          
          alert("Not able to verify the domain. Use debug button in current window to find out if any issue is there. Wait for few minutes and retry")
          jQuery("#debugbtn").show()
        }
      }
      if(type=='http-01'){
        jQuery("#verifyhttpprogess").hide()
      }
      else{
        jQuery("#verifydnsprogess").hide()
      }
    },
    error:function(err){
      if(type=='http-01'){
        jQuery("#verifyhttpprogess").hide()
      }
      else{
        jQuery("#verifydnsprogess").hide()
      }
      alert("Something went wrong. Please deactivate any other SSL plugin before generating SSL")
    }
  });
}

function startsslfetch(){
  
  var ssl_email = jQuery('#sslemail').val()
  var ssl_verification_type = jQuery('input[name="verificationtype"]:checked').val();
  
  


  jQuery('#redicon').addClass('animate-pulse')
  jQuery('#inprogess').show();
  jQuery.ajax({
    type : "post",
    url : ajax_url.ajaxurl,
    data : {action: "wpssl_get_challenge",'ssl_email':ssl_email,'verificationtype':ssl_verification_type},
    success: function(response) {

      var obj = JSON.parse(response);
      
      console.log(obj)
      if("dnschallenge"==obj.action){
        alert("DNS challenge recieved please follow the instructions")
        showchallenge(obj.msg)
        jQuery('#inprogess').hide();
        jQuery('#redicon').removeClass('animate-pulse')
        return
      }
      jQuery('#msg').html(response)
      jQuery('#msg').addClass('text-gray-800')
      jQuery('#inprogess').hide();
      jQuery('#redicon').removeClass('animate-pulse')
       // location.reload();
    },
    error:function(err){
      
      jQuery('#msg').html(err.responseText)

      jQuery('#msg').addClass('text-red-800')
      jQuery('#redicon').removeClass('animate-pulse')
      jQuery('#inprogess').hide();
      alert("Something went wrong! Please mail us via support option")
      
    }
 });
}


jQuery(document).ready(function(){

  //get_order()

  jQuery("#debugbtn").hide()
  jQuery("#debugbtn").click(function(){
    //jQuery("#sslstart").prop('disabled', true);
    //startsslfetch();
    debug_letsencrypt()
    
  })

    jQuery("#sslstart").click(function(){
      //jQuery("#sslstart").prop('disabled', true);
      //startsslfetch();
      get_order();
      
    })

    jQuery("#verifydns").click(function(){
      jQuery("#verifydnsprogess").show()
      verify_challenge('dns-01');
    })

    jQuery("#verifyhttp").click(function(){
      
      jQuery("#verifyhttpprogess").show()
      verify_challenge('http-01');
    })

    jQuery("#wildcardcheckbox").change(function(){
      iswildcard = this.checked
      set_wildcard(iswildcard)
    });
    
    

    jQuery("#starttestrun").click(function(){
      test_run();
    });

    jQuery("#checkdns").click(function(){

    });

    jQuery("#completedns").click(function(){
      complete_challenge_dns()
    });

    jQuery("#checkpanelbtn").click(function(){
      checkpanel()
    });

});


function createanddownload(filename, text) {
  var blob = new Blob([text]);
    if(window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveBlob(blob, filename);
    }
    else{
        var elem = window.document.createElement('a');
        elem.href = window.URL.createObjectURL(blob);
        elem.download = filename;        
        document.body.appendChild(elem);
        elem.click();        
        document.body.removeChild(elem);
    }
}

var tab_list = ['#httpmethod','#dnsmethod']
var step_list = ['#step1','#step2','#step3']

function switchtab(tab){
    for (let i = 0; i < tab_list.length; i++) {
        const element = tab_list[i];
        if(tab==element){
            jQuery(tab).show()
            jQuery(tab+'btn').addClass('border-b-2')
            jQuery(tab+'btn').addClass('border-blue-500')  
            jQuery(tab+'btn').addClass('text-blue-500')  
            jQuery(tab+'btn').addClass('font-medium')  
            
        }
        else{
            jQuery(element).hide()
            jQuery(element+'btn').removeClass('border-b-2')
            jQuery(element+'btn').removeClass('border-blue-500') 
            jQuery(element+'btn').removeClass('text-blue-500')  
            jQuery(element+'btn').removeClass('font-medium')   
        }
    }
}

function switchstep(step){
    if(step=="#step3"&&!isverified){
      alert("At least one verification has to be successful")
      return;
    }
    if(step=='#step2'&&isverified) {
      return
    }
    for (let i = 0; i < step_list.length; i++) {
        const element = step_list[i];
        if(step==element){
            if(element=='#step2'&&!isverified){

              get_challenge('dns-01')
              if(!iswildcard){
                get_challenge('http-01')
                jQuery('#httpmethod').show()
                jQuery('#httpmethodbtn').show()
              }
              else{
                jQuery('#httpmethod').hide()
                jQuery('#httpmethodbtn').hide()
                switchtab('#dnsmethod')
              }
              
              
            }
            if(element=='#step3'){
              get_certificate()
            }
            
            
            jQuery(element).show()
        }
        else{
            jQuery(element).hide()
        }
    }
}
