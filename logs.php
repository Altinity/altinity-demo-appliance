<?php

if (isset($_FILES['log'])) {
    $x = mt_rand(100000, 999999);

    if (!file_exists('/tmp/logs')) {
        mkdir('/tmp/logs', 0777, true);
    }

    $prefix = $_REQUEST['type'] == 'Upload as Web Log' ? 'access_' : 'mysql_';
    move_uploaded_file($_FILES['log']['tmp_name'], "/tmp/logs/{$prefix}{$x}.log");
}

header('Location: /');
