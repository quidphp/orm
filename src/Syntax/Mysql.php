<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm\Syntax;
use Quid\Orm;

// mysql
// class with static methods to generate MySQL syntax strings (compatible with MySQL and MariaDB)
class Mysql extends Orm\Syntax
{
    // config
    public static $config = [];
}

// init
Mysql::__init();
?>