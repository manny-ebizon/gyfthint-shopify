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
                  <div id="table-list-container" class="col-md-12">
                    <!-- Striped Rows -->
                    <div class="card">
                      <h5 class="card-header pb-3"><?php echo "<i class='bx ".$data['roleaccess']['module_icon']."'></i> " . $data['roleaccess']['module_name']?>
                      <?php echo $data['roleaccess']['can_add']==1?'<button type="submit" class="btn btn-primary btn-sm ms-2 addBtn">Add <i class="bx bxs-plus-circle"></i></button></h5>':'';?>  
                      <?php echo '<input type="hidden" name="box-can-add" id="box-can-add" value="'.$data['roleaccess']['can_add'].'"/>'; ?>               
                       <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Service Name </th>
                              <th>Service Description</th>
                              <th>Service Price</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">                            
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!--/ Striped Rows -->
                  </div>
                  <div id="viewBoxContainer" class="col-md-6 col-lg-5">
                    <div id="viewBox" class="card mb-4 d-none">
                        <h5 class="card-header"><b>Add New Service</b><a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>
                        <div class="card-body">
                          <form id="boxForm" method="POST">
                            <input type="hidden" name="uuid">
                            <div class="row">
                              <div class="mb-3">
                              <label for="service_name" class="form-label">Name <span class="text-danger">*</span></label>
                              <input
                                type="text"
                                class="form-control"
                                id="service_name"
                                name="service_name"
                                placeholder="Enter Service Name"
                                autofocus
                                required
                              />
                            </div>
                            <div class="mb-3">
                              <label for="service_description" class="form-label">Description <span class="text-danger">*</span></label>
                              <input
                                type="text"
                                class="form-control"
                                id="service_description"
                                name="service_description"
                                placeholder="Enter Service Description"
                                autofocus
                                required
                              />
                            </div>
                            <div class="mb-3">
                              <label for="service_price" class="form-label">Price <span class="text-danger">*</span></label>
                              <input
                                type="number"
                                class="form-control"
                                id="service_price"
                                name="service_price"
                                placeholder="Enter Service Price"
                                autofocus
                                step="0.01"
                                required
                              />
                            </div>
                            
                            <div class="mb-3">
                              <label for="service_price" class="form-label">Service Type <span class="text-danger">*</span></label>
                              <select
                                class="form-control"
                                id="service_type"
                                name="service_type">
                                <option value="">Select Service Type</option>
                                <option value="1 month">1 Month</option>
                                <option value="subscription">Subscription</option>
                              </select>
                            </div>
                            <div class="mt-2">
                              <?php echo $data['roleaccess']['can_update']==1?'<button type="submit" class="btn btn-primary me-2">Save changes</button>':'';?> 
                              <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                              <?php echo $data['roleaccess']['can_update']==1?'<a class="pull-right deleteBtn pointer"><i class="bx bx-trash me-1"></i> Delete</a>':'';?>
                            </div>
                          </form>
                        </div>
                      </div>
                        <!-- /Account -->
                      </div>
                  </div>
              </div>
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">              
              $(document).ready(function(){                
                function fetchData() {
                    $("#datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    $.post("/api/fetch-table/",{"t":"services","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        $("#datatable tbody").html("");
                        if (result.status) {
                            $('#datatable').DataTable().destroy();
                            $('#datatable').DataTable({
                                data: result.datatable,
                                stateSave: true,
                                columnDefs: []
                            });
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }
                fetchData();

                if (localStorage.getItem("view-box")!=null) {
                  if (localStorage.getItem("view-box")==1 && $('#box-can-add').val()==1) {
                    $(".deleteBtn").addClass("d-none");                  
                    $("#boxForm button[type=submit]").text("Add");
                    $("#viewBox .card-header > b").text("Add New Service");
                    $("#viewBox").removeClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").addClass("col-lg-7");  
                    } else {
                      $("#viewBox").addClass("d-none");
                    }
                  } else {
                    $("#viewBox").addClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").removeClass("col-lg-7");  
                    } else {
                      $("#table-list-container").show();  
                      $("#viewBoxContainer").removeClass("col-md-12");
                      $("#viewBoxContainer").addClass("col-md-6");
                    }
                  }
                }

                $(".deleteBtn").click(function(){
                  // alert("Are you sure you want to delete?");
                  toastr.options = {
                    "closeButton": true,
                    "showDuration": "10000",
                    "hideDuration": "500",
                }
                  toastr.error('<button type="button" class="btn btn-primary btn-toastr btn-toastr-yes mt-2">YES</button><button type="button" class="btn btn-outline-secondary btn-toastr btn-toastr-no mt-2">NO</button>' , 'Are you sure you want to delete?');
                })

                $(document).on('click', '.btn-toastr-yes', function() {                  
                  $.post("/api/delete-data",{"token":localStorage.getItem("token"),"t":"services","id":$("#boxForm input[name=uuid]").val()},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        $(".deleteBtn").addClass("d-none");                  
                        $("#boxForm button[type=submit]").text("Add");
                        $("#viewBox .card-header > b").text("Add New Service");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
                                

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update Service Details");
                  $("#viewBox").removeClass("d-none");
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-7");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"services"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $("#boxForm input[name=service_name]").val(result.data.service_name);
                      $("#boxForm input[name=service_description]").val(result.data.service_description);
                      $("#boxForm input[name=service_price]").val(result.data.service_price);
                      $("#service_type").val(result.data.service_type);
                      $("#boxForm input[name=uuid]").val(result.data.uuid);

                      $(".password-box").hide();
                      $(".password-box input[name=password]").removeAttr("required");
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                })

                $(".addBtn").click(function(){
                  $("#boxForm :input").val('');
                  $(".deleteBtn").addClass("d-none");                  
                  $("#boxForm button[type=submit]").text("Add");
                  $("#viewBox .card-header > b").text("Add New Service");
                  $("#viewBox").removeClass("d-none");
                  $(".password-box").show();
                  $(".password-box input[name=password]").prop("required",true);
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-7");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  fetchData();
                });

                $("#boxForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"services","data":$(this).serialize(),"unique_index":"email"},function(response){
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

                $(".closeBtn").click(function(e){
                  e.preventDefault();
                  $("#viewBox").addClass("d-none");
                  localStorage.setItem("view-box",0);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").removeClass("col-lg-7");  
                  } else {
                    $("#table-list-container").show();  
                    $("#viewBoxContainer").removeClass("col-md-12");
                    $("#viewBoxContainer").addClass("col-md-6");
                  }
                  fetchData();
                })

                $("#datatable").on("click",".changePassBtn",function(){                  
                  $("#changePassModal input[name=uuid]").val($(this).data("id"));
                  $("#changePassModal").modal("show");
                })                

                $("#changePassForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);

                  if ($("#changePassForm input[name=new_pass]").val()!=$("#changePassForm input[name=new_pass2]").val()) {
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    toastr.error("Confirmed password doesn't match.", 'Oops!')
                    return;  
                  }
                  
                  $.post("/api/update-password",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#changePassForm :input').val("");
                        $("#changePassModal").modal("hide");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                })
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>