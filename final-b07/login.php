<?php
include 'db.php';
session_start();

// 處理註冊邏輯
if (isset($_POST['register'])) {
    $email = $_POST['reg_email'];
    $password = $_POST['reg_password'];

    // 檢查信箱是否重複
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo "<script>alert('此信箱已被註冊！');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'user', 1)");
        $stmt->execute([$email, $password]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'user';

        echo "<script>alert('🎉 註冊成功！已為您自動登入。'); location.href='index.php';</script>";
        exit;
    }
}

// 處理登入邏輯
if (isset($_POST['login'])) {
    $email = $_POST['log_email'];
    $password = $_POST['log_password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "<script>alert('登入失敗！帳號或密碼錯誤。');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head><meta charset="UTF-8"><title>會員中心</title></head>
<body style="font-family: sans-serif; padding: 30px;">
    <h2>🔐 歡迎來到系統門戶</h2>
    <hr>

    <div style="display: flex; gap: 50px;">
        <div style="border: 1px solid #ccc; padding: 20px; width: 300px;">
            <h3>會員登入</h3>
            <form method="POST">
                <input type="hidden" name="login" value="1">
                信箱: <input type="email" name="log_email" required style="width:90%;"><br><br>
                密碼: <input type="password" name="log_password" required style="width:90%;"><br><br>
                <button type="submit" style="width:100%; padding: 8px;">登入</button>
            </form>
            </div>

        <div style="border: 1px solid #ccc; padding: 20px; width: 300px;">
            <h3>新會員註冊</h3>
            <form method="POST">
                <input type="hidden" name="register" value="1">
                信箱: <input type="email" name="reg_email" required style="width:90%;"><br><br>
                密碼: <input type="password" name="reg_password" required style="width:90%;"><br><br>
                <button type="submit" style="width:100%; padding: 8px; background:#28a745; color:white; border:none;">立即註冊並登入</button>
            </form>
        </div>
    </div>
    <br><a href="index.php">➔ 直接以遊客身分前往商品首頁</a>
</body>
</html>