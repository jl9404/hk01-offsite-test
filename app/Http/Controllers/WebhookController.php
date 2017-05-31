<?php

namespace App\Http\Controllers;

use Facades\App\Hk01\Payment\Gateway;
use Illuminate\Http\Request;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\WebhookEvent;

class WebhookController extends Controller
{
    public function paypal(Request $request)
    {
        $paypal = Gateway::driver('paypal');

        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);

        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO'][0]);
        $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID'][0]);
        $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL'][0]);
        $signatureVerification->setWebhookId("1JG94068294428706"); // Note that the Webhook ID must be a currently valid Webhook that you created with your client ID/secret.
        $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG'][0]);
        $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME'][0]);

        $webhookEvent = new WebhookEvent();
        $webhookEvent->fromJson(json_encode($request->input));
        $signatureVerification->setWebhookEvent($webhookEvent);

        $output = $signatureVerification->post($paypal->getContext());

        \Log::debug($output);
        \Log::debug(json_encode($request->input()));
    }
}
