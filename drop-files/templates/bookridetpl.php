<div class="container-fluid">
    <div class="row" style="position:relative;height:350px;">
        <div id="booking-map1" style="width:100%;height:350px;position:absolute;top:0;"></div>
        <div id="booking-map2" style="width:100%;height:350px;position:absolute;top:0;"></div>
    </div>
</div>

<div class="container">
    <br>
    
    <div class="row">
    <h4 style="text-align:center;margin-top:10px;margin-bottom:30px"> Choose your Ride </h4>        
       <div class="routerides" style="height:100px;">
           <?php 
                foreach($rides_data as $ridedata){
                        
            ?>
                
                <div style="text-align:center;display:none;" class="col-sm-12" id="route-<?php echo $ridedata['r_id']; ?>">
                    <?php
                        foreach($ridedata['cars'] as $ridesdata){
                            echo "<img data-rideid='{$ridesdata['ride_id']}' class='tariff-ride-imgs' title='{$ridesdata['ride_desc']}' src='{$ridesdata['ride_img']}' height=80 />";
                        }

                    ?>
                </div>
            <?php
                }
            ?>

       </div>         
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-3">
            <label for="mode">Mode</label>
            <select class="form-control" id="mode" name="mode">
                <option value="0" selected>Inter-City</option> 
                <option value="1">Intra-State</option>
            </select>
        </div>

        <div class="col-sm-3" id="r-city">
            <label for="route-city">Route</label>
            <select class="form-control" id="route-city" name="route-city">
                <?php
                    foreach($route_data as $routedata){
                        if($routedata['r_scope'] == 1)continue;
                ?> 
                    <option data-routeid="<?php echo $routedata['id']; ?>" data-lng="<?php echo $routedata['lng']; ?>" data-lat="<?php echo $routedata['lat']; ?>" value="<?php echo $routedata['id']; ?>"><?php echo $routedata['r_title']; ?></option>
                <?php
                    }
                ?>
                
            </select>
        </div>


        <div class="col-sm-3" id="r-state" style="display:none">
            <label for="route-state">Route</label>
            <select class="form-control" id="route-state" name="route-state">
            <?php
                    foreach($route_data as $routedata){
                        if($routedata['r_scope'] == 0)continue;
                ?> 
                    <option data-pickaddr="<?php echo $routedata['pick_name']; ?>" data-dropaddr="<?php echo $routedata['drop_name']; ?>" data-routeid="<?php echo $routedata['id']; ?>" data-plng="<?php echo $routedata['pick_lng']; ?>" data-plat="<?php echo $routedata['pick_lat']; ?>" data-dlng="<?php echo $routedata['drop_lng']; ?>" data-dlat="<?php echo $routedata['drop_lat']; ?>" value="<?php echo $routedata['id']; ?>"><?php echo $routedata['r_title']; ?></option>
                <?php
                    }
                ?>
            </select>
        </div>

        <div class="col-sm-3">
            <label for="pickup">Pick-up Location</label>
            <input  type="text"  class="form-control" id="pickup" placeholder="" name="pickup" value="" >
            <input  type="text"  hidden="hidden" id="pcz-long"  name="pcz-long" value="" >
            <input  type="text"  hidden="hidden" id="pcz-lat"   name="pcz-lat" value="" >
        </div>

        <div class="col-sm-3">
            <label for="dropoff">Dropp-off Location</label>
            <input  type="text"  class="form-control" id="dropoff" placeholder="" name="dropoff" value="" >
            <input  type="text"  hidden="hidden" id="dcz-long"  name="dcz-long" value="" >
            <input  type="text"  hidden="hidden" id="dcz-lat"   name="dcz-lat" value="" >
        </div>
    </div>
    <br>
    <div class="row" id="route-price-details" style="display:none;">
        <div class="col-sm-10 col-sm-offset-1">
            <div class="row">
                <div class="col-sm-4">
                    <h4><i style="color:#FFC61A" class='fa fa-map'></i> Distance: <span id='route-dist'></span></h4>            
                </div>

                <div class="col-sm-4">
                    <h4><i style="color:#FFC61A" class='fa fa-clock-o'></i> Duration: <span id='route-dur'></span></h4>            
                </div>

                <div class="col-sm-4">
                    <h4><i style="color:#FFC61A" class='fa fa-money'></i> Price: ₦<span id='route-cost'></span></h4>            
                </div>
        
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">                
            <div style="text-align:center;"><button id="gettariffbtn" href="#" class="btn btn-black-bordered btn-lg ">Get Tariff</button><button id="bookridebtn" href="#" class="btn btn-black-bordered btn-lg ">Book Ride</button></div>                
        </div>
    </div>
    <br>
    <br>
    
</div>



<form style="display:none" id="bookrideform" enctype="multipart/form-data" class="" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

    
    <input  type="text" hidden="hidden" name="b-token" id="b-token" value="" > 
    

</form>






<style>

.tariff-ride-imgs{
    margin-left:5px;
    margin-right:5px;
    border-bottom: thick solid transparent;
    cursor: pointer;
}


.tariff-ride-imgs:hover{
    margin-left:5px;
    margin-right:5px;
    border-bottom: thick solid #FFC61A;
}


.tariff-ride-imgs-selected{
    margin-left:5px;
    margin-right:5px;
    border-bottom: thick solid #FFC61A;
}




</style>









<script>

var map = undefined; 
var map2 = undefined;
var bounds = undefined; 
var marker1 = undefined;
var marker = undefined;                  
var marker2 = undefined;
var marker3 = undefined;
var marker4 = undefined; 
var latLong = undefined;
var latLong1 = undefined;
var latLong2 = undefined;
var latLong3 = undefined;
var latLong4 = undefined;
var ride_selected_id = 0;


if (typeof google === 'object' && typeof google.maps === 'object') {
   
   if(typeof mapOptions === 'undefined'){
       mapOptions = {
       center: new google.maps.LatLng(9.0338725,8.677457),
       zoom: 5,
       disableDefaultUI: false,
       mapTypeId: google.maps.MapTypeId.ROADMAP
     };
     map = new google.maps.Map(document.getElementById("booking-map1"), mapOptions);
     directionsService = new google.maps.DirectionsService;
     directionsDisplay = new google.maps.DirectionsRenderer({
         map: map
     });
             
  
   }
  
  
   if(typeof mapOptions2 === 'undefined'){
       mapOptions2 = {
       center: new google.maps.LatLng(9.0338725,8.677457),
       zoom: 5,
       disableDefaultUI: false,
       mapTypeId: google.maps.MapTypeId.ROADMAP
     };
     map2 = new google.maps.Map(document.getElementById("booking-map2"), mapOptions2);
     directionsService2 = new google.maps.DirectionsService;
     directionsDisplay2 = new google.maps.DirectionsRenderer({
         map: map2
     });
     bounds = new google.maps.LatLngBounds();
  
   }
  }


jQuery('#gettariffbtn').show();
jQuery('#bookridebtn').hide();

if(jQuery('#mode').val() == 0){
    jQuery('#r-city').show();
    jQuery('#r-state').hide();
    jQuery('#pickup').removeAttr("disabled");
    jQuery('#dropoff').removeAttr("disabled");
    jQuery('#booking-map1').show();
    jQuery('#booking-map2').hide();
    var route_id = jQuery('#route-city').val();
    var route_ride_elem = "#route-" + route_id;
    jQuery(route_ride_elem).show();
    jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
    ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
    jQuery("#route-price-details").hide();
    jQuery('#gettariffbtn').attr("disabled","disabled");
    

}else{
    jQuery('#r-city').hide();
    jQuery('#r-state').show();
    jQuery('#pickup').attr("disabled","disabled");
    jQuery('#dropoff').attr("disabled","disabled");
    jQuery('#booking-map1').hide();
    jQuery('#booking-map2').show();
    var route_id = jQuery('#route-state').val();
    var route_ride_elem = "#route-" + route_id;
    jQuery(route_ride_elem).show();
    jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
    ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
    jQuery("#route-price-details").hide();
    jQuery('#gettariffbtn').removeAttr("disabled");
}



jQuery('#mode').on('change', function(){

        jQuery("#gettariffbtn").show();
        jQuery("#bookridebtn").hide();
        jQuery("#route-price-details").hide();
    
        jQuery('.routerides div').each(function(){
            jQuery(this).hide();
        });
             
         if(jQuery(this).val() == 0){
             jQuery('#r-city').show();
             jQuery('#r-state').hide();
             jQuery('#pickup').val("");
             jQuery('#dropoff').val("");
             jQuery('#pickup').removeAttr("disabled");
             jQuery('#dropoff').removeAttr("disabled");
             jQuery('#booking-map1').show();
             jQuery('#booking-map2').hide();
             var route_id = jQuery('#route-city').val();
             var route_ride_elem = "#route-" + route_id;
            jQuery(route_ride_elem).show();
            jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
            ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
            
            jQuery('#gettariffbtn').attr("disabled","disabled");

         }else{
            var pick_addr =  jQuery("#route-state").find(':selected').data('pickaddr');
            var drop_addr =  jQuery("#route-state").find(':selected').data('dropaddr');
            jQuery('#r-city').hide();
             jQuery('#r-state').show();
             jQuery('#pickup').val(pick_addr);
             jQuery('#dropoff').val(drop_addr);
             jQuery('#pickup').attr("disabled","disabled");
             jQuery('#dropoff').attr("disabled","disabled");
             jQuery('#booking-map1').hide();
             jQuery('#booking-map2').show();
             var route_id = jQuery('#route-state').val();
            var route_ride_elem = "#route-" + route_id;
            jQuery(route_ride_elem).show();
            jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
            ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
            jQuery('#gettariffbtn').removeAttr("disabled");
            interStatePlot();
         }
         
           
});


jQuery('#route-state').on('change', function(){
    var pick_addr =  jQuery("#route-state").find(':selected').data('pickaddr');
    var drop_addr =  jQuery("#route-state").find(':selected').data('dropaddr');
    
    jQuery('.routerides div').each(function(){
        jQuery(this).hide();
    });
        
    jQuery("#gettariffbtn").show();
    jQuery("#bookridebtn").hide();

    jQuery('#pickup').val(pick_addr);
    jQuery('#dropoff').val(drop_addr);

    var route_id = jQuery(this).find(':selected').data('routeid');
    var route_ride_elem = "#route-" + route_id;
    jQuery(route_ride_elem).show();
    jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
    ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
    jQuery("#route-price-details").hide();
    interStatePlot();
       
});



jQuery('#route-city').on('change', function(){
    
    jQuery('.routerides div').each(function(){
        jQuery(this).hide();
    });
    
    jQuery("#gettariffbtn").show();
    jQuery("#bookridebtn").hide();

    jQuery('#pickup').val('');
    jQuery('#dropoff').val('');
    jQuery("#route-price-details").hide();
    jQuery('#gettariffbtn').attr("disabled","disabled");
    

    var route_id = jQuery(this).find(':selected').data('routeid');
    var route_ride_elem = "#route-" + route_id;
    jQuery(route_ride_elem).show();
    jQuery(route_ride_elem + ' .tariff-ride-imgs').first().addClass("tariff-ride-imgs-selected");
    ride_selected_id = jQuery(route_ride_elem + ' .tariff-ride-imgs').first().data("rideid");
    
    
    
       
});


jQuery('.tariff-ride-imgs').on('click', function(){
    jQuery("#gettariffbtn").show();
    jQuery("#bookridebtn").hide();
    jQuery("#route-price-details").hide();

    jQuery('.tariff-ride-imgs').each(function(){
        jQuery(this).removeClass("tariff-ride-imgs-selected");
    });
    jQuery(this).addClass("tariff-ride-imgs-selected");
    ride_selected_id = jQuery(this).data("rideid");
})


 jQuery('#gettariffbtn').on('click', function(){
        jQuery('#gettariffbtn').html("Please Wait...");
        var selected_mode = jQuery("#mode").find(':selected').val();
        if(selected_mode == "1"){
            var route_id = jQuery('#route-state').find(':selected').data('routeid');
            var plng = jQuery('#route-state').find(':selected').data('plng');
            var plat = jQuery('#route-state').find(':selected').data('plat');

            var dlng = jQuery('#route-state').find(':selected').data('dlng');
            var dlat = jQuery('#route-state').find(':selected').data('dlat');
            var paddr = jQuery('#pickup').val();
            var daddr = jQuery('#dropoff').val();

        }else{
            var route_id = jQuery('#route-city').find(':selected').data('routeid');
            var plng = jQuery('#pcz-long').val();
            var plat = jQuery('#pcz-lat').val();

            var dlng = jQuery('#dcz-long').val();
            var dlat = jQuery('#dcz-lat').val();

            var paddr = jQuery('#pickup').val();
            var daddr = jQuery('#dropoff').val();

        }


                    
        //jQuery('#busy').modal('show');
                
        var post_data = {'action':'calctariff','p_addr':paddr,'d_addr':daddr,'mode':selected_mode,'route_id':route_id,'ride_id':ride_selected_id,'a_lng':plng,'a_lat':plat,'b_lng':dlng,'b_lat':dlat};       
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout : 10000,            
            data: post_data,
            success: function (data, status)
            {
                //jQuery('#busy').modal('hide');
                jQuery('#gettariffbtn').html("Get Tariff");
                console.log(data);
                try{
                    var data_obj = JSON.parse(data);
                }catch(e){
                    return;
                }

                if(data_obj.hasOwnProperty('error')){                    
                    alert(data_obj.error);
                    return;
                }

                if(data_obj.hasOwnProperty('notloggedin')){                    
                    alert(data_obj.notloggedin);
                    return;
                }


                if(data_obj.hasOwnProperty('distance')){  
                                        
                    jQuery('#route-dist').html(data_obj.distance);
                    jQuery('#route-dur').html(data_obj.duration);
                    jQuery('#route-cost').html(data_obj.price);

                    jQuery('#b-token').val(data_obj.token);

                    jQuery("#route-price-details").show();

                    jQuery("#gettariffbtn").hide();
                    jQuery("#bookridebtn").show();
                    


                    return;
                }



                
                
                
            },
            error: function() {                                
                
                //jQuery('#busy').modal('hide');
                jQuery('#gettariffbtn').html("Get Tariff");
            }

        });





 })




 jQuery("#bookridebtn").on("click",function(){

    jQuery('#bookrideform').submit();



 })








pczAutocomplete();
dczAutocomplete(); 

/* jQuery("#city-zone").focusout(function(){
   checkGeocode(document.getElementById("city-zone"));        
})   */

function interStatePlot(){

 
    var plng = jQuery('#route-state').find(':selected').data('plng');
    var plat = jQuery('#route-state').find(':selected').data('plat');

    var dlng = jQuery('#route-state').find(':selected').data('dlng');
    var dlat = jQuery('#route-state').find(':selected').data('dlat');
    
    if(marker3){
           marker3.setMap(null);
           marker3 = [];
       }

    if(marker4){
        marker4.setMap(null);
        marker4 = [];
    }
 
    setTimeout(() => {

        latLong3 = new google.maps.LatLng(parseFloat(plat), parseFloat(plng));
        marker3 = new google.maps.Marker({
            position: latLong3,
            map: map2,
            animation: google.maps.Animation.DROP
        });
        marker3.setMap(map2);
        map2.setZoom(7);
        //map.setCenter(marker.getPosition());
        map2.panTo(marker3.getPosition());


        setTimeout(() => {
        latLong4 = new google.maps.LatLng(parseFloat(dlat), parseFloat(dlng));
        marker4 = new google.maps.Marker({
            position: latLong4,
            map: map2,
            animation: google.maps.Animation.DROP
        });
        marker4.setMap(map2);
        map2.setZoom(7);
        //map.setCenter(marker.getPosition());
        map2.panTo(marker4.getPosition());


        setTimeout(function(){
        bounds.extend(latLong3);
        bounds.extend(latLong4);
        var pointA = latLong3;
        var pointB = latLong4;                
        map2.fitBounds(bounds);
        
        bounds = [];
        bounds = new google.maps.LatLngBounds();  
        calculateAndDisplayRoute(directionsService2, directionsDisplay2, pointA, pointB);
    }, 1500);
        
    }, 1500);

                    
    }, 1500);



}




function pczAutocomplete() {                            
   var input = document.getElementById('pickup');
   var options = {
       componentRestrictions: {country: 'ng'},
       strictBounds: true
   };

   pczautocomplete = new google.maps.places.Autocomplete(input, options);
   
   google.maps.event.addListener(pczautocomplete, 'place_changed', function() {
       var place = pczautocomplete.getPlace();
       jQuery("#route-price-details").hide();
       jQuery("#gettariffbtn").show();
        jQuery("#bookridebtn").hide();


        jQuery('#pcz-long').val(place.geometry.location.lng());
        jQuery('#pcz-lat').val(place.geometry.location.lat());
             
       
              
       if(marker1){
           marker1.setMap(null);
           marker1 = [];
       }
       
       latLong1 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
       marker1 = new google.maps.Marker({
       position: latLong1,
       map: map,
       animation: google.maps.Animation.DROP
       });
       marker1.setMap(map);
       map.setZoom(16);
       //map.setCenter(marker.getPosition());
       map.panTo(marker1.getPosition());

       if(marker1 && marker2){
        jQuery('#gettariffbtn').removeAttr("disabled");
           setTimeout(function(){
               bounds.extend(latLong1);
               bounds.extend(latLong2);                
               var pointA = latLong1;
               var pointB = latLong2;                
               map.fitBounds(bounds);
               
               bounds = [];
               bounds = new google.maps.LatLngBounds();  
               calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB);   
           }, 2000);
                     
       }
       


   });
}




function dczAutocomplete() {                            
   var input = document.getElementById('dropoff');
   var options = {
       componentRestrictions: {country: 'ng'},
       strictBounds: true
   };

   dczautocomplete = new google.maps.places.Autocomplete(input, options);
   
   google.maps.event.addListener(dczautocomplete, 'place_changed', function() {
        jQuery("#route-price-details").hide();
        jQuery("#gettariffbtn").show();
        jQuery("#bookridebtn").hide();


       var place = dczautocomplete.getPlace();
        jQuery('#dcz-long').val(place.geometry.location.lng());
        jQuery('#dcz-lat').val(place.geometry.location.lat());
       
       
       
       if(marker2){
           marker2.setMap(null);
           marker2 = [];
       }
       latLong2 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
       marker2 = new google.maps.Marker({
       position: latLong2,
       map: map,
       animation: google.maps.Animation.DROP
       });
       marker2.setMap(map);
       map.setZoom(16);
       //map.setCenter(marker.getPosition());
       map.panTo(marker2.getPosition());
       
       if(marker1 && marker2){
        jQuery('#gettariffbtn').removeAttr("disabled");
           setTimeout(function(){
               bounds.extend(latLong1);
               bounds.extend(latLong2);
               var pointA = latLong1;
               var pointB = latLong2;                
               map.fitBounds(bounds);
               
               bounds = [];
               bounds = new google.maps.LatLngBounds();  
               calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB);
           }, 2000);
                        
           
       }


   });
}


function calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB) {
   directionsService.route({
       origin: pointA,
       destination: pointB,
       avoidTolls: false,
       avoidHighways: false,
       unitSystem: google.maps.UnitSystem.METRIC,
       travelMode: google.maps.TravelMode.DRIVING
   }, function (response, status) {
       
       if (status == google.maps.DirectionsStatus.OK) {
           directionsDisplay.setDirections(response);
           jQuery("#map2-header-title").html("Map: Distance = " + response.routes[0].legs[0].distance.text + " Duration: " + response.routes[0].legs[0].duration.text);
       } else {

           imgurl = '../img/info_.gif?a=' + Math.random();                    
           swal({
               title: '<h1>Error</h1>',
               text: 'Cannot determine location coordinates. Please ensure you use google maps location suggestions!' ,
               imageUrl:imgurl,
               html:true
           });
           //window.alert('Directions request failed due to ' + status);
       }
   });
}


function checkGeocode(addr){ //verify if address entered is a valid google map address
       // Get geocoder instance
       var geocoder = new google.maps.Geocoder();
       
       // Geocode the address
       geocoder.geocode({'address': addr.value}, function(results, status){
       var coord = undefined;  
           if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
           //alert('looks good')
           
               /* // set it to the correct, formatted address if it's valid
               addr.value = results[0].formatted_address;; */
               coord = {"lat":results[0].geometry.location.lat(),"long" : results[0].geometry.location.lng()};
               //return({"lat":results[0].geometry.location.lat(),"long" : results[0].geometry.location.lng()});
           }else{
               
           imgurl = '../img/info_.gif?a=' + Math.random();                    
           swal({
               title: '<h1>Error</h1>',
               text: 'The address you entered is not valid. Please select an option from the google adress list!' ,
               imageUrl:imgurl,
               html:true
           });
           
           } 
           return coord;
       });    
                        
};






</script>