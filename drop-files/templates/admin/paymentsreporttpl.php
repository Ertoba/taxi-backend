<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Get an overview of all payments.
        </div>
    </div>
    <div class="col-sm-12">   
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">PAYMENTS STATS</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                <br />

                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-blue"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">All Completed Bookings Amount</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_completed_booking_cost); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-green"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Successful Wallet funding (online)</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_completed_transaction_cost); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-yellow"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Wallet funding (Deposit)</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_wallet_funding); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-grey"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Payouts</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_payouts); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                    
                    <div class="clearfix"></div>
                 <hr />                  
                 
                              





                </div><!-- /.box-body -->
            </div>
    </div><!--/col-sm-12-->
</div><!--/row-->





 <div class="row">
    <div class="col-sm-12">

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Payment Period / Scope </h3>                            
            </div>
            <div class="box-body">
                <p>Please select a month, year and scope to display payment data</p>
                <form  enctype="multipart/form-data" class="form-inline" id="payment-record-query-form" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                     <label for="reg-period-month">Select Month &nbsp;</label>
                     <select class="form-control" name="reg-period-month">
                         <?php
                            $count = 0;
                            
                            foreach($month_names as $monthnames){
                                $count ++;
                                $current_month = date("F",time());
                                $selected = "";
                                $monthnames_sel = "";
                                

                                if(isset($_POST['reg-period-month'])){
                                    $selected = '';
                                    if($count == $_POST['reg-period-month']){
                                        $selected = "selected";
                                    }

                                }



                            echo "<option {$monthnames_sel} value='{$count}' {$selected}>{$monthnames}</option>";
                                /* if($monthnames == $current_month && $_POST['reg-period-year'] == (int) date("Y")){
                                    break;

                                } */

                            }
                        ?>
                    </select>
                    &nbsp;   
                    <label for="reg-period-year">Select Year &nbsp;</label>
                    <select class="form-control" name="reg-period-year">
                         <?php
                            $count = 0;
                            if($oldest_reg_year){
                                $old_reg_year = (int) $oldest_reg_year;
                                $selected = '';
                                $year_sel = '';
                                while($old_reg_year <= (int) date("Y")){
                                    if($old_reg_year == (int) date("Y")){
                                        $year_sel = "style='font-weight:bold'";
                                        $selected = "selected";
                                    }
                                    if(isset($_POST['reg-period-year'])){
                                        $selected = '';
                                        if($old_reg_year == (int) $_POST['reg-period-year']){
                                            $selected = "selected";
                                        }


                                    }    

                                    echo "<option {$year_sel} value='{$old_reg_year}' {$selected} >{$old_reg_year}</option>";
                                    $old_reg_year++;
                                }
                            }
                        ?>
                    </select>
                    &nbsp; 


                     <label for="scope">Select Scope &nbsp;</label>
                    <select class="form-control" id="scope" name="scope">
                        <option value="1" <?php echo isset($_POST['scope']) && $_POST['scope'] == 1 ? 'selected':'' ?> >City</option>
                        <option value="2" <?php echo isset($_POST['scope']) && $_POST['scope'] == 2 ? 'selected':'' ?> >Customer</option>
                        <option value="3" <?php echo isset($_POST['scope']) && $_POST['scope'] == 3 ? 'selected':'' ?> >Driver</option>
                        <option value="4" <?php echo isset($_POST['scope']) && $_POST['scope'] == 4 ? 'selected':'' ?> >Franchise</option>                         
                    </select>
                    &nbsp; 


                    
                     <button type="submit" class="btn btn-primary" value="1" name="submit-btn" id="submit-btn" >Ok</button>

                     <br>
                    
                     <div id="city-scope">
                         <hr>
                        <label for="report-city">Select City &nbsp;</label>
                        <select class="form-control" id="report-city" name="report-city">
                            <option value="">---</option>
                            <?php
                                        
                                
                                foreach($inter_city_routes as $intercityroutes){
                                    $city_sel = '';
                                    $selected = '';
                                    if(isset($_POST['report-city'])){
                                        
                                        if($intercityroutes['id'] == (int) $_POST['report-city']){
                                            $selected = "selected";
                                            $city_sel = "style='font-weight:bold'";
                                        }
        
        
                                    }else{
                                        $selected = "selected";
                                        $city_sel = "style='font-weight:bold'";
                                    }  
                                    echo "<option {$city_sel} value='{$intercityroutes['id']}' {$selected} >{$intercityroutes['r_title']}</option>";
                                }
                                
                            ?>
                        </select>
                   
                     </div>

                    <div id="customer-scope" style='display:none'>
                        <hr>
                        <label>Customer name &nbsp;</label>
                        <input  type="text" class="form-control" id="booking-customer" placeholder="" name="customer-scope-name" value="<?php echo isset($_POST["customer-scope-name"]) ? $_POST["customer-scope-name"] : ''; ?>" >
                        <input  type="text" hidden='hidden' id="booking-customerid" placeholder="" name="customer-scope-id" value="<?php echo isset($_POST["customer-scope-id"]) ? $_POST["customer-scope-id"] : ''; ?>" >
                    </div>
                    
                     <div id="driver-scope" style='display:none'>
                        <hr>
                        <label>Driver name &nbsp;</label>
                        <input  type="text" class="form-control" id="booking-driver" placeholder="" name="driver-scope-name" value="<?php echo isset($_POST["driver-scope-name"]) ? $_POST["driver-scope-name"] : ''; ?>" >
                        <input  type="text" hidden='hidden' id="booking-driverid" placeholder="" name="driver-scope-id" value="<?php echo isset($_POST["driver-scope-id"]) ? $_POST["driver-scope-id"] : ''; ?>" >
                    </div>

                     <div id="franchise-scope" style='display:none'>
                        <hr>
                        <label>Franchise name &nbsp;</label>
                        
                        <select class="form-control" id="franchise-scope-name" name="franchise-scope-name">
                            <option value="">Franchise</option> 
                            <?php 
                                $select = '';
                                foreach($franchise_data as $franchisedata){
                                $select = !empty($_POST['franchise-scope-name']) && ($_POST['franchise-scope-name'] == $franchisedata['id']) ? "selected" : '';  
                            ?>   
                            <option value="<?php echo $franchisedata['id'] ?>" <?php echo $select; ?> ><?php echo $franchisedata['franchise_name'] ?></option>  
                            <?php } ?>                                            
                        </select>

                        <input  type="text" hidden='hidden' id="franchise-scope-id" placeholder="" name="franchise-scope-id" value="<?php echo isset($_POST["franchise-scope-id"]) ? $_POST["franchise-scope-id"] : ''; ?>" >
                    </div>




                </form>
                <hr />

               
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-blue"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">All Completed Bookings Amount</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_completed_booking_cost_month); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-green"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Successful Wallet funding (online)</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_completed_transaction_cost_month); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-yellow"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Wallet funding (Deposit)</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_wallet_funding_month); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-grey"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Payouts</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_payouts_month); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                <div class="col-md-4 col-sm-4 col-xs-12" <?php echo !empty($_POST['scope']) &&  ($_POST['scope'] == 3 || $_POST['scope'] == 4) ? "" : "style='display:none;'"; ?>>
                    <div class="info-box" title="">
                        <span class="info-box-icon bg-red"><i class="fa  fa-money"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Earning</span>
                            <span class="info-box-number"><?php echo $default_currency_symbol.floattocurrency($sum_all_completed_booking_earning_month); ?></span>
                        </div><!-- /.info-box-content -->
                    </div><!-- /.info-box -->
                </div>


                    
                    <div class="clearfix"></div>
                 <hr />   


                <div class="chart">
                    <canvas id="lineChart" style="height: 250px; width: 510px;" height="250" width="510"></canvas>
                </div>

                <div class="clearfix"></div>
        <hr />  

        <div class="table-responsive">
            <table class="table table-responsive table-bordered table-condensed" style="font-size:11px;">
                <thead>
                    <th>Date</th>
                    <th>Bookings completed</th>
                    <th>Wallet Funding(Online)</th>
                    <th>Wallet Funding(Deposit)</th>
                    <th>Payouts</th>
                    <?php echo !empty($_POST['scope']) &&  ($_POST['scope'] == 3 || $_POST['scope'] == 4) ? "<th>Earning</th>" : ""; ?>
                    
                </thead>

                <tbody>
                    <?php
                        $dates_asc = array_reverse($dates);
                        $earning_column = '';
                        foreach($dates_asc as $datesasc){
                            if(!empty($_POST['scope']) &&  ($_POST['scope'] == 3 || $_POST['scope'] == 4)){
                                $earning_column = "<td>{$payment_data_bookings_completed_earnings_sort[$datesasc]}</td>";
                            }
                            echo "<tr><td>{$datesasc}</td><td>{$payment_data_bookings_completed_sort[$datesasc]}</td><td>{$payment_data_online_sort[$datesasc]}</td><td>{$payment_data_wallet_fund_sort[$datesasc]}</td><td>{$payment_data_payouts_sort[$datesasc]}</td>{$earning_column}</tr>";
                        }
                    ?>
                </tbody>
            </table>


        </div>

            </div>
            <!-- /.box-body -->
        </div>

         




    </div>
</div>













<?php

    $date_label = implode(",",array_reverse($dates_label));
    $data = implode(",",array_reverse($payment_data_bookings_completed_sort));


    
?>




<script>
    

jQuery(function () {

        var opt_val =  jQuery("#scope").find(':selected').val();
        
        if(opt_val == 2){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").show();
            jQuery("#franchise-scope").hide();
        }else if(opt_val == 3){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").show();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").hide();
        }else if(opt_val == 4){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").show();
        }else{
            jQuery("#city-scope").show();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").hide();
        }


    $('#submit-btn').on('click', function(e){
        e.preventDefault();
        var opt_val =  jQuery("#scope").find(':selected').val();
        var imgurl = '../img/info_.gif?a=' + Math.random();
        if(opt_val == 1 && !$('#report-city').val()){
            swal({
                    title: '<h1>Error</h1>',
                    text: 'Please select a city from the list.',
                    imageUrl:imgurl,
                    html:true
            });
            return;
        }else if(opt_val == 2 && !$('#booking-customerid').val()){
            swal({
                    title: '<h1>Error</h1>',
                    text: 'No customer selected! Please select a customer from the autocomplete dropdown list while entering the customer name.',
                    imageUrl:imgurl,
                    html:true
            });
            return;
        }else if(opt_val == 3 && !$('#booking-driverid').val()){
            swal({
                    title: '<h1>Error</h1>',
                    text: 'No driver selected! Please select a driver from the autocomplete dropdown list while entering the driver name.',
                    imageUrl:imgurl,
                    html:true
            });
            return;
        }else if(opt_val == 4 && !$('#franchise-scope-name').val()){
            swal({
                    title: '<h1>Error</h1>',
                    text: 'Please select a franchise from the list.',
                    imageUrl:imgurl,
                    html:true
            });
            return;
        }
        $('#payment-record-query-form')[0].submit();
    })  

    jQuery('#scope').on('change', function(){
        
        var opt_val =  jQuery("#scope").find(':selected').val();
        
        if(opt_val == 2){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").show();
            jQuery("#franchise-scope").hide();
        }else if(opt_val == 3){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").show();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").hide();
        }else if(opt_val == 4){
            jQuery("#city-scope").hide();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").show();
        }else{
            jQuery("#city-scope").show();
            jQuery("#driver-scope").hide();
            jQuery("#customer-scope").hide();
            jQuery("#franchise-scope").hide();
        }

        
    });

    var areaChartData = {
      labels: [<?php echo $date_label;?>],
      datasets: [
        
        {
          label: "Digital Goods",
          fillColor: "rgba(60,141,188,0.9)",
          strokeColor: "rgba(60,141,188,0.8)",
          pointColor: "#3b8bba",
          pointStrokeColor: "rgba(60,141,188,1)",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(60,141,188,1)",
          data: [<?php echo $data;?>]
        }
      ]
    };



    var areaChartOptions = {
      //Boolean - If we should show the scale at all
      showScale: true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines: true,
      //String - Colour of the grid lines
      scaleGridLineColor: "rgba(0,0,0,.05)",
      //Number - Width of the grid lines
      scaleGridLineWidth: 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines: true,
      //Boolean - Whether the line is curved between points
      bezierCurve: false,
      //Number - Tension of the bezier curve between points
      bezierCurveTension: 0.3,
      //Boolean - Whether to show a dot for each point
      pointDot: true,
      //Number - Radius of each point dot in pixels
      pointDotRadius: 4,
      //Number - Pixel width of point dot stroke
      pointDotStrokeWidth: 1,
      //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
      pointHitDetectionRadius: 20,
      //Boolean - Whether to show a stroke for datasets
      datasetStroke: true,
      //Number - Pixel width of dataset stroke
      datasetStrokeWidth: 2,
      //Boolean - Whether to fill the dataset with a color
      datasetFill: true,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
      //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio: true,
      //Boolean - whether to make the chart responsive to window resizing
      responsive: true
    };


    var lineChartCanvas = jQuery("#lineChart").get(0).getContext("2d");
    var lineChart = new Chart(lineChartCanvas);
    var lineChartOptions = areaChartOptions;
    lineChartOptions.datasetFill = false;
    lineChart.Line(areaChartData, lineChartOptions);



       


});


</script>