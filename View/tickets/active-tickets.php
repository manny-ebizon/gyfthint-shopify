<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<!-- Layout container -->
<div class="layout-page">
    <!-- Assigned To Modal -->
    <div id="assginedToModal" class="modal fade">
      <div class="modal-dialog modal-md">
         <div class="modal-content">
            <form id="assignedToForm">
               <input type="hidden" name="ticket_id" class="ticket_id" value=""/>
               <input type="hidden" name="tier_type" id="tier_type" value="2">
               <div class="modal-header">
                <h5 class="modal-title">Assign Ticket To</h5>
                  <button type="button" class="close btn btn-link p-0" data-dismiss="modal">&times;</button>
               </div>
               <div class="modal-body py-2">               
                  <div class="form-group mb-2">
                     <label>Staff Name <span class="text-danger">*</span></label>
                     <select class="form-control" name="assigned_to" id="assigned_to">
                      </select>
                  </div>
               </div>
               <div class="modal-footer text-right pb-0">
                  <button type="button" class="btn btn-link" data-dismiss="modal"><?php __("Close"); ?></button>
                  <button type="submit" class="btn btn-success"><?php __("Save"); ?></button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!-- Assigned To -->

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
                      <h5 class="card-header pb-3"><?php echo "<i class='bx ".$data['roleaccess']['module_icon']."'></i> Active Tickets"?>
                      <div class="table-responsive text-nowrap">
                        <table id="datatable" class="table table-striped">
                          <thead>
                            <tr>
                              <th>Ticket No.</th>
                              <th>Name</th>
                              <th>Details</th>
                              <th>Status</th>
                              <th>Created By</th>
                            </tr>
                          </thead>
                          <tbody class="table-border-bottom-0">                            
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <!--/ Striped Rows -->
                  </div>
                  <div id="viewBoxContainer" class="col-md-6 col-lg-6">
                    <div id="viewBox" class="card mb-4 d-none">
                        <div class="ticket-thread">
                          <div class="ticket-thread-header clearfix">
                            <div class="row">
                              <div class="col-lg-6">
                                  <div class="ticket-thread-about">
                                      <h6 class="m-b-0 ticket-header">Aiden Chavez</h6>
                                      <small class="ticket-id"></small>
                                  </div>
                              </div>
                              <div class="col-lg-6 hidden-sm text-right">
                                <?php 
                                  if($_SESSION['tier'] == 2){
                                    echo '<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm" id="btn-update-assign-to"><i class="bx bx-group"></i> Assign</a> ';
                                    echo '<a href="javascript:void(0);" class="btn btn-outline-success btn-sm doneBtn"><i class="bx bxs-user-check"></i> Done</a>';
                                  }
                                  if($_SESSION['tier'] == 3){
                                    echo '<a href="javascript:void(0);" class="btn btn-outline-success btn-sm doneBtn"><i class="bx bxs-user-check"></i> Done</a>';
                                  }
                                ?>
                                  <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm closeBtn"><i class='bx bx-x' ></i> Close</a>
                              </div>
                            </div>
                          </div>
                          <div class="ticket-thread-history">
                              <ul class="m-b-0">
                                  <li class="clearfix">
                                      <div class="message-data">
                                            <img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">
                                            <span class="message-data-user ticket-date-user"></span>
                                            <span class="message-data-time ticket-date-created"></span>  
                                      </div>
                                      <div class="message my-message ticket-body-message"> </div>
                                  </li>
                              </ul>
                              <ul class="m-b-0" id="threads">
                              </ul>
                            </div>
                            <form id="replyForm" method="POST">
                              <div class="ticket-thread-message clearfix">
                                <input type="checkbox" id="is_internal" name="is_internal" checked > Internal Reply</input>
                                <input type="hidden" class="form-control" id="ticket_id" name="ticket_id" class="ticket_id"/>
                                <input type="hidden" class="form-control" id="uuid" name="uuid"/>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="message-body" name="body" placeholder="Type a message" aria-label="Reply" aria-describedby="basic-addon2">
                                      <button class="btn btn-outline-secondary" type="submit"><i class='bx bx-paper-plane' ></i></button>
                                  </div>
                                  <a class="pointer"><i class='bx bx-paperclip'></i></a>
                              </div>
                            </form>
                        </div>
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
                    $.post("/ticketing/fetch-tickets/",{"status":"ongoing","token":localStorage.getItem("token")},function(data){
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
                function fetchTicketHistory() {
                    //$("#datatable tbody").html('<tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td><td><div class="loading-content"><h5 class="loading-short"></h5></div></td></tr><tr><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td><td><div class="loading-content"><h5 class="loading-long"></h5></div></td></tr>')
                    var login_id = <?php echo $_SESSION['login_id']; ?>;
                    $.post("/api/fetch-ticket-thread",{"ticket_id":$('#ticket_id').val(), "t":"ticket_threads","token":localStorage.getItem("token")},function(data){
                        var result = JSON.parse(data);
                        if (result.status) {
                            var threadsData = result.data;
                            console.log(threadsData);
                            $('#threads').html("");
                            var threadHtml = '';
                            $.each(threadsData, function(index, value){
                              console.log(value['first_name']);
                              if(login_id == value['user_id']){
                                threadHtml += '<li class="clearfix">';
                                  threadHtml += '<div class="message-data text-right">';
                                    threadHtml += '<span class="message-data-time ticket-date-created">'+value['created_at']+'</span>';
                                    threadHtml += '<span class="message-data-user ticket-date-user">'+value['first_name']+'</span>';
                                      threadHtml +='<img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">';
                                  threadHtml +='</div>';
                                    threadHtml += '<div class="message other-message float-right ticket-body-message"> '+value['body']+' </div>';
                                threadHtml +='</li>';
                              }else{
                                threadHtml += '<li class="clearfix">';
                                  threadHtml += '<div class="message-data">';
                                      threadHtml +='<img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="avatar">';0
                                    threadHtml += '<span class="message-data-user ticket-date-user">'+value['first_name']+'</span>';
                                    threadHtml += '<span class="message-data-time ticket-date-created">'+value['created_at']+'</span>';
                                  threadHtml +='</div>';
                                    threadHtml += '<div class="message my-message ticket-body-message"> '+value['body']+' </div>';
                                threadHtml +='</li>';
                              }
                            });
                            $('#threads').html(threadHtml);
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
                    $("#viewBox .card-header > b").text("Add New Role");
                    $("#viewBox").removeClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").addClass("col-lg-6");  
                    } else {
                      $("#viewBox").addClass("d-none");
                    }
                  } else {
                    $("#viewBox").addClass("d-none");
                    var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (screenWidth >= 992) {
                      $("#table-list-container").removeClass("col-lg-6");  
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

                $(document).on('click', '#btn-update-assign-to', function() {
                  var tier = 3;
                  var site_id = <?php echo $_SESSION['site_id']; ?>;     
                  console.log(site_id);            
                  $.post("/ticketing/fetch_ticketing_user",{"token":localStorage.getItem("token"),"tier": tier,"site_id":site_id},function(response){
                    var result = JSON.parse(response);
                    $("#assigned_to").html("");
                    if (result.status) {
                      console.log('Users');
                      console.log(result.data);
                        $("#assginedToModal input[name=uuid]").val($(this).data("id"));
                        $("#assginedToModal").modal("show");
                        var users = '<option value="">Select Staff</option>';
                        $.each(result.data, function(i, item){
                          users += '<option value="'+item.uuid+'">' + item.first_name + ' ' + item.last_name + '</option>';
                        });
                        $("#assigned_to").html(users);
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
                                

                $("#datatable").on("click",".openBoxBtn",function(){
                  $(".deleteBtn").removeClass("d-none");
                  $("#boxForm button[type=submit]").text("Save changes");
                  $("#viewBox .card-header > b").text("Update Ticket");
                  $("#viewBox").removeClass("d-none");
                  localStorage.setItem("view-box",1);
                  var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                  if (screenWidth >= 992) {
                    $("#table-list-container").addClass("col-lg-6");  
                  } else {
                    $("#table-list-container").hide();
                    $("#viewBoxContainer").removeClass("col-md-6");
                    $("#viewBoxContainer").addClass("col-md-12");
                  }
                  
                  $.post("/api/fetch-data/",{"id":$(this).data("id"),"token":localStorage.getItem("token"),"t":"tickets"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                      $(".ticket-header").html(result.data.header);
                      var ticketIdHtml = 'Ticket No. : # '+ result.data.id.padStart(5, "0");
                      $(".ticket-id").html(ticketIdHtml);
                      $(".ticket-body-message").html(result.data.body);
                      $(".ticket-date-created").html(result.data.created_at);
                      $(".ticket-date-user").html(result.data.first_name);
                      $('.ticket_id').val(result.data.uuid);
                      $('#ticket_id').val(result.data.uuid);
                      $("#assginedToModal input[name=uuid]").val(result.data.uuid);
                      fetchTicketHistory();
                      fetchData();
                    } else {
                      toastr.error(result.message, 'Oops!')
                    }
                  })
                })

                $("#replyForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/addupdate-data",{"token":localStorage.getItem("token"),"t":"ticket_threads","data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')             
                        $("button[type=submit]",$this).html('<i class="bx bx-paper-plane" ></i>');
                        $('#message-body').val("");
                        fetchTicketHistory();
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
                    $("#table-list-container").addClass("col-lg-6");  
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

                $("#assignedToForm").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/ticketing/update_assignee",{"token":localStorage.getItem("token"),"t":"ticket_management_relation","data":$(this).serialize(), "status":"working"},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        $('#assignedToForm :input').val("");     
                        if (screenWidth >= 992) {
                          $("#table-list-container").removeClass("col-lg-6");  
                        } else {
                          $("#table-list-container").show();  
                          $("#viewBoxContainer").removeClass("col-md-12");
                          $("#viewBoxContainer").addClass("col-md-6");
                        }
                        
                        $('#viewBox').addClass('d-none');
                        $("#assginedToModal").modal("hide");
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
                    $("#table-list-container").removeClass("col-lg-6");  
                  } else {
                    $("#table-list-container").show();  
                    $("#viewBoxContainer").removeClass("col-md-12");
                    $("#viewBoxContainer").addClass("col-md-6");
                  }
                  fetchData();
                })

                $("#datatable").on("click",".assignedToBtn",function(){                  
                  $("#assginedToModal input[name=uuid]").val($(this).data("id"));
                  $("#assginedToModal").modal("show");
                })
                
                $(".doneBtn").click(function(){
                  // alert("Are you sure you want to delete?");
                  toastr.options = {
                    "closeButton": true,
                    "showDuration": "10000",
                    "hideDuration": "500",
                }
                  toastr.success('<button type="button" class="btn btn-primary btn-toastr btn-toastr-yes-done mt-2">YES</button><button type="button" class="btn btn-outline-secondary btn-toastr btn-toastr-no mt-2">NO</button>' , 'Tag Ticket for Review?');
                });

                $(document).on('click', '.btn-toastr-yes-done', function() {                  
                  $.post("/ticketing/update-ticket-status",{"token":localStorage.getItem("token"),"t":"tickets","ticket_id":$("#ticket_id").val(), "status":"internal review"},function(response){
                    var result = JSON.parse(response);
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!') ;
                        if (screenWidth >= 992) {
                          $("#table-list-container").removeClass("col-lg-6");  
                        } else {
                          $("#table-list-container").show();  
                          $("#viewBoxContainer").removeClass("col-md-12");
                          $("#viewBoxContainer").addClass("col-md-6");
                        }
                        $('#viewBox').addClass('d-none');
                        fetchData();
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });

              })
            </script>            
      <?php include('View/includes/foot.php'); ?>