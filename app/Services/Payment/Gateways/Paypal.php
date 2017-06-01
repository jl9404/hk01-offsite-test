<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\GatewayContract;
use App\Services\Payment\CreditCard;
use App\Services\Payment\Gateways\Responses\PaypalResponse;
use PayPal\Api\Amount;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentCard;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

class Paypal implements GatewayContract
{
    protected $context;

    public function __construct()
    {
        $config = config('services.paypal');

        $this->context = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                array_get($config, 'client_id'),
                array_get($config, 'client_secret')
            )
        );
    }

    public function getContext()
    {
        return $this->context;
    }

    public function purchase(array $data = [])
    {
        $name = explode(' ', trim(array_get($data, 'ccname')));
        $lastName = last($name);
        array_pop($name);
        $firstName = $name;
        if (is_array($firstName)) {
            $firstName = implode(' ', $firstName);
        }

        $cardType = CreditCard::parse(array_get($data, 'ccnumber'))->getType();

        $card = new PaymentCard();
        $card->setType(($cardType !== 'unknown' ? $cardType : 'visa'))
            ->setNumber(array_get($data, 'ccnumber'))
            ->setExpireMonth(array_get($data, 'ccmonth'))
            ->setExpireYear(array_get($data, 'ccyear'))
            ->setCvv2(array_get($data, 'cvv'))
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setBillingCountry("HK");

        $fi = new FundingInstrument();
        $fi->setPaymentCard($card);

        $payer = new Payer();
        $payer->setPaymentMethod("credit_card")
            ->setFundingInstruments(array($fi));

        $amount = new Amount();
        $amount->setCurrency(strtoupper(array_get($data, 'currency')))
            ->setTotal(array_get($data, 'amount'));

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setCustom(json_encode(array_except($data, ['ccname', 'ccnumber', 'ccmonth', 'ccyear', 'cvv'])))
            ->setDescription(array_get($data, 'customer_name') . ' ' . array_get($data, 'customer_phone'))
            ->setNotifyUrl(route('notify.paypal'))
            ->setInvoiceNumber(array_get($data, 'transaction_id'));

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setTransactions(array($transaction));

        $errors = null;

        try {
            $payment->create($this->context);
        }catch(PayPalConnectionException $e){
            $data = json_decode($e->getData());
            $errors = ($data->message ?: '') . ' (#' . $e->getCode() . ')';
        } catch (\Exception $e) {
        }

        return new PaypalResponse($payment, $errors);
    }
}
