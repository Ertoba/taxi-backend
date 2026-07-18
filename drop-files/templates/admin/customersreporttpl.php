<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Get an overview of users registrations.
        </div>
    </div>
    <div class="col-sm-12">   
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">REGISTRATIONS STATS</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                <br />

                <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="info-box" title="Total number of users registered">
                            <span class="info-box-icon bg-blue"><i class="fa  fa-users"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Total Registrations</span>
                                <span class="info-box-number"><?php echo $total_num_of_users; ?></span>
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
                <h3 class="box-title">Registration Period data</h3>                            
            </div>
            <div class="box-body">
                <p>Please select a city, month and year to display user registration data</p>
                <form  enctype="multipart/form-data" class="form-inline" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                  
                    <label for="reg-city">Select City &nbsp;</label>
                    <select class="form-control" name="reg-city">
                         <?php
                                     
                            
                            foreach($inter_city_routes as $intercityroutes){
                                $city_sel = '';
                                $selected = '';
                                if(isset($_POST['reg-city'])){
                                    
                                    if($intercityroutes['id'] == (int) $_POST['reg-city']){
                                        $selected = "selected";
                                        $city_sel = "style='font-weight:bold'";
                                    }
    
    
                                }  
                                echo "<option {$city_sel} value='{$intercityroutes['id']}' {$selected} >{$intercityroutes['r_title']}</option>";
                            }
                            
                        ?>
                    </select>
                    &nbsp;

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


                    


                     <button type="submit" class="btn btn-primary" value="1" name="reg-period" >Ok</button>
                </form>
                <hr />

                <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="info-box"  title="Number of registrations for selected month ">
                            <span class="info-box-icon bg-grey"><i class="fa  fa-users"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Month's Total Registrations</span>
                                <span class="info-box-number"><?php echo $month_trend_data['month_total']; ?></span>
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
                    <th>Number of Registrations</th>
                </thead>

                <tbody>
                    <?php
                        $month_totals = array_reverse($month_trend_data);
                        
                        foreach($month_totals as $key => $monthtotal){
                            if($key == "month_total")continue; 
                            //if(empty($monthtotal['num_of_users']))continue;                            
                            $date_reg = str_replace('"',"",$monthtotal['date']);                     
                            echo "<tr><td>{$date_reg}</td><td>{$monthtotal['num_of_users']}</td></tr>";
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

    $date_label = implode(",",array_reverse(array_column($month_trend_data,"date")));
    $data = implode(",",array_reverse(array_column($month_trend_data,"num_of_users")));


    
?>




<script>

jQuery(function () {

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