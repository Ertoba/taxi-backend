<?php
$user_reviews_data = [];
$query_modifier  = " = {$id} AND " . DB_TBL_PREFIX . "tbl_bookings.status = 3";
$number_of_reviews_data = 0;


//get number of transactions
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_bookings 
INNER JOIN %1$stbl_ratings_users ON %1$stbl_ratings_users.booking_id = %1$stbl_bookings.id
INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
WHERE %1$stbl_bookings.driver_id %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_reviews_data = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page']) && (isset($_GET['tab']) && $_GET['tab'] == "sreviews")){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_reviews_data / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get reviews data
$query = sprintf('SELECT *, %1$stbl_bookings.id AS book_id, %1$stbl_ratings_drivers.driver_rating AS book_driver_rating FROM %1$stbl_bookings  
INNER JOIN %1$stbl_ratings_drivers ON %1$stbl_ratings_drivers.booking_id = %1$stbl_bookings.id
INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
WHERE %1$stbl_bookings.user_id %2$s ORDER BY %1$stbl_bookings.date_created DESC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $user_reviews_data[] = $row;
        }        
    }
}


?>






<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">

            <br />
            <div> <!--pages-->
            
                <?php
                    
                    
                    if(!empty($pages)){
                        $url = $_SERVER['REQUEST_URI'];
                        $url_parts = parse_url($url);
                        if(isset($url_parts['query'])){
                            parse_str($url_parts['query'], $params);
                        }
                        $params['tab'] = "sreviews";
                        echo "Pages: ";

                        if($page_number > 1){
                            
                            $params['page'] = 1;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> << </a>";

                            $prev_page = $page_number - 1;
                            $params['page'] = $prev_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> < </a>";

                        }
                        
                        // range of num links to show
                        $range = 2;

                        // display links to 'range of pages' around 'current page'
                        $initial_num = $page_number - $range;
                        $condition_limit_num = ($page_number + $range)  + 1;

                        
                        for($i = $initial_num;$i < $condition_limit_num + 1; $i++){

                            // be sure '$i is greater than 0' AND 'less than or equal to the $total_pages'
                            if (($i > 0) && ($i <= $pages)) {

                                if($i == $page_number){
                                    echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                }else{
                                    
                                    $params['page'] = $i;     // Overwrite if exists
                                    $url_parts['query'] = http_build_query($params);                                                
                                    echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'>".$i."</a>";
                                        
                                } 

                            }
                            
                             
                            
                        }

                        if($page_number < $pages){

                            $next_page = $page_number + 1;
                            $params['page'] = $next_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> > </a>";
                            
                            $params['page'] = $pages;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> >> </a>";

                            

                        }


                    }
                ?>
            </div><!--/pages-->
            <br />
            <div class="table-responsive">
                <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                    <th>#</th>    
                        <th>Driver</th>
                        <th>Booking ID</th>
                        <th>Rating</th>
                        <th>Review / Comment</th>                   
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    
                    
                    foreach($user_reviews_data as $userreviewsdata){
                        $transaction_type = '';
                        $booking_id = !empty($userreviewsdata['book_id']) ? "<a href='view-booking.php?bkid={$userreviewsdata['book_id']}'>" . str_pad($userreviewsdata['book_id'] , 5, '0', STR_PAD_LEFT) . "</a>" : 'N/A';
                        $driver_details = $userreviewsdata['driver_firstname'] . " " . $userreviewsdata['driver_lastname'] . "<br>" . $userreviewsdata['country_dial_code'] . " ". (!empty(DEMO) ? mask_string($userreviewsdata['driver_phone']) : $userreviewsdata['driver_phone']);
                        
                        $rating = !empty($userreviewsdata['book_driver_rating']) ? "<img src='../img/rating-{$userreviewsdata['book_driver_rating']}.png' style='width:100px;' />" : "";
                                                    
                        echo "<tr><td>{$count}</td><td>{$driver_details}</td><td>{$booking_id}</td><td>{$rating}</td><td>{$userreviewsdata['driver_comment']}</td></tr>";
                        $count++;
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($user_reviews_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Reviews.</h1>";} ?>
                        
            
                            
        </div><!-- /.box-body -->
    </div>


