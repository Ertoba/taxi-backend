<div class="page-header margin-bottom-0" style="background-color:#42a5f5">
		<div class="container" style="padding: 20px 10px;">
            <h2 style="margin:0;color:white;"><?php echo $GLOBALS['template']['page-heading'];?></h2>
			<ol style="text-align: right;padding: 0;margin: 0;">
                <?php
                    if (!empty($GLOBALS['template']['breadcrumbs'])){
                        $count = count($GLOBALS['template']['breadcrumbs']);
                        foreach ($GLOBALS['template']['breadcrumbs'] as $key => $value) {
                            
                ?>
				<li style="display:inline;">
					<a style="color:white;" href="<?php echo $value; ?>"><span><?php echo $key; ?></span></a>
                </li>
                <?php
                    $count--;
                    if($count){
                        echo "<li class='' style='display:inline;'><span>/</span></li>";
                    }
                ?>
                <?php
                        }
                    }
                ?>
			</ol>		
			
		</div>
</div>


