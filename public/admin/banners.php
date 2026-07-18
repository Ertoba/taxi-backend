<?php
session_start();
include("../../drop-files/lib/common.php");
include "../../drop-files/config/db.php";
include "../../drop-files/config/redis.php";

if(isset($_SESSION['expired_session'])){
    header("location: ".SITE_URL."login.php?timeout=1");
    exit;
}

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
  header("location: ".SITE_URL."login.php"); //Yes? then redirect user to the login page
  exit;
}

if($_SESSION['account_type'] /* != 2 && $_SESSION['account_type'] */ != 3){ ////if user is an admin or dispatcher
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}

$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-flag'></i> Banners"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "banners"; //Set the appropriate menu item active


//get all intra-city routes
$inter_city_routes = [];

$query = sprintf('SELECT * FROM %1$stbl_routes WHERE r_scope = 0 ORDER BY r_title ASC', DB_TBL_PREFIX);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $inter_city_routes[$row['id']] = $row;
        }
    
     }
    mysqli_free_result($result);
}



//get all banners
$banners_data = [];

$query = sprintf('SELECT %1$stbl_banners.*, %1$stbl_routes.r_title FROM %1$stbl_banners 
LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_banners.city
ORDER BY %1$stbl_banners.date_created DESC', DB_TBL_PREFIX);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $banners_data[] = $row;
        }
    
     }
    mysqli_free_result($result);
}



$redis = connectRedis();


if(isset($_GET['action']) && $_GET['action'] == "del"){ //delete banner button clicked
    if(DEMO){
        $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
        header("location: ".SITE_URL."admin/banners.php"); //Yes? then redirect 
        exit;
    }

    $bid = empty($_GET['bid']) ? 0 : (int) $_GET['bid'];
    
    //delete banner database record
    $query = sprintf('DELETE FROM %stbl_banners WHERE id=%d', 
        DB_TBL_PREFIX, 
        $bid
    );

    

    if(! $result = mysqli_query($GLOBALS['DB'], $query)){
        //echo mysqli_error($GLOBALS['DB']);
        
        $_SESSION['action_error'][] = "An error has occured. Could not delete banner. Ensure database connection is working";
    
        ob_start();
        include('../../drop-files/templates/admin/bannerstpl.php'); 
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
        



        $pageContent = ob_get_clean();
        $GLOBALS['admin_template']['page_content'] = $pageContent;
        include "../../drop-files/templates/admin/admin-interface.php";
        exit;

    
    }

        //refresh coupon codes data
        $banners_data = [];

        $query = sprintf('SELECT %1$stbl_banners.*, %1$stbl_routes.r_title FROM %1$stbl_banners 
        LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_banners.city
        ORDER BY %1$stbl_banners.date_created DESC', DB_TBL_PREFIX);


        if($result = mysqli_query($GLOBALS['DB'], $query)){
        
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    $banners_data[] = $row;
                }
            
            }
            mysqli_free_result($result);
        }



        $_SESSION['action_success'][] = "Banner deleted successfully!";
        ob_start();
        include('../../drop-files/templates/admin/bannerstpl.php'); 
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
        $pageContent = ob_get_clean();
        $GLOBALS['admin_template']['page_content'] = $pageContent;
        include "../../drop-files/templates/admin/admin-interface.php";
        exit;


}





if(empty($_POST)){ //let's render the page UI'
    
    ob_start();
    include('../../drop-files/templates/admin/bannerstpl.php');
    if(!empty($_SESSION['action_error'])){
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
    $pageContent = ob_get_clean();
    $GLOBALS['admin_template']['page_content'] = $pageContent;
    include "../../drop-files/templates/admin/admin-interface.php";
    exit;


}


if(DEMO){
    $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
    header("location: ".SITE_URL."admin/banners.php"); //Yes? then redirect 
    exit;
}





if(isset($_POST['add-banner'])){


                        
            if(empty($_POST['banner-title'])){

                $_SESSION['action_error'][]    = "Please enter a banner title";
            }


            if(empty($_POST['banner-excerpt'])){

                $_SESSION['action_error'][]    = "Please enter an excerpt";
            }


            if(empty($_POST['banner-content'])){

                $_SESSION['action_error'][]    = "Please enter a content for the banner";
            }


            if(empty($_POST['banner-fimg-data'])){
                $banner_image_filename = '';
            }else{
                $banner_img_encoded = $_POST['banner-fimg-data']; //Get Base64 encoded image data.
                $banner_img_encoded_array = explode(',', $banner_img_encoded);
                $image_data = array_pop($banner_img_encoded_array);
                $banner_img_decoded = base64_decode($image_data); //Decode the data

                
                if(!$banner_img_decoded){ //Verify that data is valid base64 data
                    
                    $_SESSION['action_error'][]    = "Please upload a valid image file";
                    
                } 

                //prepare filename and save the file. Cropit plugin has been configured to export base64 image data in JPEG format. We should be expecting a JPEG image data then.
                $banner_image_filename =  crypto_string('distinct',20) . '.jpg';

                $file = realpath('../img/') . '/' .  $banner_image_filename;
            
                
                file_put_contents($file, $banner_img_decoded); //store the photo to disk.  

            }


            if(!empty($_SESSION['action_error'])){
    
                ob_start();
                include('../../drop-files/templates/admin/bannerstpl.php'); 
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
                
        
        
        
                $pageContent = ob_get_clean();
                $GLOBALS['admin_template']['page_content'] = $pageContent;
                include "../../drop-files/templates/admin/admin-interface.php";
                exit;
        
        
        
            }

            

            $banner_city = (int) $_POST['banner-city'];

            $banner_visibility = (int) $_POST['banner-viz'];

            $banner_status = (int) $_POST['banner-status'];
           
            
            

            
            //Store banner data to database

            $query = sprintf('INSERT INTO %stbl_banners(title,excerpt,city,visibility,content,feature_img,`status`,date_created) VALUES'.

            '("%s","%s","%d","%d","%s","%s","%d","%s")', 
                DB_TBL_PREFIX, 
                mysqli_real_escape_string($GLOBALS['DB'], $_POST['banner-title']),
                mysqli_real_escape_string($GLOBALS['DB'], $_POST['banner-excerpt']),
                $banner_city,
                $banner_visibility,
                mysqli_real_escape_string($GLOBALS['DB'], $_POST['banner-content']),
                $banner_image_filename,
                $banner_status,
                gmdate('Y-m-d H:i:s', time())

            );


                if(! $result = mysqli_query($GLOBALS['DB'], $query)){
                    //echo mysqli_error($GLOBALS['DB']);
                    $err = mysqli_error($GLOBALS['DB']);
                    $_SESSION['action_error'][] = "{$err} An error has occured. Could not save new banner to database. Ensure database connection is working";
                
                    ob_start();
                    include('../../drop-files/templates/admin/bannerstpl.php'); 
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
                    



                    $pageContent = ob_get_clean();
                    $GLOBALS['admin_template']['page_content'] = $pageContent;
                    include "../../drop-files/templates/admin/admin-interface.php";
                    exit;

                
                }
                else{
                        $id = mysqli_insert_id ( $GLOBALS['DB'] );
                        
                    }

                    //refresh coupon codes data
                    $banners_data = [];

                    $query = sprintf('SELECT %1$stbl_banners.*, %1$stbl_routes.r_title FROM %1$stbl_banners 
                    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_banners.city
                    ORDER BY %1$stbl_banners.date_created DESC', DB_TBL_PREFIX);


                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                    
                        if(mysqli_num_rows($result)){
                            while($row = mysqli_fetch_assoc($result)){
                                $banners_data[] = $row;
                            }
                        
                        }
                        mysqli_free_result($result);
                    }




            
                    $_SESSION['action_success'][] = "Banner was added successfully.";
                    ob_start();
                    include('../../drop-files/templates/admin/bannerstpl.php'); 
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
                    $pageContent = ob_get_clean();
                    $GLOBALS['admin_template']['page_content'] = $pageContent;
                    include "../../drop-files/templates/admin/admin-interface.php";
                    exit;

}elseif(isset($_POST['edit-banner'])){

    

    if(empty($_POST['ebanner-title'])){

        $_SESSION['action_error'][]    = "Please enter a banner title";
    }


    if(empty($_POST['ebanner-excerpt'])){

        $_SESSION['action_error'][]    = "Please enter an excerpt";
    }


    if(empty($_POST['ebanner-content'])){

        $_SESSION['action_error'][]    = "Please enter a content for the banner";
    }


    if(empty($_POST['ebanner-fimg-data'])){
        $banner_image_filename = $_POST['e-banner-fimg-file'];
    }else{
        $banner_img_encoded = $_POST['ebanner-fimg-data']; //Get Base64 encoded image data.
        $banner_img_encoded_array = explode(',', $banner_img_encoded);
        $image_data = array_pop($banner_img_encoded_array);
        $banner_img_decoded = base64_decode($image_data); //Decode the data

        
        if(!$banner_img_decoded){ //Verify that data is valid base64 data
            
            $_SESSION['action_error'][]    = "Please upload a valid image file";
            
        } 

        //prepare filename and save the file. Cropit plugin has been configured to export base64 image data in JPEG format. We should be expecting a JPEG image data then.
        $banner_image_filename =  crypto_string('distinct',20) . '.jpg';

        $file = realpath('../img/') . '/' .  $banner_image_filename;
    
        
        file_put_contents($file, $banner_img_decoded); //store the photo to disk.  

    }
   


    if(!empty($_SESSION['action_error'])){
    
        ob_start();
        include('../../drop-files/templates/admin/bannerstpl.php'); 
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
        



        $pageContent = ob_get_clean();
        $GLOBALS['admin_template']['page_content'] = $pageContent;
        include "../../drop-files/templates/admin/admin-interface.php";
        exit;



    }

    $banner_id = (int) $_POST['ebanner-id'];

    $banner_city = (int) $_POST['ebanner-city'];

    $banner_visibility = (int) $_POST['ebanner-viz'];

    $banner_status = (int) $_POST['ebanner-status'];


    //update banner record on database
    $query = sprintf('UPDATE %stbl_banners SET title = "%s", excerpt = "%s",content ="%s",city=%d,visibility=%d,feature_img = "%s",`status`= %d,date_created = "%s" WHERE id=%d', 
        DB_TBL_PREFIX, 
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['ebanner-title']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['ebanner-excerpt']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['ebanner-content']),
        $banner_city,
        $banner_visibility,
        $banner_image_filename,
        $banner_status,
        gmdate('Y-m-d H:i:s', time()),
        $banner_id

    );

    

        if(! $result = mysqli_query($GLOBALS['DB'], $query)){
            //echo mysqli_error($GLOBALS['DB']);
            
            $_SESSION['action_error'][] = "An error has occured. Could not update banner on database. Ensure database connection is working";
        
            ob_start();
            include('../../drop-files/templates/admin/bannerstpl.php'); 
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
            



            $pageContent = ob_get_clean();
            $GLOBALS['admin_template']['page_content'] = $pageContent;
            include "../../drop-files/templates/admin/admin-interface.php";
            exit;

        
        }

            //refresh banner data
            $banners_data = [];

            $query = sprintf('SELECT %1$stbl_banners.*, %1$stbl_routes.r_title FROM %1$stbl_banners 
            LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_banners.city
            ORDER BY %1$stbl_banners.date_created DESC', DB_TBL_PREFIX);


            if($result = mysqli_query($GLOBALS['DB'], $query)){
            
                if(mysqli_num_rows($result)){
                    while($row = mysqli_fetch_assoc($result)){
                        $banners_data[] = $row;
                    }
                
                }
                mysqli_free_result($result);
            }




    
            $_SESSION['action_success'][] = "Banner was updated successfully.";
            ob_start();
            include('../../drop-files/templates/admin/bannerstpl.php'); 
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
            $pageContent = ob_get_clean();
            $GLOBALS['admin_template']['page_content'] = $pageContent;
            include "../../drop-files/templates/admin/admin-interface.php";
            exit;

}


?>