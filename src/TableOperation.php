<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// tableOperation
// abstract class used for a complex operation on a database table
abstract class TableOperation extends Operation
{
    // config
    public static $config = [];
    
    
    // dynamique
    protected $table = null; // conserve la table pour l'opération
    
    
    // construct
    // construit l'objet de l'opération
    final public function __construct(Table $table,?array $attr=null) 
    {
        $this->makeAttr($attr);
        $this->setTable($table);
        
        return;
    }
    
    
    // setTable
    // lie une table à l'objet
    final protected function setTable(Table $table):void 
    {
        $this->table = $table;
        
        return;
    }
    
    
    // table
    // retourne la table liée à l'opération
    final protected function table():Table 
    {
        return $this->table;
    }
    
    
    // db
    // retourne la db de l'opération
    final public function db():Db 
    {
        return $this->table()->db();
    }
}
?>