<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// rowsIndex
// class for a collection of many rows within different tables (keys are indexed)
class RowsIndex extends Rows
{
    // trait
    use Main\Map\_sequential;


    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','sequential','filter','sort','clone']; // méthodes permises
    protected array $mapAfter = ['sequential']; // sequential après chaque appel qui modifie, sequential ne crée pas de clone


    // isTable
    // retourne vrai si le rowsIndex contient au moins un élément de cette table
    final public function isTable($value):bool
    {
        return $this->some(fn($row) => (is_object($value) && $value === $row->table()) || (is_string($value) && $value === $row->table()->name()));
    }


    // sameTable
    // retourne vrai si toutes les lignes dans l'objet ont la même table
    final public function sameTable():bool
    {
        $return = false;
        $table = $this->table();

        if(!empty($table))
        $return = $this->every(fn($row) => $row->sameTable($table));

        return $return;
    }


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


    // add
    // ajoute une ou plusieurs rows dans l'objet
    // valeurs doivent être des objets row
    // possible de fournir un ou plusieurs objets rows (ou row)
    // deux objets identiques ne peuvent pas être ajoutés dans rows
    // des objets de différentes tables peuvent être ajoutés dans rowsIndex
    // n'appel pas sequential (checkAfter) après chaque ajout, c'est inutile
    final public function add(...$values):self
    {
        $this->checkAllowed('add');
        $values = $this->prepareValues(...$values);
        $data =& $this->arr();

        foreach ($values as $value)
        {
            if(!$value instanceof Row)
            static::throw('requiresRow');

            if(!in_array($value,$data,true))
            $data[] = $value;

            else
            static::throw('alreadyIn');
        }

        return $this;
    }


    // filterByTable
    // retourne un objet rows avec toutes les lignes étant dans la table fourni en argument
    // l'objet retourné est dans la bonne classe rows pour la table
    final public function filterByTable($table):?Rows
    {
        $return = null;
        $db = $this->tableDb($table);

        if(!empty($db))
        {
            $table = $db->table($table);
            $classe = $table->classe()->rows();

            if(!empty($classe))
            {
                $return = new $classe();
                foreach ($this->arr() as $value)
                {
                    if($value->sameTable($table))
                    $return->add($value);
                }
            }

            else
            static::throw('noClass');
        }

        return $return;
    }


    // groupByTable
    // retourne un tableau multidimensionnel avec toutes les lignes séparés par le nom de table
    // le id est toujours utilisé comme clé des tableaux
    // les objets retournés retournés sont dans les bonnes classes rows pour les tables
    final public function groupByTable():array
    {
        $return = [];

        foreach ($this->arr() as $value)
        {
            $id = $value->primary();
            $table = $value->table();
            $tableName = $table->name();

            if(!array_key_exists($tableName,$return))
            {
                $classe = $table->classe()->rows();

                if(!empty($classe))
                $return[$tableName] = new $classe();

                else
                static::throw('noClass');
            }

            $return[$tableName]->add($value);
        }

        return $return;
    }


    // tableDb
    // retourne l'objet db pour la première ligne d'une table spécifiée en argument
    final public function tableDb($table):?Db
    {
        $return = null;

        foreach ($this->data as $value)
        {
            if($value->sameTable($table))
            {
                $return = $value->db();
                break;
            }
        }

        return $return;
    }


    // tableRemove
    // enlève de l'objet toutes les lignes appartenant à une table
    // possible de unlink
    final public function tableRemove($table,bool $unlink=false):self
    {
        $table = $this->filterByTable($table);

        foreach ($table as $value)
        {
            $this->remove($value);

            if($unlink === true)
            $value->unlink();
        }

        return $this->checkAfter();
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
    final public function refresh():Rows
    {
        foreach ($this->groupByTable() as $table => $rows)
        {
            $rows->refresh();
        }

        return $this->checkAfter();
    }
}
?>