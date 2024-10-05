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
              <div class="row mb-5">
                  <div id="table-list-container" class="col-md-12">
                    <!-- Striped Rows -->
                    <div class="card">
                      <h5 class="card-header pb-3"><i class='bx bxs-package me-1'></i> Create Order
                      </h5>   
                      <div class="col-md-12">
                        <section>
                          <div class="row product-list" id="product-list">
                          </div>
                        </section>
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
                    $.post("/api/fetch-table/",{"t":"services","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        $("#product-list").html("");
                        var html = '';
                        var imagesColorPallete = ['FFB6C1','6495ED','FF7F50','00BFFF','00BFFF','00BFFF','00CED1','9400D3','FFD700','FFD700','FFD700','3CB371','FFB6C1','C71585','87CEEB','FFB6C1'];
                        if (result.status) {
                          $.each(result.datatable, function(i, item){
                            html +='<div class="col-md-3">';
                                html+= '<section class="panel-prod">';    
                                  html+= '<div class="pro-img-box">';
                                      html+='<img src="https://www.bootdey.com/image/250x220/'+imagesColorPallete[i]+'/000000" alt="" />';
                                        html+='<a href="/order/add/'+item.uuid+'" class="adtocart">';
                                            html+='<i class="bx bx-shopping-bag"></i>';
                                        html+='</a>';
                                  html+='</div>';
                                  html+='<div class="panel-prod-body text-center">';
                                      html+='<h4>';
                                        html+='<a href="/order/add/'+item.uuid+'" class="pro-title">';
                                          html+=item.service_name;
                                        html+='</a>';
                                      html+='</h4>';
                                      html+='<p class="price">'+item.service_price+'</p>';
                                        html+='<a href="/order/add/'+item.uuid+'" class="btn btn-primary me-2 btn-sm">Order Now</a>';
                                  html+='</div>';
                                html+='</section>';
                            html+=' </div>';
                          });
                        $("#product-list").html(html);
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }
                fetchData();             
                
              })          
            </script>            
      <?php include('View/includes/foot.php'); ?>            