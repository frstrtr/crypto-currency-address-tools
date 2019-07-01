<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Validation;

class DOGE extends Validation
{
    protected $base58PrefixToHexVersion = [
        'D' => '1E',
    ];
}