
           <div class="row">

           <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-drivers-license"></i> Quick Info!</h4>
                    Modify driver account details.
                  </div>
              </div>
                
                <div class="col-sm-12" >
                    <div class="box box-success">
                        <div class="box-header with-border">
                            <?php

                                $account_activate = '';
                                $enable_photo_edit = '';
                                $enable_vehicle_edit = '';
                                

                                $act_code = 0;
                                if(!empty($driver_page_items['is_activated'])){
                                    $account_activate = "<a href='modify-drvr.php?action=deact&id=".$driver_page_items['driver_id']."' class='btn btn-xs btn-danger'>Deactivate account</a>";
                                }else{
                                    $act_code = !empty($driver_page_items['code']) ? $driver_page_items['code'] : "N/A";
                                    $account_activate = "<a href='modify-drvr.php?action=act&id=".$driver_page_items['driver_id']."' class='btn btn-xs btn-success'>Activate account [{$act_code}]</a>";
                                }


                                if(!empty($driver_page_items['allow_photo_edit'])){
                                    $enable_photo_edit = "<a href='modify-drvr.php?action=disphoto&id=".$driver_page_items['driver_id']."' style='margin-left:15px;' class='btn btn-xs btn-danger'>Disable photo change on App</a>";
                                }else{
                                    
                                    $enable_photo_edit = "<a href='modify-drvr.php?action=enphoto&id=".$driver_page_items['driver_id']."' style='margin-left:15px;' class='btn btn-xs btn-success'>Enable photo change on App</a>";
                                }


                                if(!empty($driver_page_items['allow_vehicle_edit'])){
                                    $enable_vehicle_edit = "<a href='modify-drvr.php?action=discar&id=".$driver_page_items['driver_id']."' style='margin-left:15px;' class='btn btn-xs btn-danger'>Disable vehicle change on App</a>";
                                }else{
                                    
                                    $enable_vehicle_edit = "<a href='modify-drvr.php?action=encar&id=".$driver_page_items['driver_id']."' style='margin-left:15px;' class='btn btn-xs btn-success'>Enable vehicle change on App</a>";
                                }

                            ?>
                        <h3 class="box-title">Modify Driver Details | <?php echo $account_activate . $enable_photo_edit . $enable_vehicle_edit;?></h3>
                        
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <?php
                                //$photo = explode('/',$driver_page_items['photo_file']);
                                $photo_file = isset($driver_page_items['photo_file']) ? $driver_page_items['photo_file'] : "0";

                            ?>


                                <div class="form-group">
                                    <div  style="margin-left:auto; margin-right:auto; float:none;overflow-x: auto;" class="col-sm-6 col-md-6">
                                        <div id="image-editor" style="border:0px">
                                            <div class="cropit-preview" style="background-image: url(<?php echo empty($photo_file) ? "../img/usersample.jpg" : "../photofile.php?file=". $photo_file;?>);"></div>
                                            <h5 style="text-align:center;"><span style="color:red">*</span>Upload driver passport photo. Adjust position of photo by draging in the box. Use controls below to zoom and rotate.</h5>
                                            <p><input  class="form-control cropit-image-input" type="file" name="photo" accept=".jpg,.png" /></p>
                                            
                                            <div class="controls-wrapper"><div class="slider-wrapper"><i style="font-size:12px;" class="fa fa-image"></i><input type="range" class="cropit-image-zoom-input"><i style="font-size:16px;" class="fa fa-image"></i></div><div class="rotate-btns"><i class="fa fa-rotate-left rotate-ccw-btn"></i><i class="fa fa-rotate-right rotate-cw-btn"></i></div></div>

                                        </div>
                                    </div>
                                </div>

                        
                                <form  id="reg-form" enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                                        <input type="hidden" name="image-data" class="hidden-image-data" />
                                        <input  type="text" id="driver-id" hidden="hidden" name="driver-id" value="<?php echo isset($driver_page_items['driver_id']) ? $driver_page_items['driver_id'] : ''; ?>" />   
                                        <br>   
                                        <br>                          
                                        
                                    <div class="form-group">                       
                                        <div class="col-sm-6">
                                                <label for="firstname"><span style="color:red">*</span>Firstname</label>
                                                <input  type="text"  required="required" class="form-control" id="firstname" placeholder="" name="firstname" value="<?php echo !empty($driver_page_items['firstname']) ? $driver_page_items['firstname'] : ''; ?>" >
                                            </div> 

                                            <div class="col-sm-6">
                                                <label for="lastname"><span style="color:red">*</span>Lastname</label>
                                                <input  type="text"  required="required" class="form-control" id="lastname" placeholder="" name="lastname" value="<?php echo !empty($driver_page_items['lastname']) ? $driver_page_items['lastname'] : ''; ?>" >
                                            </div> 
                                            
                                        </div>


                                        <div class="form-group">

                                            <div class="col-sm-4">
                                                    <label for="address"><span style="color:red">*</span>Address</label>
                                                    <input  type="text"  required="required" class="form-control" id="address" placeholder="" name="address" value="<?php echo !empty($driver_page_items['drv_address']) ? $driver_page_items['drv_address'] : ''; ?>" >
                                            </div>


                                            <div class="col-sm-4">
                                                <label for="state"><span style="color:red">*</span>State</label>
                                                <input  type="text" class="form-control" id="state" placeholder="" name="state" value="<?php echo !empty($driver_page_items['state']) ? $driver_page_items['state'] : ''; ?>" >
                                            </div>

                                            <input type="text" id="country-code" name="country-code" hidden value="<?php echo !empty($driver_page_items['country_code']) ? $driver_page_items['country_code'] : 'ng'; ?>" >

                                            <input type="text" id="country-dial-code" name="country-dial-code" hidden value="<?php echo !empty($driver_page_items['country_dial_code']) ? str_replace("+","",$driver_page_items['country_dial_code']) : '234'; ?>" >

                                            <div class="col-sm-4">
                                                <label for="phone" style="display:block;"><span style="color:red">*</span>Phone</label>
                                                <div class="intl-tel-code" id="intl-tel-code" style="width:60px;display: inline-block;position:relative;" onclick="showCountryTelReg();">
                                                    <div id="country-flag" class="<?php echo !empty($driver_page_items['country_code']) ? "iti__flag iti__" . $driver_page_items['country_code'] : 'iti__flag iti__ng'; ?>" data-country="ng" style="cursor:pointer;width:20px;display:inline-block;"></div><span id="tel-code" data-dialcode="234" > <?php echo !empty($driver_page_items['country_dial_code']) ? $driver_page_items['country_dial_code'] : '+234'; ?></span>
                                                    <div id="countrydialcodewrapper" style="display:none;position: absolute;width: 200px;height: 400px;overflow-y: scroll;background-color: white;z-index: 100;border: thin solid #ccc;top: 30px;">
                                                        <?php include('countrydialcodestpl.php');?>
                                                    </div>
                                                </div>
                                                <input  style="display: inline-block;width:calc(100% - 90px);" type="text"  required="required" class="form-control" id="phone" placeholder="" name="phone" value="<?php echo !empty($driver_page_items['phone']) ? (!empty(DEMO) ? mask_string($driver_page_items['phone']) : $driver_page_items['phone']) : ''; ?>" >
                                            </div>
                                            

                                        </div>


                                        


                                        <div class="form-group">
                                            <div class="col-sm-4">
                                                <label for="email"><span style="color:red">*</span>Email</label>
                                                <input  type="text"  required="required" class="form-control" id="email" placeholder="" name="email" value="<?php echo !empty($driver_page_items['email']) ? (!empty(DEMO) ? mask_email($driver_page_items['email']) : $driver_page_items['email']) : ''; ?>" >
                                            </div>

                                            <div  class="col-sm-4">                
                                                <label for="franchise">Franchise</label>
                                                <select class="form-control" name="franchise">
                                                    <?php
                                                        
                                                        foreach($franchise_data as $franchisedata){
                                                            $selected = $franchisedata['id'] == $driver_page_items['franchise_id'] ? "selected" : "";

                                                    ?>
                                                        <option <?php echo $selected;?> value="<?php echo $franchisedata['id'] ?>"><?php echo $franchisedata['franchise_name'] ?></option> 
                                                    
                                                    <?php
                                                        }
                                                    ?>
                                                </select>                
                                            </div>

                                            
                                            <div class="col-sm-4">
                                                <label for="refcode"><span style="color:red">*</span>Referal Code</label>
                                                <input  type="text"  readonly required="required" class="form-control" id="refcode" placeholder="" name="refcode" value="<?php echo !empty($driver_page_items['referal_code']) ? $driver_page_items['referal_code'] : crypto_string("alnum",15); ?>" >
                                            </div>
                                            

                                           

                                        </div>


                                        <div class="form-group">
                                            

                                           

                                            <div class="col-sm-4">
                                                <label for="password"><span style="color:red">*</span>Password</label>
                                                <input  type="text" required="required" class="form-control" id="password" placeholder="" name="password" value="<?php echo !empty($driver_page_items['pwd_raw']) ? $driver_page_items['pwd_raw'] : ''; ?>" >
                                                <a style="margin-top:3px;" id="gen-pass" class="btn btn-success btn-xs">Generate password</a>
                                            </div>


                                        </div>

                                    <br>
                                    <br>
                                        <h4 class="box-title">Driver Car Details</h4>
                                        <hr>

                                        <div class="form-group">
                                            <div class="col-sm-4">
                                                <label for="cpnumber"><span style="color:red">*</span>Car Plate #</label>
                                                <input  type="text" required="required" class="form-control" id="cpnumber" placeholder="" name="cpnumber" value="<?php echo !empty($driver_page_items['car_plate_num']) ? $driver_page_items['car_plate_num'] : ''; ?>" >
                                            </div>


                                            <div class="col-sm-4">
                                                <label for="refcode"><span style="color:red">*</span>Car Make/Model</label>
                                                <input  type="text" required="required" class="form-control" id="carmake" placeholder="" name="carmake" value="<?php echo !empty($driver_page_items['car_model']) ? $driver_page_items['car_model'] : ''; ?>" >
                                            </div>

                                            <div  class="col-sm-4">                
                                                <label for="ridetype">Car / Ride Type</label>
                                                <select class="form-control" name="ridetype">
                                                    <?php
                                                        foreach($ride_data as $ridedata){
                                                            $selected = $ridedata['id'] == $driver_page_items['ride_id'] ? "selected" : "";
                                                    ?>
                                                    <option <?php echo $selected;?> value="<?php echo $ridedata['id'] ?>"><?php echo $ridedata['ride_type'] ?></option> 
                                                    <?php
                                                        }
                                                    ?>
                                                </select>                
                                            </div>


                                        </div>



                                        <div class="form-group">

                                            <!-- <div class="col-sm-4">
                                                <label for="carregnum"><span style="color:red">*</span>Car Reg. #</label>
                                                <input  type="text" required="required" class="form-control" id="carregnum" placeholder="" name="carregnum" value="<?php echo !empty($driver_page_items['car_reg_num']) ? $driver_page_items['car_reg_num'] : ''; ?>" >
                                            </div>  -->  
                                            
                                            <div class="col-sm-4">
                                                <label for="caryear">Car model year</label>
                                                <select class="form-control" id="caryear" name="caryear">                                              
                                                    
                                                    <?php
                                                        foreach($vehicle_years as $vehicleyear){
                                                            $selected = isset($driver_page_items['car_year']) && $driver_page_items['car_year'] == $vehicleyear ? "selected" : "";
                                                            echo "<option {$selected} value='{$vehicleyear}'>{$vehicleyear}</option>\n";
                                                        }
                                                    ?> 
                                                
                                                </select>                                          
                                            </div>
                                            

                                            <div  class="col-sm-4">                
                                                <label for="carcolor">Car Color</label>
                                                <select class="form-control" name="carcolor" id="carcolor">
                                                    <option value="Black">Black</option>
                                                    <option value="Brown">Brown</option>
                                                    <option value="Red">Red</option>
                                                    <option value="Orange">Orange</option>
                                                    <option value="Yellow">Yellow</option>
                                                    <option value="Green">Green</option>
                                                    <option value="Blue">Blue</option>
                                                    <option value="Sky-Blue">Sky-Blue</option>
                                                    <option value="Pink">Pink</option>
                                                    <option value="Purple">Purple</option>
                                                    <option value="Grey-Ash">Grey / Ash</option>
                                                    <option value="White">White</option>
                                                    <option value="Gold">Gold</option>
                                                    <option value="Silver">Silver</option>

                                                </select>                
                                            </div>


                                            <div class="col-sm-4">
                                                <label for="carcolor">Operational City</label>
                                                <select class="form-control" id="carcity" name="carcity">                                              
                                                    <option value="">Select city</option>
                                                    <?php
                                                        foreach($inter_city_routes as $intercityroutes){
                                                         $selected = !empty($driver_page_items['route_id']) && $driver_page_items['route_id'] == $intercityroutes['id'] ? "selected = 'selected'" : '';  
                                                        echo "<option value='{$intercityroutes['id']}' {$selected}>{$intercityroutes['r_title']}</option>\n";
                                                        }
                                                    ?> 
                                                
                                                </select>                                          
                                            </div>


                                        </div>
                                        <!-- <br>
                                        <div class="form-group">

                                            <div class="col-sm-6">
                                                <label for="drivers-license" style="margin-bottom: 20px;"><span style="color:red">*</span>Driver's License</label>
                                                <input type="file" class="form-control" id="drivers-license" name="drivers-license" >
                                                <img src="<?php echo !empty($driver_page_items['driving_license_file']) ? "../photofile.php?file=" . $driver_page_items['driving_license_file'] : '../img/drv-license.png'; ?>" id="drivers-license-preview" style="display:block;width:100%;margin: 20px 0;" />
                                                <input type="text" id="driver-license-data" name="driver-license-data" hidden >                                                
                                                
                                            </div>


                                            <div class="col-sm-6">
                                                <label for="road-worthiness" style="margin-bottom: 20px;"><span style="color:red">*</span>Road Worthiness Certificate</label>
                                                <input type="file" class="form-control" id="road-worthiness" name="road-worthiness" >
                                                <img src="<?php echo !empty($driver_page_items['road_worthiness_file']) ? "../photofile.php?file=" . $driver_page_items['road_worthiness_file'] : '../img/../img/road-worth.png'; ?>" id="road-worthiness-preview" style="display:block;width:100%;margin: 20px 0;" />
                                                <input type="text" id="road-worthiness-data" name="road-worthiness-data" hidden >                                                
                                                
                                            </div>

                                            
                                        </div> -->
                                        <br>

                                        <br>
                            <h4 class="box-title">Bank Details</h4>
                             <hr>

                            <div class="form-group">                       
                                                               
                                <div class="col-sm-6">
                                    <label for="bank-acc-holders-name"><span style="color:red">*</span>Bank Account Holder's Name</label>
                                    <input  type="text" class="form-control" id="bank-acc-holders-name" placeholder="" name="bank-acc-holders-name" value="<?php echo !empty($driver_page_items["bank_acc_holder_name"]) ? $driver_page_items["bank_acc_holder_name"] : ''; ?>" >
                                </div>  

                                <div class="col-sm-6">
                                    <label for="bank-details-acc-num"><span style="color:red">*</span>Account Number</label>
                                    <input  type="text" class="form-control" id="bank-details-acc-num" placeholder="" name="bank-details-acc-num" value="<?php echo !empty($driver_page_items["bank_acc_num"]) ? $driver_page_items["bank_acc_num"] : ''; ?>" >
                                </div>

                            </div>

                            
                            
                            <div class="form-group">
                                <div  class="col-sm-4">                
                                    <label for="ridetype">Select Bank</label>
                                    <select class="form-control" name="bank-details-code" id="bank-details-code">
                                        <?php
                                            $other_bank = 1;
                                            foreach($banks_details as $key => $value){
                                            $selected =  $driver_page_items["bank_code"] == $key ? "selected='selected'" : '';   
                                            if($driver_page_items["bank_code"] == $key)$other_bank = 0;
                                            if($key == "xxx" && $other_bank == 1)$selected = "selected";
                                        ?>
                                        <option value="<?php echo $key ?>" <?php echo $selected;?>><?php echo $value ?></option> 
                                        <?php
                                            }
                                        ?>
                                    </select>                
                                </div> 
                            </div>

                            <div class="form-group" id="other-bank-details" style="<?php echo isset($banks_details[$driver_page_items['bank_code']]) ? "display:none;" : ""; ?>">                       
                               <div class="col-sm-6">
                                    <label for="other-bank-name"><span style="color:red">*</span>Bank Name</label>
                                    <input  type="text" class="form-control" id="other-bank-name" placeholder="" name="other-bank-name" value="<?php echo !empty($driver_page_items['bank_name']) ? $driver_page_items['bank_name'] : ''; ?>" >
                                </div>  
                                

                                <div class="col-sm-6">
                                    <label for="other-bank-code"><span style="color:red">*</span>Bank Code</label>
                                    <input  type="text" class="form-control" id="other-bank-code" placeholder="" name="other-bank-code" value="<?php echo !empty($driver_page_items['bank_code']) ? $driver_page_items['bank_code'] : ''; ?>" >
                                </div>                                                              

                            </div>

                            <div class="form-group">                       
                              
                                
                                <div class="col-sm-6">
                                    <label for="bank-details-swift">Swift / BIC</label>
                                    <input  type="text" class="form-control" id="bank-details-swift" placeholder="" name="bank-details-swift" value="<?php echo !empty($driver_page_items['bank_swift_code']) ? $driver_page_items['bank_swift_code'] : ''; ?>" >
                                </div>  
                                
                                

                            </div>


                             <br>
                           <br>
                            <h4 class="box-title">Driver Commision</h4>
                             <hr>

                            
                            <div class="form-group">                       
                               <div class="col-sm-6">
                                    <label for="commission"><span style="color:red">*</span>Percentage commision for every successful trip</label>
                                    <input  type="number" type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="commission" placeholder="" name="commission" value="<?php echo !empty($driver_page_items["driver_commision"]) ? $driver_page_items["driver_commision"] : ''; ?>" >
                                </div>  
                                
                                

                            </div>

                                        <br>
                                        <br>

                                        <hr>
                                        
                                    <a href="#" id="register" class="btn btn-primary btn-block" value="1" name="savedriver" >Update Driver</a> 
                                    </form>
                
                
                
                                            
                        </div>
                        <!-- /.box-body -->
                    </div>

                </div> <!--/col-sm-12-->

                


        </div>
    
                


<style>


        .cropit-preview {
            
            background-size: cover;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 7px;
            width: 300px;
            height: 300px;
            cursor: move;
        }

        .slider-wrapper {
            display: inline-block;
        }

        .controls-wrapper {
            text-align: center;
            margin-top: 5px;
        }

        .rotate-btns {
            display: inline-block;
            margin-left: 20px;
        }

        .cropit-image-zoom-input {
            width: 130px !important;
            display: inline-block !important;
            margin-left: 5px;
            margin-right: 5px;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
            vertical-align: middle;
        }


        .rotate-cw-btn {
            font-size: 16px;
            margin-left: 20px;
            margin-right: 20px;
            cursor: pointer;
        }

        .rotate-ccw-btn {
            font-size: 16px;
            margin-left: 20px;
            margin-right: 20px;
            cursor: pointer;
        }


        input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        border: 1px solid #777;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #ffffff;
        cursor: pointer;
        margin-top: -8px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
        box-shadow: 1px 1px 1px #000000, 0px 0px 1px #0d0d0d; /* Add cool effects to your sliders! */
        }


        input[type=range]::-moz-range-thumb {
        box-shadow: 1px 1px 1px #000000, 0px 0px 1px #0d0d0d;
        border: 1px solid #777;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #ffffff;
        cursor: pointer;
        }


        input[type=range]::-ms-thumb {
        box-shadow: 1px 1px 1px #000000, 0px 0px 1px #0d0d0d;
        border: 1px solid #777;
        height: 16px;
        width: 16px;
        border-radius: 50%;
        background: #ffffff;
        cursor: pointer;
        }

        input[type=range]::-webkit-slider-runnable-track {
        width: 100%;
        height: 5px;
        cursor: pointer;
        background: #999;
        border-radius: 1.3px;
        border: 0.2px solid #010101;
        }



        input[type=range]::-moz-range-track {
        width: 100%;
        height: 5px;
        cursor: pointer;
        background: #999;
        border-radius: 1.3px;
        border: 0.2px solid #010101;
        }

        input[type=range]::-ms-track {
        width: 100%;
        height: 5px;
        cursor: pointer;
        background: #999;
        border-radius: 1.3px;
        border: 0.2px solid #010101;
        }



</style>


    





















    <!-- jQuery 2.2.3 -->
    <!-- <script src="../js/jquery-1.9.1.min.js"></script>
    <script src="../js/app.js"></script> -->
    <!-- Bootstrap 3.3.6 -->
    <!-- <script src="../js/bootstrap.min.js"></script>
    <script src="../js/sweetalert.min.js"></script>
    <script type="text/javascript" src="../fancybox/source/jquery.fancybox.pack.js?v=2.1.6"></script> -->

         <?php
           
            
           if(!empty($_SESSION['action_success'])){
            $msgs = '';
            foreach($_SESSION['action_success'] as $action_success){
                $msgs .= "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> ".$action_success . "</p>";
            }
        
            $cache_prevent = RAND();
            echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Success</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
        
                unset($_SESSION['action_success']);
        
        }elseif(!empty($_SESSION['action_error'])){
                $msgs = '';
                foreach($_SESSION['action_error'] as $action_error){
                    $msgs .= "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> ".$action_error . "</p>";
                }
        
                $cache_prevent = RAND();
                echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Error</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
            
                    unset($_SESSION['action_error']);
            
        }




       ?>



    


<script src="../js/cropit.js"></script>
<script>
        $('#gen-pass').click(function(){

            var password = generatePass(10);
            $('#password').val(password);

        })

        jQuery("select#carcolor option[value='<?php echo $driver_page_items['car_color'];  ?>']").attr('selected', 'selected');
        jQuery("select#country option[value='<?php echo $driver_page_items['drv_country'];  ?>']").attr('selected', 'selected');

        jQuery('#image-editor').cropit({
                smallImage:'stretch',
                width:300,
                height:300
            });       

            jQuery('.rotate-cw-btn').click(function() {
                jQuery('#image-editor').cropit('rotateCW');
            });
            jQuery('.rotate-ccw-btn').click(function() {
                jQuery('#image-editor').cropit('rotateCCW');
            });

            
            
            

            jQuery('#register').click(function(e) {

                e.preventDefault();                

                var imageData = jQuery('#image-editor').cropit('export', {
                    type: 'image/jpeg',
                    quality: .9                    
                });           

                if(imageData){
                    jQuery('.hidden-image-data').val(imageData);
                };

                
                
                var ref = jQuery('#reg-form').find("[required]");
                var empty_fields;
                var type;
                jQuery(ref).each(function(){
                    
                    
                    if ( jQuery(this).val() == '')
                    {
                        imgurl = '../img/info_.gif?a=' + Math.random();
                        
                        swal({
                                    title: '<h1>Error</h1>',
                                    text: 'Required fields should not be blank.',
                                    imageUrl:imgurl,
                                    html:true
                        }); 
                        
                        jQuery('#busy').modal('hide');
                        jQuery(this).focus();

                        empty_fields = 1;
                    }
                }); 
                    if(empty_fields)return;
                    var phone_num = $('#phone').val();
                    if(phone_num.indexOf('+') != -1){
                        imgurl = '../img/info_.gif?a=' + Math.random();
                        swal({
                                    title: '<h1>Error</h1>',
                                    text: 'Please do not include the international dial code (+___) in the phone number field.',
                                    imageUrl:imgurl,
                                    html:true
                        }); 
                        return;
                    }

                    jQuery('#busy').modal('show');

                    window.setTimeout(function() {
                        jQuery("#reg-form").submit();                
                    }, 1000);


                });


                function generatePass(password_len){

                    var smallalpha="abcdefghijklmnopqrstuvwxyz";
                    var capalpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    var numeric="123456789";
                    var symbols="!@#_&+-";
                    var p_chars='';
                    var temp = '';
                    var smallalpha_len = Math.floor(password_len/2);
                    var capsalpha_len = 1;
                    var symbols_len = 1;
                    var numeric_len = password_len - smallalpha_len - capsalpha_len - symbols_len;


                    for (i=0;i<capsalpha_len;i++)
                        temp+=capalpha.charAt(Math.floor(Math.random()*capalpha.length));

                    for (i=0;i<smallalpha_len;i++)
                        temp+=smallalpha.charAt(Math.floor(Math.random()*smallalpha.length));

                    for (i=0;i<symbols_len;i++)
                        temp+=symbols.charAt(Math.floor(Math.random()*symbols.length));

                    for (i=0;i<numeric_len;i++)
                        temp+=numeric.charAt(Math.floor(Math.random()*numeric.length));    

                        temp=temp.split('').sort(function(){return 0.5-Math.random()}).join('');

                    return temp;
                }


                

                $('#bank-details-code').on('change', function(){
                    var selected_bank_code = $('#bank-details-code').find(':selected').val();
                    var selected_bank_name = $('#bank-details-code').find(':selected').text();

                    if(selected_bank_code === 'xxx'){
                        console.log('yeah');
                        $('#other-bank-details').fadeIn();
                        $('#other-bank-name').val('');
                        $('#other-bank-code').val('');
                    }else{
                        
                        $('#other-bank-details').fadeOut();
                        $('#other-bank-name').val(selected_bank_name);
                        $('#other-bank-code').val(selected_bank_code);            
                    }
                })


                function showCountryTelReg(){

                    $('#countrydialcodewrapper').show();

                    var count = 0;

                    $('.iti__country').off('click').on('click', function(e){
                        e.stopPropagation();
                        var country_code = $(this).data('country-code');
                        var dial_code = $(this).data('dial-code');

                        
                        if(country_code){
                            $('#country-flag').attr('class', 'iti__flag iti__' + country_code);
                            $('#country-code').val(country_code)
                            $('#tel-code').html(' +' + dial_code);
                            $('#country-dial-code').val(dial_code);                        
                        }

                        
                        $('#countrydialcodewrapper').hide();
                        
                        
                            
                    });


                }

                $(document).off('click').on('click', function (e) {
                    //console.log(e);
                    if (!$('#country-flag').is(e.target) && !$('#countrydialcodewrapper').is(e.target) && $('#countrydialcodewrapper').has(e.target).length === 0) 
                    {
                        $('#countrydialcodewrapper').hide();
                    }
                });

                $('#drivers-license').on('change', function(event){
                    readImgFile(event,function(result){
                        if(result.error){
                            $('#drivers-license').val('');
                            $('#drivers-license-preview').attr('src','../img/drv-license.png');
                            var imgurl = '../img/info_.gif?a=' + Math.random();

                            swal({
                                        title: '<h1>Error</h1>',
                                        text: result.error_msg,
                                        imageUrl:imgurl,
                                        html:true
                            });
                            
                            return;
                        }

                        $('#drivers-license-preview').attr('src',result.data);
                        $('#driver-license-data').val(result.data);

                    });
                });


                $('#road-worthiness').on('change', function(event){
                    readImgFile(event,function(result){
                        if(result.error){
                            $('#road-worthiness').val('');
                            $('#road-worthiness-preview').attr('src','../img/road-worth.png');
                            var imgurl = '../img/info_.gif?a=' + Math.random();

                            swal({
                                        title: '<h1>Error</h1>',
                                        text: result.error_msg,
                                        imageUrl:imgurl,
                                        html:true
                            });
                            
                            return;
                        }

                        $('#road-worthiness-preview').attr('src',result.data);
                        $('#road-worthiness-data').val(result.data);
                        

                    });
                });
            




        </script>

     
     
    </body>


</html>