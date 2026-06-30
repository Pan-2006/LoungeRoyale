<?php
// File: admin/sales.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$filterDate  = isset($_GET['filter_date'])  ? trim($_GET['filter_date'])  : '';
$filterMonth = isset($_GET['filter_month']) ? trim($_GET['filter_month']) : '';
$filterYear  = isset($_GET['filter_year'])  ? trim($_GET['filter_year'])  : '';

$where = [];
if($filterDate)  $where[] = "sales.sale_date = '".mysqli_real_escape_string($conn,$filterDate)."'";
if($filterMonth) $where[] = "MONTH(sales.sale_date) = '".mysqli_real_escape_string($conn,$filterMonth)."'";
if($filterYear)  $where[] = "YEAR(sales.sale_date)  = '".mysqli_real_escape_string($conn,$filterYear)."'";
$wc = $where ? 'WHERE '.implode(' AND ',$where) : '';

$sql = "
SELECT
  sales.sale_id,
  COALESCE(customers.name, 'Walk-in Customer') AS client_name,
  COALESCE(services.service_name, 'Walk-in Sale') AS service_name,
  sales.total_amount,
  sales.sale_date
FROM sales
LEFT JOIN appointments ON sales.appointment_id = appointments.appointment_id
LEFT JOIN customers    ON appointments.customer_id = customers.customer_id
LEFT JOIN services     ON appointments.service_id  = services.service_id
$wc
ORDER BY sales.sale_date DESC
";

$result = mysqli_query($conn, $sql);

$totalRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(total_amount),0) AS total FROM sales " . ($wc ?: '')
));
$grandTotal = $totalRow['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Report — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <h1 class="section-title">SALES REPORT</h1>

  <!-- Filters -->
  <form method="GET" class="filter-bar" style="margin-bottom:20px;">
    <label>Date:</label>
    <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filterDate); ?>">
    <label>Month:</label>
    <select name="filter_month">
      <option value="">All</option>
      <?php for($m=1;$m<=12;$m++): $mn=str_pad($m,2,'0',STR_PAD_LEFT); ?>
      <option value="<?php echo $mn; ?>" <?php echo $filterMonth==$mn?'selected':''; ?>>
        <?php echo date("F",mktime(0,0,0,$m,1)); ?>
      </option>
      <?php endfor; ?>
    </select>
    <label>Year:</label>
    <select name="filter_year">
      <option value="">All</option>
      <?php for($y=2024;$y<=2035;$y++): ?>
      <option value="<?php echo $y; ?>" <?php echo $filterYear==$y?'selected':''; ?>><?php echo $y; ?></option>
      <?php endfor; ?>
    </select>
    <button type="submit">Filter</button>
    <a href="sales.php" style="color:var(--text-muted);font-size:.82rem;text-decoration:none;">Clear</a>
  </form>

  <div style="margin-bottom:18px;">
    <span style="color:var(--gold-light);font-family:'Cinzel',serif;font-size:1.1rem;font-weight:700;">
      Total Sales: ₱<?php echo number_format($grandTotal,2); ?>
    </span>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>SALE #</th>
          <th>CLIENT</th>
          <th>SERVICE</th>
          <th>AMOUNT</th>
          <th>DATE</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?php echo $row['sale_id']; ?></td>
          <td><?php echo htmlspecialchars($row['client_name']); ?></td>
          <td><?php echo htmlspecialchars($row['service_name']); ?></td>
          <td>₱<?php echo number_format($row['total_amount'],2); ?></td>
          <td><span class="date-pill"><?php echo date('n/j/Y',strtotime($row['sale_date'])); ?></span></td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr>
          <td colspan="5" style="text-align:center;padding:32px;color:#555;">No sales records found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div style="margin-top:28px;">
    <a href="sales_summary.php" class="action-btn btn-gold" style="padding:10px 22px;">Monthly / Yearly Summary</a>
    <a href="walkin_sale.php" class="action-btn btn-view" style="padding:10px 22px;margin-left:10px;">Walk-In Sale</a>
  </div>

</div>

</body>
</html>
