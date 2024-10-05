<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="/assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title><?php echo $data['title']; ?> | Client Portal</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/assets/css/default.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="/assets/vendor/css/pages/page-auth.css" />
    <!-- Helpers -->
    <script src="/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/assets/js/config.js"></script>    
  </head>

  <body>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="/" class="app-brand-link gap-2">
                  <img src="/assets/img/logo.png" style="width:100%;">
                </a>
              </div>
              <!-- /Logo -->

              <form id="form-forgot-password" class="mb-3" action="" method="POST">                
                <div class="text-center">
                    <h5 class="content-group">Password recovery<br><small class="display-block">We'll send you instructions in email</small></h5>
                 </div>
                 <div class="form-group has-feedback">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                    <div class="form-control-feedback">
                       <i class="icon-mail5 text-muted"></i>
                    </div>
                 </div>                
                 <div class="mt-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">Recover Password</button>
                  </div>
                  <div class="mt-3">
                    Return to <a href="/login/">Login page!</a>
                  </div>
                </div>                                              
              </form>
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/assets/vendor/libs/popper/popper.js"></script>
    <script src="/assets/vendor/js/bootstrap.js"></script>
    <script src="/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Page JS -->
   <script type="text/javascript">
      $(document).ready(function(){               
         localStorage.clear();

         $("#form-forgot-password").submit(function(e){
             e.preventDefault();
             $("#error-msg").html("");
             var $this = this;
             var button_text = $("button[type=submit]",this).html();
             $("button[type=submit]",$this).attr("disabled","disabled");                
             $("button[type=submit]",$this).html(loading_text);
             $.post("/api/forgot-password",{"email":$("input[name=email]",this).val()},function(data){
              $("button[type=submit]",$this).html(button_text);
              $("button[type=submit]",$this).removeAttr("disabled");
               var result = JSON.parse(data);
               if (result.status) {
                  toastr.success(result.message);
                  $("#form-forgot-password :input").val("");
               } else {                                    
                  toastr.error(result.message, 'Oops!')
               }
             })
         })
     })
   </script>
    
  </body>
</html>