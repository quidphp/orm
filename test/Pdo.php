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

// pdo
// class for testing Quid\Orm\Pdo
class Pdo extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $table = 'main';
        $table2 = $table.'2';
        $boot = $data['boot'];
        $credentials = $boot->getAttr('assert/db');

        // construct
        $pdo = new Orm\Pdo(...$credentials);
        $syntax = $pdo->getSyntax();
        assert($pdo->truncate($table) instanceof \PDOStatement);
        assert($pdo->insert($table,['id'=>1,'name_en'=>'james','dateAdd'=>10]));
        assert($pdo->insert($table,['id'=>2,'name_en'=>'james2','dateAdd'=>11]));
        assert($pdo->insert($table,['id'=>3,'name_en'=>'james3','dateAdd'=>10]));
        assert(($x = $pdo->makeDrop([$table2],true,['dropExists'=>true])) instanceof \PDOStatement);

        // destruct

        // invoke
        assert($pdo("SELECT * FROM $table",null) instanceof \PDOStatement);

        // toString

        // cast
        assert($pdo->_cast() === $pdo->name());

        // onSetInst

        // onBeforeMakeStatement

        // onAfterMakeStatement

        // instName
        assert($pdo->instName() === $pdo->dsn());

        // connect

        // disconnect
        assert($pdo->disconnect() === $pdo);
        assert($pdo->connect($credentials[1],$credentials[2]) === $pdo);

        // pdo
        assert($pdo->pdo() instanceof \Pdo);

        // primary
        assert($pdo->primary() === 'id');

        // charset
        assert($pdo->charset() === 'utf8mb4');

        // collation
        assert($pdo->collation() === 'utf8mb4_general_ci');

        // dsn
        assert($pdo->dsn() === $credentials[0]);

        // setDsn

        // getSyntax

        // setSyntax

        // syntaxCall

        // driver
        assert($pdo->driver() === 'mysql');

        // host
        assert($pdo->host() === 'localhost');

        // dbName
        assert(!empty($pdo->dbName()));

        // username
        assert($pdo->username() === $credentials[1]);

        // setUsername

        // name
        assert($pdo->name() === $pdo->_cast());

        // clientVersion
        assert(!empty($pdo->clientVersion()));

        // connectionStatus
        assert(!empty($pdo->connectionStatus()));

        // serverVersion
        assert(!empty($pdo->serverVersion()));

        // serverInfo
        assert(!empty($pdo->serverInfo()));

        // getSqlOption
        assert(count($pdo->getSqlOption()) === 3);

        // setDebug
        assert($pdo->setDebug(false));

        // isReady
        assert($pdo->isReady());

        // checkReady

        // setRollback
        assert($pdo->setRollback(true));
        assert($pdo->getAttr('rollback') === true);

        // makeHistory

        // history
        assert($pdo->history() instanceof Orm\History);

        // setHistory
        assert($pdo->setHistory(true) === $pdo);

        // historyRollback
        assert($pdo->insert($table,['id'=>30]) === 30);
        assert($pdo->historyRollback('insert',-1) === 1);
        assert($pdo->historyRollback('insert',-1) === 0);
        assert($pdo->setRollback(false));
        assert($pdo->insert($table,['id'=>30]) === 30);
        assert($pdo->historyRollback('insert',-1) === null);
        assert($pdo->setRollback(true));
        assert($pdo->delete($table,30));

        // info
        assert(count($pdo->info()) === 17);

        // importantVariables
        assert(count($pdo->importantVariables()) === 20);

        // getPdoAttr
        assert($pdo->getPdoAttr(\Pdo::ATTR_AUTOCOMMIT) === 1);
        assert($pdo->getPdoAttr(\Pdo::ATTR_CASE) === 0);
        assert(is_string($pdo->getPdoAttr(\Pdo::ATTR_CLIENT_VERSION)));
        assert(is_string($pdo->getPdoAttr(\Pdo::ATTR_CONNECTION_STATUS)));
        assert($pdo->getPdoAttr(\Pdo::ATTR_DRIVER_NAME) === 'mysql');
        assert($pdo->getPdoAttr(\Pdo::ATTR_ERRMODE) === 2);
        assert($pdo->getPdoAttr(\Pdo::ATTR_ORACLE_NULLS) === 0);
        assert($pdo->getPdoAttr(\Pdo::ATTR_PERSISTENT) === false);
        assert(is_string($pdo->getPdoAttr(\Pdo::ATTR_SERVER_INFO)));
        assert(is_string($pdo->getPdoAttr(\Pdo::ATTR_SERVER_VERSION)));

        // setPdoAttr
        assert($pdo->setPdoAttr(\Pdo::ATTR_CASE,\Pdo::CASE_UPPER));
        assert($pdo->query("SELECT * FROM $table",'assoc')['NAME_EN'] === 'james');
        assert($pdo->setPdoAttr(\Pdo::ATTR_CASE,\Pdo::CASE_NATURAL));

        // errorCode

        // errorInfo
        assert(count($pdo->errorInfo()) === 3);

        // beginTransaction
        assert(!$pdo->inTransaction());
        assert($pdo->beginTransaction());

        // inTransaction
        assert($pdo->inTransaction());

        // commit
        assert($pdo->commit());
        assert(!$pdo->inTransaction());

        // rollback
        assert($pdo->beginTransaction());
        assert($pdo->rollback());
        assert(!$pdo->inTransaction());

        // lastInsertId
        assert($pdo->lastInsertId() === null);

        // quote
        assert($pdo->quote('TEST') === "'TEST'");

        // makeStatement
        assert($pdo->makeStatement("SELECT * FROM $table") instanceof \PDOStatement);

        // statementException

        // infoStatement
        $sql = $syntax::select('*',$table,[2,'name_en'=>'james2']);
        $statement = $pdo->statement($sql);
        assert(count($pdo->infoStatement($sql,$statement)) === 14);

        // outputStatement
        assert($pdo->outputStatement(['sql'=>'SELECT *'],'rowCount',$statement) === 1);
        assert(count($pdo->outputStatement(['sql'=>'SELECT *'],'*',$statement)) === 10);

        // getColumnMeta
        assert(count($pdo->getColumnMeta($statement)) === 4);

        // fetchKeyPairStatement
        $sql = $syntax::select('*',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairStatement(null,$statement) === [1=>'james']);
        assert($pdo->fetchKeyPairStatement(null,$statement) !== [1=>'james']);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairStatement(['name_en','id'],$statement) === ['james'=>1]);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairStatement([1,0],$statement) === ['james'=>1]);
        $statement = $pdo->statement($syntax::select('id,name_en',$table));
        assert($pdo->fetchKeyPairStatement(null,$statement) === [1=>'james']);

        // fetchKeyPairsStatement
        $sql = $syntax::select('*',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairsStatement(null,$statement) === [1=>'james',2=>'james2',3=>'james3']);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairsStatement(['name_en','id'],$statement) === ['james'=>1,'james2'=>2,'james3'=>3]);
        $sql = $syntax::select('id,name_en',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchKeyPairsStatement(null,$statement) === [1=>'james',2=>'james2',3=>'james3']);

        // fetchColumnStatement
        $sql = $syntax::select('*',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnStatement(null,$statement) === 1);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnStatement('name_en',$statement) === 'james');
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnStatement([0],$statement) === 1);

        // fetchColumnsStatement
        $sql = $syntax::select('*',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnsStatement(null,$statement) === [1,2,3]);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnsStatement('name_en',$statement) === ['james','james2','james3']);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchColumnsStatement([0],$statement) === [1,2,3]);

        // fetchSegmentStatement
        $sql = $syntax::select('*',$table);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchSegmentStatement(['[id] [name_%lang%]'],$statement) === '1 james');

        // fetchSegmentsStatement
        $sql = $syntax::select('*',$table,null,['id'=>'desc']);
        $statement = $pdo->statement($sql);
        assert($pdo->fetchSegmentsStatement(['[id] [name_%lang%]'],$statement) === [3=>'3 james3',2=>'2 james2',1=>'1 james']);

        // query
        $statement = $pdo->query($syntax::select('*',$table),null);
        assert($pdo->query("SELECT * FROMz $table",'debug')['sql'] === 'SELECT * FROMz main');
        assert($pdo->setDebug(true)->query("SELECT * FROMz $table")['sql'] === 'SELECT * FROMz main');
        assert($pdo->setDebug());
        assert(count($pdo->query("SELECT * FROM $table",true)) === 3);
        assert($pdo->query("SELECT * FROM $table",'rowCount') === 3);
        assert($pdo->query("SELECT * FROM $table",'rowCount') === 3);
        assert(count($pdo->query("SELECT * FROM $table",'assocs')) === 3);
        assert($pdo->query("SELECT * FROM $table",'assoc') === ['id'=>1,'name_en'=>'james','active'=>null,'dateAdd'=>10]);
        assert($pdo->query("SELECT * FROM $table",'objs')[0] instanceof \stdClass);
        assert($pdo->query("SELECT * FROM $table",'obj') instanceof \stdClass);
        assert($pdo->query("SELECT * FROM $table",null) instanceof \PDOStatement);
        assert(!empty($pdo->query("SELECT * FROM $table",'columnMeta')['id']));
        assert($pdo->query("SELECT id, name_en FROM $table",'keyPair') === [1=>'james']);
        assert($pdo->query("SELECT name_en, id FROM $table",'keyPairs') === ['james'=>1,'james2'=>2,'james3'=>3]);
        assert($pdo->query("SELECT * FROM $table",'columns') === [1,2,3]);
        assert($pdo->query("SELECT * FROM $table",['columns','arg'=>1]) === ['james','james2','james3']);
        assert($pdo->query("SELECT * FROM $table",'column') === 1);
        assert($pdo->query("SELECT * FROM $table",['column','arg'=>1]) === 'james');
        assert(Base\Arr::isIndexed($pdo->query("SELECT * FROM $table",'nums')[0]));
        assert(Base\Arr::isIndexed($pdo->query("SELECT * FROM $table",'num')));
        assert(count($pdo->query("SELECT * FROM $table",'boths')[0]) === 8);
        assert(count($pdo->query("SELECT * FROM $table",'both')) === 8);
        assert(count($pdo->query("SELECT * FROM $table",'named')) === 4);
        assert(count($pdo->query("SELECT * FROM $table",'nameds')[0]) === 4);
        $x = ($pdo->query("SELECT * FROM $table",'lazy'));
        assert($pdo->query("SELECT * FROM $table",'assocsUnique')[1] === ['name_en'=>'james','active'=>null,'dateAdd'=>10]);
        assert($pdo->query("SELECT * FROM $table",['columnsGroup','arg'=>3]) === [10=>[1,3],11=>[2]]);
        assert($pdo->query("SELECT * FROM $table",['columnsGroup','arg'=>0]) === [1=>[1],2=>[2],3=>[3]]);
        assert($pdo->query("INSERT INTO $table VALUES(4,'james4',null,14)") === 4);
        assert($pdo->query("DELETE FROM $table WHERE id = 4") === 1);
        assert(count($pdo->query("SELECT * FROM $table",'info')) === 10);
        assert($pdo->query("SELECT * FROM $table WHERE id = 3",['beforeAfter'=>'assoc']) instanceof \PDOStatement);

        // statement

        // queryBeforeAfter

        // preparedStatement

        // outputStatementSelectShow

        // make
        assert($pdo->make('select',['*',$table,['id'=>3]])[0]['id'] === 3);
        assert($pdo->make('select',['*',$table,['id'=>3]],'assoc')['id'] === 3);

        // makeSelect
        assert($pdo->makeSelect(['*',$table,['id'=>3]])[0]['id'] === 3);
        assert($pdo->makeSelect([true,$table,[['id','>',1],'and','(',['id','=',2],'or',['id','=',3]]],'debug')['emulate'] === 'SELECT * FROM `main` WHERE `id` > 1 AND (`id` = 2 OR `id` = 3)');
        assert($pdo->makeSelect(['*',$table],'keyPairs') === $pdo->makeSelect([['id','name_en'],$table],'keyPairs'));
        assert($pdo->makeSelect(['*',$table],['columns','arg'=>'name_en']) === $pdo->makeSelect(['*',$table],['columns','arg'=>1]));
        assert(count($pdo->makeSelect([true,$table,'group'=>['dateAdd']])) === 2);
        assert($pdo->setDebug(true)->makeSelect([true,$table,'join'=>['table'=>'session','on'=>[[$table.'.id','`=`','session.id']]]])['sql'] === 'SELECT * FROM `main` JOIN `session` ON(main.`id` = session.`id`)');
        $pdo->setDebug();

        // makeShow
        assert(in_array($table,$pdo->makeShow(['TABLES'],'columns'),true));

        // makeInsert
        assert($pdo->makeInsert([$table,['id'=>6,'name_en'=>'ok','dateAdd'=>time()]]) === 6);
        assert($pdo->makeInsert([$table,['id'=>7,'name_en'=>'ok3','dateAdd'=>time()]],'rowCount') === 1);
        assert($pdo->makeInsert([$table,['id'=>8,'name_en'=>'ok4','dateAdd'=>time()]],'rowCount') === 1);
        assert($pdo->makeInsert([$table,['id'=>100,'name_en'=>'OK']],['beforeAfter'=>'assoc']) === ['before'=>null,'query'=>100,'after'=>['id'=>100,'name_en'=>'OK','active'=>null,'dateAdd'=>null]]);

        // makeUpdate
        assert($pdo->makeUpdate([$table,['name_en'=>'ok2'],6]) === 1);
        assert($pdo->makeUpdate([$table,['name_en'=>'ok2'],6]) === 0);
        assert($pdo->makeUpdate([$table,['name_en'=>'ok3'],6],['beforeAfter'=>'assoc'])['before']['name_en'] === 'ok2');

        // makeDelete
        assert($pdo->makeDelete([$table,['id'=>6]]) === 1);
        assert($pdo->makeDelete([$table,['id'=>6]]) === 0);
        assert($pdo->makeDelete([$table,['id'=>[7,8]]]) === 2);
        assert($pdo->makeDelete([$table,100],['beforeAfter'=>'assoc']) === ['before'=>['id'=>100,'name_en'=>'OK','active'=>null,'dateAdd'=>null],'query'=>1,'after'=>null]);
        assert($pdo->alterAutoIncrement($table,0) instanceof \PdoStatement);

        // makeCreate
        assert(($x = $pdo->makeCreate([$table2,[['id','int','length'=>12,'null'=>null,'autoIncrement'=>true,'unsigned'=>true],['name_en','varchar','length'=>200,'default'=>"L'article de james"]],[['key','name_en'],['primary','id']]])) instanceof \PDOStatement);
        assert($x->columnCount() === 0);

        // makeAlter
        assert($pdo->makeAlter([$table2,['james','varchar','length'=>100],['unique','james'],['name_en','rename'=>'namez','varchar','length'=>200,'default'=>'james'],null,['name_en']]) instanceof \PDOStatement);

        // makeTruncate
        assert($pdo->makeInsert([$table2,['id'=>6,'namez'=>'ok']]) === 6);
        assert($pdo->makeTruncate([$table2]) instanceof \PDOStatement);

        // makeDrop
        assert($pdo->makeDrop([$table2]) instanceof \PDOStatement);

        // prepareRollback
        assert($pdo->prepareRollback('update',$syntax::make('update',[$table,['name'=>'bla'],2]))['rollback']['id'] === 2);

        // select
        assert(count($pdo->select(true,$table,null,['id'=>'DESC'],'1,2')) === 2);
        assert(count($pdo->select(true,$table,[['name_en','like','james2']])) === 1);
        assert(count($pdo->select(true,$table,[['id','findInSet',2]])) === 1);
        assert(count($pdo->select(true,$table,[['id','>',1],'and','(',['id','=',2],'or',['id','=',3],')'])) === 2);
        assert(count($pdo->select(true,$table,['id'=>[1,2,4]])) === 2);
        assert(count($pdo->select(true,$table,[['id','notIn',[2,3]]])) === 1);
        assert(count($pdo->select(true,$table,null,['id'=>'DESC'],'1,2')) === 2);
        assert($pdo->insert($table,['id'=>4,'name_en'=>'james','dateAdd'=>Base\Date::mk(2017,1,5)]) === 4);
        assert(count($pdo->select(true,$table,[['dateAdd','day',Base\Date::mk(2017,1,5)]])) === 1);
        assert(count($pdo->select(true,$table,[['dateAdd','day',Base\Date::mk(2017,1,6)]])) === 0);
        assert(count($pdo->select(true,$table,[['dateAdd','year',Base\Date::mk(2017,3)]])) === 1);
        assert(count($pdo->select(true,$table,[['dateAdd','month',Base\Date::mk(2017,1,20)]])) === 1);
        assert(count($pdo->select(true,$table,[['dateAdd','year',Base\Date::mk(2018,3)]])) === 0);
        assert(count($pdo->select(true,$table,[['name_en','%like','ja'],'or',['name_en','like%','s2']])) === 4);
        assert(count($pdo->select(true,$table,[['name_en','%like','ja'],'and',['name_en','like%','s2']])) === 1);
        assert(count($pdo->select(true,$table,[['id','findInSet',[1,2]]])) === 0);
        assert(count($pdo->select(true,$table,[['id','or|findInSet',[1,2]]])) === 2);
        assert($pdo->delete($table,['id'=>4]) === 1);

        // selectAssoc
        assert($pdo->selectAssoc('id',$table) === ['id'=>1]);
        assert($pdo->selectAssoc('id',$table,1000) === null);

        // selectAssocs
        assert(count($pdo->selectAssocs(['id','name_en'],$table)[0]) === 2);
        assert($pdo->selectAssocs('id',$table,1000) === []);

        // selectAssocsUnique
        assert($pdo->selectAssocsUnique('*',$table)[2]['name_en'] === 'james2');

        // selectAssocsKey
        assert($pdo->selectAssocsKey('name_en','*',$table)['james']['name_en'] === 'james');
        assert($pdo->selectAssocsKey(1,'*',$table)['james']['name_en'] === 'james');

        // selectAssocsPrimary
        assert($pdo->selectAssocsPrimary('*',$table)[1]['id'] === 1);

        // selectColumnIndex
        assert($pdo->selectColumnIndex(0,'*',$table,null,['id'=>'desc']) === 3);

        // selectColumnsIndex
        assert($pdo->selectColumnsIndex(0,'*',$table,null,['id'=>'desc']) === [3,2,1]);

        // selectRowCount
        assert($pdo->selectRowCount('id',$table) === 3);
        assert($pdo->selectRowCount('id',$table,null,null,2) === 2);
        assert($pdo->selectRowCount('id',$table,2,null,2) === 1);

        // selectColumnCount
        assert($pdo->selectColumnCount('id',$table) === 1);
        assert($pdo->selectColumnCount('*',$table) === 4);

        // show
        assert(!empty($pdo->show('TABLES')));

        // showAssoc
        assert(count($pdo->showAssoc("COLUMNS FROM $table")) === 6);

        // showAssocs
        assert(count($pdo->showAssocs("COLUMNS FROM $table")) === 4);

        // showAssocsKey
        assert($pdo->showAssocsKey(0,"COLUMNS FROM $table")['id']['Field'] === 'id');
        assert($pdo->showAssocsKey('Field',"COLUMNS FROM $table")['id']['Field'] === 'id');

        // showColumn
        assert($pdo->showColumn('Field',"COLUMNS FROM $table") === 'id');
        assert($pdo->showColumn(1,"COLUMNS FROM $table") === 'int(11) unsigned');

        // showColumns
        assert($pdo->showColumns('Field',"COLUMNS FROM $table") === ['id','name_en','active','dateAdd']);
        assert($pdo->showColumns(2,"COLUMNS FROM $table") === ['NO','YES','YES','YES']);

        // showKeyValue
        assert($pdo->showKeyValue('Field','Type',"COLUMNS FROM $table") === ['id'=>'int(11) unsigned']);
        assert($pdo->showKeyValue(0,1,"COLUMNS FROM $table") === ['id'=>'int(11) unsigned']);

        // showKeyValues
        assert(count($pdo->showKeyValues('Field','Type',"COLUMNS FROM $table")) === 4);
        $syntax::setShortcut('pe','pe');
        assert(count($pdo->showKeyValues('Field','Ty[pe]',"COLUMNS FROM $table")) === 4);
        $syntax::unsetShortcut('pe');
        assert($pdo->showKeyValues('Field','Ty[pe]',"COLUMNS FROM $table") === []);

        // showCount
        assert($pdo->showCount("COLUMNS FROM $table") === 4);

        // showColumnCount
        assert($pdo->showColumnCount("COLUMNS FROM $table") === 6);

        // insert
        assert($pdo->insert($table,['id'=>9,'name_en'=>'NINE']) === 9);
        assert($pdo->insert($table) === null);
        assert($pdo->insert($table,[]) === 10);
        assert($pdo->delete($table,10) === 1);

        // inserts
        assert($pdo->inserts($table,['id','name_en'],[99,'OK'],[100,'YEP']) === [99,100]);
        assert($pdo->delete($table,99) === 1);
        assert($pdo->delete($table,100) === 1);
        $pdo->alterAutoIncrement($table);
        assert($pdo->insert($table,['id'=>11,'name_en'=>'NINE']) === 11);

        // insertBeforeAfter
        assert($pdo->insertBeforeAfter($table,['id'=>12,'name_en'=>'douze']) === ['before'=>null,'query'=>12,'after'=>['id'=>12,'name_en'=>'douze','active'=>null,'dateAdd'=>null]]);
        assert($pdo->delete($table,12) === 1);
        assert($pdo->insertBeforeAfter($table,[]) === ['before'=>null,'query'=>13,'after'=>null]);
        assert($pdo->delete($table,13) === 1);

        // insertBeforeAfters
        assert($pdo->insertBeforeAfters($table,['id'=>12,'name_en'=>'douze']) === ['before'=>[],'query'=>12,'after'=>[['id'=>12,'name_en'=>'douze','active'=>null,'dateAdd'=>null]]]);
        assert($pdo->delete($table,12) === 1);

        // update
        assert($pdo->update($table) === null);
        assert($pdo->update($table,['id'=>10],['name_en'=>'NINE'],['id'=>'asc'],1) === 1);
        assert($pdo->update($table,['id'=>12],['name_en'=>'NINE'],['id'=>'asc'],1) === 1);

        // updateBeforeAfter
        assert($pdo->updateBeforeAfter($table,['name_en'=>'james'],['id'=>12])['after']['name_en'] === 'james');
        assert($pdo->updateBeforeAfter($table,['name_en'=>'james'],['id'=>12])['query'] === 0);

        // updateBeforeAfters
        assert($pdo->updateBeforeAfters($table,['name_en'=>'james'],['id'=>12])['after'][0]['name_en'] === 'james');

        // delete
        assert($pdo->delete($table) === null);
        assert($pdo->delete($table,['id'=>12],['id'=>'asc'],1) === 1);

        // deleteBeforeAfter
        assert($pdo->deleteBeforeAfter($table,11) === ['before'=>['id'=>11,'name_en'=>'NINE','active'=>null,'dateAdd'=>null],'query'=>1,'after'=>null]);

        // deleteBeforeAfters
        assert($pdo->deleteBeforeAfters($table,11) === ['before'=>[],'query'=>0,'after'=>[]]);

        // create
        assert($pdo->create($table) === null);

        // alter

        // truncate
        assert($pdo->truncate($table) instanceof \PDOStatement);

        // drop

        // reservePrimary
        assert($pdo->reservePrimary($table));
        assert($pdo->showTableAutoIncrement($table) === 2);
        assert($pdo->truncate($table) instanceof \PDOStatement);

        // reservePrimaryDelete

        // selectCount
        assert($pdo->insert($table,['id'=>1,'name_en'=>'james','dateAdd'=>10]));
        assert($pdo->insert($table,['id'=>2,'name_en'=>'james2','dateAdd'=>11]));
        assert($pdo->insert($table,['id'=>3,'name_en'=>'james3','dateAdd'=>10]));
        assert($pdo->selectCount($table,null,['id'=>'desc'],3) === 3);
        assert($pdo->selectCount($table,2) === 1);

        // selectAll
        assert($pdo->selectAll($table,3)['id'] === 3);

        // selectAlls
        assert($pdo->selectAlls($table,2)[0]['id'] === 2);

        // selectAllsKey
        assert($pdo->selectAllsKey(1,$table)['james']['id'] === 1);
        assert($pdo->selectAllsKey('id',$table)[1]['id'] === 1);

        // selectAllsPrimary
        assert($pdo->selectAllsPrimary($table)[2]['id'] === 2);

        // selectFunction
        assert($pdo->selectFunction('dateAdd','sum',$table) === 31);
        assert($pdo->selectFunction('dateAdd','sum()',$table) === 31);

        // selectFunctions
        assert($pdo->selectFunctions('dateAdd','sum',$table)[0] === 31);

        // selectDistinct
        assert($pdo->selectDistinct('name_en',$table) === ['james','james2','james3']);
        assert($pdo->selectDistinct('dateAdd',$table) === [10,11]);
        
        // selectCountDistinct
        assert($pdo->selectCountDistinct('dateAdd',$table) === 2);
        
        // selectColumn
        assert($pdo->selectColumn('id',$table,null,['id'=>'desc']) === 3);
        assert($pdo->selectColumn(['dateAdd','sum()'],$table) === 31);
        $pdo->setDebug();
        assert($pdo->selectColumn('id',$table,null,['id'=>'desc'],[2,3])['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC LIMIT 2 OFFSET 3');
        assert($pdo->selectColumn('id',$table,null,['id'=>'desc'])['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC LIMIT 1');
        assert($pdo->makeSelect([['id'],$table,null,['id'=>'desc']])['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC');
        $pdo->setDebug();

        // selectColumns
        assert($pdo->selectColumns('id',$table,null,['id'=>'desc']) === [3,2,1]);
        assert($pdo->selectColumns(['dateAdd','distinct()'],$table) === [10,11]);

        // selectKeyPair
        assert($pdo->selectKeyPair('id','name_en',$table,null,['id'=>'desc']) === [3=>'james3']);
        assert($pdo->selectKeyPair('id','name_en',$table,1000) === null);

        // selectKeyPairs
        assert($pdo->selectKeyPairs('name_en','id',$table,['id'=>[2,1]]) === ['james'=>1,'james2'=>2]);
        assert($pdo->selectKeyPairs('id','name_en',$table,1000) === []);
        assert($pdo->setDebug(true)->selectKeyPairs('id','name_[lang]','main',2)['sql'] === 'SELECT `id`, `name_en` FROM `main` WHERE `id` = 2');
        $pdo->setDebug();

        // selectPrimary
        assert($pdo->selectPrimary($table,['id'=>[3,2]]) === 2);

        // selectPrimaries
        assert($pdo->selectPrimaries($table) === [1,2,3]);

        // selectPrimaryPair
        assert($pdo->selectPrimaryPair('name_en',$table) === [1=>'james']);

        // selectPrimaryPairs
        assert($pdo->selectPrimaryPairs('name_en',$table,1) === [1=>'james']);

        // selectSegment
        assert($pdo->selectSegment('[id] [name_en]',$table,1) === '1 james');

        // selectSegments
        assert($pdo->selectSegments('[id] [name_en]',$table,null,['id'=>'desc']) === [3=>'3 james3',2=>'2 james2',1=>'1 james']);

        // selectSegmentAssoc
        assert($pdo->selectSegmentAssoc('[name_en] [id] [id]',$table,1) === ['id'=>1,'name_en'=>'james']);

        // selectSegmentAssocs
        assert($pdo->selectSegmentAssocs('[name_en] [id] [id]',$table)[0] === ['id'=>1,'name_en'=>'james']);

        // selectSegmentAssocsKey
        assert($pdo->selectSegmentAssocsKey('[name_en] [id] [id]',$table)[1] === ['id'=>1,'name_en'=>'james']);

        // selectTableColumnCount
        assert($pdo->selectTableColumnCount('main') === 4);

        // showDatabase
        assert($pdo->showDatabase($pdo->dbName()) === $pdo->dbName());

        // showDatabases
        assert(count($pdo->showDatabases()) > 1);

        // showVariable
        assert($pdo->showVariable('autocommit') === 'ON');

        // showVariables
        assert(count($pdo->showVariables()) > 400);
        assert(count($pdo->showVariables('innodb_%')) < 400);

        // showTable
        assert($pdo->showTable($table) === $table);

        // showTables
        assert(count($pdo->showTables()) >= 1);
        assert(count($pdo->showTables('main')) === 1);
        assert(count($pdo->showTables('mainz')) === 0);

        // showTableStatus
        assert(count($pdo->showTableStatus($table)) >= 18);

        // showTableAutoIncrement
        assert($pdo->showTableAutoIncrement($table) === 4);

        // showTablesColumns
        assert(Base\Column::is($pdo->showTablesColumns()));

        // showTableColumn
        assert($pdo->setDebug(true)->showTableColumn('main','name_[lang]')['sql'] === "SHOW FULL COLUMNS FROM `main` WHERE FIELD = 'name_en'");
        $pdo->setDebug();
        assert(count($pdo->showTableColumn('main','id')) === 9);
        assert(count($pdo->showTableColumn('main','id',['full'=>false])) === 6);

        // showTableColumnField
        assert($pdo->showTableColumnField('main','id') === 'id');
        assert($pdo->showTableColumnField('main','idzzz') === null);

        // showTableColumns
        assert($pdo->showTableColumns('main')['id']['Field'] === 'id');

        // showTableColumnsField
        assert($pdo->showTableColumnsField('main') === ['id','name_en','active','dateAdd']);

        // updateColumn
        assert($pdo->updateColumn('name_en','james44',$table,1) === 1);
        assert($pdo->updateColumn('name_en','james44',$table,1) === 0);
        assert($pdo->selectAssocsPrimary('*',$table)[1]['name_en'] === 'james44');

        // updateIncrement
        assert($pdo->updateIncrement('dateAdd',1,$table,1) === 1);
        assert($pdo->updateIncrement('dateAdd',2,$table,1) === 1);
        assert($pdo->updateIncrement('dateAdd',0,$table,1) === null);
        assert($pdo->updateIncrement('name_en',2,$table,1) === null);
        assert($pdo->selectAssocsPrimary('*',$table)[1]['dateAdd'] === 13);

        // updateDecrement
        assert($pdo->updateDecrement('dateAdd',3,$table,1) === 1);
        assert($pdo->updateDecrement('dateAdd',4,$table,1) === 1);
        assert($pdo->updateDecrement('dateAdd',4,$table,1,['beforeAfter'=>'assoc'])['before']['dateAdd'] === 6);
        assert($pdo->selectAssocsPrimary('*',$table)[1]['dateAdd'] === 2);

        // getDeleteTrimPrimaries
        assert($pdo->insert($table,['id'=>20]));
        assert($pdo->insert($table,['id'=>21]));
        assert($pdo->insert($table,['id'=>22]));
        assert($pdo->insert($table,['id'=>23]));
        assert($pdo->insert($table,['id'=>24]));
        assert($pdo->insert($table,['id'=>25]));
        assert($pdo->insert($table,['id'=>26]));
        assert($pdo->insert($table,['id'=>27]));
        assert(count($pdo->selectPrimaries($table)) === 11);
        assert(count($pdo->getDeleteTrimPrimaries($table,3)) === 8);
        assert($pdo->getDeleteTrimPrimaries($table,0) === null);
        assert(count($pdo->getDeleteTrimPrimaries($table,1)) === 10);

        // deleteTrim
        assert($pdo->deleteTrim($table,5) === 6);
        assert($pdo->selectCount($table) === 5);
        assert(count($pdo->getDeleteTrimPrimaries($table,1)) === 4);

        // alterAutoIncrement
        assert($pdo->alterAutoIncrement('main',20) instanceof \PDOStatement);

        // emulate
        $sql = $syntax::select('*',$table,[2,'active'=>'bla'],true,4);
        assert($pdo->emulate($sql['sql'],$sql['prepare']) === "SELECT * FROM `main` WHERE `id` = 2 AND `active` = 'bla' ORDER BY `id` ASC LIMIT 4");

        // debug
        assert(count($pdo->debug($syntax::select('*',$table,[2,'active'=>'bla'],true,4))) === 7);

        // sql
        assert($pdo->sql() instanceof Orm\PdoSql);
        assert($pdo->sql()->clone()->db() === $pdo);

        // isOutput
        assert($pdo->isOutput('select',true));
        assert($pdo->isOutput('select','assoc'));
        assert($pdo->isOutput('select',['columns','arg'=>2]));
        assert(!$pdo->isOutput('update','assoc'));
        assert(!$pdo->isOutput('select','insertId'));
        assert($pdo->isOutput('insert','insertId'));
        assert($pdo->isOutput('insert','rowCount'));
        assert($pdo->isOutput('insert',true));
        assert($pdo->isOutput('insert','statement'));
        assert(!$pdo->isOutput('insert','assoc'));
        assert($pdo->isOutput('select','*'));
        assert($pdo->isOutput('create',true));
        assert($pdo->isOutput('create','statement'));
        assert($pdo->isOutput('create',null));
        assert(!$pdo->isOutput('create','assoc'));

        // output
        assert($pdo->output('select',true) === ['method'=>'fetchAll','fetch'=>2,'type'=>'assocs']);
        assert($pdo->output('insert','assoc') === null);
        assert($pdo->output('select','insertId') === null);
        assert($pdo->output('insert',true) === ['method'=>'lastInsertId','type'=>'insertId']);
        assert($pdo->output('create',true) === ['type'=>'statement']);
        assert($pdo->output('create','assoc') === null);
        assert($pdo->output('create','statement') === ['type'=>'statement']);
        assert($pdo->output('insert','insertId') === ['method'=>'lastInsertId','type'=>'insertId']);
        assert($pdo->output('show','obj') === ['method'=>'fetchObject','selectLimit'=>1,'type'=>'obj']);
        assert($pdo->output('select','objs') === ['method'=>'fetchAll','fetch'=>5,'type'=>'objs']);
        assert($pdo->output('select',['columns','arg'=>2]) === ['method'=>'fetchAll','fetch'=>7,'arg'=>[2],'type'=>'columns']);
        assert($pdo->output('select',['beforeAfter'=>'assoc']) === ['type'=>'statement']);
        assert($pdo->output('select',null) === ['type'=>'statement']);
        assert($pdo->output('delete',true) === ['method'=>'rowCount','type'=>'rowCount']);
        assert($pdo->output('update',true) === ['method'=>'rowCount','type'=>'rowCount']);
        assert($pdo->output('delete',null) === ['type'=>'statement']);
        assert($pdo->output('delete','statement') === ['type'=>'statement']);
        assert($pdo->output('select','row') === null);
        assert($pdo->output('select','segment')['fetch'] === 'segment');

        // selectLimit
        assert($pdo->selectLimit('assoc',['what'=>'ok']) === ['what'=>'ok','limit'=>1]);
        assert($pdo->selectLimit('assocs',['what'=>'ok']) === ['what'=>'ok']);

        // parseFetch
        assert($pdo->parseFetch('assoc') === 2);
        assert($pdo->parseFetch(\Pdo::FETCH_OBJ) === 5);
        assert($pdo->parseFetch('assocz') === null);

        // defaultPort
        assert($pdo->defaultPort() === 3306);

        // isDriver
        assert(Orm\Pdo::isDriver('mysql'));
        assert(!Orm\Pdo::isDriver('oracle'));

        // parseDsn
        $dsn = 'mysql:host=localhost;dbname=quid995';
        assert(count(Orm\Pdo::parseDsn($dsn,'utf8mb4',350)) === 8);
        assert(Orm\Pdo::parseDsn($dsn,'utf8',324)['dbname'] === 'quid995');

        // parseDataType
        assert(Orm\Pdo::parseDataType('str') === \Pdo::PARAM_STR);
        assert(Orm\Pdo::parseDataType([]) === null);
        assert(Orm\Pdo::parseDataType(1.2) === \Pdo::PARAM_STR);

        // outputKey
        assert(Orm\Pdo::outputKey(0,[[2,'test'],[3,'test']]));
        $obj = new \stdclass();
        $obj->test = 2;
        $obj2 = new \stdclass();
        $obj2->test = 3;
        assert(Orm\Pdo::outputKey(0,[$obj,$obj2])[2] === $obj);

        // allDrivers
        assert(in_array('mysql',Orm\Pdo::allDrivers(),true));

        // setDefaultHistory
        $pdo::setDefaultHistory(true);

        // attr
        assert(count($pdo->attr()) === 13);

        // cleanup
        assert($pdo->truncate($table) instanceof \PDOStatement);
        assert($pdo->disconnect());

        return true;
    }
}
?>