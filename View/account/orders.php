<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<!-- Layout container -->
<div class="layout-page">
    <!-- main nav -->
    <?php include('View/includes/menu-nav.php'); ?>
          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row mb-5">
                  <div id="table-list-container" class="col-md-12">
                    <!-- Striped Rows -->
                    <div class="card">
                      <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Order</th>
                              <th>Date</th>
                              <th>Customer</th>
                              <th>Total</th>
                              <th>Payment status</th>
                              <th>Fulfillment status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>#1013</td>
                              <td>Today at 1:45 a.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$180.00</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-warning me-1">Unfulfilled</span></td>                              
                            </tr>
                            <tr>
                              <td>#1012</td>
                              <td>Friday at 12:36 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$14.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-warning me-1">Unfulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1011</td>
                              <td>Friday at 12:14 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$0.00</td>
                              <td><span class="badge bg-label-secondary me-1">Refunded</span></td>
                              <td><span class="badge bg-label-warning me-1">Unfulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1010</td>
                              <td>Thursday at 05:16 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$74.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1009</td>
                              <td>Thursday at 05:15 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$59.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1008</td>
                              <td>Thursday at 05:11 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$51.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1007</td>
                              <td>Thursday at 05:10 p.m.</td>
                              <td>vitor customer cancelled closed</td>
                              <td>$44.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1006</td>
                              <td>Nov. 29 at 11:01 p.m.</td>
                              <td>Vitor Reward</td>
                              <td>$38.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1005</td>
                              <td>Nov. 29 at 10:46 p.m.</td>
                              <td>Reward Discount</td>
                              <td>$38.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</td>
                              
                            </tr>
                            <tr>
                              <td>#1004</td>
                              <td>Nov. 29 at 10:41 p.m.</td>
                              <td>Reward Discount</td>
                              <td>$14.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-warning me-1">Unfulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1003</td>
                              <td>Nov. 26 at 4:30 p.m.</td>
                              <td>Vitor Guest</td>
                              <td>$44.90</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1002</td>
                              <td>Aug. 18 at 1:28 a.m.</td>
                              <td>Customer Test tester</td>
                              <td>$25.00</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-warning me-1">Unfulfilled</span></td>
                              
                            </tr>
                            <tr>
                              <td>#1001</td>
                              <td>Aug. 18 at 1:05 a.m.</td>
                              <td>Customer Test tester</td>
                              <td>$30.00</td>
                              <td><span class="badge bg-label-primary me-1">Paid</span></td>
                              <td><span class="badge bg-label-secondary me-1">Fulfilled</span></td>
                              
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!--/ Striped Rows -->
                  </div>
              </div>
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">              
              $(document).ready(function(){            
                $("#datatable").DataTable();
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>