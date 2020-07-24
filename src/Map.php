<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// map
// root class for a collection of cells, cols or rows
abstract class Map extends Main\MapObj
{
    // trait
    use Main\Map\_readOnly;
    use Main\Map\_sort;


    // config
    protected static array $config = [];


    // construct
    // construit un nouvel objet cells
    final public function __construct(...$values)
    {
        $this->add(...$values);
    }


    // toString
    // retourne les noms de cellules séparés par des virgules
    final public function __toString():string
    {
        return implode(',',$this->keys());
    }


    // onPrepareReturns
    // prépare le retour pour indexes, gets, slice et slice index
    // les lignes sont toujours retournés dans un nouvel objet cells
    final protected function onPrepareReturns(array $array):self
    {
        $array = Base\Arr::clean($array);
        return new static(...array_values($array));
    }


    // cast
    // retourne la valeur cast
    final public function _cast():array
    {
        return $this->keys();
    }
}
?>