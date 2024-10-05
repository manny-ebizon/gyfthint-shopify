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
  .datatable,#datatable_filter,#datatable_length {
    table-layout: fixed;
  }
  .table-responsive .table {
    min-width: 1000px !important;
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
              <div class="nav-align-top">
                <div class="tab-content pd0">
                  <div class="tab-pane fade show active" id="navs-pills-blogger-outreach-service" role="tabpanel">
                    <div class="row">
                      <div id="table-list-container" class="col-md-12">
                        <!-- Striped Rows -->
                        <div class="card">
                          <h5 class="card-header pb-3"><i class='bx bxs-check-circle me-1'></i> Completed Orders                        
                          </h5>                      
                          <div class="table-responsive text-nowrap">
                            <table id="blogger-datatable" class="table datatable table-striped">
                              <thead>
                                <tr>
                                  <th>Order ID</th>
                                  <?php if ($_SESSION['role']!="client"): ?>
                                    <th>Client</th>
                                  <?php endif ?>
                                  <th>Service Type</th>
                                  <th>Order Date</th>
                                  <th>Amount</th>
                                  <th>Status</th>
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
                </div>
              </div>              
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">              
              $(document).ready(function(){                
                function fetchData(service) {
                    $("#blogger-datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    $.post("/api/fetch-completed-orders/",{"service":service,"token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);                        
                        $("#blogger-datatable tbody").html("");
                        if (result.status) {
                            $('#blogger-datatable').DataTable().destroy();
                            $('#blogger-datatable').DataTable({
                                data: result.datatable,
                                stateSave: true,
                                columnDefs: []
                            });
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }
                fetchData("all");

                // $("#bloggerOutreachBtn").click(function(){
                //   fetchData("blogger outreach");
                // });

                // $("#otherServicesBtn").click(function(){
                //   fetchData("other services");
                // });                
              })          
            </script>            
      <?php include('View/includes/foot.php'); ?>            