<?php
// File: admin/services_admin.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$msg = '';

// ── Add Service ──
if(isset($_POST['add_service'])){
    $sn = mysqli_real_escape_string($conn, trim($_POST['service_name']));
    $cat = mysqli_real_escape_string($conn, trim($_POST['category']));
    $pr  = (float)$_POST['price'];
    $dur = (int)$_POST['duration'];
    mysqli_query($conn, "INSERT INTO services (service_name,category,price,duration) VALUES ('$sn','$cat','$pr','$dur')");
    $msg = 'Service added successfully.';
}

// ── Edit Service ──
if(isset($_POST['edit_service'])){
    $id  = (int)$_POST['service_id'];
    $sn  = mysqli_real_escape_string($conn, trim($_POST['service_name']));
    $cat = mysqli_real_escape_string($conn, trim($_POST['category']));
    $pr  = (float)$_POST['price'];
    $dur = (int)$_POST['duration'];
    mysqli_query($conn, "UPDATE services SET service_name='$sn',category='$cat',price='$pr',duration='$dur' WHERE service_id='$id'");
    $msg = 'Service updated successfully.';
}

// ── Delete Service ──
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM services WHERE service_id='$id'");
    header("Location: services_admin.php?msg=deleted");
    exit();
}

// ── Fetch for edit modal ──
$editRow = null;
if(isset($_GET['edit'])){
    $eid = (int)$_GET['edit'];
    $editRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM services WHERE service_id='$eid'"));
}

// ── Filter / Search ──
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterCat = isset($_GET['category']) ? trim($_GET['category']) : '';
$s_safe = mysqli_real_escape_string($conn, $search);
$c_safe = mysqli_real_escape_string($conn, $filterCat);

$where = [];
if($s_safe) $where[] = "(service_name LIKE '%$s_safe%')";
if($c_safe) $where[] = "category='$c_safe'";
$wc = $where ? 'WHERE '.implode(' AND ',$where) : '';

$result = mysqli_query($conn, "SELECT * FROM services $wc ORDER BY category, service_name");

$categories = ['Hand Services','Foot Services','Royale Deluxe','Nail Extension','Kiddie Services','Other Services','Wax Services'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Services — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:24px;">
    <h1 class="section-title" style="margin-bottom:0;">MANAGE SERVICES</h1>
    <button class="action-btn btn-gold" onclick="document.getElementById('addModal').classList.add('open')"
            style="padding:10px 22px;">
      + Add Service
    </button>
  </div>

  <?php if($msg || isset($_GET['msg'])): ?>
  <div class="alert alert-success">
    <?php echo $msg ?: ($GET['msg']=='deleted' ? 'Service deleted.' : ''); ?>
  </div>
  <?php endif; ?>

  <!-- Filter Bar -->
  <form method="GET" class="filter-bar">
    <select name="category" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php foreach($categories as $cat): ?>
      <option value="<?php echo $cat; ?>" <?php echo $filterCat==$cat?'selected':''; ?>>
        <?php echo $cat; ?>
      </option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="search" placeholder="Search service name…"
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
    <a href="services_admin.php" style="color:var(--text-muted);font-size:.82rem;text-decoration:none;">Clear</a>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>SERVICE NAME</th>
          <th>CATEGORY</th>
          <th>PRICE</th>
          <th>DURATION</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && mysqli_num_rows($result) > 0):
          $i = 1;
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?php echo $i++; ?></td>
          <td style="text-align:left;"><?php echo htmlspecialchars($row['service_name']); ?></td>
          <td><?php echo htmlspecialchars($row['category']); ?></td>
          <td>₱<?php echo number_format($row['price'],2); ?></td>
          <td><?php echo $row['duration']; ?> mins</td>
          <td>
            <a href="services_admin.php?edit=<?php echo $row['service_id']; ?>"
               class="action-btn btn-edit">Edit</a>
            <a href="services_admin.php?delete=<?php echo $row['service_id']; ?>"
               class="action-btn btn-delete"
               onclick="return confirm('Delete this service?')">Delete</a>
          </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr>
          <td colspan="6" style="text-align:center;padding:32px;color:#555;">No services found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Add Service Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">&times;</button>
    <h2>Add New Service</h2>
    <form method="POST">
      <label>Service Name *</label>
      <input type="text" name="service_name" required>

      <label>Category *</label>
      <select name="category" required>
        <?php foreach($categories as $cat): ?>
        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
        <?php endforeach; ?>
      </select>

      <label>Price (₱) *</label>
      <input type="number" name="price" step="0.01" min="0" required>

      <label>Duration (minutes) *</label>
      <input type="number" name="duration" min="1" required>

      <div class="modal-actions">
        <button type="button" class="btn-cancel-modal"
                onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_service" class="btn-save">Add Service</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Service Modal (auto-opens when ?edit=X) -->
<?php if($editRow): ?>
<div class="modal-overlay open" id="editModal">
  <div class="modal-box">
    <button class="modal-close" onclick="window.location.href='services_admin.php'">&times;</button>
    <h2>Edit Service</h2>
    <form method="POST">
      <input type="hidden" name="service_id" value="<?php echo $editRow['service_id']; ?>">

      <label>Service Name *</label>
      <input type="text" name="service_name" value="<?php echo htmlspecialchars($editRow['service_name']); ?>" required>

      <label>Category *</label>
      <select name="category" required>
        <?php foreach($categories as $cat): ?>
        <option value="<?php echo $cat; ?>" <?php echo $editRow['category']==$cat?'selected':''; ?>>
          <?php echo $cat; ?>
        </option>
        <?php endforeach; ?>
      </select>

      <label>Price (₱) *</label>
      <input type="number" name="price" step="0.01" min="0" value="<?php echo $editRow['price']; ?>" required>

      <label>Duration (minutes) *</label>
      <input type="number" name="duration" min="1" value="<?php echo $editRow['duration']; ?>" required>

      <div class="modal-actions">
        <button type="button" class="btn-cancel-modal"
                onclick="window.location.href='services_admin.php'">Cancel</button>
        <button type="submit" name="edit_service" class="btn-save">Update Service</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>
