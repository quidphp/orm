<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// exception
// class used for a database query exception
class Exception extends Main\Exception
{
    // config
    protected static array $config = [
        'code'=>33, // code de l'exception
        'query'=>false // affiche la query
    ];


    // dynamique
    protected ?string $query = null; // conserve la requête sql sous forme de string


    // setQuery
    // lie la query à l'exception
    final public function setQuery(string $value)
    {
        $this->query = $value;

        return $this;
    }


    // getQuery
    // retourne la query
    final public function getQuery():?string
    {
        return $this->query;
    }


    // content
    // retourne la query si showQuery est true, sinon retourne null
    final public function content():?string
    {
        return ($this->getAttr('query') === true)? $this->query:null;
    }


    // showQuery
    // affiche ou non la requête sql dans le message
    final public static function showQuery(bool $value):void
    {
        static::$config['query'] = $value;
    }
}

// init
Exception::__init();
?>