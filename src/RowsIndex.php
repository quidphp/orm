<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;

// rowsIndex
// class for a collection of rows within different tables (keys are indexed)
class RowsIndex extends RowsMap
{
    // trait
    use _mapIndex;


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','sequential','filter','sort','clone']; // méthodes permises
    protected array $mapAfter = ['sequential']; // sequential après chaque appel qui modifie, sequential ne crée pas de clone
    protected static string $collectionType = 'rows'; // type de collection


    // config
    protected static array $config = [];


    // hasCell
    // retourne vrai si toutes les lignes dans l'objet ont la cellule
    final public function hasCell($key):bool
    {
        return $this->every(fn($row) => $row->hasCell($key));
    }


    // primaries
    // retourne un tableau multidimensionnel avec toutes les ids de lignes séparés par le nom de table
    final public function primaries():array
    {
        $return = [];

        foreach ($this->arr() as $value)
        {
            $id = $value->primary();
            $table = $value->table()->name();

            if(!array_key_exists($table,$return))
            $return[$table] = [];

            $return[$table][] = $id;
        }

        return $return;
    }


    // alive
    // vérifie l'existence des ligne, fait une requête par table
    // retourne faux si une des tables n'a pas les lignes
    final public function alive():bool
    {
        $return = false;

        foreach ($this->groupByTable() as $table => $rows)
        {
            $return = $rows->alive();

            if($return === false)
            break;
        }

        return $return;
    }


    // refresh
    // rafraichit les lignes, fait une requête par table
    final public function refresh():self
    {
        foreach ($this->groupByTable() as $table => $rows)
        {
            $rows->refresh();
        }

        return $this->checkAfter();
    }
}
?>