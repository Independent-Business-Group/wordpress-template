<div  class="w-full py-8 px-5 bg-white shadow-sm">
    <div class="flex justify-between">
        <div class=" flex">
        <img class="h-10 hidden w-10 mr-2" src="<?php echo plugin_dir_url(__FILE__).'/assets/img/ssl.png'; ?>" />
        <h3 class=" m-auto text-2xl font-semibold text-gray-700" >WP FREE SSL</h3>
        </div>
        <div class=" text-lg">
            <?php if(wfs_fs()->is_not_paying()){ ?>
                <span class="bg-gray-700 text-gray-200 py-1 px-2 rounded-md">Free</span>
            <?php }else{ ?>
                <span class="bg-yellow-700 text-gray-200 py-1 px-2 rounded-md">Premium</span>
            <?php } ?>
            <span class=" text-gray-700">Version:</span> <span class=" text-gray-800"><?php echo WPSSL_VER; ?></span>
        </div>
    </div>
</div>

<div  class=" mt-5 flex w-full p-5  flex-row">

    <div class=" w-3/4 mr-2 shadow-sm p-4 rounded-sm bg-white">

        <div id="step1"  class=" flex flex-col">
            <div class="p-4 flex-col flex">
                <h3 class=" m-auto text-2xl font-semibold text-gray-700" >Generate SSL Certificate</h3>
            </div>
            <div class="mb-2 mt-4 w-1/2 ml-auto mr-auto">
                <input id="ssl_domain" disabled class="shadow text-center appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="ssldomain" id="ssldomain" type="text" required value="<?php echo get_option( 'wpssl_basedomain',''); ?>" placeholder="Site Domain">
            </div>
            <label class="  m-auto mt-4 block text-gray-500 font-bold">
                <input <?php echo ($issubdomain)?"disabled":''; ?> id="wildcardcheckbox" <?php if(wfs_fs()->is_not_paying()){ echo "disabled"; } ?> <?php echo (get_option('wpssl_iswildcard',"0")=="1")?'checked':''; ?> class="mr-2 leading-tight" type="checkbox">
                
                <span class="text-sm">
                    Wildcard Certificate <?php echo ($issubdomain)?" (Not supported on subdomain)":''; ?>
                </span>
                <?php if(wfs_fs()->is_not_paying()){ ?>
                    <a href="<?php echo wfs_fs()->get_upgrade_url(); ?>"> <span class="bg-yellow-700 text-gray-200 py-1 px-2 rounded-md">Premium</span></a>
                <?php } ?>
            </label>
            <div class="mb-2 mt-4 w-1/2 ml-auto mr-auto">
                <input class="shadow text-center appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="ssl_email" id="sslemail" type="email" required value="<?php echo get_option( 'admin_email',""); ?>" placeholder="Your email">
            </div>

            <label class=" m-auto mt-4 block text-gray-500 font-bold">
                <input checked="checked" class="mr-2 leading-tight" type="checkbox">
                <span class="text-sm">
                    Send log anonymously
                </span>
            </label>
            <label class=" m-auto mt-4 block text-gray-500 font-bold">
                <input checked="checked" class="mr-2 leading-tight" type="checkbox">
                <span class="text-sm">
                    I accept Let's Encrypt's <a target="_blank" href="https://letsencrypt.org/privacy/">Terms and policy </a>
                </span>
            </label>
            <label class=" hidden m-auto mt-4 block text-gray-500 font-bold">
                <input class="mr-2 leading-tight" type="checkbox">
                <span class="text-sm">
                    I accept WP Free SSL Terms
                </span>
            </label>

            <div class="hidden m-auto mt-4">
                <div class="mt-2">
                    <label class="inline-flex items-center">
                    <input type="radio" checked="checked" class="form-radio" name="verificationtype" value="http-01">
                    <span class="ml-2 font-semibold">Include Subdomains</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                    <input type="radio" class="form-radio" name="verificationtype" value="dns-01">
                    <span class="ml-2 font-semibold">Dont include Subdomains</span>
                    </label>
                </div>
            </div>

            <div class="w-full flex mt-5 justify-end flex-row">
                <button id="sslstart"  class="px-3 inline-flex mt-4 rounded-md shadow-sm py-2 bg-blue-700 text-gray-200">
                    <svg id="inprogess" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Next
                </button>
            </div>
            
        </div>

        <div id="step2" style="display:none" class="  flex flex-col">
            
            
            <div class="bg-white">
                <div class="p-4 flex-col flex">
                    <h3 class=" m-auto text-xl font-semibold text-gray-600" >Verify your domain</h3>
                </div>
                <nav class="flex flex-col sm:flex-row">
                    <button onclick="switchtab('#httpmethod')" id="httpmethodbtn" class="text-gray-600 text-lg py-4 px-6 block hover:text-blue-500 focus:outline-none text-blue-500 border-b-2 border-blue-500 font-medium ">
                        HTTP
                    </button>
                    <button id="dnsmethodbtn" onclick="switchtab('#dnsmethod')" class="text-gray-600 text-lg py-4 px-6 block hover:text-blue-500 focus:outline-none">
                        DNS
                    </button>
                </nav>
                
            </div>
            <div class="p-5 flex flex-col ">
                <div class="flex flex-col" id="httpmethod">
                    <div class="flex flex-col" style="display:none"  id="httpmethodmanual">
                        <p class="text-gray-700 m-2 text-base">Create <b>.well-known/acme-challenge</b> folder at root of your domain.<br>Put following file(s) in that folder as it is. Remove .txt extention from file if it is present after downloading files below</p>
                        <div class="flex flex-col justify-center">
                            <div id="httptable">
                                
                            </div>
                            
                        </div>
                        
                        <button id="verifyhttp" class="px-3 ml-auto ml-auto inline-flex mr-auto mt-4 rounded-md shadow-sm py-2 bg-red-800 text-gray-200">
                            <svg id="verifyhttpprogess" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verify HTTP
                        </button>
                    </div>
                    <div class="w-full flex flex-col justify-center" id="completehttp">

                            <p class="text-gray-700 m-auto mt-5 httpverifyinfo text-base">We are attempting to verify domain</p>
                            <p class="text-gray-700 m-auto mt-2 httpverifyinfo text-base">creating files at <b><?php echo get_home_path(); ?>.well-known/acme-challenge</b></p>
                            <p style="display:none" class="text-gray-700 m-auto mt-2 httpverificationprogress text-base">Attepting to verify via HTTP method. This may take some time.</p>
                            <svg class="animate-spin m-auto mt-5 h-40 w-40 text-yellow-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                    </div>
                </div>

                <div class="flex flex-col" style="display:none" id="dnsmethod">
                    <p class="text-gray-700 text-base">Create following TXT records in you domain DNS</p>
                    
                    <div class="flex flex-row justify-center">
                        <div id="dnstables" class="flex flex-row  justify-between">
                        
                        </div>
                    </div>
                    
                    <button id="verifydns" class="px-3 ml-auto ml-auto inline-flex mr-auto mt-4 rounded-md shadow-sm py-2 bg-red-800 text-gray-200">
                        <svg id="verifydnsprogess" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verify DNS
                    </button>
                    <button id="completedns" class="hidden px-3 ml-auto ml-auto inline-flex mr-auto mt-4 rounded-md shadow-sm py-2 bg-red-800 text-gray-200">
                        <svg id="verifydnsprogess" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Complete DNS
                    </button>
                    
                </div>
                
                
            </div>
            <div id="debuginfo" style="display:none" class="p-4 mt-5">
                <h3 class="  text-xl mb-4 font-semibold text-gray-700" >Debug Result</h3>
                <p class=" text-lg mt-2  text-gray-700">HTTP verification: - <span id="httpproblem" class="m-auto text-lg mt-2 text-gray-600">All Okay!</span></p>
                <p id="httpproblemexplaination"  class="m-auto text-base  text-gray-600">Try HTTP method for domain verification</p>
                <p class=" text-lg mt-2  text-gray-700">DNS verification: - <span id="dnsproblem" class="m-auto text-lg mt-2 text-gray-600">All Okay!</span></p>
                <p id="dnsproblemexplaination"  class="m-auto text-base  text-gray-600">Try DNS method for domain verification</p>
            </div>
            <div class="w-full flex flex-row mt-5  justify-between">
                    <button onclick="switchstep('#step1')" class="px-3  inline-flex   rounded-md shadow-sm py-2 bg-gray-700 text-gray-200">
                            Previous
                    </button>
                    <div class="inline flex">
                        <button id="debugbtn" class="px-3 mr-2 inline-flex cursor-not-allowed rounded-md shadow-sm py-2 bg-yellow-700 text-gray-200">
                            <svg id="inprogessdebug" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" style="fill:white" width="20" height="20" class="mr-2" viewBox="0 0 24 24"><path d="M7.074 1.408c0-.778.641-1.408 1.431-1.408.942 0 1.626.883 1.38 1.776-.093.336-.042.695.138.995.401.664 1.084 1.073 1.977 1.078.88-.004 1.572-.408 1.977-1.078.181-.299.231-.658.138-.995-.246-.892.436-1.776 1.38-1.776.79 0 1.431.63 1.431 1.408 0 .675-.482 1.234-1.118 1.375-.322.071-.6.269-.769.548-.613 1.017.193 1.917.93 2.823-1.21.562-2.524.846-3.969.846-1.468 0-2.771-.277-3.975-.84.748-.92 1.555-1.803.935-2.83-.168-.279-.446-.477-.768-.548-.636-.14-1.118-.699-1.118-1.374zm13.485 14.044h2.387c.583 0 1.054-.464 1.054-1.037s-.472-1.037-1.054-1.037h-2.402c-.575 0-1.137-.393-1.227-1.052-.092-.677.286-1.147.765-1.333l2.231-.866c.541-.21.807-.813.594-1.346-.214-.533-.826-.795-1.367-.584l-2.294.891c-.329.127-.734.036-.926-.401-.185-.423-.396-.816-.62-1.188-1.714.991-3.62 1.501-5.7 1.501-2.113 0-3.995-.498-5.703-1.496-.217.359-.421.738-.601 1.146-.227.514-.646.552-.941.437l-2.295-.89c-.542-.21-1.153.051-1.367.584-.213.533.053 1.136.594 1.346l2.231.866c.496.192.854.694.773 1.274-.106.758-.683 1.111-1.235 1.111h-2.402c-.582 0-1.054.464-1.054 1.037s.472 1.037 1.054 1.037h2.387c.573 0 1.159.372 1.265 1.057.112.728-.228 1.229-.751 1.462l-2.42 1.078c-.53.236-.766.851-.526 1.373s.865.753 1.395.518l2.561-1.14c.307-.137.688-.106.901.259 1.043 1.795 3.143 3.608 6.134 3.941 2.933-.327 5.008-2.076 6.073-3.837.261-.432.628-.514.963-.364l2.561 1.14c.529.236 1.154.005 1.395-.518.24-.522.004-1.137-.526-1.373l-2.42-1.078c-.495-.221-.867-.738-.763-1.383.128-.803.717-1.135 1.276-1.135z"/></svg>
                            Debug
                        </button>
                        
                        <button onclick="switchstep('#step3')" class="px-3  inline-flex  rounded-md shadow-sm py-2 bg-blue-700 text-gray-200">
                                Next
                        </button>
                    </div>
                    
            </div>
            
            
        </div>

        <div id="step3" style="display:none" class="  flex flex-col">
            
            
            <div class="bg-white">
                <div class="p-4 flex-col flex">
                    <h3 class=" m-auto text-xl font-semibold text-gray-600" >Get your free certificate</h3>
                </div>
            
                
            </div>
            <div class="p-5">
                <div class="flex flex-col justify-center" id="yourcertificate">
                    <div style="display:none" class="w-full flex-col flex" id="fetchcertificate">
                        <p class="text-gray-700 m-auto text-base">Your certificate is being generated</p>
                        <svg class="animate-spin m-auto mt-5 h-40 w-40 text-green-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    
                    <button onclick="switchstep('#step2')" class="px-3 hidden ml-auto ml-auto inline-flex mr-auto mt-4 rounded-md shadow-sm py-2 bg-gray-700 text-gray-200">
                        Go Back
                    </button>
                    <button id="sslstart" class="px-3 ml-auto ml-auto hidden inline-flex mr-auto mt-2 rounded-md shadow-sm py-2 bg-blue-700 text-gray-200">
                        My Certificate
                    </button>
                </div>
                
            </div>
            

            <div id="certdownloadbox" style="display:none"   class="p-4 flex flex-col w-1/2 ml-auto mr-auto m2-2 mb-2 bg-white rounded-md shadow-md">
                <h3 class="text-lg m-auto font-semibold text-gray-700 mb-1">Your certificate is ready</h3>
                <p class="mt-2 mb-2">Your certificate is saved in WordPress root. Download and install on your server/CPanel. Click below to go to download the certificate</p>
                <p><a class="underline" target="_blank" href="https://wpxlearn.com/how-to-install-wp-free-ssl-certificate/">Learn how to install SSL certificate</a></p>
                <div class="flex flex-row  justify-between">
                    <a href="<?php menu_page_url('certificate',true); ?>" class="px-3 ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-green-700 hover:text-gray-200 text-gray-200">Download SSL Certificate</a>
                </div>
                <?php if(wfs_fs()->is_not_paying()){ ?>
                    <h3 class="text-xl mt-5 m-auto font-semibold text-gray-700 mb-1">Secure More</h3>
                    <p class="mt-2 mb-2">This certificate will protect your main site url but not subdomains. You need to get wildcard certificate for protecting wildcard</p>
                    <p>Upgrade to pro plan and get many features in your plugin, Including WildCard certificate and auto renewal of certificate</p>
                    <div class="flex flex-row  justify-between">
                        <a href="<?php echo wfs_fs()->get_upgrade_url(); ?>" class="px-3 ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-yellow-700 hover:text-gray-200 text-gray-200">Upgrade Now</a>
                    </div>
                <?php } ?>
            </div>
            

            
            
        </div>
        

    </div>


    



    <div class=" w-1/4 p-4 shadow-sm flex flex-col justify-center rounded-sm bg-white">
        <?php if(wfs_fs()->is_not_paying()){ ?>
            <div class="mb-8" >
                <img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/img/upgrade.svg' ?>" />
                <a href="<?php echo wfs_fs()->get_upgrade_url(); ?>" class="py-3 mt-2 flex flex-row justify-center text-lg align-middle  rounded-md shadow-sm block px-5 w-full text-gray-200 bg-yellow-600">
                    <svg class="mr-2" style="fill:white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M3 16l-3-10 7.104 4 4.896-8 4.896 8 7.104-4-3 10h-18zm0 2v4h18v-4h-18z"/></svg>                 
                    <span class="text-bold text-white">Upgrade Plugin</span>
                </a>
            </div>
        <?php } ?> 
        <span class=" mt-5 text-2xl text-gray-800">Extra</span>
        <div class="flex mt-4 ">
            <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/wp-free-ssl?rate=5#new-post" class="py-2  text-center hover:text-white rounded-md shadow-sm block px-4 w-1/2 mr-1 bg-green-700 text-gray-100">Vote Up</a>
            <a target="_blank" href="http://bit.ly/PKDonate" class="py-2  text-center hover:text-white rounded-md shadow-sm block px-4 w-1/2 bg-purple-700 text-gray-100">Donate</a>
        
        </div>
        <div class="flex mt-2">
            <a target="_blank" href="https://track.fiverr.com/visit/?bta=102069&brand=fiverrcpa&landingPage=https%3A%2F%2Fwww.fiverr.com%2Fprasadkirpekar%2Fdevelop-custom-feature-plugin-for-your-wordpress-site" class="py-2 text-center hover:text-white  rounded-md shadow-sm block px-4 w-full mr-1 bg-blue-700 text-gray-100">Make Plugin</a>
            <a target="_blank" href="https://bit.ly/WPFiverr" class="py-2 text-center hover:text-white rounded-md shadow-sm block px-4 w-full bg-gray-800 text-gray-100">Support</a>

        </div>
        <p class="mt-2 text-gray-600">(My own affiliate links. No third party service)</p>

        
        
    </div>
</div>

<script>

</script>