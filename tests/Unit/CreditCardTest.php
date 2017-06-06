<?php

namespace Tests\Unit;

use App\Services\Payment\CreditCard;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreditCardTest extends TestCase
{
    public function testCreditCardClassIsExisted()
    {
        $this->assertTrue(class_exists(CreditCard::class));
    }

    public function testCreditCardParse()
    {
        $creditcard = CreditCard::parse('number');

        $this->assertEquals('number', $this->getObjectAttribute($creditcard, 'number'));
    }

    public function testCreditCardType()
    {
        // VISA
        $type = CreditCard::parse('4111111111111111')->getType();
        $this->assertEquals('visa', $type);

        // MasterCard
        $type = CreditCard::parse('5555555555554444')->getType();
        $this->assertEquals('mastercard', $type);

        // Discover
        $type = CreditCard::parse('6011111111111117')->getType();
        $this->assertEquals('discover', $type);

        // Maestro
        $type = CreditCard::parse('6304000000000000')->getType();
        $this->assertEquals('maestro', $type);

        // AMEX
        $type = CreditCard::parse('378282246310005')->getType();
        $this->assertEquals('amex', $type);

        // Unknown
        $type = CreditCard::parse('1231123')->getType();
        $this->assertEquals('unknown', $type);
    }

    public function testCreditCardLuhn()
    {
        // VISA
        $luhn = CreditCard::parse('4111111111111111')->checkLuhn();
        $this->assertTrue($luhn);

        // MasterCard
        $luhn = CreditCard::parse('5555555555554444')->checkLuhn();
        $this->assertTrue($luhn);

        // Discover
        $luhn = CreditCard::parse('6011111111111117')->checkLuhn();
        $this->assertTrue($luhn);

        // Maestro
        $luhn = CreditCard::parse('6304000000000000')->checkLuhn();
        $this->assertTrue($luhn);

        // AMEX
        $luhn = CreditCard::parse('378282246310005')->checkLuhn();
        $this->assertTrue($luhn);

        // Unknown
        $luhn = CreditCard::parse('1231123')->checkLuhn();
        $this->assertFalse($luhn);
    }

    public function testCreditCardLength()
    {
        // VISA
        $length = CreditCard::parse('4111111111111111')->checkLength();
        $this->assertTrue($length);

        // MasterCard
        $length = CreditCard::parse('5555555555554444')->checkLength();
        $this->assertTrue($length);

        // Discover
        $length = CreditCard::parse('6011111111111117')->checkLength();
        $this->assertTrue($length);

        // Maestro
        $length = CreditCard::parse('6304000000000000')->checkLength();
        $this->assertTrue($length);

        // AMEX
        $length = CreditCard::parse('378282246310005')->checkLength();
        $this->assertTrue($length);

        // Unknown
        $length = CreditCard::parse('1231123')->checkLength();
        $this->assertFalse($length);
    }

    public function testCreditCardCcv()
    {
        // VISA
        $cvv = CreditCard::parse('4111111111111111')->checkCvv('123');
        $this->assertTrue($cvv);

        // MasterCard
        $cvv = CreditCard::parse('5555555555554444')->checkCvv('123');
        $this->assertTrue($cvv);

        // Discover
        $cvv = CreditCard::parse('6011111111111117')->checkCvv('123');
        $this->assertTrue($cvv);

        // Maestro
        $cvv = CreditCard::parse('6304000000000000')->checkCvv('123');
        $this->assertTrue($cvv);

        // AMEX
        $cvv = CreditCard::parse('378282246310005')->checkCvv('123');
        $this->assertTrue($cvv);

        $cvv = CreditCard::parse('378282246310005')->checkCvv('1234');
        $this->assertTrue($cvv);

        // Unknown
        $cvv = CreditCard::parse('1231123')->checkLength('12');
        $this->assertFalse($cvv);

        $cvv = CreditCard::parse('1231123')->checkLength('1212');
        $this->assertFalse($cvv);

        $cvv = CreditCard::parse('1231123')->checkLength('12234');
        $this->assertFalse($cvv);
    }

    public function testCreditCardIsValid()
    {
        // VISA
        $result = CreditCard::parse('4111111111111111')->isValid();
        $this->assertTrue($result);

        // MasterCard
        $result = CreditCard::parse('5555555555554444')->isValid();
        $this->assertTrue($result);

        // Discover
        $result = CreditCard::parse('6011111111111117')->isValid();
        $this->assertTrue($result);

        // Maestro
        $result = CreditCard::parse('6304000000000000')->isValid();
        $this->assertTrue($result);

        // AMEX
        $result = CreditCard::parse('378282246310005')->isValid();
        $this->assertTrue($result);

        // Unknown
        $result = CreditCard::parse('1231123')->isValid();
        $this->assertFalse($result);

        $result = CreditCard::parse('abc')->isValid();
        $this->assertFalse($result);
    }
}
