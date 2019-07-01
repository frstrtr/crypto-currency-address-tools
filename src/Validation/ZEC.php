<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Validation;
use CryptoCurrencyAddressTools\Validation\ZEC\ZECT;
use  CryptoCurrencyAddressTools\Validation\ZEC\ZECZ;
use CryptoCurrencyAddressTools\ValidationInterface;

class ZEC implements ValidationInterface
{
    /** @var  ZECT */
    protected $zect;

    /** @var  ZECZ */
    protected $zecz;

    public function __construct($address)
    {
        $this->zect = new ZECT($address);
        $this->zecz = new ZECZ($address);
    }

    public function validate()
    {
        return $this->zect->validate() || $this->zecz->validate();
    }


}