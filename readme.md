# Offsite Test (Backend)
This application and documentation is prepared by Jason Leung. This repo should be only used for the offsite test or any education purpose.

## Installation
It is recommended to use [Homestead](https://laravel.com/docs/master/homestead) to deploy this application as it provides all the essential software with simple configuration and installation.
 
Software Requirements:
- Nginx 1.11.9+
- PHP 7.1.3-3+
- MySQL 5.7.17+
- Redis 3.2.8+
- Ngrok 2.2.3+
- Composer
- Git

Installation commands:
```bash
git clone https://github.com/jl9404/offsite-test.git
cd ./offsite-test
composer install
cp .env.example .env
php artisan key:generate
```

Please review the database and redis settings in `.env` file, please make sure it is correct and then run the following command.
```bash
php artisan migrate
```

## Data Structure

### Used packages

| Name                    | Description                                  |
|-------------------------|----------------------------------------------|
| paypal/rest-api-sdk-php | Paypal offical sdk                           |
| braintree/braintree_php | Braintree offical sdk                        |
| predis/predis           | Redis wrapper in PHP                         |
| laravelcollective/html  | Form inputs generator                        |
| guzzlehttp/guzzle       | HTTP request/response (for IPN verification) |

### Database table
| column         | format        | description                    |
|----------------|---------------|--------------------------------|
| id             | int(10)       | Primary id with auto increment |
| transaction_id | varchar(255)  | Unique transaction id in local |
| reference_id   | varchar(255)  | Reference id in gateway server |
| customer_name  | varchar(255)  | Customer name                  |
| customer_phone | varchar(255)  | Customer phone                 |
| currency       | varchar(3)    | Currency in ISO format         |
| amount         | decimal(10,2) | Payment amount                 |
| debug          | text          | Response from gateway          |
| paid_at        | timestamp     | Payment time                   |
| created_at     | timestamp     | Payment creation time          |
| updated_at     | timestamp     | Payment update time            |

### File Structure
Payment process related class:
```
├── App/
|   └── Services/
|       └── Payment/
|           ├── Contracts/
|           |   ├── FactoryContract.php
|           |   ├── GatewayContract.php
|           |   └── ResponseContract.php
|           ├── Gateways/
|           |   ├── Response
|           |   |   ├── BraintreeResponse.php
|           |   |   └── PaypalResponse.php
|           |   ├── Braintree.php
|           |   └── Paypal.php
|           ├── CreditCard.php
|           └── Gateway.php
```

### Payment flow

1. User Input
2. Input Validation
3. Choose gateway depending on currency
4. Prepare request to gateway
5. Send payment request to gateway
6. Recieve response from gateway
7. If success, save the record, or else, show errors
8. Return transaction ID to user

## Bonus

### 1. Consider how to add additional payment gateways;
The `Gateway` class use singleton and factory pattern with the extend feature, so new payment gateway can be implemented easily. Also, there are `GatewayContract` and `ResponseContract` for gateway and gateway response which is inspired from the design of [Contract](https://laravel.com/docs/master/contracts) to enforce the standard of each gateway.

```php
use Facades\App\Services\Payment\Gateway;

// to add Stripe gateway
Gatway::extend('stripe', function () {
    return new Stripe();
});
```

### 2. Consider how to guarantee payment record are found in your database and Paypal;
There are two ways (active and passive approach) to keep the payment records in synchronization with Paypal.

#### Active Approach
`PaypalSync` is a [Job](https://laravel.com/docs/5.4/queues#creating-jobs) class which is used for synchronize the payment records with Paypal in backend. It retrieves Paypal payment records by using `Payment::all()` API and compare the data with the local database, if the record is not existed in local database, the payment record will be inserted to the database. The latest synchronization time will be cached and will be used for the next synchronization to boost up the synchronization process.

Call job in [Tinker](https://laravel.com/docs/5.4/artisan#introduction)
```bash
php artisan tinker
>>> dispatch_now(new \App\Jobs\PaypalSync);
```

#### Passive Approach

[IPN](https://developer.paypal.com/docs/classic/products/instant-payment-notification/) (Instant Payment Notification) is a notification service which is provided by Paypal. By using this server, Paypal will send the notification after the new payment is created. As the application plays as the receiver role, so it is a passive approach to work as synchronization and verification. It also provides retry mechanism to resend the notification when it is failed to delivery.

In this case, two approaches should be used together which makes the local database is synchronize with Paypal payment records.

### 3. Consider cache may expired when check order record;
The cache is stored in Redis by using the `Cache::forever()` method. Normally, it should be stored in Redis and will not be erased in the normal operation. However, when there is accidential issue arise, the cache may be all flushed. In this situation, the application provides `CacheSync` Job class which can synchronize the local database record to Redis server.

Also, Redis provides mechanism to make data become [persistence](https://redis.io/topics/persistence) by just enabling `appendonly` in the config file. However, there are a few drawbacks(e.g. performance issue) to use AOF instead of RDF.

### 4. Consider the data encryption for the payment record query
As the redis server does not provide any encryption mechanism (unless using [stunnel](https://www.digitalocean.com/community/tutorials/how-to-encrypt-traffic-to-redis-with-stunnel-on-ubuntu-16-04)), it is better to encrypt the payload in the cache. Laravel provides `encrypt()` and `decrypt()` helper to simplify the en/decryption operation.

Saving Model with encrypted cache automatically
```php
static::saved(function (Transaction $transaction) {
    Cache::driver('redis')
        ->tags(['orders', snake_case($transaction->customer_name)])
        ->forever($transaction->transaction_id, encrypt(serialize($transaction)));
});
```
Retrieve cache with decryption
```php
public static function findFromCache($customerName, $transactionId)
{
    $transaction = Cache::tags(['orders', snake_case($customerName)])->get($transactionId);
    if (empty($transaction)) {
        throw new ModelNotFoundException;
    }
    try {
        return unserialize(decrypt($transaction));
    } catch (DecryptException $e) { // try to refresh the corrupted cache
        return tap((new self)->where([
            'customer_name' => $customerName,
            'transaction_id' => $transactionId,
        ])->first())->save();
    }
}
```

### 5. Consider how to handle security for saving credit cards.
Saving card holder data is very [risky](https://stackoverflow.com/questions/3002189/best-practices-to-store-creditcard-information-into-database?answertab=votes#tab-top) and it is not encourage to do it without following [PCI-DSS](https://www.pcisecuritystandards.org/pci_security/). In order to save the card holder data, it involves a brunch of security controls from disk encryption to table column encryption. In this application, card holder data is not stored, but I will illustrate how to do it in the following section. By the [PCI Data Storage Guildline](https://www.pcisecuritystandards.org/pdfs/pci_fs_data_storage.pdf), only PAN, card holder name, service code and expiration can be stored, so the cvv must not be stored in the database. There are special requirements for PAN that the additional productions (e.g. truncation and strong cryptography) should be implemented. Laravel Eloquent's mutator and accessor will be useful and suitable in this scenario.

```php
public function setCcnumberAttribute($value) {
    return $this->attributes['ccnumber'] = encrypt(substr($value, 0, -4));
}

public function getCcnumberAttribute($value) {
    return decrypt($attribute);
}
```

## Apendix

### Test cards for Braintree
```
4111111111111111 VISA
5555555555554444 MasterCard
36259600000004   Diners Club
6011111111111117 Discover
3530111333300000 JCB
6304000000000000 Maestro
```

