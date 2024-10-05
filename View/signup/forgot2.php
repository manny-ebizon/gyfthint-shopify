<?php include('View/includes/head.php'); ?>

   <!-- Page container -->
   <div class="page-container login-container">

      <!-- Page content -->
      <div class="page-content">

         <!-- Main content -->
         <div class="content-wrapper">

            <!-- Content area -->
            <div class="content">

               <!-- Password recovery -->
               <form id="recovery-form">
                  <div class="panel panel-body login-form bg-kolek-white">
                     <div class="text-center">
                        <div class="icon-object border-slate-300 text-slate-300"><i class="icon-spinner11"></i></div>
                        <h5 class="content-group">Password recovery <small class="display-block">We'll send you instructions in email</small></h5>
                     </div>

                     <div class="form-group has-feedback">
                        <input type="email" class="form-control" placeholder="Your email">
                        <div class="form-control-feedback">
                           <i class="icon-mail5 text-muted"></i>
                        </div>
                     </div>

                     <button type="submit" class="btn bg-blue btn-block">Reset password <i class="icon-circle-right2 position-right"></i></button>
                     <a class="btn btn-link btn-block" href="/login/">Back to Login</a>
                  </div>
               </form>
               <!-- /password recovery -->
               
            <?php include('View/includes/foot-script.php'); ?>
            <script type="text/javascript">
              $(document).ready(function(){               
                  localStorage.clear();
                  if (localStorage.getItem('token')!="" && localStorage.getItem('token')!=null) {
                     $.post("/login/token",{"token":localStorage.getItem('token')},function(data){
                        var result = JSON.parse(data);
                        if (result.status) {
                           window.location = "/dashboard/";
                        } else {
                           localStorage.clear();
                        }
                      })
                  }

                  $("#form-login").submit(function(e){
                      e.preventDefault();
                      var $this = this;
                      var button_text = $("button[type=submit]",this).html();
                      $("button[type=submit]",$this).attr("disabled","disabled");                
                      $("button[type=submit]",$this).html(loading_text);
                      $.post("/login/confirm",{"data":$(this).serialize()},function(data){                  
                        $("button[type=submit]",$this).html(button_text);
                        $("button[type=submit]",$this).removeAttr("disabled");
                        var result = JSON.parse(data);
                        if (result.status) {
                           localStorage.setItem('token',result.token);
                           localStorage.setItem('userdata',result.userdata);
                           window.location = "/dashboard/";
                        } else {
                           swal({
                                 title: "Oops...",
                                 text: result.message,
                                 confirmButtonColor: "#EF5350",
                                 type: "error",
                                 timer: 2000
                           });
                        }
                      })
                  })

                  $("#form-login input[name=username]").on("keydown",function(){
                     if ($(".err-message").hasClass("text-danger")) {
                        $(".err-message").text("Login to stay connected.");
                        $(".err-message").removeClass("text-danger");   
                     }
                  })
              })
          </script>
          <?php include('View/includes/foot.php'); ?>