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

// _dbAccess
// trait that grants database access to the class using
trait _dbAccess
{
    // dynamique
    protected $db = null; // objet db, peut être objet ou string


    // serialize
    // serialize un objet
    // envoie une exception si l'objet db est stocké en objet (ne peut pas être serialize)
    final public function __serialize():array
    {
        if($this->db instanceof Pdo)
        static::throw('cannotSerializeDbObject');

        return parent::__serialize();
    }


    // setDb
    // lie une base de donnée à l'objet
    // si la base de donnée est dans inst, met le inst name, sinon met l'objet
    final protected function setDb(Pdo $db):void
    {
        if($db instanceof Db)
        {
            if($db->inInst())
            $this->db = $db->instName();

            else
            $this->db = $db;
        }

        else
        $this->db = $db;
    }


    // hasDb
    // retourne vrai si une base de donnée est lié à l'objet (via string ou objet)
    final public function hasDb():bool
    {
        return is_string($this->db) || $this->db instanceof Pdo;
    }


    // checkDb
    // envoie une exception si l'objet n'est pas lié à une base de donnée
    // retourne l'objet courant
    final public function checkDb():self
    {
        if(!$this->hasDb())
        static::throw('dbPropertyIsInvalid','objectUnusable');

        return $this;
    }


    // db
    // retourne l'objet base de données
    // retourne une erreur si le retour n'est pas instance de db
    final public function db():Pdo
    {
        $return = $this->db;

        if(is_string($return))
        $return = Db::instSafe($return);

        return static::checkClass($return,Pdo::class,'dbPropertyIsInvalid','objectUnusable');
    }


    // isLinked
    // retourne vrai si l'objet est lié
    final public function isLinked():bool
    {
        return $this->hasDb();
    }


    // checkLink
    // retourne this si l'objet est lié, envoie une exception sinon
    final public function checkLink():self
    {
        if($this->isLinked() === false)
        static::throw();

        return $this;
    }
}
?>