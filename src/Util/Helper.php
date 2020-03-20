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

    static public function scanFiles(string $path, &$files = null, int $depth = 1)
    {
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                self::scanFiles("$file/*", $files, $depth++);
            } else {
                $files[] = $file;
            }
        }
        return $files;
    }

    static public function startSeparator(string $path)
    {
        if ($path && substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }
}
