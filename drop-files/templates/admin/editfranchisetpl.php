<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Quickly Edit a franchise. 
        </div>
    </div>
</div> <!--/Row-->
<div class="row">
    <div class="col-sm-10" >
            <div class="box box-success">
                <div class="box-header with-border">
                <h3 class="box-title">Franchise Details</h3>
                
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                
                
                        <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                            <input  type="text" id="franch-id" hidden="hidden" name="franch-id" value="<?php echo isset($franchise_data['id']) ? $franchise_data['id'] : ''; ?>" />
                                
                            <div class="form-group">
                            
                                <div class="col-sm-6">
                                    <label for="franch-name"><span style="color:red">*</span>Franchise Name</label>
                                    <p>Must be unique</p>
                                    <input  type="text"  class="form-control" id="franch-name" placeholder="" name="franch-name" value="<?php echo isset($franchise_data["franchise_name"]) ? $franchise_data["franchise_name"] : ''; ?>" >
                                </div>  
                                
                            </div>


                            <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="franch-name"><span style="color:red">*</span>Email</label>
                                    <p>Must be unique</p>
                                    <input  type="text"  class="form-control" required="required" id="franch-email" placeholder="" name="franch-email" value="<?php echo isset($franchise_data['franchise_email']) ? (!empty(DEMO) ? mask_email($franchise_data['franchise_email']) : $franchise_data['franchise_email']) : ''; ?>" >
                                </div>  
                                
                            </div>


                            <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="franch-phone"><span style="color:red">*</span>Phone</label>
                                    <p>Must be unique</p>
                                    <input  type="text"  class="form-control" required="required" id="franch-phone" placeholder="" name="franch-phone" value="<?php echo isset($franchise_data['franchise_phone']) ? (!empty(DEMO) ? mask_string($franchise_data['franchise_phone']) : $franchise_data['franchise_phone']) : ''; ?>" >
                                </div>  
                                
                            </div>


                            <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="franch-pwd"><span style="color:red">*</span>Password</label>
                                    <input  type="text"  class="form-control" id="franch-pwd" placeholder="" name="franch-pwd" value="<?php echo isset($franchise_data['pwd_raw']) ? $franchise_data['pwd_raw'] : ''; ?>" >
                                    <a style="margin-top:3px;" id="gen-pass" class="btn btn-success btn-xs">Generate password</a>
                                </div>
                                
                            </div>
                            
                            
                            <div class="form-group">
                                
                                <div class="col-sm-12">
                                        <label for="franch-desc"><span style="color:red">*</span>Franchise Description</label>
                                        <p>A brief description of this franchise</p>
                                        <textarea  rows="3" style="display:block; width:100%;" name="franch-desc" required="required" maxlength="250"><?php echo isset($franchise_data["franchise_desc"]) ? $franchise_data["franchise_desc"] : ''; ?></textarea>
                                </div>  
                                    
                            </div>


                            <div style="<?php echo $franchise_data['id'] == 1 ? "display:none" : ""; ?>">   

                                <br>
                                <h4 class="box-title">Bank Details</h4>
                                <hr>

                                <div class="form-group">                       
                                <!-- <div class="col-sm-6">
                                        <label for="bank-name"><span style="color:red">*</span>Bank Name</label>
                                        <input  type="text" class="form-control" id="bank-name" placeholder="" name="bank-name" value="<?php echo !empty($franchise_data["bank_name"]) ? $franchise_data["bank_name"] : ''; ?>" >
                                    </div>  --> 
                                    
                                    <div class="col-sm-6">
                                        <label for="bank-acc-holders-name"><span style="color:red">*</span>Bank Account Holder's Name</label>
                                        <input  type="text" class="form-control" id="bank-acc-holders-name" placeholder="" name="bank-acc-holders-name" value="<?php echo !empty($franchise_data["bank_acc_holder_name"]) ? $franchise_data["bank_acc_holder_name"] : ''; ?>" >
                                    </div>  

                                </div>

                            
                                <div class="form-group">                       
                                    <div class="col-sm-6">
                                        <label for="bank-details-acc-num"><span style="color:red">*</span>Account Number</label>
                                        <input  type="text" class="form-control" id="bank-details-acc-num" placeholder="" name="bank-details-acc-num" value="<?php echo !empty($franchise_data["bank_acc_num"]) ? $franchise_data["bank_acc_num"] : ''; ?>" >
                                    </div>  
                                    
                                    <!-- <div class="col-sm-6">
                                        <label for="bank-details-code"><span style="color:red">*</span>Bank Code</label>
                                        <input  type="text" class="form-control" id="bank-details-code" placeholder="" name="bank-details-code" value="<?php echo !empty($franchise_data["bank_code"]) ? $franchise_data["bank_code"] : ''; ?>" >
                                    </div>   -->

                                </div>

                                <div class="form-group">
                                    <div  class="col-sm-4">                
                                        <label for="ridetype">Select Bank</label>
                                        <select class="form-control" name="bank-details-code" id="bank-details-code">
                                            <?php
                                                foreach($banks_details as $key => $value){
                                                $selected =  $franchise_data["bank_code"] == $key ? "selected='selected'" : '';   
                                            ?>
                                            <option value="<?php echo $key ?>" <?php echo $selected;?>><?php echo $value ?></option> 
                                            <?php
                                                }
                                            ?>
                                        </select>                
                                    </div> 
                                </div>

                                <div class="form-group" id="other-bank-details" style="display:none;">                       
                                    <div class="col-sm-6">
                                        <label for="other-bank-name"><span style="color:red">*</span>Bank Name</label>
                                        <input  type="text" <?php echo $franchise_data['id'] == 1 ? "" : "required"; ?> class="form-control" id="other-bank-name" placeholder="" name="other-bank-name" value="<?php echo !empty($franchise_data['bank_name']) ? $franchise_data['bank_name'] : ''; ?>" >
                                    </div>  
                                

                                    <div class="col-sm-6">
                                        <label for="other-bank-code"><span style="color:red">*</span>Bank Code</label>
                                        <input  type="text" <?php echo $franchise_data['id'] == 1 ? "" : "required"; ?> class="form-control" id="other-bank-code" placeholder="" name="other-bank-code" value="<?php echo !empty($franchise_data['bank_code']) ? $franchise_data['bank_code'] : ''; ?>" >
                                    </div>                                                              

                                </div>

                                <div class="form-group">
                                    
                                    <div class="col-sm-6">
                                        <label for="bank-details-swift">Swift / BIC</label>
                                        <input  type="text" class="form-control" id="bank-details-swift" placeholder="" name="bank-details-swift" value="<?php echo !empty($franchise_data['bank_swift_code']) ? $franchise_data['bank_swift_code'] : ''; ?>" >
                                    </div>

                                </div>

                                <br>
                                <br>
                                <h4 class="box-title">Franchise Commision</h4>
                                <hr>

                                    
                                <div class="form-group">                       
                                    <div class="col-sm-6">
                                        <label for="commission"><span style="color:red">*</span>Percentage commision for every successful trip by franchise driver</label>
                                        <input  type="number" type="number"  min="0.00" step="0.01" <?php echo $franchise_data['id'] == 1 ? "" : "required"; ?> class="form-control" id="commission" placeholder="" name="commission" value="<?php echo !empty($franchise_data["franchise_commision"]) ? $franchise_data["franchise_commision"] : ''; ?>" >
                                    </div>                                        

                                </div>

                                <br>

                            </div>                 
                                
                                
                            <hr />
                            <button type="submit" class="btn btn-primary btn-block" value="1" name="updatefranch" >Update Franchise</button> 
                        </form>
        
        
        
                                    
                </div>
                <!-- /.box-body -->
            </div>

    </div> <!--/col-sm-8-->
</div>


<script>

    var selected_b_code = $('#bank-details-code').find(':selected').val();
    var selected_b_name = $('#bank-details-code').find(':selected').text();
    if(selected_b_code === 'xxx'){
        $('#other-bank-details').fadeIn();
        $('#other-bank-name').val('');
        $('#other-bank-code').val('');
    }else{
        $('#other-bank-details').fadeOut();
        $('#other-bank-name').val(selected_bank_name);
        $('#other-bank-name').prop('disabled', true);
        $('#other-bank-code').val(selected_bank_code); 
        $('#other-bank-code').prop('disabled', true);            
    }
        

    $('#bank-details-code').on('change', function(){
        var selected_bank_code = $('#bank-details-code').find(':selected').val();
        var selected_bank_name = $('#bank-details-code').find(':selected').text();

        if(selected_bank_code === 'xxx'){
            $('#other-bank-details').fadeIn();
            $('#other-bank-name').val('');
            $('#other-bank-code').val('');
        }else{
            $('#other-bank-details').fadeOut();
            $('#other-bank-name').val(selected_bank_name);
            $('#other-bank-name').prop('disabled', true);
            $('#other-bank-code').val(selected_bank_code); 
            $('#other-bank-code').prop('disabled', true);           
        }
    })



    $('#gen-pass').click(function(){

        var password = generatePass(10);
        $('#franch-pwd').val(password);

    })


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


</script>














