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
                              <th>Role Name</th>
                              <th>Role Description</th>
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
                        <h5 class="card-header"><b>Add New Role</b><a class="pull-right closeBtn pointer"><i class="bx bx-x me-1"></i></a></h5>
                        <div class="card-body">
                          <form id="boxForm" method="POST">
                            <input type="hidden" name="uuid" />
                            <input type="hidden" name="role_id" id="role_id" class="form-control"/>
                            <div class="row">
                              <div class="mb-3">
                              <label for="role_name" class="form-label">Name <span class="text-danger">*</span></label>
                              <input
                                type="text"
                                class="form-control"
                                id="role_name"
                                name="role_name"
                                placeholder="Enter Role Name"
                                autofocus
                                required
                              />
                            </div>
                            <div class="mb-3">
                              <label for="role_description" class="form-label">Description <span class="text-danger">*</span></label>
                              <input
                                type="text"
                                class="form-control"
                                id="role_description"
                                name="role_description"
                                placeholder="Enter Role Description"
                                autofocus
                                required
                              />
                            </div>
                            <div class="mb-3">
                              <label for="role_description" class="form-label">Tier</span></label>
                              <select
                                class="form-control"
                                id="tier"
                                name="tier">
                                <option value="0"></option>
                                <option value="1">Tier 1</option>
                                <option value="2">Tier 2</option>
                                <option value="3">Tier 3</option>
                              </select>
                            </div>
                            <div class="mb-3">
                              <label for="access configuration" class="form-label"> <b>Access Configuration</b> </label>
                              <hr class="my-0" />
                            </div>
                            <div class="mb-3">
                              <table class="table table-access-config">
                                <tbody id="rolemodulecheckboxes">
                                </tbody>
                              </table>
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
                    $.post("/api/fetch-table/",{"t":"roles","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        console.log(data);
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

                function fetchModules(){
                  $.post("/api/fetch_for_dropdown/",{"t":"modules","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        $("#rolemodulecheckboxes").html("");
                        if (result.status) {
                            var list = '';
                            $.each(result.data, function(i, item){
                              list += '<tr>';
                              list += '<td>'+item.module_name+'</td>';
                              list += '<td><input type="hidden" name="module_id[]" value="'+item.id+'"></input></td>';
                              list += '<td><input class="access_module" type="checkbox" name="permission['+item.id+'][can_read]">Read</input></td>';
                              list += '<td><input class="access_module" type="checkbox" name="permission['+item.id+'][can_add]">Add</input></td>';
                              list += '<td><input class="access_module" type="checkbox" name="permission['+item.id+'][can_update]">Update</input></td>';
                              list += '<td><input class="access_module" type="checkbox" name="permission['+item.id+'][can_delete]">Delete</input></td>';
                              list += '</tr>';
                            });
                            $("#rolemodulecheckboxes").html(list)
                        } else {
                            toastr.error(result.message, 'Oops!')
                        }
                    })
                }
                fetchData();
                fetchModules();

                if (localStorage.getItem("view-box")!=null) {
                  if (localStorage.getItem("view-box")==1 && $('#box-can-add').val()==1) {
                    $(".deleteBtn").addClass("d-none");                  
                    $("#boxForm button[type=submit]").text("Add");
                    $("#viewBox .card-header > b").text("Add New Role");
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
                  $.post("/api/delete-data",{"token":localStorage.getItem("token"),"t":"roles","id":$("#boxForm input[name=uuid]").val()},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        $(".deleteBtn").addClass("d-none");                  
                        $("#boxForm button[type=submit]").text("Add");
                        $("#viewBox .card-header > b").text("Add New Role");
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
                                

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update Role & Access Details");
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
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"roles"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $("#boxForm input[name=role_name]").val(result.data.role_name);
                      $("#boxForm input[name=role_description]").val(result.data.role_description);
                      $("#tier").val(result.data.tier);
                      $("#boxForm input[name=uuid]").val(result.data.uuid);
                      $("#boxForm input[name=role_id]").val(result.data.id);

                      $("#rolemodulecheckboxes").html("");
                      var list = '';
                      $.each(result.data.rolemodules, function(i, item){
                        list += '<tr>';
                        list += '<td>'+item.module_name+'</td>';
                        list += '<td><input type="hidden" name="module_id[]" value="'+item.module_id+'"></input></td>';
                        list += item.can_read==1?'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_read]" checked>Read</input></td>':'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_read]">Read</input></td>';
                        list += item.can_add==1?'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_add]" checked>Add</input></td>':'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_add]">Add</input></td>';
                        list += item.can_update==1?'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_update]" checked>Update</input></td>':'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_update]">Update</input></td>';
                        list += item.can_delete==1?'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_delete]" checked>Delete</input></td>':'<td><input class="access_module" type="checkbox" name="permission['+item.module_id+'][can_delete]">Delete</input></td>';
                        list += '</tr>';
                      });
                      $("#rolemodulecheckboxes").html(list);

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
                  $("#viewBox .card-header > b").text("Add New Role");
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
                  fetchModules();
                });

                $("#boxForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"roles","data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#boxForm :input').val("");
                        fetchData();
                        fetchModules();
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