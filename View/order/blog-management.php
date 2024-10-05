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
                <h5 class="modal-title" id="infoModalLabel">Blog Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Transform your online presence with our Blog Management service, where we bring the art of storytelling to life. We're not just managing blogs; we're crafting a personalized journey for your brand. From crafting captivating content to strategic scheduling, we infuse your blog with personality and purpose. Why choose us? Because we don't just write; we create a narrative that resonates. Let's make your blog a dynamic space that sparks conversations and keeps your audience coming back for more. Blogging with a personal touch, where every word adds a stroke to your brand's canvas. Ready to tell your story in style? Let's make it happen!</p>
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
                <input type="hidden" name="service" value="blog management">
                <input type="hidden" name="package_value" value="">
                <div class="row mb-5">
                    <div class="col-md-6">
                      <!-- Order Details -->
                      <div class="card">
                        <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Order Details - Blog Management<button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#infoModal"><i class='bx bxs-info-circle' id="info-icon"></i></button></h5>
                        <div class="card-body">
                            <div class="clear20"></div>
                            <h6 class="mb-3">Select Package</h6>
                            <div class="row">
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp1">
                                    <input name="package" class="packages form-check-input" type="radio" value="0" data-price="1000" data-name="Basic Package" id="customRadioTemp1"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">Basic Package</span>
                                      <span><b class="text-primary">$1000</b></span>
                                    </span>
                                    <br>
                                    <span class="custom-option-body">
                                      <small>10 Articles Per Month</small>
                                    </span>
                                  </label>
                                </div>
                              </div>
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp2">
                                    <input name="package" class="packages form-check-input" type="radio" value="0" data-price="1350" data-name="Standard Package" id="customRadioTemp2"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">Standard Package</span>
                                      <span><b class="text-primary">$1350</b></span>
                                    </span>
                                    <br>
                                    <span class="custom-option-body">
                                      <small>15 Articles Per Month</small>
                                    </span>
                                  </label>
                                </div>
                              </div>
                              <div class="col-12 mb-2">
                                <div class="form-check custom-option custom-option-basic form-control">
                                  <label class="form-check-label custom-option-content" for="customRadioTemp3">
                                    <input name="package" class="packages form-check-input" type="radio" value="0" data-price="2000" data-name="Premium Package" id="customRadioTemp3"/>
                                    <span class="custom-option-header">
                                      <span class="h6 mb-0">Premium Package</span>
                                      <span><b class="text-primary">$2000</b></span>
                                    </span>
                                    <br>
                                    <span class="custom-option-body">
                                      <small>25 Articles Per Month</small>
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
                if (localStorage.getItem("info-blog-management")!=null) {
                  $("#info-icon").removeClass("bx-tada");
                  $("#info-icon").removeClass("text-warning");
                } else {
                  $("#info-icon").addClass("bx-tada");
                  $("#info-icon").addClass("text-warning");
                }
                $('#infoModal').on('shown.bs.modal', function (e) {
                    if (localStorage.getItem("info-blog-management")==null) {
                      localStorage.setItem("info-blog-management","1");
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