<?php
header('Content-Type: application/json; charset=utf-8');

// --- 1. 資料庫連線設定 ---
$host = 'localhost';
$dbname = 'voting_system';
$username = 'root';
$password = ''; // XAMPP 預設密碼為空

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    json(['success' => false, 'message' => '資料庫連線失敗: ' . $e->getMessage()], 500);
}

// 輔助函式：統一回傳 JSON
function json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- 2. 自動建立示範資料 (Seed) ---
function seed() {
    global $db;
    $stmt = $db->query("SELECT COUNT(*) FROM activities");
    if ($stmt->fetchColumn() > 0) return;

    // 新增活動
    $db->exec("INSERT INTO activities (name, description, creator) VALUES
        ('最愛水果投票', '選出你最喜歡的水果。', '系統示範'),
        ('最喜歡的程式語言', '從選項中選擇你喜歡的開發語言。', '系統示範')");

    $a1 = $db->query("SELECT id FROM activities WHERE name='最愛水果投票'")->fetchColumn();
    $a2 = $db->query("SELECT id FROM activities WHERE name='最喜歡的程式語言'")->fetchColumn();

    // 新增項目 (不包含圖片)
    $db->exec("INSERT INTO candidates (activity_id, name, description) VALUES
        ($a1, '蘋果', '紅紅的水果，香甜多汁。'),
        ($a1, '香蕉', '黃色水果，口感軟滑。'),
        ($a1, '芒果', '熱帶水果，香氣濃郁。'),
        ($a2, 'PHP', '伺服器端腳本語言，適合快速開發。'),
        ($a2, 'JavaScript', '前後端通用語言。'),
        ($a2, 'Python', '簡潔易讀，適合資料分析與自動化。')");
}
seed();

// --- 3. 動作分流處理 ---
$action = $_REQUEST['action'] ?? '';

// [GET] 列出所有活動
if ($action === 'listActivities') {
    $stmt = $db->query("SELECT id, name, description, creator, created_at FROM activities ORDER BY id DESC");
    json(['success' => true, 'activities' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// [GET] 取得單一活動與其項目
if ($action === 'getActivity') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT id, name, description, creator FROM activities WHERE id = ?");
    $stmt->execute([$id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) json(['success' => false, 'message' => '找不到活動'], 404);

    // 移除選取 image 欄位
    $stmt = $db->prepare("SELECT id, name, description FROM candidates WHERE activity_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $activity['candidates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json(['success' => true, 'activity' => $activity]);
}

// [GET] 取得投票統計結果
if ($action === 'getResults') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT c.id, c.name, COUNT(v.id) AS votes
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.id
        WHERE c.activity_id = ?
        GROUP BY c.id ORDER BY c.id");
    $stmt->execute([$id]);
    json(['success' => true, 'results' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- POST 請求 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 建立活動
    if ($action === 'createActivity') {
        $name = trim($_POST['name'] ?? '');
        $creator = trim($_POST['creator'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $creator === '') json(['success' => false, 'message' => '名稱與建立者為必填']);

        $stmt = $db->prepare("INSERT INTO activities (name, description, creator) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $creator]);
        json(['success' => true]);
    }

    // 建立投票項目 (已移除圖片上傳邏輯)
    if ($action === 'createCandidate') {
        $activity_id = intval($_POST['activity_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($activity_id === 0 || $name === '') json(['success' => false, 'message' => '活動與項目名稱為必填']);

        $stmt = $db->prepare("INSERT INTO candidates (activity_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$activity_id, $name, $description]);
        json(['success' => true]);
    }

    // 進行投票
    if ($action === 'vote') {
        $activity_id = intval($_POST['activity_id'] ?? 0);
        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        $voter_name = trim($_POST['voter_name'] ?? '');

        if ($activity_id === 0 || $candidate_id === 0 || $voter_name === '') {
            json(['success' => false, 'message' => '請填寫名稱並選擇項目']);
        }

        try {
            $stmt = $db->prepare("INSERT INTO votes (activity_id, candidate_id, voter_name) VALUES (?, ?, ?)");
            $stmt->execute([$activity_id, $candidate_id, $voter_name]);
            json(['success' => true]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                json(['success' => false, 'message' => '此名稱已在該活動中投過票！']);
            }
            json(['success' => false, 'message' => '投票失敗']);
        }
    }
}

json(['success' => false, 'message' => '未知的請求']);