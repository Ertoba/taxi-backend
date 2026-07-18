<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Send broadcast messages across all persons on the service. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Message Details</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                

                
                    <form  enctype="multipart/form-data" id="broadcast-form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        
                        <div class="form-group">
                            <div class="col-sm-8">
                                <p> <b>Title</b></p>
                                <input  type="text"  class="form-control" id="heading" placeholder="" name="heading" value="<?php echo isset($_POST["heading"]) ? $_POST["heading"] : ''; ?>" >
                            </div>                           
                        </div>

                        <br>
                        <div class="form-group" id="push-msgcontent">
                            <div class="col-sm-8">
                                <p><span style="color:red">*</span> <b>Message</b>(300 characters Max.) HTML Anchor tags are supported</p>
                                <textarea id="msg-text" maxlength="300" rows="3" style="display:block; width:100%;" name="msg" required="required" ><?php echo isset($_POST["msg"]) ? $_POST["msg"] : ''; ?></textarea>
                            </div>                           
                        </div>
                        <br>
                        <div class="form-group">

                            <div class="col-sm-8">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Add Image (JPEG)</p>
                                <div style="text-align:left;"><img id="notif-fimg-preview" src="../img/picture-sample.png" style="width:200px;" /></div>
                                <input  type="text" hidden="hidden" id="notif-fimg-data" name="notif-fimg-data" value="" > 
                                <input  type="file" class="form-control" id="notif-fimg" name="notif-fimg" value="" > 
                            </div>  
                                
                        </div>

                        
                        <br>
                        <div class="form-group">
                            <div class="col-sm-8">
                                <p><b>Send to</b></p>
                                                            
                                <select class="form-control" id="push-scope" name="push-scope">
                                    <option value="1" selected='selected'>Customers</option>
                                    <option value="2">Drivers</option>
                                    <!-- <option value="3">Staff</option> -->
                                    <option value="4">Specific Customer</option>
                                    <option value="5">Specific Driver</option>
                                    <!-- <option value="6">Specific Staff</option> -->
                                </select>
                            </div>

                        </div>

                        <div id="customer-details" style="display:none;">
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <p><span style="color:red">*</span> <b>Enter Customer Name</b></p>
                                    <input  type="text"   class="form-control" id="booking-customer" placeholder="" name="booking-customer" value="" >
                                    <input  type="text"  hidden="hidden" class="" id="booking-customerid" placeholder="" name="booking-customerid" value="" >
                                </div>                           
                            </div>

                        </div>

                        <div id="driver-details" style="display:none;">
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <p><span style="color:red">*</span> <b>Enter Driver Name</b></p>
                                    <input  type="text"  class="form-control" id="booking-driver" placeholder="" name="booking-driver" value="" >
                                    <input  type="text"  hidden="hidden" class="" id="booking-driverid" placeholder="" name="booking-driverid" value="" >
                                </div>                           
                            </div>

                        </div>

                        <!-- <div id="staff-details" style="display:none;">
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <p><span style="color:red">*</span> <b>Enter Staff Name</b></p>
                                    <input  type="text"  class="form-control" id="booking-staff" placeholder="" name="booking-staff" value="" >
                                    <input  type="text"  hidden="hidden" class="" id="booking-staffid" placeholder="" name="booking-staffid" value="" >
                                </div>                           
                            </div>

                        </div> -->

                        
                        <br>
                        <div id="cities">
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <p><b>Select City</b></p>                                                            
                                    <select class="form-control" id="city-route" name="city-route">
                                        <option value="0" selected='selected'>Select a city</option>
                                        <?php 
                                            foreach($inter_city_routes as $intercityroutes){
                                                echo "<option value='{$intercityroutes['id']}'>{$intercityroutes['r_title']}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>


                            
                        <hr />
                        <button type="submit" class="btn btn-primary btn-block" value="1" name="send-push-msg" >Send</button> 
                    </form>
                
             
                
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>


<script>

window.onload = function(){
    $('#push-scope').find('option').each(function(el){
        $(this).prop('selected',false);
    })
    $('#push-scope').find('option[value=1]').prop('selected',true);
}




$('#push-scope').on('change', function(e){
    var selected_scope = $(this).val();
    var city_scope = $('#city-scope').val();
    switch(selected_scope){
        case '1':
        case '2':
        case '3':
        $('#cities').show();
        $('#customer-details').hide();
        $('#driver-details').hide();
        /* $('#staff-details').hide(); */
        break;

        case '4': 
        $('#customer-details').show();
        $('#driver-details').hide();
        /* $('#staff-details').hide(); */
        $('#cities').hide();
        break;

        case '5':
        $('#customer-details').hide();
        $('#driver-details').show();
        /* $('#staff-details').hide(); */
        $('#cities').hide();
        break;
        
        /* case '6':
        $('#customer-details').hide();
        $('#driver-details').hide();
        $('#staff-details').show();
        $('#cities').hide();   */   

        
        
    }
})


$('#broadcast-form').on('submit', function(e){
    e.preventDefault();
    var imgurl = '../img/info_.gif?a=' + Math.random();
    var selected_scope = $('#push-scope').val();
    if(selected_scope == 4 && !$('#booking-customerid').val()){        
    
        swal({
                    title: '<h1>Error</h1>',
                    text: 'No customer selected! Please select a customer from the autocomplete dropdown list while entering the customer name.',
                    imageUrl:imgurl,
                    html:true
        });
        return;
    }else if(selected_scope == 5 && !$('#booking-driverid').val()){

        swal({
                    title: '<h1>Error</h1>',
                    text: 'No driver selected! Please select a driver from the autocomplete dropdown list while entering the driver name.',
                    imageUrl:imgurl,
                    html:true
        });
        return;

    }/* else if(selected_scope == 6 && !$('#booking-staffid').val()){

        swal({
                    title: '<h1>Error</h1>',
                    text: 'No staff selected! Please select a staff from the autocomplete dropdown list while entering the staff name.',
                    imageUrl:imgurl,
                    html:true
        });
        return;

    } */
    
    $('#broadcast-form')[0].submit();

})


$('#notif-fimg').on('change', function(e){
    
    readImgFile(e, function(res){
        console.log(res);

        if(res.error){
            
            $('#notif-fimg-data').val('');                
            let imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: res.error_msg,
                        imageUrl:imgurl,
                        html:true
            });

        }else{

            $('#notif-fimg-preview').attr('src',res.data);
            $('#notif-fimg-data').val(res.data);
            

        }   
    })



});





function readImgFile(input, callback) {
        if (input.target.files && input.target.files[0]) {
            var imgPath = input.target.files[0].name;
            var imgSize = input.target.files[0].size;
            
            var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
            var result = {data:'',error:1,error_msg:''};
            if(imgSize > 500000){
                //filesize greater than 1MB
                result.error_msg = 'File size must not be greater than 500KB';
                callback(result);
                return;
            }

            if (extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                    var reader = new FileReader();			
                    reader.onload = function (e) {
                        /* jQuery('#passport')
                            .attr('src', e.target.result)
                            .width(150)
                            .height('auto'); */
                        
                        
                        
                        result.data = e.target.result;
                        result.error = 0;
                        callback(result);
                                    
                            
                    };

                    reader.readAsDataURL(input.target.files[0]);
                }

            }else{
                result.error_msg = 'Invalid file type. Only JPG files are allowed.';
                callback(result);
            }
        }
    }



</script>














