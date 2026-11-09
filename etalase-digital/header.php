<?php
// header.php
require_once __DIR__ . '/config.php';
$currentUser = current_user();
$currentRole = current_role();
$pageTitle = isset($pageTitle) ? $pageTitle . ' - Etalase Digital Stella' : 'Etalase Digital - SMK Telkom Lampung';
$activePage = $activePage ?? '';
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="Etalase Digital Karya Siswa SMK Telkom Lampung - Project UKK / LKPD Pemrograman Web & Hak Akses">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Header -->
<header class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">
    <img
        src="assets/images/logo-telkom.jpg"
        alt="Logo Telkom"
        class="logo-telkom"
    >

    <div>
        <span class="brand-sub">Etalase Digital</span>
    </div>
</a>

        <!-- Mobile Menu Button -->
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Navigation Links -->
        <nav>
            <ul class="nav-menu" id="navMenu">
                <li>
                    <a href="index.php" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">
                        <i class="fa-solid fa-compass"></i> Katalog Karya
                    </a>
                </li>
                <?php if ($currentUser): ?>
                    <li>
                        <a href="dashboard.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="project_form.php" class="nav-link <?= $activePage === 'add_project' ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Project
                        </a>
                    </li>
                    <?php if ($currentRole === 'super_admin' || $currentRole === 'admin'): ?>
                        <li>
                            <a href="users.php" class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
                                <i class="fa-solid fa-users-gear"></i> Kelola <?= $currentRole === 'admin' ? 'Siswa' : 'Pengguna' ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Right Side: User Menu / Login -->
        <div class="nav-actions">
            <?php if ($currentUser): ?>
                <div class="nav-user">
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser['nama'], 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= e($currentUser['nama']) ?></span>
                        <div style="font-size: 0.72rem;">
                            <?= role_badge($currentRole) ?>
                        </div>
                    </div>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm" title="Keluar dari Akun">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Akun
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Main Wrapper -->
<main class="main-content">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'danger' ? 'fa-circle-xmark' : 'fa-circle-info') ?> fa-lg"></i>
                <div><?= e($flash['message']) ?></div>
            </div>
        <?php endif; ?>
