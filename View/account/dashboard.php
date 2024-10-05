<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style>
  
.metric-card {
  text-align: center;
  color: #002366;
  background-color: #fff;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
}
.metric-card h1 {
  font-size: 36px;
  margin: 0;
  color: #131872;
  font-weight: bold;
}
.metric-card p {
  margin: 5px 0;
  font-size: 18px;
}
.chart-card {
  background-color: #fff;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
}
.chart-title {
  font-size: 18px;
  margin-bottom: 20px;
}
.bar-chart .bar {
  background-color: #002366;  
  margin-bottom: 10px;
  border-radius: 5px;
  height: 30px;
  padding: 4px 0;
  color: #fff;
  text-indent: 10px;
  font-weight: bold;
}

  </style>
<!-- Layout container -->
<div class="layout-page">
    <!-- main nav -->
    <?php include('View/includes/menu-nav.php'); ?>

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="metric-card">
                        <h1>$960k</h1>
                        <p>Sales (GMV)</p>
                        <p class="text-success">+5.2%</p>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="metric-card">
                        <h1>25.4k</h1>
                        <p>Total GyftHints</p>
                        <p class="text-success">+5.2%</p>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="metric-card">
                        <h1>11.5k</h1>
                        <p>GyftHint accounts</p>
                        <p class="text-success">Text</p>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="metric-card">
                        <h1>63%</h1>
                        <p>Conversion rate</p>
                        <p class="text-success">+1.5%</p>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="metric-card">
                        <h1>1.5%</h1>
                        <p>Returns</p>
                        <p class="text-danger">-0.3%</p>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="chart-card">
                        <h2 class="chart-title">Top Products Added to GyftHint</h2>
                        <div class="bar-chart">
                          <div class="bar" style="width: 90%;">Product 1</div>
                          <div class="bar" style="width: 80%;">Product 2</div>
                          <div class="bar" style="width: 70%;">Product 3</div>
                          <div class="bar" style="width: 60%;">Product 4</div>
                          <div class="bar" style="width: 50%;">Product 5</div>
                          <div class="bar" style="width: 40%;">Product 6</div>
                          <div class="bar" style="width: 30%;">Product 7</div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="chart-card">
                        <h2 class="chart-title">Top GyftHints Purchased</h2>
                        <div class="bar-chart">
                          <div class="bar" style="width: 90%;">Product 1</div>
                          <div class="bar" style="width: 80%;">Product 2</div>
                          <div class="bar" style="width: 70%;">Product 3</div>
                          <div class="bar" style="width: 60%;">Product 4</div>
                          <div class="bar" style="width: 50%;">Product 5</div>
                          <div class="bar" style="width: 40%;">Product 6</div>
                          <div class="bar" style="width: 30%;">Product 7</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="chart-card">
                        <h2 class="chart-title">Sales Over Period Of Time</h2>
                        <div id="salesChart"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="chart-card">
                        <h2 class="chart-title">Top Categories of Products</h2>
                        <div id="categoriesChart"></div>
                      </div>
                    </div>
                  </div>
              </div>
              <!-- / Content -->
      <?php include('View/includes/foot-script.php'); ?>
      <!-- Include charting library, for example, Chart.js -->
  <!-- Include charting library, for example, Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Sample chart data and configuration
    var salesCtx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Sales',
          data: [50000, 60000, 70000, 80000, 65000, 90000],
          borderColor: '#002366',
          backgroundColor: 'rgba(0, 35, 102, 0.2)',
          fill: true
        }]
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    });

    var categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    var categoriesChart = new Chart(categoriesCtx, {
      type: 'doughnut',
      data: {
        labels: ['Category A', 'Category B', 'Category C', 'Category D'],
        datasets: [{
          data: [38.6, 22.5, 30.8, 8.1],
          backgroundColor: ['#002366', '#4A90E2', '#7ED321', '#F5A623']
        }]
      },
      options: {
        responsive: true,
        legend: {
          position: 'bottom'
        }
      }
    });
  </script>
    <?php include('View/includes/foot.php'); ?>