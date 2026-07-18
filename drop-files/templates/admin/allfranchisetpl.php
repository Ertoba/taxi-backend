
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Get an overview of all franchises.
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
                        <span class="info-box-icon bg-yellow"><i class="fa fa-briefcase"></i></span>

                        <div class="info-box-content">
                          <span class="info-box-text">Franchises</span>
                          <span class="info-box-number"><?php echo $number_of_franchise; ?></span>
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
                      
                    <h3 class="box-title">Franchises <?php if($number_of_franchise) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
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
                                        echo "<a class='btn' href='all-franchise.php?page=".$i."'>".$i."</a>";
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
                              <th>Franchise Name</th>
                              <th>Description</th>
                              <th>Wallet Amount</th>
                              <th>Number of Drivers</th>
                              <th>Date Created</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                          $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";

                              foreach($franchise_page_items as $franchisepageitems){
                                  $del_action = $franchisepageitems['id'] == 1 ? "" : " <a href='edit-franchise.php?action=delete&id=".$franchisepageitems['id'] ."' data-msg='This franchise will be deleted and all drivers on the franchise will be moved to the default franchise or company.Do you want to continue?' class='delete-item btn btn-danger btn-xs'>Delete</a>";
                                  //$del_action = '';
                                  $owner = $franchisepageitems['id'] == 1 ? "*" : "";
                                  $num_of_drivers = !empty($number_of_franchise_drivers[$franchisepageitems['id']]['COUNT(*)']) ? $number_of_franchise_drivers[$franchisepageitems['id']]['COUNT(*)'] : 0;
                                  echo "<tr><td>". $count++ . "</td><td>" . $owner . $franchisepageitems['franchise_name']. "</td><td>" . $franchisepageitems['franchise_desc'] ."</td><td>". $default_currency_symbol .$franchisepageitems['fwallet_amount'] ."</td><td>". $num_of_drivers . "</td><td>" . date('l, M j, Y H:i:s',strtotime($franchisepageitems['date_created'].' UTC')) . "</td><td>"."<a href='edit-franchise.php?id=".$franchisepageitems['id'] ."' class='btn btn-success btn-xs'>edit</a>" . $del_action ." <a href='view-franchise.php?id=".$franchisepageitems['id'] ."' class='btn btn-primary btn-xs'>View</a> </td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   <?php if(!$number_of_franchise){ echo "<h1 style='text-align:center;'>Nothing to Show. Add Franchises to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    