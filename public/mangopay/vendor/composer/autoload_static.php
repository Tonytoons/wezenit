<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitea943d83253b1160e175b3708ac91334
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'MangoPay\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'MangoPay\\' => 
        array (
            0 => __DIR__ . '/..' . '/mangopay/php-sdk-v2/MangoPay',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitea943d83253b1160e175b3708ac91334::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitea943d83253b1160e175b3708ac91334::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
