<?php

namespace App\Hk01\Payment;

class CreditCard
{

    protected $formats = [
        //visa, mastercard, amex, discover, maestro
        'amex' => [
            'pattern' => '3[47]',
            'length' => [15],
        ],
        'mastercard' => [
            'pattern' => '(5[1-5]|2(22[1-9]|2[3-9][0-9]|[3-6][0-9]{2}|7[01][0-9]|720))',
            'length' => [16],
        ],
        'visa' => [
            'pattern' => '4',
            'length' => [13, 16, 19],
        ],
        'discover' => [
            'pattern' => '((601|622|64[4-9])|65)',
            'length' => [16, 19],
        ],
        'maestro' => [
            'pattern' => '(5[068]|6)',
            'length' => [12, 13, 14, 15, 16, 17, 18, 19],
        ],
    ];

    protected $number;

    protected $type;

    public function __construct($number)
    {
        $this->number = $number;
    }

    public static function parse($number)
    {
        return new self($number);
    }

    public function isValid()
    {
        return ctype_digit($this->number) && $this->checkLength() && $this->checkLuhn();
    }

    public function getType()
    {
        if (! empty($this->type)) {
            return $this->type;
        }
        $this->type = 'unknown';
        foreach ($this->formats as $type => $format) {
            if (preg_match('/^' . $format['pattern'] . '/', $this->number)) {
                return $this->type = $type;
            }
        }
        return $this->type;
    }

    public function checkLength()
    {
        $cardLength = strlen($this->number);
        if (($type = $this->getType()) !== 'unknown') {
            return in_array($cardLength, $this->formats[$type]['length']);
        }
        return $cardLength >= 12;
    }

    public function checkCvv($cvv)
    {
        $cvvLength = strlen($cvv);
        return ctype_digit($cvv) && ($cvvLength == 3 || $this->getType() == 'amex' && $cvvLength == 4);
    }

    public function checkLuhn()
    {
        //https://github.com/inacho/php-credit-card-validator/blob/master/src/CreditCard.php#L205
        $number = $this->number;
        $checksum = 0;
        for ($i = (2 - (strlen($number) % 2)); $i <= strlen($number); $i += 2) {
            $checksum += (int) ($number{$i - 1});
        }
        // Analyze odd digits in even length strings or even digits in odd length strings.
        for ($i = (strlen($number)% 2) + 1; $i < strlen($number); $i += 2) {
            $digit = (int) ($number{$i - 1}) * 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += ($digit - 9);
            }
        }
        if (($checksum % 10) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
