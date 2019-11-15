<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Orm;

// row
// class for testing Quid\Orm\Row
class Row extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormRow';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name_en','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',1521762409,2,12,2],[2,2,'james2',20,2,22,2]) === [1,2]);
        $tb = $db[$table];
        assert($tb instanceof Orm\Table);
        $row = $tb->row(1);
        $row2 = $tb->row(2);
        assert($row instanceof Orm\Row);
        foreach ($row as $key => $value) { }
        $logSql = $db['logSql'];
        assert($row['active'] instanceof Orm\Cell);
        assert($row['active']('get') === 1);
        assert($row['active']('label','%:') === 'Active:');

        // construct

        // toString

        // call

        // onInit

        // onRefreshed

        // onInserted

        // onUpdated

        // onCommitted

        // onDeleted

        // onCommittedOrDeleted

        // toArray
        assert($row->toArray()['id'] instanceof Orm\Cell);

        // cast
        assert($row->_cast() === 1);

        // offsetGet
        assert($row['active']->value() === 1);

        // offsetSet
        assert(($row['active'] = 2) === 2);
        assert($row['active']->value() === 2);
        assert($row['active'] = 3);
        assert($row['active']->value() === 3);

        // offsetUnset
        unset($row['active']);
        assert($row['active']->value() === null);
        assert($row['active'] = 3);
        unset($row['active']);

        // arr

        // isLinked
        assert($row->isLinked());

        // alive
        assert($row->alive());

        // hasCell
        assert($row->hasCell('id','dateAdd',$row2->cell('id')));
        assert($row->hasCell($row2->cell('id')->col()));
        assert($row->hasCell($row2->cell('id')));

        // hasChanged
        assert($row->hasChanged());
        $row->cell('active')->reset();
        assert(!$row->hasChanged());

        // isUpdateable
        assert($row->isUpdateable());

        // isDeleteable
        assert($row->isDeleteable());

        // hasRelationChilds
        assert(!$row->hasRelationChilds());

        // sameRow
        assert($row2->sameRow($row2->cell('id')));

        // setPrimary

        // primary
        assert($row->primary() === 1);
        $get = $row->cells()->keyValue();

        // id
        assert($row->id() === 1);

        // attrRef
        assert(count($row->attr()) >= 18);
        assert(!$row->isAttrNotEmpty('test'));
        assert($row->isAttrNotEmpty('priority'));

        // attrPermissionRolesObject

        // pointer
        assert($row->pointer() === 'ormRow-1');

        // value
        assert(count($row->value()) === 10);
        assert($row->value()['dateAdd'] === 1521762409);

        // label
        assert($db['user'][1]->label() === 'User #1');
        assert($db['user'][1]->label('%:','fr') === 'Utilisateur #1:');

        // description
        assert($db['user'][1]->description() === null);

        // cellsNew

        // cellsLoad

        // cellsRefresh
        assert($row->cellsRefresh(['id'=>1,'active'=>3,'bla'=>'megh']) === $row);
        assert($row->cells()->sets(['id'=>1,'active'=>3]) === $row->cells());

        // cells
        assert($row->cells() instanceof Orm\Cells);
        assert($row->cells('id')->isCount(1));

        // cellsClass
        assert(is_a($row->cellsClass(),Orm\Cells::class,true));

        // cellClass
        assert(is_a($row->cellClass($tb['id']),Orm\Cell::class,true));

        // cellMake

        // cell
        assert($row->cell('id') instanceof Orm\Cell);

        // cellPattern
        assert($row->cellPattern('name')->name() === 'name_en');
        assert($row->cellPattern('namez') === null);
        assert($row->cellPattern('name','*_de')->name() === 'name_de');
        assert($row->cellPattern('name','fr') === null);

        // cellValue
        assert($row->cellValue('id') === 1);

        // segment
        assert($row->segment('[name_%lang%] - [id] [dateAdd]') === 'james - 1 1521762409');
        assert($row->segment('[name_%lang%] - [id] [dateAdd]',true) === 'james - 1 March 22, 2018 19:46:49');

        // keyValue
        $row['active'] = 2;
        $row['name_en'] = 'bla';
        assert($row->keyValue('id','name_[lang]') === [1=>'bla']);
        assert($row->keyValue('id','dateAdd') === [1=>1521762409]);
        assert($row->keyValue('id',['lol','dateAdd']) === [1=>1521762409]);
        assert($row->keyValue('id',['lol','dateAdd'],true) === [1=>'March 22, 2018 19:46:49']);

        // relationKeyValue
        assert($row->relationKeyValue() === 'bla (#1)');

        // relationChilds
        assert($row->relationChilds() === []);

        // isActive
        assert($row->isActive(2));
        assert($logSql->insert(['type'=>1])->isActive());

        // deactivate
        $row['date']->set(time());
        assert($row->deactivate() === 1);

        // isVisible
        assert(!$row->isVisible());

        // cellActive
        assert($row->cellActive()->name() === 'active');
        assert($logSql->insert(['type'=>1])->cellActive() === null);

        // cellKey
        assert($row->cellKey()->name() === 'id');

        // cellName
        assert($row->cellName()->name() === 'name_en');
        assert($row->cellName()(2) === 'bl');
        assert($row->cellName('de')->name() === 'name_de');

        // cellContent
        assert($row->cellContent()->name() === 'content_en');
        assert($row->cellContent()(true) === '');

        // cellsDateCommit
        assert(count($row->cellsDateCommit()) === 2);
        assert(count($row->cellsDateCommit()['dateAdd']) === 2);

        // cellsOwner
        assert($row->cellsOwner()->isCount(2));

        // lastDateCommit
        assert(count($row->lastDateCommit()) === 2);

        // namePrimary
        assert($row->namePrimary() === 'bla (#1)');

        // slugName
        assert($row->slugName() === 'bla');

        // toRows
        assert($row->toRows()->first() === $row);

        // refresh
        $rowz = $tb->insert(['date'=>time(),'name_en'=>'well']);
        assert($db->delete($tb,$rowz) === 1);
        assert($rowz->isLinked());
        $rowz->refresh();
        assert(!$rowz->isLinked());

        // duplicate
        assert($row->duplicate() instanceof Orm\Row);
        assert($row->duplicate() !== $row);

        // get
        assert($row->get()['dateAdd'] === 'March 22, 2018 19:46:49');
        assert(count($row->get()) === 10);
        assert(count($row->get('id','active')) === 2);

        // set
        assert($row->set(['active'=>1]) === $row);
        assert($row->update() === 1);

        // preValidate
        assert($row->preValidate(['date'=>'a','active'=>['a']],['strict'=>false,'com'=>true]) === ['active'=>['a']]);
        assert(strlen($row->db()->com()->flush()) === 253);

        // setUpdateMethod

        // setUpdate
        assert($row->setUpdate(['active'=>null]) === 1);
        assert($row->setUpdate(['active'=>1],['com'=>true]) === 1);
        assert($row->setUpdate(['active'=>1],['com'=>true]) === 0);
        $row->db()->com()->flush();

        // setUpdateChanged

        // setUpdateValid
        assert($row->setUpdateValid(['active'=>1],['com'=>true]) === 0);
        assert(strlen($row->db()->com()->flush()) === 178);
        assert($row->setUpdateValid(['active'=>null],['com'=>true]) === 1);
        assert(strlen($row->db()->com()->flush()) === 183);
        assert($row->setUpdateValid(['active'=>'a','name_en'=>'ok'],['com'=>true]) === 1);
        assert(strlen($row->db()->com()->flush()) === 353);
        $row['active'] = 1;

        // update
        assert($row->hasChanged());
        assert($row->update() === 1);
        assert(!$row->hasChanged());
        assert($row->update() === 0);
        $row['active'] = null;

        // updateChanged
        assert($row->updateChanged() === 1);
        $row['name_en'] = 'blaz';
        assert($row->hasChanged());
        assert($row->cell('name_en')->valueInitial() === 'ok');
        assert($row->updateChanged() === 1);
        assert($row->cell('name_en')->valueInitial() === 'blaz');
        assert(!$row->hasChanged());

        // updateValid
        assert($row->updateValid(['com'=>true]) === 0);
        assert(strlen($row->db()->com()->flush()) === 178);
        $row['active'] = 1;
        assert($row->updateValid() === 1);

        // updateCom

        // delete
        assert($row->isLinked());
        assert($tb->row(1) === $row);
        assert($row->delete(['com'=>true]) === 1);
        assert(strlen($tb->db()->com()->flush()) === 183);
        assert(!$tb->hasRow(1));
        assert($tb->row(1) === null);
        assert(!$row->isLinked());

        // deleteOrDeactivate
        $row4 = $tb->insert(['date'=>time(),'name_en'=>'sure']);
        assert($row4->deleteOrDeactivate() === 1);

        // terminate

        // unlink

        // writeFile

        // insertFinalValidate

        // updateFinalValidate

        // commitFinalValidate

        // initReplaceMode

        // getOverloadKeyPrepend
        assert(Orm\Row::getOverloadKeyPrepend() === null);

        // tableAccess
        $row3 = $tb->insert(['date'=>time(),'name_en'=>'sure']);
        assert($row3->isLinked());
        assert($row3->checkLink() === $row3);
        assert($row->hasDb() === false);
        assert($row2->hasDb());
        assert($row2->checkDb());
        assert($row2->sameTable($row2->cell('id')));
        assert($row2->tableName() === $table);
        assert($row2->table() instanceof Orm\Table);
        assert($row2->db() instanceof Orm\Db);
        assert($row2->tables() instanceof Orm\Tables);

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>