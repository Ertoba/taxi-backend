<?php
    
?>

<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">

            <?php
                foreach($user_documents as $userdocument){
                    $expired = "";
                    $status = "";
                    $doc_id_input = "";
                if(!empty($userdocument['u_doc_expiry_date'])){
                    $expiry_date = date('Y-m-d', strtotime($userdocument['u_doc_expiry_date'])) ;
                    if(strtotime($userdocument['u_doc_expiry_date']) > time()){
                        $expired = "<div style='display: inline-block;margin-right: 20px;font-weight: bold;font-size: 16px;'> Expiry <span style='padding:5px;color:white;background-color:green'>{$expiry_date}</span></div>";
                    }else{
                        $expired = "<div style='display: inline-block;margin-right: 20px;font-weight: bold;font-size: 16px;'> Expiry <span style='padding:5px;color:white;background-color:red'>{$expiry_date}</span></div>";
                    }
                }

                if(!empty($userdocument['u_doc_id_num'])){
                    $doc_id_input = "<div><span style='color:black;padding:20px;border:thin dashed black;background-color:white;display: inline-block;min-width: 400px;text-align: center;font-weight: bold;font-size: 22px;letter-spacing: 10px;'>{$userdocument['u_doc_id_num']}</span></div>";
                }

                $selected1 = "";
                $selected2 = "";
                $selected3 = "";
                $selected4 = "";

                switch($userdocument['u_doc_status']){
                    case 0: 
                    $selected1 = "selected";
                    $status = "<div style='display:inline-block;'>Status <span style='display:inline-block;padding:5px;border-radius:5px;color:white;background-color:#9c27b0'>Pending</span></div>";
                    break; 
                    
                    case 1:
                    $selected2 = "selected";
                    $status = "<div style='display:inline-block;'>Status <span style='display:inline-block;padding:5px;border-radius:5px;color:white;background-color:red'>Failed</span></div>";
                    break;

                    case 2:
                    $selected3 = "selected";
                    $status = "<div style='display:inline-block;'>Status <span style='display:inline-block;padding:5px;border-radius:5px;color:white;background-color:orange'>Expired</span></div>";
                    break;

                    case 3:
                    $selected4 = "selected";
                    $status = "<div style='display:inline-block;'>Status <span style='display:inline-block;padding:5px;border-radius:5px;color:white;background-color:green'>Approved</span></div>";
                    break;

                    default:
                    $status = "<div style='display:inline-block;'>Status <span style='display:inline-block;padding:5px;border-radius:5px;color:white;background-color:#9c27b0'>Pending</span></div>";
                }


                $select_options = "<div style='margin: 10px 0;font-size: 16px;font-weight: bold;display: inline-block;'>Status <select id='doc-id-status-{$userdocument['id']}'>
                                        <option {$selected1} value='0'>Pending </option>
                                        <option {$selected2} value='1'>Failed </option>
                                        <option {$selected3} value='2'>Expired </option>
                                        <option {$selected4} value='3'>Approved </option>
                                    </select> <button data-docid='{$userdocument['id']}' data-userid='{$userdocument['u_id']}' data-usertype='0' onclick='updateDocStatus($(this))' class='btn btn-primary'>Update Status</button></div>";

                
            ?>

                <br />
                <h3><?php echo $userdocument['u_doc_title']?></h3>

                
                <?php echo $expired; ?>
                <?php echo $select_options; ?>
                <br />
                <br />
                <div><img src="<?php echo $userdocument['u_doc_img']?>" style="width:50%;max-width:500px;" /></div>
                <br />
                <br />
                <h3><?php echo $userdocument['u_doc_id_num_title']?></h3>                                
                <br />
                <?php echo $doc_id_input; ?>
                <br />
                <br>
                <div style="margin:30px 0;border-bottom:3px solid #777"></div>
                
            
                                
            <?php
                }
                if(empty($user_documents)){ echo "<h1 style='text-align:center;'>No document submitted by customer.</h1>";};
            ?>
                        
            
                            
        </div><!-- /.box-body -->
    </div>



    <script>

    function updateDocStatus(elem){

        let user_id = elem.data('userid');
        let doc_id = elem.data('docid');
        let user_type = elem.data('usertype');
        let doc_status = $(`#doc-id-status-${doc_id} option:selected`).val();

        
        $('#busy').modal('show');
        var post_data = {'action':'updateDocStatus','user_id' : user_id,'doc_id' : doc_id, 'user_type' : user_type, 'doc_status':doc_status};
        var search_data = [];
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            crossDomain:true,
            xhrFields: {withCredentials: true},
            data: post_data,
            success: function (data, status)
            {
                $('#busy').modal('hide');
                try{
                    var data_obj = JSON.parse(data);
                }catch(e){
                    
                    let imgurl = '../img/info_.gif?a=' + Math.random();

                    swal({
                                title: '<h1>Error</h1>',
                                text: "Error updating document status",
                                imageUrl:imgurl,
                                html:true
                    });
                }

                if(data_obj.hasOwnProperty('success')){
                    
                    let imgurl = '../img/success_.gif?a=' + Math.random();

                    swal({
                                title: '<h1>Success</h1>',
                                text: "Document status was updated successfully",
                                imageUrl:imgurl,
                                html:true
                    });
                
                }  
    
            },
            error:function(jqXHR,textStatus, errorThrown){
                $('#busy').modal('hide');
                return;
            }
            
        });


    }

</script>



