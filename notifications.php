<?php
// ============================================
// NOTIFICATIONS PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user = getCurrentUser();

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    markAllNotificationsRead($user['id']);
    header('Location: notifications.php');
    exit;
}

$notifications = getNotifications($user['id'], 50);
$unreadCount = getUnreadNotificationsCount($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - <?= APP_NAME ?></title>
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
                <?php if ($user['role'] === 'mahasiswa'): ?>
                    <a href="submit.php" class="nav-item">📝 Ajukan Surat</a>
                    <a href="my-submissions.php" class="nav-item">📄 Pengajuan Saya</a>
                <?php else: ?>
                    <a href="review.php" class="nav-item">📋 Review Surat</a>
                <?php endif; ?>
                <a href="notifications.php" class="nav-item active">🔔 Notifikasi</a>
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
                <h1>🔔 Notifikasi</h1>
                <?php if ($unreadCount > 0): ?>
                    <a href="notifications.php?mark_read=1" class="btn btn-outline btn-sm">Tandai Semua Dibaca</a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <p>Tidak ada notifikasi</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notif): 
                            $data = json_decode($notif['data'], true);
                            $isRead = !empty($notif['read_at']);
                        ?>
                            <div class="notification-item <?= $isRead ? '' : 'unread' ?>">
                                <div class="notification-icon">
                                    <?php
                                    $type = $data['type'] ?? '';
                                    echo match($type) {
                                        'letter_submitted' => '📝',
                                        'letter_approved' => '✅',
                                        'letter_rejected' => '❌',
                                        default => '🔔',
                                    };
                                    ?>
                                </div>
                                <div class="notification-content">
                                    <p><?= htmlspecialchars($data['message'] ?? 'Notifikasi baru') ?></p>
                                    <small class="text-muted"><?= timeAgo($notif['created_at']) ?></small>
                                </div>
                                <?php if (!empty($data['submission_id'])): ?>
                                    <a href="detail.php?id=<?= $data['submission_id'] ?>" class="btn btn-sm btn-outline">Lihat</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
