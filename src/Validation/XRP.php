<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Validation;

class XRP extends Validation
{
    protected static $base58Dictionary = 'rpshnaf39wBUDNEGHJKLM4PQRST7VWXYZ2bcdeCg65jkm8oFqi1tuvAxyz';

    public function validate()
    {
        $hexAddress = static::base58ToHex($this->address);
        $check = substr($hexAddress, 0, strlen($hexAddress) - 8);
        $check = pack("H*", $check);
        $check = strtoupper(hash("sha256", hash("sha256", $check, true)));
        $check = substr($check, 0, 8);
        return $check == substr($hexAddress, strlen($hexAddress) - 8);
    }
}