<?php

namespace silnex\Util;

class Helper
{
    static public function rmrf($path)
    {
        foreach (glob($path, GLOB_MARK | GLOB_BRACE) as $file) {
            if (is_dir($file)) {
                self::rmrf($file . '{,.}[!.,!..]*');
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}