
<?php
set_time_limit(120);
$error = [];
$show_success_message = 0;
$license_file_write_message = "";
$license_data = [];
$write_success = false;


if(!empty($_POST)){

    if(!function_exists("curl_init")){
        $error[] = 'CURL PHP extension is not found on this server. Please install PHP CURL to continue';
    }

    if(empty($_POST['p-code'])){
        $error[] = 'Please enter a purchase code';
    }

    if(empty($_POST['serverurl'])){
        $error[] = 'Please enter the server url';
    }elseif((substr($_POST['serverurl'],0,strlen('http://')) != 'http://') && (substr($_POST['serverurl'],0,strlen('https://')) != 'https://')){
        $error[] = 'Server URL must start with https';
    }elseif(get_domain(trim($_POST['serverurl'])) == false){
        $error[] = 'Please enter a valid server url';
    }

    if(empty($_POST['bizname'])){
        $error[] = 'Please enter a name for your taxi business';
    }


    if(empty($_POST['tagline'])){
        $error[] = 'Please enter a your taxi business tag line';
    }

    //test write permissions first before attempting to activate
    $write_test = file_put_contents(dirname(__DIR__) . "/drop-files/lib/wt.php","Woohoo! Droptaxi is able to write to the drop-files folder." . " ?>");

    if(!$write_test){
        $show_success_message = 1;        
    }else{

        //@unlink(dirname(__DIR__) . "/drop-files/lib/wt.php");

        //echo get_domain(trim($_POST['serverurl']));

        if(empty($error)){
            //all good. activate software
            $content_json = json_encode($_POST);
            $curl = curl_init('https://droptaxi.com.ng/service-endpoints/activation/v230/activatedroptaxi.php');
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            //curl_setopt($curl, CURLOPT_HTTPGET, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $content_json);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $response = json_decode($json_response, true);
            
            if(json_last_error()){
                $error[] = 'Remote activation server returned error. Droptaxi is not activated';
            } 
            
            if(isset($response['error'])){
                $error[] = $response['message'];            
            }

            if(isset($response['success'])){
                $show_success_message = 1;
                
                $license_data = $response['license_data'];
                $common_data = $response['common_data'];
                $options_data = $response['options_data'];

                //save license.php file
                $write_success = file_put_contents(dirname(__DIR__) . "/drop-files/lib/license.php",'<?php $license_data = ' . var_export($response['license_data'], true) . " ?>");
                if($write_success === false){
                    $license_file_write_message = "<span style='color:red'>We couldn't write the license data to the license.php file in the drop-files/lib folder on your server. You can manually update the file with the above license information.</span>";
                }else{
                    if(!empty($response['common_data'])){
                        file_put_contents(dirname(__DIR__) . "/drop-files/lib/common.php",$response['common_data']);
                    }

                    if(!empty($response['options_data'])){
                        file_put_contents(dirname(__DIR__) . "/drop-files/lib/options.php",$response['options_data']);
                    }
                    
                    
                    $license_file_write_message = "Your license file has been updated automatically. No further actions are required.";
                }

            }

        }

    }

}

function get_domain($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }
    return false;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Activate Droptaxi</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  
  <!-- Favicons -->
  <link href="img/favicon.png" rel="icon">
  <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,500,600,700,700i|Montserrat:300,400,500,600,700" rel="stylesheet">

  <!-- Bootstrap CSS File -->
  <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Libraries CSS Files -->
  <link href="lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link href="lib/animate/animate.min.css" rel="stylesheet">
  
  
  <!-- Main Stylesheet File -->
  <link href="css/style.css" rel="stylesheet">
  <link href="css/floating-wpp.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/sweetalert.css">
  
</head>

<body>
    <div style="clear:both;"></div>
    <div style="background-color:#f0f8ff;border-bottom:thin solid #ccc;min-height:100vh;">
            <br >
            <br >
            <br >
        <div class="container">
            <div class="row">               
                    
                <div class="col-sm-8 ml-auto mr-auto">
                    <div style="min-height: 500px;padding: 15px;border: thin solid #d8d8d8;background-color: white;border-radius: 5px;">

                        <div style="<?php echo  $show_success_message == 0 ? '' : 'display:none';?>">
                                            
                            <form style="margin: 0 0 30px 0;" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post">
                                <input type="hidden" name="purchase-code" id="purchase-code" value="">
                                
                                <br>
                                <div><img src="img/droptaxi-vlogo.png?v=230" style="width:100%;display:block;margin-left:auto;margin:20px auto;" /></div>
                                <br>
                                <div style="padding: 10px;">
                                    <p style="font-size: 36px;font-weight:300;margin: 0 0 20px;text-align: center;">Activate Droptaxi</p>
                                    <p style="margin:0;font-size: 16px;text-align: left;">Activation is required for Droptaxi to run on this server. Please fill the form below with the correct information. <span>Ensure you enter the correct Server URL address where Droptaxi will run as every activation is linked to a domain and cannot be changed once activated for that domain. We recommend running Droptaxi on a sub-domain so you can use the main domain for your company's website.</span></p>
                                </div>
                                <br>
                                <?php
                                    if (!empty($GLOBALS['error'])) {
                                        foreach ($GLOBALS['error'] as $error) {
                                            echo '<div class="alert alert-danger" role="alert">'.$error.'</div>';
                                        }
                                    } 
                            
                                    if (!empty($GLOBALS['messages'])) {
                                        foreach ($GLOBALS['messages'] as $messages) {
                                            echo '<div class="alert alert-success" role="alert">'.$messages.'</div>';
                                        }
                                    } 
                            
                                ?>
                                                        
                                    <div class="form-group">
                                        <p style="margin:10px 0;font-size:12px;"><span style='color:red'>*</span>Enter your codecanyon purchase code</p>
                                    <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text fa fa-barcode"></span>
                                            </div>
                                            <input type="text" required class="form-control" placeholder="" name="p-code" id="p-code" value="<?php echo isset($_POST['p-code']) ? $_POST['p-code'] : ''; ?>">
                                        </div> 
                                    </div>
                                    
                                    <div class="form-group">
                                        <p style="margin:10px 0;font-size:12px;" ><span style='color:red'>*</span>Enter the server URL address where Droptaxi will run. Example: <span style='color:red;font-weight:bold;'>https://appserver.dreamtaxi.com</span></p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text fa fa-globe"></span>
                                            </div>
                                            <input type="text" required class="form-control" placeholder="" name="serverurl" id="serverurl" value="<?php echo isset($_POST['serverurl']) ? $_POST['serverurl'] : (isset($_SERVER['HTTP_HOST']) ? 'https://' .$_SERVER['HTTP_HOST'] : ''); ?>">
                                        </div>
                                    </div> 

                                    <div class="form-group">
                                        <p style="margin:10px 0;font-size:12px;" ><span style='color:red'>*</span>Enter your taxi business name. Example: <span style='color:red;font-weight:bold;'>Dream Taxi</span></p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text fa fa-suitcase"></span>
                                            </div>
                                            <input type="text" class="form-control" required placeholder="" id="bizname" name="bizname" value="<?php echo isset($_POST['bizname']) ? $_POST['bizname'] : ''; ?>" >
                                        </div>
                                    </div> 


                                    <div class="form-group">
                                        <p style="margin:10px 0;font-size:12px;" ><span style='color:red'>*</span>Enter your business tag line. Example: <span style='color:red;font-weight:bold;'>The Best Taxi Service</span></p>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text fa fa-suitcase"></span>
                                            </div>
                                            <input type="text" class="form-control" required placeholder="" id="tagline" name="tagline" value="<?php echo isset($_POST['tagline']) ? $_POST['tagline'] : ''; ?>">
                                        </div>
                                    </div> 


                                <div style="text-align:left;" ><input type="submit" name="activate" value="Activate" class="btn btn-lg btn-primary"></div>
                                
                            </form>

                        </div>
                        <div style="<?php echo  $show_success_message == 1 ? '' : 'display:none';?>">
                            
                            <div style="padding: 10px;<?php echo  $write_success === false ? '' : 'display:none';?>">
                                <img src="img/info_.gif" style="width:100px;display:block;margin-left:auto;margin:20px auto;" />
                                <br>
                                <p style="font-size: 18px;font-weight:bold;margin: 0 0 10px;text-align: center;">Activation Failed</p>
                                <p style="margin:0;font-size: 16px;text-align: center;">Activation of Droptaxi was not successful. We couldn't write your license file in the drop-files folder. Please ensure you have write permissions to the drop-files folder. Also disable open_basedir if it is enabled. Please take these actions and try again. If all fails, contact us at droptaxisoftware@gmail.com</p>
                                <br>
                                <br>
                                
                                    
                                <br>
                                <a href='/index.php' class='btn btn-lg btn-success'>Return</a>

                            </div>
                            <div style="padding: 10px;<?php echo  $write_success !== false ? '' : 'display:none';?>">
                                <img src="img/success_.gif" style="width:100px;display:block;margin-left:auto;margin:20px auto;" />
                                <br>
                                <p style="font-size: 18px;font-weight:bold;margin: 0 0 10px;text-align: center;">Activation Successful</p>
                                <p style="margin:0;font-size: 16px;text-align: center;">Congratulations! You have successfully activated Droptaxi. Thank you for your purchase. We wish you great success in your taxi business.</p>
                                <br>
                                <br>
                                <p>Here are your activation details:</p>
                                <br>
                                <p><b>License Key:</b> <?php echo isset($license_data['license_key']) ? $license_data['license_key'] : ''; ?></p>
                                <p><b>Server URL:</b> <?php echo isset($license_data['domain_url']) ? $license_data['domain_url'] : ''; ?></p>
                                <p><b>Company Name:</b> <?php echo isset($license_data['company_name']) ? $license_data['company_name'] : ''; ?></p>
                                <p><b>Company Tag Line:</b> <?php echo isset($license_data['company_tagline']) ? $license_data['company_tagline'] : ''; ?></p>
                                <p><b>Purchase Code:</b> <?php echo isset($license_data['purchase_code']) ? $license_data['purchase_code'] : ''; ?></p>
                                <br>
                                    
                                <br>
                                <a href='/index.php' class='btn btn-lg btn-success'>Continue</a>

                            </div>
                            <br>
                        </div>
                        
                    </div>
                </div>

            </div>

        </div>
    </div>


</body>

</html>

<?php

?>
