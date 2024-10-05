<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">
  #product-table_wrapper {
    margin-top: -20px;
  }
</style>
<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabel">Keyword Research</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Time to sprinkle some keyword magic on your online world! Our Keyword Research isn't just about finding words; it's like having a digital Sherlock Holmes sniffing out the perfect phrases that speak to your peeps. Imagine your content doing a little victory dance on search engines – that's the vibe we're going for. Let's give your online game a personalised touch and make those keywords work wonders for you!</p>
            </div>
        </div>
    </div>
</div>
<!-- Layout container -->
<div class="layout-page">
    <!-- main nav -->
    <?php include('View/includes/menu-nav.php'); ?>

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <form id="orderForm" method="POST">
                <input type="hidden" name="service" value="keyword research">
                <input type="hidden" name="package_value" value="">
                <div class="row mb-5">
                    <div class="col-md-6">
                      <!-- Order Details -->
                      <div class="card">
                        <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Order Details - Keyword Research<button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#infoModal"><i class='bx bxs-info-circle' id="info-icon"></i></button></h5>
                        <div class="card-body">
                            <div class="clear20"></div>
                            <h6 class="mb-3">Select Package</h6>
                            <div class="row">
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                    <input name="package" class="packages form-check-input" data-price="99" data-name="$99/Report" type="radio" value="0" id="customRadioTemp1"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">Report</span>
                                      <span><b class="text-primary">$99</b></span>
                                    </span>
                                    <br>
                                    <span class="custom-option-body">
                                      <small>price per report</small>
                                    </span>
                                  </label>
                                </div>
                              </div>
                            </div>
                            <div class="form-group mt-3">
                              <label class="form-label">Website <span class="text-danger">*</span></label>
                              <input type="text" name="website" class="form-control" required/>
                            </div>
                            <div class="form-group mt-3">
                              <label class="form-label">No. of pages</label>
                              <input type="number" name="no_pages" value="0" class="form-control"/>
                            </div>
                            <div class="form-group mt-3">
                              <label class="form-label">Share Spreadsheet/Doc link</label>
                              <input type="text" name="doc_link" class="form-control"/>
                            </div>
                            <div class="form-group mt-3">
                              <label class="form-label">Order Comments <span class="text-danger">*</span></label>
                              <textarea name="comments" class="form-control" required></textarea>
                            </div>
                            
                        </div>
                      </div>
                      <!--/ Order Details -->
                    </div>

                    <div class="col-md-6">
                      <!-- Checkout -->
                      <?php include('View/order/checkout-box.php'); ?>
                      <!--/ Checkout -->
                    </div>
                </div>
              </form>
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">
              $(document).ready(function(){
                /* info action */
                if (localStorage.getItem("info-keyword")!=null) {
                  $("#info-icon").removeClass("bx-tada");
                  $("#info-icon").removeClass("text-warning");
                } else {
                  $("#info-icon").addClass("bx-tada");
                  $("#info-icon").addClass("text-warning");
                }
                $('#infoModal').on('shown.bs.modal', function (e) {
                    if (localStorage.getItem("info-keyword")==null) {
                      localStorage.setItem("info-keyword","1");
                      $("#info-icon").removeClass("bx-tada");
                      $("#info-icon").removeClass("text-warning");
                    }
                });
                /* /info action */

                $(".packages").change(function(){
                  $(".packages").val(0);
                  $("input[name=package_value]").val($(this).data("name"));
                  $(this).val(1);
                  
                })
              })
            </script>
            <?php include('View/order/checkout-script.php'); ?>    
      <?php include('View/includes/foot.php'); ?>            