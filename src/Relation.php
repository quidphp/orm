<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// relation
// abstract class that is extended by ColRelation and Relation
abstract class Relation extends Main\ArrMap
{
    // trait
    use _tableAccess;


    // config
    public static $config = [];


    // set
    // set pas permis
    final public function set():void
    {
        static::throw('notAllowed');

        return;
    }


    // unset
    // unset pas permis
    final public function unset():void
    {
        static::throw('notAllowed');

        return;
    }
}
?>