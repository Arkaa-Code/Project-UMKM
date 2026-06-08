<?php
$current_page = $current_page ?? '';

function navActive(string $page, string $current): string {
    return $page === $current ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top" id="mainNavbar">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand" href="index.php">
      <img src="../assets/images/equipment/rzlogo.png?v=4" alt="RZKY Equipment Services logo" class="brand-logo">
      RZKY<span>Equipment Services</span>
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarMain"
            aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item">
          <a class="nav-link <?= navActive('home', $current_page) ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('catalog', $current_page) ?>" href="display.php">Catalog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('portfolio', $current_page) ?>" href="portfolio.php">Portfolio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('about', $current_page) ?>" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= navActive('contact', $current_page) ?>" href="contact.php">Contact</a>
        </li>
        <li class="nav-item ms-lg-3">
          <a class="btn btn-nav-cta" href="reservation.php">
            <i class="bi bi-calendar-check-fill me-1"></i>Book Now
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>
