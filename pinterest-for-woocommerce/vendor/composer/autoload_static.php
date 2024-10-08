<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb5c9fee13999d9e3a4203b036345411b
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Defuse\\Crypto\\' => 14,
        ),
        'A' => 
        array (
            'Automattic\\WooCommerce\\Pinterest\\' => 33,
            'Automattic\\WooCommerce\\Grow\\Tools\\CompatChecker\\v0_0_1\\' => 55,
            'Automattic\\WooCommerce\\ActionSchedulerJobFramework\\' => 51,
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Defuse\\Crypto\\' => 
        array (
            0 => __DIR__ . '/..' . '/defuse/php-encryption/src',
        ),
        'Automattic\\WooCommerce\\Pinterest\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Automattic\\WooCommerce\\Grow\\Tools\\CompatChecker\\v0_0_1\\' => 
        array (
            0 => __DIR__ . '/..' . '/woocommerce/grow/src',
        ),
        'Automattic\\WooCommerce\\ActionSchedulerJobFramework\\' => 
        array (
            0 => __DIR__ . '/..' . '/woocommerce/action-scheduler-job-framework/src',
        ),
        'Automattic\\Jetpack\\Autoloader\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static $classMap = array (
        'Automattic\\Jetpack\\Autoloader\\AutoloadGenerator' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/AutoloadGenerator.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb5c9fee13999d9e3a4203b036345411b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb5c9fee13999d9e3a4203b036345411b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb5c9fee13999d9e3a4203b036345411b::$classMap;

        }, null, ClassLoader::class);
    }
}
