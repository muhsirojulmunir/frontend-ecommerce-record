<?php

/**
 * Polyfill untuk server hosting yang belum mengaktifkan ekstensi PHP fileinfo.
 * File ini dimuat sebelum bootstrap/app.php agar tidak konflik dengan namespace.
 */

if (! class_exists('finfo')) {
    if (! defined('FILEINFO_NONE'))      { define('FILEINFO_NONE',      0);    }
    if (! defined('FILEINFO_MIME_TYPE')) { define('FILEINFO_MIME_TYPE', 16);   }
    if (! defined('FILEINFO_MIME'))      { define('FILEINFO_MIME',      1040); }

    class finfo
    {
        public function __construct($flags = null, $magicFile = null) {}

        public function file(string $filename, int $flags = FILEINFO_MIME_TYPE, $context = null): string|false
        {
            $ext   = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimes = [
                'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
                'gif'  => 'image/gif',  'webp' => 'image/webp', 'svg'  => 'image/svg+xml',
                'pdf'  => 'application/pdf', 'zip' => 'application/zip',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls'  => 'application/vnd.ms-excel', 'csv' => 'text/csv',
                'txt'  => 'text/plain', 'json' => 'application/json',
                'mp4'  => 'video/mp4',  'mov'  => 'video/quicktime',
                'webm' => 'video/webm', 'ogg'  => 'video/ogg',
            ];
            return $mimes[$ext] ?? 'application/octet-stream';
        }

        public function buffer(string $string, int $flags = FILEINFO_MIME_TYPE, $context = null): string|false
        {
            return 'application/octet-stream';
        }
    }
}