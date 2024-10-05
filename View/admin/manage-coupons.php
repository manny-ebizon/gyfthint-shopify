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
                      <h5 class="card-header pb-3"><i class='bx bxs-discount me-1'></i> Manage Coupons <button type="submit" class="btn btn-primary btn-sm ms-2 addBtn">Add <i class='bx bxs-plus-circle'></i></button></h5>                      
                      <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Code</th>
                              <th>Value</th>
                              <th>Assigned to</th>
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
                        <h5 class="card-header"><b>Add Coupon</b><a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>
                        <div class="card-body">
                          <form id="boxForm" method="POST">
                            <input type="hidden" name="uuid">
                            <div class="row">
                              <div class="mb-3">
                        <label for="coupon_code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="coupon_code"
                          name="coupon_code"
                          placeholder="Enter Coupon Code"
                          autofocus
                          required
                          style="text-transform: uppercase;"
                        />
                      </div>
                      <div class="mb-3">
                        <label for="coupon_type" class="form-label">Coupon Type <span class="text-danger">*</span></label>
                        <select
                          class="form-control"
                          id="coupon_type"
                          name="coupon_type"
                          autofocus
                          required
                        />
                          <option value="value">Value</option>
                          <option value="percentage">Percentage</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="coupon_value" class="form-label">Coupon Value <span class="text-danger">*</span></label>
                        <input
                          type="number"
                          class="form-control"
                          id="coupon_value"
                          name="coupon_value"
                          value="0"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="coupon_value" class="form-label">Assigned To</label>
                        <select class="form-control" name="assigned_to">
                          <option value="">Choose specific client</option>
                          <?php foreach ($data['clients'] as $key => $value): ?>
                            <option value="<?php echo $value['uuid']; ?>"><?php echo ucwords($value['first_name']." ".$value['last_name'])." - ".$value['email']; ?></option>
                          <?php endforeach ?>
                        </select>
                      </div>
                    </div>
                    <div class="mt-2">
                      <button type="submit" class="btn btn-primary me-2">Save changes</button>
                      <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                      <a class="pull-right deleteBtn pointer"><i class="bx bx-trash me-1"></i> Delete</a>
                    </div>
                  </form>
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
                    $.post("/api/fetch-table/",{"t":"coupons","token":localStorage.getItem("token")},function(data){
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
                  if (localStorage.getItem("view-box")==1) {
                    $(".deleteBtn").addClass("d-none");                  
                    $("#boxForm button[type=submit]").text("Add");
                    $("#viewBox .card-header > b").text("Add Coupon");
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
                  $.post("/api/delete-data",{"token":localStorage.getItem("token"),"t":"coupons","id":$("#boxForm input[name=uuid]").val()},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        $(".deleteBtn").addClass("d-none");                  
                        $("#boxForm button[type=submit]").text("Add");
                        $("#viewBox .card-header > b").text("Add Coupon");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
                                

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update Coupon");
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
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"coupons"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $("#boxForm input[name=coupon_code]").val(result.data.coupon_code);
                      $("#boxForm input[name=coupon_value]").val(result.data.coupon_value);
                      $("#boxForm select[name=coupon_type]").val(result.data.coupon_type);
                      $("#boxForm select[name=assigned_to]").val(result.data.assigned_to);                      
                      $("#boxForm input[name=uuid]").val(result.data.uuid);

                      $(".password-box").hide();
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                })

                $(".addBtn").click(function(){
                  $("#boxForm :input").val('');
                  $(".deleteBtn").addClass("d-none");                  
                  $("#boxForm button[type=submit]").text("Add");
                  $("#viewBox .card-header > b").text("Add Coupon");
                  $("#viewBox").removeClass("d-none");
                  $(".password-box").show();
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-7");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  $("#boxForm select[name=coupon_type]").val("value");
                  fetchData();
                });

                $("#boxForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"coupons","data":$(this).serialize(),"unique_index":"coupon_code"},function(response){
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
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>            