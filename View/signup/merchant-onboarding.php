<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="/assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?php echo $data['title']; ?></title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

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

        .form-section {
            margin-bottom: 20px;
        }
        .form-section.hidden {
            display: none;
        }
        .form-section label {
            display: block;
            margin-bottom: 5px;
        }
        .form-section input, .form-section select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        .form-section .description {
            margin: 10px 0;
            font-style: italic;
        }
        #gsa-perc, #gsa-val, #nogsa-val,#nogsa-perc {
            font-size: 40px;
            font-weight: bold;
            text-align: center;
        }     
    </style>    
</head>

<body>
    <!-- Content -->
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
                <!-- Basic Information Section -->
                <?php if (false): ?>
                    <div class="text-center">
                        <h5 class="content-group">Merchant Onboarding Shopify</h5>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Input password"/>
                              </div>
                        </div>       
                    </div>
                    <div class="clear10"></div>
                    <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100" id="continue1" type="button">Continue</button>
                    </div>
                <?php endif ?>
                 <!-- Affiliate Onboarding Section -->
                <div class="form-section" id="affiliateSection">
                    <div class="clear20"></div>
                    <h5 class="text-center">Affiliate Onboarding</h5>
                    <label for="affiliateGroup">Do you have an existing Affiliate Group Relationship?</label>
                    <select id="affiliateGroup" name="affiliateGroup" class="form-control">
                        <option value="" disabled selected>Select an option</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                    
                    <div id="affiliateNoSection" class="d-none">
                        <p class="description">As a new Merchant customer without an existing affiliate group commission model, you will need to establish a direct affiliate relationship with GyftHint for all orders that GyftHint will send to you from the GyftHint Platform.</p>
                        <p><b>The GyftHint Standard Affiliate Commission Rate is 15%.</b>
                        <br> Create and apply a persistent (non-expiring) Affiliate Link Code for all products sold through GyftHint. This 15% discount will be invisible to the buyer, who will pay the full GMV for the product, but will be captured by you as an affiliate commission to be paid out monthly based on the orders from GyftHint processed in the previous month.</p>                        
                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100" type="button" onclick="jumpToAffiliateCodeSection(this)">Generate Affiliate Code</button>
                        </div>
                    </div>
                    
                    <div id="affiliateYesSection" class="d-none">
                        <label for="primaryAffiliateGroup">Who is your primary Affiliate Group today?</label>
                        <select id="primaryAffiliateGroup" name="primaryAffiliateGroup" class="form-control">
                            <option value="" disabled selected>Select an option</option>
                            <option value="group1">Group 1</option>
                            <option value="group2">Group 2</option>
                            <option value="other">Other</option>
                        </select>
                        
                        <div id="otherAffiliateGroupSection" class="d-none">
                            <label for="otherAffiliateGroupName">Other Affiliate Group Name:</label>
                            <input type="text" id="otherAffiliateGroupName" name="otherAffiliateGroupName" class="form-control">
                        </div>
                        
                        <label for="affiliateGroupUrl">Provide URL for Affiliate Group website:</label>
                        <input type="url" id="affiliateGroupUrl" name="affiliateGroupUrl" class="form-control">
                        
                        <label for="blanketCommission">Do you pay a single blanket affiliate commission for any product sold?</label>
                        <select id="blanketCommission" name="blanketCommission" class="form-control">
                            <option value="" disabled selected>Select an option</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        
                        <div id="blanketCommissionYesSection" class="d-none">
                            <label for="totalAffiliateCommission">What is the Maximum Total Affiliate Group Commission you pay to the Affiliate Group today? (Enter percentage of product sale GMV)</label>
                            <input type="number" id="totalAffiliateCommission" name="totalAffiliateCommission" class="form-control" value="0" min="0" max="100" step="0.01">
                            
                            <label for="flatAmountCommission">If you pay Affiliate Group a flat $ Amount per sale either with or without a per sale percentage, enter the flat $ Amount:</label>
                            <input type="number" id="flatAmountCommission" name="flatAmountCommission" class="form-control" value="0" min="0" step="0.01">                            

                            <div class="clear20"></div>                            
                            <div id="standard-affiliate">
                                <h5 class="text-center">GyftHint Standard Affiliate</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card" style="background-color: #fafafa;">
                                            <div class="card-header">
                                                <h5 class="card-title text-center">
                                                    Commission Rate %
                                                </h5>
                                            </div>
                                            <div id="gsa-perc" class="card-body">
                                                0%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card" style="background-color: #fafafa;">
                                            <div class="card-header">
                                                <h5 class="card-title text-center">
                                                    Commission Rate $
                                                </h5>
                                            </div>
                                            <div id="gsa-val" class="card-body">
                                                $0.00
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p>I agree that the GyftHint Standard Affiliate Commission Rate % and/or GyftHint Standard Affiliate Commission Rate $ Amount shown above shall be payable on all orders generated through the GyftHint Marketplace and processed with Affiliate Link Code below: </p>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="button" onclick="jumpToAffiliateCodeSection(this)">Generate Affiliate Code</button>
                            </div>
                        </div>

                        <div id="blanketCommissionNoSection" class="d-none">
                            <?php if (false): ?>
                                <label>The GyftHint Affiliate Commission Rate will be set to the Average Total Affiliate Group Commission paid per order during the past year (or past quarter if prior year data is not available). </label>
                                <input type="number" name="" class="form-control" min="0" step="0.01">
                            <?php endif ?>

                            <label>Enter total $ GMV for all Affiliate Group Orders Processed in the Period:</label>
                            <input type="number" name="" class="form-control" min="0" step="0.01">

                            <label>Enter total Affiliate Group Commission paid for all orders in the period:</label>
                            <input type="number" name="" class="form-control" min="0" step="0.01">

                            <?php if (false): ?>
                                <label>Calculated Average Total Affiliate Group Commissions paid in the period:</label>
                                <input type="number" name="" class="form-control" min="0" step="0.01">

                                <label>GyftHint Average Commission Rate 90% of Average Total Affiliate Rate: </label>
                                <input type="number" name="" class="form-control" min="0" step="0.01">
                            <?php endif ?>

                            <div id="nostandard-affiliate">
                                <h5 class="text-center">GyftHint Standard Affiliate</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card" style="background-color: #fafafa;">
                                            <div class="card-header">
                                                <h5 class="card-title text-center">
                                                    Commission Rate %
                                                </h5>
                                            </div>
                                            <div id="nogsa-perc" class="card-body">
                                                0%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card" style="background-color: #fafafa;">
                                            <div class="card-header">
                                                <h5 class="card-title text-center">
                                                    Commission Rate $
                                                </h5>
                                            </div>
                                            <div id="nogsa-val" class="card-body">
                                                $0.00
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p>I agree that the GyftHint Average Commission Rate shown above shall be payable on all orders generated through the GyftHint Marketplace and processed with Affiliate Link Code below: </p>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="button" onclick="jumpToAffiliateCodeSection(this)">Generate Affiliate Code</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Affiliate Code Section -->
                <div class="form-section hidden" id="affiliateCodeSection">
                    <h4>Affiliate Code: <b>GYFTHINT<span class="affperc"></span></b></h4>
                    <p>As the Merchant, I agree to pay out the commission(s) shown above for all product sales driven through GyftHint and processed with the link provided below. This discount and/or per sale fee will be invisible to the buyer, who will pay the full GMV for the product, but will be captured by you as an affiliate commission to paid out monthly based on the orders from GyftHint processed in the previous month:</p>                    
                    <p>This affiliate link code, when appended to the PDP URL Link, should be Deep Link compatible to allow it to link directly to the PDP for any product on your website. e.g.  <b style="color:blue">http://companyname.com/productdetailpage/?aff=GYFTHINT<span class="affperc"></span></b></p>
                </div>                
                <div id="final-submit" class="d-none">
                    <?php if (false): ?>
                        <div class="mb-3">
                       <input type="checkbox" name="agree" style="cursor:pointer;margin-right: 5px;"> I agree to receive messages from GyftHint at the phone number provided above notifying me of upcoming gifting events for my friends and family. I understand data rates may apply. I may reply STOP to opt out at any time.
                     </div>
                    <?php endif ?>
                     <div class="mb-3">
                        <button class="btn btn-primary d-grid w-100" type="submit">Submit</button>
                     </div>
                 </div>
              </form>
            </div>
          </div>
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
            $("#continue1").click(function(){
                $("#affiliateSection").removeClass("d-none");
                $(this).parent().addClass("d-none");
            })

            $("input[name=totalAffiliateCommission]").on("input",function(){
                if ($(this).val()>0) {
                    var newval = $(this).val();
                    var tenperc = $(this).val()*.9;
                    if (tenperc<0) {
                        tenperc = 0;
                    }
                    $("#gsa-perc").html(tenperc.toFixed(2)+"%");
                }
            })

            $("input[name=flatAmountCommission]").on("input",function(){
                if ($(this).val()>0) {                    
                    var newval = $(this).val();
                    var tenperc = $(this).val()*.9;                    
                    if (tenperc<0) {
                        tenperc = 0;
                    }
                    $("#gsa-val").html("$"+tenperc.toFixed(2));
                }
            })

            $("#affiliateCode").on("input",function(){
                if ($(this).val()!="") {
                    $("#final-submit").addClass("d-none");
                } else {
                    $("#final-submit").addClass("d-none");
                }
            })

            $('#affiliateGroup').on('change', function() {
                var value = this.value;
                if (value=="yes") {
                    $('#affiliateNoSection').addClass("d-none");
                    $('#affiliateYesSection').removeClass("d-none");
                    $("#affiliateCodeSection").addClass("d-none");
                } else if(value=="no") {
                    $(".affperc").html(15)
                    $('#affiliateNoSection').removeClass("d-none");
                    $('#affiliateYesSection').addClass("d-none");
                    $("#affiliateNoSection > div").removeClass("d-none");
                    $("#affiliateCodeSection").addClass("d-none");
                }
                $("#final-submit").addClass("d-none");                
            });

            $('#primaryAffiliateGroup').on('change', function() {
                var value = this.value;
                if (value=="other") {
                    $('#otherAffiliateGroupSection').removeClass("d-none");
                } else {
                    $('#otherAffiliateGroupSection').addClass("d-none");

                }
            });

            $('#blanketCommission').on('change', function() {
                var value = this.value;
                if (value=="yes") {
                    $('#blanketCommissionYesSection').removeClass("d-none");
                    $('#blanketCommissionNoSection').addClass("d-none");
                    $("#final-submit").addClass("d-none");
                } else {
                    $('#blanketCommissionYesSection').addClass("d-none");
                    $('#blanketCommissionNoSection').removeClass("d-none");
                    $("#final-submit").addClass("d-none");
                }
            });

            function jumpToAffiliateCodeSection($this) {
                $($this).parent().addClass("d-none");
                $("#affiliateCodeSection").removeClass("d-none");
                $('#affiliateCodeSection').removeClass('hidden');
                $('#affiliateCode').focus();
                $("#final-submit").removeClass("d-none");
            }

            window.jumpToAffiliateCodeSection = jumpToAffiliateCodeSection;

            $("#form-register").submit(function(e){
                e.preventDefault();
                // Add your form submission code here
                alert('Proceed to DocuSign!');
            });
        });
    </script>
</body>
</html>
