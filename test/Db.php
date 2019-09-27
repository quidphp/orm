<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Main;
use Quid\Orm;

// db
// class for testing Quid\Orm\Db
class Db extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $db->autoSave();
        $table = 'ormDb';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert(Orm\Db::isInst($db->instName()));
        assert($db->inserts($table,['id','name_[lang]','dateAdd'],[1,'james',10],[2,'james2',11],[3,'james3',10]) === [1,2,3]);
        $tb = $db[$table];
        foreach ($db as $key => $value) { }

        // construct

        // onBeforeMakeStatement

        // onAfterMakeStatement

        // onCloseDown

        // connect

        // disconnect

        // arr
        $count = count($db->tables()->toArray());
        assert(count($db) === $count);
        assert($db[$table] instanceof Orm\Table);
        assert(isset($db[$table]));
        assert(!empty($db[$table]));
        assert(!array_key_exists($table,$db));
        $i = 0;
        foreach ($db as $key => $value)
        {
            assert(is_string($key) && $value instanceof Orm\Table);
            $i++;
        }
        assert($i === $count);
        assert(is_array($db) === false);

        // offsetGet
        assert($db[$table] === $tb);
        assert($db[$tb] === $tb);

        // offsetSet

        // offsetUnset

        // off
        assert($db->off() === $db);

        // on
        assert($db->on() === $db);

        // hasPermission
        assert($db->hasPermission('select'));

        // checkPermission
        assert($db->checkPermission('create','session') === $db);

        // permission
        assert(count($db->permission()) >= 10);
        assert($db->permission()['select'] === true);
        assert(count($db->permission('session')) >= 10);

        // setPermission
        assert($db->setPermission(true) === $db);

        // setLog
        assert($db->setLog(true) === $db);
        assert($db->getOption('log') === true);

        // statementException
        
        // getExceptionClass
        
        // setExceptionClass
        
        // makeTables

        // tablesLoad

        // tablesColsLoad

        // tableMake

        // tables
        assert($db->tables() instanceof Orm\Tables);

        // makeClasse

        // classe
        assert($db->classe() instanceof Orm\Classe);

        // makeSchema

        // schema
        assert($db->schema() instanceof Orm\Schema);

        // setLang

        // hasLang
        assert($db->hasLang());

        // lang
        assert($db->lang() instanceof Main\Lang);

        // label
        assert($db->label() === '[db/label/'.$db->dbName().']');
        assert($db->label('%:') === '[db/label/'.$db->dbName().']:');

        // description
        assert($db->description(null,null,'fr') === null);

        // setRole

        // hasRole
        assert($db->hasRole());

        // role
        assert($db->role() instanceof Main\Role);

        // setCom

        // hasCom
        assert($db->hasCom());

        // com
        assert($db->com() instanceof Main\Com);

        // getSqlOption
        assert(count($db->getSqlOption()) === 4);
        assert(count($db->getSqlOption(['primary'=>'beurp'])) === 4);
        assert(count($db->getSqlOption(['primaryz'=>'beurp'])) === 5);

        // getTableDefault
        assert($db->getTableDefault($table) === ['what'=>['id'],'where'=>['id'=>2]]);
        assert($db->select(true,$table,true) === [['id'=>2]]);

        // hasTable
        assert($db->hasTable('ormDb'));
        assert(!$db->hasTable('ormDbz'));
        assert($db->hasTable($tb));
        assert($db->hasTable(0,1,'ormCell'));
        assert(!$db->hasTable(0,1,2,10000));

        // table
        assert($db->table('ormDb') === $tb);
        assert($db->table('ormDb') === $tb);
        assert($db->table($tb) === $tb);
        assert($db->table(0)->name() === 'main');
        assert($db->table(['what','lol','ormCell'])->name() === 'ormCell');

        // query
        assert($db->query(['type'=>'select','id'=>2,'whereOnlyId'=>true,'table'=>$table],'rowOut') instanceof Orm\Row);
        assert($db->query(['type'=>'select','id'=>2,'whereOnlyId'=>true,'table'=>$table],'rowOut') === null);

        // fromPointer
        assert($db->fromPointer('ormDb/2111') === null);
        assert($db->fromPointer('ormDb/2','/') instanceof Orm\Row);
        assert($db->fromPointer('ormDb/2','/',['core','ormDb']) instanceof Orm\Row);
        assert($db->fromPointer('ormDb/2','/',['core','ormDbz']) === null);
        assert($db->fromPointer('ormDb/2','-') === null);

        // prepareRow
        assert($db->prepareRow(Orm\Syntax::makeSelect(['*',$table,['name_[lang]'=>'james']]),'rows')['id'] === [1]);
        assert($db->prepareRow(Orm\Syntax::makeSelect(['*',$table,['name_[lang]'=>'james']]),'row')['id'] === 1);

        // row
        assert($db->row($tb,2) instanceof Orm\Row);
        assert($db->row($tb,[2,'dateAdd'=>0]) === null);
        assert($db->row($tb,[2,'dateAdd'=>11]) instanceof Orm\Row);
        assert($db->row($tb,[2,'dateAdd'=>11]) === $db->row($tb,2));
        $db->update($tb,['dateAdd'=>12],2);
        assert($db->row($tb,[2])['dateAdd']->value() === 11);

        // rowRefresh
        assert($db->rowRefresh($tb,[2])['dateAdd']->value() === 12);
        assert($db->rowRefresh($tb,[2,'dateAdd'=>12]) === $db->row($tb,2));

        // rowIn
        assert($db[$table][1] instanceof Orm\Row);
        $tb->rowsUnlink(2);
        assert($db->rowIn($tb,2) === null);
        assert($db->rowIn($tb,1) instanceof Orm\Row);
        assert($db->row($tb,2) instanceof Orm\Row);
        $tb->rowsUnlink(2);

        // rowInRefresh
        $count = count($db->history()->keyValue());
        assert($db->rowInRefresh($tb,2) === null);
        assert($db->rowInRefresh($tb,1) instanceof Orm\Row);
        assert(count($db->history()->keyValue()) === $count + 1);

        // rowOut
        $tb->rowsUnlink(2);
        assert($db->rowOut($tb,['id'=>2]) instanceof Orm\Row);
        assert($db->rowOut($tb,['id'=>2]) === null);

        // rows
        assert($db->inserts($table,['id','name_[lang]','dateAdd'],[10,'james10',10],[11,'james11',11],[12,'james12',10]) === [10,11,12]);
        assert(count($db->rows($tb,[['id','>=',10]])) === 3);
        assert($tb->rows(3)->isCount(1));
        assert(count($tb->rows()) === 6);

        // rowsRefresh
        $db->update($tb,['dateAdd'=>13],2);
        $x = $db->rows($tb,2)[2];
        assert($x['dateAdd']->value() === 12);
        assert($db->rowsRefresh($tb,2)[2]['dateAdd']->value() === 13);
        assert($x['dateAdd']->value() === 13);
        $rowz = $tb->rows(10,11,12);
        assert($rowz->delete() === 3);
        assert($tb->rows()->count() === 3);

        // rowsIn
        assert($db->rowsIn($tb,2)->isCount(1));
        assert($db->rowsIn($tb,2)->isCount(1));

        // rowsInRefresh
        assert($db->rowsInRefresh($tb,2)->isCount(1));

        // rowsOut
        $tb->rowsUnlink(2);
        assert($db->rowsOut($tb,2)->isCount(1));
        assert($db->rowsOut($tb,2)->isCount(0));

        // sql
        assert($db->sql() instanceof Orm\Sql);

        // reservePrimaryDelete

        // setAutoSave
        assert($db->setAutoSave(true) === $db);

        // autoSave
        assert(count($db->autoSave()) === 0);

        // tableAttr
        assert($db->tableAttr('ormTable') === ['test'=>'ok']);
        assert($db->tableAttr('ormDbz') === null);

        // colAttr
        assert($db->colAttr('activez') === null);

        // info
        assert(count($db->info()) === 18);

        // isRowOutput
        assert(Orm\Db::isRowOutput('rowIn'));
        assert(Orm\Db::isRowOutput('rowsIn'));
        assert(!Orm\Db::isRowOutput('rowsInz'));

        // getRowOutputType
        assert(Orm\Db::getRowOutputType('rowIn') === 'row');
        assert(Orm\Db::getRowOutputType('rowsIn') === 'rows');

        // getPriorityIncrement
        assert(Orm\Db::getPriorityIncrement() === 10);

        // option
        assert(count($db->option()) === 18);

        // inst
        assert(Orm\Db::hasInst());
        assert(Orm\Db::isInst(0));
        assert(Orm\Db::isInst($db->instName()));
        assert($db->inInst());
        assert(!Orm\Db::isInst(1));
        assert(Orm\Db::inst($db->instName()) === $db);
        assert(Orm\Db::inst(0) === $db);
        assert(Orm\Db::inst($db) === $db);
        assert(count(Orm\Db::insts()) === 1);

        // pdo + cast
        $tables = $db->tables();
        $tb = $tables->get($table);
        $rows = $tb->rows();
        $col = $tb->cols()->get('id');
        $row = $tb->row(1);
        $cell = $row->cell('id');
        $sql = $db->sql()->select('*')->table('james')->where(2);

        // cast
        assert($db->_cast() === $db->name());
        assert(Base\Obj::cast([$db,$rows]) === [$db->name(),[1,3,2]]);
        assert(Base\Obj::cast([$col,$row,[$cell]]) === ['id',1,[1]]);
        assert(Base\Obj::casts(0,$col,$row,[$cell]) === ['id',1,[1]]);
        assert(Base\Html::div($cell,['col'=>$col,'data-ids'=>$rows,'table'=>$table,'style'=>['color'=>$col]]) === "<div col='id' data-ids='[1,3,2]' table='ormDb' style='color: id;'>1</div>");
        assert(Base\Date::time($row) === 1);
        assert(Base\Date::format('ymdhis',$row) === '1969-12-31 19:00:01');

        // true
        assert($db->setDebug(true) === $db);
        assert($db->select('*',$tb,['active'=>2])['sql'] === 'SELECT * FROM `ormDb` WHERE `active` = 2');
        assert($db->select('*',$tb,true)['sql'] === 'SELECT * FROM `ormDb` WHERE `id` = 2');
        assert($db->select('*',$tb,[true,'active'=>2])['sql'] === 'SELECT * FROM `ormDb` WHERE `id` = 2 AND `active` = 2');
        assert($db->select('*',$tb,[true,true,'active'=>2])['sql'] === 'SELECT * FROM `ormDb` WHERE `id` = 2 AND `active` = 2');
        assert($db->select('*',$tb,['active'=>2,true])['sql'] === 'SELECT * FROM `ormDb` WHERE `active` = 2 AND `id` = 2');
        assert($db->setDebug(false) === $db);

        // pdo
        assert(Orm\Db::isOutput('select','rowsIn'));
        assert(!Orm\Db::isOutput('insert','rowOut'));
        assert(!Orm\Db::isOutput('show','rows'));
        assert(Orm\Db::output('select','rowOut') === ['onlySelect'=>true,'selectLimit'=>1,'type'=>'rowOut']);
        assert(Orm\Db::output('show','rowOut') === null);
        assert(Orm\Db::selectLimit('row',['*','james',2])['limit'] === 1);
        assert(count(Orm\Db::selectLimit('rows',['*','james',2])) === 3);
        assert($db->selectColumn($col,$table,$row) === 1);
        assert($db->showTable($tb) === 'ormDb');
        assert(is_string(Base\Obj::cast($sql)));
        assert($db->make('select',['*',$tb,['id'=>3]])[0]['id'] === 3);
        assert($db->make('select',['*',$tb,['id'=>3]],'assoc')['id'] === 3);
        assert($db->makeSelect(['*',$tb,['id'=>3]])[0]['id'] === 3);
        assert($db->makeSelect(['*',$tb],'keyPairs') === $db->makeSelect([['id','name_[lang]'],$tb],'keyPairs'));
        assert($db->makeSelect(['*',$tb],['columns','arg'=>'name_[lang]']) === $db->makeSelect(['*',$tb],['columns','arg'=>1]));
        assert($db->makeInsert([$tb,['id'=>6,'name_[lang]'=>'ok','dateAdd'=>time()]]) === 6);
        assert($db->makeInsert([$tb,['id'=>100,'name_[lang]'=>'OK']],['beforeAfter'=>'assoc']) === ['before'=>null,'query'=>100,'after'=>['id'=>100,'name_en'=>'OK','dateAdd'=>null]]);
        assert($db->makeDelete([$tb,['id'=>6]]) === 1);
        assert(count($db->select(true,$tb,null,['id'=>'DESC'],'1,2')) === 2);
        assert($db->inserts($tb,[$tb['id'],$tb['name_[lang]'],'dateAdd'],[4,'james',Base\Date::mk(2017,1,5)]) === [4]);
        assert(count($db->select(true,$tb,[['dateAdd','day',Base\Date::mk(2017,1,5)]])) === 1);
        assert(count($db->select(true,$tb,[[$tb->col('dateAdd'),'year',Base\Date::mk(2017,3)]])) === 1);
        assert(count($db->select(true,$tb,[['dateAdd','month',Base\Date::mk(2017,1,20)]])) === 1);
        assert(count($db->select(true,$tb,[['name_[lang]','%like','ja'],'and',[$tb->col('name_[lang]'),'like%','s2']])) === 1);
        assert(count($db->select(true,$tb,[[$tb->col('id'),'or|findInSet',[1,2]]])) === 2);
        assert($db->makeDelete([$tb,['id'=>[4,100]]]) === 2);
        assert(Base\Arr::isIndexed($db->selectNum(true,$tb)));
        assert(count($db->selectNums(true,$tb)) === 3);
        assert($db->selectAssoc($tb->col('id'),$tb,1000) === null);
        assert(count($db->selectAssocs(['id','name_[lang]'],$tb)[0]) === 2);
        assert($db->selectAssocs($tb->col('id'),$tb,1000) === []);
        assert($db->selectAssocsUnique('*',$tb)[2]['name_en'] === 'james2');
        assert($db->selectAssocsKey($tb->col('name_[lang]'),'*',$tb)['james']['name_en'] === 'james');
        assert($db->selectAssocsPrimary('*',$tb)[1]['id'] === 1);
        assert($db->selectNumsKey(1,'*',$tb)['james'][1] === 'james');
        assert($db->selectObjsKey($tb->col('name_[lang]'),'*',$tb)['james'] instanceof \stdclass);
        assert($db->selectColumnIndex(0,'*',$tb,null,['id'=>'desc']) === 3);
        assert($db->selectRowCount($tb->col('id'),$tb,null,null,2) === 2);
        assert($db->selectColumnCount($tb->col('id'),$tb) === 1);
        assert(count($db->showAssoc("COLUMNS FROM $tb")) === 6);
        assert(count($db->showAssocs("COLUMNS FROM $tb")) === 3);
        assert($db->showAssocsKey(0,"COLUMNS FROM $tb")['id']['Field'] === 'id');
        assert($db->showAssocsKey('Field',"COLUMNS FROM $tb")['id']['Field'] === 'id');
        assert($db->showColumn(1,"COLUMNS FROM $tb") === 'int(11) unsigned');
        assert($db->showColumns(2,"COLUMNS FROM $tb") === ['NO','YES','YES']);
        assert($db->showkeyValue(0,1,"COLUMNS FROM $tb") === ['id'=>'int(11) unsigned']);
        assert($db->showkeyValues('Field','Ty[pe]',"COLUMNS FROM $tb") === []);
        assert($db->showCount("COLUMNS FROM $tb") === 3);
        assert($db->showColumnCount("COLUMNS FROM $tb") === 6);
        assert($db->insert($tb,['id'=>9,'name_[lang]'=>'NINE']) === 9);
        assert($db->insert($tb,['id'=>11,'name_[lang]'=>'NINE']) === 11);
        assert($db->insertBeforeAfter($tb,['id'=>12,'name_[lang]'=>'douze']) === ['before'=>null,'query'=>12,'after'=>['id'=>12,'name_en'=>'douze','dateAdd'=>null]]);
        assert($db->delete($tb,$tb[12]) === 1);
        assert($db->insertBeforeAfters($tb,['id'=>12,'name_[lang]'=>'douze']) === ['before'=>[],'query'=>12,'after'=>[['id'=>12,'name_en'=>'douze','dateAdd'=>null]]]);
        assert($db->delete($tb,$tb[12]) === 1);
        assert($db->update($tb,['id'=>10],['name_[lang]'=>'NINE'],['id'=>'asc'],1) === 1);
        assert($db->update($tb,['id'=>12],['name_[lang]'=>'NINE'],['id'=>'asc'],1) === 1);
        assert($db->updateBeforeAfter($tb,['name_[lang]'=>'james'],['id'=>12])['after']['name_en'] === 'james');
        assert($db->updateBeforeAfter($tb,['name_[lang]'=>'james'],['id'=>12])['query'] === 0);
        assert($db->updateBeforeAfters($tb,['name_[lang]'=>'james'],['id'=>12])['after'][0]['name_en'] === 'james');
        assert($db->delete($tb,['id'=>12],['id'=>'asc'],1) === 1);
        assert($db->deleteBeforeAfter($tb,11) === ['before'=>['id'=>11,'name_en'=>'NINE','dateAdd'=>null],'query'=>1,'after'=>null]);
        assert($db->deleteBeforeAfters($tb,11) === ['before'=>[],'query'=>0,'after'=>[]]);
        assert($db->truncate($tb) instanceof \PDOStatement);
        assert($db->inserts($tb,['id','name_[lang]','dateAdd'],[1,'james',10],[2,'james2',11],[3,'james3',10]) === [1,2,3]);
        assert($db->selectCount($tb,$tb[2]) === 1);
        assert($db->selectAll($tb,$tb[3])['id'] === 3);
        assert($db->selectAlls($tb,2)[0]['id'] === 2);
        assert($db->selectAllsKey(1,$tb)['james']['id'] === 1);
        assert($db->selectAllsKey($tb->col('id'),$tb)[1]['id'] === 1);
        assert($db->selectAllsPrimary($tb)[2]['id'] === 2);
        assert($db->selectFunction($tb->col('dateAdd'),'sum',$tb) === 31);
        assert($db->selectFunctions('dateAdd','sum',$tb)[0] === 31);
        assert($db->selectDistinct($tb->col('name_[lang]'),$tb) === ['james','james2','james3']);
        assert($db->selectColumn($tb->col('id'),$tb,null,['id'=>'desc']) === 3);
        assert($db->selectColumn([$tb->col('dateAdd'),'sum()'],$tb) === 31);
        assert($db->selectColumns($tb->col('id'),$tb,null,['id'=>'desc']) === [3,2,1]);
        assert($db->selectColumns([$tb->col('dateAdd'),'distinct()'],$tb) === [10,11]);
        assert($db->selectKeyPair($tb->col('id'),$tb->col('name_[lang]'),$tb,null,['id'=>'desc']) === [3=>'james3']);
        assert($db->selectKeyPair($tb->col('id'),$tb->col('name_[lang]'),$tb,1000) === null);
        assert($db->selectKeyPairs('name_[lang]',$tb->col('id'),$tb,['id'=>[2,1]]) === ['james'=>1,'james2'=>2]);
        assert($db->selectKeyPairs($tb->col('id'),$tb->col('name_[lang]'),$tb,1000) === []);
        assert($db->selectPrimary($tb,['id'=>[3,2]]) === 2);
        assert($db->selectPrimaries($tb) === [1,2,3]);
        assert($db->selectPrimaryPair($tb->col('name_[lang]'),$tb) === [1=>'james']);
        assert($db->selectPrimaryPairs($tb->col('name_[lang]'),$tb,1) === [1=>'james']);
        assert($db->showDatabase($db->dbName()) === $db->dbName());
        assert($db->showTable($tb) === $tb->name());
        assert(count($db->showTables()) >= 1);
        assert(count($db->showTables($tb)) === 1);
        assert($db->setDebug(true)->showTableColumn($tb,$tb->col('name_[lang]'))['sql'] === "SHOW COLUMNS FROM `ormDb` WHERE FIELD = 'name_en'");
        $db->setDebug();
        assert(count($db->showTableColumn($tb,$tb->col('id'))) === 6);
        assert($db->showTableColumnField($tb,$tb->col('id')) === 'id');
        assert($db->showTableColumns($tb)['id']['Field'] === 'id');
        assert($db->showTableColumnsField($tb) === ['id','name_en','dateAdd']);
        assert($db->updateColumn($tb->col('name_[lang]'),'james44',$tb,1) === 1);
        assert($db->updateColumn($tb->col('name_[lang]'),'james44',$tb,1) === 0);
        assert($db->selectAssocsPrimary('*',$tb)[1]['name_en'] === 'james44');
        assert($db->updateIncrement($tb->col('dateAdd'),1,$tb,1) === 1);
        assert($db->updateIncrement($tb->col('dateAdd'),2,$tb,1) === 1);
        assert($db->updateIncrement($tb->col('dateAdd'),0,$tb,1) === null);
        assert($db->updateIncrement($tb->col('name_[lang]'),2,$tb,1) === null);
        assert($db->selectAssocsPrimary('*',$tb)[1]['dateAdd'] === 13);
        assert($db->updateDecrement($tb->col('dateAdd'),3,$tb,1) === 1);
        assert($db->updateDecrement($tb->col('dateAdd'),4,$tb,1) === 1);
        assert($db->updateDecrement($tb->col('dateAdd'),4,$tb,1,['beforeAfter'=>'assoc'])['before']['dateAdd'] === 6);
        assert($db->selectAssocsPrimary('*',$tb)[1]['dateAdd'] === 2);
        assert($db->inserts($tb,['id'],[20],[21],[22],[23],[24],[25],[26],[27]) === [20,21,22,23,24,25,26,27]);
        assert($db->deleteTrim($tb,5) === 6);
        assert($db->selectCount($tb) === 5);
        assert($db->alterAutoIncrement($tb,20) instanceof \PDOStatement);

        // cleanup
        assert($db->truncate($tb) instanceof \PDOStatement);

        return true;
    }
}
?>