<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Create, edit and manage banners here. Banners are displayed on the apps and can be used to show adverts or informations. 
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

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-banner" >Add new banner</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>


<div class="row">
    <div class="col-sm-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Banners</h3>       
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>#</th>  
                                <th>Title</th>  
                                <th>Excerpt</th>
                                <th>City</th>
                                <th>Visibility</th>
                                <th>Views</th>
                                <th>Status</th>
                                <th>Date modified</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $count = 0;
                                
                                foreach($banners_data as $bannersdata){
                                    $count++;
                                    $banner_visibility = "";

                                    switch($bannersdata['visibility']){

                                        case 0:
                                        $banner_visibility = "Rider and Driver";
                                        break;

                                        case 1:
                                        $banner_visibility = "Rider";  
                                        break;

                                        case 2:
                                        $banner_visibility = "Driver";  
                                        break;

                                    }

                                    //get views / impressions
                                    $banner_views = 0;

                                    if($redis){
                                        $view_count = $redis->zCount("bannerviewcount:{$bannersdata['id']}", "-inf","+inf");
                                        if(!empty(intval($view_count)))$banner_views = $view_count;
                                    }
                                    

                                    $city_title = "All";
                                    if(!empty($bannersdata['city'])){
                                        if(empty($bannersdata['r_title'])){
                                            $city_title = "Not found"; 
                                        }else{
                                            $city_title = $bannersdata['r_title'];
                                        }
                                    }


                                    $status = !empty($bannersdata['status']) ? "<i class='fa fa-circle' style='color:green' ></i>" : "<i class='fa fa-circle' style='color:red' ></i>";                                    

                                    
                                    $banner_created_date = date('Y-m-d', strtotime($bannersdata['date_created'] . ' UTC'));

                                    
                                    echo "<tr><td>{$count}</td><td>{$bannersdata['title']}</td><td>{$bannersdata['excerpt']}</td><td>{$city_title}</td><td>{$banner_visibility}</td><td>{$banner_views}</td><td>{$status}</td><td>{$banner_created_date}</td><td> <a class='btn btn-xs btn-success edit-banner-btn' href='#' data-bannerid='{$bannersdata['id']}' data-bannertitle='{$bannersdata['title']}' data-bannerexcerpt='{$bannersdata['excerpt']}'  data-bannercity='{$bannersdata['city']}' data-bannerviz='{$bannersdata['visibility']}' data-bannerstatus='{$bannersdata['status']}' data-bannerfimg='{$bannersdata['feature_img']}' >Edit</a> <div id='b-content-{$bannersdata['id']}' style='display:none'>{$bannersdata['content']}</div> <a class='btn btn-xs btn-danger del-banner-btn' href='banners.php?action=del&bid={$bannersdata['id']}' >Delete</a></td></tr>";
                                }

                            ?>
                        </tbody>

                        

                    </table>

                </div>
                
                <?php echo empty($banners_data) ? "<h1 style='text-align:center;'>Nothing to Show. Add banners to get this area populated.</h1>" : ""; ?>
                    
                                
            </div><!-- /.box-body -->
        </div>
    </div>
</div>



<div class="modal fade" id="add-banner" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add New Banner</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Title</p>
                                <input  type="text"  required= "required" class="form-control" id="banner-title" name="banner-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Excerpt</p>
                                <input  type="text"  required= "required" class="form-control" id="banner-excerpt" name="banner-excerpt" value="" > 
                            </div>  
                                
                        </div>


                        <div class="form-group">

                                                       
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Feature Image (1024px X 180px)</p>
                                <div style="text-align:center;"><img id="banner-fimg-preview" src="../img/default-banner-img.jpg" style="width:150px;" /></div>
                                <input  type="text" hidden="hidden" id="banner-fimg-data" name="banner-fimg-data" value="" > 
                                <input  type="file" class="form-control" id="banner-fimg" name="banner-fimg" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter Banner Content</p>
                                <textarea required= "required" class="textformat" id="banner-content" placeholder="" name="banner-content"></textarea> 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select city where banner will be visible</p>
                                <select class="form-control" id="banner-city" name="banner-city">
                                    <option value="0" >All Cities</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select app visibility</p>
                                <select class="form-control" id="banner-viz" name="banner-viz">
                                    <option value="0" >Rider and Driver App</option>
                                    <option value="1" >Rider App</option>
                                    <option value="2" >Driver App</option>                         
                                </select>
                                
                            </div>                     
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Status</p>
                                <select class="form-control" id="banner-status" name="banner-status">
                                    <option value="0" >Inactive</option>
                                    <option value="1" >Active</option>                       
                                </select>
                                
                            </div>                     
                        </div>


                                                    
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="add-banner" >Add Banner</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>





<div class="modal fade" id="edit-coupon" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-coupon-label">Edit Coupon</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="e-banner-id" hidden="hidden" name="ebanner-id" value="0" />  
                        <input  type="text" id="e-banner-fimg-file" hidden="hidden" name="e-banner-fimg-file" value="0" />  
                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Title</p>
                                <input  type="text"  required= "required" class="form-control" id="ebanner-title" name="ebanner-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Excerpt</p>
                                <input  type="text"  required= "required" class="form-control" id="ebanner-excerpt" name="ebanner-excerpt" value="" > 
                            </div>  
                                
                        </div>

                        
                        <div class="form-group">

                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Banner Feature Image (1024px X 180px)</p>
                                <div style="text-align:center;"><img id="ebanner-fimg-preview" src="../img/default-banner-img.jpg" style="width:150px;" /></div>
                                <input  type="text" hidden="hidden" id="ebanner-fimg-data" name="ebanner-fimg-data" value="" > 
                                <input  type="file" class="form-control" id="ebanner-fimg" name="ebanner-fimg" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 10px;margin-bottom: 2px;">Enter Banner Content</p>
                                <textarea class="textformat" id="ebanner-content" placeholder="" name="ebanner-content"></textarea> 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select city where banner will be visible</p>
                                <select class="form-control" id="ebanner-city" name="ebanner-city">
                                    <option value="0" >All Cities</option>
                                    <?php
                                    foreach($inter_city_routes as $intercityroutes){
                                        echo "<option value='{$intercityroutes['id']}' >{$intercityroutes['r_title']}</option>" . "\n";
                                    }

                                    ?>                            
                                </select>
                                
                            </div>                     
                        </div>


                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select app visibility</p>
                                <select class="form-control" id="ebanner-viz" name="ebanner-viz">
                                    <option value="0" >Rider and Driver App</option>
                                    <option value="1" >Rider App</option>
                                    <option value="2" >Driver App</option>                         
                                </select>
                                
                            </div>                     
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Status</p>
                                <select class="form-control" id="ebanner-status" name="ebanner-status">
                                    <option value="0" >Inactive</option>
                                    <option value="1" >Active</option>                       
                                </select>
                                
                            </div>                     
                        </div>
                            
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="edit-banner" >Update Banner</button>  


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>



<script>

    var b_content;
    var sel_city;
    var sel_viz;
    var sel_status;
    var f_img;

    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });

       


      function decodeHtml(html) {
          var txt = document.createElement("textarea");
          txt.innerHTML = html;
          return txt.value;
      }

    

    $('.edit-banner-btn').on('click', function(){
        
        let elem = $(this);
        
        $('#e-banner-id').val(elem.data('bannerid'));
        $('#ebanner-title').val(elem.data('bannertitle'));
        $('#ebanner-excerpt').val(elem.data('bannerexcerpt'));

        f_img = elem.data('bannerfimg');

        if(f_img){
            $('#ebanner-fimg-preview').attr('src','<?php echo SITE_URL . "img/" ?>' + f_img);
            $('#e-banner-fimg-file').val(f_img);
        }else{
            $('#ebanner-fimg-preview').attr('src','../img/default-banner-img.jpg');
        }
        


        let b_id = elem.data('bannerid');
        b_content = $(`#b-content-${b_id}`).html();

        sel_city = elem.data('bannercity');
        jQuery("select#ebanner-city option[value='" + sel_city + "']").prop({selected: true}); 

        sel_viz = elem.data('bannerviz');
        jQuery("select#ebanner-viz option[value='" + sel_viz + "']").prop({selected: true}); 

        sel_status = elem.data('bannerstatus');
        jQuery("select#ebanner-status option[value='" + sel_status + "']").prop({selected: true}); 
                
        $('#edit-coupon').modal('show');

    })



    $('#edit-coupon').on('hidden.bs.modal', function () {
        jQuery("select#ebanner-city option[value='" + sel_city + "']").prop({selected: false});         
        jQuery("select#ebanner-viz option[value='" + sel_viz + "']").prop({selected: false});         
        jQuery("select#ebanner-status option[value='" + sel_status + "']").prop({selected: false}); 
    });

    
    $('#edit-coupon').on('shown.bs.modal', function () {
        tinymce.activeEditor.setContent(decodeHtml(b_content));
        $('#ebanner-content').focus();
    });
    
    var imgurl;
    $('.del-banner-btn').on('click', function(e){
        e.preventDefault();
        element = $(this);
        imgurl = '../img/info_.gif?a=' + Math.random();
        swal({
                title: "<h2>Delete banner</h2>",
                text: "This banner will be deleted" ,
                imageUrl:imgurl,
                html:true,
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!",
                closeOnConfirm: true,
                closeOnCancel: true
                },
                function(isConfirm){
                if (isConfirm) {
                        var link = element.attr('href');
                        window.location = link;
                } 
            });
    })
    


   $('#banner-fimg').on('change', function(e){
    
    readBannerImgFile(e, function(res){
        console.log(res);
        if(res.error){

            $('#banner-fimg-preview').attr('src','../img/default-banner-img.jpg');
            $('#banner-fimg-data').val('');
            $('#banner-fimg').val('');
            let imgurl = '../img/info_.gif?a=' + Math.random();
    
            swal({
                        title: '<h1>Error</h1>',
                        text: res.error_msg,
                        imageUrl:imgurl,
                        html:true
            });

        }else{
            $('#banner-fimg-preview').attr('src',res.data);
            $('#banner-fimg-data').val(res.data);
        }
    });   

   });


   $('#ebanner-fimg').on('change', function(e){
    
        readBannerImgFile(e, function(res){
            console.log(res);

            if(res.error){
                
                $('#ebanner-fimg-data').val('');                
                let imgurl = '../img/info_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Error</h1>',
                            text: res.error_msg,
                            imageUrl:imgurl,
                            html:true
                });

            }else{

                $('#ebanner-fimg-preview').attr('src',res.data);
                $('#ebanner-fimg-data').val(res.data);
                

            }   
        })



    });


   
    function readBannerImgFile(input, callback) {
        if (input.target.files && input.target.files[0]) {
            var imgPath = input.target.files[0].name;
            var imgSize = input.target.files[0].size;
            
            var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
            var result = {data:'',error:1,error_msg:''};
            if(imgSize > 1000000){
                //filesize greater than 1MB
                result.error_msg = 'File size must not be greater than 200KB';
                callback(result);
                return;
            }

            if (extn == "jpg" || extn == "jpeg") {
            if (typeof (FileReader) != "undefined") {
                    var reader = new FileReader();			
                    reader.onload = function (e) {
                        /* jQuery('#passport')
                            .attr('src', e.target.result)
                            .width(150)
                            .height('auto'); */
                        
                        
                        
                        result.data = e.target.result;
                        result.error = 0;
                        callback(result);
                                    
                            
                    };

                    reader.readAsDataURL(input.target.files[0]);
                }

            }else{
                result.error_msg = 'Invalid file type. Only JPG files are allowed.';
                callback(result);
            }
        }
    }
    

    



</script>