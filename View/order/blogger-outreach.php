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
                <h5 class="modal-title" id="infoModalLabel">Blogger Outreach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Hey there! We're all about snagging those natural, spot-on links that scream authenticity and quality. With a team of native UK and US writers, we ensure that the content aligns seamlessly with your brand voice and resonates with your target audience. And here's the kicker: every link we snag? Pure dedication and elbow grease, no shortcuts. We're talking 100% genuine, hands-on outreach. It's not just a strategy; it's a commitment to making your online presence grow organically, just like a cool plant but in the digital world. Let's make your brand shine!</p>
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
                <input type="hidden" name="service" value="blogger outreach">                
                <div class="row mb-5">
                    <div class="col-md-6">
                      <!-- Order Details -->
                      <div class="card">
                        <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Order Details - Blogger Outreach<button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#infoModal"><i class='bx bxs-info-circle' id="info-icon"></i></button>
                        </h5>
                        <div class="card-body">
                            <div class="clear20"></div>
                            <h6 class="mb-3">Place Your Order</h6>
                            <label class="form-label">DR 30+ Websites</label>
                            <div class="input-group">
                              <input type="number" class="form-control packages" value="0" step="1" name="package[dr_30]" data-price="120" data-name="DR 30+ Websites"/>
                              <span class="input-group-text">$120 each</span>
                            </div>
                            <div class="clear10"></div>
                            <label class="form-label">DR 40+ Websites</label>
                            <div class="input-group">
                              <input type="number" class="form-control packages" value="0" step="1" name="package[dr_40]" data-price="160" data-name="DR 40+ Websites"/>
                              <span class="input-group-text">$160 each</span>
                            </div>
                            <div class="clear10"></div>
                            <label class="form-label">DR 50+ Websites</label>
                            <div class="input-group">
                              <input type="number" class="form-control packages" value="0" step="1" name="package[dr_50]" data-price="250" data-name="DR 50+ Websites"/>
                              <span class="input-group-text">$250 each</span>
                            </div>
                            <div class="clear10"></div>
                            <label class="form-label">DR 60+ Websites</label>
                            <div class="input-group">
                              <input type="number" class="form-control packages" value="0" step="1" name="package[dr_60]" data-price="300" data-name="DR 60+ Websites"/>
                              <span class="input-group-text">$300 each</span>
                            </div>                            
                            <div class="form-check form-switch mt-4">
                              <input class="form-check-input" name="pre_approved_websites" type="checkbox" id="flexSwitchCheckChecked" value="1" />
                              <label class="form-check-label pointer" for="flexSwitchCheckChecked"
                                >Do you want to pre-approve websites</label
                              >
                            </div>
                            <div class="form-check form-switch mt-2">
                              <input class="form-check-input" name="anchor_landing_pages" type="checkbox" id="flexSwitchCheckChecked2" value="1" />
                              <label class="form-check-label pointer" for="flexSwitchCheckChecked2"
                                >Do you want to submit your own set of anchor and landing pages?</label
                              >
                            </div>
                            <div class="form-group mt-3">
                              <label class="form-label doc-required">Share Spreadsheet/Doc Link/Target URL</label>
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
              $(function(){                
                if (localStorage.getItem("info-blogger-outreach")!=null) {
                  $("#info-icon").removeClass("bx-tada");
                  $("#info-icon").removeClass("text-warning");
                } else {
                  $("#info-icon").addClass("bx-tada");
                  $("#info-icon").addClass("text-warning");
                }
                $('#infoModal').on('shown.bs.modal', function (e) {
                    if (localStorage.getItem("info-blogger-outreach")==null) {
                      localStorage.setItem("info-blogger-outreach","1");
                      $("#info-icon").removeClass("bx-tada");
                      $("#info-icon").removeClass("text-warning");
                    }
                });

                $("input[name=anchor_landing_pages]").change(function(){
                  if ($(this).is(':checked')) {
                      $(".doc-required").append(' <span class="text-danger">*</span>')
                      $("input[name=doc_link]").prop("required",true);
                  } else {
                      $(".doc-required span").remove();
                      $("input[name=doc_link]").removeAttr("required");
                  }
                }) 
              })
            </script>
            <?php include('View/order/checkout-script.php'); ?> 
      <?php include('View/includes/foot.php'); ?>            