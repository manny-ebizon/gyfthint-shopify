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
              <div class="row mb-5 d-none">
                  <div id="table-list-container" class="col-md-6">
                    <!-- Striped Rows -->
                    <div class="card">
                      <h5 class="card-header pb-3"><?php echo "<i class='bx ".$data['roleaccess']['module_icon']."'></i> " . $data['roleaccess']['module_name']?>
                      <?php echo '<input type="hidden" name="box-can-add" id="box-can-add" value="'.$data['roleaccess']['can_add'].'"/>'; ?>               
                      <div class="card-body">
                          <form id="boxForm" method="POST">
                            <input type="hidden" name="uuid">
                            <div class="row">
                              <div class="mb-3">
                              <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                              <input
                                type="text"
                                class="form-control"
                                id="company_name"
                                name="company_name"
                                placeholder="Enter Company Name"
                                autofocus
                                required
                              />
                            </div>
                            <div class="mb-3">
                              <label for="company_logo" class="form-label">Company Logo</label>
                              <input
                                type="file"
                                class="form-control"
                                id="company_logo"
                                name="company_logo"
                                placeholder="Enter Company Logo"
                                autofocus
                              />
                            </div>
                            <div class="mb-3">
                              <label for="company_domain" class="form-label" id="company-domain">Company Domain 
                              </label>
                              <input
                                type="text"
                                class="form-control"
                                id="company_domain"
                                name="company_domain"
                                placeholder="Enter Company Domain"
                                autofocus
                              />
                            </div>
                              <?php 
                                echo '<input
                                  type="hidden"
                                  class="form-control"
                                  id="site_admin_id"
                                  name="site_admin_id"
                                  placeholder=""
                                  autofocus
                                  value='.$_SESSION['login_id'].'
                                  />' 
                                ?>
                            <div class="mt-2">
                              <?php echo $data['roleaccess']['can_update']==1?'<button type="submit" class="btn btn-primary me-2">Save changes</button>':'';?> 
                              <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                            </div>
                          </form>
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
                var id = <?php echo $_SESSION['site_id']; ?>   
                function fetchData() {
                  $.post("/api/fetch-data/",{"id":id,"token":localStorage.getItem("token"),"t":"sites", "feature":"site_settings"},function(response){
                    var result = JSON.parse(response);
                    console.log(result);
                    if (result.status) {
                      $("#boxForm input[name=company_name]").val(result.data.company_name);
                      $("#boxForm input[name=company_logo]").val(result.data.company_logo);
                      $("#boxForm input[name=company_domain]").val(result.data.company_domain);
                      $('#site_admin_id').val(result.data.site_admin_id);
                      $("#boxForm input[name=uuid]").val(result.data.uuid);
                      var is_verified_html = result.data.is_verified==0?"Company Domain <i class='bx bx-shield-x for-verification'></i> for verification":"Company Domain <i class='bx bx-check-shield verification-status'></i> verified";
                      $("#company-domain").html(is_verified_html);
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                }
                fetchData();

                $("#boxForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"sites","data":$(this).serialize(),"unique_index":"email"},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                })       
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>