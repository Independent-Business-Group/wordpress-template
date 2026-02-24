<div class="w-full py-8 px-5 bg-white shadow-sm">
    <div class="flex justify-between">
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
        <h2 class=" m-auto text-3xl text-gray-700" >Enable HTTPS</h2>
    </div>
    <div class="flex flex-col w-full">
        
        <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-10 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-2">Apply your certificate</h3>
            <div class="mt-2 mb-2">
                <p>Before you continue, Please make sure you have <a class="underline" target="_blank" href="https://wpxlearn.com/how-to-install-wp-free-ssl-certificate/">installed certificate on your server/CPanel.</a></p>
                
            </div>

            <form class="ml-auto mr-auto flex flex-col justify-center" action="<?php echo $action_url; ?>" method="post">
                
            <label class="  m-auto mt-2 block text-gray-500 font-bold">
                <input required class="mr-2 leading-tight" type="checkbox">
                <span class="text-sm">
                    I have installed certificate on server
                </span>
            </label>
            <div class="flex flex-row  justify-between">
                    <input type="hidden" value="1" name="enablehttps">
                    <button type="submit" class="px-3 m-auto items-center  flex  mt-4 rounded-md shadow-sm py-2 bg-gray-800 text-gray-200"><svg class="mr-1" style="fill:green" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-2.995 2.995-7.486 4-11 4 0 8.583 5.068 16.097 11 20 5.932-3.903 11-11.417 11-20-3.514 0-8.005-1.005-11-4z"/></svg>Enable Force HTTPS</button>
                
            </div>
            <?php if($havessl) { ?>
                <div class="flex items-center bg-green-500 mt-4 text-white text-sm font-bold px-4 py-3" role="alert">
                <p>We have detected SSL on your server. You should enable Force HTTPS for ensured security</p>
                </div>
                
            <?php } else { ?>
                <div class="flex items-center bg-red-500 mt-4 text-white text-sm font-bold px-4 py-3" role="alert">
                <p>We have not detected SSL on your server! Have you installed it correctly?</p>
                </div>
            <?php } ?>
            </form>
        </div>
        
        <div class="p-4 flex flex-col w-1/2 ml-auto mr-auto mt-5 bg-white rounded-md shadow-md">
            <h3 class="text-lg m-auto font-semibold text-gray-700 mb-1">Revert</h3>
            <div class="flex flex-row  justify-between">
            <form class="ml-auto mr-auto" action="<?php echo $action_url; ?>" method="post">
                    <input type="hidden" value="1" name="disablehttps">
                    <button type="submit" class="px-3 items-center  flex ml-auto mr-auto mt-4 rounded-md shadow-sm py-2 bg-gray-800 text-gray-200"><svg class="mr-1" style="fill:#a22424" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-2.995 2.995-7.486 4-11 4 0 8.583 5.068 16.097 11 20 5.932-3.903 11-11.417 11-20-3.514 0-8.005-1.005-11-4z"/></svg>Disable HTTPS</button>

            </form>
            
            </div>
        </div>
        

    </div>

    

</div>