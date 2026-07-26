<?php
// ============================================
// HELPER FUNCTIONS
// ============================================

require_once __DIR__ . '/config.php';

// Status Labels
function getStatusLabel($status) {
    return match($status) {
        'pending' => 'Menunggu Review',
        'staff_reviewed' => 'Direview Staff',
        'dekan_reviewed' => 'Direview Dekan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'completed' => 'Selesai',
        default => $status,
    };
}

function getStatusBadgeClass($status) {
    return match($status) {
        'pending' => 'badge-warning',
        'staff_reviewed' => 'badge-info',
        'dekan_reviewed' => 'badge-primary',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'completed' => 'badge-secondary',
        default => 'badge-secondary',
    };
}

// Letter Types
function getLetterTypes() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM letter_types WHERE is_active = 1 ORDER BY name");
    return $stmt->fetchAll();
}

function getLetterType($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM letter_types WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Letter Submissions
function getSubmissions($filters = [], $limit = 50) {
    $db = getDB();
    $where = [];
    $params = [];
    
    if (!empty($filters['user_id'])) {
        $where[] = "ls.user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if (!empty($filters['status'])) {
        $where[] = "ls.status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['for_staff'])) {
        $where[] = "ls.status = 'pending'";
    }
    
    if (!empty($filters['for_dekan'])) {
        $where[] = "ls.status = 'staff_reviewed'";
        $where[] = "lt.requires_dekan_approval = 1";
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT ls.*, lt.name as letter_type_name, lt.requires_dekan_approval,
                   u.name as user_name, u.nim, u.email as user_email,
                   a.name as approver_name, d.name as dekan_approver_name
            FROM letter_submissions ls
            JOIN letter_types lt ON ls.letter_type_id = lt.id
            JOIN users u ON ls.user_id = u.id
            LEFT JOIN users a ON ls.approved_by = a.id
            LEFT JOIN users d ON ls.dekan_approved_by = d.id
            $whereClause
            ORDER BY ls.created_at DESC
            LIMIT $limit";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getSubmission($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT ls.*, lt.name as letter_type_name, lt.requires_dekan_approval,
                                 u.name as user_name, u.nim, u.email as user_email, u.phone as user_phone,
                                 u.department as user_department,
                                 a.name as approver_name, d.name as dekan_approver_name
                          FROM letter_submissions ls
                          JOIN letter_types lt ON ls.letter_type_id = lt.id
                          JOIN users u ON ls.user_id = u.id
                          LEFT JOIN users a ON ls.approved_by = a.id
                          LEFT JOIN users d ON ls.dekan_approved_by = d.id
                          WHERE ls.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAttachments($submissionId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM attachments WHERE letter_submission_id = ? ORDER BY created_at");
    $stmt->execute([$submissionId]);
    return $stmt->fetchAll();
}

function getAttachment($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM attachments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Activity Logs
function getActivityLogs($submissionId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT al.*, u.name as user_name
                          FROM activity_logs al
                          LEFT JOIN users u ON al.user_id = u.id
                          WHERE al.letter_submission_id = ?
                          ORDER BY al.created_at DESC");
    $stmt->execute([$submissionId]);
    return $stmt->fetchAll();
}

function logActivity($submissionId, $userId, $action, $description = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_logs (letter_submission_id, user_id, action, description, created_at, updated_at)
                          VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$submissionId, $userId, $action, $description]);
}

// Notifications
function getNotifications($userId, $limit = 20) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE notifiable_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getUnreadNotificationsCount($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE notifiable_id = ? AND read_at IS NULL");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function createNotification($userId, $type, $message, $data = []) {
    $db = getDB();
    $id = bin2hex(random_bytes(16));
    $jsonData = json_encode(array_merge($data, ['message' => $message, 'type' => $type]));
    
    $stmt = $db->prepare("INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at)
                          VALUES (?, ?, 'App\\\\Models\\\\User', ?, ?, NOW(), NOW())");
    $stmt->execute([$id, $type, $userId, $jsonData]);
}

function markNotificationRead($notificationId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ?");
    $stmt->execute([$notificationId]);
}

function markAllNotificationsRead($userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE notifications SET read_at = NOW() WHERE notifiable_id = ? AND read_at IS NULL");
    $stmt->execute([$userId]);
}

// Statistics
function getStats($userId = null, $role = null) {
    $db = getDB();
    $stats = [];
    
    if ($role === 'mahasiswa' && $userId) {
        $stmt = $db->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM letter_submissions WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM letter_submissions");
    }
    
    return $stmt->fetch();
}

function getLetterTypeStats() {
    $db = getDB();
    $stmt = $db->query("SELECT lt.name, COUNT(ls.id) as count
                        FROM letter_types lt
                        LEFT JOIN letter_submissions ls ON lt.id = ls.letter_type_id
                        GROUP BY lt.id, lt.name
                        ORDER BY count DESC");
    return $stmt->fetchAll();
}

// File Upload
function uploadFile($file, $submissionId) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload gagal: ' . $file['error']];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file melebihi 5MB'];
    }
    
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $path = 'uploads/' . $submissionId . '/';
    
    if (!is_dir(UPLOAD_DIR . $submissionId)) {
        mkdir(UPLOAD_DIR . $submissionId, 0755, true);
    }
    
    $fullPath = UPLOAD_DIR . $submissionId . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO attachments (letter_submission_id, filename, original_name, mime_type, size, path, created_at, updated_at)
                              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$submissionId, $filename, $file['name'], $file['type'], $file['size'], $path . $filename]);
        
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan file'];
}

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Flash Messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Date Format
function formatDate($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    
    if ($diff->y > 0) return $diff->y . ' tahun lalu';
    if ($diff->m > 0) return $diff->m . ' bulan lalu';
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}
