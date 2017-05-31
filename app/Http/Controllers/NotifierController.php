<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NotifierController extends Controller
{
    public function paypal(Request $request)
    {
        $uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        $sandboxUri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

        $client = new Client;

        $response = $client->post((config('services.paypal.environment') === 'sandbox' ? $sandboxUri : $uri), [
            'form_params' => array_merge($request->input(), ['cmd' => '_notify-validate'])
        ]);

        \Log::debug($response->getBody()->getContents());
        \Log::debug(json_encode($request->input()));
    }
}
