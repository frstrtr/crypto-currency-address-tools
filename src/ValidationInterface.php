<?php

namespace CryptoCurrencyAddressTools;

interface ValidationInterface
{
    public function __construct($address);

    /**  @return bool */
    public function validate();
}