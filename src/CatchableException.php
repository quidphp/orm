<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// catchableException
// class used for a catchable database query exception
class CatchableException extends Exception implements Main\Contract\Catchable
{
    // config
    protected static array $config = [
        'code'=>34, // code de l'exception
        'query'=>false // affiche la query
    ];
}

// init
CatchableException::__init();
?>