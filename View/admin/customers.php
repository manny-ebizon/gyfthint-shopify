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
                      <?php if (false): ?>
                         <h5 class="card-header pb-3"><i class='bx bxs-user-account'></i> Customers</h5>  
                      <?php endif ?>
                      <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Name</th>
                              <th>Email</th>                              
                              <th>Phone</th>
                              <th>Birth Date</th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">
                            <?php foreach ($data['customers'] as $key => $value): ?>
                              <tr>
                                <td><?php echo $value['first_name']." ".$value['last_name']; ?></td>
                                <td><?php echo $value['email']; ?></td>
                                <td><?php echo $value['phone']; ?></td>
                                <td><?php echo date("m/d/Y",strtotime($value['birthdate'])); ?></td>
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
                
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>