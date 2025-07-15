<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $q = $db->prepare("SELECT id,password FROM users WHERE username=?");
    $q->execute([$user]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass,$row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        if (!isset($_SESSION['yek'])) {
            $_SESSION['yek'] = openssl_random_pseudo_bytes(
                openssl_cipher_iv_length('aes-256-cbc')
            );
        }
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html><head><title>Login</title>
<style>.error{color:red}</style></head><body>
<h2>Login</h2>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="post">
  Username <input name="username"><br>
  Password <input name="password" type="password"><br>
  <button>Login</button>
</form>
<p><a href="register.php">Register</a></p>
</body></html>
