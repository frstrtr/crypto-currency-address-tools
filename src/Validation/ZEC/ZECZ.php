<?php

namespace  CryptoCurrencyAddressTools\Validation\ZEC;

use  CryptoCurrencyAddressTools\Validation;

class ZECZ extends Validation
{
    protected $length = 140;

    protected $base58PrefixToHexVersion = [
        'z' => '16'
    ];
}