<?php
// ============================================
// DASHBOARD PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user = getCurrentUser();
$stats = getStats($user['id'], $user['role']);
$recentSubmissions = getSubmissions(['user_id' => $user['id']], 5);

// For staff/dekan, show all submissions
if ($user['role'] === 'staff') {
    $pendingCount = getSubmissions(['for_staff' => true], 100);
    $stats['pending_review'] = count($pendingCount);
}
if ($user['role'] === 'dekan') {
    $pendingDekan = getSubmissions(['for_dekan' => true], 100);
    $stats['pending_dekan'] = count($pendingDekan);
}

$unreadCount = getUnreadNotificationsCount($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
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
                <a href="dashboard.php" class="nav-item active">🏠 Dashboard</a>
                <?php if ($user['role'] === 'mahasiswa'): ?>
                    <a href="submit.php" class="nav-item">📝 Ajukan Surat</a>
                    <a href="my-submissions.php" class="nav-item">📄 Pengajuan Saya</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'staff'): ?>
                    <a href="review.php?filter=pending" class="nav-item">📋 Review Surat</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'dekan'): ?>
                    <a href="review.php?filter=dekan" class="nav-item">📋 Persetujuan Dekan</a>
                <?php endif; ?>
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
                    <span class="user-role badge <?= getRoleBadgeClass($user['role']) ?>"><?= $user['role_label'] ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>Dashboard</h1>
                <p>Selamat datang, <?= htmlspecialchars($user['name']) ?>!</p>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Pengajuan</div>
                </div>
                
                <?php if ($user['role'] === 'staff'): ?>
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value"><?= $stats['pending_review'] ?? 0 ?></div>
                        <div class="stat-label">Menunggu Review</div>
                    </div>
                <?php elseif ($user['role'] === 'dekan'): ?>
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value"><?= $stats['pending_dekan'] ?? 0 ?></div>
                        <div class="stat-label">Menunggu Persetujuan</div>
                    </div>
                <?php else: ?>
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                <?php endif; ?>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value"><?= $stats['approved'] ?? 0 ?></div>
                    <div class="stat-label">Disetujui</div>
                </div>
                
                <div class="stat-card stat-danger">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value"><?= $stats['rejected'] ?? 0 ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>
            
            <!-- Recent Submissions -->
            <div class="card">
                <div class="card-header">
                    <h2>Pengajuan Terbaru</h2>
                    <?php if ($user['role'] === 'mahasiswa'): ?>
                        <a href="submit.php" class="btn btn-primary btn-sm">+ Ajukan Baru</a>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($recentSubmissions)): ?>
                    <div class="empty-state">
                        <p>Belum ada pengajuan surat.</p>
                        <?php if ($user['role'] === 'mahasiswa'): ?>
                            <a href="submit.php" class="btn btn-primary">Ajukan Surat Sekarang</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jenis Surat</th>
                                <th>Subjek</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSubmissions as $sub): ?>
                                <tr>
                                    <td>#<?= $sub['id'] ?></td>
                                    <td><?= htmlspecialchars($sub['letter_type_name']) ?></td>
                                    <td><?= htmlspecialchars($sub['subject']) ?></td>
                                    <td>
                                        <span class="badge <?= getStatusBadgeClass($sub['status']) ?>">
                                            <?= getStatusLabel($sub['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($sub['created_at']) ?></td>
                                    <td>
                                        <a href="detail.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline">Detail</a>
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
