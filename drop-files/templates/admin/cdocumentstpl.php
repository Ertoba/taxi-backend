<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Create and manage documents required by customers for registration on the service. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">
    <div class="col-sm-12">   
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Add New</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <br />

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-document" >Add new document</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>


<div class="row">
    <div class="col-sm-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Documents</h3>            
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>#</th>  
                                <th>Title</th>
                                <th>City</th>
                                <th>Document type</th>
                                <th>Expirable</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $count = 0;
                                
                                foreach($documents_data as $documentsdata){
                                    $count++;
                                    $document_type = '';
                                    switch($documentsdata['doc_type']){
                                        case '0':
                                        $document_type = 'Personal';
                                        break;

                                        case '1':
                                        $document_type = 'Vehicle';
                                        break;

                                    }

                                    
                                    
                                    
                                    $doc_create_date = date('Y-m-d', strtotime($documentsdata['date_created'] . ' UTC'));
                                    $expirable = !empty($documentsdata['doc_expiry']) ? "Yes" : "No";
                                    $city_title = "All";
                                    if(!empty($documentsdata['doc_city'])){
                                        if(empty($documentsdata['r_title'])){
                                            $city_title = "Not found"; 
                                        }else{
                                            $city_title = $documentsdata['r_title'];
                                        }
                                    }
                                    $doc_status = empty($documentsdata['status']) ? "<a href='cdocuments.php?status=1&did={$documentsdata['did']}' class='btn btn-xs btn-success'>Activate</a> " : "<a href='cdocuments.php?status=0&did={$documentsdata['did']}' class='btn btn-xs btn-danger'>Deactivate</a>";
                                    $doc_status_indicator = empty($documentsdata['status']) ? "<i class='fa fa-circle' style='color:red;' title='disabled'></i>" : "<i class='fa fa-circle' style='color:green;' title='enabled'></i>";
                                    echo "<tr><td>{$count}</td><td>{$documentsdata['title']}</td><td>{$city_title}</td><td>{$document_type}</td><td>{$expirable}</td><td>{$doc_status_indicator}</td><td>{$doc_status} <a class='btn btn-xs btn-success edit-doc-btn' href='#' data-docid='{$documentsdata['did']}' data-doctitle='{$documentsdata['title']}' data-docdesc='{$documentsdata['doc_desc']}'  data-doccityid='{$documentsdata['doc_city']}' data-doctype='{$documentsdata['doc_type']}' data-docexpiry='{$documentsdata['doc_expiry']}' data-docidnum='{$documentsdata['doc_id_num']}' data-docidnumtitle='{$documentsdata['doc_id_num_title']}' data-docidnumdesc='{$documentsdata['doc_id_num_desc']}' >Edit</a> <a data-msg='This document type will be deleted. Do you want to continue?' class='btn btn-xs btn-danger delete-item' href='cdocuments.php?action=del&did={$documentsdata['did']}' >Delete</a></td></tr>";
                                }

                            ?>
                        </tbody>

                        

                    </table>

                </div>
                
                <?php echo empty($documents_data) ? "<h1 style='text-align:center;'>Nothing to Show. Add documents to get this area populated.</h1>" : ""; ?>
                    
                                
            </div><!-- /.box-body -->
        </div>
    </div>
</div>



<div class="modal fade" id="add-document" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add New Document</h4>
            </div>
            <div class="modal-body">

                    <form id="add-doc-form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter document title</p>
                                <input  type="text"  required= "required" class="form-control" id="doc-title" name="doc-title" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter document description</p>
                                <input  type="text"  required= "required" class="form-control" id="doc-desc" name="doc-desc" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select class="form-control" id="doc-city" name="doc-city">
                                    <option value="0" >All cities</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>


                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Document type</p>
                                <select class="form-control" id="doc-type" name="doc-type">
                                    <option value="1" >Vehicle document</option>
                                    <option value="0" >Personal document</option>   
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Expiry date</p>
                                <select class="form-control" id="doc-expiry" name="doc-expiry">
                                    <option value="0" >No, document expiry date is not required</option>
                                    <option value="1" >Yes, document expiry date is required</option>
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Document identification number input</p>
                                <select class="form-control" id="doc-id-num-inp" name="doc-id-num-inp">
                                    <option value="0" >No, document identification number input is not required</option>
                                    <option value="1" >Yes, document identification number input is required</option>
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div id="id-num-item-title" class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Document identification numper input title</p>
                                <input  type="text"  required= "required" class="form-control" id="doc-id-num-title" name="doc-id-num-title" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div id="id-num-item-desc" class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Document identification numper input description</p>
                                <input  type="text"  required= "required" class="form-control" id="doc-id-num-desc" name="doc-id-num-desc" value="" > 
                            </div>  
                                
                        </div>

                                                

                        <hr />
                        <button type="submit" id="add-doc-btn" class="btn btn-primary" value="1" name="add-document" >Add Document</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>





<div class="modal fade" id="edit-document" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-document-label">Edit Document</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="e-doc-id" hidden="hidden" name="doc-id" value="0" />  
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter document title</p>
                                <input  type="text"  required= "required" class="form-control" id="e-doc-title" name="doc-title" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter document description</p>
                                <input  type="text"  required= "required" class="form-control" id="e-doc-desc" name="doc-desc" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a city</p>
                                <select class="form-control" id="e-doc-city" name="doc-city">
                                    <option value="0" >All cities</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>


                        <br>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Document type</p>
                                <select class="form-control" id="e-doc-type" name="doc-type">
                                    
                                    <option value="1" >Vehicle document</option> 
                                    <option value="0" >Personal document</option>
                                    
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Expiry date</p>
                                <select class="form-control" id="e-doc-expiry" name="doc-expiry">
                                    <option value="0" >No, document expiry date is not required</option>
                                    <option value="1" >Yes, document expiry date is required</option>
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Document identification number input</p>
                                <select class="form-control" id="e-doc-id-num-inp" name="doc-id-num-inp">
                                    <option value="0" >No, document identification number input is not required</option>
                                    <option value="1" >Yes, document identification number input is required</option>
                                                               
                                </select>
                                
                            </div>                     
                        </div>

                        <br>


                        <div id="e-id-num-item-title" class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Document identification numper input title</p>
                                <input  type="text"  required= "required" class="form-control" id="e-doc-id-num-title" name="doc-id-num-title" value="" > 
                            </div>  
                                
                        </div>

                        <br>

                        <div id="e-id-num-item-desc" class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Document identification numper input description</p>
                                <input  type="text"  required= "required" class="form-control" id="e-doc-id-num-desc" name="doc-id-num-desc" value="" > 
                            </div>  
                                
                        </div>

                        <br>
                            
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="edit-document" >Update Document</button>  


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>

<?php
           
            
           if(!empty($_SESSION['action_success'])){
            $msgs = '';
            foreach($_SESSION['action_success'] as $action_success){
                $msgs .= "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> ".$action_success . "</p>";
            }
        
            $cache_prevent = RAND();
            echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Success</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
        
                unset($_SESSION['action_success']);
        
        }elseif(!empty($_SESSION['action_error'])){
                $msgs = '';
                foreach($_SESSION['action_error'] as $action_error){
                    $msgs .= "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> ".$action_error . "</p>";
                }
        
                $cache_prevent = RAND();
                echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Error</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
            
                    unset($_SESSION['action_error']);
            
        }




       ?>



<script>

    var sel_city; 
    var doc_type;
    var doc_expiry;
    var doc_id_num;  


    $('#add-document').on('shown.bs.modal', function () {      
        
      
    });



    let req_id_num = $('#doc-id-num-inp').val();

    if(req_id_num == 1){
        $('#id-num-item-title').fadeIn();
        $('#id-num-item-desc').fadeIn();
        $('#doc-id-num-title').prop('disabled', false);
        $('#doc-id-num-desc').prop('disabled', false);
    }else{
        $('#id-num-item-title').fadeOut();
        $('#id-num-item-desc').fadeOut();
        $('#doc-id-num-title').prop('disabled', true);
        $('#doc-id-num-desc').prop('disabled', true);
    }


    $('#doc-id-num-inp').on('change', function(){
        let val = $(this).val();

        if(val == 1){
            $('#id-num-item-title').fadeIn();
            $('#id-num-item-desc').fadeIn();
            $('#doc-id-num-title').prop('disabled', false);
            $('#doc-id-num-desc').prop('disabled', false);
        }else{
            $('#id-num-item-title').fadeOut();
            $('#id-num-item-desc').fadeOut();
            $('#doc-id-num-title').prop('disabled', true);
            $('#doc-id-num-desc').prop('disabled', true);
        }

    })


    $('#e-doc-id-num-inp').on('change', function(){
        let val = $(this).val();

        if(val == 1){
            $('#e-id-num-item-title').fadeIn();
            $('#e-id-num-item-desc').fadeIn();
            $('#e-doc-id-num-title').prop('disabled', false);
            $('#e-doc-id-num-desc').prop('disabled', false);
        }else{
            $('#e-id-num-item-title').fadeOut();
            $('#e-id-num-item-desc').fadeOut();
            $('#e-doc-id-num-title').prop('disabled', true);
            $('#e-doc-id-num-desc').prop('disabled', true);
        }

    })
    

    $('.edit-doc-btn').on('click', function(){

        $('#e-doc-id').val($(this).data('docid'));
        $('#e-doc-title').val($(this).data('doctitle'));
        $('#e-doc-desc').val($(this).data('docdesc'));
        sel_city = $(this).data('doccityid');
        jQuery("select#e-doc-city option[value='" + sel_city + "']").prop({selected: true});

        doc_type = $(this).data('doctype');
        jQuery("select#e-doc-type option[value='" + doc_type + "']").prop({selected: true});


        doc_expiry = $(this).data('docexpiry');
        jQuery("select#e-doc-expiry option[value='" + doc_expiry + "']").prop({selected: true});

        doc_id_num = $(this).data('docidnum');
        jQuery("select#e-doc-id-num-inp option[value='" + doc_id_num + "']").prop({selected: true});

        if(doc_id_num == 1){
            $('#e-id-num-item-title').show();
            $('#e-id-num-item-desc').show();
            $('#e-doc-id-num-title').prop('disabled', false);
            $('#e-doc-id-num-desc').prop('disabled', false);
            $('#e-doc-id-num-title').val($(this).data('docidnumtitle'));
            $('#e-doc-id-num-desc').val($(this).data('docidnumdesc'));

        }else{
            $('#e-id-num-item-title').hide();
            $('#e-id-num-item-desc').hide();
            $('#e-doc-id-num-title').prop('disabled', true);
            $('#e-doc-id-num-desc').prop('disabled', true);
        }
                
        $('#edit-document').modal('show');
    })



    $('#edit-document').on('hidden.bs.modal', function () {
        jQuery("select#e-doc-city option[value='" + sel_city + "']").prop({selected: false}); 
        jQuery("select#e-doc-type option[value='" + doc_type + "']").prop({selected: false}); 
        jQuery("select#e-doc-expiry option[value='" + doc_expiry + "']").prop({selected: false});  

        $('#e-doc-id-num-title').val('');
        $('#e-doc-id-num-desc').val('');
    });

    
    




       


   

    

    



</script>