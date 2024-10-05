<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">
  #product-table_wrapper {
    margin-top: -20px;
  }
  .table-responsive {
    position: relative;
    overflow-x: auto;
  }
  #datatable,#datatable_filter,#datatable_length {
    table-layout: fixed;
  }
  .table-responsive .table {
    min-width: 800px !important;
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
              <div class="row mb-5 d-none">
                  <div id="table-list-container" class="col-md-12">
                    <!-- Striped Rows -->
                    <div class="card">
                      <h5 class="card-header pb-3"><i class='bx bxs-receipt me-1'></i> View Invoices
                      </h5>                      
                      <div class="table-responsive text-nowrap">
                        <style type="text/css">
                          
                        </style>
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th></th>
                              <th>Order Number</th>
                              <?php if ($_SESSION['role']=="admin"): ?>
                                <th>Client</th>
                              <?php endif ?>
                              <th>Order Date</th>
                              <th>Service</th>
                              <th>Payment Mode</th>
                              <th>Amount</th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">                            
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
                function fetchData() {
                    $("#datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    $.post("/api/fetch-invoices/",{"token":localStorage.getItem("token")},function(data){
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
                fetchData();                
                
              })          
            </script>            
      <?php include('View/includes/foot.php'); ?>            