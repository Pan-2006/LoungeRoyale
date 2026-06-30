<?php
// File: admin/sales_summary.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$monthly = mysqli_query($conn, "
SELECT YEAR(sale_date) AS year, MONTHNAME(sale_date) AS month,
       SUM(total_amount) AS monthly_sales
FROM sales
GROUP BY YEAR(sale_date), MONTH(sale_date)
ORDER BY YEAR(sale_date) DESC, MONTH(sale_date) DESC
");

$yearly = mysqli_query($conn, "
SELECT YEAR(sale_date) AS year, SUM(total_amount) AS yearly_sales
FROM sales
GROUP BY YEAR(sale_date)
ORDER BY YEAR(sale_date) DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Summary — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <h1 class="section-title">SALES SUMMARY</h1>

  <h2 style="color:var(--gold-light);font-family:'Cinzel',serif;font-size:1.1rem;margin-bottom:14px;">Monthly Sales</h2>

  <div class="admin-table-wrap" style="margin-bottom:36px;">
    <table class="admin-table">
      <thead>
        <tr><th>MONTH</th><th>YEAR</th><th>TOTAL SALES</th></tr>
      </thead>
      <tbody>
        <?php if($monthly && mysqli_num_rows($monthly) > 0):
          while($row = mysqli_fetch_assoc($monthly)): ?>
        <tr>
          <td><?php echo $row['month']; ?></td>
          <td><?php echo $row['year']; ?></td>
          <td>₱<?php echo number_format($row['monthly_sales'],2); ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="3" style="text-align:center;padding:24px;color:#555;">No data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h2 style="color:var(--gold-light);font-family:'Cinzel',serif;font-size:1.1rem;margin-bottom:14px;">Yearly Sales</h2>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>YEAR</th><th>TOTAL SALES</th></tr>
      </thead>
      <tbody>
        <?php if($yearly && mysqli_num_rows($yearly) > 0):
          while($row = mysqli_fetch_assoc($yearly)): ?>
        <tr>
          <td><?php echo $row['year']; ?></td>
          <td>₱<?php echo number_format($row['yearly_sales'],2); ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="2" style="text-align:center;padding:24px;color:#555;">No data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="margin-top:24px;">
    <a href="sales.php" class="action-btn btn-gold" style="padding:10px 22px;">Back to Sales Report</a>
    <a href="Dashboard.php" class="action-btn btn-view" style="padding:10px 22px;margin-left:10px;">Dashboard</a>
  </div>

</div>

</body>
</html>
