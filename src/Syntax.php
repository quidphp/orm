<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// syntax
// abstract class with static methods to generate SQL syntax
abstract class Syntax extends Main\Root
{
    // trait
    use Base\_option;
    use Base\_shortcut;


    // config
    public static $config = [];


    // _construct
    // pas de possibilité de construire l'objet
    private function __construct()
    {
        return;
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Syntax':null;
    }
}
?>