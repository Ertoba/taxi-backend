
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Get an overview of all zones.
                  </div>
              </div>

              <div class="col-sm-12">   
                
                <div class="box box-default">
                  <div class="box-header with-border">
                    <h3 class="box-title">STATS</h3>
                  </div><!-- /.box-header -->
                  <div class="box-body">
                    <br />

                    <div class="col-md-3 col-sm-6 col-xs-12">
                      <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fa fa-plane"></i></span>

                        <div class="info-box-content">
                          <span class="info-box-text">Zones</span>
                          <span class="info-box-number"><?php echo $number_of_zones; ?></span>
                        </div>
                        <!-- /.info-box-content -->
                      </div>
                      <!-- /.info-box -->
                    </div>
                    
                  </div><!-- /.box-body -->
                </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <div class="box-header with-border">
                      
                    <h3 class="box-title">Zones <?php if($number_of_zones) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
                  </div><!-- /.box-header -->
                <div class="box-body">
                  <br />
                    <div> <!--pages-->
                   
                        <?php
                            if(!empty($pages)){
                                echo " Pages: ";
                                for($i = 1;$i < $pages + 1; $i++){
                                    if($i == $page_number){
                                        echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                    }else{
                                        echo "<a class='btn' href='all-zones.php?page=".$i."'>".$i."</a>";
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
                              <th>Zone Name</th>
                              <th>Zone City</th>
                              <th>Fare type</th>
                              <th>Fare Value</th>                              
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);

                              foreach($zone_page_items as $zone_page_item){
                                    $edit_action = "<a class='btn btn-xs btn-success' href='edit-zone.php?action=edit&id={$zone_page_item['id']}'>Edit</a>"; 
                                    $fare_type =  $zone_page_item['zone_fare_type'] == 1 ? "Multiplier" : "Additional";

                                    if($zone_page_item['zone_fare_type'] == 1){
                                        $fare_type = "Multiplier";
                                        $fare_value = $zone_page_item['zone_fare_value'] . "X";
                                    }else{
                                        $fare_type = "Additional";
                                        $fare_value = "+" . $zone_page_item['symbol'].$zone_page_item['zone_fare_value'];
                                    }

                                  $del_action = " <a href='edit-zone.php?action=delete&id=".$zone_page_item['id'] ."' data-msg='This zone will be deleted.Do you want to continue?' class='delete-item btn btn-danger btn-xs'>Delete</a>";
                                  echo "<tr><td>". $count++ . "</td><td>" . $zone_page_item['title']. "</td><td>" . $zone_page_item['r_title'] ."</td><td>". $fare_type ."</td><td>". $fare_value . "</td><td>". $edit_action . " " . $del_action ."</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   <?php if(!$number_of_zones){ echo "<h1 style='text-align:center;'>Nothing to Show. Add zones to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    