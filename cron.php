<?php

require_once __DIR__ . '/bootstrap.php';

// check for daemon running state
if (file_exists($pidFile)) {
    $pid = file_get_contents($pidFile);
    if (posix_getpgid($pid)) {
        return;
    }
}

// store daemon's pid
file_put_contents($pidFile, getmypid());

while (true) {
    if (!file_exists($statusFile)) {
        sleep(3);
        continue;
    }

    $demoInfo = getStatus();
    if ($demoInfo['status'] == 'start') {
        installOntime();
    }
}