<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Session</title>
</head>
<body>
    <h1>Test Session Data</h1>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h2>Test Links:</h2>
    <p><a href="index.php?login=admin">Login sebagai Admin (Quick)</a></p>
    <p><a href="admin_menu.php">Coba Akses Admin Panel</a></p>
    <p><a href="index.php">Kembali ke Home</a></p>
    
    <h2>Set Session Manual:</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" value="admin">
        <input type="text" name="role" placeholder="Role" value="admin">
        <button type="submit" name="set_session">Set Session</button>
    </form>
    
    <?php
    if (isset($_POST['set_session'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['role'] = $_POST['role'];
        echo "<p>Session set! Refresh halaman.</p>";
    }
    ?>
</body>
</html>