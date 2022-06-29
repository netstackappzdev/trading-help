<?php
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src=
"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.js"></script>
<body class="bg-light">
    
<div class="container">
    <main>
        <div class="py-5 text-center">
            <h2>Trading analyse</h2>
            <div class="alert alert-danger" role="alert" id="error-msg" style="display:none;"></div>
        </div>

        <div class="row g-5">
            <div class="col-md-5 col-lg-4 order-md-last">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">Report</span>
                </h4>
                <table class="table" id="table">
                    <thead>
                        <tr>
                            <th scope="col" data-field="buy_date">Buying Date</th>
                            <th scope="col" data-field="sell_date">Selling Date</th>
                            <th scope="col" data-field="profit">Profit</th>
                        </tr>
                    </thead>
                </table>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                        <h6 class="my-0">Mean</h6>
                        </div>
                        <span class="text-muted" id="total-mean"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between lh-sm">
                        <div>
                        <h6 class="my-0">Standard Deviation</h6>
                        </div>
                        <span class="text-muted" id="total-standard-deviation"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total Profit</span>
                        <strong id="total-profit"></strong>
                    </li>
                </ul>
            </div>

            <div class="col-md-7 col-lg-8">
                <h4 class="mb-3">Stock </h4>
                <form class="needs-validation" enctype="multipart/form-data" method="POST" action="process.php" id="stockAnalysForm" novalidate>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="stockName" class="form-label">Stock Name</label>
                            <input type="text" class="form-control" name="stockName"  id="stockName" placeholder="" value="" required/>
                            <div class="invalid-feedback">
                                Valid stock name is required.
                            </div>
                        </div>

                        <div class="col-sm-6">
                        </div>

                        <div class="col-12">
                            <label for="stockFile" class="form-label">Default file input example</label>  
                            <div class="input-group has-validation">
                                <input class="form-control" name="stockFile" type="file" id="stockFile" required/>
                                <div class="invalid-feedback">
                                    CSV file is required.
                                </div>
                            </div>
                        </div>


                        <div class="col-sm-6">
                            <label for="stockStartDate" class="form-label">Stock Name</label>
                            <div class="input-group date" id="datepickerstart">
                                <input type="text" class="form-control" id="stockStartDate" name="stockStartDate" required/>
                                <span class="input-group-append">
                                    <span class="input-group-text bg-light d-block">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </span>
                            </div>
                            <div class="invalid-feedback">
                                Please select a valid date.
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="stockEndDate" class="form-label">Stock Name</label>
                            <div class="input-group date" id="datepickerend">
                                <input type="text" class="form-control" id="stockEndDate" name="stockEndDate" required/>
                                <span class="input-group-append">
                                    <span class="input-group-text bg-light d-block">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </span>
                            </div>
                            <div class="invalid-feedback">
                                Please select a valid date.
                            </div>
                        </div>

                        <hr class="my-4">

                        <button id="submit" class="w-100 btn btn-primary btn-lg" type="submit">Continue to checkout</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script type="text/javascript">
//https://datahub.io/core/nasdaq-listings/r/nasdaq-listed.json

// Example starter JavaScript for disabling form submissions if there are invalid fields
$(document).ready(function() {

var productNames = new Array();
var productIds = new Object();

    $.getJSON( 'nasdaq-listed.json', null,
    function ( jsonData )
    {
        $.each( jsonData, function ( index, product )
        {
            productNames.push( product.Symbol );
            productIds[product.Symbol] = product.Symbol;
        } );
        $( '#stockName' ).typeahead( { source:productNames } );
    });


    $('#stockAnalysForm').on("submit", function(e){  
    var form = $("#stockAnalysForm")

    if (form[0].checkValidity() === false) {
      event.preventDefault()
      event.stopPropagation()
    } else {
        $('#table').bootstrapTable("destroy");
        $('#total-profit, #total-standard-deviation, #total-mean').html("");
        $('#error-msg').hide();
        var data = form.serialize();
        // console.log(data);
        $.ajax({
            type: "post",
            dataType: 'json',
            processData: false, // important
            contentType: false, // important
            data:new FormData(this),  
            url  : 'http://localhost:8080/stocktask/process.php',
            success :  function(response)
            {

                var $table = $('#table')
                var data = [];
                var responseData = response.reports.trading_dates;
                 console.log(responseData);
                // // Here you have to flat the array
                // Object.keys(responseData).forEach(function(key){ 
                //     var value = responseData[key]; 
                //     data.push(value);
                // })
                $table.bootstrapTable({data: responseData})
                $('#total-profit').html(response.reports.total_profit);
                $('#total-standard-deviation').html(response.reports.standard_deviation);
                $('#total-mean').html(response.reports.mean);
                if(response.msg){
                    $('#error-msg').html(response.msg);
                    $('#error-msg').show();
                }
                
            },
            error: function (data) {
                // console.log(data);
            }
        });
        return false;
    }
    form.addClass('was-validated');

  });    
});


$(function () {
    $("#datepickerstart,#datepickerend").datepicker();
});
</script>