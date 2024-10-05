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
              <div class="row mb-5">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                          <!--orders --->
                          <div id="order-details-container" class="col-md-12">
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
                                              echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; x".$value."<br>";
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
                                  <?php
                                    if($_SESSION['role'] == 'client'){
                                      echo '<button type="button" class="btn btn-primary btn-sm pull-left" id="btnCreateTicket">Create Ticket <i class="bx bxs-plus-circle"></i></button>';
                                    }
                                  ?>                    
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <!-- ticket list -->
                          <div id="table-list-container" class="col-md-12">
                            <div class="card">
                              <h5 class="card-header pb-3">Tickets</h5>             
                              <div class="table-responsive text-nowrap">
                                <table id="datatable" class="table table-striped">
                                  <thead>
                                    <tr>
                                      <th>Ticket ID</th>
                                      <th>Ticket Name</th>
                                      <th>Status</th>
                                      <th>Updated Date</th>
                                    </tr>
                                  </thead>
                                  <tbody class="table-border-bottom-0">                            
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>
                          <!-- end Ticket List -->
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                  <!-- Ticket Details-->
                  <div id="viewBoxContainer" class="col-md-12 col-lg-12">
                  <div class="card d-none" id="viewBox">
                          <h5 class="card-header pb-3">Create Ticket <a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>             
                            <div class="card-body">
                              <form id="boxForm" method="POST">
                                <input type="hidden" name="uuid" />
                                <div class="row">
                                  <div class="mb-3">
                                    <label for="header" class="form-label">Ticket Name <span class="text-danger">*</span></label>
                                    <input
                                      type="text"
                                      class="form-control"
                                      id="header"
                                      name="header"
                                      placeholder="Enter Ticket Name"
                                      autofocus
                                      required
                                    />
                                  </div>
                                  <div class="mb-3">
                                    <label for="body" class="form-label">Ticket Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="body" name="body" placeholder="Enter Ticket Description" rows="4">
                                    </textarea>
                                  </div>
                                  <div class="mb-3">
                                    <label for="attachment" class="form-label">Attachment</label>
                                    <input
                                      type="file"
                                      class="form-control"
                                      id="ticket_attachment"
                                      name="ticket_attachment"
                                      placeholder="Attach File"
                                      autofocus
                                      required
                                      disabled
                                    />
                                  </div>
                                </div>
                              <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                <button type="reset" id="backButton" class="btn btn-outline-secondary">Cancel</button>
                              </div>
                              </form>
                            </div>
                        </div>
                        <div class="card d-none" id="ticketingBox">
                          <h5 class="card-header pb-3"><span class="ticket-header"> </span><br><span class="ticket-id"></span><a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>     
                            <hr />
                            <div class="card-body">
                              <!-- ticket-thread History -->
                              <div class="ticket-thread">
                                <div class="ticket-thread-history">
                                  <ul class="m-b-0">
                                      <li class="clearfix">
                                          <div class="message-data text-right">
                                            <span class="message-data-time ticket-date-created"></span>  
                                            <span class="message-data-user ticket-date-user"></span>
                                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">
                                          </div>
                                          <div class="message other-message float-right ticket-body-message"> </div>
                                      </li>
                                  </ul>
                                  <ul class="m-b-0" id="threads">
                                  </ul>
                                </div>
                                <form id="replyForm" method="POST">
                                  <div class="ticket-thread-message clearfix">
                                    <input type="hidden" class="form-control" id="is_internal" name="is_internal" value="0"/>
                                    <input type="hidden" class="form-control" id="ticket_id" name="ticket_id"/>
                                    <input type="hidden" class="form-control" id="uuid" name="uuid"/>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="message-body" name="body" placeholder="Type a message" aria-label="Reply" aria-describedby="basic-addon2">
                                          <button class="btn btn-outline-secondary" type="submit"><i class='bx bx-paper-plane' ></i></button>
                                      </div>
                                      <a class="pointer"><i class='bx bx-paperclip'></i></a>
                                  </div>
                                </form>
                              </div>
                            </div>
                            </div>
                        </div>
                  </div>
                  <!-- End of Ticket Details-->
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
                var order_id = "<?php echo $data['order_details']['uuid']; ?>";    
                function fetchData() {
                    $("#datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    $.post("/api/fetch-table/",{"order_id":order_id, "t":"tickets","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        $("#datatable tbody").html("");
                        if (result.status) {
                            $('#datatable').DataTable().destroy();
                            $('#datatable').DataTable({
                                data: result.datatable,
                                stateSave: true,
                                columnDefs: []
                            });
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }

                function fetchTicketHistory() {
                    //$("#datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    var login_id = <?php echo $_SESSION['login_id']; ?>;
                    $.post("/api/fetch-ticket-thread",{"ticket_id":$('#ticket_id').val(), "t":"ticket_threads","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        if (result.status) {
                            var threadsData = result.data;
                            $('#threads').html("");
                            var threadHtml = '';
                            $.each(threadsData, function(index, value){
                              if(login_id == value['user_id']){
                                threadHtml += '<li class="clearfix">';
                                  threadHtml += '<div class="message-data text-right">';
                                    threadHtml += '<span class="message-data-time ticket-date-created">'+value['created_at']+'</span>';
                                    threadHtml += '<span class="message-data-user ticket-date-user">'+value['first_name']+'</span>';
                                      threadHtml +='<img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">';
                                  threadHtml +='</div>';
                                    threadHtml += '<div class="message other-message float-right ticket-body-message"> '+value['body']+' </div>';
                                threadHtml +='</li>';
                              }else{
                                threadHtml += '<li class="clearfix">';
                                  threadHtml += '<div class="message-data">';
                                      threadHtml +='<img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">';0
                                    threadHtml += '<span class="message-data-user ticket-date-user">'+value['first_name']+'</span>';
                                    threadHtml += '<span class="message-data-time ticket-date-created">'+value['created_at']+'</span>';
                                  threadHtml +='</div>';
                                    threadHtml += '<div class="message my-message ticket-body-message"> '+value['body']+' </div>';
                                threadHtml +='</li>';
                              }
                            });
                            $('#threads').html(threadHtml);
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }

                fetchData();

                $("#boxForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"order_id":"<?php echo $data['order_details']['uuid']; ?>", "token":localStorage.getItem("token"),"t":"tickets","data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                })

                $("#replyForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"ticket_threads","data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')             
                        $("button[type=submit]",$this).html('<i class="bx bx-paper-plane" ></i>');
                        $('#message-body').val("");
                        fetchTicketHistory();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                })

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

                if (localStorage.getItem("view-box")!=null) {
                  if (localStorage.getItem("view-box")==1 && $('#box-can-add').val()==1) {
                    $(".deleteBtn").addClass("d-none");                  
                    $("#boxForm button[type=submit]").text("Add");
                    $("#viewBox .card-header > b").text("Add New Site");
                    $("#viewBox").removeClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").addClass("col-lg-12");  
                    } else {
                      $("#viewBox").addClass("d-none");
                    }
                  } else {
                    $("#viewBox").addClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").removeClass("col-lg-6");  
                    } else {
                      $("#table-list-container").show();  
                      $("#viewBoxContainer").removeClass("col-md-12");
                      $("#viewBoxContainer").addClass("col-md-6");
                    }
                  }
                }

                $(".closeBtn").click(function(e){
                  e.preventDefault();
                  $("#viewBox").addClass("d-none");
                  $("#ticketingBox").addClass("d-none");
                  localStorage.setItem("view-box",0);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").removeClass("col-lg-6");  
                  } else {
                    $("#table-list-container").show();  
                    $("#viewBoxContainer").removeClass("col-md-12");
                    $("#viewBoxContainer").addClass("col-md-6");
                  }
                  $("#btnCreateTicket").removeClass("d-none");
                  $("#makePayment").removeClass("d-none");
                })

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update Site Details");
                  $("#ticketingBox").removeClass("d-none");
                  $("#viewBox").addClass("d-none");
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-12");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"tickets"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $(".ticket-header").html(result.data.header);
                      var ticketIdHtml = 'Ticket No. : # '+ result.data.id.padStart(5, "0");
                      $(".ticket-id").html(ticketIdHtml);
                      $(".ticket-body-message").html(result.data.body);
                      $(".ticket-date-created").html(result.data.created_at);
                      $(".ticket-date-user").html(result.data.first_name);
                      $('#ticket_id').val(result.data.uuid);
                      fetchTicketHistory();
                      fetchData();
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                })

                $("#btnCreateTicket").click(function(){
                  $("#boxForm :input").val('');
                  $(".deleteBtn").addClass("d-none");                  
                  $("#boxForm button[type=submit]").text("Submit");
                  $("#viewBox .card-header > b").text("Create New Ticket");
                  $("#viewBox").removeClass("d-none");
                  $("#ticketingBox").addClass("d-none");
                  $("#btnCreateTicket").addClass("d-none");
                  $("#makePayment").addClass("d-none");
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-12");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  //fetchData();
                });

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