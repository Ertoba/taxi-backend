
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Get an overview of users transactions.
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
                      <span class="info-box-icon bg-yellow"><i class="fa fa-drivers-license"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Transactions</span>
                        <span class="info-box-number"><?php echo $num_of_transactions; ?></span>
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
                      
                    <h3 class="box-title">Details <?php if($num_of_transactions) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
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
                                            $url = $_SERVER['REQUEST_URI'];
                                            $url_parts = parse_url($url);
                                            if(isset($url_parts['query'])){
                                                parse_str($url_parts['query'], $params);
                                            }
                                            $params['page'] = $i;     // Overwrite if exists
                                            $url_parts['query'] = http_build_query($params);
                                          
                                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'>".$i."</a>";
                                            
                                        }  
                                    
                                }
                            }
                        ?>
                    </div><!--/pages-->
                    <br />
                    <table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Trans.Ref</th>
                            <th>Total</th>     
                            <th style="">Total Paid by Customer</th>
                            <th style="">Total Credited to Merchant</th>
                            <th style="">Extra Charges by Merchant</th>
                            <th style="">Memo</th>
                            <th style="">Status</th>
                            <th style="">Date</th>
                            <th style="">Fund Maturity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                        
                        foreach($transaction_data as $transactiondata){
                            echo "<tr><td>{$count}</td><td>{$transactiondata['v_transaction_id']}</td><td>{$transactiondata['total']}</td><td>{$transactiondata['total_paid_by_buyer']}</td><td>{$transactiondata['total_credited_to_merchant']}</td><td>{$transactiondata['extra_charges_by_merchant']}</td><td>{$transactiondata['memo']}</td><td>{$transactiondata['status']}</td><td>{$transactiondata['date']}</td><td>{$transactiondata['fund_maturity']}</td></tr>";
                            $count++;
                        }
                       
                        ?>
                    </tbody>
                    </table>
                                  
                    <?php if(!$num_of_transactions){ echo "<h1 style='text-align:center;'>No transaction records.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    