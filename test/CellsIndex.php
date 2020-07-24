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

// cellsIndex
// class for testing Quid\Orm\CellsIndex
class CellsIndex extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCells';
        $table2 = 'ormRows';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);
        assert($db->inserts($table,['id','name_en','dateAdd','userAdd','dateModify','userModify'],[1,'james',10,2,12,2],[2,'james2',20,2,22,2],[3,'james2',30,2,32,2]) === [1,2,3]);
        assert($db->inserts($table2,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,2,'james2',20,2,22,2],[3,3,'james2',30,2,32,2]) === [1,2,3]);
        $tb = $db->table($table);
        $row1 = $tb[1];
        $tb2 = $db->table($table2);
        $row2 = $tb2[2];

        // map
        $cells = new Orm\CellsIndex($row1['id'],$row2['id'],$row1['name_en']);
        assert($cells->isCount(3));
        assert($cells->isTable($table));
        assert(!$cells->isTable('james'));
        assert(!$cells->sameTable());
        assert($cells->table()->name() === 'ormCells');
        assert(count($cells->groupByTable()) === 2);
        assert($cells->filterByTable($table) instanceof Orm\Cells);

        // cleanup
        $tb->rowsUnlink();
        $tb2->rowsUnlink();
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);

        return true;
    }
}
?>