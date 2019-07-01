<?php


namespace CryptoCurrencyAddressTools\Converter;


use CryptoCurrencyAddressTools\Exception\BCHException;

class BCH {

    const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
    const ALPHABET_MAP =
        [-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1,  0,  1,  2,  3,  4,  5,  6,  7,  8, -1, -1, -1, -1, -1, -1,
            -1,  9, 10, 11, 12, 13, 14, 15, 16, -1, 17, 18, 19, 20, 21, -1,
            22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, -1, -1, -1, -1, -1,
            -1, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, -1, 44, 45, 46,
            47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, -1, -1, -1, -1, -1];
    const BECH_ALPHABET =
        [-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            15, -1, 10, 17, 21, 20, 26, 30,  7,  5, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
            1, 0, 3, 16, 11, 28, 12, 14, 6, 4, 2, -1, -1, -1, -1, -1];
    const EXPAND_PREFIX_UNPROCESSED = [2, 9, 20, 3, 15, 9, 14, 3, 1, 19, 8, 0];
    const EXPAND_PREFIX_TESTNET_UNPROCESSED = [2, 3, 8, 20, 5, 19, 20, 0];
    const EXPAND_PREFIX = 1058337025301;
    const EXPAND_PREFIX_TESTNET = 584719417569;
    const BASE16 = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 10, 11, 12,
        13, 14, 15];

    public function __construct()
    {
        if (PHP_INT_SIZE < 5) {

            // Requires x64 system and PHP!
            throw new BCHException('Run it on a x64 system (+ 64 bit PHP)');
        }
    }
    /**
     * convertBits is the internal function to convert 256-based bytes
     * to base-32 grouped bit arrays and vice versa.
     * @param  array $data Data whose bits to be re-grouped
     * @param  integer $fromBits Bits per input group of the $data
     * @param  integer $toBits Bits to be put to each output group
     * @param  boolean $pad Whether to add extra zeroes
     * @return array $ret
     * @throws BCHException
     */
    static private function convertBits(array $data, $fromBits, $toBits, $pad = true)
    {
        $acc    = 0;
        $bits   = 0;
        $ret    = [];
        $maxv   = (1 << $toBits) - 1;
        $maxacc = (1 << ($fromBits + $toBits - 1)) - 1;

        $datalen = sizeof($data);
        for ($i = 0; $i < $datalen; $i++)
        {
            $value = $data[$i];

            if ($value < 0 || $value >> $fromBits !== 0)
            {
                throw new BCHException('Error!');
            }

            $acc  = (($acc << $fromBits) | $value) & $maxacc;
            $bits += $fromBits;

            while ($bits >= $toBits)
            {
                $bits  -= $toBits;
                $ret[] = (($acc >> $bits) & $maxv);
            }
        }

        if ($pad)
        {
            if ($bits)
            {
                $ret[] = ($acc << $toBits - $bits) & $maxv;
            }
        }
        else if ($bits >= $fromBits || ((($acc << ($toBits - $bits))) & $maxv))
        {
            throw new BCHException('Error!');
        }

        return $ret;
    }

    /**
     * polyMod is the internal function create BCH codes.
     * @param  array $var 5-bit grouped data array whose polyMod to be calculated.
     * @param  integer c Starting value, 1 if the prefix is appended to the array.
     * @return integer $polymodValue polymod result
     */
    static private function polyMod($var, $c = 1)
    {
        $varlen = sizeof($var);;
        for ($i = 0; $i < $varlen; $i++)
        {
            $c0 = $c >> 35;
            $c = (($c & 0x07ffffffff) << 5) ^
                ($var[$i]) ^
                (-($c0 & 1) & 0x98f2bc8e61) ^
                (-($c0 & 2) & 0x79b76d99e2) ^
                (-($c0 & 4) & 0xf33e5fb3c4) ^
                (-($c0 & 8) & 0xae2eabe2a8) ^
                (-($c0 & 16) & 0x1e4f43e470);
        }

        return $c ^ 1;
    }

    /**
     * rebuildAddress is the internal function to recreate error
     * corrected addresses.
     * @param  array $addressBytes
     * @return string $correctedAddress
     */
    static private function rebuildAddress($addressBytes)
    {
        $ret = '';
        $i   = 0;

        while ($addressBytes[$i] !== 0)
        {
            // 96 = ord('a') & 0xe0
            $ret .= chr(96 + $addressBytes[$i]);
            $i++;
        }

        $ret .= ':';
        $len = sizeof($addressBytes);
        for ($i++; $i < $len; $i++)
        {
            $ret .= self::CHARSET[$addressBytes[$i]];
        }

        return $ret;
    }

    /**
     * old2new converts an address in old format to the new Cash Address format.
     * @param  string $oldAddress (either Mainnet or Testnet)
     * @return string $newAddress Cash Address result
     * @throws BCHException
     */
    static public function old2new($oldAddress)
    {
        $bytes = [0];

        for ($x = 0; $x < strlen($oldAddress); $x++)
        {
            $carry = ord($oldAddress[$x]);
            if ($carry > 127 || ((($carry = self::ALPHABET_MAP[$carry]) === -1)))
            {
                throw new BCHException('Unexpected character in address!');
            }

            $bytes_len = sizeof($bytes);
            for ($j = 0; $j < $bytes_len; $j++)
            {
                $carry     += $bytes[$j] * 58;
                $bytes[$j] = $carry & 0xff;
                $carry     >>= 8;
            }

            while ($carry !== 0)
            {
                array_push($bytes, $carry & 0xff);
                $carry >>= 8;
            }
        }

        for ($numZeros = 0; $numZeros < strlen($oldAddress) && $oldAddress[$numZeros] === '1'; $numZeros++)
        {
            array_push($bytes, 0);
        }

        // reverse array
        $answer = [];

        for ($i = sizeof($bytes) - 1; $i >= 0; $i--)
        {
            array_push($answer, $bytes[$i]);
        }

        $version = $answer[0];
        $payload = array_slice($answer, 1, sizeof($answer) - 5);

        if (sizeof($payload) % 4 !== 0)
        {
            throw new BCHException('Unexpected address length!');
        }

        // Assume the checksum of the old address is right
        // Here, the Cash Address conversion starts
        if ($version === 0x00)
        {
            // P2PKH
            $addressType = 0;
            $realNet = true;
        }
        else if ($version === 0x05)
        {
            // P2SH
            $addressType = 1;
            $realNet = true;
        }
        else if ($version === 0x6f)
        {
            // Testnet P2PKH
            $addressType = 0;
            $realNet = false;
        }
        else if ($version === 0xc4)
        {
            // Testnet P2SH
            $addressType = 1;
            $realNet = false;
        }
        else if ($version === 0x1c)
        {
            // BitPay P2PKH
            $addressType = 0;
            $realNet = true;
        }
        else if ($version === 0x28)
        {
            // BitPay P2SH
            $addressType = 1;
            $realNet = true;
        }
        else
        {
            throw new BCHException('Unknown address type!');
        }

        $encodedSize = (sizeof($payload) - 20) / 4;

        $versionByte      = ($addressType << 3) | $encodedSize;
        $data             = array_merge([$versionByte], $payload);
        $payloadConverted = self::convertBits($data, 8, 5, true);
        $arr              = array_merge($payloadConverted, [0, 0, 0, 0, 0, 0, 0, 0]);
        if ($realNet) {
            $expand_prefix = self::EXPAND_PREFIX;
            $ret = 'bitcoincash:';
        } else {
            $expand_prefix = self::EXPAND_PREFIX_TESTNET;
            $ret = 'BCHtest:';
        }
        $mod          = self::polymod($arr, $expand_prefix);
        $checksum     = [0, 0, 0, 0, 0, 0, 0, 0];

        for ($i = 0; $i < 8; $i++)
        {
            // Convert the 5-bit groups in mod to checksum values.
            // $checksum[$i] = ($mod >> 5*(7-$i)) & 0x1f;
            $checksum[$i] = ($mod >> (5 * (7 - $i))) & 0x1f;
        }

        $combined     = array_merge($payloadConverted, $checksum);
        $combined_len = sizeof($combined);
        for ($i = 0; $i < $combined_len; $i++)
        {
            $ret .= self::CHARSET[$combined[$i]];
        }

        return $ret;
    }

    /**
     * Decodes Cash Address.
     * @param  string $inputNew New address to be decoded.
     * @param  boolean $shouldFixErrors Whether to fix typing errors.
     * @param  boolean &$isTestnetAddressResult Is pointer, set to whether it's
     * a testnet address.
     * @return array $decoded Returns decoded byte array if it can be decoded.
     * @return string $correctedAddress Returns the corrected address if there's
     * a typing error.
     * @throws BCHException
     */
    static public function decodeNewAddr($inputNew, $shouldFixErrors, &$isTestnetAddressResult) {
        $inputNew = strtolower($inputNew);
        if (strpos($inputNew, ':') === false) {
            $afterPrefix = 0;
            $expand_prefix = self::EXPAND_PREFIX;
            $isTestnetAddressResult = false;
        }
        else if (substr($inputNew, 0, 12) === 'bitcoincash:')
        {
            $afterPrefix = 12;
            $expand_prefix = self::EXPAND_PREFIX;
            $isTestnetAddressResult = false;
        }
        else if (substr($inputNew, 0, 8) === 'BCHtest:')
        {
            $afterPrefix = 8;
            $expand_prefix = self::EXPAND_PREFIX_TESTNET;
            $isTestnetAddressResult = true;
        }
        else
        {
            throw new BCHException('Unknown address type');
        }

        $data = [];
        $len  = strlen($inputNew);
        for (; $afterPrefix < $len; $afterPrefix++)
        {
            $i = ord($inputNew[$afterPrefix]);
            if ($i > 127 || (($i = self::BECH_ALPHABET[$i]) === -1))
            {
                throw new BCHException('Unexpected character in address!');
            }
            array_push($data, $i);
        }

        $checksum = self::polyMod($data, $expand_prefix);

        if ($checksum !== 0)
        {
            if ($expand_prefix === self::EXPAND_PREFIX_TESTNET) {
                $unexpand_prefix = self::EXPAND_PREFIX_TESTNET_UNPROCESSED;
            } else {
                $unexpand_prefix = self::EXPAND_PREFIX_UNPROCESSED;
            }
            // Checksum is wrong!
            // Try to fix up to two errors
            if ($shouldFixErrors) {
                $syndromes = Array();
                $datalen = sizeof($data);
                for ($p = 0; $p < $datalen; $p++)
                {
                    for ($e = 1; $e < 32; $e++)
                    {
                        $data[$p] ^= $e;
                        $c        = self::polyMod($data, $expand_prefix);
                        if ($c === 0)
                        {
                            return self::rebuildAddress(array_merge($unexpand_prefix, $data));
                        }
                        $syndromes[$c ^ $checksum] = $p * 32 + $e;
                        $data[$p]                  ^= $e;
                    }
                }

                foreach ($syndromes as $s0 => $pe)
                {
                    if (array_key_exists($s0 ^ $checksum, $syndromes))
                    {
                        $data[$pe >> 5]                         ^= $pe % 32;
                        $data[$syndromes[$s0 ^ $checksum] >> 5] ^= $syndromes[$s0 ^ $checksum] % 32;
                        return self::rebuildAddress(array_merge($unexpand_prefix, $data));
                    }
                }
                throw new BCHException('Can\'t correct typing errors!');
            }
        }
        return $data;
    }

    /**
     * Corrects Cash Address typing errors.
     * @param  string $inputNew Cash Address to be corrected.
     * @return string $correctedAddress Error corrected address, or the input itself
     * if there are no errors.
     * @throws BCHException
     */
    static public function fixCashAddrErrors($inputNew) {
        try {
            $corrected = self::decodeNewAddr($inputNew, true, $isTestnet);
            if (gettype($corrected) === 'array') {
                return $inputNew;
            } else {
                return $corrected;
            }
        }
        catch(BCHException $e) {
            throw $e;
        }
    }


    /**
     * new2old converts an address in the Cash Address format to the old format.
     * @param  string $inputNew Cash Address (either mainnet or testnet)
     * @param  boolean $shouldFixErrors Whether to fix typing errors.
     * @return string $oldAddress Old style 1... or 3... address
     * @throws BCHException
     */
    static public function new2old($inputNew, $shouldFixErrors)
    {
        try {
            $corrected = self::decodeNewAddr($inputNew, $shouldFixErrors, $isTestnet);
            if (gettype($corrected) === 'array') {
                $values = $corrected;
            } else {
                $values = self::decodeNewAddr($corrected, false, $isTestnet);
            }
        }
        catch(Exception $e) {
            throw new BCHException('Error');
        }

        $values      = self::convertBits(array_slice($values, 0, sizeof($values) - 8), 5, 8, false);
        $addressType = $values[0] >> 3;
        $addressHash = array_slice($values, 1, 21);

        // Encode Address
        if ($isTestnet) {
            if ($addressType) {
                $bytes = [0xc4];
            } else {
                $bytes = [0x6f];
            }
        } else {
            if ($addressType) {
                $bytes = [0x05];
            } else {
                $bytes = [0x00];
            }
        }
        $bytes      = array_merge($bytes, $addressHash);
        $merged     = array_merge($bytes, self::doubleSha256ByteArray($bytes));
        $digits     = [0];
        $merged_len = sizeof($merged);
        for ($i = 0; $i < $merged_len; $i++)
        {
            $carry = $merged[$i];
            $digits_len = sizeof($digits);
            for ($j = 0; $j < $digits_len; $j++)
            {
                $carry      += $digits[$j] << 8;
                $digits[$j] = $carry % 58;
                $carry      = intdiv($carry, 58);
            }

            while ($carry !== 0)
            {
                array_push($digits, $carry % 58);
                $carry = intdiv($carry, 58);
            }
        }

        // leading zero bytes
        for ($i = 0; $i < $merged_len && $merged[$i] === 0; $i++)
        {
            array_push($digits, 0);
        }

        // reverse
        $converted = '';
        for ($i = sizeof($digits) - 1; $i >= 0; $i--)
        {
            if ($digits[$i] > strlen(self::ALPHABET))
            {
                throw new BCHException('Error!');
            }
            $converted .= self::ALPHABET[$digits[$i]];
        }

        return $converted;
    }

    /**
     * internal function to calculate sha256
     * @param  array $byteArray Byte array of data to be hashed
     * @return array $hashResult First four bytes of sha256 result
     */
    private static function doubleSha256ByteArray($byteArray) {
        $stringToBeHashed = '';
        $byteArrayLen = sizeof($byteArray);
        for ($i = 0; $i < $byteArrayLen; $i++)
        {
            $stringToBeHashed .= chr($byteArray[$i]);
        }
        $hash = hash('sha256', $stringToBeHashed);
        $hashArray = [];
        for ($i = 0; $i < 32; $i++)
        {
            array_push($hashArray, self::BASE16[ord($hash[2 * $i]) - 48] * 16 + self::BASE16[ord($hash[2 * $i + 1]) - 48]);
        }
        $stringToBeHashed = '';
        for ($i = 0; $i < 32; $i++)
        {
            $stringToBeHashed .= chr($hashArray[$i]);
        }

        $hashArray = [];
        $hash      = hash('sha256', $stringToBeHashed);
        for ($i = 0; $i < 4; $i++)
        {
            array_push($hashArray, self::BASE16[ord($hash[2 * $i]) - 48] * 16 + self::BASE16[ord($hash[2 * $i + 1]) - 48]);
        }
        return $hashArray;
    }
}

?>