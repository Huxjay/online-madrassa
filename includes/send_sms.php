<?php
require _DIR_.'/../vendor/autoload.php';  // adjust path

use AfricasTalking\SDK\AfricasTalking;

function sendApprovalSMS($toNumbers, $message) {
    $username = 'YOUR_AT_USERNAME';   // e.g. sandbox
    $apiKey   = 'YOUR_AT_API_KEY';
    $AT       = new AfricasTalking($username, $apiKey);
    $sms      = $AT->sms();

    try {
        $response = $sms->send([
            'to'      => $toNumbers,
            'message' => $message,
            'enqueue' => true
        ]);
        return $response;
    } catch(Exception $e) {
        return "SMS Error: " . $e->getMessage();
    }
}
?>