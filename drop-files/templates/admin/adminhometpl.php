

<div class="row">	
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Activity</h3>       
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#b3e5fc;color:black;"><i class="fa fa-user-plus"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">New users</span>
                        <span id="new-customers" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#e1bee7;color:black;"><i class="fa fa-user"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Daily active users</span>
                        <span id="daily-active-customers" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#d7ccc8;color:black;"><i class="fa fa-drivers-license"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">New Drivers</span>
                        <span id = "new-drivers" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#cfd8dc;color:black;"><i class="fa fa-bolt"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Drivers Online</span>
                        <span id = "drivers-available" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>



                            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>



<div class="row">
    <div class="col-sm-12">   
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Today's bookings</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                <br />

                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="chart" style="margin-bottom: 20px;">
                            <i id="placeholder-pie-chart" class="fa fa-pie-chart" style="position: absolute;font-size: 150px;color: #f2e7f4;top: 50%;left: 50%;transform: translate(-50%,-50%);"></i>
                            <div id="chartjs-legend" class="chart-legend" style="position: absolute;top: 0;left: 0;"></div>
                            <canvas id="pieChart" style="height: 250px; width: 510px;" height="250" width="510"></canvas>
                        </div>        
                    </div>


                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class=row>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon" style="background-color:#c5cae9;color:black;"><i class="fa fa-flag-checkered"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Trips completed</span>
                                    <span id = "trips-completed" class="info-box-number">---</span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon" style="background-color:#b3e5fc;color:black;"><i class="fa fa-car"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Rider cancelled</span>
                                    <span id = "trips-cancelled-rider" class="info-box-number">---</span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon" style="background-color:#fff9c4;color:black;"><i class="fa fa-car"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Driver cancelled</span>
                                    <span id = "trips-cancelled-driver" class="info-box-number">---</span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>


                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon" style="background-color:#ffccbc;color:black;"><i class="fa fa-car"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">No driver</span>
                                    <span id = "trips-cancelled-no-driver-available" class="info-box-number">---</span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>


                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon" style="background-color:#dcedc8;color:black;"><i class="fa fa-car"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">All bookings</span>
                                    <span id = "new-bookings" class="info-box-number">---</span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>
                        </div>



                        
                    </div>
                    
                    
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="chart" style="margin-top: 30px;">
                            <h4>Bookings Trend</h4>
                            <canvas id="lineChartbt" style="height: 400px; width: 510px;"></canvas>
                        </div>
                    </div>


                </div><!-- /.box-body -->
            </div>
    </div><!--/col-sm-12-->
</div><!--/row-->




<div class="row">	
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Today's financials</h3>       
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#eeeeee;color:black;"><i class="fa fa-line-chart"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Earnings</span>
                        <span id = "todays-earnings" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#b3e5fc;color:black;"><i class="fa fa-line-chart"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Company earning</span>
                        <span id="company-earning" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#ffecb3;color:black;"><i class="fa fa-line-chart"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Drivers earning</span>
                        <span id="drivers-earning" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>


                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon" style="background-color:#cfd8dc;color:black;"><i class="fa fa-tags"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Discounts</span>
                        <span id = "trips-discounts" class="info-box-number">---</span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>



                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="chart" style="margin-top: 30px;">
                        <h4>Earnings trend</h4>
                        <canvas id="lineChart" style="height: 250px; width: 510px;" height="250" width="510"></canvas>
                    </div>
                </div>


                





      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>



<div class="row">	
    <div class="col-sm-12" >
		<div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Daily Active Users Trend</h3>       
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="col-md-3 col-sm-6 col-xs-12" >
                    <div class="info-box">
                        <span class="info-box-icon" style="background-color:#dcedc8;color:black;"><i class="fa fa-level-up"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Maximum</span>
                            <span id = "maximum-dau" class="info-box-number">---</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12" >
                    <div class="info-box">
                        <span class="info-box-icon" style="background-color:#ffccbc;color:black;"><i class="fa fa-level-down"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Minimum</span>
                            <span id="minimum-dau" class="info-box-number">---</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>



                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="chart" style="margin-top: 30px;">
                        <!-- <h4>Active Users trend</h4> -->
                        <canvas id="lineChartDau" style="height: 250px; width: 510px;" height="250" width="510"></canvas>
                    </div>
                </div>

      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>



<div class="row">	
    <div class="col-sm-12" >
		<div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Daily Online Drivers Trend</h3>       
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="col-md-3 col-sm-6 col-xs-12" >
                    <div class="info-box">
                        <span class="info-box-icon" style="background-color:#dcedc8;color:black;"><i class="fa fa-level-up"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Maximum</span>
                            <span id = "maximum-ond" class="info-box-number">---</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12" >
                    <div class="info-box">
                        <span class="info-box-icon" style="background-color:#ffccbc;color:black;"><i class="fa fa-level-down"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Minimum</span>
                            <span id="minimum-ond" class="info-box-number">---</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>



                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="chart" style="margin-top: 30px;">
                        <!-- <h4>Online Drivers trend</h4> -->
                        <canvas id="lineChartOnd" style="height: 250px; width: 510px;" height="250" width="510"></canvas>
                    </div>
                </div>

      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>




<?php

    if(!empty($_SESSION['action_success'])){
        $msgs = '';
        foreach($_SESSION['action_success'] as $action_success){
            $msgs .= "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> ".$action_success . "</p>";
        }
    
        $cache_prevent = RAND();
        echo"<script>
        setTimeout(function(){ 
                jQuery( function(){
                swal({
                    title: '<h1>Success</h1>'".',
        text:"'.$msgs .'",'.
        "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
        html:true,
                });
                });
                },500); 
                
                </script>";
    
            unset($_SESSION['action_success']);
    
    }elseif(!empty($_SESSION['action_error'])){
            $msgs = '';
            foreach($_SESSION['action_error'] as $action_error){
                $msgs .= "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> ".$action_error . "</p>";
            }
    
            $cache_prevent = RAND();
            echo"<script>
        setTimeout(function(){ 
                jQuery( function(){
                swal({
                    title: '<h1>Error</h1>'".',
        text:"'.$msgs .'",'.
        "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
        html:true,
                });
                });
                },500); 
                
                </script>";
        
                unset($_SESSION['action_error']);
        
    }
    
?>
           



<script>

    var num_bookings_completed = 0;
    var num_bookings_cancelled_rider = 0;
    var num_bookings_cancelled_driver = 0;
    var num_bookings_cancelled_no_driver = 0;
    var pieChart;
    var lineChart;
    var lineChartDau;
    var lineChartOnd;
    var barChartBT;

    $(function (){

        var pieChartCanvas = jQuery("#pieChart").get(0).getContext("2d");
        
        var PieData = [
                        {
                            value: num_bookings_completed,
                            color: '#311b92',
                            highlight: '#311b92',
                            label: 'Completed'
                        },
                        {
                            value: num_bookings_cancelled_rider,
                            color: '#29b6f6',
                            highlight: '#4fc3f7',
                            label: 'Rider cancelled'
                        },
                        {
                            value: num_bookings_cancelled_driver,
                            color: '#ffeb3b',
                            highlight: '#ffee58',
                            label: 'Driver cancelled'
                        },
                        {
                            value: num_bookings_cancelled_no_driver,
                            color: '#ff5722',
                            highlight: '#ff7043',
                            label: 'No driver'
                        }
                
        
        ];
        var pieOptions = {
        //Boolean - Whether we should show a stroke on each segment
        segmentShowStroke: true,
        //String - The colour of each segment stroke
        segmentStrokeColor: "#fff",
        //Number - The width of each segment stroke
        segmentStrokeWidth: 2,
        //Number - The percentage of the chart that we cut out of the middle
        percentageInnerCutout: 40, // This is 0 for Pie charts
        //Number - Amount of animation steps
        animationSteps: 100,
        //String - Animation easing effect
        animationEasing: "easeOutBounce",
        //Boolean - Whether we animate the rotation of the Doughnut
        animateRotate: true,
        //Boolean - Whether we animate scaling the Doughnut from the centre
        animateScale: false,
        //Boolean - whether to make the chart responsive to window resizing
        responsive: true,
        // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        maintainAspectRatio: true,
        //String - A legend template
        //legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>"
        legendTemplate: "<ul style=\"list-style: none;padding:0;\" class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"width: 10px;display: inline-block;height: 10px;vertical-align: middle;margin-right: 5px;border-radius: 50%;background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>"
        
        };
        //Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        pieChart = new Chart(pieChartCanvas).Pie(PieData,pieOptions);
        document.getElementById('chartjs-legend').innerHTML = pieChart.generateLegend();
        //pieChart.Doughnut(PieData, pieOptions);
        //pieChart = new Chart(ctx[0]).Pie(data,options);
        //document.getElementById('chartjs-legend').innerHTML = pieChart.generateLegend();
        <?php
            $w_end_date = gmdate('Y-m-d 23:59:00', strtotime('now'));
            $w_start_date = gmdate('Y-m-d 00:00:00', strtotime($w_end_date . "- 6 days"));
        
            $w_start_date_components = explode("-",explode(" ", $w_start_date)[0]);
            $w_end_date_components = explode("-",explode(" ", $w_end_date)[0]);
        
            $start_year = $w_start_date_components[0];
            $start_month = $w_start_date_components[1];
            $start_day = $w_start_date_components[2];
        
        
            $end_year = $w_end_date_components[0];
            $end_month = $w_end_date_components[1];
            $end_day = $w_end_date_components[2];
        
        
            $dates_data = [];
            if($start_day > $end_day){
                $start_month_num_days = (int) date('t', strtotime("{$start_year}-{$start_month}-01"));
                for($x = intVal($start_day);$x <= $start_month_num_days;$x++){
                    $day_formatted = $x < 10 ? "0{$x}" : $x;
                    $dates_data[] = "{$start_year}-{$start_month}-{$day_formatted}";        
                }
        
                for($y = 1;$y <= intVal($end_day);$y++){
                    $day_formatted = $y < 10 ? "0{$y}" : $y;
                    $dates_data[] = "{$end_year}-{$end_month}-{$day_formatted}"; 
                }
            }else{
                for($z = intVal($start_day);$z <= intVal($end_day);$z++){
                    $day_formatted = $z < 10 ? "0{$z}" : $z;
                    $dates_data[] = "{$start_year}-{$start_month}-{$day_formatted}"; 
                }
            }

            $dates_data_str = '';

            foreach($dates_data as $datesdata){
                $dates_data_str .= '"' . $datesdata . '",';
            }

        ?>


        var areaChartData = {
        /* labels: ["---","---","---","---","---","---","---"], */
        labels: [<?php echo $dates_data_str ?>],
        datasets: [
            
            {
                label: "",
                fillColor: "rgba(49,27,146,1)",
                strokeColor: "rgba(49,27,146,1)",
                pointColor: "rgba(49,27,146,1)",
                pointStrokeColor: "rgba(49,27,146,1)",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(49,27,146,1)",
                data: [0,0,0,0,0,0,0]
            }
            ]
        };


        var areaChartData2 = {
        /* labels: ["---","---","---","---","---","---","---"], */
        labels: [<?php echo $dates_data_str ?>],
        datasets: [
            
            {
                label: "",
                fillColor: "rgb(255,235,59)",
                strokeColor: "rgba(255,235,59,1)",
                pointColor: "rgba(255,235,59,1)",
                pointStrokeColor: "rgba(255,235,59,1)",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(255,235,59,1)",
                data: [0,0,0,0,0,0,0]
            }
            ]
        };


        var areaChartData3 = {
        /* labels: ["---","---","---","---","---","---","---"], */
        labels: [<?php echo $dates_data_str ?>],
        datasets: [
            
            {
                label: "",
                fillColor: "rgba(41,182,246,1)",
                strokeColor: "rgba(41,182,246,1)",
                pointColor: "rgba(41,182,246,1)",
                pointStrokeColor: "rgba(41,182,246,1)",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(41,182,246,1)",
                data: [0,0,0,0,0,0,0]
            }
            ]
        };

        /**
         * {
                            value: num_bookings_completed,
                            color: '#311b92',
                            highlight: '#311b92',
                            label: 'Completed'
                        },
                        {
                            value: num_bookings_cancelled_rider,
                            color: '#29b6f6',
                            highlight: '#4fc3f7',
                            label: 'Rider cancelled'
                        },
                        {
                            value: num_bookings_cancelled_driver,
                            color: '#ffeb3b',
                            highlight: '#ffee58',
                            label: 'Driver cancelled'
                        },
                        {
                            value: num_bookings_cancelled_no_driver,
                            color: '#ff5722',
                            highlight: '#ff7043',
                            label: 'No driver'
                        }
         */


        var bchartData = {
        /* labels: ["---","---","---","---","---","---","---"], */
        labels: [<?php echo $dates_data_str ?>],
        datasets: [
            
                {
                    label: "Completed",
                    fillColor: "#311b92",
                    highlightFill: "#311b92",
                    data: [0,0,0,0,0,0,0]
                },
                {
                    label: "Rider cancelled",
                    fillColor: "#29b6f6",
                    highlightFill: "#4fc3f7",
                    data: [0,0,0,0,0,0,0]
                },
                {
                    label: "Driver cancelled",
                    fillColor: "#ffeb3b",
                    highlightFill: "#ffee58",
                    data: [0,0,0,0,0,0,0]
                },
                {
                    label: "No driver",
                    fillColor: "#ff5722",
                    highlightFill: "#ff7043",
                    data: [0,0,0,0,0,0,0]
                },
                {
                    label: "All",
                    fillColor: "#8BC34A",
                    highlightFill: "#8BC34A",
                    data: [0,0,0,0,0,0,0]
                }
            ]
        };


        



        var chartOptions = {
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



        var bchartOptions = {
            //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
            scaleBeginAtZero : true,

            //Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines : true,

            //String - Colour of the grid lines
            scaleGridLineColor : "rgba(0,0,0,.05)",

            //Number - Width of the grid lines
            scaleGridLineWidth : 1,

            //Boolean - Whether to show horizontal lines (except X axis)
            scaleShowHorizontalLines: true,

            //Boolean - Whether to show vertical lines (except Y axis)
            scaleShowVerticalLines: true,

            //Boolean - If there is a stroke on each bar
            barShowStroke : false,

            //Number - Pixel width of the bar stroke
            barStrokeWidth : 2,

            //Number - Spacing between each of the X value sets
            barValueSpacing : 30,

            //Number - Spacing between data sets within X values
            barDatasetSpacing : 2,

            //String - A legend template
            legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: true,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: true
        };


        
        var lineChartCanvas = jQuery("#lineChart").get(0).getContext("2d");
        lineChart = new Chart(lineChartCanvas).Line(areaChartData, chartOptions);

        var lineChartCanvas = jQuery("#lineChartDau").get(0).getContext("2d");
        lineChartDau = new Chart(lineChartCanvas).Line(areaChartData2, chartOptions);

        var lineChartCanvas = jQuery("#lineChartOnd").get(0).getContext("2d");
        lineChartOnd = new Chart(lineChartCanvas).Line(areaChartData3, chartOptions);

        var ChartCanvas = jQuery("#lineChartbt").get(0).getContext("2d");
        barChartBT = new Chart(ChartCanvas).Bar(bchartData, bchartOptions);

        updateDashboard();       

    });

    

    setInterval(updateDashboard, 5000);

    
    function updateDashboard(){

        var post_data = {'action_get':'updateDashboard'};
        var search_data = [];
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            crossDomain:true,
            xhrFields: {withCredentials: true},
            data: post_data,
            success: function (data, status)
            {
                
                try{
                    var data_obj = JSON.parse(data);
                }catch(e){
                    
                    return;
                }

                if(data_obj.hasOwnProperty('success')){
                    
                    
                    $('#new-customers').html(data_obj.num_of_customers);
                    $('#daily-active-customers').html(data_obj.num_active_customers);
                    $('#new-drivers').html(data_obj.num_driver_regs);
                    $('#drivers-available').html(data_obj.num_of_available_drivers);

                    $('#trips-completed').html(data_obj.num_bookings_completed);
                    $('#trips-cancelled-rider').html(data_obj.num_bookings_cancelled_rider);
                    $('#trips-cancelled-driver').html(data_obj.num_bookings_cancelled_driver);
                    $('#trips-cancelled-no-driver-available').html(data_obj.num_booking_cancelled_no_driver);   
                    
                    if(pieChart){
                        if(data_obj.num_bookings_completed || data_obj.num_bookings_cancelled_rider || data_obj.num_bookings_cancelled_driver || data_obj.num_booking_cancelled_no_driver){
                            $('#placeholder-pie-chart').css('visibility','hidden');
                        }else{
                            $('#placeholder-pie-chart').css('visibility','visible');
                        }
                        pieChart.segments[0].value = parseInt(data_obj.num_bookings_completed);
                        pieChart.segments[1].value = parseInt(data_obj.num_bookings_cancelled_rider);
                        pieChart.segments[2].value = parseInt(data_obj.num_bookings_cancelled_driver);
                        pieChart.segments[3].value = parseInt(data_obj.num_booking_cancelled_no_driver);
                        pieChart.update();
                    }
                    

                    $('#new-bookings').html(data_obj.num_of_bookings);
                    
                    $('#todays-earnings').html(data_obj.todays_earnings);
                    $('#company-earning').html(data_obj.company_earnings);
                    $('#drivers-earning').html(data_obj.driver_earnings);
                    $('#trips-discounts').html("-" + data_obj.ride_discounts);

                    let week_earnings = data_obj.week_earnings;
                    if(week_earnings && lineChart){
                        let count = 0;
                        for(let key in week_earnings){
                            lineChart.datasets[0].points[count].value = (Math.round(week_earnings[key] * 100) / 100).toFixed(2);
                            count++;
                        }
                        lineChart.update();
                    }

                    /**
                     * 
                     * {
                    label: "Completed",
                    fillColor: "#311b92",
                    highlightFill: "#311b92",
                    data: [10,5,8,1,20,12,7]
                },
                {
                    label: "Rider cancelled",
                    fillColor: "#29b6f6",
                    highlightFill: "#4fc3f7",
                    data: [10,5,8,1,20,12,7]
                },
                {
                    label: "Driver cancelled",
                    fillColor: "#ffeb3b",
                    highlightFill: "#ffee58",
                    data: [10,5,8,1,20,12,7]
                },
                {
                    label: "No driver",
                    fillColor: "#ff5722",
                    highlightFill: "#ff7043",
                    data: [10,5,8,1,20,12,7]
                },
                {
                    label: "All",
                    fillColor: "#000000",
                    highlightFill: "#000000",
                    data: [10,5,8,1,20,12,7]
                }
                     */


                    let bookings_trend_data = data_obj.booking_trend_data;
                    if(bookings_trend_data && barChartBT){
                        let count = 0;
                        for(let key in bookings_trend_data){
                            barChartBT.datasets[0].bars[count].value = parseInt(bookings_trend_data[key]['comp']);
                            barChartBT.datasets[1].bars[count].value = parseInt(bookings_trend_data[key]['r_canc']);
                            barChartBT.datasets[2].bars[count].value = parseInt(bookings_trend_data[key]['d_canc']);
                            barChartBT.datasets[3].bars[count].value = parseInt(bookings_trend_data[key]['no_drv']);
                            barChartBT.datasets[4].bars[count].value = parseInt(bookings_trend_data[key]['all']);
                            count++;
                        }
                        barChartBT.update();
                    }


                    $('#maximum-dau').html(data_obj.max_dau);
                    $('#minimum-dau').html(data_obj.min_dau);
                    $('#maximum-ond').html(data_obj.max_ond);
                    $('#minimum-ond').html(data_obj.min_ond);

                    let peak_dau = data_obj.peak_dau_trend;
                    if(peak_dau && lineChartDau){
                        let count = 0;
                        for(let key in peak_dau){
                            lineChartDau.datasets[0].points[count].value = peak_dau[key];
                            count++;
                        }
                        lineChartDau.update();
                    }


                    let peak_ond = data_obj.peak_ond_trend;
                    if(peak_ond && lineChartOnd){
                        let count = 0;
                        for(let key in peak_ond){
                            lineChartOnd.datasets[0].points[count].value = peak_ond[key];
                            count++;
                        }
                        lineChartOnd.update();
                    }
                                        
                }  
    
            },
            error:function(jqXHR,textStatus, errorThrown){
                return;
            }
            
        });


    }







        
    



</script>












