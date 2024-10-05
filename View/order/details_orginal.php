<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">
  .table-responsive .table {
    min-width: 1000px !important;
  }  
  #orderTable {
    border: 1px solid #d9dee3;
    border-bottom: 0;
    background-color: #fff;
    table-layout: fixed; 
  }  
  #orderTable td.text-wrapped {
    white-space: nowrap;  /* Prevents text from wrapping to the next line */    

  }
  #orderTable tbody td.sheet-title {
    text-align: center;
    color: #000;
    border: 1px solid #d9dee3;
    border-top: 0;
    border-right: 0;
    line-height: 1;
    font-size: 12px;
    background-color: #eee;
  }
  #orderTable tbody td {
    padding: 6px 3px !important;
    border-bottom: 0;
    color: #000;
    border: 1px solid #d9dee3;
    border-top: 0;
    border-right: 0;
    line-height: 1;
    font-size: 12px;
    overflow: scroll;
  }
  #orderTable tbody td input {
    width: 100%;
    font-size: 12px;
    padding: 2px;
    border: 0;
    border-top: 0;
    border-right: 0;
    border-radius: 0;
    outline: none;
  }  
</style>
<!-- Layout container -->
<div class="layout-page">
    <!-- main nav -->
    <?php include('View/includes/menu-nav.php'); ?>

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <div class="col-md-12">
                  <!-- Striped Rows -->
                    <div class="card">
                      <h5 class="card-header pb-3">Order #<?php echo str_pad($data['order_details']['order_id'], 5, '0', STR_PAD_LEFT); ?>
                        <?php if ($_SESSION['role']=="admin"): ?>
                          <select class="form-control pull-right" name="status" style="max-width:250px;position: relative;top:-5px;">
                            <option value="Awaiting Payment" <?php if ($data['order_details']['status']=="Awaiting Payment") {
                              echo "selected";
                            } ?>>Awaiting Payment</option>
                            <option value="Paid" <?php if ($data['order_details']['status']=="Paid") {
                              echo "selected";
                            } ?>>Received & Paid</option>
                            <option value="Client Approval Required" <?php if ($data['order_details']['status']=="Client Approval Required") {
                              echo "selected";
                            } ?>>Client Approval Required</option>
                            <option value="In Process" <?php if ($data['order_details']['status']=="In Process") {
                              echo "selected";
                            } ?>>In Process</option>
                            <option value="Completed" <?php if ($data['order_details']['status']=="Completed") {
                              echo "selected";
                            } ?>>Completed</option>
                          </select>
                        <?php else: ?>                          
                            <span style="position: relative;top:-5px;" class="pull-right badge <?php if ($data['order_details']['status']!="Completed") { echo "bg-info"; } else { echo "bg-success"; } ?>"><?php echo str_replace("Paid","Paid & Received",$data['order_details']['status']); ?></span>
                                                    
                        <?php endif ?>                      
                      </h5>                      
                      <div class="card-body">
                        <?php if ($_SESSION['role']=="admin" && isset($data['client_details'])): ?>
                          <h4 class="m-0"><b><?php echo ucwords($data['client_details']['first_name']." ".$data['client_details']['last_name']." - ".$data['client_details']['company']); ?></b></h4>
                          <p class="mb-2"><small class="small text-muted"><?php echo $data['client_details']['email']; ?></small></p>
                        <?php endif ?>
                        <div class="row">
                          <div class="col-6">
                            <p class="m-0">Service: <b><?php 
                            $service = str_replace("seo","SEO",$data['order_details']['service']);
                            $service = str_replace("pdp","PDP",$service);
                            echo ucwords($service); ?></b></p>    
                            <?php 
                              if ($data['order_details']['service']=="blogger outreach") {
                                echo '<p class="m-0 ps-2">';
                                foreach (json_decode($data['order_details']['line_items'],true) as $key => $value) {
                                  if ($value>0) {
                                    if ($key=="dr_30") {
                                      echo "DR 30+ WEBSITES x".$value."<br>";
                                    } else if ($key=="dr_40") {
                                      echo "DR 40+ WEBSITES x".$value."<br>";
                                    } else if ($key=="dr_50") {
                                      echo "DR 50+ WEBSITES x".$value."<br>";
                                    } else if ($key=="dr_60") {
                                      echo "DR 60+ WEBSITES x".$value."<br>";
                                    }
                                  }
                                }
                                echo '</p><div class="clear10"></div>';
                              }
                             ?>

                            <?php if ($data['order_details']['service']=="blogger outreach" && isset($data['order_details']['pre_approved_websites']) && $data['order_details']['pre_approved_websites']!=null && $data['order_details']['pre_approved_websites']!=''): ?>
                              <p class="m-0">Pre-approve websites? <b><?php echo ($data['order_details']['pre_approved_websites']==1)? "Yes":"No"; ?></b></p>      
                            <?php endif ?>

                            <?php if ($data['order_details']['service']=="blogger outreach" && isset($data['order_details']['anchor_landing_pages']) && $data['order_details']['anchor_landing_pages']!=null && $data['order_details']['anchor_landing_pages']!=''): ?>
                              <p class="m-0">Own set of anchor and landing pages? <b><?php echo ($data['order_details']['anchor_landing_pages']==1)? "Yes":"No"; ?></b></p>      
                            <?php endif ?>

                            <?php if (isset($data['order_details']['package']) && $data['order_details']['package']!=null && $data['order_details']['package']!=''): ?>
                              <p class="m-0">Package: <b><?php echo $data['order_details']['package']; ?></b></p>
                            <?php endif ?>
                            <?php if (isset($data['order_details']['website']) && $data['order_details']['website']!=null && $data['order_details']['website']!=''): ?>
                              <p class="m-0">Website: <b><?php echo $data['order_details']['website']; ?></b></p>
                            <?php endif ?>
                            <?php if (isset($data['order_details']['no_pages']) && $data['order_details']['no_pages']!=null && $data['order_details']['no_pages']!=''): ?>
                              <p class="m-0">Number pages: <b><?php echo $data['order_details']['no_pages']; ?></b></p>
                            <?php endif ?>
                            
                            <?php if ($data['order_details']['doc_link']!=null && $data['order_details']['doc_link']!='' && $_SESSION['role']=="client"): ?>
                              <p class="m-0"><a href="<?php echo $data['order_details']['doc_link']; ?>" target="_blank">Open Sheet <i class='bx bx-link-external'></i></a></p>
                            <?php elseif($data['order_details']['service']=="blogger outreach" && ($data['order_details']['doc_link']==null || $data['order_details']['doc_link']=='') || $_SESSION['role']=="admin"): ?>
                              <form id="docLinkForm" class="mt-3 mb-2">
                                <div class="row">
                                  <div class="col-9">
                                    <input type="text" name="doc_link" class="form-control" value="<?php if($data['order_details']['doc_link']!="" && $data['order_details']['doc_link']!=null) { echo $data['order_details']['doc_link']; } ?>" placeholder="SHARE SPREADSHEET/DOC LINK" required>
                                  </div>
                                  <div class="col-3">
                                    <button type="submit" class="btn btn-primary btn">Update</button>
                                  </div>
                                </div>
                              </form>                              
                              <?php if ($data['order_details']['service']=="blogger" && ($data['order_details']['doc_link']==null || $data['order_details']['doc_link']=='')): ?>
                                <p class="m-0 mb-2 text-danger">No sheet link found</p>  
                              <?php endif ?>
                            <?php endif ?>

                            <p class="m-0">Comments: <br><i><?php echo ucfirst($data['order_details']['comments']); ?></i></p>
                          </div>
                          <div class="col-6 text-end">
                            <p class="m-0">Amount: <b>$<?php echo number_format($data['order_details']['amount'],2,".",","); ?></b></p>                                
                            <p class="m-0">Payment Method: <b><?php 
                            if ($data['order_details']['paid_with']!=null && $data['order_details']['paid_with']!='') {
                              $payment_method = $data['order_details']['paid_with'];
                            } else {
                              $payment_method = $data['order_details']['payment_method'];
                            }
                            echo ucwords(str_replace("_"," ", $payment_method)); ?></b></p>    
                            <p class="m-0">Order Date: <b><?php echo date("d/m/Y",strtotime($data['order_details']['date_ordered'])); ?></b></p>    

                            <?php if ($data['order_details']['status']=="Awaiting Payment" && $_SESSION['role']=="client"): ?>
                              <div class="clear20"></div>
                              <button id="makePayment" type="button" class="pull-right btn btn-primary btn-sm">Make Payment</button>
                              <div class="clear"></div>
                              <div id="paymentCheckoutBox" class="mt-3 d-none">
                                <div>
                                    <label class="form-label">Select payment method:</label>
                                    <div class="clear5"></div>  
                                    <div>
                                        <label for="card-payment-method" class="pointer">
                                            <input id="card-payment-method" type="radio" name="payment_method" value="razorpay" required> Razorpay
                                        </label>
                                        <label for="paypal-payment-method" class="pointer ms-3">
                                            <input id="paypal-payment-method" type="radio" name="payment_method" value="paypal" required> Paypal
                                        </label> 
                                    </div>                               
                                    <div class="clear20"></div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <div style="margin-right:148px;" id="loading-div" class="d-none"><i class='bx bx-loader-circle bx-spin'></i></div>
                                    <div id="paypal-button-container" class="d-none" style="max-width: 200px;"></div>
                                    <div id="razorpay-button-container" class="d-none">
                                      <button id="razorpayBtn" class="btn btn-success me-2" type="button">Pay with Razorpay</button>
                                    </div>
                                </div>
                            </div>
                            <?php endif ?>                            
                          </div>                          
                        </div>
                            <form id="orderForm" class="mt-4">
                              <input type="hidden" name="uuid" value="<?php echo $data['order_details']['uuid']; ?>">
                              <?php if ($data['order_details']['doc_link']!="" && $data['order_details']['doc_link']!=null && $data['order_details']['line_items']!=null && $data['order_details']['line_items']!=''): ?>
                                <?php if ($_SESSION['role']=="admin"): ?>
                                  <div class="text-start mb-3">
                                  <button id="syncSheetBtn" data-id="<?php echo $data['order_details']['uuid']; ?>" type="button" class="btn btn-success btn-sm">Sync from Sheet</button>
                                </div>
                                <?php endif ?>                                
                              <div class="table-responsive">
                                <table id="orderTable" class="table">
                                  <thead>
                                    <tr>
                                      <th style="width:70px;">DR</th>
                                      <th style="width:100px;">Traffic</th>
                                      <th>Prospect URL</th>
                                      <th>Anchor Text</th>
                                      <th>Target URL</th>
                                      <th>Published Link</th>
                                    </tr>
                                  </thead>
                                  <tbody> 
                                    <?php if ($data['order_details']['order_details']==null): ?>
                                        <?php foreach (json_decode($data['order_details']['line_items'],true) as $key => $value):?>
                                          <?php for ($i=0; $i < $value; $i++): ?> 
                                            <tr>
                                              <?php if ($_SESSION['role']=="admin"): ?>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][dr]"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][traffic]"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][prospect_url]"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][anchor_text]"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][target_url]"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][published_link]"/></td>
                                              <?php else: ?>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                              <?php endif ?>
                                              
                                            </tr> 
                                          <?php endfor; ?>                              
                                      <?php endforeach ?>
                                    <?php else: ?>
                                      <?php 

                                      $orderDetails = json_decode($data['order_details']['order_details'],true);
                                      
                                      foreach ($orderDetails as $key => $row):
                                        foreach ($row as $i => $value):?>
                                          <?php if ($i==0): ?>
                                            <tr>
                                              <td colspan="6" class="sheet-title">
                                                <b><?php echo $key; ?></b>
                                              </td>
                                            </tr> 
                                          <?php endif ?>
                                          <tr>
                                            <?php if ($_SESSION['role']=="admin"): ?>
                                              <?php if ($data['order_details']['status']!="Completed"): ?>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][dr]" value="<?php echo $orderDetails[$key][$i]['dr']; ?>"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][traffic]" value="<?php echo $orderDetails[$key][$i]['traffic']; ?>"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][prospect_url]" value="<?php echo $orderDetails[$key][$i]['prospect_url']; ?>"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][anchor_text]" value="<?php echo $orderDetails[$key][$i]['anchor_text']; ?>"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][target_url]" value="<?php echo $orderDetails[$key][$i]['target_url']; ?>"/></td>
                                                <td><input type="text" name="order_details[<?php echo $key; ?>][<?php echo $i; ?>][published_link]" value="<?php echo $orderDetails[$key][$i]['published_link']; ?>"/></td>
                                              <?php else: ?>                                        
                                                <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['dr']; ?></td>
                                                <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['traffic']; ?></td>
                                                <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['prospect_url']; ?></td>
                                                <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['anchor_text']; ?></td>
                                                <td class="p-1 text-wrapped"><?php
                                                  $target_url = $orderDetails[$key][$i]['target_url'];
                                                  // Check if the string is a valid URL
                                                  if (filter_var($target_url, FILTER_VALIDATE_URL)) {
                                                      echo "<a href='".$target_url."' target='_blank'>".$target_url."</a>";
                                                  } else {
                                                      echo $orderDetails[$key][$i]['target_url'];
                                                  } ?></td>
                                                <td class="p-1 text-wrapped"><a href="<?php echo $orderDetails[$key][$i]['published_link']; ?>" target="_blank"><?php echo $orderDetails[$key][$i]['published_link']; ?></a></td>
                                              <?php endif ?>
                                            <?php else: ?>
                                              <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['dr']; ?></td>
                                              <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['traffic']; ?></td>
                                              <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['prospect_url']; ?></td>
                                              <td class="p-1 text-wrapped"><?php echo $orderDetails[$key][$i]['anchor_text']; ?></td>
                                              <td class="p-1 text-wrapped"><span><?php
                                                $target_url = $orderDetails[$key][$i]['target_url'];
                                                // Check if the string is a valid URL
                                                if (filter_var($target_url, FILTER_VALIDATE_URL)) {
                                                    echo "<a href='".$target_url."' target='_blank'>".$target_url."</a>";
                                                } else {
                                                    echo $orderDetails[$key][$i]['target_url'];
                                                } ?></span></td>
                                              <td class="p-1 text-wrapped"><a href="<?php echo $orderDetails[$key][$i]['published_link']; ?>" target="_blank"><?php echo $orderDetails[$key][$i]['published_link']; ?></a></td>
                                            <?php endif ?>                                          
                                          </tr>                     
                                        <?php endforeach ?>
                                      <?php endforeach ?>
                                    <?php endif ?>                                                       
                                  </tbody>
                                </table>
                              </div>
                              <?php if ($data['order_details']['status']!="Completed" && $data['order_details']['status']!="Awaiting Payment"): ?>
                                <?php if ($data['order_details']['status']=="Client Approval Required" && $_SESSION['role']=="client"): ?>
                                  <div class="text-end mt-3">
                                    <button type="button" class="approveBtn btn btn-primary btn-sm">Approved</button>
                                  </div> 
                                <?php elseif ($data['order_details']['status']=="Paid" && $_SESSION['role']=="client"): ?>   
                                <?php elseif ($_SESSION['role']=="admin"): ?>
                                  <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary btn-sm">Update Order</button>
                                  </div>
                                <?php endif ?>                                  
                              <?php endif ?>                                  
                            <?php endif ?> 
                          </form>                       
                      </div>
                    </div>
                    <!--/ Striped Rows -->
                </div>
              </div>            
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>
            <?php if ($data['order_details']['status']=="Awaiting Payment" && $_SESSION['role']=="client"): ?>
                <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <?php endif; ?>
            <script type="text/javascript">              
              $(document).ready(function(){     
                $("#syncSheetBtn").click(function(){
                  var $this = this;
                  $($this).attr("disabled","disabled");                
                  $($this).html(loading_text);
                  $.post("/api/sync-sheet-data",{"token":localStorage.getItem("token"),"order_id":$(this).data("id")},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      location.reload();
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                });

                $("#orderForm").submit(function(e){
                  e.preventDefault();
                  var not_complete = false;
                  $("#orderForm input[type=text]").each(function(){
                    if ($(this).val()=="") {
                        not_complete = true;
                    }
                  })
                  var $this = this;
                  var button_text = $("button[type=submit]",$this).html();
                  $("button[type=submit]",$this).attr("disabled","disabled");
                  $("button[type=submit]",$this).html(loading_text);                  
                  if (not_complete==false) {

                  }
                  $.post("/api/update-order/",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).removeAttr("disabled");
                    $("button[type=submit]",$this).html(button_text);
                    var result = JSON.parse(response);
                    if (result.status) {
                      toastr.success(result.message);
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })                  
                })

                $("select[name=status]").change(function(){
                  var id = $("#orderForm input[name=uuid]").val();
                  var status = $(this).val();
                  $.post("/api/set-order-status",{"token":localStorage.getItem("token"),"order_id":id,"status":status},function(response){
                    var result = JSON.parse(response);
                      if (result.status) {
                          toastr.success("Order set as "+status)
                      } else {
                          toastr.error(result.message, 'Oops!')
                      }
                  })
                })

                $(".approveBtn").click(function(){
                  var id = $("#orderForm input[name=uuid]").val();
                  var status = "In Process";                  
                  $(this).attr("disabled","disabled");
                  $(this).html(loading_text);
                  $.post("/api/set-order-status",{"token":localStorage.getItem("token"),"order_id":id,"status":status},function(response){
                    var result = JSON.parse(response);
                      if (result.status) {

                          toastr.success("Order set as "+status)
                        setTimeout(function(){
                          location.reload();
                        },2000)
                      } else {
                          toastr.error(result.message, 'Oops!')
                      }
                  })
                })

                $("#makePayment").click(function(){
                  $(this).remove();
                  $("#paymentCheckoutBox").removeClass("d-none");
                })

                <?php if ($data['order_details']['status']=="Awaiting Payment" && $_SESSION['role']=="client"): ?>

                    $("input[name=payment_method]").change(function(){
                      if ($(this).val()=="paypal") {
                        $("#razorpay-button-container").addClass("d-none");
                        $("#loading-div").addClass("d-none");
                        $("#paypal-button-container").removeClass("d-none");   
                        $("#paypal-button-container").html("");
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
                                              value: <?php echo number_format($data['order_details']['amount'],2,".",""); ?>, // Replace with the amount you want to charge
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
                                              toastr.success(result.message, 'Good Job!');
                                              setTimeout(function(){
                                                location.reload();
                                              },2000);
                                            } else {
                                              toastr.error(result.message, 'Oops!')
                                            }
                                      })
                                  });
                              }
                          }).render('#paypal-button-container');  

                      } else {
                        $("#loading-div").removeClass("d-none");
                        $("#paypal-button-container").addClass("d-none");        
                        $.post("/api/generate-razorpay-id",{"amount":<?php echo $data['order_details']['amount']*100; ?>,"token":localStorage.getItem("token"),"data":$("#orderForm").serialize()},function(response){
                          var result = JSON.parse(response);
                          if (result.status) {            
                            $("#paypal-button-container").addClass("d-none");
                            $("#loading-div").addClass("d-none");
                            $("#razorpay-button-container").removeClass("d-none")

                            var options = {
                                "key": "<?php echo RAZORPAY_API_KEY; ?>",
                                "amount": <?php echo $data['order_details']['amount']*100; ?>,
                                "currency": "USD",
                                "name": "Creative Brains for Design and Marketing",
                                "description": "<?php echo strtoupper($data['order_details']['service']); ?>",
                                "order_id": result.razorpay_id, // Pass the `id` obtained in the previous step
                                "handler": function (response){
                                  $.post('/api/order-payment',{"order_id":"<?php echo $data['order_details']['uuid']; ?>","razorpay_signature":response.razorpay_signature,"razorpay_order_id":response.razorpay_order_id,"paymentID":response.razorpay_payment_id,"token":localStorage.getItem("token"),"paid_with":$("input[name=payment_method]:checked").val()},function(response){
                                            var result = JSON.parse(response);
                                            if (result.status) {
                                              toastr.success(result.message, 'Good Job!');
                                              setTimeout(function(){
                                                location.reload();
                                              },2000);
                                            } else {
                                              toastr.error(result.message, 'Oops!')
                                            }
                                      })                                                                    
                                }
                            };

                            var rzp1 = new Razorpay(options);
                            document.getElementById('razorpayBtn').onclick = function(e){
                                rzp1.open();
                                e.preventDefault();
                            }
                          } else {
                            toastr.error(result.message, 'Oops!')
                            $("#card-payment-method").prop("checked",false);
                            $("#loading-div").addClass("d-none");
                          }
                        })
                      }
                    })                                      

                <?php endif; ?>

                $("#docLinkForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",$this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/update-doc-link",{"token":localStorage.getItem("token"),"order_id":"<?php echo $data['order_details']['uuid']; ?>","doc_link":$("#docLinkForm input[name=doc_link]").val()},function(response){
                    $("button[type=submit]",$this).removeAttr("disabled");                
                    $("button[type=submit]",$this).html(button_text);
                    var result = JSON.parse(response);
                    if (result.status) {
                      toastr.success(result.message);
                      setTimeout(function(){
                        location.reload()
                      },2000)
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                })
              })          
            </script>
      <?php include('View/includes/foot.php'); ?>            