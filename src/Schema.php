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

// schema
// class that provides a schema for a database with tables and columns information
class Schema extends Main\Map
{
    // trait
    use Main\Map\_arrs;
    use _dbAccess;


    // config
    public static $config = [];


    // map
    protected static $allow = ['empty','jsonSerialize','serialize','clone']; // méthodes permises


    // construct
    // construit l'objet schema
    final public function __construct(?array $data=null,Db $db)
    {
        $this->setDb($db);

        if(!empty($data))
        $this->makeOverwrite($data);

        return;
    }


    // tables
    // retourne le nom de toutes les tables
    // si cache est false, ignore la cache et ensuite écrase la
    final public function tables(bool $cache=true):array
    {
        $return = [];
        $tables = null;
        $data =& $this->arr();

        if($cache === true && !empty($data))
        $return = array_keys($data);

        if(empty($return) || $cache === false)
        {
            $db = $this->db();
            $tables = $db->showTables();

            if(is_array($tables) && !empty($tables))
            {
                foreach ($tables as $table)
                {
                    if(!array_key_exists($table,$data))
                    $data[$table] = [];
                }

                $return = $tables;
            }
        }

        return $return;
    }


    // table
    // retourne le schema pour une table
    // si cache est false, ignore la cache et ensuite écrase la
    final public function table($table,bool $cache=true):?array
    {
        $return = null;
        $table = Base\Obj::cast($table);
        $data =& $this->arr();

        if(is_string($table))
        {
            if($cache === true)
            $return = $this->get($table);

            if(empty($return) || $cache === false)
            {
                $db = $this->db();
                $schema = $db->showTableColumns($table);

                if(is_array($schema) && !empty($schema))
                {
                    $data[$table] = $schema;
                    $return = $schema;
                }
            }
        }

        return $return;
    }


    // col
    // retourne le schema pour une colonne
    // si cache est false, ignore la cache et ensuite écrase la
    final public function col($table,$col,bool $cache=true):?array
    {
        $return = null;
        $table = Base\Obj::cast($table);
        $col = Base\Obj::cast($col);
        $data =& $this->arr();

        if(is_string($table) && is_string($col))
        {
            if($cache === true)
            $return = $this->get([$table,$col]);

            if(empty($return) || $cache === false)
            {
                $db = $this->db();
                $schema = $db->showTableColumn($table,$col);

                if(is_array($schema) && !empty($schema))
                {
                    $data[$table][$col] = $schema;
                    $return = $schema;
                }
            }
        }

        return $return;
    }


    // all
    // recharge tout le schema de la base de données
    final public function all():?array
    {
        $return = null;
        $data =& $this->arr();
        $this->empty();
        $this->tables();

        foreach ($data as $key => $value)
        {
            $this->table($key);
        }

        $return = $data;

        return $return;
    }
}
?>