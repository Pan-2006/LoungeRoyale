<?php
// File: AdminInterface/navbar.php
// Include this at the top of every admin page (after session_start + auth check)
// Usage: include "navbar.php";
?>
<nav class="admin-navbar">
  <a href="Dashboard.php" class="nav-logo">
    <img src="../assets/main logo.png" alt="The Lounge Royale">
  </a>
  <div class="nav-avatar">
    <img src="../assets/avatar_placeholder.png"
         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 40 40%22><circle cx=%2220%22 cy=%2220%22 r=%2220%22 fill=%22%23555%22/><circle cx=%2220%22 cy=%2216%22 r=%228%22 fill=%22%23888%22/><ellipse cx=%2220%22 cy=%2238%22 rx=%2213%22 ry=%229%22 fill=%22%23888%22/></svg>'"
         alt="Admin">
  </div>
  <ul class="nav-links" id="navLinks">
    <li><a href="home.php">HOME</a></li>
    <li><a href="about.php">ABOUT</a></li>
    <li><a href="services.php">SERVICES</a></li>
    <li><a href="Dashboard.php">ADMIN DASHBOARD</a></li>
    <li><a href="appointments.php">MANAGE BOOKINGS</a></li>
    <li><a href="customers.php">MANAGE CLIENTS</a></li>
  </ul>
  <div class="nav-logout">
    <a href="../logout.php">LOGOUT</a>
  </div>
  <button class="hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>
<script>
(function(){
  var btn = document.getElementById('hamburger');
  var nav = document.getElementById('navLinks');
  if(btn && nav){
    btn.addEventListener('click', function(){
      nav.classList.toggle('open');
    });
  }
})();
</script>
