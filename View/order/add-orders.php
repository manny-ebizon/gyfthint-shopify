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
                <input type="hidden" name="service" id="service" value="blogger outreach">                
                <div class="row mb-5">
                    <div class="col-md-6">
                      <!-- Order Details -->
                      <div class="card">
                        <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Order Details <button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#infoModal"><i class='bx bxs-info-circle' id="info-icon"></i></button>
                        </h5>
                        <div class="card-body">
                            <div class="row">
                              <div class="col-md-6">
                                <section class="panel-prod">    
                                    <div class="pro-img-box">
                                        <img src="https://www.bootdey.com/image/250x220/FFB6C1/000000" alt="" />
                                    </div>
                                </section>
                              </div>
                              <div class="col-md-6">
                                <div class="panel-prod-body" id="service_details">  
                                </div>
                              </div>
                            </div>
                            <div class="clear20"></div>
                            <h6 class="mb-3">Place Your Order</h6>
                            <div class="form-group mt-3">
                              <label class="form-label">Order Comments <span class="text-danger">*</span></label>
                              <textarea name="comments" class="form-control" required></textarea>
                              <input type="hidden" class="form-control" value="" id="price" name="price" data-name="Price"/>
                              <input type="hidden" class="form-control" value="" id="service_type" name="service_type" data-name="ServiceType"/>
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