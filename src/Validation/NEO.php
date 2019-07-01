<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Validation;

class NEO extends Validation
{
    // more info at https://en.bitcoin.it/wiki/List_of_address_prefixes
    protected $base58PrefixToHexVersion = [
        'A' => '17',
    ];
}