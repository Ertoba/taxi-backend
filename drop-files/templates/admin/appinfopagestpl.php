
<div class="row">
  <div class="col-sm-12">
      <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Modify service Information displayed on the Rider and Driver Apps.
      </div>
  </div>             

</div> <!--/row-->



<div class="modal fade" id="edit-appinfo" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Edit App Info Page</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="appinfo-id" hidden="hidden" name="appinfo-id" value="0" />                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Page</p>
                                <input  type="text" disabled="disabled" class="form-control" id="appinfo-title" placeholder="" name="appinfo-title" value="" > 
                            </div>  
                                
                        </div>

                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Description</p>
                                <input  type="text" disabled="disabled" class="form-control" id="appinfo-excerpt" placeholder="" name="appinfo-excerpt" value="" > 
                            </div>  
                                
                        </div>



                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Content</p>
                                <textarea required= "required" class="textformat" id="appinfo-content" placeholder="" name="appinfo-content"></textarea> 
                            </div>  
                                
                        </div>

                        
                        
                       
                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="updateinfo" >Update</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>



        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <!-- <div class="box-header with-border">
                      
                    <h3 class="box-title"> </h3>
                  </div> --><!-- /.box-header -->
                <div class="box-body">
                  <br />
                    
                    <br />
                    <div class="table-responsive">
                      <table class='table table-bordered table-striped'>
                      <thead>
                          <tr>
                          <th>#</th>    
                              <th>Page</th>
                              <th>Description</th>
                              <th>Date Created</th>
                              <th>Date Modified</th>
                              <th style="width:100px">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1;
                              foreach($appinfo_page_items as $appinfopageitems){
                                  $item_data = array('title'=>$appinfopageitems['title'],'excerpt'=>$appinfopageitems['excerpt'],'content'=>htmlentities($appinfopageitems['content']));
                                  $item_data_json = json_encode($item_data, JSON_HEX_AMP);
                                  echo "<tr><td>". $count++ . "</td><td>" . $appinfopageitems['title']. "</td><td>" . $appinfopageitems['excerpt'] . "</td><td>". date('l, M j, Y H:i:s',strtotime($appinfopageitems['date_created'].' UTC')). "</td><td>". date('l, M j, Y H:i:s',strtotime($appinfopageitems['date_modified'].' UTC')) . "</td><td>". "<a href='#' data-itemid='{$appinfopageitems['id']}' class='edit-appinfo btn btn-success btn-xs'>Edit</a><span id='appinfoitemdata-{$appinfopageitems['id']}' style='display:none;'>{$item_data_json}</span>" ."</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   
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

      $('#edit-appinfo').on('show.bs.modal', function () {
        //$('#appinfo-content').html(data.content);
        tinymce.activeEditor.setContent(decodeHtml(data.content));
        
        //console.log(data.content);
        
      });


      


      $('.edit-appinfo').on('click', function(){
        var item_id = $(this).data('itemid');
        $('#appinfo-id').val(item_id);
        data = JSON.parse($('#appinfoitemdata-'+item_id).html());
        $('#appinfo-title').val(data.title);
        $('#appinfo-excerpt').val(data.excerpt);
        
        $('#edit-appinfo').modal('show');
      })


      function decodeHtml(html) {
          var txt = document.createElement("textarea");
          txt.innerHTML = html;
          return txt.value;
      }


  </script>
    