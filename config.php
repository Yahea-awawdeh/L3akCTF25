<?php
define('KEY', '0123456789abcdef0123456789abcdef');
$dangerous = [
    'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', '$', '`',
    'curl_exec', 'curl_multi_exec', 'eval', 'assert', 'create_function', 'sys',
    'include', 'include_once', 'require', 'require_once', "file_get_contents",
    'readfile', 'fopen', 'fwrite', 'fclose', 'unlink', 'rmdir', 'base64_decode',
    'copy', 'rename', 'chmod', 'chown', 'chgrp', 'touch', 'mkdir',
    'rmdir', 'fseek', 'fread', 'fgets', 'fgetcsv', 'strrev', 'str_rot13',
    'file_put_contents', 'stream_get_contents', 'stream_copy_to_stream',
    'stream_get_line', 'stream_set_blocking', 'stream_set_timeout',
    'stream_select', 'stream_socket_client', 'stream_socket_server',
    'stream_socket_accept', 'stream_socket_recvfrom', 'stream_socket_sendto',
    'stream_socket_get_name', 'stream_socket_pair', 'stream_context_create',
    'stream_context_set_option', 'stream_context_get_options', 'hex2bin'
];
try {
    $db = new PDO('sqlite:/var/www/db/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        content TEXT,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");
} catch (PDOException $e) {
    echo 'DB Error: ' . $e->getMessage();
    exit;
}
?>
