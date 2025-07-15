<?php
require '../vendor/autoload.php'; // In project root

use AfricasTalking\SDK\AfricasTalking;

$username   = "Onlinemadrassa";      // 🔁 REPLACE with your actual Africa's Talking username
$apiKey     = "atsk_527d457845ef64d7c2d4c40cc761cf75e7a3e97f01f14fea3658523471ac50f6a1229809";   

$AT = new AfricasTalking($username, $apiKey);
$sms = $AT->sms();

try {
    $result = $sms->send([
        'to'      => '+255615336237',  // Replace with real phone number
        'message' => 'Test SMS from madrassa system.',
        // 'from' => 'YOUR_SENDER_ID'  // Optional if your account supports
    ]);

    print_r($result);
} catch (Exception $e) {
    echo "❌ Error sending SMS: " . $e->getMessage();
}