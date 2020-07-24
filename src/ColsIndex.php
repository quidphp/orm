<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;

// colsIndex
// class for a collection of cols within different tables (keys are indexed)
class ColsIndex extends ColsMap
{
    // trait
    use _mapIndex;


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','sequential','filter','sort','clone']; // méthodes permises
    protected array $mapAfter = ['sequential']; // sequential après chaque appel qui modifie, sequential ne crée pas de clone
    protected static string $collectionType = 'cols'; // type de collection


    // config
    protected static array $config = [];
}
?>