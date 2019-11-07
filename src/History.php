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

// history
// class used to store the history of requests made to the PDO object
class History extends Main\Map
{
    // config
    public static $config = [];


    // map
    protected static $is = 'array'; // les valeurs doivent passés ce test de validation ou exception
    protected static $allow = ['push','empty']; // méthodes permises


    // dynamique
    protected $syntax = null; // garde une copie de la classe de syntaxe à utiliser


    // invoke
    // retourne un index de l'historique
    final public function __invoke(...$args)
    {
        return $this->index(...$args);
    }


    // toString
    // affiche le dump du tableau des requêtes uni
    final public function __toString():string
    {
        return Base\Debug::varGet($this->keyValue());
    }


    // cast
    // cast de l'historique, retourne le count
    final public function _cast()
    {
        return $this->count();
    }


    // getSyntax
    // retourne la classe de syntaxe
    final public function getSyntax():?string
    {
        return $this->syntax;
    }


    // setSyntax
    // enregistre la classe de syntaxe
    final protected function setSyntax(Pdo $pdo):void
    {
        $this->syntax = $pdo->getSyntax();

        return;
    }


    // add
    // ajoute un statement dans l'historique db
    final public function add(array $value,\PDOStatement $statement,Pdo $pdo):self
    {
        if(empty($this->getSyntax()))
        $this->setSyntax($pdo);

        if(!empty($value['type']))
        {
            if(array_key_exists('cast',$value))
            unset($value['cast']);

            if($pdo->isOutput($value['type'],'rowCount'))
            $value['row'] = $statement->rowCount();

            if($pdo->isOutput($value['type'],'columnCount'))
            {
                $value['column'] = $statement->columnCount();
                $value['cell'] = $value['row'] * $value['column'];
            }

            $this->push($value);
        }

        return $this;
    }


    // all
    // retourne des donnés de l'historique
    // possibilité de filtrer par type
    final public function all(?string $type=null,bool $reverse=false):array
    {
        $return = [];
        $data = $this->arr();

        if(is_string($type))
        {
            foreach ($data as $value)
            {
                if(is_array($value) && !empty($value['type']) && $value['type'] === $type)
                $return[] = $value;
            }
        }

        else
        $return = $data;

        if($reverse === true)
        $return = array_reverse($return,false);

        return $return;
    }


    // keyValue
    // retourne un tableau unidimensionnel d'historique
    // emule la requête si nécessaire
    final public function keyValue(?string $type=null,bool $reverse=false):array
    {
        $return = [];
        $syntax = $this->getSyntax();

        if(!empty($syntax))
        {
            foreach ($this->all($type,$reverse) as $value)
            {
                if(is_array($value) && array_key_exists('sql',$value))
                {
                    $sql = $value['sql'];
                    if(!empty($value['prepare']))
                    $sql = $syntax::emulate($sql,$value['prepare']);

                    $return[] = $sql;
                }
            }
        }

        return $return;
    }


    // typeCount
    // retourne les données counts de l'historique
    // le type est requis
    final public function typeCount(string $type):array
    {
        $return = [];

        foreach ($this->all($type) as $value)
        {
            if(is_array($value) && !empty($value))
            {
                if(!array_key_exists('query',$return))
                $return['query'] = 1;
                else
                $return['query']++;

                foreach (['row','column','cell'] as $v)
                {
                    if(array_key_exists($v,$value) && is_int($value[$v]))
                    {
                        if(!array_key_exists($v,$return))
                        $return[$v] = 0;

                        $return[$v] += $value[$v];
                    }
                }
            }
        }

        return $return;
    }


    // typeIndex
    // retourne un index de l'historique filtre par type ou null si non existant
    // par défaut index est le dernier, plus récent
    final public function typeIndex(string $type,int $index=-1):?array
    {
        return Base\Arr::index($index,$this->all($type));
    }


    // total
    // retourne les données counts de l'historique pour tous les types
    final public function total():array
    {
        $return = [];
        $syntax = $this->getSyntax();

        if(!empty($syntax))
        {
            foreach ($syntax::getQueryTypes() as $type)
            {
                $array = $this->typeCount($type);

                if(!empty($array))
                $return[$type] = $array;
            }
        }

        return $return;
    }
}
?>