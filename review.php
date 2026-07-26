<?php
// ============================================
// REVIEW LETTER PAGE (Staff/Dekan)
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole(['staff', 'dekan']);

$user = getCurrentUser();
$filter = $_GET['filter'] ?? 'all';

// Build filters based on role
$filters = [];
if ($user['role'] === 'staff') {
    if ($filter === 'pending') {
        $filters['for_staff'] = true;
    }
} elseif ($user['role'] === 'dekan') {
    if ($filter === 'dekan') {
        $filters['for_dekan'] = true;
    }
}

$submissions = getSubmissions($filters, 100);
$unreadCount = getUnreadNotificationsCount($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Surat - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📋 Surat Kampus</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">🏠 Dashboard</a>
                <a href="review.php?filter=pending" class="nav-item <?= $filter === 'pending' ? 'active' : '' ?>">📋 Review Surat</a>
                <a href="notifications.php" class="nav-item">
                    🔔 Notifikasi
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-danger"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role badge <?= getRoleBadgeClass($user['role']) ?>"><?= getRoleLabel($user['role']) ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>📋 Review Pengajuan Surat</h1>
                <p>Tinjau dan proses pengajuan surat dari mahasiswa</p>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="review.php?filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">Semua</a>
                <a href="review.php?filter=pending" class="tab <?= $filter === 'pending' ? 'active' : '' ?>">Menunggu Review</a>
            </div>
            
            <div class="card">
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <p>Tidak ada pengajuan surat yang perlu direview.</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pengirim</th>
                                <th>Jenis Surat</th>
                                <th>Subjek</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td>#<?= $sub['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($sub['user_name']) ?></strong>
                                        <br><small><?= $sub['nim'] ?? $sub['user_email'] ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($sub['letter_type_name']) ?></td>
                                    <td><?= htmlspecialchars($sub['subject']) ?></td>
                                    <td>
                                        <span class="badge <?= getStatusBadgeClass($sub['status']) ?>">
                                            <?= getStatusLabel($sub['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($sub['created_at']) ?></td>
                                    <td>
                                        <a href="detail.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-primary">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
