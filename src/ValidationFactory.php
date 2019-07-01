<?php

namespace CryptoCurrencyAddressTools;

class ValidationFactory
{
    /**
     * @param $currencyCode
     * @param $address
     * @return App\Http\ValidationInterface
     */
    public function build($currencyCode, $address)
    {
        $currencyCode = strtoupper($currencyCode);
        $className = "App\Http\\Validation\\$currencyCode";
        return new $className($address);
    }
}