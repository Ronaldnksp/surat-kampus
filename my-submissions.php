<?php
// ============================================
// MY SUBMISSIONS PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('mahasiswa');

$user = getCurrentUser();
$submissions = getSubmissions(['user_id' => $user['id']], 50);
$unreadCount = getUnreadNotificationsCount($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Saya - <?= APP_NAME ?></title>
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
                <a href="submit.php" class="nav-item">📝 Ajukan Surat</a>
                <a href="my-submissions.php" class="nav-item active">📄 Pengajuan Saya</a>
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
                    <span class="user-role badge badge-info">Mahasiswa</span>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">Keluar</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1>📄 Pengajuan Saya</h1>
                <a href="submit.php" class="btn btn-primary">+ Ajukan Baru</a>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <p>Belum ada pengajuan surat.</p>
                        <a href="submit.php" class="btn btn-primary">Ajukan Surat Sekarang</a>
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
                            <?php foreach ($submissions as $sub): ?>
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
