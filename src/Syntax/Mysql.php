<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm\Syntax;
use Quid\Orm;

// mysql
// class with static methods to generate MySQL syntax strings (compatible with MySQL and MariaDB)
class Mysql extends Orm\Syntax
{
    // config
    protected static array $config = [];
}

// init
Mysql::__init();
?>