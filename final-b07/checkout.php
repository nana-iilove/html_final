<?php
include 'db.php';
session_start();

// 沒登入也可以結帳（當作遊客 999 號），有登入就抓會員 ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 999;

if (empty($_SESSION['cart'])) {
    echo "<script>alert('購物車是空的！'); location.href='index.php';</script>";
    exit;
}

// 1. 計算總價
$total_price = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p) { $total_price += $p['price'] * $qty; }
}

// 2. 寫入訂單主檔
$stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
$stmt->execute([$user_id, $total_price]);
$order_id = $pdo->lastInsertId();

// 3. 寫入明細
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $id, $qty]);
}

// 4. 清除購物車 Session
unset($_SESSION['cart']);

echo "<script>alert('🎉 訂單送出成功！單號為：#$order_id'); location.href='index.php';</script>";
?>