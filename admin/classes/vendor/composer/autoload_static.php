<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb678be6644f3961301fe977d577e2ce
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Shuchkin\\SimpleXLSX' => __DIR__ . '/..' . '/shuchkin/simplexlsx/src/SimpleXLSX.php',
        'SimpleXLS' => __DIR__ . '/..' . '/shuchkin/simplexls/src/SimpleXLS.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticIniteb678be6644f3961301fe977d577e2ce::$classMap;

        }, null, ClassLoader::class);
    }
}
