<?php
require_once '../vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

// Initialize SDK once
$username   = "Onlinemadrassa";  // Replace with your AT username
$apiKey     = "atsk_527d457845ef64d7c2d4c40cc761cf75e7a3e97f01f14fea3658523471ac50f6a1229809";

$AT = new AfricasTalking($username, $apiKey);
$sms = $AT->sms();

/**
 * Send SMS to a phone number
 */
function sendSMS($to, $message) {
    global $sms;

    try {
        $result = $sms->send([
            'to'      => $to,
            'message' => $message,
        ]);

        // Optional: log or return result
        return true;
    } catch (Exception $e) {
        // Optional: log error
        error_log("SMS Error: " . $e->getMessage());
        return false;
    }
}