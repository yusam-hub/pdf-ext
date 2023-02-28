<?php

namespace YusamHub\PdfExt\Fpdi\Filters;

/**
 * Class FilterASCIIHexDecode
 */
class FilterASCIIHexDecode
{
    /**
     * Converts an ASCII hexadecimal encoded string into it's binary representation.
     *
     * @param string $data The input string
     * @return string
     */
    public function decode($data)
    {
        $data = preg_replace('/[^0-9A-Fa-f]/', '', rtrim($data, '>'));
        if ((strlen($data) % 2) == 1) {
            $data .= '0';
        }

        return pack('H*', $data);
    }

    /**
     * Converts a string into ASCII hexadecimal representation.
     *
     * @param string $data The input string
     * @param boolean $leaveEOD
     * @return string
     */
    public function encode($data, $leaveEOD = false)
    {
        return current(unpack('H*', $data)) . ($leaveEOD ? '' : '>');
    }
}