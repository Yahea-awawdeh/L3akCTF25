<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === '' || $pass === '') {
        $error = 'Fill in both fields.';
    } else {
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$user]);
        if ($check->fetch()) {
            $error = 'Username taken.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (username,password) VALUES(?,?)");
            $ins->execute([$user,$hash]);
            $success = 'Registered! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html><head><title>Register</title>
<style>.error{color:red}.success{color:green}</style></head><body>
<h2>Register</h2>
<?php if(isset($error))   echo "<div class='error'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>

<form method="post">
  Username <input name="username"><br>
  Password <input name="password" type="password"><br>
  <button>Register</button>
</form>
<p><a href="login.php">Log in</a></p>
</body></html>
