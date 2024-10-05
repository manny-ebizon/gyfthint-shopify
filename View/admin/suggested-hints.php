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
              <div class="row mb-5">
                  <div id="table-list-container" class="col-4">
                    <!-- Striped Rows -->
                    <div class="card">
                      <?php if (false): ?>
                        <h5 class="card-header pb-3"><i class='bx bxs-gears'></i> Suggested Hints</h5>               
                      <?php endif ?>
                      <div class="card-body">
                        <form id="suggestedhints" method="POST">
                        <div class="row">
                          <div class="col-12">                                                        
                            <?php if (count($data['suggested_hints'])==0): ?>
                              <?php if ($data['license'][0]['label']=="Free"): ?>
                                  <input style="margin-bottom:12px;" type="text" name="suggested_hints[]" value="" class="form-control" placeholder="PDP Url">
                              <?php elseif ($data['license'][0]['label']=="Pro"): ?>
                                  <input style="margin-bottom:12px;" type="text" name="suggested_hints[]" value="" class="form-control"  placeholder="PDP Url">
                                  <input style="margin-bottom:12px;" type="text" name="suggested_hints[]" value="" class="form-control"  placeholder="PDP Url">
                                  <input style="margin-bottom:12px;" type="text" name="suggested_hints[]" value="" class="form-control" placeholder="PDP Url">
                              <?php endif ?>
                            <?php else: ?>
                              <?php foreach ($data['suggested_hints'] as $key => $value): ?>
                                <input style="margin-bottom:12px;" type="text" name="suggested_hints[<?php echo $value['uuid']; ?>]" value="<?php echo $value['hint_url']; ?>" class="form-control">
                              <?php endforeach ?>
                            <?php endif ?>
                            
                          </div>
                        </div>                          
                        <button type="submit" class="mt-2 btn btn-primary btn-sm">Update</button>
                        </form>
                      </div>                      
                    </div>
                    <!--/ Striped Rows -->
                  </div>
              </div>
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">              
              $(document).ready(function(){            
                $("#suggestedhints").submit(function(e){
                  e.preventDefault();
                  var $this = this;
                  var button_text = $("button[type=submit]",this).text();
                  $("button[type=submit]",$this).attr("disabled","disabled");                
                  $("button[type=submit]",$this).html(loading_text);
                  $.post("/api/update-suggested-hints",{"token":localStorage.getItem("token"),"data":$(this).serialize()},function(response){
                    $("button[type=submit]",$this).html(button_text);
                    $("button[type=submit]",$this).removeAttr("disabled");
                    console.log(response);
                    var result = JSON.parse(response);                    
                    if (result.status) {
                        toastr.success(result.message, 'Good Job!')
                        setTimeout(function(){
                          location.reload()
                        },1500);
                    } else {
                        toastr.error(result.message, 'Oops!')
                    }
                  })
                });
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>