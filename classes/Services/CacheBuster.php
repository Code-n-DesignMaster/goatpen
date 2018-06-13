<?php
namespace GoatPen\Services;

class CacheBuster
{
    public static function getPath(string $filename): string
    {
        $path = PUBLIC_DIR . $filename;

        if (! file_exists($path)) {
            return $filename;
        }

        return preg_replace('/\.([a-z]+)$/', '-' . md5_file($path) . '.$1', $filename);
    }
}
