<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;

// cellsIndex
// class for a collection of cells within different tables (keys are indexed)
class CellsIndex extends CellsMap
{
    // trait
    use _mapIndex;


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','sequential','filter','sort','clone']; // méthodes permises
    protected array $mapAfter = ['sequential']; // sequential après chaque appel qui modifie, sequential ne crée pas de clone
    protected static string $collectionType = 'cells'; // type de collection


    // config
    protected static array $config = [];
}
?>