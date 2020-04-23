<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm;

// _tableAccess
// trait that grants table access to the class using
trait _tableAccess
{
    // trait
    use _dbAccess;


    // dynamique
    protected $table = null; // objet table


    // setLink
    // set la table et db à l'objet
    // envoie une exception si l'objet existe déjà
    final protected function setLink(Table $value,bool $checkLink=false):void
    {
        $this->setDb($value->db());
        $this->table = $value->name();

        if($checkLink === true && $this->isLinked())
        static::throw('alreadyInstantiated');

        return;
    }


    // tableName
    // retourne la propriété protégé table
    final public function tableName():string
    {
        return $this->table;
    }


    // tables
    // retourne l'objet tables
    final public function tables():Tables
    {
        return $this->db()->tables();
    }


    // table
    // retourne l'objet table
    final public function table():Table
    {
        return $this->db()->table($this->table);
    }


    // sameTable
    // retourne vrai si l'objet et celui fourni ont la même table
    final public function sameTable($table):bool
    {
        return $this->db()->hasTable($table) && $this->table() === $this->db()->table($table);
    }
}
?>