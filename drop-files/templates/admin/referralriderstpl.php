<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Riders Referrals enable you manage incentives through ride discounts for riders who refer other people to register on the service. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Referral Details</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             
             
                      <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                            
                            <div class="form-group">                            
                                <div class="col-sm-6">
                                    <p>Referral Incentive Beneficiary</p>
                                    <select class="form-control" id="ref-benef" name="ref-benef">
                                        <option value="0" <?php echo isset($referral_data['beneficiary']) && $referral_data['beneficiary'] == 0 ? "selected='selected'" : ""; ?> >Customer</option>
                                        <option value="1" <?php echo isset($referral_data['beneficiary']) && $referral_data['beneficiary'] == 1 ? "selected='selected'" : ""; ?> >Invitee</option>
                                        <option value="2" <?php echo isset($referral_data['beneficiary']) && $referral_data['beneficiary'] == 2 ? "selected='selected'" : ""; ?> >Customer and Invitee</option>                                                                  
                                    </select>                                    
                                </div>                     
                            </div>


                            <div class="form-group">

                                <div class="col-sm-6">
                                    <p style="margin-top: 15px;margin-bottom: 2px;"><span style="color:red">*</span>Discount (%)</p>
                                    <input type="number"  min="0.00" step="0.01" max="100" required= "required" class="form-control" id="ref-discount" placeholder="" name="ref-discount" value="<?php echo isset($referral_data['discount_value']) ? $referral_data['discount_value'] : ""; ?>" > 
                                </div>

                            </div>




                            <div class="form-group">

                                <div class="col-sm-6">
                                    <p style="margin-top: 15px;margin-bottom: 2px;">Status</p>
                                    <select class="form-control" id="ref-status" name="ref-status" style="width: 100%;">
                                        <option value="1" <?php echo isset($referral_data['status']) && $referral_data['status'] == 1 ? "selected='selected'" : ""; ?> >Active</option>
                                        <option value="0" <?php echo isset($referral_data['status']) && $referral_data['status'] == 0 ? "selected='selected'" : ""; ?>>Inactive</option>                                                            
                                    </select>
                                </div>

                            </div>


                            <!-- <div class="form-group">
                            
                                <div class="col-sm-12">
                                    <p style="margin-top: 15px;margin-bottom: 2px;">Description (Displays on rider's App)</p>
                                    <textarea  rows="3" style="display:block; width:100%;" name="ref-desc" placeholder="Earn xx% discount on your next ride when you invite a friend to register on our service using your referral code!" maxlength="500"><?php echo isset($referral_data['description']) ? $referral_data['description'] : ""; ?></textarea>
                                </div>  
                                
                            </div> -->
                          
                                                 
                            
                             
                              <hr />
                           <button type="submit" class="btn btn-primary btn-block" value="1" name="saveref" >Save</button> 
                        </form>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>

















