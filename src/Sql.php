<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;

// sql
// class used to build a sql query in a object-oriented way, uses the DB class (linked to the ORM components)
class Sql extends PdoSql
{
    // config
    protected static array $config = [];


    // setOutput
    // change le output de la requête
    // si le output est de row, change what pour *
    final public function setOutput($output=true):self
    {
        parent::setOutput($output);
        $db = $this->db();

        if($db->isRowOutput($output))
        $this->set('what','*');

        return $this;
    }


    // primary
    // retourne la clé primaire de la table ou de l'objet db
    final public function primary():string
    {
        $table = $this->getTableObject();
        return (!empty($table))? $table->primary():parent::primary();
    }


    // getTableObject
    // retourne l'objet table lié à l'objet sql si existant
    final public function getTableObject():?Table
    {
        $return = null;
        $db = $this->db();
        $table = $this->getTable();

        if(!empty($table) && $db->hasTable($table))
        $return = $db->table($table);

        return $return;
    }


    // checkTableObject
    // retourne l'objet table lié à l'objet sql
    // envoie une exception si non existant
    final public function checkTableObject():Table
    {
        return $this->getTableObject() ?: static::throw();
    }


    // filter
    // gère un filtre pour un objet sql
    // il doit y avoir un table object
    final public function filter(array $values,string $filterAndOr='or'):self
    {
        $table = $this->checkTableObject();

        foreach ($values as $key => $value)
        {
            $col = $table->col($key);

            if($col->canRelation())
            {
                $method = $col->filterMethod();

                if(is_string($method) && $col->canRelation())
                {
                    $loop = [];
                    $rel = $col->relation();

                    if(!is_array($value))
                    $value = [$value];

                    foreach ($value as $k => $v)
                    {
                        if($col->isFilterEmptyNotEmpty() && $col::isFilterEmptyNotEmptyValue($v))
                        {
                            $emptyNotEmpty = ((int) $v === 0)? 'empty':'notEmpty';
                            $loop[] = [$emptyNotEmpty];
                            unset($value[$k]);
                        }
                    }

                    if($rel->isType('distinct'))
                    {
                        $distinct = (array) $rel->keyValue($value);
                        if(!empty($distinct))
                        $loop[] = [$method,array_values($distinct)];
                    }

                    elseif(!empty($value))
                    $loop[] = [$method,$value];

                    $multiLoop = count($loop) > 1;

                    if($multiLoop === true)
                    $this->where('(');

                    foreach ($loop as $i => $args)
                    {
                        if($i > 0)
                        $this->where($filterAndOr);

                        $this->where($col,...$args);
                    }

                    if($multiLoop === true)
                    $this->where(')');
                }
            }
        }

        return $this;
    }


    // checkMake
    // retourne le tableau make, si problème ou retour vide lance une exception
    final protected function checkMake($output,?array $option=null):?array
    {
        $arr = $this->arr();
        $db = $this->db();
        $required = $db->syntaxCall('getQueryRequired',$this->getType());

        if(!empty($required) && !Base\Arr::keysExists($required,$arr))
        {
            $strip = Base\Arr::valuesStrip(array_keys($arr),$required);
            static::throw('missingRequiredClause',$strip);
        }

        elseif($db->isRowOutput($output) && !in_array('*',(array) $arr['what'] ?? null,true))
        static::throw('rowOutput','whatOnlyAccepts','*');

        elseif(empty($arr))
        static::throw('queryEmpty');

        return $this->make($output,$option) ?: static::throw('sqlReturnEmpty');
    }


    // row
    // vide l'objet, change le type pour select avec output row
    // argument est table
    final public function row($value=null):self
    {
        $this->setType('select');
        $this->setOutput('row');

        if(!empty($value))
        $this->table($value);

        return $this;
    }


    // rows
    // vide l'objet, change le type pour select avec output rows
    // argument est table
    final public function rows($value=null):self
    {
        $this->setType('select');
        $this->setOutput('rows');

        if(!empty($value))
        $this->table($value);

        return $this;
    }


    // fromArray
    // étend la méthode fromArray de pdoSql, ajoute la liaison aux attributs de la table
    // ajoute aussi la recherche dans la table
    final public function fromArray(array $array):self
    {
        $table = $this->getTableObject();

        if(!empty($table))
        {
            $array = (array) Base\Obj::cast($array);
            $search = $array['search'] ?? null;
            $searchSeparator = $array['searchSeparator'] ?? $table->getAttr('searchSeparator');
            $searchMethod = $array['searchMethod'] ?? $table->getAttr('searchMethod');
            $searchCols = $array['searchCols'] ?? $table->cols()->searchable();
            $searchTermValid = $array['searchTermValid'] ?? true;

            if(is_string($searchCols))
            $searchCols = [$searchCols];

            if(is_array($searchCols))
            $searchCols = $table->cols(...array_values($searchCols));

            if(is_scalar($search))
            $search = Base\Str::prepareSearch($search,$searchSeparator);

            if(is_array($search) && !empty($search) && $searchCols->isNotEmpty() && is_string($searchMethod))
            {
                if($searchTermValid === true && !$searchCols->isSearchTermValid($search))
                static::throw('invalidSearchTerm',$search);

                $this->whereOrMany($searchMethod,$searchCols,$search);
            }
        }

        return parent::fromArray($array);
    }


    // triggerTableCount
    // retourne le nombre de ligne dans la table, peu importe le reste de la requête
    // possible de mettre le retour en cache, via la classe core/table
    final public function triggerTableCount(bool $cache=false):?int
    {
        return $this->checkTableObject()->rowsCount(true,$cache);
    }


    // triggerRow
    // trigge l'objet sql et retourne un objet row
    final public function triggerRow():?Row
    {
        return $this->set('what','*')->trigger('row');
    }


    // triggerRows
    // trigge l'objet sql et retourne un objet rows
    final public function triggerRows():Rows
    {
        return $this->set('what','*')->trigger('rows');
    }


    // triggerRowsChunk
    // méthode qui permet de faire des requêtes groupés par chun
    // chaque row est passé dans le callback
    // si le callback retourne false, break tout... si retourne true, unlink la row
    // retourne le nombre de chunk
    final public function triggerRowsChunk(int $chunk,\Closure $closure):int
    {
        $return = 0;
        $page = 1;
        $this->limit([$page=>$chunk]);
        $rows = $this->triggerRows();

        while ($rows->isNotEmpty())
        {
            foreach ($rows as $row)
            {
                $result = $closure($row,$return,$page);
                $return++;

                if($result === false)
                break 2;

                elseif($result === true)
                $row->unlink();
            }

            $page++;
            $this->limit([$page=>$chunk]);
            $rows = $this->triggerRows();
        }

        return $return;
    }
}

// init
Sql::__init();
?>