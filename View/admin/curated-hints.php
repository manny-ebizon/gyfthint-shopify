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
                      <h5 class="card-header pb-3"><i class='bx bxs-collection'></i> Curated Hints</h5>               
                      <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th></th>
                              <th>Product Name</th>
                              <th>Qty</th>
                              <th>Price</th>
                              <th>Source</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">
                            <?php foreach ($data['hints'] as $key => $value): ?>
                              <tr>
                                <td><img src="<?php echo $value['product_images_links']; ?>" style="width:70px;height:70px;"></td>
                                <td><?php echo $value['product_name']."<br><small>".ucfirst($value['variant_title'])."</small>"; ?></td></td>
                                <td><?php echo $value['product_quantity']; ?></td>
                                <td><?php echo $value['product_price']; ?></td>
                                <td><?php echo ucwords(str_replace("_", " ", $value['source'])); ?></td>
                                <td><a href="https://<?php echo $value['product_url']; ?>?&q=<?php echo $value['product_quantity']; ?>" target="_blank"><button class="btn btn-primary btn-sm">View Product</button></a></td>
                              </tr>
                            <?php endforeach ?>
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