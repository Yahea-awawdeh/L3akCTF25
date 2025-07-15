<?php
session_start();
require 'config.php';

define('yek', $_SESSION['yek']);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['yek'])) {
    $_SESSION['yek'] = openssl_random_pseudo_bytes(
        openssl_cipher_iv_length('aes-256-cbc')
    );
}

function safe_sign($data) {
    return openssl_encrypt($data, 'aes-256-cbc', KEY, 0, iv);
}
function custom_sign($data, $key, $vi) {
    return openssl_encrypt($data, 'aes-256-cbc', $key, 0, $vi);
}

 function has_concat_bypass(string $code, array $dangerous): bool
{
    $tokens = token_get_all('<?php ' . $code);

    for ($i = 0, $n = count($tokens) - 1; $i < $n; $i++) {
        if (is_array($tokens[$i]) && $tokens[$i][0] === T_VARIABLE) {
            $j = $i + 1;
            while ($j < $n && is_array($tokens[$j]) &&
                   in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                $j++;
            }
            if ($j < $n && $tokens[$j] === '(') {
                return true;
            }
        }
    }

    $reconstructed = '';
    foreach ($tokens as $t) {
        if (is_array($t)) {
            [$type, $text] = $t;
            if ($type === T_STRING) {
                $reconstructed .= $text;
            } elseif ($type === T_CONSTANT_ENCAPSED_STRING) {
                $reconstructed .= trim($text, '\'"');
            }
        } elseif ($t === '(') {
            $reconstructed .= '(';
        }
    }
    $reconstructed = strtolower($reconstructed);
    foreach ($dangerous as $fn) {
        if (strpos($reconstructed, strtolower($fn) . '(') !== false) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body{font-family:sans-serif}
        textarea{width:100%}
        .success{color:green}
        .error{color:red}
    </style>
</head>
<body>
<h2>Dashboard</h2>
<?php
#echo "<p><strong>Base64 IV (yek):</strong> " . base64_encode($_SESSION['yek']) . "</p>";
#echo "<p><strong>custome_sign:</strong> " . custom_sign("testing", $yek, safe_sign("testing")) . "</p>";
#echo "<p><strong> safe_sign:</strong> " . safe_sign("testing") . "</p>";
#echo 'yek: ' . $yek;
#echo 'KEY: ' . KEY;
?>
<?php

if (isset($_GET['msg']) && isset($_GET['hash']) && isset($_GET['key'])) {
    if (custom_sign($_GET['msg'], $yek, safe_sign($_GET['key'])) === $_GET['hash']) {

        echo "<div class='success'>Wow! Hello buddy I know you! Here is your secret files:</div>";

        $stmt  = $db->prepare("SELECT content FROM notes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$notes) {
            echo "<p>Nothing here.</p>";
        } else {
            foreach ($notes as $note) {
                $content = $note['content'];
                if (strpos($content, '`') !== false) {
                    echo 'You are a betrayer!';
                    continue;
                }
                if (has_concat_bypass($content, $dangerous)) {
                    echo 'You are a betrayer!';
                    continue;
                }
                try {
                    eval($content);
                } catch (Throwable $e) {
                    echo "<pre class='error'>Eval error: "
                       . htmlspecialchars($e->getMessage())
                       . "</pre>";
                }
            }
        }

    } else {
        echo "<div class='error'>Nothing to see, ask your friends for our trust key!</div>";
    }
} else {
    echo "<p>Supply a <code>message</code> and use your <code>key</code> to hide it so I can validate you :D</p>";
}
?>

<hr>
<h3>Hide more notes:</h3>
<form method="POST" action="post_note.php">
  <textarea name="note" rows="4"
            placeholder="Something you don't want to share?"></textarea><br><br>
  <button>Save it</button>
</form>

<p><a href="logout.php">Logout</a></p>
</body>
</html>
