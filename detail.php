<?php
// ============================================
// DETAIL LETTER PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$user = getCurrentUser();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: dashboard.php');
    exit;
}

$submission = getSubmission($id);
if (!$submission) {
    setFlash('danger', 'Pengajuan tidak ditemukan');
    header('Location: dashboard.php');
    exit;
}

// Check access
$canView = false;
$canApprove = false;

if ($user['role'] === 'mahasiswa' && $submission['user_id'] == $user['id']) {
    $canView = true;
} elseif ($user['role'] === 'staff') {
    $canView = true;
    if ($submission['status'] === 'pending') {
        $canApprove = true;
    }
} elseif ($user['role'] === 'dekan') {
    $canView = true;
    if ($submission['status'] === 'staff_reviewed' && $submission['requires_dekan_approval']) {
        $canApprove = true;
    }
}

if (!$canView) {
    setFlash('danger', 'Anda tidak memiliki akses');
    header('Location: dashboard.php');
    exit;
}

$attachments = getAttachments($id);
$activityLogs = getActivityLogs($id);

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canApprove) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission';
    } else {
        $action = $_POST['action'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        
        $db = getDB();
        
        if ($action === 'approve') {
            if ($user['role'] === 'staff') {
                if ($submission['requires_dekan_approval']) {
                    $newStatus = 'staff_reviewed';
                    $logAction = 'staff_reviewed';
                    $logDesc = 'Surat direview oleh staff, menunggu persetujuan dekan';
                    
                    // Notify dekan
                    $dekanUsers = $db->query("SELECT id FROM users WHERE role = 'dekan' AND is_active = 1")->fetchAll();
                    foreach ($dekanUsers as $dekan) {
                        createNotification($dekan['id'], 'letter_submitted',
                            'Surat perlu persetujuan dekan: ' . $submission['subject'],
                            ['submission_id' => $id]);
                    }
                } else {
                    $newStatus = 'approved';
                    $logAction = 'staff_approved';
                    $logDesc = 'Surat disetujui oleh staff';
                }
                
                $stmt = $db->prepare("UPDATE letter_submissions SET status = ?, approved_by = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $user['id'], $id]);
                
            } elseif ($user['role'] === 'dekan') {
                $newStatus = 'approved';
                $logAction = 'dekan_approved';
                $logDesc = 'Surat disetujui oleh dekan';
                
                $stmt = $db->prepare("UPDATE letter_submissions SET status = ?, dekan_approved_by = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $user['id'], $id]);
            }
            
            // Notify user
            createNotification($submission['user_id'], 'letter_approved',
                'Surat kamu telah disetujui: ' . $submission['subject'],
                ['submission_id' => $id]);
            
            logActivity($id, $user['id'], $logAction, $logDesc);
            setFlash('success', 'Surat berhasil disetujui');
            
        } elseif ($action === 'reject') {
            if (empty($reason)) {
                $error = 'Alasan penolakan harus diisi';
            } else {
                $stmt = $db->prepare("UPDATE letter_submissions SET status = 'rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$reason, $id]);
                
                logActivity($id, $user['id'], 
                    $user['role'] === 'staff' ? 'staff_rejected' : 'dekan_rejected',
                    'Surat ditolak: ' . $reason);
                
                createNotification($submission['user_id'], 'letter_rejected',
                    'Surat kamu ditolak: ' . $submission['subject'],
                    ['submission_id' => $id, 'reason' => $reason]);
                
                setFlash('danger', 'Surat berhasil ditolak');
            }
        }
        
        header('Location: detail.php?id=' . $id);
        exit;
    }
}

generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Surat #<?= $id ?> - <?= APP_NAME ?></title>
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
                <a href="notifications.php" class="nav-item">🔔 Notifikasi</a>
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
                <h1>Detail Pengajuan Surat #<?= $id ?></h1>
                <a href="<?= $user['role'] === 'mahasiswa' ? 'my-submissions.php' : 'review.php' ?>" class="btn btn-outline">← Kembali</a>
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="detail-grid">
                <!-- Submission Info -->
                <div class="card">
                    <div class="card-header">
                        <h2>Informasi Pengajuan</h2>
                        <span class="badge <?= getStatusBadgeClass($submission['status']) ?> badge-lg">
                            <?= getStatusLabel($submission['status']) ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Jenis Surat</label>
                        <span><?= htmlspecialchars($submission['letter_type_name']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Subjek</label>
                        <span><?= htmlspecialchars($submission['subject']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Isi Pengajuan</label>
                        <div class="detail-content"><?= nl2br(htmlspecialchars($submission['body'])) ?></div>
                    </div>
                    
                    <div class="detail-row">
                        <label>Tanggal Pengajuan</label>
                        <span><?= formatDate($submission['created_at']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Batas Waktu</label>
                        <span><?= formatDate($submission['due_date'], 'd M Y') ?></span>
                    </div>
                    
                    <?php if ($submission['rejection_reason']): ?>
                        <div class="detail-row">
                            <label>Alasan Penolakan</label>
                            <span class="text-danger"><?= htmlspecialchars($submission['rejection_reason']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- User Info -->
                <div class="card">
                    <div class="card-header">
                        <h2>Informasi Pengirim</h2>
                    </div>
                    
                    <div class="detail-row">
                        <label>Nama</label>
                        <span><?= htmlspecialchars($submission['user_name']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>NIM</label>
                        <span><?= $submission['nim'] ?? '-' ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Email</label>
                        <span><?= htmlspecialchars($submission['user_email']) ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Telepon</label>
                        <span><?= $submission['user_phone'] ?? '-' ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <label>Jurusan</label>
                        <span><?= $submission['user_department'] ?? '-' ?></span>
                    </div>
                </div>
                
                <!-- Attachments -->
                <div class="card">
                    <div class="card-header">
                        <h2>Lampiran</h2>
                    </div>
                    
                    <?php if (empty($attachments)): ?>
                        <p class="text-muted">Tidak ada lampiran</p>
                    <?php else: ?>
                        <div class="attachments-list">
                            <?php foreach ($attachments as $att): ?>
                                <div class="attachment-item">
                                    <span class="attachment-icon">
                                        <?= $att['mime_type'] === 'application/pdf' ? '📄' : '🖼️' ?>
                                    </span>
                                    <div class="attachment-info">
                                        <span class="attachment-name"><?= htmlspecialchars($att['original_name']) ?></span>
                                        <span class="attachment-size"><?= round($att['size'] / 1024) ?> KB</span>
                                    </div>
                                    <a href="uploads/<?= $submission['id'] ?>/<?= $att['filename'] ?>" 
                                       class="btn btn-sm btn-outline" target="_blank">Download</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Approval Info -->
                <?php if ($submission['approver_name'] || $submission['dekan_approver_name']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Informasi Persetujuan</h2>
                        </div>
                        
                        <?php if ($submission['approver_name']): ?>
                            <div class="detail-row">
                                <label>Disetujui Staff</label>
                                <span><?= htmlspecialchars($submission['approver_name']) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($submission['dekan_approver_name']): ?>
                            <div class="detail-row">
                                <label>Disetujui Dekan</label>
                                <span><?= htmlspecialchars($submission['dekan_approver_name']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Activity Logs -->
                <div class="card">
                    <div class="card-header">
                        <h2>Riwayat Aktivitas</h2>
                    </div>
                    
                    <?php if (empty($activityLogs)): ?>
                        <p class="text-muted">Belum ada aktivitas</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($activityLogs as $log): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong>
                                        <p><?= htmlspecialchars($log['description'] ?? $log['action']) ?></p>
                                        <small class="text-muted"><?= timeAgo($log['created_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Form -->
                <?php if ($canApprove): ?>
                    <div class="card card-action">
                        <div class="card-header">
                            <h2>Aksi</h2>
                        </div>
                        
                        <form method="POST">
                            <?= csrfField() ?>
                            
                            <div class="form-group">
                                <label for="reason">Catatan / Alasan (wajib jika tolak)</label>
                                <textarea id="reason" name="reason" rows="3" 
                                          placeholder="Masukkan catatan atau alasan..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="action" value="reject" class="btn btn-danger">
                                    ❌ Tolak
                                </button>
                                <button type="submit" name="action" value="approve" class="btn btn-success">
                                    ✅ Setujui
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
