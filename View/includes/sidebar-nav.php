<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="/" class="app-brand-link">
      <img src="/assets/img/logo.png" style="width:80%;">
    </a>    
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>
  <?php if ($_SESSION['role']=="admin"): ?><h5 class="m-0 text-center text-black"><b>Admin Panel</b></h5><?php endif;?>
  <div style="clear:both;height: 20px;"></div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">   
    <?php if ($_SESSION['role']=="merchant"): ?>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="dashboard") {echo "active";} ?>">
        <a href="/dashboard/" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-dashboard"></i>
          <div>Dashboard</div>
        </a>
      </li>      
    <?php endif ?>    
    <?php if ($_SESSION['role']=="admin"): ?>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="merchants") {echo "active";} ?>">
        <a href="/admin/manage-merchants" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-user-account"></i>
          <div>Manage Merchants</div>
        </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="customers") {echo "active";} ?>">
        <a href="/admin/customers" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-user-account"></i>
          <div>Manage Customers</div>
        </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="licenses") {echo "active";} ?>">
          <a href="/admin/manage-licenses" class="menu-link">
            <i class="menu-icon tf-icons bx bx-sidebar"></i>
            <div>Manage Licenses</div>
          </a>
      </li>
    <?php endif ?>    
    <?php if ($_SESSION['role']=="merchant"): ?>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="suggested hints") {echo "active";} ?>">
        <a href="/suggested-hints" class="menu-link">
          <i class="menu-icon tf-icons bx bx-link"></i>
          <div>Suggested Hints</div>
        </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="orders") {echo "active";} ?>">
          <a href="/orders/" class="menu-link">
            <i class="menu-icon tf-icons bx bxs-package"></i>
            <div>Orders</div>
          </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="curated hints") {echo "active";} ?>">
        <a href="/curated-hints/" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-collection"></i>
          <div>Curated Hints</div>
        </a>
      </li>      
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="gyfthint value") {echo "active";} ?>">
        <a href="/gyfthint-value/" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-gift"></i>
          <div>Gyfthint Value</div>
        </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="promotions") {echo "active";} ?>">
        <a href="/promotions" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-megaphone"></i>
          <div>Promotions</div>
        </a>
      </li>
      <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="analytics") {echo "active";} ?>">
        <a href="/analytics" class="menu-link">          
          <i class="menu-icon tf-icons bx bxs-bar-chart-square"></i>
          <div>Analytics</div>
        </a>
      </li>
      <li class="disabled menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="advertising") {echo "active";} ?>">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-crown"></i>
          <div>Advertising</div>
        </a>
      </li>
      <li class="disabled menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="affiliates") {echo "active";} ?>">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-traffic"></i>
          <div>Affiliates</div>
        </a>
      </li>
      <?php if ($_SESSION['role']=="admin"): ?>
        <li class="disabled menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="customers") {echo "active";} ?>">
        <a href="#" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-user-account"></i>
          <div>Customers</div>
        </a>
      </li>
      <?php endif ?>      
    <?php endif ?>
    <li class="menu-item <?php if(isset($data['menu-link']) && $data['menu-link']=="profile") {echo "active";} ?>">
        <a href="/profile/" class="menu-link">
          <i class="menu-icon tf-icons bx bxs-user"></i>
          <div>Profile Settings</div>
        </a>
    </li>
  </ul>
</aside>
<!-- / Menu -->