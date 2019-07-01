<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Validation;

class USDT extends Validation
{
    // more info at https://en.bitcoin.it/wiki/List_of_address_prefixes
    protected $base58PrefixToHexVersion = [
        '1' => '00',
        '3' => '05'
    ];
}