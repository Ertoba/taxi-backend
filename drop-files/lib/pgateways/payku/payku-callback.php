<?php 

    //include FILES_FOLDER."/templates/headertpl.php";
    $res_status = 0;
    $order_id = isset($_GET['orderid']) ? (int) $_GET['orderid'] : 0;

    if($order_id){

        $p_pk = DEFAULT_PAYMENT_GATEWAY['payku']['payku-public-token'];

        $query = sprintf('SELECT * FROM %stbl_pgateway WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $order_id);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);

                $curl = curl_init("https://app.payku.cl/api/transaction/{$row['p_transaction_ref']}");
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($curl, CURLOPT_USERPWD, $p_sk);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_pk}","cache-control: no-cache"));
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                //curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
                //curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
                $json_response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
            
                $response = json_decode($json_response, true);

                if(json_last_error()){
                    $res_status = 0;
                }elseif(!isset($response['status'])){
                    $res_status = 0;
                }elseif($response['status'] == "pending" || $response['status'] == "success"){                    
                    $res_status = 1;
                }


            }
            
        }
  
        
        
        
        
		
        

    }

    
?>

<div style="clear:both;"></div>
<div style="background-color:#fff;border-bottom:thin solid #ccc;">
    <div class="container">
        <div class="row">        
            
            <?php if(!$res_status){?>   
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

