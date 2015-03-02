<?php

namespace CS\Models\GoogleMaps;

/**
 * Description of StaticMap
 *
 * @author root
 */
class StaticMap
{

    private static $pixelWidth = array(
        0 => 160300.64,
        1 => 80150.32,
        2 => 40075.16,
        3 => 20037.58,
        4 => 10018.79,
        5 => 5009.395,
        6 => 2504.6975,
        7 => 1252.34875,
        8 => 626.174375,
        9 => 313.0871875,
        10 => 156.54359375,
        11 => 78.271796875,
        12 => 39.135898438,
        13 => 19.567949219,
        14 => 9.783974609,
        15 => 4.891987305,
        16 => 2.445993652,
        17 => 1.222996826,
        18 => 0.611498413,
        19 => 0.305749207,
        20 => 0.152874603
    );

    public static function getImageUrl($latitude, $longitude, $width, $height, $zoom)
    {
        return 'http://maps.googleapis.com/maps/api/staticmap?center=' .
                $latitude . ',' . $longitude . '&zoom=' .
                $zoom . '&size=' . $width . 'x' . $height . '&sensor=false';
    }

    public static function getImageUrlCircle($latitude, $longitude, $width, $height, $radius)
    {
        for ($i = 20; $i > 0; $i--) {
            if ($radius < self::getPixelWidth($i) * $width &&
                    $radius < self::getPixelWidth($i) * $width) {
                return self::getImageUrl($latitude, $longitude, $width, $height, $i);
            }
        }

        return self::getImageUrl($latitude, $longitude, $width, $height, 0);
    }

    public static function getPixelWidth($zoom)
    {
        if ($zoom < 0) {
            return self::$pixelWidth[0];
        } else if ($zoom > 20) {
            return self::$pixelWidth[20];
        }

        return self::$pixelWidth[$zoom];
    }

}
