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

    <title><?php echo $data['title']; ?></title>

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
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="registration-inner">
          <!-- Register -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="/" class="app-brand-link gap-2">
                  <img src="/assets/img/logo.png" style="width:150px;">
                </a>
              </div>
              <!-- /Logo -->
              <form id="form-register" class="mb-3" action="" method="POST">
                <input type="hidden" name="redirect_url" value="<?php if(isset($_GET['redirect_url'])) { echo $_GET['redirect_url']; } ?>">
                <div class="text-center">
                    <h5 class="content-group">Create Profile</h5>
                 </div>
                 <div class="row">
                   <div class="col-6">
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
                   </div>
                   <div class="col-6">
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
                   </div>
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
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="phone"
                      name="phone"
                      placeholder="Enter Phone Number"
                      autofocus
                      required
                    />
                  </div>
                 <div class="mb-3">
                   <input type="checkbox" name="agree" style="cursor:pointer;margin-right: 5px;" required> I agree to receive messages from GyftHint at the phone number provided above notifying me of upcoming gifting events for my friends and family. I understand data rates may apply. I may reply STOP to opt out at any time.
                 </div>
                  <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100" type="submit">Send my code</button>
                  </div>
                </form>
              </div>
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

          $("#otpBtn").click(function(){
            $("#twoFAModal").modal("show");
             return;
          })

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
             if ($("#form-register input[name=password]").val()==$("#form-register input[name=password2]").val() && $("#form-register input[name=password]").val()!="") {                
                $.post("/api/register-customer",{"data":$("#form-register").serialize()},function(data){
                $("button[type=submit]",$this).html(button_text);
                $("button[type=submit]",$this).removeAttr("disabled");
                console.log(data);                           
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
         $("#form-register").submit(function(e){
             e.preventDefault();
             $("#twoFAModal").modal("show");
             return;
             var $this = this;
             var button_text = $("button[type=submit]",this).html();
             $("button[type=submit]",$this).attr("disabled","disabled");                
             $("button[type=submit]",$this).html(loading_text);
             if ($("#form-register input[name=password]").val()==$("#form-register input[name=password2]").val() && $("#form-register input[name=password]").val()!="") {                
                $.post("/api/register-customer",{"data":$(this).serialize()},function(data){
                $("button[type=submit]",$this).html(button_text);
                $("button[type=submit]",$this).removeAttr("disabled");                
                console.log(data);
                return;
                 var result = JSON.parse(data);
                 if (result.status) {
                    localStorage.setItem('userdata',JSON.stringify(result.userdata));
                    localStorage.setItem('token',result.token);
                    toastr.success("Sign up successful. Logging in...");
                    setTimeout(function(){
                      window.location = "/login";
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
     })
   </script>
    
  </body>
</html>