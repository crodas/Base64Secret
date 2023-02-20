<?php
/*
+---------------------------------------------------------------------------------+
| Copyright (c) 2023 César D. Rodas                                               |
+---------------------------------------------------------------------------------+
| Redistribution and use in source and binary forms, with or without              |
| modification, are permitted provided that the following conditions are met:     |
| 1. Redistributions of source code must retain the above copyright               |
|    notice, this list of conditions and the following disclaimer.                |
|                                                                                 |
| 2. Redistributions in binary form must reproduce the above copyright            |
|    notice, this list of conditions and the following disclaimer in the          |
|    documentation and/or other materials provided with the distribution.         |
|                                                                                 |
| 3. All advertising materials mentioning features or use of this software        |
|    must display the following acknowledgement:                                  |
|    This product includes software developed by César D. Rodas.                  |
|                                                                                 |
| 4. Neither the name of the César D. Rodas nor the                               |
|    names of its contributors may be used to endorse or promote products         |
|    derived from this software without specific prior written permission.        |
|                                                                                 |
| THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
| EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
| WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
| DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
| DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
| (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
| LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
| ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
| SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
+---------------------------------------------------------------------------------+
| Authors: César Rodas <crodas@php.net>                                           |
+---------------------------------------------------------------------------------+
*/
namespace Base64Secret;


/** 
 * Base64Secret
 * 
 * This class is used to encode and decode data using a secret key. The key is
 * used the determine the alphabet used for encoding and decoding.
 * 
 * Without the key is would be hard to decode the data.
 * 
 * Warning: This class is not meant to be used for security purposes. It was
 * design to obfuscate data, but never to encrypt it. Do not use it encrypt
 * sensitive data
 */
class Base64Secret
{
    /**
     * The alphabet used for encoding
     * 
     * The key is the regular base64 alphabet, the value is the alphabet used
     * for encoding and decoding. The alphabet is sorted by the key that is
     * provided.
     * 
     * As long as the key remains private, it will be hard to decode any data
     * without the key.
     */
    protected readonly array $transitionTable;

    /**
     * The alphabet used for decoding.
     * 
     * It is inverse from the transitionTable.
     */
    protected readonly array $transitionTableRev;

    public function __construct(protected readonly string $key)
    {
        /**
         * List of base64 characters and their crc32. This is used to sort the
         * alphabet for the base64
         */
        $alphabet = [
            'A' => 3554254475,
            'B' => 1255198513,
            'C' => 1037565863,
            'D' => 2746444292,
            'E' => 3568589458,
            'F' => 1304234792,
            'G' => 985283518,
            'H' => 2852464175,
            'I' => 3707901625,
            'J' => 1141589763,
            'K' => 856455061,
            'L' => 2909332022,
            'M' => 3664761504,
            'N' => 1130791706,
            'O' => 878818188,
            'P' => 3110715001,
            'Q' => 3463352047,
            'R' => 1466425173,
            'S' => 543223747,
            'T' => 3187964512,
            'U' => 3372436214,
            'V' => 1342839628,
            'W' => 655174618,
            'X' => 3081909835,
            'Y' => 3233089245,
            'Z' => 1505515367,
            'a' => 3904355907,
            'b' => 1908338681,
            'c' => 112844655,
            'd' => 2564639436,
            'e' => 4024072794,
            'f' => 1993550816,
            'g' => 30677878,
            'h' => 2439710439,
            'i' => 3865851505,
            'j' => 2137352139,
            'k' => 140662621,
            'l' => 2517025534,
            'm' => 3775001192,
            'n' => 2013832146,
            'o' => 252678980,
            'p' => 2181537457,
            'q' => 4110462503,
            'r' => 1812594589,
            's' => 453955339,
            't' => 2238339752,
            'u' => 4067256894,
            'v' => 1801730948,
            'w' => 476252946,
            'x' => 2363233923,
            'y' => 4225443349,
            'z' => 1657960367,
            '0' => 4108050209,
            '1' => 2212294583,
            '2' => 450215437,
            '3' => 1842515611,
            '4' => 4088798008,
            '5' => 2226203566,
            '6' => 498629140,
            '7' => 1790921346,
            '8' => 4194326291,
            '9' => 2366072709,
            '-' => 2547889144,
            '_' => 701932520,
        ];

        $stdAlphabet = array_merge(array_slice(array_keys($alphabet), 0, -2), ['+', '/']);

        $revHash = crc32(strrev($key));
        $hash = crc32($key);
        $pos = 0;
        foreach ($alphabet as $letter => $letterHash) {
            $alphabet[$letter] = $letterHash % ($pos % 2 == 0 ? $hash : $revHash);
            ++$pos;
        }
        arsort($alphabet);

        $this->transitionTable = array_combine($stdAlphabet, array_keys($alphabet));
        $this->transitionTableRev = array_combine(array_keys($alphabet), $stdAlphabet);
    }

    /** 
     * Encodes a string to a base64 string safe
     * 
     * The alphabet used is sorted by the crc32 of the key. This means that
     */
    public function encode(string $data): string
    {
        $base64 = base64_encode($data);
        return rtrim(strtr($base64, $this->transitionTable), "=");
    }

    public function decode(string $data): string
    {
        $data = strtr($data, $this->transitionTableRev);
        return base64_decode($data);
    }
}