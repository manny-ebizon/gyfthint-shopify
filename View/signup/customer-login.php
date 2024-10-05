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

    <title><?php echo $data['title']; ?> | Gyfthint Portal</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

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
    <!-- <script src="https://www.google.com/recaptcha/enterprise.js?render=6LfYyAsoAAAAAKX9U5UG5s77MJ0Av79uZEDwAz1l"></script> -->
    <script src="https://accounts.google.com/gsi/client" async></script>
    <style type="text/css">
        #google-signin iframe {
            margin: 0 auto !important;
        }
        body {
          background-repeat: no-repeat;
          background-attachment: fixed;
        }
    </style>
    <style type="text/css">
      .input-2fa, .input-2fa:focus {
          padding: 0px;
          text-align: center;
          font-weight: bold;
          font-size: 30px;
          height: 80px;
          width: 100%;
          border: 2px solid;
          outline: none;
      }
      /* Hide spinners for Chrome, Safari, Edge, and Opera */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide spinners for Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Optional: Hide arrows in IE and Edge */
        input[type=number]::-ms-clear,
        input[type=number]::-ms-reveal {
            display: none;
            width: 0;
            height: 0;
        }
    </style>
  </head>
  <body>
    <!-- Content -->
    <div id="twoFAModal" class="modal fade">
       <div class="modal-dialog">
          <div class="modal-content">
             <div class="modal-header">
                <h5 class="modal-title"><?php __("Two-Factor Authentication (2FA)"); ?></h5>              
                <button type="button" class="close" data-dismiss="modal">&times;</button>           
             </div>         
             <form id="twoFAForm" style="max-width:500px;width:100%;margin: auto;">            
                <input type="hidden" name="email">
                <div class="modal-body">
                   <p><?php __("Check your email for a 6-digit OTP to complete your login. Please enter the OTP to proceed."); ?></p>
                   <div class="clear"></div>
                   <div class="row">
                      <div class="col-2">
                         <input type="number" name="otp[0]" min="0" max="9" maxlength="1" class="form-control input-2fa"/>
                      </div>
                      <div class="col-2">
                         <input type="number" name="otp[1]" min="0" max="9" maxlength="1" class="form-control input-2fa"/>
                      </div>
                      <div class="col-2">
                         <input type="number" name="otp[2]" min="0" max="9"  maxlength="1"class="form-control input-2fa"/>
                      </div>
                      <div class="col-2">
                         <input type="number" name="otp[3]" min="0" max="9" maxlength="1" class="form-control input-2fa"/>
                      </div>
                      <div class="col-2">
                         <input type="number" name="otp[4]" min="0" max="9" maxlength="1" class="form-control input-2fa"/>
                      </div>
                      <div class="col-2">
                         <input type="number" name="otp[5]" min="0" max="9" maxlength="1" class="form-control input-2fa"/>
                      </div>
                   </div>
                   <div class="clear10"></div>
                   <p class="text-right mg0"><span class="text-muted"><?php __("Did not received the email?"); ?></span> <a class="text-success" id="resend-otp" href="#"><?php __("Resend OTP"); ?></a></p>
                   <div class="clear20"></div>
                   <button type="submit" class="btn btn-primary input-xlg" style="width:100%;"><?php __("Continue"); ?></button>
                   <div class="clear20"></div>
                </div>            
             </form>
          </div>      
       </div>
    </div>
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-4 mt-2" style="text-align: center;">
                <a href="/" class="app-brand-link gap-2">
                  <img src="/assets/img/logo.png" style="width:40%;">
                </a>
              </div>
              <!-- /Logo -->

              <form id="form-login" class="mb-3" action="" method="POST">
                  <div>
                    <h5 class="content-group">Welcome</h5>
                    <i>Please sign-in to your account</i>
                  </div>
                  <hr/>
                <div class="mb-3">
                    <label>Phone Number</label>
                  <input
                    type="text"
                    class="form-control"
                    name="phone"
                    placeholder="Phone Number"
                    required
                  />
                </div>             
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                </div>                
                <div class="mb-3">
                  Don't have an account yet? <a href="#">Sign up now!</a>
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

          <?php if (isset($_GET['token'])): ?>
            localStorage.setItem('token',"<?php echo $_GET['token']; ?>");            
            toastr.success("Logging in...");            
            window.location = "/dashboard/";
            return;
          <?php else: ?>
            // grecaptcha.enterprise.ready(async () => {
            // const token = await grecaptcha.enterprise.execute('6LfYyAsoAAAAAKX9U5UG5s77MJ0Av79uZEDwAz1l', {action: 'LOGIN'});
          });  
          <?php endif; ?>                    

         localStorage.clear();
         if (localStorage.getItem('token')!="" && localStorage.getItem('token')!=null) {
            $.post("/login/token",{"token":localStorage.getItem('token')},function(data){
               var result = JSON.parse(data);
               if (result.status) {
                  <?php if (isset($_GET['ref_url'])): ?>
                     window.location = "<?php echo $_GET['ref_url']; ?>";
                  <?php else: ?>
                     window.location = "/dashboard/";
                  <?php endif; ?>
                  
               } else {
                  localStorage.clear();
               }
             })
         }

        <?php if(isset($data['userdata']) && isset($data['token'])): ?>          
          localStorage.setItem('userdata',JSON.stringify(<?php echo $data['userdata']; ?>));          
          localStorage.setItem('token',"<?php echo $data['token']; ?>");
          window.location = "/profile/";
        <?php endif; ?>
          
        // Handle input for single digit restriction
           $('#twoFAForm .input-2fa').on('input', function () {
                $(this).removeClass("border-danger");
                this.value = this.value.slice(0, 1);
           });

           // Handle keyup for auto-focus to next input
           $('#twoFAForm .input-2fa').on('keyup', function () {
                $(this).removeClass("border-danger");
                if (this.value.length === this.maxLength) {
                   $(this).next('.input-2fa').focus();
                }
           });

           // Handle paste event
           $('#twoFAForm .input-2fa').on('paste', function (e) {
               e.preventDefault();
               let data = (e.originalEvent || e).clipboardData.getData('text');
               if (data.length > 0 && /^\d+$/.test(data)) {
                   $('#twoFAForm .input-2fa').each(function (index) {
                       $(this).val(data[index]);
                       $(this).removeClass("border-danger");
                   });
               }
           });


          $("#twoFAModal").submit(function(e){
            e.preventDefault();             
             var $this = this;
             var button_text = $("button[type=submit]",this).html();
             $("button[type=submit]",$this).attr("disabled","disabled");                
             $("button[type=submit]",$this).html(loading_text);
             if (true) {                
                var phone = $("input[name=phone]").val()
                $.post("/api/login-customer",{"phone":phone,"redirect_url":"<?php echo $_GET['redirect_url']; ?>"},function(data){
                $("button[type=submit]",$this).html(button_text);
                $("button[type=submit]",$this).removeAttr("disabled");
                 var result = JSON.parse(data);
                 if (result.status) {
                    localStorage.setItem('userdata',JSON.stringify(result.userdata));
                    localStorage.setItem('token',result.token);
                    toastr.success("Sign up successful. Logging in...");
                    setTimeout(function(){
                      window.location = result.redirect_url;
                    },500);
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

          // Function to get URL parameter by name
            function getParameterByName(name, url) {
                if (!url) url = window.location.href;
                name = name.replace(/[\[\]]/g, '\\$&');
                var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }            

         $("#form-login").submit(function(e){
            e.preventDefault();
            var phone = $("#form-login input[name=phone]").val();
            var cleanedPhone = phone.replace(/[+\s\-()]/g, '');             

            $.get("https://gyfthint-dev.uc.r.appspot.com/auth?phone=%2B" + cleanedPhone)
            .done(function(response) {
                if (response.data.statusCode == 200) {
                    var redirectUrl = getParameterByName('redirect_url');
                    if (redirectUrl) {
                        var separator = redirectUrl.includes('?') ? '&' : '?';
                        var newUrl = redirectUrl + separator + 'token=' + response.data.userId;
                        window.location.href = newUrl;
                    }
                } else {
                    toastr.error("Invalid phone number. Please check and try again.");
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                toastr.error("Invalid phone number. Please check and try again.");
            });
             
             // $.get("https://gyfthint-dev.uc.r.appspot.com/auth?phone=%2B"+cleanedPhone,function(response){
             //    console.log(response);
             //    if (response.data.statusCode==200) {                    
             //        var redirectUrl = getParameterByName('redirect_url');
             //        if (redirectUrl) {
             //            var separator = redirectUrl.includes('?') ? '&' : '?';
             //            var newUrl = redirectUrl + separator + 'token='+response.data.userId;
             //            console.log(newUrl);
             //            //window.location.href = newUrl;
             //       }
             //    } else {
             //        // toastr.error(result.message, 'Oops!')
             //    }
             // })

             //$("#twoFAModal").modal("show");
             return;
             var $this = this;
             var button_text = $("button[type=submit]",this).html();
             $("button[type=submit]",$this).attr("disabled","disabled");                
             $("button[type=submit]",$this).html(loading_text);

             $.post("/api/confirm",{"data":$(this).serialize()},function(data){
              $("button[type=submit]",$this).html(button_text);
              $("button[type=submit]",$this).removeAttr("disabled");                                                          
               var result = JSON.parse(data);
               if (result.status) {
                  localStorage.setItem('userdata',JSON.stringify(result.userdata));
                  localStorage.setItem('token',result.token);                  
                  toastr.success("Logging in...");
                  setTimeout(function(){
                    <?php if (isset($_GET['ref_url'])): ?>
                       window.location = "<?php echo $_GET['ref_url']; ?>";
                    <?php else: ?>
                       window.location = "/profile/";
                    <?php endif; ?>  
                  },1000);
               } else {                                    
                  toastr.error(result.message, 'Oops!')
               }
             })
         })      
   </script>    
  </body>
</html>