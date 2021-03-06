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

// rowsIndex
// class for testing Quid\Orm\RowsIndex
class RowsIndex extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormRowsIndex';
        $table2 = 'ormRows';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);
        assert($db->inserts($table,['id','activez','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,2,'james2',20,2,22,2],[3,3,'james2',30,2,32,2]) === [1,2,3]);
        assert($db->inserts($table2,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,2,'james2',20,2,22,2],[3,3,'james2',30,2,32,2]) === [1,2,3]);
        $tb = $db->table($table);
        $tb->rowsLoad();
        $tb2 = $db->table($table2);
        $tb2->rowsLoad();
        $rows = new Orm\RowsIndex(...$tb->rows()->toArray());
        assert(Base\Arr::isSequential($rows->toArray()));

        // hasCell
        assert($rows->hasCell('id'));
        assert(!$rows->hasCell('active'));
        assert(count($rows->cell('id')) === 3);

        // primaries
        assert($rows->primaries() === [$table=>[1,2,3]]);

        // map
        assert($rows->isTable($table));
        assert($rows->isTable($tb));
        assert(!$rows->isTable('session'));
        assert($rows->sameTable());
        $rows->add(...$tb2->rows()->toArray());
        assert($rows->isTable($tb2));
        assert(count($rows->primaries()) === 2);
        assert($rows->gets(0,1,2) instanceof Orm\RowsIndex);
        assert($rows->gets(0,1,2)->get(0) instanceof Orm\Row);
        assert(!$rows->sameTable());
        $rows2 = new Orm\RowsIndex();
        assert($rows2->add($tb->rows(),$tb2->rows())->isCount(6));
        assert($rows->filterByTable($tb)->isCount(3));
        assert($rows->filterByTable('LOL') === null);
        assert($rows->filterByTable($tb) instanceof Orm\Rows);
        assert(!$rows->filterByTable($table) instanceof Orm\RowsIndex);
        assert(count($rows->groupByTable()) === 2);

        // alive
        assert($rows->alive());

        // refresh
        assert($rows->refresh() === $rows);
        assert($rows->refresh() instanceof Orm\RowsIndex);

        // rows
        assert($rows->sequential() === $rows);
        $sort = $rows->sortBy('primary',false);
        assert(Base\Arr::isSequential($sort->keys()));
        assert($sort !== $rows);
        assert($rows->delete() === 6);
        assert($db->inserts($table,['id','activez','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,11,12,13],[2,2,'james3',20,21,22,23],[3,3,'james2',30,31,32,33]) === [1,2,3]);
        assert($db->inserts($table2,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,11,12,13],[2,2,'james3',20,21,22,23],[3,3,'james2',30,31,32,33]) === [1,2,3]);
        assert($rows->add($tb->rows(1,2,3))->isCount(3));
        assert($rows->add($tb2->rows(1,2,3))->isCount(6));
        assert(count($rows->group('cellName')) === 3);
        assert($rows->order(['name'=>'DESC'])->first()['name']->value() === 'james3');
        assert($rows->limit(2,5)->isCount(4));
        assert($rows->where([['name','!','james'],['id','>',2]])->isCount(2));
        assert(count($rows->keyValue(0,1)) === 3); // collision de id
        assert(count($rows->segment('[id] [name]')) === 6);

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);

        return true;
    }
}
?>