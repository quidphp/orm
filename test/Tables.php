<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Orm;

// tables
// class for testing Quid\Orm\Tables
class Tables extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $dbName = 'quid995';
        $table = 'ormTables';
        $table2 = 'ormDb';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,1,'james2',20,2,22,2]) === [1,2]);
        assert($db->inserts($table2,['id','name_[lang]','dateAdd'],[1,'james',10],[2,'james2',11],[3,'james3',10]) === [1,2,3]);
        $tables = $db->tables();
        $tb = $tables[$table];
        $tb2 = $tables[$table2];
        assert(count($tables) > 10);
        $tb3 = $db['ormDb'];
        $clone = clone $tables;

        // construct

        // toString

        // onPrepareKey

        // onPrepareReturns
        assert($tables->gets($table,'session') instanceof Orm\Tables);
        assert($tables->gets($tb,'session')->count() === 2);

        // cast
        assert(!empty($tables->_cast()));

        // offsetSet

        // hasChanged
        assert(!($tables->hasChanged()));

        // names
        assert(is_array($tables->names()));

        // db
        assert($tables->db() instanceof Orm\Db);

        // add

        // labels
        assert(Base\Arr::isMulti($tables->labels(null,['error'=>false])));

        // descriptions
        assert(Base\Arr::isMulti($tables->descriptions()));

        // hasPermission
        assert($tables->hasPermission('update')->count() !== $tables->count());
        assert($tables->hasPermission('update','insert')->count() !== $tables->count());

        // search
        assert(count($tables->search('james')) < count($tables));

        // changed
        $tb[1]['active']->set(null);
        $tb2[1]['dateAdd']->set(2);
        assert($tables->changed()->isCount(2));
        $tb2[1]['dateAdd']->reset();

        // total
        assert(count($tables->total()) === 4);
        assert($tables->total(true)['row'] !== $tables->total()['row']);
        $tables->total(true,true);
        $count = count($tb->db()->history()->keyValue());
        $tables->total(true,true);
        assert($count === count($tb->db()->history()->keyValue()));

        // info
        assert(Base\Arrs::is($tables->info()));

        // searchable
        assert(count($tables->searchable()) < count($tables));

        // searchMinLength
        assert($tables->searchMinLength() === 3);

        // isSearchTermValid
        assert($tables->gets('ormCell','ormCells','ormCol','ormCols')->isSearchTermValid('okz'));
        assert(!$tables->isSearchTermValid('k'));

        // truncate

        // keyParent
        assert($tables->keyParent()['ormRowsIndex'] === 'ormRows');

        // hierarchy
        $ormTable = $tables['ormTable'];
        $deep = $tables['ormRowsIndexDeep'];
        $count = count($tables->hierarchy(true));
        assert(count($tables->hierarchy(false)) === ($count + 2));

        // childsRecursive
        assert($tables->childsRecursive('ormTable') === null);
        assert(count($tables->childsRecursive('ormTable',false)) === 1);
        assert($tables->childsRecursive('ormRowsIndex',false) === ['ormRowsIndexDeep'=>null]);
        assert(!empty($tables->childsRecursive('doesNotExist',false)));
        assert(empty($tables->childsRecursive('doesNotExist')));

        // tops
        assert(!in_array('doesNotExist',$tables->tops()->keys(),true));
        assert(count($tables->tops()) > count($tables->siblings('user')));

        // parent
        assert($tables->parent($ormTable)->name() === 'ormDb');
        assert($tables->parent($tb3) === null);
        assert($tb3->parent() === 'doesNotExist');

        // top
        assert($tables->top($ormTable)->name() === 'ormDb');
        assert($tables->top($deep)->name() === 'ormRows');
        assert($tables->top($tb3) === null);

        // parents
        assert(count($tables->parents($deep)) === 2);

        // breadcrumb
        assert(count($tables->breadcrumb($deep)) === 3);

        // siblings
        assert(count($tables->siblings('user')) < count($tables));

        // childs
        assert(count($tables->childs('ormDb')) === 1);

        // relationChilds
        assert($tables->relationChilds('tables',1) === []);

        // keyClassExtends
        assert(count($tables::keyClassExtends()) === 5);

        // mapObj
        assert($tables->pair('hasPermission','delete')['email'] === true);
        assert($tables->filterReject($tables)->isEmpty());
        assert($tables->filterReject($tables) !== $tables);
        assert($tables->filterReject($tables->gets('user','session'))->count() === ($tables->count() - 2));
        assert($tables->filterReject($tables['user'],$tables['session'])->count() === ($tables->count() - 2));
        assert($tables->filterReject('user','session')->count() === ($tables->count() - 2));
        assert($tables->filter(fn($table) => $table->name() === 'ormTable')->isCount(1));
        assert($tables->find(fn($table) => $table->name() === 'ormTable') instanceof Orm\Table);
        assert($tables->filter(fn($table) => !$table->hasPermission('update'))->isCount(7));
        assert(count($tables->group('name')) === 25);
        $sort = $clone->sortBy('priority',false);
        assert($sort->first()->name() === 'ormDb');
        assert(!($tables->first()->name() === 'ormDb'));
        assert($sort !== $tables);
        $sort = $clone->sortDefault();
        assert($sort->first()->name() === 'main');
        assert($sort === $clone);

        // map
        assert($tables->get(0)->name() === 'main');
        assert($tables->get([1000,'ormCell'])->name() === 'ormCell');
        assert($tables->get($tb->col('id')) === $tb);
        assert($tables->get($tb[1]) === $tb);
        assert($tables->get($tb[1]['id']) === $tb);
        assert($tables->exists($tb->col('id')));
        assert($tables->exists($tb[1]));
        assert($tables->exists($tb[1]['id']));
        assert($tables->get($table) instanceof Orm\Table);
        assert($tables->get($tb) instanceof Orm\Table);
        assert($tables[$table] === $tb);
        assert($tables->exists($table));
        assert($tables->exists($tb));
        assert(!$tables->exists('NPE'));
        assert(count($tables->keys()) === 25);
        assert(count($tables->gets($table,'user')) === 2);
        assert($tables->in($tb));
        assert($tables->exists('user',$tb));
        assert($tables->get('OK') === null);
        $sort = $clone->sort(false);
        assert(assert($sort) !== $clone);

        // readOnly
        assert($clone->empty()->isEmpty());
        assert($clone->add(...$tables->values())->isNotEmpty());
        assert(!$clone->isReadOnly());
        $count = $clone->count();
        assert(($gets = $clone->gets($tb,'user','session'))->isCount(3));
        assert($clone->unset($tb,'user','session')->count() === ($count - 3));
        assert($clone->add(...$gets->values())->count() === $count);
        assert($clone->remove(...$gets->values())->count() === ($count - 3));
        assert($clone->add($gets)->count() === $count);
        assert($clone->remove($gets)->count() === ($count - 3));
        assert($clone->add($gets)->count() === $count);
        assert($clone->unset($gets)->count() === ($count - 3));

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);
        $tables = null;

        return true;
    }
}
?>