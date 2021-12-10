<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4a2b971601378576218a0e61e955a6a2
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Photogallery\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Photogallery\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Photogallery\\PhotoGallery' => __DIR__ . '/../..' . '/src/PhotoGallery.class.php',
        'Photogallery\\PhotoGalleryImage' => __DIR__ . '/../..' . '/src/PhotoGalleryImage.class.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4a2b971601378576218a0e61e955a6a2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4a2b971601378576218a0e61e955a6a2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4a2b971601378576218a0e61e955a6a2::$classMap;

        }, null, ClassLoader::class);
    }
}