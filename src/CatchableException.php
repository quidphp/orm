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
use Quid\Main;

// catchableException
// class used for a catchable database query exception
class CatchableException extends Exception implements Main\Contract\Catchable
{
    // config
    public static $config = [
        'code'=>34, // code de l'exception
        'query'=>false // affiche la query
    ];
}

// init
CatchableException::__init();
?>