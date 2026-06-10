<?php

if (!function_exists('generateQrString')) {
    function generateQrString(string $name, string $phone): string
    {
        $dataPayload = create_tlv('59', $name) . create_tlv('99', $phone);

        $crcValue = crc16_ccitt_false($dataPayload);

        $crcHex = sprintf('%04X', $crcValue);

        $crcTlv = create_tlv('63', $crcHex);

        return $dataPayload . $crcTlv;
    }
}

if (!function_exists('create_tlv')) {
    function create_tlv(string $tag, string $value): string
    {
        $length = sprintf('%02d', strlen($value));

        return $tag . $length . $value;
    }
}

if (!function_exists('crc16_ccitt_false')) {
    function crc16_ccitt_false(string $data): int
    {
        $crc = 0xFFFF;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= ord($data[$i]) << 8;

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }

        return $crc & 0xFFFF;
    }
}