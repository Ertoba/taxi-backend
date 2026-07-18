

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Quickly create car categories. 
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Car Details</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             
             
                      <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                            
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <img id="rideimg" class="img-responsive center-block" src="../../img/ride-sample.png" height=100px/>                                       
                                </div>

                                <div class="col-sm-6">
                                    <label for="rideimage"><span style="color:red">*</span>Select an image for the car (Image resolution: 400px X 235px)</label>
                                    <input  class="form-control" type="file" onchange="readURL(this)" name="rideimage" id="rideimage" value=""/>
                                </div>     
                            </div>

                            
                            
                           <div class="form-group">                       
                               <div class="col-sm-6">
                                    <label for="franch-name"><span style="color:red">*</span>Car Name</label>
                                    <p>Must be unique</p>
                                    <input  type="text"  class="form-control" id="ride-type" placeholder="" name="ride-type" value="<?php echo isset($_POST["ride-type"]) ? $_POST["ride-type"] : ''; ?>" >
                                </div>  
                                
                             </div>
                           
                          
                             <div class="form-group">
                            
                               <div class="col-sm-12">
                                    <label for="franch-desc"><span style="color:red">*</span>Car Description</label>
                                    <p>A brief description of this car</p>
                                    <textarea  rows="3" style="display:block; width:100%;" name="ride-desc" required="required" maxlength="250"><?php echo isset($_POST["ride-desc"]) ? $_POST["ride-desc"] : ''; ?></textarea>
                                </div>  
                                
                             </div>


                             <hr>

                             <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="franch-desc"><span style="color:red">*</span>Seating  Capacity</label>
                                    <p>Select the number of passengers this vehicle can carry</p>
                                    <select name="num-seats" class="form-control">
                                        <?php
                                            $max_num_seats = 50;
                                            
                                            for($i = 1;$i < $max_num_seats + 1;$i++){
                                                if($i == 1){
                                                    $selected = "selected";
                                                }else{
                                                    $selected = '';
                                                }
                                                echo "<option value='{$i}' {$selected} >{$i}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>  
                                
                             </div>


                             <hr>


                             <div class="form-group">
                                <div class="col-sm-6">
                                    <label for="car-icon-type">Car Icon type</label><br>
                                    <p>Select the icon type that will be shown on map for this car</p>
                                    <img id="car-icon-type-preiew" src="../img/ride-icon-types/city-driver-icon-1.png" style="display:block;width:64px;" />
                                    <select class="form-control" id="car-icon-type" name="car-icon-type">
                                        <option value="1">Icon type 1</option>
                                        <option value="2">Icon type 2</option>
                                        <option value="3">Icon type 3</option>
                                        <option value="4">Icon type 4</option>
                                        <option value="5">Icon type 5</option>
                                        <option value="6">Icon type 6</option>
                                    </select>
                                </div>
                            </div>


                             <hr>


                             <div class="form-group">
                                <div class="col-sm-6">
                                    <label for="ride-avail">Set Car Availability</label><br>
                                    Available <input  type="checkbox"  id="ride-avail" name="ride-avail">
                                </div>
                            </div>

                                                 
                            
                             
                              <hr />
                           <button type="submit" class="btn btn-primary btn-block" value="1" name="savefranch" >Save Ride</button> 
                        </form>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
    
</div>

<script>

    var car_icon_type;

    car_icon_type = $('#car-icon-type option:selected').val();

    $('#car-icon-type-preiew').attr('src', `../img/ride-icon-types/city-driver-icon-${car_icon_type}.png`)

    $('#car-icon-type').on('change', function(){
        car_icon_type = $('#car-icon-type option:selected').val();

        $('#car-icon-type-preiew').attr('src', `../img/ride-icon-types/city-driver-icon-${car_icon_type}.png`)
    })


</script>


















