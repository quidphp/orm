<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
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
    protected static array $config = [];


    // set
    // set pas permis
    final public function set():void
    {
        static::throw('notAllowed');
    }


    // unset
    // unset pas permis
    final public function unset():void
    {
        static::throw('notAllowed');
    }


    // appendPrimary
    // utilisé pour ajouter le id entre paranthèse avec #
    final public static function appendPrimary($return,$value):string
    {
        if(!is_string($return))
        $return = (string) $return;

        if(is_numeric($value) && strlen($return) && (string) $value !== $return)
        $return .= " (#$value)";

        return $return;
    }
}
?>