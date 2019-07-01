<?php

namespace CryptoCurrencyAddressTools\Validation;

use CryptoCurrencyAddressTools\Exception\BCHException;
use CryptoCurrencyAddressTools\Exception\Base32Exception;
use CryptoCurrencyAddressTools\Exception\InvalidChecksumException;
use CryptoCurrencyAddressTools\Utils\Base32;
use CryptoCurrencyAddressTools\Validation;
use Exception;

/**
 * Bitcoin Cash (BCH) address validation class.
 * Implementation borrowed from https://github.com/bitcoincoltd/cashaddress
 */
class BCH extends Validation
{
    /**
     * @var array
     */
    protected static $hashBits = [
        160 => 0,
        192 => 1,
        224 => 2,
        256 => 3,
        320 => 4,
        384 => 5,
        448 => 6,
        512 => 7,
    ];

    /**
     * @var array
     */
    protected static $versionBits = [
        "pubkeyhash" => 0,
        "scripthash" => 1,
    ];

    public function validate()
    {
        try {
            $addr = $this->address;
            if (strpos($addr, ":") === false) {
                $addr = "bitcoincash:".$addr;
            }
            static::decode($addr);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $string  - cashaddr string
     * @return string[] - prefix, scriptType, hash
     * @throws Base32Exception
     * @throws BCHException()
     */
    public static function decode($string)
    {
        try {
            /**
             * @var string $prefix
             * @var int[] $words
             */
            list ($prefix, $words) = Base32::decode($string);
        } catch (InvalidChecksumException $e) {
            throw new BCHException("Checksum failed to verify", 0, $e);
        } catch (Base32Exception $e) {
            throw new BCHException("Failed to decode address", 0, $e);
        }

        $numWords = count($words);
        $bytes = Base32::fromWords($numWords, $words);
        $numBytes = count($bytes);

        list ($scriptType, $hash) = self::extractPayload($numBytes, $bytes);

        return [$prefix, $scriptType, $hash];
    }


    /**
     * @param  int  $version
     * @return array
     * @throws BCHException()
     */
    protected static function decodeVersion($version)
    {
        if (($version >> 7) & 1) {
            throw new BCHException("Invalid version - MSB is reserved");
        }

        $scriptMarkerBits = ($version >> 3) & 0x1f;
        $hashMarkerBits = ($version & 0x07);

        $hashBitsMap = array_flip(self::$hashBits);
        if (!array_key_exists($hashMarkerBits, $hashBitsMap)) {
            throw new BCHException("Invalid version or hash length");
        }
        $hashLength = $hashBitsMap[$hashMarkerBits];

        switch ($scriptMarkerBits) {
            case 0:
                $scriptType = "pubkeyhash";
                break;
            case 1:
                $scriptType = "scripthash";
                break;
            default:
                throw new BCHException('Invalid version or script type');
        }

        return [
            $scriptType, $hashLength
        ];
    }

    /**
     * @param  int  $numBytes
     * @param  int[]  $payloadBytes
     * @return string[] - script type and hash
     * @throws BCHException()
     */
    protected static function extractPayload($numBytes, $payloadBytes)
    {
        if ($numBytes < 1) {
            throw new BCHException("Empty base32 string");
        }

        list ($scriptType, $hashLengthBits) = self::decodeVersion($payloadBytes[0]);

        if (($hashLengthBits / 8) !== $numBytes - 1) {
            throw new BCHException("Hash length does not match version");
        }

        $hash = "";

        foreach (array_slice($payloadBytes, 1) as $byte) {
            $hash .= pack("C*", $byte);
        }

        return [$scriptType, $hash];
    }
}