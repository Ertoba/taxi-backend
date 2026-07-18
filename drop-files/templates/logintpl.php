<main id="main" style="margin-top:80px;">
    <div style="clear:both;"></div>
    <div style="background-color:#f0f8ff;border-bottom:thin solid #ccc;min-height:100vh;">
        <div class="container">
            <div class="row">
           
                                    
                <div class="col-sm-12 d-flex justify-content-center align-items-center" data-aos-delay="200" data-aos="fade-up">
                    <div style="width:500px;margin: 20px 0 30px;min-height: 500px;padding: 15px;border: thin solid #d8d8d8;background-color: white;border-radius: 5px;">

                                            
                        <form style="text-align: center;margin: 100px 0;" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post">
                            <input type="hidden" name="timezone" id="timezone" value="">
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
                            <br>
                            <img src="img/apple-touch-icon.png" style="width:100px;display:block;margin-left:auto;margin:20px auto;" />
                            
                            <br>
                            <br>
                            
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text ri-user-line"></span>
                                    </div>
                                    <select required class="form-control custom-select" id="acc-type-sel">
                                        <option value="0">Select account type</option>
                                        <option value="3">Admin</option>
                                        <option value="2">Dispatcher</option>
                                        <option value="5">Biller</option>
                                        <option value="4">Franchise</option>
                                    </select>
                                </div>                    
                            </div>
                            
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text ri-mail-line"></span>
                                    </div>
                                    <input type="text" required class="form-control" placeholder="Email" name="email" aria-label="Email" aria-describedby="basic-addon1" value="">
                                </div>                    
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text ri-key-line"></span>
                                    </div>
                                    <input type="password" required class="form-control" placeholder="Password" name="password" aria-label="Password" aria-describedby="basic-addon1" value="">
                                </div>                    
                            </div>
                                
                            
                            <div style="text-align:left;" ><input type="submit" name="login" value="Login" class="btn btn-lg btn-primary"></div>
                            <input type="hidden" id="type" name="type" value="3">
                        </form>
                        
                    </div>
                </div>




            </div>

        </div>
    </div>
</div>
<script src="/js/jstz.min.js"></script>
<script>
    var tz = jstz.determine(); // Determines the time zone of the browser client
    $('#timezone').val(tz.name());
    
    
    $('#acc-type-sel').on('change', function(){
        var acc_type = $(this).val();
        $('#type').val(acc_type);
    })

</script>
