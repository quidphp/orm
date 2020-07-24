<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Orm;

// colsIndex
// class for testing Quid\Orm\ColsIndex
class ColsIndex extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCols';
        $tb = $db[$table];
        $tb2 = $db['ormDb'];
        $col1 = $tb['id'];
        $email = $tb['email'];
        $col2 = $tb2['id'];

        // cols
        $cols = new Orm\ColsIndex($col1,$email,$col2);
        assert($cols->isCount(3));
        assert($cols->isTable($table));
        assert(!$cols->isTable('james'));
        assert(!$cols->sameTable());
        assert($cols->table()->name() === 'ormCols');
        assert(count($cols->groupByTable()) === 2);
        assert($cols->filterByTable($table) instanceof Orm\Cols);

        return true;
    }
}
?>