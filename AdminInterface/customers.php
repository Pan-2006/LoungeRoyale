<?php
// File: admin/customers.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_safe = mysqli_real_escape_string($conn, $search);

$where = $search_safe !== ''
    ? "WHERE customers.name LIKE '%$search_safe%' OR users.email LIKE '%$search_safe%'"
    : '';

$sql = "
SELECT
  customers.customer_id,
  customers.name,
  customers.phone,
  users.email,
  COUNT(appointments.appointment_id) AS total_appointments
FROM customers
JOIN users ON customers.user_id = users.user_id
LEFT JOIN appointments ON customers.customer_id = appointments.customer_id
$where
GROUP BY customers.customer_id
ORDER BY customers.name ASC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Clients — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <h1 class="section-title">CLIENT DATABASE</h1>

  <!-- Search -->
  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search client name or email…"
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="filter-btn">Search</button>
    <?php if($search): ?>
      <a href="customers.php" style="color:var(--text-muted);font-size:.82rem;text-decoration:none;">Clear</a>
    <?php endif; ?>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>NAME</th>
          <th>EMAIL</th>
          <th>PHONE</th>
          <th>NO. OF APPOINTMENTS</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td>
            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"
               style="color:var(--black);text-decoration:underline;">
              <?php echo htmlspecialchars($row['email']); ?>
            </a>
          </td>
          <td><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></td>
          <td>
            <a href="customer_history.php?id=<?php echo $row['customer_id']; ?>"
               style="color:var(--black);font-weight:700;text-decoration:underline;">
              <?php echo $row['total_appointments']; ?>
            </a>
          </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr>
          <td colspan="4" style="text-align:center;padding:32px;color:#555;">
            No clients found.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
