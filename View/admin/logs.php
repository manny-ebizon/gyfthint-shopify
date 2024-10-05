<?php include('View/includes/head.php'); ?>     
    <?php include('View/includes/sidebar-nav.php');?>      
    <main class="main-content">
      <div class="position-relative iq-banner">
        <?php include('View/includes/menu-nav.php'); ?>
          <div class="iq-navbar-header" style="height: 215px;">
              <div class="container-fluid iq-container">
                  <div class="row">
                      <div class="col-md-12">
                          <div class="flex-wrap d-flex justify-content-between align-items-center">
                              <div>
                                  <h1>Hello <?php echo $_SESSION['userdata']['first_name']; ?>!</h1>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="iq-header-img">
                  <img src="/assets/images/dashboard/top-header.png" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
              </div>
          </div>
          <!-- Nav Header Component End -->
        <!--Nav End-->
      </div>
      <div class="conatiner-fluid content-inner mt-n5 py-0">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">UPLOAD LOGS</h4>
                            </div>
                        </div>
                        <div class="card-body" style="max-height:600px;overflow-y: scroll;">
                            <?php foreach ($data['upload'] as $key => $value): ?>
                                <?php if ($value!="") {
                                    echo "<hr/>".$value;
                                } ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">DOWNLOAD LOGS</h4>
                            </div>
                        </div>
                        <div class="card-body" style="max-height:600px;overflow-y: scroll;">
                            <?php foreach ($data['download'] as $key => $value): ?>
                                <?php if ($value!="") {
                                    echo "<hr/>".$value;
                                } ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          </div>
      <?php include('View/includes/foot.php'); ?> 
    </main>
    <!-- Wrapper End-->
    <?php include('View/includes/foot-script.php'); ?>   
    <script type="text/javascript">
        $(document).ready(function(){
            $('.datatable').DataTable();

            $("#check-prospect").submit(function(e){
                e.preventDefault();
                var $this = this;
                var button_text = $("button[type=submit]",this).text();
                $("button[type=submit]",$this).attr("disabled","disabled");                
                $("button[type=submit]",$this).html(loading_text);

                $.post("/admin/check-prospect/",{"data":$(this).serialize(),"token":localStorage.getItem("token")},function(data){
                    $("#check-prospect .result-message").text("");
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    var result = JSON.parse(data);
                    if (result.status) {
                        $("#check-prospect .result-message").removeClass("text-danger");
                        $("#check-prospect .result-message").text(result.data.length+" prospects found.");
                        $("#check-prospect .result-message").addClass("text-success");                  
                    } else {
                        $("#check-prospect .result-message").removeClass("text-success");
                        $("#check-prospect .result-message").text(result.message);
                        $("#check-prospect .result-message").addClass("text-danger");
                    }                    
                })
            })

             $("#check-prospect select").change(function(){
                $("#check-prospect .result-message").text("");
            })
        })
    </script>
  </body>
</html>