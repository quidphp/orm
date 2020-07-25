<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// _mapIndex
// trait that grants common methods for indexed collections (cols, cells, rows)
trait _mapIndex
{
    // trait
    use Main\Map\_sequential;


    // isTable
    // retourne vrai si la collection contient au moins un élément de cette table
    final public function isTable($value):bool
    {
        return $this->some(fn($row) => (is_object($value) && $value === $row->table()) || (is_string($value) && $value === $row->table()->name()));
    }


    // sameTable
    // retourne vrai si toutes les entrées dans l'objet ont la même table
    final public function sameTable():bool
    {
        $return = false;
        $table = $this->table();

        if(!empty($table))
        $return = $this->every(fn($row) => $row->sameTable($table));

        return $return;
    }


    // table
    // retourne la table du premier objet
    final public function table():?Table
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->table();

        return $return;
    }


    // add
    // ajoute une ou plusieurs objects dans la collection
    final public function add(...$values):self
    {
        $this->checkAllowed('add');
        $class = $this->mapIs;
        $values = $this->prepareValues(...$values);
        $data =& $this->arr();

        foreach ($values as $value)
        {
            if(!is_a($value,$class,true))
            static::throw('requires',$class);

            if(in_array($value,$data,true))
            static::throw('alreadyIn');

            $data[] = $value;
        }

        return $this;
    }


    // filterByTable
    // retourne un objet collections avec toutes les entrées étant dans la table fourni en argument
    // l'objet retourné est dans la bonne classe de collection pour la table
    final public function filterByTable($table):?Map
    {
        $return = null;

        if(is_string($table))
        {
            $first = $this->table();
            if(!empty($first))
            {
                $db = $first->db();
                if($db->hasTable($table))
                $table = $db->table($table);
            }
        }

        if($table instanceof Table)
        {
            $type = static::$collectionType;
            $classe = $table->classe()->$type() ?: static::throw('noClass');
            $return = new $classe();

            foreach ($this->arr() as $value)
            {
                if($value->sameTable($table))
                $return->add($value);
            }
        }

        return $return;
    }


    // groupByTable
    // retourne un tableau multidimensionnel avec toutes les entrées séparés par le nom de table
    // les objets retournés retournés sont dans les bonnes classes pour les tables
    final public function groupByTable():array
    {
        $return = [];

        foreach ($this->arr() as $value)
        {
            $table = $value->table();
            $tableName = $table->name();

            if(!array_key_exists($tableName,$return))
            {
                $type = static::$collectionType;
                $classe = $table->classe()->$type() ?: static::throw('noClass');
                $return[$tableName] = new $classe();
            }

            $return[$tableName]->add($value);
        }

        return $return;
    }
}
?>