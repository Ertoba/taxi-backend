
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Get an overview of all cars.
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
                      <span class="info-box-icon bg-yellow"><i class="fa fa-car"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Cars</span>
                        <span class="info-box-number"><?php echo $number_of_rides; ?></span>
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
                      
                    <h3 class="box-title">Cars <?php if($number_of_rides) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
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
                                        echo "<a class='btn' href='all-rides.php?page=".$i."'>".$i."</a>";
                                        }  
                                    
                                }
                            }
                        ?>
                    </div><!--/pages-->
                    <br />
                    <div class="table-responsive">
                      <table class='table table-bordered'>
                      <thead>
                          <tr>
                          <th>#</th>
                              <th style="">Car Image</th>    
                              <th style="">Car Name</th>
                              <th style="">Description</th>
                              <th style="">Map Icon</th>
                              <th style="">Seating capacity</th>
                              <th style="">Availability</th>
                              <th style="width:100px">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                              foreach($ride_page_items as $ridepageitems){
                                  $del_action = $ridepageitems['id'] == 1 ? "" : " <a data-msg='Deleting this car will delete all bookings, tariffs and drivers associated with it. Do you want to continue?' href='edit-ride.php?action=delete&id=".$ridepageitems['id'] ."' class='delete-item btn btn-danger btn-xs'>Delete</a>";
                                  $del_action = '';
                                  $icon_type = "<img style='width:32px;' src ='../img/ride-icon-types/city-driver-icon-{$ridepageitems['icon_type']}.png' />";
                                  $availability = !empty($ridepageitems['avail']) ? "<i class='fa fa-check-circle' style='color:green'></i>" : "<i class='fa fa-exclamation-circle' style='color:red'></i>";
                                  echo "<tr><td>". $count++ . "</td><td>"."<img src='".$ridepageitems['ride_img'] ."' height=32 /></td><td>" . $ridepageitems['ride_type']. "</td><td>" . $ridepageitems['ride_desc']. "</td><td>" . $icon_type . "</td><td>" . $ridepageitems['num_seats'] ."</td><td>". $availability ."</td><td>"."<a href='edit-ride.php?id=".$ridepageitems['id'] ."' class='btn btn-success btn-xs'>edit</a>" . $del_action ."</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   <?php if(!$number_of_rides){ echo "<h1 style='text-align:center;'>Nothing to Show. Add Cars to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    