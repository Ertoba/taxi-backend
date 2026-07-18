<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">
        
        
                <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                        <div class="form-group">
                            
                            <div class="col-sm-6">

                                <label for="email-transport"><span style="color:red"></span>Email Transport</label>
                                <p>Set method of Email delivery. <b>System</b> uses PHP mail() function while <b>Custom SMTP</b> allows you configure an SMTP server</p>
                                <select id="email-transport" class="form-control" name="email-transport">
                                    <option <?php echo isset($settings_data4['email-transport']) &&  ($settings_data4['email-transport'] == '1') ? 'selected' : ''; ?> value="1">System</option>
                                    <option <?php echo isset($settings_data4['email-transport']) &&  ($settings_data4['email-transport'] == '2') ? 'selected' : ''; ?> value="2">Custom SMTP</option>
                                </select>

                            </div> 
                            
                            <div class="col-sm-12" id="custom-smtp-settings">
                                <br>
                                <hr>
                                <div class="form-group">

                                    <div class="col-sm-6">
                                        <br>
                                        <label for="smtp-hostname"><span style="color:red">*</span>SMTP Host</label>
                                        <p>Enter the SMTP Host name (example smtp.gmail.com)</p>
                                        <input  type="text" required class="form-control" id="smtp-hostname" placeholder="" name="smtp-hostname" value="<?php echo isset($settings_data4['smtp-hostname']) ? $settings_data4['smtp-hostname'] : ''; ?>" >
                                    </div>  

                                    <div class="col-sm-6">
                                        <br>
                                        <label for="smtp-portnum"><span style="color:red">*</span>SMTP Port Number</label>
                                        <p>Enter the SMTP Host port number</p>
                                        <input  type="number" required class="form-control" id="smtp-portnum" placeholder="" name="smtp-portnum" value="<?php echo isset($settings_data4['smtp-portnum']) ? $settings_data4['smtp-portnum'] : ''; ?>" >
                                    </div>

                                    <div class="col-sm-6">
                                        <br>
                                        <label for="smtp-username"><span style="color:red">*</span>SMTP Username</label>
                                        <p>Enter the email address you want to setup.</p>
                                        <input type="text" required class="form-control" id="smtp-username" placeholder="" name="smtp-username" value="<?php echo isset($settings_data4['smtp-username']) ? $settings_data4['smtp-username'] : ''; ?>" >
                                    </div>

                                    <div class="col-sm-6">
                                        <br>
                                        <label for="smtp-password"><span style="color:red">*</span>SMTP Password</label>
                                        <p>Enter the password for the email account.</p>
                                        <input type="password" required class="form-control" id="smtp-password" placeholder="" name="smtp-password" value="<?php echo isset($settings_data4['smtp-password']) ? (!empty(DEMO) ? "**********************" : $settings_data4['smtp-password'])  : ''; ?>" >
                                    </div>

                                    <div class="col-sm-12">
                                        <br>
                                        <button type="button" id="test-smtp-btn" class="btn btn-sm btn-success">Test SMTP settings</button>
                                    </div>

                                </div>   
                                
                                

                            </div>
                            
                        </div>
                        
                        <hr>



                        <div class="form-group">
                        
                            <div class="col-sm-6">
                                <label for="sender-email"><span style="color:red">*</span>Sender Email</label>
                                <p>Set the name displayed as the sender of emails sent</p>
                                <input  class="form-control" type="text" required id="sender-email" placeholder="" name="sender-email" value="<?php echo isset($settings_data4['sender-email']) ? $settings_data4['sender-email'] : ''; ?>" >
                            </div>  
                        
                        </div>


                        <hr>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <br>
                                <label for="new-riders-reg-email-subj"><span style="color:red">*</span>Riders Registration Email Subject</label>
                                <p>Enter the subject of the email sent to new riders after Registration</p>
                                <input  class="form-control" type="text" required id="new-riders-reg-email-subj" placeholder="" name="new-riders-reg-email-subj" value="<?php echo isset($settings_data4['new-riders-reg-email-subj']) ? $settings_data4['new-riders-reg-email-subj'] : ''; ?>" >
                            </div>  
                            
                            <div class="col-sm-12">
                                <br>
                                <label for="new-riders-reg-email-msg"><span style="color:red">*</span>Riders Registration Email Message</label>
                                <p style="margin-top: 15px;margin-bottom: 2px;">Enter the email message sent to new riders after Registration</p>
                                <textarea class="textformat" id="new-riders-reg-email-msg" placeholder="" name="new-riders-reg-email-msg"></textarea> 
                                <div id="new-riders-reg-email-msg-content" style="display:none;"><?php echo isset($settings_data4['new-riders-reg-email-msg']) ? $settings_data4['new-riders-reg-email-msg'] : ''; ?></div>
                            </div>  
                                
                        </div>


                        <hr>

                        <div class="form-group">

                            <div class="col-sm-6">
                                <br>
                                <label for="new-drivers-reg-email-subj"><span style="color:red">*</span>Drivers Registration Email Subject</label>
                                <p>Enter the subject of the email sent to new drivers after Registration</p>
                                <input  class="form-control" type="text" required id="new-drivers-reg-email-subj" placeholder="" name="new-drivers-reg-email-subj" value="<?php echo isset($settings_data4['new-drivers-reg-email-subj']) ? $settings_data4['new-drivers-reg-email-subj'] : ''; ?>" >
                            </div>
                            
                            <div class="col-sm-12">
                                <br>
                                <label for="new-drivers-reg-email-msg"><span style="color:red">*</span>Drivers Registration Email Message</label>
                                <p style="margin-top: 15px;margin-bottom: 2px;">Enter the email message sent to new drivers after Registration</p>
                                <textarea class="textformat" id="new-drivers-reg-email-msg" placeholder="" name="new-drivers-reg-email-msg"></textarea> 
                                <div id="new-drivers-reg-email-msg-content" style="display:none;"><?php echo isset($settings_data4['new-drivers-reg-email-msg']) ? $settings_data4['new-drivers-reg-email-msg'] : ''; ?></div>
                            </div>  
                                
                        </div>

                        <hr>

                        <div class="form-group">

                            <div class="col-sm-6">
                                <br>
                                <label for="password-reset-email-subj"><span style="color:red">*</span>Password Reset Email Subject</label>
                                <p>Enter the subject of the email sent for password reset</p>
                                <input  class="form-control" type="text" required id="password-reset-email-subj" placeholder="" name="password-reset-email-subj" value="<?php echo isset($settings_data4['password-reset-email-subj']) ? $settings_data4['password-reset-email-subj'] : ''; ?>" >
                            </div>
                            
                            <div class="col-sm-12">
                                <br>
                                <label for="password-reset-email-msg"><span style="color:red">*</span>Password Reset Email Message</label>
                                <p style="margin-top: 15px;margin-bottom: 2px;">Enter the email message sent for password reset</p>
                                <textarea class="textformat" id="password-reset-email-msg" placeholder="" name="password-reset-email-msg"></textarea> 
                                <div id="password-reset-email-msg-content" style="display:none;"><?php echo isset($settings_data4['password-reset-email-msg']) ? $settings_data4['password-reset-email-msg'] : ''; ?></div>
                            </div>  
                                
                        </div>

                        <hr>
                        

                    <button type="submit" class="btn btn-primary btn-block" value="1" name="savesettings4" >Save</button> 
                </form>



                            
        </div>
        <!-- /.box-body -->
    </div>

    <script>

        var email_content;
        var email_transport;

        email_transport = $('#email-transport').val();
        if(email_transport == 2){
            $('#custom-smtp-settings').show();
        }else{
            $('#custom-smtp-settings').hide();
        }

        $('#email-transport').on('change', function(e){
            email_transport = $(this).val();
            if(email_transport == 2){
                $('#custom-smtp-settings').fadeIn();
                $('#smtp-hostname').prop('required', true);
                $('#smtp-username').prop('required', true);
                $('#smtp-password').prop('required', true);
            }else{
                $('#custom-smtp-settings').fadeOut();
                $('#smtp-hostname').prop('required', false);
                $('#smtp-username').prop('required', false);
                $('#smtp-password').prop('required', false);
            }

        })

        $('#test-smtp-btn').on('click', function(){
            let hostname = $('#smtp-hostname').val();
            let username = $('#smtp-username').val();
            let password = $('#smtp-password').val();

            if(!hostname || !username || !password ){
                imgurl = '../img/info_.gif?a=' + Math.random();
                    
                swal({
                        title: '<h1>Error</h1>',
                        text: 'SMTP fields cannot be blank',
                        imageUrl:imgurl,
                        html:true
                });
                return;
            }

                swal({
                        title: "Test SMTP Settings",
                        text: "Enter an email address where test message will be sent",
                        type: "input",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Test",
                        cancelButtonText: "Cancel",
                        inputPlaceholder: "mytestemail@domain.com",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                        showLoaderOnConfirm: true

                    },function(inputValue){
                            if (inputValue === false) return false;

                            if (inputValue === "") {
                                //swal.showInputError("Please enter an email address");
                                return false
                            }

                            
                            //send message through AJAX
                            //swal.close();
                            //$('#busy').modal('show');
                            var post_data = {'action':'checkSMTP','hostname' : hostname, 'username':username , 'password':password , 'to' : inputValue};
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                timeout : 30000,
                                crossDomain:true,
                                xhrFields: {withCredentials: true},
                                data: post_data,
                                success: function (data, status)
                                {
                                    $('#busy').modal('hide');    
                                    try{
                                        var data_obj = JSON.parse(data);
                                    }catch(e){
                        
                                        imgurl = '../img/info_.gif?a=' + Math.random();
                
                                        swal({
                                                    title: '<h1>Error</h1>',
                                                    text: 'Failed to send SMTP test message!',
                                                    imageUrl:imgurl,
                                                    html:true
                                        });

                                    }
                        
                                    
                                    if(data_obj.hasOwnProperty('error')){
                                        imgurl = '../img/info_.gif?a=' + Math.random();
                                        
                                        swal({
                                                    title: '<h1>Error</h1>',
                                                    text: data_obj.message,
                                                    imageUrl:imgurl,
                                                    html:true
                                        });
                                    }
                                    
                                    
                                    if(data_obj.hasOwnProperty('success')){

                                        imgurl = '../img/success_.gif?a=' + Math.random();
                
                                        swal({
                                                    title: '<h1>Success</h1>',
                                                    text: `SMTP test message was sent successfully. Please check ${inputValue} for the message sent`,
                                                    imageUrl:imgurl,
                                                    html:true
                                        });
                                        
                                        
                                    } 
                                    
                                    

                                    
                        
                        
                                },
                                error: function(jqXHR,textStatus, errorThrown) {  
                                    $('#busy').modal('hide');
                                    imgurl = '../img/info_.gif?a=' + Math.random();
                
                                    swal({
                                                title: '<h1>Error</h1>',
                                                text: 'Failed to send message!',
                                                imageUrl:imgurl,
                                                html:true
                                    });
                                    
                                }
                                
                            });

                            
                        
                        })
        })

        $(document).ready(function() {

            setTimeout(function(){
                email_content = $('#new-riders-reg-email-msg-content').html();
                tinymce.get('new-riders-reg-email-msg').setContent(decodeHtml(email_content));

                email_content = $('#new-drivers-reg-email-msg-content').html();
                tinymce.get('new-drivers-reg-email-msg').setContent(decodeHtml(email_content));

                email_content = $('#password-reset-email-msg-content').html();
                tinymce.get('password-reset-email-msg').setContent(decodeHtml(email_content));

            },1000)            
                
        });
        

        function decodeHtml(html) {
            var txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        }


    </script>