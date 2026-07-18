
<div class="row">
  <div class="col-sm-12">
      <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Add, edit and delete help topics displayed on the Rider and Driver Apps.
      </div>
  </div>             

</div> <!--/row-->



<div class="modal fade" id="add-helptopic" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add Help Topic</h4>
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
                                
                            <div class="col-sm-4">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Category</p>
                                <select class="form-control" id="helptopic-cat" name="helptopic-cat" >
                                    <?php
                                        foreach($help_topics_categories as $helptopicscategories){
                                            echo "<option value='{$helptopicscategories['id']}'>{$helptopicscategories['title']}</option>";
                                        }
                                    ?>                                        
                                                                       
                                </select>
                            </div>  
                                
                        </div>


                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Title</p>
                                <input  type="text" required="required" class="form-control" id="helptopic-title" placeholder="" name="helptopic-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Excerpt</p>
                                <input  type="text" required="required" class="form-control" id="helptopic-excerpt" placeholder="" name="helptopic-excerpt" value="" > 
                            </div>  
                                
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Content</p>
                                <textarea required= "required" class="textformat" id="helptopic-content" placeholder="" name="helptopic-content"></textarea> 
                            </div>  
                                
                        </div>

                        
                        
                       
                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="savetopic" >Save</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-helptopic" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Edit Help Topic</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="ehelptopic-id" hidden="hidden" name="ehelptopic-id" value="0" />  


                        <div class="form-group">
                                
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Visiblitiy</p>
                                Web <input type="checkbox" id="eshow-web" name="eshow-web" style="margin-right:10px;"> 
                                Rider App <input type="checkbox" id="eshow-rider" name="eshow-rider" style="margin-right:10px;"> 
                                Driver App <input type="checkbox" id="eshow-driver" name="eshow-driver" style="margin-right:10px;">
                            </div>  
                                
                        </div>


                        <div class="form-group">
                                
                            <div class="col-sm-4">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Category</p>
                                <select class="form-control" id="ehelptopic-cat" name="ehelptopic-cat" >
                                    <?php
                                        foreach($help_topics_categories as $helptopicscategories){
                                            echo "<option value='{$helptopicscategories['id']}'>{$helptopicscategories['title']}</option>";
                                        }
                                    ?>                                        
                                                                       
                                </select>
                            </div>  
                                
                        </div>
                        
                                              
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Title</p>
                                <input  type="text" class="form-control" id="ehelptopic-title" placeholder="" name="ehelptopic-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Excerpt</p>
                                <input  type="text" class="form-control" id="ehelptopic-excerpt" placeholder="" name="ehelptopic-excerpt" value="" > 
                            </div>  
                                
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Content</p>
                                <textarea required= "required" class="textformat" id="ehelptopic-content" placeholder="" name="ehelptopic-content">mikolo</textarea> 
                            </div>  
                                
                        </div>

                        
                        
                       
                        <hr />
                        <button type="submit" class="btn btn-success" value="1" name="edittopic" >Update</button> 


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

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-helptopic" >Add new topic</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>



        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <div class="box-header with-border">                      
                    <h3 class="box-title">Help Topics </h3>
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
                                            echo "<a class='btn' href='help-topics.php?page=".$i."'>".$i."</a>";
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
                              <th style="">Excerpt</th>
                              <th style="">Category</th>
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
                              foreach($help_topics_page_items as $helptopicspageitems){
                                  $item_data = array('title'=>$helptopicspageitems['title'],'excerpt'=>$helptopicspageitems['excerpt'],'content'=>htmlentities($helptopicspageitems['content']),'cat_id'=>$helptopicspageitems['cat_id'],'show_web'=> $helptopicspageitems['show_web'],'show_rider'=> $helptopicspageitems['show_rider'],'show_driver'=> $helptopicspageitems['show_driver']);
                                  $item_data_json = json_encode($item_data, JSON_HEX_AMP);
                                  $show_web = !empty($helptopicspageitems['show_web']) && $helptopicspageitems['show_web'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  $show_rider = !empty($helptopicspageitems['show_rider']) && $helptopicspageitems['show_rider'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  $show_driver = !empty($helptopicspageitems['show_driver']) && $helptopicspageitems['show_driver'] == 1 ? "<i class='fa fa-circle' style='color:green;'></i>" : "<i class='fa fa-circle' style='color:red;'></i>";
                                  echo "<tr><td>". $count++ . "</td><td>" . $helptopicspageitems['help_topic_title']. "</td><td>" . $helptopicspageitems['excerpt']. "</td><td>" . $helptopicspageitems['cat_title']. "</td><td>" . $show_web. "</td><td>" . $show_rider. "</td><td>" . $show_driver . "</td><td>". date('l, M j, Y H:i:s',strtotime($helptopicspageitems['date_created'].' UTC')). "</td><td>". date('l, M j, Y H:i:s',strtotime($helptopicspageitems['date_modified'].' UTC')) . "</td><td>". "<a href='#' data-itemid='{$helptopicspageitems['id']}' class='edit-helptopic btn btn-success btn-margin-top btn-xs'>Edit</a> <a href='help-topics.php?action=del&id={$helptopicspageitems['id']}' data-itemid='{$helptopicspageitems['id']}' class='delete-item del-helptopic btn btn-danger btn-margin-top btn-xs'>Delete</a><span id='helptopicitemdata-{$helptopicspageitems['id']}' style='display:none;'>{$item_data_json}</span>" ."</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                    <?php if(!$number_of_help_topics){ echo "<h1 style='text-align:center;'>Nothing to Show. Add help topics to get this area populated.</h1>";} ?> 
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



  <script>

      var data;

      $(document).on('focusin', function(e) {
            if ($(e.target).closest(".mce-window").length) {
                e.stopImmediatePropagation();
            }
        });

      $('#edit-helptopic').on('show.bs.modal', function () {
        tinymce.activeEditor.setContent(decodeHtml(data.content));
        console.log(data.content);
        
      });


      $('.edit-helptopic').on('click', function(){
        var item_id = $(this).data('itemid');
        $('#ehelptopic-id').val(item_id);
        console.log($('#helptopicitemdata-'+item_id).html());
        data = JSON.parse($('#helptopicitemdata-'+item_id).html());
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

        $("#ehelptopic-cat [value='"+ data.cat_id +"']").prop("selected",true);

        $('#ehelptopic-title').val(data.title);
        $('#ehelptopic-excerpt').val(data.excerpt);
        
        $('#edit-helptopic').modal('show');
      });


      function decodeHtml(html) {
          var txt = document.createElement("textarea");
          txt.innerHTML = html;
          return txt.value;
      }


  </script>
    