<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
            Get a birds eye view of every driver and track their locations 
        </div>
    </div>
</div> <!--/Row-->

<div class="row">	
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><b style="display:inline;font-size:14px" id='location-update'></b></h3>          
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">

                    <div class="col-sm-3">
                        
                        <select class="form-control" id="carcity" name="carcity">                                              
                            <option value="0">All cities</option>
                            <?php
                                foreach($inter_city_routes as $intercityroutes){
                                echo "<option data-lat='{$intercityroutes['lat']}' data-lng='{$intercityroutes['lng']}' value='{$intercityroutes['id']}'>{$intercityroutes['r_title']}</option>\n";
                                }
                            ?> 
                        
                        </select>                                          
                    </div>

                    
                    <div class="col-sm-5">                        
                        <input  type="text"  class="form-control" id="track-driver" placeholder="Enter Driver Name or Phone" name="track-driver" value="" >
                        <input  type="text"  hidden="hidden" class="" id="track-driverid" placeholder="" name="track-driverid" value="" >
                    </div>                           
                    

                </div>
                <hr>
                
                <div style="position:relative;">
                    
                    <div id="dashmap" style="height:500px;"></div>
                </div>

      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
</div>


      


<script src="../js/carsvg.js"></script>
<script>

    var map = undefined; 
    var bounds = undefined; 
    var marker = undefined;                  
    var latLong = undefined;
    var mapOptions = undefined;
    var longitue;
    var latitude;
    var location_update_timer_id;
    var driver_id = 0;
    var route_id = 0;
    var single_driver_marker;
    var driver_markers = {};
    var driver_icon_image = "<?php echo SITE_URL . 'img/driver-marker-icon.png';?>";
    var infoWindow;
    var clear_single_driver_marker = 0;
    var clear_driver_markers = 0;
    var single_driver = 0;
    var driver_id_sel = 0;
    var infowindow_content = [];
    var selected_city_id = 0;
    var driver_locations_timer_handle = 0;

        

    if (typeof google === 'object' && typeof google.maps === 'object') {
        
        if(typeof mapOptions === 'undefined'){
                mapOptions = {
                center: new google.maps.LatLng(9.0338725,8.677457),
                zoom: 2,
                disableDefaultUI: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("dashmap"), mapOptions);
            /* directionsService = new google.maps.DirectionsService;
            directionsDisplay = new google.maps.DirectionsRenderer({
                map: map
            }); */
            //bounds = new google.maps.LatLngBounds();
            latitude = 9.0338725;
            longitude = 8.677457;
            latLong = new google.maps.LatLng(latitude,longitude);
            infoWindow = new google.maps.InfoWindow();
            /* marker = new google.maps.Marker({
                                                position: latLong,
                                                map: map
                                            });  */                   

        }
  
          
    }

    jQuery('#carcity').on('change', function(){        
        route_id = jQuery('#carcity option:selected').val();
        city_lat = parseFloat(jQuery('#carcity option:selected').data('lat'));
        city_lng = parseFloat(jQuery('#carcity option:selected').data('lng'));
        
        if(!route_id)return;
        selected_city_id = route_id;        
        driver_id = 0;
        $('#track-driver').val('');
        $('#track-driverid').val(0); 

        if(map){
            if(route_id == 0){
                map.setZoom(2);
                map.setCenter({lat:9.0338725,lng:8.677457});
            }else{
                map.setZoom(13);
                map.setCenter({lat:city_lat,lng:city_lng});
            }
            
        }  
        
        
    });


    
    updateDriverLocation();

    setInterval(updateDriverLocation,5000);
    

    var updating_driver_location = 0;
    function updateDriverLocation(){
        
        if(updating_driver_location)return;
        updating_driver_location = 1;
        var post_data = {'action':'getDriverLocation','driver_id' : driver_id, 'route_id':route_id};        
        var driver_data;
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            crossDomain:true,
            xhrFields: {withCredentials: true},
            data: post_data,
            timeout : 15000,
            success: function (data, status)
            {
                updating_driver_location = 0;
                try{
                    var data_obj = JSON.parse(data);
                }catch(e){
                    
                    return;
                }

                if(data_obj.hasOwnProperty('success')){

                    driver_data = data_obj.data;
                    
                    let time_updated = Date.now();


                    for(let key in driver_data){
                            
                            infowindow_content[key] = `<h3>${driver_data[key].name}</h3><p>Last seen: ${driver_data[key].location_date}</p><p>${driver_data[key].view_link}</p>`;
                            if(driver_markers.hasOwnProperty(key)){
                                //marker exists. update the marker
                                let marker_icon = driver_markers[key].marker.getIcon();
                                marker_icon.rotation = driver_data[key].b_angle;
                                driver_markers[key].marker.setIcon(marker_icon);

                                driver_markers[key]['marker'].setPosition({lat : parseFloat(driver_data[key].lat), lng : parseFloat(driver_data[key].lng)});
                                driver_markers[key]['last_update_time'] = time_updated;

                            }else{
                                //create the marker
                                driver_markers[key] = {};
                                driver_markers[key]['last_update_time'] = time_updated;
                                let icon = {
                                                path : car_svg_data,
                                                fillColor: '#283593',
                                                fillOpacity: 1,
                                                anchor: new google.maps.Point(50,50),
                                                strokeWeight: 0,
                                                scale: 0.4,
                                                rotation: driver_data[key].b_angle
                                            };

                                
                                driver_markers[key]['marker'] = new google.maps.Marker({
                                position: {lat : parseFloat(driver_data[key].lat), lng : parseFloat(driver_data[key].lng)},
                                map: map,
                                icon:icon,
                                marker_key : key
                            });

                                /* let marker2 = new google.maps.Marker({
                                    position: {lat : parseFloat(driver_data[key].lat), lng : parseFloat(driver_data[key].lng)},
                                    map: map,
                                    label: driver_data[key].name,
                                    marker_key : key
                                });

                               marker2.setMap(map);  */
                                
                                
                            driver_markers[key]['marker'].addListener("click", () => {
                                                infoWindow.close();
                                                infoWindow.setContent(infowindow_content[driver_markers[key]['marker']['marker_key']]);
                                                infoWindow.open(driver_markers[key]['marker'].getMap(), driver_markers[key]['marker']);
                                            });

                            driver_markers[key]['marker'].setMap(map);
                                
                            }


                    }

                    //clear markers that were not updated. obviously they are offline
                    for(let key in driver_markers){
                        if(driver_markers[key]['last_update_time'] != time_updated){
                            driver_markers[key]['marker'].setMap(null);
                            delete driver_markers[key];
                        }
                    }                                                         
                    
                
                }  
    
            },
            error:function(jqXHR,textStatus, errorThrown){
                updating_driver_location = 0;
                return;
            }
            
        });


    }

    


    jQuery('#track-driver').autocomplete({
        source: function(req,res){
            var post_data = {'action':'driverscityautocomp','term' : req.term,'route_id' : selected_city_id};
            var search_data = [];
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                crossDomain:true,
                xhrFields: {withCredentials: true},
                data: post_data,
                success: function (data, status)
                {
                                                                    
                    console.log(data);
                    res(data);
        
                },
                error:function(jqXHR,textStatus, errorThrown){
                    res();
                }
                
            });
        },
        select:function(event,ui){
            jQuery(this).val(ui.item.label );
            jQuery('#track-driverid').val(ui.item.value.driver_id);
            if(map){
                map.setZoom(15);
                map.setCenter({lat: parseFloat(ui.item.value.lat),lng: parseFloat(ui.item.value.long)});
            }
            return false;
        },
        minLength:1

 });


   



</script>

