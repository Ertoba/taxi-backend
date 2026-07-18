<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Drivers Referrals enable you manage incentives through earnings for drivers who refer other people to register and drive on the service. 
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

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-referral" >Add city referral</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>



<div class="row">
    <div class="col-sm-12">   
        <div class="box box-success">
            <div class="box-header with-border">                      
            <h3 class="box-title">City Drivers Referrals </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                                
                <div> <!--pages-->
                
                    <?php
                            if(!empty($pages)){
                                echo " Pages: ";
                                for($i = 1;$i < $pages + 1; $i++){
                                    if($i == $page_number){
                                        echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                    }else{
                                        echo "<a class='btn' href='help-topics.php?page=".$i."'>".$i."</a>";
                                        }  
                                    
                                }
                            }
                        ?>
                </div><!--/pages-->
                                    
                <br />
                <div class="table-responsive">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>#</th>    
                                <th style="">City</th>
                                <th style="">Beneficiary</th>
                                <th style="">Invitee Incentive</th>
                                <th style="">Driver Commission</th>
                                <th style="">Status</th>
                                <th style="">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            $count = 1;
                                foreach($referral_drivers_data as $referraldriversdata){
                                    $beneficiary = '';
                                    switch($referraldriversdata['beneficiary']){
                                        case 0:
                                        $beneficiary = "Driver";
                                        break;
                                        case 1:
                                        $beneficiary = "Invitee";
                                        break;
                                        default:
                                        $beneficiary = "Driver & Invitee";
                                        break;
                                    }

                                    $item_data = array(
                                                    'item_id'=> $referraldriversdata['ref_id'],
                                                    'beneficiary'=>$referraldriversdata['beneficiary'],
                                                    'city_id'=>$referraldriversdata['route_id'],
                                                    'num_of_rides'=>$referraldriversdata['number_of_rides'],
                                                    'num_of_days'=>$referraldriversdata['number_of_days'],
                                                    'invitee_incentive'=>$referraldriversdata['invitee_incentive'],
                                                    'driver_incentive'=>$referraldriversdata['driver_incentive'],
                                                    'status'=>$referraldriversdata['status'],
                                                    /* 'description'=>$referraldriversdata['description'],
                                                    'description_reg'=>$referraldriversdata['description_reg'] */
                                                );

                                    $item_data_json = json_encode($item_data);

                                    $status = !empty($referraldriversdata['status']) && $referraldriversdata['status'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";                                    
                                    echo "<tr><td>". $count++ . "</td><td>".$referraldriversdata['r_title'] ."</td><td>" . $beneficiary. "</td><td>" . $referraldriversdata['symbol'].$referraldriversdata['invitee_incentive']. "</td><td>" . $referraldriversdata['symbol'].$referraldriversdata['driver_incentive']. "</td><td>" .$status . "</td><td>". "<a href='#' data-itemid='{$referraldriversdata['ref_id']}' class='edit-referral btn btn-success btn-xs'>Edit</a> <a href='referral-drivers.php?action=del&id={$referraldriversdata['ref_id']}' data-itemid='{$referraldriversdata['ref_id']}' class='delete-item del-referral btn btn-danger btn-xs'>Delete</a> <span id='referralitemdata-{$referraldriversdata['ref_id']}' style='display:none;'>{$item_data_json}</span>" ."</td></tr>";
                                }
                        
                            ?>
                        </tbody>
                    </table>
                </div>
                                
                <?php if(empty($referral_drivers_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. Add city referrals to get this area populated.</h1>";} ?> 
            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div> <!--/row-->



<div class="modal fade" id="add-referral" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add a City Referral</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select class="form-control" id="city-list" name="city-list">
                                    <option value="0" >Select City</option>
                                    <?php
                                    foreach($city_currency_data as $citycurrencydata){
                                        echo "<option value='{$citycurrencydata['city_id']}' >{$citycurrencydata['r_title']} (Currency: {$citycurrencydata['name']})</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>
                        
                        <div class="form-group">                            
                            <div class="col-sm-6">
                                <p>Referral Incentive Beneficiary</p>
                                <select class="form-control" id="ref-benef" name="ref-benef">
                                    <option value="0">Driver</option>
                                    <option value="1">Invitee</option>
                                    <option value="2">Driver and Invitee</option>                                                                  
                                </select>                                    
                            </div>                     
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Invitee number of rides</p>
                                <input  type="number" min="0" step="1" class="form-control" id="target-rides" placeholder="" name="target-rides" value="0" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Within number of Days</p>
                                <input  type="number" min="0" step="1" class="form-control" id="target-days" placeholder="" name="target-days" value="0" > 
                            </div>  
                                
                        </div>

                        
                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Invitee Incentive</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="invitee-incentive" placeholder="" name="invitee-incentive" value="" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Driver Commission</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="driver-commission" placeholder="" name="driver-commission" value="" > 
                            </div>

                        </div>


                        <div class="form-group">

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Status</p>
                                <select class="form-control" id="ref-status" name="ref-status" style="width: 100%;">
                                    <option value="1" >Active</option>
                                    <option value="0" >Inactive</option>                                                            
                                </select>
                            </div>

                        </div>


                        <!-- <div class="form-group">

                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description (Displays on driver's App in profile page)</p>
                                <textarea  rows="3" style="display:block; width:100%;" name="ref-desc" placeholder="" maxlength="500"></textarea>
                            </div>  

                        </div> -->


                        <!-- <div class="form-group">

                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description (Displays on driver's App in registration page)</p>
                                <textarea  rows="3" style="display:block; width:100%;" name="ref-desc-reg" placeholder="" maxlength="500"></textarea>
                            </div>  

                        </div> -->
                                                    
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="add-ref" >Add Referral</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="edit-referral" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Edit a City Referral</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="e-referral-id" hidden="hidden" name="e-referral-id" value="0" />                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select disabled class="form-control" id="ecity-list" name="ecity-list">
                                    <option value="0" >Select City</option>
                                    <?php
                                    foreach($city_currency_data_all as $citycurrencydataall){
                                        echo "<option value='{$citycurrencydataall['city_id']}' >{$citycurrencydataall['r_title']} (Currency: {$citycurrencydataall['name']})</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>
                        
                        <div class="form-group">                            
                            <div class="col-sm-6">
                                <p>Referral Incentive Beneficiary</p>
                                <select class="form-control" id="eref-benef" name="eref-benef">
                                    <option value="0">Driver</option>
                                    <option value="1">Invitee</option>
                                    <option value="2">Driver and Invitee</option>                                                                  
                                </select>                                    
                            </div>                     
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Invitee number of rides</p>
                                <input  type="number" min="0" step="1" class="form-control" id="etarget-rides" placeholder="" name="etarget-rides" value="0" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Within number of Days</p>
                                <input  type="number" min="0" step="1" class="form-control" id="etarget-days" placeholder="" name="etarget-days" value="0" > 
                            </div>  
                                
                        </div>

                        
                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Invitee Incentive</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="einvitee-incentive" placeholder="" name="einvitee-incentive" value="" > 
                            </div>

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Driver Commission</p>
                                <input type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="edriver-commission" placeholder="" name="edriver-commission" value="" > 
                            </div>

                        </div>


                        <div class="form-group">

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Status</p>
                                <select class="form-control" id="eref-status" name="eref-status" style="width: 100%;">
                                    <option value="1" >Active</option>
                                    <option value="0" >Inactive</option>                                                            
                                </select>
                            </div>

                        </div>


                        <!-- <div class="form-group">

                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description (Displays on driver's App in profile page)</p>
                                <textarea  rows="3" style="display:block; width:100%;" id="eref-desc" name="eref-desc" placeholder="" maxlength="500"></textarea>
                            </div>  

                        </div> -->

                        <!-- <div class="form-group">

                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description (Displays on driver's App in registration page)</p>
                                <textarea  rows="3" style="display:block; width:100%;" id="eref-desc-reg" name="eref-desc-reg" placeholder="" maxlength="500"></textarea>
                            </div>  

                        </div> -->
                                                    
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="edit-ref" >Update Referral</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>






<script>

    var ref_id = 0;
    var item_json;
    var item_data;
    


    


    $('.edit-referral').on('click', function(){
        ref_id = $(this).data('itemid');
        item_json = $('#referralitemdata-' + ref_id).html();
        item_data = JSON.parse(item_json);
        
        $('#e-referral-id').val(ref_id);
        $('#etarget-rides').val(item_data.num_of_rides);
        $('#etarget-days').val(item_data.num_of_days);
        $('#einvitee-incentive').val(item_data.invitee_incentive);
        $('#edriver-commission').val(item_data.driver_incentive);
        //$('#eref-desc').html(item_data.description);
        //$('#eref-desc-reg').html(item_data.description_reg);

        
        
        jQuery("select#ecity-list option[value='" + item_data.city_id + "']").prop({selected: true}); 
        jQuery("select#eref-benef option[value='" + item_data.beneficiary + "']").prop({selected: true}); 
        jQuery("select#eref-status option[value='" + item_data.status + "']").prop({selected: true}); 
                
        $('#edit-referral').modal('show');

    })



    $('#edit-referral').on('hidden.bs.modal', function () {
        jQuery("select#ecity-list option[value='" + item_data.city_id + "']").prop({selected: false}); 
        jQuery("select#eref-benef option[value='" + item_data.beneficiary + "']").prop({selected: false}); 
        jQuery("select#eref-status option[value='" + item_data.status + "']").prop({selected: false});  
    });

    
    
   

    

    



</script>













