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
                <h5 class="modal-title" id="infoModalLabel">SEO Audit Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Ready for some website wisdom? Our SEO Audit Reports spill the tea on your online game. We break down the geeky stuff, giving you a roadmap to supercharge your site. It's like a digital X-ray, showing where your website's flexing and where it needs a little love. Wondering why you should roll with us? We're not just analysts; we're your website's personal hype squad, turning insights into action. Let's amp up your online presence and have your site strutting its stuff in search rankings. Ready to get your website glow-up? Let's do this!</p>
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
                <input type="hidden" name="service" value="seo audit reports">
                <input type="hidden" name="package_value" value="">
                <div class="row mb-5">
                    <div class="col-md-6">
                      <!-- Order Details -->
                      <div class="card">
                        <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Order Details - SEO Audit Reports<button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#infoModal"><i class='bx bxs-info-circle' id="info-icon"></i></button></h5>
                        <div class="card-body">
                            <div class="clear20"></div>
                            <h6 class="mb-3">Select Package</h6>
                            <div class="row">
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                    <input name="package" class="packages form-check-input" type="radio" value="0" id="customRadioTemp1" data-price="99" data-name="Basic Report"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">Basic Report</span>
                                      <span><b class="text-primary">$99</b></span>
                                    </span>
                                    <br>
                                    <span class="custom-option-body">
                                      <small>price per report</small>
                                    </span>
                                  </label>
                                </div>
                              </div>
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp2">
                                    <input name="package" class="packages form-check-input" type="radio" value="0" id="customRadioTemp2" data-price="149" data-name="PRO Report"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">PRO Report</span>
                                      <span><b class="text-primary">$149</b></span>
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
                if (localStorage.getItem("info-seo-audit")!=null) {
                  $("#info-icon").removeClass("bx-tada");
                  $("#info-icon").removeClass("text-warning");
                } else {
                  $("#info-icon").addClass("bx-tada");
                  $("#info-icon").addClass("text-warning");
                }
                $('#infoModal').on('shown.bs.modal', function (e) {
                    if (localStorage.getItem("info-seo-audit")==null) {
                      localStorage.setItem("info-seo-audit","1");
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