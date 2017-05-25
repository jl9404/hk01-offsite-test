<?php

namespace App\Http\Controllers;

use App\Hk01\Payment\Gateways\Braintree;
use App\Hk01\Payment\Order;
use App\Http\Requests\PaymentQueryRequest;
use App\Http\Requests\PaymentStoreRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Payment;

/**
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @param PaymentStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaymentStoreRequest $request)
    {
        $orderId = Carbon::now()->format('YmdHis') . random_int(1000, 9999);

        $gateway = new Braintree;

        $order = Order::create(array_merge($request->validated(), ['orderId' => $orderId]));

        $response = $gateway->purchase($order);

        $order->save();

        Session::regenerateToken();

        return response()->json([
            'success' => true,
            'order' => $order->getData()
        ], 201);
    }

    /**
     * @param PaymentQueryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(PaymentQueryRequest $request)
    {
        $order = Order::find($request->customerName, $request->orderId);

        return response()->json([
           'success' => true,
            'order' => $order->getData(),
        ]);
    }
}
