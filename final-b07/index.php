<?php
include 'db.php';
session_start();

// 處理加入購物車
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $p_id = $_GET['id'];
    if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
    $_SESSION['cart'][$p_id] = isset($_SESSION['cart'][$p_id]) ? $_SESSION['cart'][$p_id] + 1 : 1;
    header("Location: index.php");
    exit;
}

// 處理清空購物車
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    unset($_SESSION['cart']);
    header("Location: index.php");
    exit;
}

// 撈取商品列表
$products = $pdo->query("SELECT * FROM products")->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head><meta charset="UTF-8"><title>顧客線上商城</title></head>
<body style="font-family: sans-serif; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 10px 20px;">
        <h2>手機線上訂購系統 (顧客端)</h2>
        <div>
            <?php if (isset($_SESSION['email'])): ?>
                歡迎，<?= htmlspecialchars($_SESSION['email']) ?> (<?= $_SESSION['role'] ?>) | 
                <?php if ($_SESSION['role'] === 'admin'): ?><a href="admin.php">前往後台</a> | <?php endif; ?>
                <a href="logout.php" style="color:red;">登出</a>
            <?php else: ?>
                <a href="login.php">會員登入 / 註冊</a>
            <?php endif; ?>
        </div>
    </div>

    <main style="display: flex; gap: 30px; margin-top: 20px;">
        <div style="flex: 2;">
            <h3>推薦機款</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                <?php if (empty($products)): ?>
                    <p style="color:gray;">目前暫無商品，請先登入後台管理員上架商品。</p>
                <?php endif; ?>
                <?php foreach ($products as $p): ?>
                    <div style="border: 1px solid #ddd; border-radius:8px; padding: 15px; width: 180px; text-align: center;">
                        <img src="uploads/<?= $p['image_path'] ?>" style="width:100%; height:120px; object-fit:cover; border-radius:5px;"><br><br>
                        <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                        <span style="color:#e44d26; font-weight:bold;">$<?= $p['price'] ?></span><br><br>
                        <a href="index.php?action=add&id=<?= $p['id'] ?>" style="background:#007bff; color:white; padding:5px 10px; text-decoration:none; border-radius:3px; font-size:14px;">加入購物車</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="flex: 1; border-left: 2px solid #eee; padding-left: 20px; min-width: 280px;">
            <h3>🛒 我的購物車</h3>
            <?php if (!empty($_SESSION['cart'])): ?>
                <table style="width:100%; border-collapse: collapse;">
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $id => $qty): 
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$id]);
                        $item = $stmt->fetch();
                        if ($item):
                            $subtotal = $item['price'] * $qty;
                            $total += $subtotal;
                    ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 8px 0;"><?= htmlspecialchars($item['name']) ?> x <?= $qty ?></td>
                            <td style="text-align: right; color:gray;">$<?= $subtotal ?></td>
                        </tr>
                    <?php endif; endforeach; ?>
                </table>
                <h4 style="text-align: right; margin-top: 15px;">總計金額：<span style="color:#e44d26; font-size:20px;">$<?= $total ?></span></h4>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <a href="index.php?action=clear" style="flex:1; text-align:center; background:#6c757d; color:white; padding:10px; text-decoration:none; border-radius:5px;">清空</a>
                    <a href="checkout.php" style="flex:2; text-align:center; background:#28a745; color:white; padding:10px; text-decoration:none; border-radius:5px; font-weight:bold;">送出訂單</a>
                </div>
            <?php else: ?>
                <p style="color:gray; text-align:center; padding: 40px 0;">購物車空空如也...</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>