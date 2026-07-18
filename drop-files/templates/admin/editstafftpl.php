<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Quickly edit staff account. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
                            <?php

                                $disp_accounttype = '';
                                $admin_accounttype = '';
                                $biller_accounttype = '';
                                if($staff_data['account_type'] == 2){
                                    $disp_accounttype = 'checked';
                                }elseif($staff_data['account_type'] == 3){
                                    $admin_accounttype = 'checked';
                                }elseif($staff_data['account_type'] == 5){
                                    $biller_accounttype = 'checked';
                                }

                                $account_activate = '';
                                $act_code = 0;
                                if(!empty($staff_data['is_activated'])){
                                    $account_activate = "<a href='edit-staff.php?action=deact&id=".$staff_data['staff_id']."' class='btn btn-xs btn-danger'>Deactivate account</a>";
                                }else{
                                    $act_code = !empty($staff_data['code']) ? $staff_data['code'] : "N/A";
                                    $account_activate = "<a href='edit-staff.php?action=act&id=".$staff_data['staff_id']."' class='btn btn-xs btn-success'>Activate account [{$act_code}]</a>";
                                }

                                //$photo = explode('/',$staff_data['photo_file']);
                                $photo_file = isset($staff_data['photo_file']) ? $staff_data['photo_file'] : "0";

                            ?>
              <h3 class="box-title">Staff Details</h3> | <?php echo $account_activate;?></h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                    <div class="form-group">
                        <div  style="margin-left:auto; margin-right:auto; float:none;overflow-x: auto;" class="col-sm-6 col-md-6">
                            <div id="image-editor" style="border:0px">
                                <div class="cropit-preview" style="background-image: url(<?php echo empty($photo_file) ? "../img/usersample.jpg" : "../userphotofile.php?file=". $photo_file;?>);"></div>
                                <h5 style="text-align:center;"><span style="color:red">*</span>Upload customer passport photo. Adjust position of photo by draging in the box. Use controls below to zoom and rotate.</h5>
                                <p><input  class="form-control cropit-image-input" type="file" name="photo" accept=".jpg,.png" required="required" /></p>
                                
                                <div class="controls-wrapper"><div class="slider-wrapper"><i style="font-size:12px;" class="fa fa-image"></i><input type="range" class="cropit-image-zoom-input"><i style="font-size:16px;" class="fa fa-image"></i></div><div class="rotate-btns"><i class="fa fa-rotate-left rotate-ccw-btn"></i><i class="fa fa-rotate-right rotate-cw-btn"></i></div></div>

                            </div>
                        </div>
                    </div>
             
             
                      <form  id="reg-form" enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input type="hidden" name="image-data" class="hidden-image-data" />
                        <input  type="text" id="staff-id" hidden="hidden" name="staff-id" value="<?php echo isset($staff_data['staff_id']) ? $staff_data['staff_id'] : ''; ?>" />
                             <div class="form-group">
                                <div class="col-sm-12">
                                    <b>Select Account Type</b> <br>
                                    <label class="radio-inline">
                                        <input <?php echo $biller_accounttype; ?> type="radio" name="accounttype" id="biller" value="5"> Biller
                                    </label>
                                    <label class="radio-inline">
                                        <input <?php echo $disp_accounttype; ?> type="radio" name="accounttype" id="dispatcher" value="2"> Dispatcher
                                    </label>
                                    <label class="radio-inline">
                                        <input <?php echo $admin_accounttype; ?> type="radio" name="accounttype" id="admin" value="3"> Admin
                                    </label>                            
                                </div>
                            </div> 
                            
                            <div class="form-group">                       
                               <div class="col-sm-4">
                                    <label for="firstname"><span style="color:red">*</span>Firstname</label>
                                    <input  type="text"  required="required" class="form-control" id="firstname" placeholder="" name="firstname" value="<?php echo !empty($staff_data['firstname']) ? $staff_data['firstname'] : ''; ?>" >
                                </div> 

                                <div class="col-sm-4">
                                    <label for="lastname"><span style="color:red">*</span>Lastname</label>
                                    <input  type="text"  required="required" class="form-control" id="lastname" placeholder="" name="lastname" value="<?php echo !empty($staff_data['lastname']) ? $staff_data['lastname'] : ''; ?>" >
                                </div> 

                                <div class="col-sm-4">
                                    <label for="route-city">City Route </label>
                                    <select class="form-control" id="route-city" name="route-city">
                                        <?php
                                            foreach($route_data as $routedata){ 
                                                $selected = $routedata['r_id'] == $staff_data['route_id'] ? "selected" : '';                                            
                                        ?> 
                                            <option <?php echo $selected; ?> value="<?php echo $routedata['r_id']; ?>"><?php echo $routedata['r_title']; ?></option>
                                        <?php
                                            }
                                        ?>
                                        
                                    </select>
                                </div>
                                
                            </div>


                                                        


                            <div class="form-group">
                                
                                <input type="text" id="country-code" name="country-code" hidden value="<?php echo !empty($staff_data['country_code']) ? $staff_data['country_code'] : 'ng'; ?>" >

                                <input type="text" id="country-dial-code" name="country-dial-code" hidden value="<?php echo !empty($staff_data['country_dial_code']) ? str_replace("+","",$staff_data['country_dial_code']) : '234'; ?>" >

                                <div class="col-sm-4">
                                    <label for="phone" style="display:block;"><span style="color:red">*</span>Phone</label>
                                    <div class="intl-tel-code" id="intl-tel-code" style="width:60px;display: inline-block;position:relative;" onclick="showCountryTelReg();">
                                        <div id="country-flag" class="<?php echo !empty($staff_data['country_code']) ? "iti__flag iti__" . $staff_data['country_code'] : 'iti__flag iti__ng'; ?>" data-country="ng" style="cursor:pointer;width:20px;display:inline-block;"></div><span id="tel-code" data-dialcode="234" > <?php echo !empty($staff_data['country_dial_code']) ? $staff_data['country_dial_code'] : '+234'; ?></span>
                                        <div id="countrydialcodewrapper" style="display:none;position: absolute;width: 200px;height: 400px;overflow-y: scroll;background-color: white;z-index: 100;border: thin solid #ccc;top: 30px;">
                                            <?php include('countrydialcodestpl.php');?>
                                        </div>
                                    </div>
                                    <input  style="display: inline-block;width:calc(100% - 90px);" type="text"  required="required" class="form-control" id="phone" placeholder="" name="phone" value="<?php echo !empty($staff_data['phone']) ? (!empty(DEMO) ? mask_string($staff_data['phone']) : $staff_data['phone']) : ''; ?>" >
                                </div>

                                <div class="col-sm-4">
                                    <label for="email"><span style="color:red">*</span>Email</label>
                                    <input  type="text"  required="required" class="form-control" id="email" placeholder="" name="email" value="<?php echo !empty($staff_data['email']) ? (!empty(DEMO) ? mask_email($staff_data['email']) : $staff_data['email']) : ''; ?>" >
                                </div>


                                <div class="col-sm-4">
                                    <label for="password"><span style="color:red">*</span>Password</label>
                                    <input  type="text" required="required" class="form-control" id="password" placeholder="" name="password" value="<?php echo !empty($staff_data['pwd_raw']) ? $staff_data['pwd_raw'] : ''; ?>" >
                                    <a style="margin-top:3px;" id="gen-pass" class="btn btn-success btn-xs">Generate password</a>
                                </div>


                                


                            </div>


                            <div class="form-group">
                                

                                <div class="col-sm-6">
                                    <label for="refcode"><span style="color:red">*</span>Referal Code</label>
                                    <input  type="text"  readonly required="required" class="form-control" id="refcode" placeholder="" name="refcode" value="<?php echo !empty($staff_data['referal_code']) ? $staff_data['referal_code'] : ''; ?>" >
                                </div>


                                                              
                               

                            </div>                     

                            


                                                 
                            
                             
                              <hr />
                           <button type="submit" id="modify" class="btn btn-primary btn-block" value="1" name="modify" >Modify Staff</button> 
                        </form>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>


<style>

.cropit-preview {
    background-image: url(../img/usersample.jpg);
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




<script src="../js/cropit.js"></script>
<script>


 jQuery('#image-editor').cropit({
        smallImage:'stretch',
        allowDragNDrop:false,
        width:300,
        height:300
    });       

    jQuery('.rotate-cw-btn').click(function() {
        jQuery('#image-editor').cropit('rotateCW');
      });
      jQuery('.rotate-ccw-btn').click(function() {
        jQuery('#image-editor').cropit('rotateCCW');
      });

      
    
    

    jQuery('#modify').click(function(e) {

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
                return false;
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





</script>

















