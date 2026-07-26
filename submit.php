<?php
// ============================================
// SUBMIT LETTER PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('mahasiswa');

$user = getCurrentUser();
$letterTypes = getLetterTypes();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission';
    } else {
        $letterTypeId = intval($_POST['letter_type_id'] ?? 0);
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        
        if (empty($letterTypeId) || empty($subject) || empty($body)) {
            $error = 'Semua field harus diisi';
        } else {
            $db = getDB();
            
            // Create submission
            $stmt = $db->prepare("INSERT INTO letter_submissions (user_id, letter_type_id, subject, body, status, due_date, created_at, updated_at)
                                  VALUES (?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY), NOW(), NOW())");
            $stmt->execute([$user['id'], $letterTypeId, $subject, $body]);
            $submissionId = $db->lastInsertId();
            
            // Handle file uploads
            if (!empty($_FILES['attachments']['name'][0])) {
                for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                    $file = [
                        'name' => $_FILES['attachments']['name'][$i],
                        'type' => $_FILES['attachments']['type'][$i],
                        'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                        'error' => $_FILES['attachments']['error'][$i],
                        'size' => $_FILES['attachments']['size'][$i],
                    ];
                    uploadFile($file, $submissionId);
                }
            }
            
            // Log activity
            logActivity($submissionId, $user['id'], 'submitted', 'Pengajuan surat dibuat');
            
            // Notify staff
            $staffUsers = $db->query("SELECT id FROM users WHERE role = 'staff' AND is_active = 1")->fetchAll();
            foreach ($staffUsers as $staff) {
                createNotification($staff['id'], 'letter_submitted', 
                    'Pengajuan surat baru dari ' . $user['name'],
                    ['submission_id' => $submissionId, 'subject' => $subject]);
            }
            
            setFlash('success', 'Pengajuan surat berhasil dikirim!');
            header('Location: my-submissions.php');
            exit;
        }
    }
}

generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Surat - <?= APP_NAME ?></title>
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
                <a href="submit.php" class="nav-item active">📝 Ajukan Surat</a>
                <a href="my-submissions.php" class="nav-item">📄 Pengajuan Saya</a>
                <a href="notifications.php" class="nav-item">🔔 Notifikasi</a>
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
                <h1>📝 Ajukan Surat</h1>
                <p>Isi form berikut untuk mengajukan surat</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    
                    <div class="form-group">
                        <label for="letter_type_id">Jenis Surat *</label>
                        <select id="letter_type_id" name="letter_type_id" required>
                            <option value="">-- Pilih Jenis Surat --</option>
                            <?php foreach ($letterTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" <?= ($letter_type_id ?? '') == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                    <?= $type['requires_dekan_approval'] ? '(Perlu Persetujuan Dekan)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subjek *</label>
                        <input type="text" id="subject" name="subject" required 
                               placeholder="Contoh: Mohon Surat Keterangan Aktif"
                               value="<?= htmlspecialchars($subject ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="body">Isi Pengajuan *</label>
                        <textarea id="body" name="body" rows="6" required 
                                  placeholder="Tuliskan detail pengajuan surat Anda..."><?= htmlspecialchars($body ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachments">Lampiran (Maks 3 file, PDF/JPG/PNG, Max 5MB)</label>
                        <input type="file" id="attachments" name="attachments[]" multiple 
                               accept=".pdf,.jpg,.jpeg,.png" class="file-input">
                        <small class="help-text">Format: PDF, JPG, PNG. Maksimal 5MB per file.</small>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-outline">Batal</a>
                        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
