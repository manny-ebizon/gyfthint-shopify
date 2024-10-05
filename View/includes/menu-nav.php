<?php if ($_SESSION['role']=="admin"): ?>
  <style type="text/css">
    .bx-menu:before {
      color: #fff;
    }
  </style>
<?php endif;?>
<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">              
    <b class="text-black"><?php if(isset($data['menu-title'])) { echo $data['menu-title']; } ?></b>
    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <?php if (isset($_SESSION['license'])): ?>
        <li class="me-2"><label class="badge bg-label-primary fw-bold"><?php echo $_SESSION['license']; ?></label></li>  
      <?php endif ?>
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">        
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online user-initials">            
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online user-initials">
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block name-holder"></span>
                  <small class="text-muted role-holder"><?php echo $_SESSION['role']; ?></small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a class="dropdown-item" href="/profile/">
              <i class="bx bx-user me-2"></i>
              <span class="align-middle">Profile Settings</span>
            </a>
          </li>
          <?php if (false): ?>
            <li>
              <a class="dropdown-item" href="/notifications/">
                <span class="d-flex align-items-center align-middle">
                  <i class='bx bx-bell me-2'></i>
                  <span class="flex-grow-1 align-middle">Notifications</span>
                  <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20"></span>
                </span>
              </a>
            </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <?php endif ?>
          
          <li>
            <a class="dropdown-item" href="/logout/">
              <i class="bx bx-power-off me-2"></i>
              <span class="align-middle">Log Out</span>
            </a>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>
<!-- / Navbar -->