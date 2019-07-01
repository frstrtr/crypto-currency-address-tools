<?php

namespace  CryptoCurrencyAddressTools\Validation\ZEC;

use CryptoCurrencyAddressTools\Validation;

class ZECT extends Validation
{
    protected $length = 52;

    protected $base58PrefixToHexVersion = [
        't' => '1C'
    ];
}