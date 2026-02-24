
<div class="w-full py-8 px-5 bg-white shadow-sm">
    <div class="flex align-middle justify-between">
        <div class=" flex">
        <img class="h-10 w-10 mr-2" src="<?php echo plugin_dir_url(__FILE__).'../assets/img/ssl.png'; ?>" />
        <h3 class=" m-auto text-2xl font-semibold text-gray-700" >WP Free SSL</h3>
        </div>
        <div class=" text-lg">
            <span class=" text-gray-700">Version:</span> <span class=" text-gray-800"><?php echo WPSSL_VER; ?></span>
        </div>
    </div>
</div>
<div class="p-8">
    <div class="flex">
        <h2 class=" m-auto text-2xl text-gray-700" >Your SSL Certificate</h2>
    </div>
    <?php if(file_exists(WPSSL_DIR."/keys/certificate.crt")){ ?>
    <div class="flex flex-col w-full">
        
        
        <?php if($canInstallSSL) { ?>
        <div class="mt-4 p-4">
            <button type="submit" onclick="install_certificate(<?php echo $isCPanelActive ?>)" class="px-3 items-center  flex ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-green-800 text-gray-200">
            <svg id="installsslprogess" style="display:none" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg class="mr-1" style="fill:white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-2.995 2.995-7.486 4-11 4 0 8.583 5.068 16.097 11 20 5.932-3.903 11-11.417 11-20-3.514 0-8.005-1.005-11-4z"/></svg>
                Click to install SSL
            </button>
            
        </div>
        <?php } ?>
        <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-10 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-2">Download Certificate Files</h3>
            <p class="text-base m-auto font-semibold text-gray-600 mb-2">You can manually install SSL via CPanel by copying certificate from here</p>
            <p class="text-base m-auto font-semibold text-gray-600 mb-2">This certificate is valid untill <?php echo $certExpiry; ?></p>
            
            <div class="flex flex-row  justify-between">
            
                <div method="post" action="<?php echo $action_url;?>">
                    <input type="hidden" name="certdownload"/>
                    <input type="hidden" value="1" name="certnumber"/>  
                    <button onclick="fetch_cert(1)" class="px-3 items-center  flex ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-gray-800 text-gray-200"><svg style="fill:#fff" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M15 12c0 1.657-1.343 3-3 3s-3-1.343-3-3c0-.199.02-.393.057-.581 1.474.541 2.927-.882 2.405-2.371.174-.03.354-.048.538-.048 1.657 0 3 1.344 3 3zm-2.985-7c-7.569 0-12.015 6.551-12.015 6.551s4.835 7.449 12.015 7.449c7.733 0 11.985-7.449 11.985-7.449s-4.291-6.551-11.985-6.551zm-.015 12c-2.761 0-5-2.238-5-5 0-2.761 2.239-5 5-5 2.762 0 5 2.239 5 5 0 2.762-2.238 5-5 5z"/></svg>Certificate</button>
                </div>
                <div method="post" action="<?php echo $action_url;?>">
                    <input type="hidden" name="certdownload"/>
                    <input type="hidden" value="2" name="certnumber"/>  
                    <button onclick="fetch_cert(2)" class="px-3 items-center  flex ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-gray-800 text-gray-200"><svg style="fill:#fff" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M15 12c0 1.657-1.343 3-3 3s-3-1.343-3-3c0-.199.02-.393.057-.581 1.474.541 2.927-.882 2.405-2.371.174-.03.354-.048.538-.048 1.657 0 3 1.344 3 3zm-2.985-7c-7.569 0-12.015 6.551-12.015 6.551s4.835 7.449 12.015 7.449c7.733 0 11.985-7.449 11.985-7.449s-4.291-6.551-11.985-6.551zm-.015 12c-2.761 0-5-2.238-5-5 0-2.761 2.239-5 5-5 2.762 0 5 2.239 5 5 0 2.762-2.238 5-5 5z"/></svg>Private Key</button>
                </div>
                <div method="post" action="<?php echo $action_url;?>">
                    <input type="hidden" name="certdownload"/>
                    <input type="hidden" value="3" name="certnumber"/>  
                    <button onclick="fetch_cert(3)" class="px-3 items-center  flex ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-gray-800 text-gray-200"><svg style="fill:#fff" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M15 12c0 1.657-1.343 3-3 3s-3-1.343-3-3c0-.199.02-.393.057-.581 1.474.541 2.927-.882 2.405-2.371.174-.03.354-.048.538-.048 1.657 0 3 1.344 3 3zm-2.985-7c-7.569 0-12.015 6.551-12.015 6.551s4.835 7.449 12.015 7.449c7.733 0 11.985-7.449 11.985-7.449s-4.291-6.551-11.985-6.551zm-.015 12c-2.761 0-5-2.238-5-5 0-2.761 2.239-5 5-5 2.762 0 5 2.239 5 5 0 2.762-2.238 5-5 5z"/></svg>CA Bundle</button>
                </div>
                
            </div>
            <textarea class="w-full p-1 mt-5 h-64" style="display:none" id="certcontent">
                </textarea>
            
        </div>
        
        <div class="flex flex-col w-1/2 ml-auto mr-auto mt-5 ">
            <?php if(get_option('wppssl_ssl_activated',"0")=="0") { ?>    
                <?php if($havessl) { ?>
                    <div class="flex items-center bg-green-500 mt-4 text-white text-sm font-bold px-4 py-3" role="alert">
                    <p>We have detected SSL on your server. You should enable Force HTTPS for ensured security</p>
                    </div>
                    
                <?php } else { ?>
                    <div class="flex items-center bg-red-500 mt-4 text-white text-sm font-bold px-4 py-3" role="alert">
                    <p>SSL certificate is not present yet on your server. Please download files given and install it on your server</p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                    <div class="flex items-center bg-green-700 mt-4 text-white text-sm font-bold px-4 py-3" role="alert">
                        <p>Force HTTPS is Active! Looks like everything is running good</p>
                    </div>
            <?php } ?>
        </div>
        
        <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-5 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-1">Start Over</h3>
            <div class="flex flex-row  justify-between">
            <form class="ml-auto mr-auto" method="post" action="<?php echo $action_url;?>">
                <input type="hidden" value="1" name="certdelete">
                <button type="submit" class="px-3 ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-red-700 text-gray-200">Delete certificate</button>
            </form>
            
            </div>
        </div>

    </div>
    <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-5 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-1">Rate Us</h3>
            <img class="h-auto w-1/2 mt-5 mb-5 m-auto" src="<?php echo plugin_dir_url(__FILE__).'../assets/img/review.svg'; ?>" />
        
            <p class="text-base m-auto font-semibold text-gray-600 mb-2 mt-2">We have generated SSL for you successfully. Your good rating can help us grow more. That will be really appreciated.</p>
            <div class="flex flex-row  justify-between">
                <a href="https://wordpress.org/support/plugin/wp-free-ssl/reviews/#new-post" target="_blank" class="px-3 ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-green-700 hover:text-gray-200 text-gray-200">Rate WP Free SSL</a>
            </div>
    </div>
    <?php }else{ ?>
    <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-5 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-1">Not found!</h3>
            <p class="mt-2 mb-2">It looks like there is no certificate generated yet. Click below to go to get new certificate</p>
            <div class="flex flex-row  justify-between">
                <a href="<?php menu_page_url('wp-free-ssl.php',true); ?>" class="px-3 ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-yellow-700 hover:text-gray-200 text-gray-200">Get SSL Certificate</a>
            </div>
    </div>
    <?php } ?>

    

</div>