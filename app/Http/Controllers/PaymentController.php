<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\Payment\Requests\PaymentQueryRequest;
use App\Services\Payment\Requests\PaymentStoreRequest;
use Illuminate\Support\Facades\Session;

/**
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{

    protected $paymentService;


    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param PaymentStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PaymentStoreRequest $request)
    {
        $attributes = $request->validated();

        $transactionId = $this->paymentService->generateTransactionId();

        $gateway = $this->paymentService->getGateway($request->currency);

        if (empty($gateway)) {
            return abort(400);
        }

        $response = $this->paymentService->makePayment($attributes);

        return response()->json(tap([
            'success' => $response->isSuccessful(),
        ], function (&$payload) use ($response, $attributes) {
            if ($response->isSuccessful()) {
                $payload['order'] = $this->paymentService->saveRecord($attributes, $response);
                Session::regenerateToken();
            } else {
                $payload['message'] = $result->getErrors();
            }
        }), ($response->isSuccessful() ? 201 : 500));
    }

    /**
     * @param PaymentQueryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(PaymentQueryRequest $request)
    {
        $order = Transaction::findFromCache([
            'customer_name' => $request->customer_name, 
            'transaction_id' => $request->transaction_id
        ]);

        return response()->json([
           'success' => true,
            'order' => $order,
        ]);
    }
}
