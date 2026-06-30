<?php
require_once "admin_layout.php";
require_admin();

$edit = null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int)($_POST["staff_id"] ?? 0);

    if ($action === "save") {
        $name = trim($_POST["staff_name"] ?? "");
        $spec = trim($_POST["specialization"] ?? "");

        if ($id > 0) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE staff SET staff_name=?, specialization=? WHERE staff_id=?"
            );
            mysqli_stmt_bind_param($stmt, "ssi", $name, $spec, $id);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO staff (staff_name, specialization) VALUES (?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ss", $name, $spec);
        }

        mysqli_stmt_execute($stmt);

        header("Location: staffs.php");
        exit();
    }

    if ($action === "delete") {
        $used = (int)one_value(
            $conn,
            "SELECT COUNT(*) FROM appointments WHERE staff_id=?",
            "i",
            [$id]
        );

        if ($used === 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM staff WHERE staff_id=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        } else {
            $message = "This staff member has appointment history and cannot be deleted safely.";
        }
    }
}

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = mysqli_prepare($conn, "SELECT * FROM staff WHERE staff_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$search = trim($_GET["search"] ?? "");

$sql = "
    SELECT s.*, COUNT(a.appointment_id) bookings
    FROM staff s
    LEFT JOIN appointments a ON s.staff_id = a.staff_id
";

$types = "";
$params = [];

if ($search !== "") {
    $sql .= " WHERE s.staff_name LIKE ? OR s.specialization LIKE ?";
    $like = "%$search%";
    $types = "ss";
    $params = [$like, $like];
}

$sql .= " GROUP BY s.staff_id ORDER BY s.staff_name";

$stmt = mysqli_prepare($conn, $sql);

if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$staff = mysqli_stmt_get_result($stmt);

admin_header("Staffs", "Staffs");
?>

<?php if ($message): ?>
    <div class="notice"><?php echo h($message); ?></div>
<?php endif; ?>

<section class="split">
    <div class="panel">
        <div class="section-title">
            <h2>Staff Management</h2>
        </div>

        <form class="filters" method="get">
            <label class="field">
                <span>Search</span>
                <input name="search" value="<?php echo h($search); ?>" placeholder="Name or specialization">
            </label>

            <button class="btn" type="submit">Search</button>
            <a class="btn secondary" href="staffs.php">Clear</a>
        </form>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Status</th>
                    <th>Bookings</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($staff)): ?>
                    <tr>
                        <td><?php echo h($row["staff_name"]); ?></td>
                        <td><?php echo h($row["specialization"]); ?></td>
                        <td><span class="badge Completed">Active</span></td>
                        <td><?php echo h($row["bookings"]); ?></td>
                        <td class="actions">
                            <a class="btn small secondary" href="staffs.php?edit=<?php echo (int)$row["staff_id"]; ?>">
                                Edit
                            </a>

                            <form method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="staff_id" value="<?php echo (int)$row["staff_id"]; ?>">
                                <button class="btn small danger" data-confirm="Delete this staff record?" type="submit">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <aside class="panel">
        <h2><?php echo $edit ? "Edit Staff" : "Add Staff"; ?></h2>

        <form class="grid" method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="staff_id" value="<?php echo h($edit["staff_id"] ?? 0); ?>">

            <label class="field">
                <span>Name</span>
                <input name="staff_name" value="<?php echo h($edit["staff_name"] ?? ""); ?>" required>
            </label>

            <label class="field">
                <span>Specialization</span>
                <input name="specialization" value="<?php echo h($edit["specialization"] ?? "All Services"); ?>" required>
            </label>

            <button class="btn" type="submit">Save Staff</button>
        </form>
    </aside>
</section>

<?php admin_footer(); ?>
