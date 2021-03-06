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

// rows
// class for testing Quid\Orm\Rows
class Rows extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormRows';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,1,'james2',20,3,22,3],[3,1,'james2',30,4,32,4]) === [1,2,3]);
        $tb = $db->table($table);
        $tb->rowsLoad();

        // construct
        $rows = $tb->rowsNew();
        $rows->add(...$tb->rows()->toArray());
        assert($rows->count() === 3);
        assert($rows !== $tb->rows());
        assert($tb->rows() instanceof Orm\Rows);
        assert($rows instanceof Orm\Rows);
        $a = $rows[1];
        $b = $rows[2];
        $c = $rows[3];
        assert($rows->unset($a,$b,3,2)->isEmpty());
        assert($rows->add(...$tb->rows()->toArray()));
        assert($rows->unset($c)->count() === 2);
        foreach ($rows as $key => $value) { }
        $rows[] = $c;

        // onPrepareKey
        assert($rows->get(1) instanceof Orm\Row);
        assert($rows->get(2) instanceof Orm\Row);
        assert($rows->get($a) instanceof Orm\Row);
        assert($rows->get('bla') === null);
        assert($rows->get($a['id']) === $a);
        assert(!$rows->in($a['id']));
        assert($rows->get(['id'=>2])->primary() === 2);
        assert($rows->get(['id'=>1,'name'=>'james'])->primary() === 1);
        assert($rows->get(['id'=>2,'name'=>'james']) === null);

        // rowsMap
        $gets = $rows->gets(1,2);
        assert($gets !== $rows);
        assert($gets->isCount(2));
        assert($rows->gets(1,2,1000,'OK')->isCount(2));
        assert(!$rows->in(1,2,$a));
        assert($rows->exists(1,2,$a));
        assert(!$rows->in(1,2,$a,1000));
        assert(!$rows->exists(1,2,$a,1000));
        assert($rows->index(0) === $a);
        assert($rows->indexes(-1)->get(3) instanceof Orm\Row);
        assert($rows->slice(2,3)->count() === 2);
        assert($rows->sliceIndex(0,3)->count() === 3);
        assert($rows->_cast() === [1,2,3]);
        assert(!$rows->hasChanged());
        $rows[1]['name'] = 2;
        $rows[2]['name'] = 2;
        assert($rows->hasChanged());
        assert($rows->alive());

        // isTable
        assert(!$rows->isTable('session'));
        assert($rows->isTable($table));
        assert($rows->isTable($tb));

        // hasCell
        assert($rows->hasCell('active'));
        assert(!$rows->hasCell('activez'));

        // checkCell
        assert($rows->checkCell('active'));

        // table
        assert($rows->table() instanceof Orm\Table);

        // addMode

        // add
        assert($rows->empty()->count() === 0);
        assert($rows->refresh() instanceof Orm\Rows);
        assert($rows->table() === null);
        assert($rows->add($c)->get(3) instanceof Orm\Row);
        assert($rows->first()->primary() === 3);

        // addSort
        assert($rows->addSort($b,$a)->count() === 3);
        assert($rows->first()->primary() === 1);

        // rowsMap
        $uni = count($tb->db()->history()->keyValue());
        assert($rows->getRefresh(1) instanceof Orm\Row);
        assert(count($tb->db()->history()->keyValue()) === ($uni + 1));
        assert($rows->getsRefresh(1,2)->isCount(2));
        assert(count($tb->db()->history()->keyValue()) === ($uni + 2));
        $rows[1]['name'] = 2;
        $rows[2]['name'] = 2;
        assert($rows->changed()->count() === 2);
        $rows[1]['name'] = 'james';
        $rows[2]['name'] = 'james2';
        $rows->get(3)->unlink();
        $rows->clean();
        assert(count($rows->cell('id')) === 2);
        assert($rows->cell('id') instanceof Orm\CellsIndex);
        assert($rows->cellNotEmpty('name') instanceof Orm\CellsIndex);
        assert(count($rows->cellNotEmpty('name')) === 2);
        $rows[2]['name'] = '';
        assert(count($rows->cellNotEmpty('name')) === 1);
        assert($rows->cellFirstNotEmpty('name')->value() === 'james');
        $rows[2]['name'] = 'james2';
        $rows->setCell('name','james3');
        assert($rows->cellValue('name') === [1=>'james3',2=>'james3']);
        assert($rows->hasChanged());
        $rows->resetCell('name');
        assert($rows->cellValue('name') === [1=>'james',2=>'james2']);
        assert(!$rows->hasChanged());
        $rows->unsetCell('name');
        assert($rows->cellValue('name') === [1=>null,2=>null]);
        assert($rows->hasChanged());
        $rows->setCell('name','james3');

        // keyValue
        assert($rows->keyValue('id','name') === [1=>'james3',2=>'james3']);
        assert($rows->keyValue(0,3) === [1=>2,2=>3]);
        assert($rows->keyValue('id',['james','active']) === [1=>1,2=>1]);

        // rowsMap
        assert($rows->cellValue('id') === [1=>1,2=>2]);
        assert($rows->cellValue('name') === [1=>'james3',2=>'james3']);
        assert(is_int($rows->cellValue('dateAdd',false)[1]));
        assert(is_string($rows->cellValue('dateAdd',true)[1]));
        assert($rows->segment('[name] [id]') === [1=>'james3 1',2=>'james3 2']);
        $rows->add($tb[3]);
        assert($rows->where([[$tb->col('name'),true]])->isCount(3));
        assert($rows->where([['name','empty']])->isEmpty());
        assert($rows->where([['name',false]])->isEmpty());
        assert($rows->where([['name','notEmpty']])->isCount(3));
        $tb[3]['name']->set(null);
        assert($rows->where([['name',null]])->isCount(1));
        assert($rows->where([['name','notNull']])->isCount(2));
        assert($rows->where(['name'=>'james3'])->isCount(2));
        assert($rows->where(['name'=>'james3','id'=>1])->isCount(1));
        assert($rows->where([['id','!',2]])->isCount(2));
        assert($rows->where([['id','>',1],'name'=>'james3'])->isCount(1));
        assert($rows->where([['id','>',1],'name'=>'james'])->isEmpty());
        $tb[3]['name']->set('james4');
        assert($rows->where([['dateAdd','>=',30]])->isCount(1));
        assert($rows->order(['userModify'=>'desc'])->first()->id() === 3);
        assert($rows->order(['userModify'=>true])->first()->id() === 1);
        assert($rows->order(['name'=>'asc','id'=>'desc'])->keys() === [2,1,3]);
        assert(count($rows->group('cellValue','name')) === 2);
        assert($rows->limit(3)->first()->id() === 1);
        assert($rows->limit(1,2)->first()->id() === 2);
        assert($rows->limit(1,2)->isCount(2));
        $rows->unset(3);

        // alive
        assert($rows->alive());

        // refresh
        $uni = count($tb->db()->history()->keyValue());
        assert($rows->refresh() instanceof Orm\Rows);
        assert(count($tb->db()->history()->keyValue()) === ($uni + 1));

        // rowsMap
        assert($rows->clean()->count() === 2);
        $one = $tb[1];
        assert($rows->unlink()->count() === 0);
        assert(!$one->isLinked());
        $rows->add(...$tb->rowsLoad()->toArray());
        $rows->setCell('name','james3');
        assert($rows->unset($tb[3])->isCount(2));
        assert($rows->isCount(2));
        assert($tb->rows()->isCount(3));
        assert($rows->update() === [1=>1,2=>1]);
        $rows->setCell('name','james4');
        assert($rows->updateChanged() === [1=>1,2=>1]);
        $rows->setCell('name','james5');
        assert($rows->updateRowChanged() === [1=>1,2=>1]);
        $rows[1]['name'] = 'james6';
        assert($rows->updateRowChanged() === [1=>1]);
        assert($rows->updateRowChanged() === []);
        assert($rows->delete() === 2);
        assert($rows->count() === 0);
        assert($rows->delete() === null);

        // getOverloadKeyPrepend

        // readOnly
        assert($tb->rows()->isReadOnly());
        assert(!$tb->rows()->clone()->isReadOnly());
        assert(!$rows->isReadOnly());
        assert($rows->add($tb->rows())->isCount(1));
        assert($rows->unset(3)->isEmpty());

        // cleanup
        $db->autoSave();
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>