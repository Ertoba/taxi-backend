<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Setup rewards for your loyal customers. The more they spend on the service the more points they earn. Points can be redeemed to earn cash in wallet.
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Reward Point Details</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             
             
                      <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                            
                      <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p id="curtopointdesc" style="margin-top: 10px;margin-bottom: 2px;" >Enter the amount of money in (<?php echo isset($_SESSION['default_currency']) ? $_SESSION['default_currency']['iso_code'] : 'NGN'; ?>) customers must spend to earn 1 reward point</p>
                                <input  type="number"  min="0" step="0.0001" required= "required" class="form-control" id="curtopoint" placeholder="<?php isset($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] . '1000' : '₦1000'; ?>" name="curtopoint" value="<?php echo isset($reward_points_data['cur_to_points_conv']) ? $reward_points_data['cur_to_points_conv'] : ""; ?>" > 
                            </div>  
                                
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p id="pointtocurdesc" style="margin-top: 10px;margin-bottom: 2px;" >Enter the amount of money in (<?php echo isset($_SESSION['default_currency']) ? $_SESSION['default_currency']['iso_code'] : 'NGN'; ?>) customers earn in redeeming 1 reward point</p>
                                <input  type="number"  min="0" step="0.0001" required= "required" class="form-control" id="pointtocur" placeholder="<?php isset($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] . '10' : '₦10'; ?>" name="pointtocur" value="<?php echo isset($reward_points_data['points_to_cur_conv']) ? $reward_points_data['points_to_cur_conv'] : ""; ?>" > 
                            </div>  
                                
                        </div>  
                        
                        

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p id="minredeemdesc" style="margin-top: 10px;margin-bottom: 2px;" >Enter the minimum redeemable number of points</p>
                                <input  type="number"  min="0" step="1" required= "required" class="form-control" id="minredeempoint" placeholder="" name="minredeempoint" value="<?php echo isset($reward_points_data['min_points_redeemable']) ? $reward_points_data['min_points_redeemable'] : ""; ?>" > 
                            </div>  
                                
                        </div> 


                        <div class="form-group">

                            <div class="col-sm-6">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Status</p>
                                <select class="form-control" id="reward-status" name="reward-status" style="width: 100%;">
                                    <option value="1" <?php echo isset($reward_points_data['status']) && $reward_points_data['status'] == 1 ? "selected='selected'" : ""; ?> >Active</option>
                                    <option value="0" <?php echo isset($reward_points_data['status']) && $reward_points_data['status'] == 0 ? "selected='selected'" : ""; ?> >Inactive</option>                                                            
                                </select>
                            </div>

                        </div>

                        
                     
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="save-reward" >Save Reward</button> 
                        </form>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>

