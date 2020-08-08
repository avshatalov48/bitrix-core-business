<?php
/*
 * SimpleJWT
 *
 * Copyright (C) Kelvin Mo 2015
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above
 *    copyright notice, this list of conditions and the following
 *    disclaimer in the documentation and/or other materials provided
 *    with the distribution.
 *
 * 3. The name of the author may not be used to endorse or promote
 *    products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Bitrix\Sale\PaySystem;

/**
 * Functions for interacting with ASN.1 data streams.
 *
 * Note that this class only implements a small subset of the ASN.1 DER, and should
 * not be used as a general-purpose ASN.1 encoder/decoder.
 */
class ASN1 {
    const UNIVERSAL_CLASS = 0x00;
    const APPLICATION_CLASS = 0x40;
    const CONTEXT_CLASS = 0x80;
    const PRIVATE_CLASS = 0xC0;

    const INTEGER_TYPE = 0x02;
    const BIT_STRING = 0x03;
    const OCTET_STRING = 0x04;
    const NULL_TYPE = 0x05;
    const OID = 0x06;
    const SEQUENCE = 0x10;

    /**
     * Reads a DER stream and decodes a single object
     *
     * @param string $der the data stream
     * @param int $offset the offset of the data stream containing the object
     * to decode
     * @param mixed &$data the decoded object
     * @param bool $ignore_bit_strings whether to refrain from moving the
     * offset when reading a bit string - this allows the caller to read the
     * bit string manually
     * @return int the number of bytes read, or 0 if there is an error
     */
    static function readDER($der, $offset, &$data, $ignore_bit_strings = FALSE) {
        $pos = $offset;

		$size = mb_strlen($der);

        if ($size < 2) return 0;

        // Tag/Type
        $constructed = (ord($der[$pos]) >> 5) & 0x01;
        $type = ord($der[$pos++]) & 0x1f;
        if ($type == 0x1f) return 0; // Long-form type: not supported
        if ($pos >= $size) return 0;

        // Length
        $len = ord($der[$pos++]);
        if ($len & 0x80) {
            $n = $len & 0x1f;
            $len = 0;
            while ($n-- && $pos < $size) {
                $len = ($len << 8) | ord($der[$pos++]);
            }
        }
        if ($pos >= $size || $len > $size - $pos) return 0;

        // Value
        if ($type == self::BIT_STRING) { // BIT STRING
            $pos++; // Skip the first contents octet (padding indicator)
			$data = mb_substr($der, $pos, $len - 1);
            if (!$ignore_bit_strings) $pos += $len - 1;
        } elseif (!$constructed /*&& ($type != 0x04)*/) {
			$data = mb_substr($der, $pos, $len);
            $pos += $len;
        }

        return $pos - $offset;
    }

    /**
     * Encodes a value into a DER object.
     *
     * @param int $type the DER tag of the object
     * @param string $value the value to encode
     * @param bool $primitive whether the object is of a primitive or
     * constructed type
     * @return string the encoded object
     */
    static function encodeDER($type, $value = '', $primitive = true, $class = 0) {
        $tag_header = $class;
        if (!$primitive) $tag_header |= 0x20;

        // Type
        if ($type < 0x1f) {
            $der = chr($tag_header | $type);
        } else {
            return NULL; // Long form required. not supported.
        }

        // Length
		$len = mb_strlen($value);
        if ($len <= 0x7f) {
            $der .= chr($len);
        } else {
            $pack = '';
            $n = 0;
            while ($len) {
                $pack .= chr($len & 0xff);
                $len >>= 8;
                $n++;
            }

            $der .= chr($n | 0x80);

            if (pack('V', 65534) == pack('L', 65534)) {
                $der .= strrev($pack); // Little endian machine - need to convert to big endian
            } else {
                $der = $pack;
            }
        }

        return $der . $value;
    }


    /**
     * Decodes a DER-encoded object identifier into a string.
     *
     * @param $string oid the binary DER-encoded object identifier
     * @return $string the decoded string
     */
    static function decodeOID($oid) {
        $pos = 0;
		$size = mb_strlen($oid);

        // First octet
        $oct = ord($oid[$pos++]);
        $str = floor($oct / 40) . '.' . ($oct % 40);

        // Subsequent octets
        while ($pos < $size) {
            $num = 0;

            do {
                $oct = ord($oid[$pos++]);
                $num = ($num << 7) + ($oct & 0x7F);
            } while (($oct & 0x80) && ($pos < $size));

            $str .= '.' . $num;
        }

        return $str;
    }

    /**
     * Encodes a string into a DER-encoded object identifier.
     *
     * @param $string $str the object identifier string
     * @return $string the binary DER-encoded object identifier
     */
    static function encodeOID($str) {
        $numbers = explode('.', $str);

        // First octet
        $oid = chr(array_shift($numbers) * 40 + array_shift($numbers));

        // Subsequent octets
        foreach ($numbers as $num) {
            if ($num == 0) {
                $oid .= chr(0x00);
                continue;
            }
            $pack = '';

            while ($num) {
                $pack .= chr(0x80 | ($num & 0x7f));
                $num >>= 7;
            }
            $pack[0] = $pack[0] & chr(0x7f);

            if (pack('V', 65534) == pack('L', 65534)) {
                $oid .= strrev($pack); // Little endian machine - need to convert to big endian
            } else {
                $oid .= $pack;
            }
        }

        return $oid;
    }
}

?>
