<?php
// File: admin/walkin_sale.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$msg = '';

if(isset($_POST['record_sale'])){
    $service_id = (int)$_POST['service_id'];
    $custom_amount = isset($_POST['custom_amount']) && $_POST['custom_amount'] !== ''
        ? (float)$_POST['custom_amount']
        : null;

    if($service_id){
        $priceRow = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT price FROM services WHERE service_id='$service_id'"
        ));
        $amount = $custom_amount ?? ($priceRow['price'] ?? 0);
    } else {
        $amount = $custom_amount ?? 0;
    }

    $sale_date = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO sales (appointment_id, total_amount, sale_date) VALUES (NULL, '$amount', '$sale_date')");
    $msg = 'Walk-in sale of ₱'.number_format($amount,2).' recorded successfully!';
}

$services = mysqli_query($conn, "SELECT * FROM services ORDER BY category, service_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Walk-In Sale — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <h1 class="section-title">WALK-IN SALE</h1>

  <?php if($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <div style="background:var(--dark-card);border-radius:var(--radius-lg);padding:36px 40px;max-width:560px;">

    <form method="POST">

      <label style="display:block;color:var(--text-light);font-size:.88rem;font-weight:600;margin-bottom:8px;">
        Service
      </label>
      <select name="service_id" id="serviceSelect"
              style="width:100%;background:var(--dark);border:1px solid var(--gold);color:var(--white);padding:12px 16px;border-radius:8px;font-size:.9rem;font-family:'Raleway',sans-serif;outline:none;margin-bottom:20px;">
        <option value="">— Select a Service (optional) —</option>
        <?php
        $lastCat = '';
        while($svc = mysqli_fetch_assoc($services)):
          if($svc['category'] !== $lastCat){
            if($lastCat !== '') echo '</optgroup>';
            echo '<optgroup label="'.htmlspecialchars($svc['category']).'">';
            $lastCat = $svc['category'];
          }
        ?>
        <option value="<?php echo $svc['service_id']; ?>"
                data-price="<?php echo $svc['price']; ?>">
          <?php echo htmlspecialchars($svc['service_name']); ?> — ₱<?php echo number_format($svc['price'],2); ?>
        </option>
        <?php endwhile;
        if($lastCat !== '') echo '</optgroup>';
        ?>
      </select>

      <label style="display:block;color:var(--text-light);font-size:.88rem;font-weight:600;margin-bottom:8px;">
        Amount (₱) <span style="font-weight:400;color:var(--text-muted);">— override price or enter manual amount</span>
      </label>
      <input type="number" name="custom_amount" id="customAmount" step="0.01" min="0"
             placeholder="Auto-filled from service or type here"
             style="width:100%;background:var(--dark);border:1px solid var(--gold);color:var(--white);padding:12px 16px;border-radius:8px;font-size:.9rem;font-family:'Raleway',sans-serif;outline:none;margin-bottom:24px;">

      <button type="submit" name="record_sale" class="action-btn btn-gold"
              style="width:100%;padding:14px;font-size:.95rem;border-radius:8px;">
        Record Sale
      </button>

    </form>
  </div>

  <div style="margin-top:24px;">
    <a href="sales.php" class="action-btn btn-view" style="padding:10px 22px;">View Sales Report</a>
    <a href="Dashboard.php" class="action-btn btn-gold" style="padding:10px 22px;margin-left:10px;">Dashboard</a>
  </div>

</div>

<script>
(function(){
  var sel = document.getElementById('serviceSelect');
  var amt = document.getElementById('customAmount');
  if(sel && amt){
    sel.addEventListener('change', function(){
      var opt = this.options[this.selectedIndex];
      var price = opt.getAttribute('data-price');
      if(price) amt.value = price;
      else amt.value = '';
    });
  }
})();
</script>

</body>
</html>
