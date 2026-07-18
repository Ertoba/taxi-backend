<?php 

    //include FILES_FOLDER."/templates/headertpl.php";
    
?>

<div style="clear:both;"></div>
<div style="background-color:#fff;border-bottom:thin solid #ccc;">
    <div class="container">
        <div class="row">        
            
            <?php if(isset($_GET['resp']) && $_GET['resp'] == "failed"){?> 
                <script>
                    setTimeout(function(){
                        var status = '0';
                        var messageObj = {'status': status};
                        var stringifiedMessageObj = JSON.stringify(messageObj);
                        window.webkit.messageHandlers.cordova_iab.postMessage(stringifiedMessageObj);
                    },1000);        
                    
                </script>        
                <div class="col-sm-8 ml-auto mr-auto">
                    <br >
                    <br >
                    <br >
                    <br >
                    <br >
                    <br >
                    <img src="img/info_.gif" class="gifanim" width="200px"  style="margin-left:auto; margin-right:auto; display:block;"/>
                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>
                    <h1 style="text-align:center;"> Payment Failed</h1>
                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>
                    <p>Unfortunately there was an error in processing your payment at this time. Please try again later. </p>
                    <br >
                    <br >
                    <br >
                    <br >
                </div>

            <?php }else{?>
                <script>
                    setTimeout(function(){
                        var status = '1';
                        var messageObj = {'status': status};
                        var stringifiedMessageObj = JSON.stringify(messageObj);
                        window.webkit.messageHandlers.cordova_iab.postMessage(stringifiedMessageObj);
                    },1000);        
                    
                </script> 
                <div class="col-sm-8 ml-auto mr-auto">
                    <br >
                    <br >
                    <br >
                    <br >
                    <br >
                    <br >

                    <img src="img/success_.gif" class="gifanim" width="200px"  style="margin-left:auto; margin-right:auto; display:block;"/>
                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>
                    <h1 style="text-align:center;"> Payment Processing... </h1>
                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>
                    <p>Thank you for your payment, Your payment is currently being processed. Your account will be credited as soon as payment processing is complete. </p>
                    <br >
                    <br >
                    <br >
                    <br >
                </div>


            <?php } ?>
        </div>

    </div>
</div>

<?php 
        
        //include FILES_FOLDER."/templates/footertpl.php"; 
?>

