<?php
session_start();
include("../../drop-files/lib/common.php");
include "../../drop-files/config/db.php";

if(isset($_SESSION['expired_session'])){
    header("location: ".SITE_URL."login.php?timeout=1");
    exit;
}

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
  header("location: ".SITE_URL."login.php"); //Yes? then redirect user to the login page
  exit;
}

if($_SESSION['account_type'] /* != 2 && $_SESSION['account_type'] */ != 3){ ////if user is an admin
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}

$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-file-text'></i> Driver Documents"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "documents"; //Set the appropriate menu item active
$GLOBALS['admin_template']['active_sub_menu'] = "d_docs"; //Set the appropriate menu item active


//get all intra-city routes
$inter_city_routes = [];

$query = sprintf('SELECT * FROM %1$stbl_routes WHERE r_scope = 0 ORDER BY r_title ASC', DB_TBL_PREFIX);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $inter_city_routes[] = $row;
        }
    
     }
    mysqli_free_result($result);
}



//get all documents
$documents_data = [];

$query = sprintf('SELECT *,%1$stbl_documents.id AS did,%1$stbl_routes.id AS r_id FROM %1$stbl_documents 
LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_documents.doc_city
WHERE %1$stbl_documents.doc_user = %2$d ORDER BY %1$stbl_documents.date_created DESC', DB_TBL_PREFIX, 1);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $documents_data[] = $row;
        }
    
     }
    mysqli_free_result($result);
}



if(isset($_GET['status'])){ //document activate / deactivate button clicked

    if(DEMO){
        $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
        header("location: ".SITE_URL."admin/ddocuments.php"); //Yes? then redirect 
        exit;
    }

    $did = empty($_GET['did']) ? 0 : (int) $_GET['did'];
    $d_status = empty($_GET['status']) ? 0 : 1;


    

    //update document database record status
    $query = sprintf('UPDATE %stbl_documents SET `status` = %d WHERE id = %d', 
        DB_TBL_PREFIX, 
        $d_status,
        $did
    );

    

        if(! $result = mysqli_query($GLOBALS['DB'], $query)){
            //echo mysqli_error($GLOBALS['DB']);
            
            $_SESSION['action_error'][] = "An error has occured. Could not update document status. Ensure database connection is working";
        
            ob_start();
            include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

            //refresh documtns data
            $documents_data = [];

            $query = sprintf('SELECT *,%1$stbl_documents.id AS did,%1$stbl_routes.id AS r_id FROM %1$stbl_documents 
            LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_documents.doc_city
            WHERE %1$stbl_documents.doc_user = %2$d ORDER BY %1$stbl_documents.date_created DESC', DB_TBL_PREFIX, 1);


            if($result = mysqli_query($GLOBALS['DB'], $query)){
            
                if(mysqli_num_rows($result)){
                    while($row = mysqli_fetch_assoc($result)){
                        $documents_data[] = $row;
                    }
                
                }
                mysqli_free_result($result);
            }


    
            $_SESSION['action_success'][] = empty($d_status) ? "Document deactivated!" : "Document activated!";
            ob_start();
            include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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



}elseif(isset($_GET['action']) && $_GET['action'] == "del"){ //document activate / deactivate button clicked

    if(DEMO){
        $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
        header("location: ".SITE_URL."admin/ddocuments.php"); //Yes? then redirect 
        exit;
    }


    $did = empty($_GET['did']) ? 0 : (int) $_GET['did'];
    
    //delete document database record
    $query = sprintf('DELETE FROM %stbl_documents WHERE id=%d', 
        DB_TBL_PREFIX, 
        $did
    );

    

    if(! $result = mysqli_query($GLOBALS['DB'], $query)){
        //echo mysqli_error($GLOBALS['DB']);
        
        $_SESSION['action_error'][] = "An error has occured. Could not delete document. Ensure database connection is working";
    
        ob_start();
        include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

        //refresh documents data
        $documents_data = [];

        $query = sprintf('SELECT *,%1$stbl_documents.id AS did,%1$stbl_routes.id AS r_id FROM %1$stbl_documents 
        LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_documents.doc_city
        WHERE %1$stbl_documents.doc_user = %2$d ORDER BY %1$stbl_documents.date_created DESC', DB_TBL_PREFIX, 1);


        if($result = mysqli_query($GLOBALS['DB'], $query)){
        
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    $documents_data[] = $row;
                }
            
            }
            mysqli_free_result($result);
        }



        $_SESSION['action_success'][] = "Document deleted successfully!";
        ob_start();
        include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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
    include('../../drop-files/templates/admin/ddocumentstpl.php');
    $pageContent = ob_get_clean();
    $GLOBALS['admin_template']['page_content'] = $pageContent;
    include "../../drop-files/templates/admin/admin-interface.php";
    exit;


}


/* var_dump($_POST);
            exit; */


if(DEMO){
    $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
    header("location: ".SITE_URL."admin/ddocuments.php"); //Yes? then redirect 
    exit;
}


if(isset($_POST['add-document'])){


           
            
            if(empty($_POST['doc-title'])){

                $_SESSION['action_error'][]    = "Please enter the title of the document";
            }


            if(empty($_POST['doc-desc'])){

                $_SESSION['action_error'][]    = "Please enter the description of the document";
            }


            if(isset($_POST['doc-id-num-inp']) && $_POST['doc-id-num-inp'] == 1){

                if(empty($_POST['doc-id-num-title'])){

                    $_SESSION['action_error'][]    = "Please enter the title of the document identification number input field";
                }
    
    
                if(empty($_POST['doc-id-num-desc'])){

                    $_SESSION['action_error'][]    = "Please enter the description of the document identification number input field";
                }

            }

            $doc_id_num_inp = (int) $_POST['doc-id-num-inp'];
            $doc_expiry = (int) $_POST['doc-expiry'];
            $doc_type = (int) $_POST['doc-type'];
            $doc_city = (int) $_POST['doc-city'];

            

            
            //check if a document with the same title, city and account type entry already exists

            $query = sprintf('SELECT * FROM %stbl_documents WHERE title = "%s" AND doc_city = %d AND doc_type = %d AND doc_user = %d', DB_TBL_PREFIX, mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-title']), $doc_city, $doc_type,1); //Get required user information from DB


            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){
                    $_SESSION['action_error'][]    = "The document type already exists for the selected city";
                }
                
            }
            else{

                $_SESSION['action_error'][]    = "Database error!";
            }


            if(!empty($_SESSION['action_error'])){
            
                ob_start();
                include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

            //Store document data to database
            $query = sprintf('INSERT INTO %stbl_documents(title,doc_desc,doc_city,doc_type,doc_user,doc_expiry,doc_id_num,doc_id_num_title,doc_id_num_desc,`status`,date_created) VALUES'.
            '("%s","%s","%d","%d","%d","%d","%d","%s","%s","%d","%s")', 
                DB_TBL_PREFIX, 
                mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-title']),
                mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-desc']),
                $doc_city,
                $doc_type,
                1, //user type driver
                $doc_expiry,
                $doc_id_num_inp,
                isset($_POST['doc-id-num-title']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-id-num-title']) : " ",
                isset($_POST['doc-id-num-desc']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-id-num-desc']) : " ",
                1, //activated
                gmdate('Y-m-d H:i:s', time())

            );


                if(! $result = mysqli_query($GLOBALS['DB'], $query)){
                    //echo mysqli_error($GLOBALS['DB']);
                    $err = mysqli_error($GLOBALS['DB']);
                    $_SESSION['action_error'][] = "{$err} An error has occured. Could not save new document data to database. Ensure database connection is working";
                
                    ob_start();
                    include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

                    //refresh document data
                    $documents_data = [];

                    $query = sprintf('SELECT *,%1$stbl_documents.id AS did,%1$stbl_routes.id AS r_id FROM %1$stbl_documents 
                    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_documents.doc_city
                    WHERE %1$stbl_documents.doc_user = %2$d ORDER BY %1$stbl_documents.date_created DESC', DB_TBL_PREFIX, 1);


                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                    
                        if(mysqli_num_rows($result)){
                            while($row = mysqli_fetch_assoc($result)){
                                $documents_data[] = $row;
                            }
                        
                        }
                        mysqli_free_result($result);
                    }




            
                    $_SESSION['action_success'][] = "Document was added successfully.";
                    ob_start();
                    include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

}elseif(isset($_POST['edit-document'])){

     

    if(empty($_POST['doc-title'])){

        $_SESSION['action_error'][]    = "Please enter the title of the document";
    }


    if(empty($_POST['doc-desc'])){

        $_SESSION['action_error'][]    = "Please enter the description of the document";
    }


    if(isset($_POST['doc-id-num-inp']) && $_POST['doc-id-num-inp'] == 1){

        if(empty($_POST['doc-id-num-title'])){

            $_SESSION['action_error'][]    = "Please enter the title of the document identification number input field";
        }


        if(empty($_POST['doc-id-num-desc'])){

            $_SESSION['action_error'][]    = "Please enter the description of the document identification number input field";
        }

    }

    $doc_id = (int) $_POST['doc-id'];
    $doc_id_num_inp = (int) $_POST['doc-id-num-inp'];
    $doc_expiry = (int) $_POST['doc-expiry'];
    $doc_type = (int) $_POST['doc-type'];
    $doc_city = (int) $_POST['doc-city'];
    

    
    

    if(!empty($_SESSION['action_error'])){
    
        ob_start();
        include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

    //Update data to database
    $query = sprintf('UPDATE %stbl_documents SET title = "%s",doc_desc = "%s",doc_city = "%d",doc_type = "%d",doc_user = "%d",doc_expiry = "%d",doc_id_num = "%d",doc_id_num_title = "%s",doc_id_num_desc = "%s",date_created = "%s"
    WHERE id = %d', 
        DB_TBL_PREFIX, 
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-title']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-desc']),
        $doc_city,
        $doc_type,
        1, //user type driver
        $doc_expiry,
        $doc_id_num_inp,
        isset($_POST['doc-id-num-title']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-id-num-title']) : " ",
        isset($_POST['doc-id-num-desc']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['doc-id-num-desc']) : " ",
        gmdate('Y-m-d H:i:s', time()),
        $doc_id

    );


        if(! $result = mysqli_query($GLOBALS['DB'], $query)){
            //echo mysqli_error($GLOBALS['DB']);
            $err = mysqli_error($GLOBALS['DB']);
            $_SESSION['action_error'][] = "{$err} An error has occured. Could not update document. Ensure database connection is working";
        
            ob_start();
            include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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

            //refresh document data
            $documents_data = [];

            $query = sprintf('SELECT *,%1$stbl_documents.id AS did,%1$stbl_routes.id AS r_id FROM %1$stbl_documents 
            LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_documents.doc_city
            WHERE %1$stbl_documents.doc_user = %2$d ORDER BY %1$stbl_documents.date_created DESC', DB_TBL_PREFIX, 1);


            if($result = mysqli_query($GLOBALS['DB'], $query)){
            
                if(mysqli_num_rows($result)){
                    while($row = mysqli_fetch_assoc($result)){
                        $documents_data[] = $row;
                    }
                
                }
                mysqli_free_result($result);
            }




    
            $_SESSION['action_success'][] = "Document was updated successfully.";
            ob_start();
            include('../../drop-files/templates/admin/ddocumentstpl.php'); 
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