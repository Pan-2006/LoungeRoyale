<?php
require_once __DIR__ . "/auth.php";


function admin_header(string $title, string $active = ""): void {
    global $conn;
    $admin = current_admin($conn);
    $nav = [
        "Dashboard" => "Dashboard.php",
        "Staffs" => "staffs.php",
        "Manage Bookings" => "appointments.php",
        "Manage Clients" => "customers.php",
        "Manage Services" => "services_admin.php",
        "Sales Report" => "sales.php",
        "Walk-In Sales" => "walkin_sale.php",
        "Profile" => "profile.php",
    ];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($title); ?> | The Lounge Royale</title>
    <link rel="stylesheet" href="admin_style.css">
    <script src="admin_script.js" defer></script>
</head>
<body class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a class="brand" href="Dashboard.php">
            <img src="<?php echo asset_path("main logo.png"); ?>" alt="The Lounge Royale">
        </a>
        <nav>
            <?php foreach ($nav as $label => $href): ?>
                <a class="<?php echo $active === $label ? "active" : ""; ?>" href="<?php echo h($href); ?>"><?php echo h($label); ?></a>
            <?php endforeach; ?>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <button class="menu-button" type="button" data-sidebar-toggle aria-label="Open menu">☰</button>
            <div>
                <p class="eyebrow">The Lounge Royale</p>
                <h1><?php echo h($title); ?></h1>
            </div>
            <a class="profile-chip" href="profile.php"><?php echo h($admin["name"] ?: "Admin"); ?></a>
        </header>
<?php
}


function admin_footer(): void {
?>
    </main>
</body>
</html>
<?php
}
?>
'@
