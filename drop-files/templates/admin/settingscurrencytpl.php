<div class="row">
    <div class="col-sm-12">   
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Add New</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <br />

                <a class='btn btn-sm btn-primary' href="#" data-toggle="modal" data-target="#add-currency" >Add new currency</a>



            </div><!-- /.box-body -->
        </div>
    </div><!--/col-sm-12-->
</div>


<div class="row">
    <div class="col-sm-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Currencies</h3>            
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>#</th>    
                                <th style="">Currency</th>
                                <th style="">Iso Code</th>
                                <th style="">Symbol</th>
                                <th style="">Exchng. Rate</th>
                                <th style="">Default</th>
                                <th style="">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $count = 0;
                                
                                foreach($currency_page_items as $currencypageitems){
                                    $currency_name = strtoupper($currencypageitems['name']);
                                    $iso_code = strtoupper($currencypageitems['iso_code']);
                                    $default_set = !empty($currencypageitems['default']) && $currencypageitems['default'] == 1 ? "<i class='fa fa-circle' style='color:green'></i>" : "<i class='fa fa-circle' style='color:#eee'></i>";
                                    $default_set_val = !empty($currencypageitems['default']) && $currencypageitems['default'] == 1 ? 1 : 0;

                                    $count++;
                                    $symbol = htmlspecialchars($currencypageitems['symbol']);
                                    echo "<tr><td>{$count}</td><td>{$currency_name}</td><td>{$iso_code}</td><td>{$symbol}</td><td>{$currencypageitems['exchng_rate']}</td><td>{$default_set}</td><td><a class='btn btn-xs btn-success edit-cur-btn' href='#' data-curname='{$currency_name}' data-curid='{$currencypageitems['id']}' data-curdefault='{$default_set_val}' data-curexchng='{$currencypageitems['exchng_rate']}' >Edit</a></td></tr>";
                                }

                            ?>
                        </tbody>

                        

                    </table>

                </div>
            
                    
                                
            </div><!-- /.box-body -->
        </div>
    </div>
</div>



<div class="modal fade" id="add-currency" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel">Add New Currency</h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p>Select a currency from the list</p>
                                <select class="form-control" id="currency-list" name="currency-list">
                                    <option value="0" >Select Currency</option>
                                    <?php
                                    foreach($currency_list_items as $currencylistitems){
                                        echo "<option value='{$currencylistitems['id']}' >{$currencylistitems['name']} ({$currencylistitems['code']})</option>" . "\n";
                                    }

                                    ?>                            
                                </select>

                                
                            </div>                     
                        </div>
                        
                        <div id="curency_settings" style="display:none;">
                            <div class="form-group">
                                
                                <div class="col-sm-12">
                                    <p style="margin-top: 15px;margin-bottom: 2px;">Exchange rate to default currency</p>
                                    <input  type="number"  min="0" step="0.00001" required= "required" class="form-control" id="exchange-rate" placeholder="" name="exchange-rate" value="" > 
                                </div>  
                                    
                            </div>
                             <br>           
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="default-currency">Mark as default</label>
                                    <input type="checkbox" id="defaut-currency" name="defaut-currency">
                                    <p id="currency-change-info" style="color:red;display:none;">Marking a currency as default sets the exchange rate to 1.00000. Please ensure you set the exchange rates of already added currencies to the correct value to reflect this change.</p>
                                    <br>
                                </div>
                            </div>
                        </div>

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" name="newcurrency" >Add Currency</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>





<div class="modal fade" id="edit-currency" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog loader modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-currency-label">Edit - </h4>
            </div>
            <div class="modal-body">

                    <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                        <input  type="text" id="cur-id" hidden="hidden" name="cur-id" value="0" />                        
                        <div class="form-group">
                            
                            <div class="col-sm-12">
                                <p style="margin-top: 15px;margin-bottom: 2px;">Exchange rate</p>
                                <input  type="number"  min="0" step="0.00001" required= "required" class="form-control" id="edit-exchange-rate" placeholder="" name="edit-exchange-rate" value="" > 
                            </div>  
                                
                        </div>
                            <br>           
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="edit-default-currency">Mark as default</label>
                                <input type="checkbox" id="edit-defaut-currency" name="edit-defaut-currency">
                                <p id="edit-currency-change-info" style="color:red;display:none;">Marking a currency as default sets the exchange rate to 1.00000. Please ensure you set the exchange rates of already added currencies to the correct value to reflect this change.</p>
                                <br>
                            </div>
                        </div>
                        

                        <hr />
                        <button type="submit" class="btn btn-primary" value="1" id="editcurrency" name="editcurrency" >Update Currency</button> 


                    </form>
                    <br />
                    
            </div>
        </div>
    </div>
</div>



<script>


    $('#add-currency').on('shown.bs.modal', function () {

        if($('#currency-list').val() != 0){ //selected a valid currency
            $('#curency_settings').show(); 
        }else{
            $('#curency_settings').hide(); 
            $('#exchange-rate').val("");
        }
      
    });


    $('.edit-cur-btn').on('click', function(){
        $('#edit-currency-label').html('Edit - ' + $(this).data('curname'));
        $('#edit-exchange-rate').val($(this).data('curexchng'));
        $('#cur-id').val($(this).data('curid'));
        if($(this).data('curdefault')){
            $('#edit-defaut-currency').prop('checked', true);
            $('#edit-defaut-currency').attr('disabled','disabled');
            $('#edit-exchange-rate').attr('disabled','disabled');
            $('#editcurrency').attr('disabled','disabled');
            $('#edit-currency-change-info').hide();
        }else{
            $('#edit-defaut-currency').prop('checked', false);
            $('#edit-defaut-currency').removeAttr('disabled');
            $('#edit-exchange-rate').removeAttr('disabled');
            $('#editcurrency').removeAttr('disabled');
            $('#edit-currency-change-info').hide();
        }
        $('#edit-currency').modal('show');
    })


    


    $('#currency-list').on('change', function(){
        if($('#currency-list').val() != 0){ //selected a valid currency
            $('#curency_settings').show(); 
        }else{
            $('#curency_settings').hide(); 
            $('#exchange-rate').val("");
        }
    });

    jQuery('#defaut-currency').click(function() {
        if(jQuery('#defaut-currency').is(':checked')){

            $('#currency-change-info').show();
            //$('#currency-change-info').css('visibility','visible');
            
        }else{
            $('#currency-change-info').hide();
            //$('#currency-change-info').css('visibility','hidden');
        }
     }); 


     jQuery('#edit-defaut-currency').click(function() {
        if(jQuery('#edit-defaut-currency').is(':checked')){

            $('#edit-currency-change-info').show();
            //$('#currency-change-info').css('visibility','visible');
            
        }else{
            $('#edit-currency-change-info').hide();
            //$('#currency-change-info').css('visibility','hidden');
        }
     });

    



</script>