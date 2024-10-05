<?php   
    $service = [];
    $service['blogger outreach']['dr_30'] = 120;
    $service['blogger outreach']['dr_40'] = 160;
    $service['blogger outreach']['dr_50'] = 250;
    $service['blogger outreach']['dr_60'] = 300;
    $service['ecommerce seo']['LITE Package'] = 1199;
    $service['ecommerce seo']['PRO Package'] = 1799;
    $service['ecommerce seo']['ENT Package'] = 2499;
    $service['keyword research']['$99/Report'] = 99;
    $service['pdp optimization']['$99/Content per page'] = 99;
    $service['seo audit reports']['Basic Report'] = 99;
    $service['seo audit reports']['PRO Report'] = 149;
    $service['blog management']['Basic Package'] = 1000;
    $service['blog management']['Standard Package'] = 1350;
    $service['blog management']['Premium Package'] = 2000;
   
    $prices = $service;
    $service_title = [];
    $service_title['blogger outreach']['dr_30'] = "DR 30+ WEBSITES";
    $service_title['blogger outreach']['dr_40'] = "DR 40+ WEBSITES";
    $service_title['blogger outreach']['dr_50'] = "DR 50+ WEBSITES";
    $service_title['blogger outreach']['dr_60'] = "DR 60+ WEBSITES";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="author" content="Blue Wolf Solutions">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title><?php echo $data['title']; ?> | Client Portal</title>
    <style>
        .ribbon {
            --f: 10px;
            --r: 15px;
            --t: 10px;
            min-width: 100px;
            position: absolute;
            inset: var(--t) calc(-1*var(--f)) auto auto;
            padding: 0 10px var(--f) calc(10px + var(--r));
            clip-path: polygon(0 0,100% 0,100% calc(100% - var(--f)),calc(100% - var(--f)) 100%, calc(100% - var(--f)) calc(100% - var(--f)),0 calc(100% - var(--f)), var(--r) calc(50% - var(--f)/2));
            color: #fff;
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 1.5px;
            z-index: 9;
            box-shadow: 0 calc(-1*var(--f)) 0 inset #0005;
            background-color: #4CAF50;
            padding: 5px 0px 15px 40px;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            <?php if (isset($_GET['downloadview'])): ?>
              background-color: #fff;
            <?php else: ?>
              background-color: #f4f4f4;
            <?php endif ?>
        }
        .invoice-box {
            position: relative;
            max-width: 800px;
            margin: auto;
            padding: 30px;
            <?php if (isset($_GET['downloadview'])): ?>
            <?php else: ?>
              border: 1px solid #eee;
              box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            <?php endif; ?>            
            background-color: #ffffff;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        #particulars {
            border: 1px solid #eee;
        }
        #particulars tr td:nth-child(2) {
            text-align: left;
        }        
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading th {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }        
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }
            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            line-height: 1.53;
            color: #697a8d;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.4375rem 1.25rem;
            font-size: 0.9375rem;
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
        }
        .btn-sm, .btn-group-sm > .btn {
            padding: 0.25rem 0.6875rem;
            font-size: 0.75rem;
            border-radius: 0.25rem;
        }
        .btn-primary {
            color: #fff;
            background-color: #696cff;
            border-color: #696cff;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        }
        .btn-success {
            color: #fff;
            background-color: #71dd37;
            border-color: #71dd37;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(113, 221, 55, 0.4);
        }
        .btn-success, .bg-success {
            background-color: #4CAF50 !important;
            border: #4CAF50 !important;
        }
        .me-2 {
            margin-right: 0.5rem !important ;
        }
        .clear {
            clear: both;
        }
        .clear20 {
            clear: both;
            height: 20px;
        }
        .mt-3 {
            margin-top: 1rem !important;
        }
        .d-none {
            display: none !important;
        }
        .d-flex {
            display: flex !important;
        }
        .justify-content-end {
            justify-content: flex-end !important;
        }
        label {
            display: inline-block;
        }
        .form-label {
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #566a7f;
        }    
        .form-label, .col-form-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: inherit;
        }
        .pointer {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="invoice-box">        
        <?php if ($data['order_details']['paid_with']!=null): ?>
            <?php if (!isset($_GET['downloadview'])): ?>
                <div class="ribbon bg-success">PAID</div>   
            <?php endif; ?> 
        <?php endif ?>
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td colspan="2" class="title">
                                <!-- Your Logo Here -->
                                <img src="https://app.creativebraindesign.com/images/invoice-logo.png" style="width:100%; max-width:200px;">
                            </td>
                        </tr>
                        <tr>
                          <td colspan="2" style="padding-bottom: 0;">
                            <h1 style="color:#172c5c;margin:0;text-align: center;font-weight: bold;">Creative Brains For Design & Marketing</h1>
                            <p style="text-align: center;margin:0;font-size: 14px;font-weight: bold;">108, Aggarwal City Square, Mangalam Place, Sector-3, Rohini New Delhi-110085</p>
                          </td>
                        </tr>
                        <tr>
                          <td style="padding-bottom: 0;"><p style="text-align: center;margin:0;font-size: 14px;font-weight: bold;">Email: <a href="mailto:info@creativebraindesign.com">info@creativebraindesign.com</a></p></td>
                          <td style="padding-bottom: 0;"><p style="text-align: center;margin:0;font-size: 14px;font-weight: bold;">Website: <a href="https://creativebraindesign.com" target="_blank">https://creativebraindesign.com</a></p></td>
                        </tr>
                        <tr>
                          <td colspan="2" style="padding-bottom: 50px;"><p style="text-align: center;margin:0;font-size: 14px;font-weight: bold;">GST: 07AEBPG0991F2ZR</p></td>
                        </tr>                                                        
                        <tr>                            
                            <td>
                                Invoice #: <?php echo $data['order_details']['order_id']; ?>
                            </td>
                            <td>
                                Date of Issue: <?php echo date("d/m/Y",strtotime($data['order_details']['date_ordered'])); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <p style="margin-top:0;"><b>Bill To</b></p>
                                <p style="margin:0;"><?php echo ucwords($data['client_details']['first_name']." ".$data['client_details']['last_name']); ?></p>
                                <p style="margin:0;"><?php echo ucfirst($data['client_details']['billing_address']); ?></p>

                                <?php if ($data['order_details']['paid_with']!=null): ?>
                                    <p style="margin:20px 0 0;">Payment Mode: <?php 
                                      if ($data['order_details']['paid_with']!=null && $data['order_details']['paid_with']!='') {
                                        echo ucwords($data['order_details']['paid_with']);
                                      } else {
                                        echo ucwords(str_replace("_", " ", $data['order_details']['payment_method']));  
                                      }
                                    ?></p>
                                    <p style="margin:0;">Payment Date: <?php echo date("d/m/Y",strtotime($data['order_details']['payment_date'])); ?></p>
                                <?php else: ?>
                                    <?php if (!isset($_GET['downloadview'])): ?>
                                        <div class="clear20"></div>
                                        <button id="makePayment" class="btn btn-sm btn-primary">Make Payment</button>
                                        <div class="clear"></div>
                                        <div id="paymentCheckoutBox" class="mt-3 d-none">
                                            <div>
                                                <label class="form-label">Select payment method:</label>
                                                <div class="clear5"></div>  
                                                <div>
                                                    <label for="card-payment-method" class="pointer">
                                                        <input id="card-payment-method" type="radio" name="payment_method" value="stripe" required> Stripe
                                                    </label>
                                                    <label for="paypal-payment-method" class="pointer ms-3">
                                                        <input id="paypal-payment-method" type="radio" name="payment_method" value="paypal" required> Paypal
                                                    </label> 
                                                </div>                               
                                                <div class="clear20"></div>
                                            </div>
                                            <div class="d-flex">
                                                <div id="paypal-button-container" class="d-none" style="max-width: 200px;"></div>
                                                <div id="stripe-button-container" class="d-none">
                                                  <button id="stripeBtn" class="btn btn-success me-2" type="button">Pay with Stripe</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif ?>
                                <?php endif ?>
                                
                                <p style="margin:20px 0 0;">Service: <?php 
                                $service = str_replace("pdp","PDP",$data['order_details']['service']);
                                $service = str_replace("seo","SEO",$service);
                                echo ucwords($service) ?></p>
                            </td>                            
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
          </table>
          <table id="particulars">
            <tr class="heading">
                <th width="50%">Particulars</th>
                <th width="10%">Qty</th>
                <th width="20%">Price</th>
                <th width="20%">Amount</th>
            </tr>
            <?php $total = 0; if ($data['order_details']['service']=="blogger outreach"): ?>
              <?php 
              foreach (json_decode($data['order_details']['line_items'],true) as $key => $value): ?>
                <?php if ($value>0): ?>
                  <tr class="item">
                    <td><?php echo $service_title[$data['order_details']['service']][$key]; ?></td>
                    <td><?php echo $value; ?></td>
                    <td>$<?php echo number_format($prices[$data['order_details']['service']][$key],2,".",","); ?></td>
                    <td>$<?php 
                    $total += ($value*$prices[$data['order_details']['service']][$key]);
                    echo number_format(($value*$prices[$data['order_details']['service']][$key]),2,".",",");
                     ?></td>
                  </tr>  
                <?php endif ?>
              <?php endforeach ?>
            <?php else: ?>
              <tr class="item">
                  <td><?php echo $data['order_details']['package']; ?></td>
                  <td>1</td>
                  <td>$<?php echo number_format($prices[$data['order_details']['service']][$data['order_details']['package']],2,".",","); ?></td>
                  <td>$<?php 
                  $total += (1*$prices[$data['order_details']['service']][$data['order_details']['package']]);
                  echo number_format((1*$prices[$data['order_details']['service']][$data['order_details']['package']]),2,".",",");
                   ?></td>
                </tr>
            <?php endif ?>
            <tr class="item">
                <td></td>
                <td></td>
                <td>Discount:</td>
                <td>$<?php 
                $discount = 0;
                if (isset($data['order_details']['coupon_discount'])) {
                  $discount = $data['order_details']['coupon_discount'];
                }                
                echo number_format($discount,2,".",","); ?></td>
            </tr>
            <tr class="item last">
                <td></td>
                <td></td>
                <td>Net Amount:</td>
                <td>$<?php $total -= $discount; 
                  echo number_format($total,2,".",",");
                  ?>
                </td>
            </tr>
        </table>
        <div style="clear:both;height: 50px;"></div>        
    </div>
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var loading_text = 'Loading...';

            $("#makePayment").click(function(){
              $(this).remove();
              $("#paymentCheckoutBox").removeClass("d-none");
            })

            <?php if ($data['order_details']['status']=="Awaiting Payment" && $_SESSION['role']=="client"): ?>

                $("input[name=payment_method]").change(function(){
                  if ($(this).val()=="paypal") {
                    $("#stripe-button-container").addClass("d-none")
                    $("#paypal-button-container").removeClass("d-none");        
                        paypal.Buttons({
                          style: {
                              layout: 'horizontal',
                              color:  'gold',
                              shape:  'rect',
                              label:  'pay',
                          },
                          createOrder: function(data, actions) {
                              return actions.order.create({
                                  purchase_units: [{
                                      amount: {
                                          value: <?php echo number_format($data['order_details']['amount']-$data['order_details']['coupon_discount'],2,".",""); ?>, // Replace with the amount you want to charge
                                          currency: 'USD'
                                      }
                                  }],
                                  intent: "CAPTURE"
                              });
                          },
                          onApprove: function(data, actions) {
                              return actions.order.capture().then(function(details) {
                                $("#paypal-button-container").html("<i class='bx bx-loader-circle bx-spin' style='font-size:30px;'></i>");
                                  $.post('/api/order-payment',{"order_id":"<?php echo $data['order_details']['uuid']; ?>","paymentID":data.paymentID,"token":localStorage.getItem("token"),"paid_with":$("input[name=payment_method]:checked").val()},function(response){
                                        var result = JSON.parse(response);
                                        if (result.status) {
                                          setTimeout(function(){
                                            location.reload();
                                          },2000);
                                        } else {
                                          alert(result.message)
                                        }
                                  })
                              });
                          }
                      }).render('#paypal-button-container');  

                  } else {
                    $("#paypal-button-container").addClass("d-none");
                    $("#paypal-button-container").html("");
                    $("#stripe-button-container").removeClass("d-none")
                  }
                })

                $("#stripeBtn").click(function(){
                  var currentUrl = window.location.href;
                  var $this = this;
                  var button_text = $($this).text();
                  $($this).attr("disabled","disabled");                
                  $($this).html(loading_text);
                  $.post("/api/stripe/checkout-link",{"token":localStorage.getItem("token"),"amount":"<?php echo number_format($data['order_details']['amount']-$data['order_details']['coupon_discount'],2,".",""); ?>","cancelURL":currentUrl,"service":"<?php echo $data['order_details']['service']; ?>","order_id":"<?php echo $data['order_details']['uuid']; ?>"},function(response){
                    var result;
                    if (typeof response === 'string') {
                        result = JSON.parse(response);
                    } else {
                      result = response;
                    }
                    $($this).removeAttr("disabled");                
                    $($this).html(button_text);
                    if (result.status) {
                      window.location = result.checkout_link;
                    } else {
                      alert(result.message)
                    }
                  })
                })

            <?php endif; ?>
        })        
    </script>
</body>
</html>    