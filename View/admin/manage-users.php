<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<!-- Layout container -->
<div class="layout-page">
    <!-- main nav -->
    <?php include('View/includes/menu-nav.php'); ?>
<!-- Change Pass Modal -->
   <div id="changePassModal" class="modal fade">
      <div class="modal-dialog modal-sm">
         <div class="modal-content">
            <form id="changePassForm">
               <input type="hidden" name="uuid" value=""/>
               <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                  <button type="button" class="close btn btn-link p-0" data-dismiss="modal">&times;</button>
               </div>
               <div class="modal-body py-2">               
                  <div class="form-group mb-2">
                     <label>Old Password <span class="text-danger">*</span></label>
                     <input type="password" name="old_pass" class="form-control" value="" required/>
                  </div>
                  <div class="form-group mb-2">
                     <label>New Password <span class="text-danger">*</span></label>
                     <input type="password" name="new_pass" class="form-control" value="" required/>
                  </div>
                  <div class="form-group mb-2">
                     <label>Confirm New Password <span class="text-danger">*</span></label>
                     <input type="password" name="new_pass2" class="form-control" value="" required/>
                  </div>
               </div>
               <div class="modal-footer text-right pb-0">
                  <button type="button" class="btn btn-link" data-dismiss="modal"><?php __("Close"); ?></button>
                  <button type="submit" class="btn btn-success"><?php __("Update"); ?></button>
               </div>
            </form>
         </div>
      </div>
   </div>
<!-- /Change Pass Modal --> 
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
                              <th>Name</th>
                              <th>Company</th>
                              <th>Email</th>
                              <th>Role</th>
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
                        <h5 class="card-header"><b>Add New User</b><a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>
                        <div class="card-body">
                          <form id="boxForm" method="POST">
                            <input type="hidden" name="uuid">
                            <input type="hidden" name="role" id="role" value="admin"/>
                            <div class="row">
                              <div class="mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="first_name"
                          name="first_name"
                          placeholder="Enter First Name"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="last_name"
                          name="last_name"
                          placeholder="Enter Last Name"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="company" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="company"
                          name="company"
                          placeholder="Enter Company Name"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input
                          type="email"
                          class="form-control"
                          id="email"
                          name="email"
                          placeholder="Enter Email Address"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3 password-box">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input
                          type="password"
                          class="form-control"
                          id="password"
                          name="password"
                          placeholder="Enter Password"
                          autofocus
                          required
                        />
                      </div>
                      <div class="mb-3" id="role_input">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select
                          class="form-control"
                          id="role_id"
                          name="role_id"
                          autofocus required>
                          <option value="">Enter Role</option>
                        </select>
                      </div>
                      <div class="mb-3 d-none" id="lead_selection">
                        <label for="lead_id" class="form-label">Lead <span class="text-danger">*</span></label>
                        <select
                          class="form-control"
                          id="lead_id"
                          name="lead_id"
                          autofocus required>
                          <option value="">Enter Lead</option>
                        </select>
                      </div>
                            </div>
                            <div class="mt-2">
                              <?php echo $data['roleaccess']['can_update']==1?'<button type="submit" class="btn btn-primary me-2">Save changes</button>':'';?> 
                              <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                              <?php echo $data['roleaccess']['can_update']==1?'<a class="pull-right deleteBtn pointer"><i class="bx bx-trash me-1"></i> Delete</a>':'';?>
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
                    $.post("/api/fetch-table/",{"t":"users","token":localStorage.getItem("token")},function(data){
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
                
                function fetchRoles(){
                  $.post("/api/fetch_for_dropdown/",{"t":"roles","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        $("#role_id").html("");
                        if (result.status) {
                            var roles = '<option value="">Select One Role</option>';
                            $.each(result.data, function(i, item){
                              roles += '<option value="'+item.id+'" tier="'+item.tier+'">' + item.role_name + '</option>';
                            });
                            $("#role_id").html(roles)
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }
                fetchData();
                var currentRole = '<?php echo $_SESSION['role_name']; ?>';
                if(currentRole=="superadmin"){
                  $('#role_input').html("");
                  $('#role_input').html('<input type="hidden" class="form-control" id="role_id" name="role_id" value="2"/>');
                } else {
                  fetchRoles();
                }

                if (localStorage.getItem("view-box")!=null) {
                  if (localStorage.getItem("view-box")==1 && $('#box-can-add').val()==1) {
                    $(".deleteBtn").addClass("d-none");                  
                    $("#boxForm button[type=submit]").text("Add");
                    $("#viewBox .card-header > b").text("Add New User");
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
                  $.post("/api/delete-data",{"token":localStorage.getItem("token"),"t":"users","id":$("#boxForm input[name=uuid]").val()},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        $(".deleteBtn").addClass("d-none");                  
                        $("#boxForm button[type=submit]").text("Add");
                        $("#viewBox .card-header > b").text("Add New User");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
                                

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update User Details");
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
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"users"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $("#boxForm input[name=first_name]").val(result.data.first_name);
                      $("#boxForm input[name=last_name]").val(result.data.last_name);
                      $("#boxForm input[name=company]").val(result.data.company);
                      $("#boxForm input[name=website]").val(result.data.website);
                      $("#boxForm input[name=email]").val(result.data.email);
                      $('#role_id').val(result.data.role_id)
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
                  $("#role").val('admin'); //default for non-client
                  $(".deleteBtn").addClass("d-none");                  
                  $("#boxForm button[type=submit]").text("Add");
                  $("#viewBox .card-header > b").text("Add New User");
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
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"users","data":$(this).serialize(),"unique_index":"email"},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    console.log(result);
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
                
                $('#role_id').change(function(e){
                  e.preventDefault();
                  let id = $('#role_id').val();
                  $.post("/api/fetch_data_by_id/",{"id":id,"token":localStorage.getItem("token"),"t":"roles"},function(response){
                    var result = JSON.parse(response);
                    console.log(result);

                    if (result.status) {
                      if(result.data.tier==3){
                        //get all leads
                        $.post("/api/fetch_for_dropdown/",{"t":"users","field":"r.tier","value":2,"token":localStorage.getItem("token")},function(data2){
                            var result_lead = JSON.parse(data2);
                            $("#lead_id").html("");
                            if (result_lead.status) {
                                var leads = '<option value="">Enter Lead</option>';
                                $.each(result_lead.data, function(i2, item2){
                                  leads += '<option value="'+item2.uuid+'">' + item2.first_name + ' ' + item2.last_name + '</option>';
                                });
                                $("#lead_id").html(leads)
                                $('#lead_selection').removeClass('d-none');
                            } else {
                                toastr.error(result.message, 'Oops!')
                            }
                        })
                        //end
                      } else {
                        $('#lead_selection').addClass('d-none');
                      }
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                });

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