<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Create, edit and manage coupons here. Coupon codes give customers discounts on rides. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">
    <div class="col-sm-12">   
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Add New</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <br />

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-coupon" >Add new coupon</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>


<div class="row">
    <div class="col-sm-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Coupons</h3>            
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>#</th>  
                                <th>Title</th>  
                                <th>Coupon code</th>
                                <th>City</th>
                                <th>Vehicles</th>
                                <th>Discount type</th>
                                <th>Discount</th>
                                <th>Limit count</th>
                                <th>User limit count</th>
                                <th>Active date</th>
                                <th>Expiry date</th>
                                <th>Date created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $count = 0;
                                
                                foreach($coupon_codes_data as $couponcodesdata){
                                    $count++;
                                    $vehicles = $couponcodesdata['vehicles'];
                                    $coupon_vehicles = "";
                                    if(empty($vehicles)){
                                        $coupon_vehicles = "All";
                                    }else{
                                        $vehicles_arr = explode(',',$vehicles);
                                        foreach($vehicles_arr as $vehicle){
                                            if(isset($ridesdata[$vehicle])){
                                                $coupon_vehicles .= "- " . $ridesdata[$vehicle]['ride_type'] . "<br>";
                                            }
                                            
                                        }
                                    }
                                    $coupon_discount_type = !empty($couponcodesdata['discount_type']) ? "Fixed" : "Percentage";
                                    $coupon_active_date = gmdate('Y-m-d', strtotime($couponcodesdata['active_date'] . ' UTC'));
                                    $coupon_expiry_date = gmdate('Y-m-d', strtotime($couponcodesdata['expiry_date'] . ' UTC'));
                                    $coupon_created_date = gmdate('Y-m-d', strtotime($couponcodesdata['date_created'] . ' UTC'));
                                    $coupon_status = empty($couponcodesdata['status']) ? "<a href='coupons.php?status=1&cid={$couponcodesdata['cid']}' class='btn btn-xs btn-success'>Activate</a> " : "<a href='coupons.php?status=0&cid={$couponcodesdata['cid']}' class='btn btn-xs btn-danger'>Deactivate</a>";
                                    echo "<tr><td>{$count}</td><td>{$couponcodesdata['coupon_title']}</td><td>{$couponcodesdata['coupon_code']}</td><td>{$couponcodesdata['r_title']}</td><td>{$coupon_vehicles}</td><td>{$coupon_discount_type}</td><td>{$couponcodesdata['discount_value']}</td><td>{$couponcodesdata['limit_count']}</td><td>{$couponcodesdata['user_limit_count']}</td><td>{$coupon_active_date}</td><td>{$coupon_expiry_date}</td><td>{$coupon_created_date}</td><td>{$coupon_status} <a class='btn btn-xs btn-success edit-coupon-btn' href='#' data-couponcode='{$couponcodesdata['coupon_code']}' data-couponid='{$couponcodesdata['cid']}'  data-couponcityid='{$couponcodesdata['r_id']}' data-couponactdate='{$coupon_active_date}' data-couponexpdate='{$coupon_expiry_date}' data-coupondistype='{$couponcodesdata['discount_type']}' data-coupondis='{$couponcodesdata['discount_value']}' data-coupontitle='{$couponcodesdata['coupon_title']}' data-couponlimit='{$couponcodesdata['limit_count']}' data-couponulimit='{$couponcodesdata['user_limit_count']}' data-selvehicles='{$couponcodesdata['vehicles']}' data-minfare='{$couponcodesdata['min_fare']}' data-maxdisc='{$couponcodesdata['max_discount_amount']}' >Edit</a> <a class='btn btn-xs btn-danger del-coupon-btn' href='coupons.php?action=del&cid={$couponcodesdata['cid']}' >Delete</a></td></tr>";
                                }

                            ?>
                        </tbody>

                        

                    </table>

                </div>
                
                <?php echo empty($coupon_codes_data) ? "<h1 style='text-align:center;'>Nothing to Show. Add coupons to get this area populated.</h1>" : ""; ?>
                    
                                
            </div><!-- /.box-body -->
        </div>
    </div>
</div>



<div class="modal fade" id="add-coupon" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add New Coupon</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter Coupon Title</p>
                                <input  type="text"  required= "required" class="form-control" id="coupon-title" placeholder="50% promo for 3 rides" name="coupon-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select class="form-control" id="city-list" name="city-list">
                                    <option value="0" >Select City</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select Vehicle(s) where this Coupon is valid</p>
                                <select class="form-control" id="vehicle-list" name="vehicle-list">
                                    <option value="0" >All Vehicles</option>
                                    <option value="1" >Custom</option>                                                               
                                </select>                                
                            </div>
                            <div class="col-sm-12" style="display:none;" id="vehicles-options">
                                <br>
                                <?php
                                    foreach($ridesdata as $ridedata){
                                        echo "<span style='padding:15px;'><input type='checkbox' id='c-v-{$ridedata['id']}' value='{$ridedata['id']}' name='coupon-v[]' > <label for='c-v-{$ridedata['id']}'>{$ridedata['ride_type']}</label></span>" . "\n";
                                    }
                                ?> 
                            </div>                     
                        </div>
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter Coupon Code</p>
                                <input  type="text"  required= "required" class="form-control" id="coupon-code" placeholder="" name="coupon-code" value="" > 
                            </div>  
                                
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Active Date</p>
                                <input  type="text"  readonly required= "required" class="form-control datepickerinput" id="coupon-active-date" placeholder="" name="coupon-active-date" value="" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Expiry Date</p>
                                <input  type="text" readonly required= "required" class="form-control datepickerinput" id="coupon-expiry-date" placeholder="" name="coupon-expiry-date" value="" > 
                            </div>   
                                
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Usage Limit</p>
                                <input  type="number" min="0" step="1" class="form-control" id="coupon-usage-limit" placeholder="" name="coupon-usage-limit" value="0" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">User Usage Limit</p>
                                <input type="number" min="1" step="1" class="form-control" id="coupon-user-limit" placeholder="" name="coupon-user-limit" value="1" > 
                            </div>   
                                
                        </div>


                        <div class="form-group">

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Minimum Fare Amount</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="coupon-min-fare" placeholder="" name="coupon-min-fare" value="" > 
                            </div>


                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Discount type</p>
                                <select class="form-control" id="coupon-discount-type" name="coupon-discount-type" style="width: 100%;">
                                    <option value="0" selected="selected">Percentage</option>
                                    <option value="1">Fixed</option>                                                            
                                </select>
                            </div>                            

                        </div>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Discount</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="coupon-discount" placeholder="" name="coupon-discount" value="" > 
                            </div>

                            <div class="col-sm-6" id="disc-max-limit">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Maximum Percentage Discount Amount</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="coupon-max-perct-amnt" placeholder="" name="coupon-max-perct-amnt" value="" > 
                            </div>
                        </div>
                            
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="add-coupon" >Add Coupon</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>





<div class="modal fade" id="edit-coupon" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-coupon-label">Edit Coupon</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="e-coupon-id" hidden="hidden" name="coupon-id" value="0" />  
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter Coupon Title</p>
                                <input  type="text"  required= "required" class="form-control" id="ecoupon-title" placeholder="50% promo for 3 rides" name="coupon-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select class="form-control" id="e-city-list" name="city-list">
                                    <option value="0" >Select City</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select Vehicle(s) where this Coupon is valid</p>
                                <select class="form-control" id="e-vehicle-list" name="vehicle-list">
                                    <option value="0" >All Vehicles</option>
                                    <option value="1" >Custom</option>                                                               
                                </select>                                
                            </div>
                            <div class="col-sm-12" style="display:none;" id="e-vehicles-options">
                                <br>
                                <?php
                                    foreach($ridesdata as $ridedata){
                                        echo "<span style='padding:15px;'><input type='checkbox' id='e-c-v-{$ridedata['id']}' value='{$ridedata['id']}' name='coupon-v[]' > <label for='e-c-v-{$ridedata['id']}'>{$ridedata['ride_type']}</label></span>" . "\n";
                                    }
                                ?> 
                            </div>                     
                        </div>
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Coupon Code</p>
                                <input  type="text"  readonly required= "required" class="form-control" id="e-coupon-code" placeholder="" name="coupon-code" value="" > 
                            </div>  
                                
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Active Date</p>
                                <input  type="text"  readonly required= "required" class="form-control datepickerinput" id="e-coupon-active-date" placeholder="" name="coupon-active-date" value="" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Expiry Date</p>
                                <input  type="text"  readonly required= "required" class="form-control datepickerinput" id="e-coupon-expiry-date" placeholder="" name="coupon-expiry-date" value="" > 
                            </div>   
                                
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Usage Limit</p>
                                <input  type="number" min="0" step="1" class="form-control" id="e-coupon-usage-limit" placeholder="" name="coupon-usage-limit" value="0" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">User Usage Limit</p>
                                <input type="number" min="1" step="1" class="form-control" id="e-coupon-user-limit" placeholder="" name="coupon-user-limit" value="1" > 
                            </div>   
                                
                        </div>


                        
                        <div class="form-group">

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Minimum Fare Amount</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="e-coupon-min-fare" placeholder="" name="coupon-min-fare" value="" > 
                            </div>


                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Discount type</p>
                                <select class="form-control" id="e-coupon-discount-type" name="coupon-discount-type" style="width: 100%;">
                                    <option value="0" selected="selected">Percentage</option>
                                    <option value="1">Fixed</option>                                                            
                                </select>
                            </div>                            

                        </div>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Discount</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="e-coupon-discount" placeholder="" name="coupon-discount" value="" > 
                            </div>

                            <div class="col-sm-6" id="e-disc-max-limit">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Percentage Discount Amount Limit</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="e-coupon-max-perct-amnt" placeholder="" name="coupon-max-perct-amnt" value="" > 
                            </div>
                        </div>
                            
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="edit-coupon" >Update Coupon</button>  


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>



<script>

       


    $('#add-coupon').on('shown.bs.modal', function () {      
        
      
    });



    let add_coupon_discount_type = $('#coupon-discount-type').val();

    if(add_coupon_discount_type == 0){ //percentage type
        $('#disc-max-limit').show();
        $('#coupon-max-perct-amnt').prop('disabled', false);
    }else{//fixed type
        $('#disc-max-limit').hide();
        $('#coupon-max-perct-amnt').prop('disabled', true);
    }


    $('#coupon-discount-type').on('change', function(){
        let val = $(this).val();

        if(val == 0){ //percentage type
            $('#disc-max-limit').fadeIn();
            $('#coupon-max-perct-amnt').prop('disabled', false);
        }else{//fixed type
            $('#disc-max-limit').fadeOut();
            $('#coupon-max-perct-amnt').prop('disabled', true);
        }

    })



    $('#e-coupon-discount-type').on('change', function(){
        let val = $(this).val();

        if(val == 0){ //percentage type
            $('#e-disc-max-limit').fadeIn();
            $('#e-coupon-max-perct-amnt').prop('disabled', false);
        }else{//fixed type
            $('#e-disc-max-limit').fadeOut();
            $('#e-coupon-max-perct-amnt').prop('disabled', true);
        }

    })





    sel_vehicles_list_opt = $('#vehicle-list').val();

    if(sel_vehicles_list_opt == 1){
        $('#vehicles-options').fadeIn();
    }else{
        $('#vehicles-options').fadeOut();
    }


    



    $('#vehicle-list').on('change', function(){
        let option_sel = $(this).val();
        if(option_sel == 1){
            $('#vehicles-options').fadeIn();
        }else{
            $('#vehicles-options').fadeOut();
        }
    })

    $('#e-vehicle-list').on('change', function(){
        let option_sel = $(this).val();
        if(option_sel == 1){
            $('#e-vehicles-options').fadeIn();
        }else{
            $('#e-vehicles-options').fadeOut();
        }
    })


    $('.edit-coupon-btn').on('click', function(){

        $('#e-coupon-code').val($(this).data('couponcode'));
        $('#e-coupon-active-date').val($(this).data('couponactdate'));
        $('#e-coupon-expiry-date').val($(this).data('couponexpdate'));
        $('#e-coupon-usage-limit').val($(this).data('couponlimit'));
        $('#e-coupon-user-limit').val($(this).data('couponulimit'));
        $('#e-coupon-discount').val($(this).data('coupondis'));
        $('#e-coupon-id').val($(this).data('couponid'));
        $('#ecoupon-title').val($(this).data('coupontitle'));
        $('#e-coupon-min-fare').val($(this).data('minfare'));
        $('#e-coupon-max-perct-amnt').val($(this).data('maxdisc'));


        sel_city = $(this).data('couponcityid');
        sel_disc_type = $(this).data('coupondistype');
        sel_vehicles_list_opt = $(this).data('selvehicles');
        if(sel_vehicles_list_opt){
            jQuery("select#e-vehicle-list option[value='0']").prop({selected: false});
            jQuery("select#e-vehicle-list option[value='1']").prop({selected: true}); 
            $('#e-vehicles-options').show();
            let vehicles_ids_arr = sel_vehicles_list_opt.split(',');
            vehicles_ids_arr.forEach(function(val,indx){
                jQuery(`input#e-c-v-${val}`).prop('checked', true);
            })
        }else{
            jQuery("select#e-vehicle-list option[value='0']").prop({selected: true});
            jQuery("select#e-vehicle-list option[value='1']").prop({selected: false});
            $('#e-vehicles-options').hide(); 
            jQuery("input[id^=e-c-v-").prop('checked', false);
        }

        jQuery("select#e-city-list option[value='" + sel_city + "']").prop({selected: true}); 
        jQuery("select#e-coupon-discount-type option[value='" + sel_disc_type + "']").prop({selected: true}); 
                
        $('#edit-coupon').modal('show');
    })



    $('#edit-coupon').on('hidden.bs.modal', function () {
        jQuery("select#e-city-list option[value='" + sel_city + "']").prop({selected: false});    
        jQuery("select#e-coupon-discount-type option[value='" + sel_disc_type + "']").prop({selected: false}); 
        
        jQuery("select#e-vehicle-list option").prop({selected: false});    
        jQuery("input[id^=e-c-v-").prop({selected: false}); 
    });

    
    var imgurl;
    $('.del-coupon-btn').on('click', function(e){
        e.preventDefault();
        element = $(this);
        imgurl = '../img/info_.gif?a=' + Math.random();
        swal({
                title: "<h2>Delete coupon</h2>",
                text: "This coupon will be deleted" ,
                imageUrl:imgurl,
                html:true,
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                closeOnConfirm: true,
                closeOnCancel: true
                },
                function(isConfirm){
                if (isConfirm) {
                        var link = element.attr('href');
                        window.location = link;
                } 
            });
    })
    


   

    

    



</script>