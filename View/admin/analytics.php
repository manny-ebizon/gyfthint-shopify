<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">
 
.main-kpis {
  display: flex;
  
  width: 100%;
  margin-bottom: 20px;
}

.kpi {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  text-align: center;
  width: 200px;
}

.kpi .icon {
  font-size: 24px;
  margin-bottom: 10px;
}

.kpi .details h2 {
  margin: 10px 0;
  font-size: 18px;
}

.kpi .details p {
  margin: 0;
  font-size: 16px;
}

.kpi .increase {
  color: green;
}

.top-pages {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  width: 100%;
  max-width: 800px;
  margin-bottom: 20px;
}

.top-pages table {
  width: 100%;
  border-collapse: collapse;
}

.top-pages table th,
.top-pages table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

.rates-chart {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
  width: 100%;
  max-width: 800px;
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
              <div class="main-kpis">
                <div class="row">
                  <div class="col-4 col-lg-3">
                    <div class="kpi">
                      <div class="icon">👥</div>
                      <div class="details">
                        <h2>Returning Users</h2>
                        <p>2,653 <span class="increase">+2.74%</span></p>
                      </div>
                    </div>
                  </div>
                  <div class="col-4 col-lg-3">
                      <div class="kpi">
                        <div class="icon">📉</div>
                        <div class="details">
                          <h2>Bounce Rate</h2>
                          <p>23.64% <span class="increase">+0.98%</span></p>
                        </div>
                      </div>
                  </div>
                  <div class="col-4 col-lg-3">
                      <div class="kpi">
                        <div class="icon">✅</div>
                        <div class="details">
                          <h2>Goal Conversion Rate</h2>
                          <p>78% <span class="increase">+3.89%</span></p>
                        </div>
                      </div>
                  </div>
                  <div class="col-4 col-lg-3">
                    <div class="kpi">
                      <div class="icon">⏱️</div>
                      <div class="details">
                        <h2>Session Duration</h2>
                        <p>00:25:30 <span class="increase">+1.45%</span></p>
                      </div>
                    </div>
                  </div>
                </div>          
            </div>
            <div class="row">
              <div class="col-12 col-lg-6">
                <div class="top-pages">
                <h2>Top Pages</h2>
                <table>
                  <thead>
                    <tr>
                      <th>Page Path</th>
                      <th>Page Views</th>
                      <th>Sessions</th>
                      <th>New Users</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>/home</td>
                      <td>10,653</td>
                      <td>7,456</td>
                      <td>2,456</td>
                    </tr>
                    <tr>
                      <td>/shop</td>
                      <td>10,273</td>
                      <td>7,364</td>
                      <td>2,164</td>
                    </tr>
                    <tr>
                      <td>/catalog</td>
                      <td>9,367</td>
                      <td>6,853</td>
                      <td>1,953</td>
                    </tr>
                    <tr>
                      <td>/about</td>
                      <td>9,157</td>
                      <td>6,273</td>
                      <td>1,573</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              </div>
              <div class="col-12 col-lg-6">
                  <div class="rates-chart">
                  <h2>Bounce Rate vs. Exit Rate</h2>
                  <canvas id="ratesChart"></canvas>
                </div>
              </div>
            </div>                          
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
             <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
            <script type="text/javascript">              
              $(document).ready(function(){            
                const ctx = document.getElementById('ratesChart').getContext('2d');
const ratesChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['01', '02', '03', '04', '05', '06', '07', '08', '09'],
    datasets: [
      {
        label: 'Bounce Rate',
        data: [65, 59, 80, 81, 56, 55, 40, 45, 60],
        borderColor: 'blue',
        fill: false
      },
      {
        label: 'Exit Rate',
        data: [28, 48, 40, 19, 86, 27, 90, 60, 75],
        borderColor: 'lightblue',
        fill: false
      }
    ]
  },
  options: {
    responsive: true,
    scales: {
      x: {
        beginAtZero: true
      },
      y: {
        beginAtZero: true
      }
    }
  }
});

              })
            </script>            
      <?php include('View/includes/foot.php'); ?>