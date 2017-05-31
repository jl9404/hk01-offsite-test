
# HK01 Offsite Test (Backend)

In this document, 

## Installation

It is recommended to use [Homestead](https://laravel.com/docs/master/homestead) to deploy this application as it provides all the essential software with simple configuration and installation.
 
Software Requirements:
- Nginx 1.11.9
- PHP 7.1.3-3
- MySQL 5.7.17
- Redis 3.2.8
- Ngrok 2.2.3
- Composer
- Git

Installation commands:
```bash
git clone https://github.com/jl9404/hk01-offsite-test.git
composer install
cp .env.example .env
php artisan key:generate
```

Please review the database and redis settings in `.env` file, please make sure it is correct and then run the following command.
```bash
php artisan migrate
```

## Bonus

### 1. Consider how to add additional payment gateways;

The `Gateway` class use factory pattern with the extend feature, so new payment gateway can be implemented easily. Also, there are `GatewayContract` and `ResponseContract` for gateway and gateway response which is inspired from the design of [Contract](https://laravel.com/docs/master/contracts) to enforce the standard of each gateway.

```php
use Facades\App\Hk01\Payment\Gateway;

// to add Stripe gateway
Gatway::extend('stripe', function () {
    return new Stripe();
});
```

### 2. Consider how to guarantee payment record are found in your database and Paypal;

There are two ways (active and passive approch) to keep the payment records in synchronization with Paypal.

#### Active Approch

placeholder

#### Passive Approch

placeholder

### 3. Consider cache may expired when check order record;

placeholder

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