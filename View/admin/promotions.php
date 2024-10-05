<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">

.main-kpis {
  display: flex;
  
  width: 100%;
  margin-bottom: 20px;
}

.kpi {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  text-align: center;
  width: 200px;
}

.kpi .icon {
  font-size: 24px;
  margin-bottom: 10px;
}

.kpi .details h2 {
  margin: 10px 0;
  font-size: 18px;
}

.kpi .details p {
  margin: 0;
  font-size: 16px;
}

.kpi .increase {
  color: green;
}

.top-pages {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  width: 100%;
  max-width: 800px;
  margin-bottom: 20px;
}

.top-pages table {
  width: 100%;
  border-collapse: collapse;
}

.top-pages table th,
.top-pages table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

.rates-chart {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  width: 100%;
  max-width: 800px;
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
              <div class="main-kpis">
                        <div class="row">
                          <div class="col-4 col-lg-3">
                            <div class="kpi">
                              <div class="icon">👥</div>
                              <div class="details">
                                <h2>Returning Users</h2>
                                <p>2,653 <span class="increase">+2.74%</span></p>
                              </div>
                            </div>
                          </div>
                          <div class="col-4 col-lg-3">
                              <div class="kpi">
                                <div class="icon">📉</div>
                                <div class="details">
                                  <h2>Bounce Rate</h2>
                                  <p>23.64% <span class="increase">+0.98%</span></p>
                                </div>
                              </div>
                          </div>
                          <div class="col-4 col-lg-3">
                              <div class="kpi">
                                <div class="icon">✅</div>
                                <div class="details">
                                  <h2>Goal Conversion Rate</h2>
                                  <p>78% <span class="increase">+3.89%</span></p>
                                </div>
                              </div>
                          </div>
                          <div class="col-4 col-lg-3">
                            <div class="kpi">
                              <div class="icon">⏱️</div>
                              <div class="details">
                                <h2>Session Duration</h2>
                                <p>00:25:30 <span class="increase">+1.45%</span></p>
                              </div>
                            </div>
                          </div>
                        </div>          
                    </div>
              <div class="row mb-5">
                  <div id="table-list-container" class="col-md-12">
                    <!-- Striped Rows -->
                    <div class="card">                                    
                      <div class="table-responsive text-nowrap">
                        <?php $options = ["!", "", ""];
                               ?>
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th></th>
                              <th>Product Name</th>
                              <th>Stocks</th>
                              <th># of Hints</th>
                              <th>Price</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">
                            <?php foreach ($data['hints'] as $key => $value): ?>
                              <tr>
                                <td><img src="<?php echo $value['product_images_links']; ?>" style="width:70px;height:70px;"></td>
                                <td><?php echo $value['product_name']."<br><small>".ucfirst($value['variant_title'])."</small>"; ?></td></td>
                                <td><?php echo rand(1, 100); ?></td>
                                <td><?php echo rand(1, 100); ?></td>
                                <td><?php echo $value['product_price']; ?></td>
                                <td><button class="btn btn-primary btn-sm">PROMOTE</button></td>
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