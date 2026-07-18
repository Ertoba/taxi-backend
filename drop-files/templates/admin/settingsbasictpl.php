<?php 
    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦"; 
?>
<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">
        
        
                <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                                                
                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="max-driver-distance"><span style="color:red">*</span>Maximum Driver Distance</label>
                                <p>Set the maximum distance in Kilometers radius within which a driver can be assigned a ride by the system.</p>
                                <input  type="number" required min='0.1' step='0.1' class="form-control" id="max-driver-distance" placeholder="" name="max-driver-distance" value="<?php echo isset($settings_data['max-driver-distance']) ? $settings_data['max-driver-distance'] : ''; ?>" >
                            </div>  
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-location-update-interval"><span style="color:red">*</span>Driver Location Update Interval</label>
                                <p>Set the interval for location updates on the driver App. (Time in Seconds)</p>
                                <input  type="number" required min='5' step='1' class="form-control" id="driver-location-update-interval" placeholder="" name="driver-location-update-interval" value="<?php echo isset($settings_data['driver-location-update-interval']) ? $settings_data['driver-location-update-interval'] : ''; ?>" >
                            </div>  
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-inactivity-timeout"><span style="color:red">*</span>Driver Inactivity Timeout</label>
                                <p>Set the maximum amount of time the driver app should be inactive (without location updates) to stop receiving ride requests. (Time in Minutes)</p>
                                <input  type="number" required min='1' step='1' class="form-control" id="driver-inactivity-timeout" placeholder="" name="driver-inactivity-timeout" value="<?php echo isset($settings_data['driver-inactivity-timeout']) ? $settings_data['driver-inactivity-timeout'] : ''; ?>" >
                            </div>  
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="scheduled-ride"><span style="color:red">*</span>Scheduled Rides</label>
                                <p>Enable / Disable ability of riders to schedule rides ahead of time.</p>
                                <select id="scheduled-ride" name="scheduled-ride">
                                    <option <?php echo isset($settings_data['scheduled-ride']) &&  ($settings_data['scheduled-ride'] == 1) ? 'selected' : ''; ?> value="1">Enabled</option>
                                    <option <?php echo isset($settings_data['scheduled-ride']) &&  ($settings_data['scheduled-ride'] == 0) ? 'selected' : ''; ?> value="0">Disabled</option>
                                </select>
                            </div>  
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="allow-drv-change-city"><span style="color:red">*</span>Drivers City Change</label>
                                <p>Enable / Disable ability of drivers to change their city from the app.</p>
                                <select id="allow-drv-change-city" name="allow-drv-change-city">
                                    <option <?php echo isset($settings_data['allow-drv-change-city']) &&  ($settings_data['allow-drv-change-city'] == 1) ? 'selected' : ''; ?> value="1">Enabled</option>
                                    <option <?php echo isset($settings_data['allow-drv-change-city']) &&  ($settings_data['allow-drv-change-city'] == 0) ? 'selected' : ''; ?> value="0">Disabled</option>
                                </select>
                            </div>  
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="call-center"><span style="color:red">*</span>Call Center Number</label>
                                <p>Enter your call-center phone number which customers can call for help from their App.</p>
                                <input  class="form-control" type="text" required id="call-center" placeholder="" name="call-center" value="<?php echo isset($settings_data['call-center']) ? $settings_data['call-center'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-tip-amount-preset"><span style="color:red">*</span>Driver Tip Amount Presets</label>
                                <p>Enter preset amounts riders can tip drivers after a trip; Leave blank to disable driver tip. Separate each amount with the pipe "|" character. Example 0|5|10|20</p>
                                <input  class="form-control" type="text" id="driver-tip-amount-preset" placeholder="" name="driver-tip-amount-preset" value="<?php echo isset($settings_data['driver-tip-amount-preset']) ? $settings_data['driver-tip-amount-preset'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="wallet-topup-amount-preset"><span style="color:red">*</span>Wallet Top-up Amount Presets</label>
                                <p>Enter preset amounts riders can select to top-up their wallet; Leave blank to hide presets. Separate each amount with the pipe "|" character. Example 50|100|200|500</p>
                                <input  class="form-control" type="text" id="wallet-topup-amount-preset" placeholder="" name="wallet-topup-amount-preset" value="<?php echo isset($settings_data['wallet-topup-amount-preset']) ? $settings_data['wallet-topup-amount-preset'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="allowed-countries-list">Allowed Countries</label>
                                <p>Enter 2-letter country codes of countries available to customers and drivers to login or register from. Leave blank to allow all countries. Separate each country with the pipe "|" character. Example us|ng|de|fr</p>
                                <input  class="form-control" type="text" id="allowed-countries-list" placeholder="" name="allowed-countries-list" value="<?php echo isset($settings_data['allowed-countries-list']) ? $settings_data['allowed-countries-list'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-12">
                                <label for="default-banks-and-codes"><span style="color:red">*</span>Default Banks and codes </label>
                                <p>Enter default banks with their unique identification codes which drivers can select during registration to be paid to, as well as used across the platform. Group each bank name and bank code and separate with this "->" string, then separate each bank name->bank code group with the pipe "|" character.  Example Bank1_name->Bank1_code|Bank2_name->Bank2_code|Bank3_name->Bank3_code</p>
                                <input  class="form-control" type="text" id="default-banks-and-codes" placeholder="" name="default-banks-and-codes" value="<?php echo isset($settings_data['default-banks-and-codes']) ? $settings_data['default-banks-and-codes'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="call-center">OTP</label>
                                <p>Disable or set if OTP will be requested on driver App before or after a trip. </p>
                                <select id="ride-otp" name="ride-otp">
                                    <option <?php echo isset($settings_data['ride-otp']) &&  ($settings_data['ride-otp'] == 0) ? 'selected' : ''; ?> value="0">OTP Disabled</option>
                                    <option <?php echo isset($settings_data['ride-otp']) &&  ($settings_data['ride-otp'] == 1) ? 'selected' : ''; ?> value="1">Request OTP before trip starts</option>
                                    <option <?php echo isset($settings_data['ride-otp']) &&  ($settings_data['ride-otp'] == 2) ? 'selected' : ''; ?> value="2">Request OTP when trip ends</option>
                                </select>
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="call-center">Payment Types</label>
                                <p>Set payment types available to riders </p>
                                <select id="payment-type" name="payment-type">
                                    <option <?php echo isset($settings_data['payment-type']) &&  ($settings_data['payment-type'] == 0) ? 'selected' : ''; ?> value="0">Cash Only</option>
                                    <option <?php echo isset($settings_data['payment-type']) &&  ($settings_data['payment-type'] == 1) ? 'selected' : ''; ?> value="1">Wallet Only</option>
                                    <option <?php echo isset($settings_data['payment-type']) &&  ($settings_data['payment-type'] == 2) ? 'selected' : ''; ?> value="2">Cash and Wallet</option>
                                </select>
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="night-start"><span style="color:red">*</span>Night Start Hour</label>
                                <p>Enter the hour in which night tariff starts (24Hr).</p>
                                <input  class="form-control" type="number" step = '1' max = '23' min='0'  required id="night-start" placeholder="" name="night-start" value="<?php echo isset($settings_data['night-start']) ? $settings_data['night-start'] : ''; ?>" >
                            </div>


                            <div class="col-sm-6">
                                <label for="night-end"><span style="color:red">*</span>Night End Hour</label>
                                <p>Enter the hour in which night tariff ends (24Hr).</p>
                                <input  class="form-control" type="number" step = '1' max = '23' min='0' required id="night-end" placeholder="" name="night-end" value="<?php echo isset($settings_data['night-end']) ? $settings_data['night-end'] : ''; ?>" >
                            </div> 
                    
                        </div>

                        <hr>
                    
                                                
                    <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="min-book-int"><span style="color:red">*</span>Minimum Booking Interval</label>
                                <p>Set the minimum time (seconds) before customer is allowed to create new booking after his last pending or onride booking.</p>
                                <input  type="number" min='1' step='1' class="form-control" id="min-book-int" placeholder="" name="min-book-int" value="<?php echo isset($settings_data['min-book-int']) ? $settings_data['min-book-int'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="max-pend-book"><span style="color:red">*</span>Maximum Pending Bookings</label>
                                <p>Set the maximum number of pending bookings a customer can have.</p>
                                <input  type="number" min='1' step='1' class="form-control" id="max-pend-book" placeholder="" name="max-pend-book" value="<?php echo isset($settings_data['max-pend-book']) ? $settings_data['max-pend-book'] : ''; ?>" >
                            </div>  
                        
                        </div>


                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-allocate-accept-duration"><span style="color:red">*</span>Driver Ride Allocation Accept Time</label>
                                <p>Set the maximum time in seconds required for a driver to accept a ride once allocated.</p>
                                <input  type="number" min='1' step='1' class="form-control" id="driver-allocate-accept-duration" placeholder="" name="driver-allocate-accept-duration" value="<?php echo isset($settings_data['driver-allocate-accept-duration']) ? $settings_data['driver-allocate-accept-duration'] : ''; ?>" >
                            </div>  
                        
                        </div>


                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-btb-trip-distance"><span style="color:red">*</span>Back-to-back Trip Distance</label>
                                <p>Set the maximum distance in kilometer (KM) a driver must be from the destination of his current trip to be eligible to receive another trip concurrently. Enter zero (0) to disable back-to-back trips</p>
                                <input  required type="number" min='0' step='1' class="form-control" id="driver-btb-trip-distance" placeholder="" name="driver-btb-trip-distance" value="<?php echo isset($settings_data['driver-btb-trip-distance']) ? $settings_data['driver-btb-trip-distance'] : ''; ?>" >
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="call-center">Driver Registration Activation</label>
                                <p>Set how you want to activate newly registered drivers on the service</p>
                                <select id="driver-reg-act-mode" name="driver-reg-act-mode">
                                    <option <?php echo isset($settings_data['driver-reg-act-mode']) &&  ($settings_data['driver-reg-act-mode'] == 1) ? 'selected' : ''; ?> value="1">Activate driver instantly after registration</option>
                                    <option <?php echo isset($settings_data['driver-reg-act-mode']) &&  ($settings_data['driver-reg-act-mode'] == 2) ? 'selected' : ''; ?> value="2">Admin manually activates driver after verifying registration information</option>                                    
                                </select>
                            </div>  
                        
                        </div>

                        <hr>


                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="vehicle-sel-dlg-disp">Vehicle selection menu display</label>
                                <p>Set the vehicle selection dialog display type on the rider app</p>
                                <select id="vehicle-sel-dlg-disp" name="vehicle-sel-dlg-disp">
                                    <option <?php echo isset($settings_data['vehicle-sel-dlg-disp']) &&  ($settings_data['vehicle-sel-dlg-disp'] == 1) ? 'selected' : ''; ?> value="1">Slider</option>
                                    <option <?php echo isset($settings_data['vehicle-sel-dlg-disp']) &&  ($settings_data['vehicle-sel-dlg-disp'] == 2) ? 'selected' : ''; ?> value="2">List</option>                                    
                                </select>
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="trip-fare-rounding">Trip fare rounding</label>
                                <p>Set how trip fares should be rounded on the apps</p>
                                <select id="trip-fare-rounding" name="trip-fare-rounding">
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 1) ? 'selected' : ''; ?> value="1">No rounding (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>124.56)</option>
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 2) ? 'selected' : ''; ?> value="2">Nearest whole number (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>125)</option>
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 3) ? 'selected' : ''; ?> value="3">Nearest 10 (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>130)</option>
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 4) ? 'selected' : ''; ?> value="4">Nearest 50 (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>150)</option>
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 5) ? 'selected' : ''; ?> value="5">Nearest 100 (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>200)</option>
                                    <option <?php echo isset($settings_data['trip-fare-rounding']) &&  ($settings_data['trip-fare-rounding'] == 6) ? 'selected' : ''; ?> value="6">Nearest 250 (<?php echo $default_currency_symbol; ?>124.56 -------- <?php echo $default_currency_symbol; ?>250)</option>
                                </select>
                            </div>  
                        
                        </div>

                        <hr>

                        <!-- <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="booking-cancel-penalty"><span style="color:red">*</span>Booking Cancelation Penalty</label>
                                <p>Enable / Disable booking cancelation penalty for drivers and riders canceling bookings</p>
                                <select id="booking-cancel-penalty" name="booking-cancel-penalty">
                                    <option <?php echo isset($settings_data['booking-cancel-penalty']) &&  ($settings_data['booking-cancel-penalty'] == 1) ? 'selected' : ''; ?> value="1">Enabled</option>
                                    <option <?php echo isset($settings_data['booking-cancel-penalty']) &&  ($settings_data['booking-cancel-penalty'] == 0) ? 'selected' : ''; ?> value="0">Disabled</option>
                                </select>
                            </div>  
                            
                        </div>

                        
                        <div id="booking-cancel-penalty-options" style="display:none;">
                            
                            <hr>
                            <div class="form-group">
                            
                                <div class="col-sm-6">
                                    <label for="booking-cancel-frequency"><span style="color:red">*</span> Cancelation frequency</label>
                                    <p>Enter number of consecutive times rider or driver cancels a trip before getting banned</p>
                                    <input  class="form-control" type="number" step = '1' min='1'  required id="booking-cancel-frequency" placeholder="" name="booking-cancel-frequency" value="<?php echo isset($settings_data['booking-cancel-frequency']) ? $settings_data['booking-cancel-frequency'] : ''; ?>" >
                                </div>

                                <div class="col-sm-6">
                                    <label for="booking-cancel-ban-period"><span style="color:red">*</span> Ban duration</label>
                                    <p>Enter number of minutes to ban rider / driver</p>
                                    <input  class="form-control" type="number" step = '1' min='1'  required id="booking-cancel-ban-period" placeholder="" name="booking-cancel-ban-period" value="<?php echo isset($settings_data['booking-cancel-ban-period']) ? $settings_data['booking-cancel-ban-period'] : ''; ?>" >
                                </div>

                            </div>
                            
                        </div>

                        <hr> -->


                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-default-commission"><span style="color:red">*</span>Driver Default Commission</label>
                                <p>Set the default percentge commission for drivers on every succesful booking.</p>
                                <input  type="number" step='1' max="100" class="form-control" id="driver-default-commission" placeholder="" name="driver-default-commission" value="<?php echo isset($settings_data['driver-default-commission']) ? $settings_data['driver-default-commission'] : ''; ?>" >
                                <br>
                                <label for="update-all-drivers">Update all drivers commisions</label>
                                <input  type="checkbox" id="update-all-drivers" name="update-all-drivers">
                                <div>
                                    <select style="display:none;" id="update-drivers-commission-scope" name="update-drivers-commission-scope">
                                        <option value="0">All cities</option>
                                        <?php
                                            foreach($inter_city_routes as $intercityroute){
                                                echo "<option value='{$intercityroute['id']}'>{$intercityroute['r_title']}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>  
                        
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="rider-minimum-wallet-balance"><span style="color:red">*</span>Rider minimum wallet balance (<?php echo $default_currency_symbol; ?>)</label>
                                <p>Set the minimum amount a customer can have in his wallet. Below this amount he ceases to book trips except the wallet is topped up. Enter zero or negative value.</p>
                                <input  class="form-control" type="number" required id="rider-minimum-wallet-balance" placeholder="" name="rider-minimum-wallet-balance" value="<?php echo !empty($settings_data['rider-minimum-wallet-balance']) ? $settings_data['rider-minimum-wallet-balance'] : '0'; ?>" >
                            </div> 
                        
                        </div>

                        <hr>



                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-minimum-wallet-balance"><span style="color:red">*</span>Driver minimum wallet balance (<?php echo $default_currency_symbol; ?>)</label>
                                <p>Set the minimum amount a driver can have in his wallet. Below this amount he ceases to receive ride requests except the wallet is topped up</p>
                                <input  class="form-control" type="number" required id="driver-minimum-wallet-balance" placeholder="" name="driver-minimum-wallet-balance" value="<?php echo isset($settings_data['driver-minimum-wallet-balance']) ? $settings_data['driver-minimum-wallet-balance'] : ''; ?>" >
                            </div> 
                        
                        </div>

                        <hr>




                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-min-wallet-amount"><span style="color:red">*</span>Driver Minimum Withdrawal Balance Amount (<?php echo $default_currency_symbol; ?>)</label>
                                <p>Set the minimum amount of money a driver must have in his wallet to enable withdrawal.</p>
                                <input  class="form-control" type="number" required id="driver-min-wallet-amount" placeholder="" name="driver-min-wallet-amount" value="<?php echo isset($settings_data['driver-min-wallet-amount']) ? $settings_data['driver-min-wallet-amount'] : ''; ?>" >
                            </div> 
                            
                            
                            <div class="col-sm-6">
                                <label for="franchise-min-wallet-amount"><span style="color:red">*</span>Franchise Minimum Withdrawal Balance Amount (<?php echo $default_currency_symbol; ?>)</label>
                                <p>Set the minimum amount of money a franchise must have in their wallet to enable withdrawal.</p>
                                <input  class="form-control" type="number" required id="franchise-min-wallet-amount" placeholder="" name="franchise-min-wallet-amount" value="<?php echo isset($settings_data['franchise-min-wallet-amount']) ? $settings_data['franchise-min-wallet-amount'] : ''; ?>" >
                            </div> 
                        
                        </div>

                                            




                        

                        


                                            
                        
                        
                        <hr />
                    <button type="submit" class="btn btn-primary btn-block" value="1" name="savesettings" >Save</button> 
                    </form>



                            
        </div>
        <!-- /.box-body -->
    </div>


    <script>

        var booking_cancel_penalty;

        var driver_customer_referral;

        booking_cancel_penalty = $('#booking-cancel-penalty').find(':selected').val();

        if(booking_cancel_penalty == 1){
            $('#booking-cancel-penalty-options').fadeIn();
            $('#booking-cancel-frequency').prop('required', true);
            $('#booking-cancel-ban-period').prop('required', true);
        }else{
            $('#booking-cancel-penalty-options').fadeOut();
            $('#booking-cancel-frequency').prop('required', false);
            $('#booking-cancel-ban-period').prop('required', false);
        }


        $('#booking-cancel-penalty').on('change', function(){
            booking_cancel_penalty = $(this).val();
            if(booking_cancel_penalty == 1){
                $('#booking-cancel-penalty-options').fadeIn();
                $('#booking-cancel-frequency').prop('required', true);
                $('#booking-cancel-ban-period').prop('required', true);
            }else{
                $('#booking-cancel-penalty-options').fadeOut();
                $('#booking-cancel-frequency').prop('required', false);
                $('#booking-cancel-ban-period').prop('required', false);
            }
        })


        $('#update-all-drivers').on('change', function(){
            if($(this).prop('checked')){
                $('#update-drivers-commission-scope').fadeIn();
            }else{
                $('#update-drivers-commission-scope').fadeOut();
            }
        })




        /* driver_customer_referral = $('#driver-customer-referral').find(':selected').val();

        if(driver_customer_referral == 1){
            $('#driver-customer-referral-option').fadeIn();
            $('#driver-customer-referral-incentive').prop('required', true);
        }else{
            $('#driver-customer-referral-option').fadeOut();
            $('#driver-customer-referral-incentive').prop('required', false);
        }


        $('#driver-customer-referral').on('change', function(){
            driver_customer_referral = $(this).val();
            if(driver_customer_referral == 1){
                $('#driver-customer-referral-option').fadeIn();
                $('#driver-customer-referral-incentive').prop('required', true);
            }else{
                $('#driver-customer-referral-option').fadeOut();
                $('#driver-customer-referral-incentive').prop('required', false);
            }
        }) */



    </script>