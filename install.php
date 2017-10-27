<?php

require_once __DIR__ . '/bootstrap.php';

if (isset($_REQUEST['start']) && !file_exists($statusFile)) {
    updateStatus('start', 'Initialization...');
}

if (isset($_REQUEST['status'])) {
    $demoInfo = getStatus();

    header('Content-Type: application/json');
    die(json_encode($demoInfo));
}

header("Location: /");

