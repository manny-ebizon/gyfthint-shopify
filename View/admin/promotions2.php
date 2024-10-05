<!-- header -->
<?php include('View/includes/head.php'); ?>
<!-- sidebar aside -->
<?php include('View/includes/sidebar-nav.php');?>
<style type="text/css">
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f4f4f4;
}

.container {
  width: 90%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.metrics {
  display: flex;
  justify-content: space-around;
  margin-bottom: 20px;
}

.metric {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  width: 30%;
}

.metric h2 {
  margin: 0 0 10px 0;
}

.metric p {
  margin: 0;
  font-size: 24px;
  color: #333;
}

.product-table {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.product-table table {
  width: 100%;
  border-collapse: collapse;
}

.product-table th,
.product-table td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}

.product-table th {
  background-color: #f7f7f7;
}

.promote-btn {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.promote-btn:hover {
  background-color: #218838;
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
              <div class="metrics">
      <div class="metric">
        <h2>Total Products</h2>
        <p>50</p>
      </div>
      <div class="metric">
        <h2>Products on Promotion</h2>
        <p>15</p>
      </div>
      <div class="metric">
        <h2>Products Low in Stock</h2>
        <p>5</p>
      </div>
    </div>
    <div class="product-table">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Low Stock Metric</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Product 1</td>
            <td>10</td>
            <td>$20</td>
            <td>Low</td>
            <td><button class="promote-btn">PROMOTE</button></td>
          </tr>
          <tr>
            <td>Product 2</td>
            <td>50</td>
            <td>$30</td>
            <td>Normal</td>
            <td><button class="promote-btn">PROMOTE</button></td>
          </tr>
          <tr>
            <td>Product 3</td>
            <td>5</td>
            <td>$25</td>
            <td>Low</td>
            <td><button class="promote-btn">PROMOTE</button></td>
          </tr>
          <!-- Add more rows as needed -->
        </tbody>
      </table>
    </div>
            </div>
            <!-- / Content -->

            <?php include('View/includes/foot-script.php'); ?>    
            <script type="text/javascript">              
              $(document).ready(function(){            
                
              })
            </script>            
      <?php include('View/includes/foot.php'); ?>