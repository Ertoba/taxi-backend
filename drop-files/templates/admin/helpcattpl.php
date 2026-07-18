
<div class="row">
  <div class="col-sm-12">
      <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Add, edit and delete help categories displayed on the Rider and Driver Apps.
      </div>
  </div>             

</div> <!--/row-->



<div class="modal fade" id="add-helpcat" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add Help Category</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                                          
                        <div class="form-group">
                                
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Visiblitiy</p>
                                Web <input type="checkbox" id="show-web" name="show-web" style="margin-right:10px;"> 
                                Rider App <input type="checkbox" id="show-rider" name="show-rider" style="margin-right:10px;"> 
                                Driver App <input type="checkbox" id="show-driver" name="show-driver" style="margin-right:10px;">
                            </div>  
                                
                        </div>


                        

                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Title</p>
                                <input  type="text" required="required" class="form-control" id="helpcat-title" placeholder="" name="helpcat-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description</p>
                                <input  type="text" required="required" class="form-control" id="helpcat-desc" placeholder="" name="helpcat-desc" value="" > 
                            </div>  
                                
                        </div>



                                                
                        
                       
                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="savecat" >Save</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-helpcat" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Edit Help Category</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="ehelpcat-id" hidden="hidden" name="ehelpcat-id" value="0" />  


                        <div class="form-group">
                                
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Visiblitiy</p>
                                Web <input type="checkbox" id="eshow-web" name="eshow-web" style="margin-right:10px;"> 
                                Rider App <input type="checkbox" id="eshow-rider" name="eshow-rider" style="margin-right:10px;"> 
                                Driver App <input type="checkbox" id="eshow-driver" name="eshow-driver" style="margin-right:10px;">
                            </div>  
                                
                        </div>


                                                
                                              
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Title</p>
                                <input  type="text" class="form-control" id="ehelpcat-title" placeholder="" name="ehelpcat-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description</p>
                                <input  type="text" class="form-control" id="ehelpcat-desc" placeholder="" name="ehelpcat-desc" value="" > 
                            </div>  
                                
                        </div>



                                              
                        
                       
                        <hr />
                        <button type="submit" class="btn btn-success" value="1" name="editcat" >Update</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-sm-12">   
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Add New</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <br />

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-helpcat" >Add new category</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>



        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <div class="box-header with-border">                      
                    <h3 class="box-title">Help Categories </h3>
                  </div><!-- /.box-header -->
                <div class="box-body">
                  <br />
                  <br />
                    <div> <!--pages-->
                   
                        <?php
                                if(!empty($pages)){
                                    echo " Pages: ";
                                    for($i = 1;$i < $pages + 1; $i++){
                                        if($i == $page_number){
                                            echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                        }else{
                                            echo "<a class='btn' href='help-cat.php?page=".$i."'>".$i."</a>";
                                            }  
                                        
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
                              <th style="">Title</th>
                              <th style="">Description</th>
                              <th style="">Help Topics</th>
                              <th style="">Web</th>
                              <th style="">Rider App</th>
                              <th style="">Driver App</th>
                              <th style="">Date Created</th>
                              <th style="">Date Modified</th>
                              <th style="">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1;
                              foreach($help_categories_page_items as $helpcategoriespageitems){
                                  $del_button = $helpcategoriespageitems['id'] != 1 ? "<a href='help-cat.php?action=del&id={$helpcategoriespageitems['id']}' data-itemid='{$helpcategoriespageitems['id']}' data-msg = 'All help topics under this category will be moved to the uncategorized category. Are you sure you want to delete this category?' class='delete-item btn btn-danger btn-margin-top btn-xs'>Delete</a>" : "";
                                  $help_topics_count = !empty($help_categories_help_topics_count[$helpcategoriespageitems['id']]['help_topics_count']) ? $help_categories_help_topics_count[$helpcategoriespageitems['id']]['help_topics_count'] : 0;
                                  $item_data = array('title'=>$helpcategoriespageitems['title'],'desc'=>$helpcategoriespageitems['desc'],'cat_id'=>$helpcategoriespageitems['id'],'show_web'=> $helpcategoriespageitems['show_web'],'show_rider'=> $helpcategoriespageitems['show_rider'],'show_driver'=> $helpcategoriespageitems['show_driver']);
                                  $item_data_json = json_encode($item_data);
                                  $show_web = !empty($helpcategoriespageitems['show_web']) && $helpcategoriespageitems['show_web'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  $show_rider = !empty($helpcategoriespageitems['show_rider']) && $helpcategoriespageitems['show_rider'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  $show_driver = !empty($helpcategoriespageitems['show_driver']) && $helpcategoriespageitems['show_driver'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  echo "<tr><td>". $count++ . "</td><td>" . $helpcategoriespageitems['title']. "</td><td>" . $helpcategoriespageitems['desc']. "</td><td>" . $help_topics_count. "</td><td>" . $show_web. "</td><td>" . $show_rider. "</td><td>" . $show_driver . "</td><td>". date('l, M j, Y H:i:s',strtotime($helpcategoriespageitems['date_created'].' UTC')). "</td><td>". date('l, M j, Y H:i:s',strtotime($helpcategoriespageitems['date_modified'].' UTC')) . "</td><td>". "<a href='#' data-itemid='{$helpcategoriespageitems['id']}' class='edit-helpcat btn btn-success btn-margin-top btn-xs'>Edit</a> {$del_button} <span id='helpcatitemdata-{$helpcategoriespageitems['id']}' style='display:none;'>{$item_data_json}</span>" ."</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                    <?php if(!$number_of_help_categories){ echo "<h1 style='text-align:center;'>Nothing to Show. Add help categories to get this area populated.</h1>";} ?> 
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



  <script>

      var data;

      

      $('.edit-helpcat').on('click', function(){
        var item_id = $(this).data('itemid');
        $('#ehelpcat-id').val(item_id);
        data = JSON.parse($('#helpcatitemdata-'+item_id).html());
        if(data.show_web == 1){
            $('#eshow-web').prop("checked", true);
        }else{
            $('#eshow-web').prop("checked", false);
        }
        if(data.show_rider == 1){
            $('#eshow-rider').prop("checked", true);
        }else{
            $('#eshow-rider').prop("checked", false);
        }
        if(data.show_driver == 1){
            $('#eshow-driver').prop("checked", true);
        }else{
            $('#eshow-driver').prop("checked", false);
        }

        
        $('#ehelpcat-title').val(data.title);
        $('#ehelpcat-desc').val(data.desc);
        
        $('#edit-helpcat').modal('show');
      });


      

  </script>
    