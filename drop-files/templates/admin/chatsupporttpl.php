

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Respond to support messages from your customers. 
        </div>
    </div>
</div>



<div class="row">
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Open Chats</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                    <!-- <div  class="row"> 
                        <div  class="col-sm-12"> 
                            <form  id="sort-form" enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
                                <div class="form-group">
                                    <div class="col-sm-3">            
                                        <select style="margin-top:5px;" class="form-control" id="view_user_type" name="view_user_type">
                                            <option value="1" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 1 ? "selected" : "";  ?> >Show All</option>
                                            <option value="2" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 2 ? "selected" : "";  ?> >Riders Only</option> 
                                            <option value="3" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 3 ? "selected" : "";  ?>>Drivers Only</option>                                            
                                        </select>               
                                    </div>  
                                    
                                </div>
                            </form>
                        </div>
                    </div>
                    <br />
                    <div style="float:left;width:40%;"><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>"class="btn btn-default">Show All Chats</a> </div> -->
                    
                  <br />
                  <br />
                  <!-- <hr>
                  <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div> -->
             
            <br />
            <div> <!--pages-->
            
                <?php
                    
                    
                    if(!empty($pages)){
                        $url = $_SERVER['REQUEST_URI'];
                        $url_parts = parse_url($url);
                        if(isset($url_parts['query'])){
                            parse_str($url_parts['query'], $params);
                        }
                        
                        echo "Pages: ";

                        if($page_number > 1){
                            
                            $params['page'] = 1;     // Overwrite if exists

                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> << </a>";

                            $prev_page = $page_number - 1;
                            $params['page'] = $prev_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> < </a>";

                        }
                        
                        // range of num links to show
                        $range = 2;

                        // display links to 'range of pages' around 'current page'
                        $initial_num = $page_number - $range;
                        $condition_limit_num = ($page_number + $range)  + 1;

                        
                        for($i = $initial_num;$i < $condition_limit_num + 1; $i++){

                            // be sure '$i is greater than 0' AND 'less than or equal to the $total_pages'
                            if (($i > 0) && ($i <= $pages)) {

                                if($i == $page_number){
                                    echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                }else{
                                    
                                    $params['page'] = $i;     // Overwrite if exists
                                    $url_parts['query'] = http_build_query($params);                                                
                                    echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'>".$i."</a>";
                                        
                                } 

                            }
                            
                             
                            
                        }

                        if($page_number < $pages){

                            $next_page = $page_number + 1;
                            $params['page'] = $next_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> > </a>";
                            
                            $params['page'] = $pages;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> >> </a>";

                            

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
                        <th>User</th>    
                        <th>Phone</th>
                        <th>Action</th>                     
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    
                    
                    foreach($open_chat_support_data as $userdata){
                        
                        $user_fullname = $userdata['firstname'] . " " . $userdata['lastname'];
                        $user_phone = $userdata['country_dial_code'] . $userdata['phone'];
                        $user_phone = (!empty(DEMO) ? mask_string($user_phone) : $user_phone);
                        
                        if($userdata['account_type'] == 1){
                            $view_user = "<a href='view-customer.php?id={$userdata['user_id']}' class='btn btn-xs btn-primary'>View</a>";
                        }else{
                            $view_user = "<a href='view-staff.php?id={$userdata['user_id']}' class='btn btn-xs btn-primary'>View</a>";
                        }

                        $chat_btn = "<a href='#' class='btn btn-xs btn-success chat-btn' data-userid='{$userdata['user_id']}' >Open chat</a>";
                        $close_chat_btn = "<a href='#' class='btn btn-xs btn-warning close-chat-btn' data-userid='{$userdata['user_id']}' >Close chat</a>";
                                                    
                        echo "<tr><td>". $count++ . "</td><td>" . $user_fullname . "</td><td>". $user_phone . "</td><td>{$view_user} {$chat_btn} {$close_chat_btn}</td></tr>";

                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($open_chat_support_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Open Chats.</h1>";} ?>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>

<div class="modal fade" id="chat-dialog" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog " role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="gridSystemModalLabel">Chat</h4>
              </div>
              <div class="modal-body">
                  <div style="width:100%; height: 500px;">
                    <div id="chat-support-content" style="width:100%;height:100%;overflow-y: scroll;">
        
                    </div>
                    
                  </div>
              </div>
              <div class="modal-footer">
                <!-- <button type="button" id="export_data_btn" class="btn btn-primary">Export</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> -->
                <div id="chat-support-footer" style="flex-shrink: 0;padding:5px;border-top: thin solid #ddd;display: flex;flex-wrap: nowrap;align-items: center;justify-content: space-between;">
                    <textarea placeholder="Enter your message here..." id="chat-support-msg-content" style="font-weight: bold;width: calc(100% - 100px);float: left;border: 0;background-color: #eee;border-radius: 10px;padding: 10px;box-sizing: border-box;" rows="3" maxlength="250"></textarea>
                    <button class="btn btn-primary" onclick="chat_support_msg_send();" data-userid='0' id="chat-support-msg-send-btn" style="margin-right: 2px;"><i class="fa fa-paper-plane" style='color:white;font-size:20px;'></i></button>
                    <div style="clear: both;"></div>
                </div>
              </div>
            </div>
        </div>
    </div>
  

  <script>

    var upd_chat_content_timer_handle = 0;
    var chat_update_ajax_handle;

/* $('#export-data').on('click', function(){
  $('#chat-dialog').modal('show');
}); */

$('#chat-dialog').on('shown.bs.modal', function() {
    updatechatcontent($('#chat-support-msg-send-btn').data('userid')); 
    return;
});

$('#chat-dialog').on('hidden.bs.modal', function() {
    clearInterval(upd_chat_content_timer_handle);
    return;
});




function chat_support_msg_send(){

    var msg = $('#chat-support-msg-content').val();
    if(!msg)return;
    var send_user_id = $('#chat-support-msg-send-btn').data('userid');
    

    $('#chat-support-msg-send-btn').prop('disabled', true);
    $('#chat-support-msg-send-btn').css("background-color","grey");

    var post_data = {'action':'senduserchatsupportmsg','userid' : send_user_id, 'msg' : msg};

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      timeout : 60000,
      crossDomain:true,
      xhrFields: {withCredentials: true},
      data: post_data,
      success: function (data, status)
      {

        $('#chat-support-msg-send-btn').prop('disabled', false);
        $('#chat-support-msg-send-btn').css("background-color","#0077ff");

          try{
              var data_obj = JSON.parse(data);
          }catch(e){

              imgurl = '../img/info_.gif?a=' + Math.random();

              swal({
                          title: '<h1>Error</h1>',
                          text: 'Failed to send messages!',
                          imageUrl:imgurl,
                          html:true
              });
              return;

          }

          
          if(data_obj.hasOwnProperty('error')){
              imgurl = '../img/info_.gif?a=' + Math.random();

              swal({
                          title: '<h1>Error</h1>',
                          text: 'Failed to send message! - ' + data_obj.error,
                          imageUrl:imgurl,
                          html:true
              });
          }
          
          
            if(data_obj.hasOwnProperty('success')){

                $('#chat-support-msg-content').val('');
                $('#chat-support-content').empty();
                $('#chat-support-content').html(data_obj.chat_content);
                $('#chat-support-content').scrollTop(1000000000);
                
                
            }  
          
          

          


      },
      error: function(jqXHR,textStatus, errorThrown) {  
          
          

          imgurl = '../img/info_.gif?a=' + Math.random();

          swal({
                      title: '<h1>Error</h1>',
                      text: 'Failed to send messages',
                      imageUrl:imgurl,
                      html:true
          });
          
      }
      
    });



}


$('.close-chat-btn').on('click', function(e){

    e.preventDefault();

    var el = $(this);
    var user_id = el.data('userid');

    var post_data = {'action':'closeuserchatsupport','userid' : user_id};
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        timeout : 60000,
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
                            text: 'Failed to cloase chat!',
                            imageUrl:imgurl,
                            html:true
                });
                return;

            }

            
            if(data_obj.hasOwnProperty('error')){
                imgurl = '../img/info_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Error</h1>',
                            text: 'Failed to close chat! - ' + data_obj.error,
                            imageUrl:imgurl,
                            html:true
                });
            }
            
            
                if(data_obj.hasOwnProperty('success')){

                    window.location.reload();            
                    
                }  
            
            

            


        },
        error: function(jqXHR,textStatus, errorThrown) {  
            
            $('#busy').modal('hide');

            imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: 'Failed to close chat',
                        imageUrl:imgurl,
                        html:true
            });
            
        }
        
    });



})



$('.chat-btn').on('click', function(e){

  e.preventDefault();

  var el = $(this);
  var user_id = el.data('userid');
  
  //get chat messages through AJAX

  $('#busy').modal('show');
  
  var post_data = {'action':'getuserchatsupportmsg','userid' : user_id};
  $.ajax({
      url: ajaxurl,
      type: 'POST',
      timeout : 60000,
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
                          text: 'Failed to retrieve chat messages!',
                          imageUrl:imgurl,
                          html:true
              });
              return;

          }

          
          if(data_obj.hasOwnProperty('error')){
              imgurl = '../img/info_.gif?a=' + Math.random();

              swal({
                          title: '<h1>Error</h1>',
                          text: 'Failed to retrieve chat messages! - ' + data_obj.error,
                          imageUrl:imgurl,
                          html:true
              });
          }
          
          
            if(data_obj.hasOwnProperty('success')){

                $('#chat-support-msg-content').val('');
                $('#chat-support-content').empty();
                $('#chat-support-content').html(data_obj.chat_content);
                $('#chat-support-content').scrollTop(1000000000);
                $('#chat-support-msg-send-btn').data('userid', user_id);

                $('#chat-dialog').modal('show');
                
                
            }  
          
          

          


      },
      error: function(jqXHR,textStatus, errorThrown) {  
          
          $('#busy').modal('hide');

          imgurl = '../img/info_.gif?a=' + Math.random();

          swal({
                      title: '<h1>Error</h1>',
                      text: 'Failed to retrieve chat messages',
                      imageUrl:imgurl,
                      html:true
          });
          
      }
      
  });

    
})




function updatechatcontent(userid){
    console.log(userid);
    clearInterval(upd_chat_content_timer_handle);

    upd_chat_content_timer_handle = setInterval(function(){
    
        var post_data = {'action':'getuserchatsupportmsg','userid' : userid};

        if (chat_update_ajax_handle) {
            chat_update_ajax_handle.abort();
        }    
            
        

        chat_update_ajax_handle = $.ajax({
                                    url: ajaxurl,
                                    type: 'POST',
                                    timeout : 15000,
                                    crossDomain:true,
                                    xhrFields: {withCredentials: true},
                                    data: post_data,
                                    success: function (data, status)
                                    {
                                        $('#busy').modal('hide');

                                        try{
                                            var data_obj = JSON.parse(data);
                                        }catch(e){

                                            
                                            return;

                                        }

                                        
                                                                               
                                        
                                            if(data_obj.hasOwnProperty('success')){

                                                
                                                $('#chat-support-content').html(data_obj.chat_content);
                                                if(data_obj.new_msg == 1){
                                                    $('#chat-support-content').scrollTop(1000000000);
                                                }
                                                

                                                
                                                
                                                
                                            }  
                                        
                                        

                                        


                                    },
                                    error: function(jqXHR,textStatus, errorThrown) {  
                                        
                                        return;
                                        
                                    }
                                    
                                });

    }, 6000);

}

</script>


