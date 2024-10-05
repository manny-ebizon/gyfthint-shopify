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
              <div class="row">                                
                  <div class="col-md-6">
                    <div class="card mb-4">
                      <h5 class="card-header pb-3"><i class='bx bxs-user me-1'></i> User Details
                      </h5>
                      <div class="card-body">
                        <div class="row">
                          <div class="mb-3">
                              <label for="email" class="form-label">Email Address</label>
                              <input class="form-control" type="text" name="email" readonly/>
                          </div>                          
                        </div>
                      </div>
                      <hr class="my-0" />
                      <div class="card-body">
                        <form id="formAccountProfile" method="POST" onsubmit="return false">
                          <div class="row">                            
                            <div class="mb-3 col-12">
                              <label for="lastName" class="form-label">Merchant Name</label>
                              <input class="form-control" type="text" name="name" required/>
                            </div>
                            <div class="mt-2">
                              <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                      <h5 class="card-header pb-3"><i class='bx bxs-lock me-1'></i> Change Password
                      </h5>                              
                      <div class="card-body">
                        <form id="formChangePassword">
                          <div class="row">
                            <div class="mb-3 col-12">
                              <label for="old_pass" class="form-label">Old Password</label>
                              <input class="form-control" type="password" name="old_pass" autofocus required
                              />
                            </div>
                            <div class="mb-3 col-12">
                              <label for="new_pass" class="form-label">New Password</label>
                              <input class="form-control" type="password" name="new_pass" autofocus required
                              />
                            </div>
                            <div class="mb-3 col-12">
                              <label for="new_pass2" class="form-label">Confirm New Password</label>
                              <input class="form-control" type="password" name="new_pass2" autofocus required
                              />
                            </div>
                          <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Update Password</button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- / Content -->
    <?php include('View/includes/foot-script.php'); ?>
    <script type="text/javascript">
      $(document).ready(function(){        
        $("#formChangePassword").submit(function(e){
          e.preventDefault();
          var $this = this;
          var button_text = $("button[type=submit]",this).html();
          $("button[type=submit]",$this).attr("disabled","disabled");                
          $("button[type=submit]",$this).html(loading_text);
          if ($("#formChangePassword input[name=new_pass]").val()==$("#formChangePassword input[name=new_pass2]").val() && $("#formChangePassword input[name=new_pass]").val()!="") {
            $.post("/api/update-password",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(data){
              $("button[type=submit]",$this).html(button_text);
              $("button[type=submit]",$this).removeAttr("disabled");              
               var result = JSON.parse(data);
               if (result.status) {
                  $("#formChangePassword")[0].reset();
                  toastr.success(result.message, 'Good Job!');
               } else {                                    
                  toastr.error(result.message, 'Oops!')
               }
            })
          } else {
            toastr.error("Password does not match!", 'Oops!')
            $("button[type=submit]",$this).html(button_text);
            $("button[type=submit]",$this).removeAttr("disabled");
          }          
        })

        function fetch_profile() {          
          $.post("/api/fetch-user/",{"token":localStorage.getItem("token")},function(response){            
            console.log(response);
            var result = JSON.parse(response);
            if (result.status) {
              if (localStorage.getItem('userdata')==null) {
                  localStorage.setItem("userdata",JSON.stringify(result.userdata));
              }
              $("input[name=email]").val(result.userdata.email);
              if (result.userdata.category=="" || result.userdata.category==null) {
                $("#category-text").remove();
                $("#category-dropdown").show();
              } else {
                $("#category-dropdown").remove();
                $("input[name=category]").val(result.userdata.category);  
              }
              
              $("#formAccountProfile input[name=name]").val(result.userdata.name);
            } else {
              toastr.error(result.message, 'Oops!')
            }
          })
          
        }
        fetch_profile();

        $("#category-dropdown").change(function(){
          if ($("#formAccountProfile input[name=category]").val()!=undefined) {
            $("#formAccountProfile input[name=category]").val($(this).val());
          } else {
            $("#formAccountProfile").prepend('<input type="hidden" name="category" value="'+$(this).val()+'"/>');
          }
        })

        $("#formAccountProfile").submit(function(e){
          e.preventDefault();
          var $this = this;
          var button_text = $("button[type=submit]",this).html();
          $("button[type=submit]",$this).attr("disabled","disabled");                
          $("button[type=submit]",$this).html(loading_text);
          
          $.post("/api/update-profile",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(data){
            $("button[type=submit]",$this).html(button_text);
            $("button[type=submit]",$this).removeAttr("disabled");            
             var result = JSON.parse(data);
             if (result.status) {
                localStorage.setItem("token",result.token);
                localStorage.setItem("userdata",JSON.stringify(result.userdata));
                userdata = result.userdata;
                toastr.success(result.message, 'Good Job!');
                fetch_profile()
             } else {                                    
                toastr.error(result.message, 'Oops!')
             }
          })    
        })
      })
    </script>
    <?php include('View/includes/foot.php'); ?>