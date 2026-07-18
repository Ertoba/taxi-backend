<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Create zones by drawing a boundary around an area of interest within a city such as airport, estate, park...and set extra fares for the area or zone. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Zone Details</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             
             
                      <form  enctype="multipart/form-data" id="zone-form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                      <input type="text" hidden id="zone-id" name="zone-id" value="<?php echo isset($zone_data["id"]) ? $zone_data["id"] : ''; ?>"/>      
                      <input type="text" hidden id="zone-boundary-data" name="zone-boundary-data" />
                            
                           <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="zone-name"><span style="color:red">*</span>Zone Name</label>
                                    <p>Enter a name for this zone</p>
                                    <input type="text"  class="form-control" required="required" id="zone-name" placeholder="" name="zone-name" value="<?php echo isset($zone_data["title"]) ? $zone_data["title"] : ''; ?>" >
                                </div>  
                                
                             </div>

                             <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="zone-city"><span style="color:red">*</span>Zone City</label>
                                    <p>Select the city this zone will be found in</p>
                                    <input readonly data-coords="<?php echo isset($intra_city_routes['city_bound_coords']) ? $intra_city_routes['city_bound_coords'] : ''; ?>" data-cityid="<?php echo isset($intra_city_routes['id']) ? $intra_city_routes['id'] : 0; ?>" required="required" type="text" id="zone-city" name="zone-city" class="form-control" value="<?php echo isset($intra_city_routes['r_title']) ? $intra_city_routes['r_title'] : ''; ?>">
                                    <input type="text" hidden id="zone-city-id" name="zone-city-id" value="<?php echo isset($intra_city_routes['id']) ? $intra_city_routes['id'] : 0; ?>"/>
                                    
                                </div>  
                                
                             </div>


                             <br>


                            <div class="form-group">
                            
                                <div class="col-sm-12">

                                    <div id = "map2" class="box box-info">
                                        <div class="box-header with-border">
                                            <h3 id="map-header-title" class="box-title">Zone Map</h3>             
                                        </div><!-- /.box-header -->
                                        
                                        <div class="box-body">   
                                            <div>
                                                <input type="text" disabled class="form-control" id="city-quick-search" placeholder="City quick search" />
                                            </div>                                         
                                            <div id="zone-map" style="height:400px;"></div>                                                                        
                                        </div><!-- /.box-body -->

                                        <div style="padding: 5px;text-align:center;">
                                            <div style="display:inline;" id="zone-draw-controls">
                                                <button type="button" class="btn btn-sm btn-primary select-poly" style="margin:5px;">Select</button>
                                                <button type="button" class="btn btn-sm btn-info draw-poly" style="margin:5px;">Draw</button>
                                                <button type="button" class="btn btn-sm btn-danger remove-poly" style="margin:5px;" >Remove</button>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                            </div>


                             <div class="form-group">
                            
                               <div class="col-sm-6">
                                    <label for="zone-fare-type"><span style="color:red">*</span>Zone Fare Increase Type</label>
                                    <p>Select how the fare will be increased</p>
                                    <select class="form-control" id="zone-fare-type" name="zone-fare-type">
                                        <option value="1" <?php echo isset($zone_data["zone_fare_type"]) &&  $zone_data["zone_fare_type"] == 1 ? "selected" : ''; ?>>Multiplier</option>
                                        <option value="2" <?php echo isset($zone_data["zone_fare_type"]) &&  $zone_data["zone_fare_type"] == 2 ? "selected" : ''; ?>>Additional</option>
                                    </select>
                                    
                                </div>  
                                
                             </div>


                             <div class="form-group">                       
                               <div class="col-sm-6">
                                    <label for="zone-inc-value"><span style="color:red">*</span>Zone Fare Increase Value</label>
                                    <input  type="number"  min="0.00" step="0.01" required= "required" class="form-control" id="zone-inc-val" placeholder="" name="zone-inc-val" value="<?php echo !empty($zone_data["zone_fare_value"]) ? $zone_data["zone_fare_value"] : ''; ?>" >
                                </div>                              
                                

                            </div> 
                            
                            <br>
                            
                                                 
                            
                             
                              <hr />
                           <button type="submit" class="btn btn-primary btn-block" value="1" id="updatezone" name="updatezone" >Update Zone</button> 
                        </form>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>





<script>

    var map = undefined;     
     var bounds = undefined;     
     var latLong = undefined;
     var city_circle;
     var dist_unit = 0;
     var city_radius_span = 5000; //meters
     var city_lat = 0;
     var city_lng = 0;
     var city_coords_json_data = '{}'; 
     var city_coords_data;
     var city_polygon;
     var city_bounds;
     var marker;
     var autocomplete;

     var drawingManager;
    var selectedShape;
    var gmarkers = [];
    var coordinates = [];
    var allShapes = [];
    var shapeColor = "#007cff";
    var default_lat = 0.0;
    var default_lng = 0.0;
    var city_polygon_json = '<?php echo isset($zone_data['zone_bound_coords']) ? $zone_data['zone_bound_coords'] : '[]'; ?>';
    var city_polygon_data = JSON.parse(city_polygon_json.replace(/&quot;/g,'"'));
    var selectedPolygon;
    const NUM_OF_POLYGONS_INTERSTATE = 2;
    var max_polygon_num_reached = false;


    if (typeof google === 'object' && typeof google.maps === 'object') {
        
        if(typeof mapOptions === 'undefined'){
            mapOptions = {
            center: new google.maps.LatLng(9.0338725,8.677457),
            zoom: 2,
            disableDefaultUI: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
          };
          map = new google.maps.Map(document.getElementById("zone-map"), mapOptions);
                  
  
        }


        var input = document.getElementById('city-quick-search');
        var options = {
            bounds: city_bounds,
            strictBounds: true
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            jQuery('#city-zone-long').val(place.geometry.location.lng());
            jQuery('#city-zone-lat').val(place.geometry.location.lat());
            jQuery('#city-zone-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            if(marker){
                marker.setMap(null);
                marker = null;
            }

            


            let latLong = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker = new google.maps.Marker({
            position: latLong,
            map: map,
            animation: google.maps.Animation.BOUNCE
            });
            marker.setMap(map);
            map.setZoom(16);
            map.panTo(marker.getPosition());
       


        });


        var polygonOptions = {
            strokeWeight: 1,
            fillOpacity: 0.4,
            fillColor: '#ff0000',
            editable: true,
            draggable: true
        };

        // Initialize Drawing Manager
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null,
            drawingControl: false, //disable the maps drawing controls
            drawingControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER,
                drawingModes: ['polygon'] //  you can also add: 'marker', 'polyline', 'rectangle', 'circle'
            },
            polygonOptions: polygonOptions,
            map: map
        });

        $('#zone-draw-controls .select-poly').on('click', function(){
            drawingManager.setDrawingMode(null);
        });

        $('#zone-draw-controls .draw-poly').on('click', function(){
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
        });

        $('#zone-draw-controls .remove-poly').on('click', function(){
            deleteSelectedPolygon();
        });

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {

            var newShape = e.overlay;
            // console.log(newShape);

            if(allShapes.length == 1){
                newShape.setMap(null);
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'You have already drawn a zone.',
                    imageUrl:imgurl,
                    html:true
                });
                return;
            }

            let not_inside = 0;
            let poly_coords = newShape.getPath();

            poly_coords.forEach(function(val,indx){
                if(!google.maps.geometry.poly.containsLocation(val,city_polygon))not_inside = 1;
            })


            if(not_inside){
                
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'Please draw the zone inside the selected city' ,
                    imageUrl:imgurl,
                    html:true
                });
                newShape.setMap(null);
                return;
            }



            let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
            console.log(coord_data_json);
            $('#zone-boundary-data').val(coord_data_json);    

            allShapes.push(newShape); // save the newly created shape to the allShapes list

            

            // exit drawing mode after completion of the polygon
            drawingManager.setDrawingMode(null);

            setSelectedPolygon(newShape);


            // select polygon at "click"
            google.maps.event.addListener(newShape, 'click', function(e) {
                console.log('clicked');
                if (e.vertex !== undefined) {
                    return;
                    //remove polygon vertex when the vertex is clicked
                    var path = newShape.getPaths().getAt(e.path);
                    if (path.length < 4) {
                        return;
                    }
                    path.removeAt(e.vertex);
                    getPolyGonCoordinates(newShape);
                    if (path.length < 3) {
                        newShape.setMap(null);
                    }
                }
                setSelectedPolygon(newShape);
            });


            google.maps.event.addListener(newShape, 'mouseup', function() {
                
                for (i=0; i < allShapes.length; i++) { 
                    if (newShape.getPath() == allShapes[i].getPath()) {
                        allShapes[i] = newShape;                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
                        console.log(coord_data_json);
                        $('#zone-boundary-data').val(coord_data_json);                       
                        

                    }
                }                
            });


            //update coordinates
            google.maps.event.addListener(newShape, 'click', function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape, "dragend", function(e) {
                for (i=0; i < allShapes.length; i++) {
                    if (newShape.getPath() == allShapes[i].getPath()) {

                        let not_inside = 0;
                        let poly_coords = newShape.getPath();

                        poly_coords.forEach(function(val,indx){
                            if(!google.maps.geometry.poly.containsLocation(val,city_polygon))not_inside = 1;
                        })


                        if(not_inside){
                            
                            imgurl = '../img/info_.gif?a=' + Math.random();                    
                            swal({
                                title: '<h1>Error</h1>',
                                text: 'zone must be inside the selected city' ,
                                imageUrl:imgurl,
                                html:true
                            });
                            return;
                        }

                        allShapes[i] = newShape;
                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
                        console.log(coord_data_json);
                        $('#zone-boundary-data').val(coord_data_json);

                    }
                }
            });

            google.maps.event.addListener(newShape.getPath(), "insert_at", function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape.getPath(), "remove_at", function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape.getPath(), "set_at", function(e) { getPolyGonCoordinates(newShape); });


        });


        // Deselect all polygons when changing the drawing mode or when the user clicks on the map
        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelectedPolygons);
        google.maps.event.addListener(map, 'click', clearSelectedPolygons);

        city_coords_data = $('#zone-city').data('coords');
        let city_center = new google.maps.LatLng(city_coords_data.center.lat,city_coords_data.center.lng)


        $('#city-quick-search').prop('disabled', false);

        if(city_coords_data.coords){
            //add polygon 
            let polygon;            
            polygon = new google.maps.Polygon({
                paths: city_coords_data.coords,
                strokeWeight: 1,
                strokeColor:'#0000ff',
                fillColor: '#0000ff',
                fillOpacity: 0.1,
                editable: false,
                draggable: false
                
            });
            polygon.setMap(map);

            city_polygon = polygon;

            city_bounds = new google.maps.LatLngBounds();  
            city_coords_data.coords.forEach(function(val,indx){
                city_bounds.extend(val);
            })

            map.fitBounds(city_bounds);

            setTimeout(function(){
                autocomplete.setBounds(city_bounds); 
            }, 5000);

                   
            

        }



        if(city_polygon_json.length > 5){
            //add polygon 
            let polygon;            
            polygon = new google.maps.Polygon({
                paths: city_polygon_data,
                strokeWeight: 1,
                fillOpacity: 0.4,
                fillColor: '#ff0000',
                editable: true,
                draggable: true
                
            });
            polygon.setMap(map);

            allShapes[0] = polygon;

            setSelectedPolygon(polygon);

            getPolyGonCoordinates(polygon); // find coordinates peaks  
            
            let coord_data_json = JSON.stringify(getPolyGonCoordinates(polygon));
            $('#zone-boundary-data').val(coord_data_json); 
            

        }



        for(var x = 0; x < allShapes.length;x++){

            let polygon = allShapes[x];

            
            google.maps.event.addListener(polygon, 'click', function(e) {
                console.log('clicked');
                if (e.vertex !== undefined) {
                    return;
                    //remove polygon vertex when the vertex is clicked
                    var path = polygon.getPaths().getAt(e.path);
                    if (path.length < 4) {
                        return;
                    }
                    path.removeAt(e.vertex);
                    getPolyGonCoordinates(polygon);
                    if (path.length < 3) {
                        polygon.setMap(null);
                    }
                }
                setSelectedPolygon(polygon);
            });



            google.maps.event.addListener(polygon, 'mouseup', function() {
                console.log('mouse-up');
                for (i=0; i < allShapes.length; i++) { 
                    if (polygon.getPath() == allShapes[i].getPath()) {
                        allShapes[i] = polygon;                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(polygon));
                        $('#zone-boundary-data').val(coord_data_json);                       
                        

                    }
                }                
            });

            
            google.maps.event.addListener(polygon, "dragend", function(e) {
                console.log('drag');
                for (i=0; i < allShapes.length; i++) {
                    if (polygon.getPath() == allShapes[i].getPath()) {

                        let not_inside = 0;
                        let poly_coords = polygon.getPath();

                        poly_coords.forEach(function(val,indx){
                            if(!google.maps.geometry.poly.containsLocation(val,city_polygon))not_inside = 1;
                        })


                        if(not_inside){
                            
                            imgurl = '../img/info_.gif?a=' + Math.random();                    
                            swal({
                                title: '<h1>Error</h1>',
                                text: 'zone must be inside the selected city' ,
                                imageUrl:imgurl,
                                html:true
                            });
                            return;
                        }

                        allShapes[i] = polygon;
                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(polygon));
                        $('#zone-boundary-data').val(coord_data_json);

                    }
                }
            });




        }
        

        
        

        



        function getPolyGonCoordinates(polygon) {

            var path = polygon.getPath();
            coordinates = [];
            for (var i = 0; i < path.length; i++) {
                coordinates.push({
                    lat: path.getAt(i).lat(),
                    lng: path.getAt(i).lng()
                });
            }

            
            return coordinates

        }




        function clearSelectedPolygons(){
            if (selectedPolygon) { //check that the selected shape is a polygon
                if (selectedPolygon.type !== 'marker') {
                    selectedPolygon.setEditable(false);
                }
                selectedPolygon = null;
            }
        }




        function setSelectedPolygon(polygon){
            clearSelectedPolygons();
            polygon.setEditable(true);
            polygon.setDraggable(true);
            selectedPolygon = polygon;
        }




        function deleteSelectedPolygon() {
            if (selectedPolygon) {
                selectedPolygon.setMap(null);
                var index = allShapes.indexOf(selectedPolygon);
                if (index > -1) {
                    allShapes.splice(index, 1);
                    $('#zone-boundary-data').val('');                   
                    
                }
                let lat_lng = [];
                allShapes.forEach(function(data, index) {
                    lat_lng[index] = getCoordinates(data);
                });
                //document.getElementById('info').value = JSON.stringify(lat_lng);
            }

        }


  
    }





    


    jQuery('#updatezone').click(function(e) {

        e.preventDefault();  
        e.stopPropagation();              

                
        var zone_data = $('#zone-boundary-data').val();        
        var ref = jQuery('#zone-form').find("[required]");
        var empty_fields;
        var type;


        jQuery(ref).each(function(){
            
            
            if (!jQuery(this).val())
            {
                imgurl = '../img/info_.gif?a=' + Math.random();
                
                 
                
                jQuery('#busy').modal('hide');
                jQuery(this).focus();

                empty_fields = 1;
            }
        }); 

        if(empty_fields){
            swal({
                        title: '<h1>Error</h1>',
                        text: 'Required fields should not be blank.',
                        imageUrl:imgurl,
                        html:true
            });
            return;
        }


        if (!zone_data){

            imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: 'No zone boundary found. Please draw a boundary inside the city',
                        imageUrl:imgurl,
                        html:true
            });
            return;
                
                
        }
        

        jQuery('#busy').modal('show');

        window.setTimeout(function() {
            jQuery("#zone-form").submit();                
        }, 1000);


    });


    


</script>











