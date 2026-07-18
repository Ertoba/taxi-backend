<div class="container">
    <div class="row">
        <br >
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
         </div>

</div>
        <div class="container">
				<div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                        <div class="spacer-1"></div>
                        <div class="spacer-1"></div>
                            <h1>RIDE SUMMARY</h1>
                            
                            <table class="table">
                                <tr><td><h4 style="margin-top:20px;">Pick-up Location</h4></td><td><h4 style="margin-top:20px;"><?php echo $_SESSION['booking'][$bookride_token]['p_addr'];?></h4></td></tr>
                                <tr><td><h4 style="margin-top:20px;">Drop-off Location</h4></td><td><h4 style="margin-top:20px;"><?php echo $_SESSION['booking'][$bookride_token]['d_addr'];?></h4></td></tr>
                                <tr><td><h4 style="margin-top:20px;">Distance / Duration</h4></td><td><h4 style="margin-top:20px;"><?php echo $_SESSION['booking'][$bookride_token]['distance'] . " | " . $_SESSION['booking'][$bookride_token]['duration'] ;?></h4></td></tr>
                                <tr><td><h4 style="margin-top:20px;">Price</h4></td><td><h4 style="margin-top:20px;"><?php echo "₦" . $_SESSION['booking'][$bookride_token]['cost']; ?></h4></td></tr>
                            </table>    
                        
                        </div>
                    </div>
                </div>

                <form  enctype="multipart/form-data" class="form-horizontal" action="payride.php" method="post" >
                    <input  style="display:none;" type="text" hidden="hidden" name="b-token" id="b-token" value="<?php echo $_SESSION['booking'][$bookride_token]['token'];?>" > 
                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>
                    
                    <div class="form-group">    
                        <div class="col-sm-12">
                            <h1>Scheduled Date / Time</h1>
                            
                            <table class="table">
                                <tr><td><h4 style="margin-top:20px;">Pick-up Date</h4></td><td><h4 style="margin-top:20px;"><input  type="text" name="date" id="datetimepicker" value="<?php echo date('Y-m-d'); ?>" > </h4></td></tr>
                                <tr><td><h4 style="margin-top:20px;">Pick-up Time</h4></td><td><h4 style="margin-top:20px;">
                                    <select class="form-control" name="time">
                                        <?php 
                                            $time_data = array('1:00 AM','2:00 AM','3:00 AM','4:00 AM','5:00 AM','6:00 AM','7:00 AM','8:00 AM','9:00 AM','10:00 AM','11:00 AM','12:00 AM','1:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM','9:00 PM','10:00 PM','11:00 PM','12:00 PM');
                                            $current_time = date('g:00 A');
                                            $option_selected = '';
                                            foreach($time_data as $timedata){
                                                $option_selected =  $timedata == $current_time ? "selected" : '';
                                        ?>
                                        <option <?php echo $option_selected; ?> value="<?php echo $timedata;?>" ><?php echo $timedata;?></option> 
                                        <?php
                                            }
                                        ?>
                                        
                                    </select>     
                                </h4></td></tr>
                                
                            </table>    
                        
                        </div>
                    </div>

                    <div class="spacer-1"></div>
                    <div class="spacer-1"></div>

                    <div class="form-group">    
                        <div class="col-sm-12">
                            <h1>Payment Option</h1>
                            
                            <table class="table">
                                <tr><td><h4 style="margin-top:20px;"><input checked type="radio" name="payoption" id="wallet" value="wallet"> Wallet (₦1200.00)</h4></td><td><h4 style="margin-top:20px;"><input type="radio" name="payoption" id="card" value="card"> Online Pay (Debit Card)</h4></td><td><h4 style="margin-top:20px;"><input type="radio" name="payoption" id="pos" value="pos"> POS</h4></td></tr>
                                
                            </table>    
                        
                        </div>
                    </div>

                    <br>
                    <hr />
                    
                    <input type="submit" name="bookridepay" value="Proceed" class="btn btn-lg btn-yellow aligncenter">
                    <br>

                </form>
                    
        </div>


   