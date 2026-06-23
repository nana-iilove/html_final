<?php
include 'db.php';
session_start();

// 權限檢查
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 功能 A：處理商品上架
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $file_name]);
        echo "<script>alert('商品上架成功！'); location.href='admin.php';</script>";
    }
}

// 功能 B：處理商品刪除管理
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// 功能 C：撈取所有會員
$users = $pdo->query("SELECT id, email, role, is_active FROM users ORDER BY id DESC")->fetchAll();

// 功能 D：處理訂單查詢（支援關鍵字篩選）
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    // 如果輸入數字，同時查詢訂單編號或顧客編號
    $orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? OR user_id = ? ORDER BY id DESC");
    $orders_stmt->execute([$search, $search]);
} else {
    $orders_stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
}
$orders = $orders_stmt->fetchAll();

// 功能 E：圖表統計（按商品分類統計銷售總量）
$chart_stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_qty 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY p.id
");
$chart_data = $chart_stmt->fetchAll();
$labels = []; $counts = [];
foreach ($chart_data as $data) {
    $labels[] = $data['name'];
    $counts[] = (int)$data['total_qty'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>店家高階管理後台</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="font-family: sans-serif; padding: 20px; background: #fafafa;">

    <div style="display: flex; justify-content: space-between; align-items: center; background:#343a40; color:white; padding: 10px 20px; border-radius: 5px;">
        <h2>🛠️ 手機訂購系統 - 後台管理中心</h2>
        <a href="logout.php" style="color:#ffc107; font-weight:bold; text-decoration:none;">登出系統</a>
    </div>

    <section style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #eee;">
        <h3>📦 商品上架與內容管理</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; background:#f8f9fa; padding:15px;">
            <input type="hidden" name="add_product" value="1">
            名稱: <input type="text" name="name" required> | 
            價格: <input type="number" name="price" required> | 
            圖片: <input type="file" name="image" accept="image/*" required> | 
            <button type="submit" style="background:#28a745; color:white; border:none; padding:5px 15px;">確認上架</button>
        </form>

        <table border="1" style="width:100%; border-collapse:collapse; text-align:center;">
            <tr style="background:#f1f1f1;"><th>商品圖片</th><th>商品名稱</th><th>價格</th><th>操作</th></tr>
            <?php 
            $all_p = $pdo->query("SELECT * FROM products")->fetchAll();
            foreach ($all_p as $p): 
            ?>
            <tr>
                <td><img src="uploads/<?= $p['image_path'] ?>" width="50"></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>$<?= $p['price'] ?></td>
                <td><a href="admin.php?delete_product=<?= $p['id'] ?>" onclick="return confirm('確定刪除？')" style="color:red;">刪除商品</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <div style="display:flex; gap:20px; margin-top:20px;">
        <div style="flex:1; background:white; padding:20px; border:1px solid #eee;">
            <h3>👥 會員管理介面</h3>
            <table border="1" style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr style="background:#eee;"><th>會員ID</th><th>電子信箱</th><th>身分</th><th>狀態</th></tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td><?= $u['is_active'] ? '🟢 已開通' : '🔴 未驗證' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div style="flex:1; background:white; padding:20px; border:1px solid #eee;">
            <h3>📋 訂單管理與查詢介面</h3>
            <form method="GET" style="margin-bottom:10px;">
                <input type="text" name="search" placeholder="輸入 訂單編號 或 顧客ID" value="<?= htmlspecialchars($search) ?>">
                <button type="submit">查詢訂單</button>
                <?php if ($search !== ''): ?><a href="admin.php"><button type="button">重設</button></a><?php endif; ?>
            </form>

            <table border="1" style="width:100%; border-collapse:collapse; font-size:14px;">
                <tr style="background:#eee;"><th>訂單編號</th><th>顧客ID</th><th>總金額</th><th>下單時間</th></tr>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= $o['user_id'] == 999 ? '遊客' : '會員('.$o['user_id'].')' ?></td>
                    <td style="color:red;">$<?= $o['total_price'] ?></td>
                    <td><?= $o['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <section style="background: white; padding: 20px; margin-top: 20px; border: 1px solid #eee;">
        <h3>📊 店家銷售數據統計 (Chart.js)</h3>
        <div style="width: 100%; max-width: 600px; margin: 0 auto;">
            <canvas id="salesChart"></canvas>
        </div>
    </section>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: '各商品累積銷售數量 (份)',
                    data: <?= json_encode($counts) ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>