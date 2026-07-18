<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">
        
        
                <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                        <div class="form-group">
                            
                            <div class="col-sm-6">
                                <label for="rider-app-playstore-url"><span style="color:red">*</span>Rider Android App Playstore URL</label>
                                <p>Playstore URL required to redirect user to App page for rating and update.</p>
                                <input  type="text" required class="form-control" id="rider-app-playstore-url" placeholder="" name="rider-app-playstore-url" value="<?php echo isset($settings_data3['rider-app-playstore-url']) ? $settings_data3['rider-app-playstore-url'] : ''; ?>" >
                            </div>  

                            <div class="col-sm-6">
                                <label for="rider-app-appstore-url"><span style="color:red">*</span>Rider IOS App Appstore URL</label>
                                <p>App store URL required to redirect user to App page for rating and update.</p>
                                <input type="text" required class="form-control" id="rider-app-appstore-url" placeholder="" name="rider-app-appstore-url" value="<?php echo isset($settings_data3['rider-app-appstore-url']) ? $settings_data3['rider-app-appstore-url'] : ''; ?>" >
                            </div>
                            
                        </div>
                        <hr>
                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-app-playstore-url"><span style="color:red">*</span>Driver Android App Playstore URL</label>
                                <p>Playstore URL required to redirect user to App page for rating and update.</p>
                                <input  type="text" required class="form-control" id="driver-app-playstore-url" placeholder="" name="driver-app-playstore-url" value="<?php echo isset($settings_data3['driver-app-playstore-url']) ? $settings_data3['driver-app-playstore-url'] : ''; ?>" >
                            </div>  

                            <div class="col-sm-6">
                                <label for="driver-app-appstore-url"><span style="color:red">*</span>Driver IOS App Appstore URL</label>
                                <p>App store URL required to redirect user to App page for rating and update.</p>
                                <input type="text" required class="form-control" id="driver-app-appstore-url" placeholder="" name="driver-app-appstore-url" value="<?php echo isset($settings_data3['driver-app-appstore-url']) ? $settings_data3['driver-app-appstore-url'] : ''; ?>" >
                            </div>
                            
                        </div>

                        <hr>

                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="rider-android-app-version"><span style="color:red">*</span>Rider Android App Version</label>
                                <p>Version of the Rider Android App. Must be the same with the App else an Update prompt will be triggered on App</p>
                                <input type="text" required class="form-control" id="rider-android-app-version" placeholder="" name="rider-android-app-version" value="<?php echo isset($settings_data3['rider-android-app-version']) ? $settings_data3['rider-android-app-version'] : ''; ?>" >
                                <br>
                                <input id="force-update-rider-android" name="force-update-rider-android" type="checkbox" <?php echo isset($settings_data3['force-update-rider-android']) ? "checked" : ''; ?> >
                                <label for="force-update-rider-android">Force android customers to update to this version</label> 
                                
                            </div>  

                            <div class="col-sm-6">
                                <label for="rider-ios-app-version"><span style="color:red">*</span>Rider IOS App Version</label>
                                <p>Version of the Rider IOS App. Must be the same with the App else an Update prompt will be triggered on App</p>
                                <input type="text" required class="form-control" id="rider-ios-app-version" placeholder="" name="rider-ios-app-version" value="<?php echo isset($settings_data3['rider-ios-app-version']) ? $settings_data3['rider-ios-app-version'] : ''; ?>" >
                                <br>
                                <input id="force-update-rider-ios" name="force-update-rider-ios" type="checkbox" <?php echo isset($settings_data3['force-update-rider-ios']) ? "checked" : ''; ?> >
                                <label for="force-update-rider-ios">Force iOS customers to update to this version</label>
                            </div>  
                            
                        </div>
                        <hr>
                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="driver-android-app-version"><span style="color:red">*</span>Driver Android App Version</label>
                                <p>Version of the Driver Android App. Must be the same with the App else an Update prompt will be triggered on App</p>
                                <input type="text" required class="form-control" id="driver-android-app-version" placeholder="" name="driver-android-app-version" value="<?php echo isset($settings_data3['driver-android-app-version']) ? $settings_data3['driver-android-app-version'] : ''; ?>" >
                                <br>
                                <input id="force-update-driver-android" name="force-update-driver-android" type="checkbox" <?php echo isset($settings_data3['force-update-driver-android']) ? "checked" : ''; ?> >
                                <label for="force-update-driver-android">Force android drivers to update to this version</label>
                            </div>  

                            <div class="col-sm-6">
                                <label for="driver-ios-app-version"><span style="color:red">*</span>Driver IOS App Version</label>
                                <p>Version of the Driver IOS App. Must be the same with the App else an Update prompt will be triggered on App</p>
                                <input type="text" required class="form-control" id="driver-ios-app-version" placeholder="" name="driver-ios-app-version" value="<?php echo isset($settings_data3['driver-ios-app-version']) ? $settings_data3['driver-ios-app-version'] : ''; ?>" >
                                <br>
                                <input id="force-update-driver-ios" name="force-update-driver-ios" type="checkbox" <?php echo isset($settings_data3['force-update-driver-ios']) ? "checked" : ''; ?> >
                                <label for="force-update-driver-ios">Force iOS drivers to update to this version</label>
                            </div>  
                            
                        </div>

                        
                        <br>
                        <hr>
                        
                        

                        <div class="form-group">
                        
                            <div class="col-sm-6" id="r-c-r-wrapper">
                                <label>Customer Cancellation Reasons</label>
                                <br>
                                <button id="r-a-c-r-btn" type="button" class="btn btn-sm btn-success">Add reason</button> 
                                <br>
                                <br>
                                
                                <?php
                                    if(isset($settings_data3['rider-cancel-reason']) && is_array($settings_data3['rider-cancel-reason'])){
                                        $count = 0;
                                        $rider_reason = "";
                                        foreach($settings_data3['rider-cancel-reason'] as $rider_cancel_reason){
                                            if(empty($rider_cancel_reason))continue;
                                            $rider_cancel_reason = htmlentities($rider_cancel_reason);
                                            $count++;
                                            $rider_reason .= "<div class='r-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>{$count}</p><input type='text' class='form-control' placeholder='' name='rider-cancel-reason[]' value='{$rider_cancel_reason}'><button class='btn btn-xs btn-danger r-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasonrider($(this))'>Remove</button></div>";
                                        }
                                        if($count){
                                            echo $rider_reason;
                                        }else{
                                            
                                            echo "<div class='r-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>1</p><input type='text' class='form-control' placeholder='' name='rider-cancel-reason[]' value=''><button class='btn btn-xs btn-danger r-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasonrider($(this))'>Remove</button></div>";
                                        }
                                        
                                    }else{
                                        echo "<div class='r-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>1</p><input type='text' class='form-control' placeholder='' name='rider-cancel-reason[]' value=''><button class='btn btn-xs btn-danger r-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasonrider($(this))'>Remove</button></div>";
                                    }
                                ?>
                                                              
                                
                                
                                
                            </div>  
                            

                            <div class="col-sm-6" id="d-c-r-wrapper">
                                <label>Driver Cancellation Reasons</label>
                                <br>
                                <button id="d-a-c-r-btn" type="button" class="btn btn-sm btn-success">Add reason</button> 
                                <br>
                                <br>  
                                
                                <?php
                                    if(isset($settings_data3['driver-cancel-reason']) && is_array($settings_data3['driver-cancel-reason'])){
                                        $count = 0;
                                        $driver_reason = "";
                                        foreach($settings_data3['driver-cancel-reason'] as $driver_cancel_reason){
                                            if(empty($driver_cancel_reason))continue;
                                            $driver_cancel_reason = htmlentities($driver_cancel_reason);
                                            $count++;
                                            $driver_reason .= "<div class='d-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>{$count}</p> <input type='text' class='form-control' placeholder='' name='driver-cancel-reason[]' value='{$driver_cancel_reason}' ><button class='btn btn-xs btn-danger d-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasondriver($(this))'>Remove</button></div>";
                                        }

                                        if($count){
                                            echo $driver_reason;
                                        }else{
                                            echo "<div class='d-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>1</p> <input type='text' class='form-control' placeholder='' name='driver-cancel-reason[]' value='' ><button class='btn btn-xs btn-danger d-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasondriver($(this))'>Remove</button></div>";
                                        }
                                        
                                    }else{
                                        echo "<div class='d-c-r-c' style='display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;'><p style='margin:0 10px 0 0;'>1</p> <input type='text' class='form-control' placeholder='' name='driver-cancel-reason[]' value='' ><button class='btn btn-xs btn-danger d-r-c-r-btn' type='button' style='margin-left:10px;' onclick='remcancelreasondriver($(this))'>Remove</button></div>";
                                    }
                                ?>
                                
                                 
                            </div>  
                            
                        </div>

                        <br>
                        <br>



                    <button type="submit" class="btn btn-primary btn-block" value="1" name="savesettings3" >Save</button> 
                </form>



                            
        </div>
        <!-- /.box-body -->
    </div>

    <script>

        function remcancelreasonrider(el){
            let el_count = $('.r-c-r-c').length;
            if(el_count == 1)return;
            el.parent('.r-c-r-c').fadeOut(500,function(){
                $(this).remove();
                el_count--;

                //refresh count numbers
                let count = 0;
                $('.r-c-r-c p').each(function(){

                    if(count < el_count){
                        count++;
                        $(this).text(count);
                    }
                    

                })
            });
        }



        $('#r-a-c-r-btn').on('click', function(){
            let el_count = $('.r-c-r-c').length;
            if(el_count > 10)return;
            let c_r_el = `<div class="r-c-r-c" style="display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;"><p style="margin:0 10px 0 0;">1</p><input type="text" class="form-control" placeholder="" name="rider-cancel-reason[]" value=""><button class="btn btn-xs btn-danger r-r-c-r-btn" type="button" style="margin-left:10px;" onclick="remcancelreasonrider($(this))">Remove</button></div>`;
            $('#r-c-r-wrapper').append(c_r_el);
            el_count++;

            //refresh count numbers
            let count = 0;
            $('.r-c-r-c p').each(function(){
                
                if(count < el_count){
                    count++;
                    $(this).text(count);
                }
                

            })
            
            
        })



        function remcancelreasondriver(el){
            let el_count = $('.d-c-r-c').length;
            if(el_count == 1)return;
            el.parent('.d-c-r-c').fadeOut(500,function(){
                $(this).remove();
                el_count--;

                //refresh count numbers
                let count = 0;
                $('.d-c-r-c p').each(function(){

                    if(count < el_count){
                        count++;
                        $(this).text(count);
                    }
                    

                })
            });
        }



        $('#d-a-c-r-btn').on('click', function(){
            let el_count = $('.d-c-r-c').length;
            if(el_count > 10)return;
            let c_r_el = `<div class="d-c-r-c" style="display:flex;flex-wrap:nowrap;align-items:center;margin-bottom:5px;"><p style="margin:0 10px 0 0;">1</p><input type="text" class="form-control" placeholder="" name="driver-cancel-reason[]" value=""><button class="btn btn-xs btn-danger d-r-c-r-btn" type="button" style="margin-left:10px;" onclick="remcancelreasondriver($(this))">Remove</button></div>`;
            $('#d-c-r-wrapper').append(c_r_el);
            el_count++;

            //refresh count numbers
            let count = 0;
            $('.d-c-r-c p').each(function(){
                
                if(count < el_count){
                    count++;
                    $(this).text(count);
                }
                

            })
            
            
        })


    </script>