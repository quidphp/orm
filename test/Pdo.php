<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// pdo
class Pdo extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$table = "main";
		$table2 = $table."2";
		$boot = $data['boot'];
		$credentials = $boot->attr('assert/db');

		// construct
		$pdo = new Orm\Pdo(...$credentials);
		assert($pdo->truncate($table) instanceof \PDOStatement);
		assert($pdo->insert($table,array('id'=>1,'name_en'=>'james','dateAdd'=>10)));
		assert($pdo->insert($table,array('id'=>2,'name_en'=>'james2','dateAdd'=>11)));
		assert($pdo->insert($table,array('id'=>3,'name_en'=>'james3','dateAdd'=>10)));
		assert(($x = $pdo->makeDrop(array($table2),true,array('dropExists'=>true))) instanceof \PDOStatement);

		// destruct

		// invoke
		assert($pdo("SELECT * FROM $table",null) instanceof \PDOStatement);

		// toString

		// cast
		assert($pdo->_cast() === $pdo->name());

		// onSetInst

		// onUnsetInst

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
		assert($pdo->getOption('rollback') === true);

		// makeHistory

		// history
		assert($pdo->history() instanceof Orm\History);

		// setHistory
		assert($pdo->setHistory(true) === $pdo);

		// historyRollback
		assert($pdo->insert($table,array('id'=>30)) === 30);
		assert($pdo->historyRollback('insert',-1) === 1);
		assert($pdo->historyRollback('insert',-1) === 0);
		assert($pdo->setRollback(false));
		assert($pdo->insert($table,array('id'=>30)) === 30);
		assert($pdo->historyRollback('insert',-1) === null);
		assert($pdo->setRollback(true));
		assert($pdo->delete($table,30));

		// info
		assert(count($pdo->info()) === 16);

		// getAttr
		assert($pdo->getAttr(\Pdo::ATTR_AUTOCOMMIT) === 1);
		assert($pdo->getAttr(\Pdo::ATTR_CASE) === 0);
		assert(is_string($pdo->getAttr(\Pdo::ATTR_CLIENT_VERSION)));
		assert(is_string($pdo->getAttr(\Pdo::ATTR_CONNECTION_STATUS)));
		assert($pdo->getAttr(\Pdo::ATTR_DRIVER_NAME) === 'mysql');
		assert($pdo->getAttr(\Pdo::ATTR_ERRMODE) === 2);
		assert($pdo->getAttr(\Pdo::ATTR_ORACLE_NULLS) === 0);
		assert($pdo->getAttr(\Pdo::ATTR_PERSISTENT) === false);
		assert(is_string($pdo->getAttr(\Pdo::ATTR_SERVER_INFO)));
		assert(is_string($pdo->getAttr(\Pdo::ATTR_SERVER_VERSION)));

		// setAttr
		assert($pdo->setAttr(\Pdo::ATTR_CASE,\Pdo::CASE_UPPER));
		assert($pdo->query("SELECT * FROM $table",'assoc')['NAME_EN'] === 'james');
		assert($pdo->setAttr(\Pdo::ATTR_CASE,\Pdo::CASE_NATURAL));

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
		$sql = Base\Sql::select("*",$table,array(2,'name_en'=>'james2'));
		$statement = $pdo->statement($sql);
		assert(count($pdo->infoStatement($sql,$statement)) === 14);

		// outputStatement
		assert($pdo->outputStatement(array('sql'=>'SELECT *'),'rowCount',$statement) === 1);
		assert(count($pdo->outputStatement(array('sql'=>'SELECT *'),'*',$statement)) === 10);

		// getColumnMeta
		assert(count($pdo->getColumnMeta($statement)) === 4);

		// fetchKeyPairStatement
		$sql = Base\Sql::select("*",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairStatement(null,$statement) === array(1=>'james'));
		assert($pdo->fetchKeyPairStatement(null,$statement) !== array(1=>'james'));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairStatement(array('name_en','id'),$statement) === array('james'=>1));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairStatement(array(1,0),$statement) === array('james'=>1));
		$statement = $pdo->statement(Base\Sql::select('id,name_en',$table));
		assert($pdo->fetchKeyPairStatement(null,$statement) === array(1=>'james'));

		// fetchKeyPairsStatement
		$sql = Base\Sql::select("*",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairsStatement(null,$statement) === array(1=>'james',2=>'james2',3=>'james3'));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairsStatement(array('name_en','id'),$statement) === array('james'=>1,'james2'=>2,'james3'=>3));
		$sql = Base\Sql::select("id,name_en",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchKeyPairsStatement(null,$statement) === array(1=>'james',2=>'james2',3=>'james3'));

		// fetchColumnStatement
		$sql = Base\Sql::select("*",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnStatement(null,$statement) === 1);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnStatement('name_en',$statement) === 'james');
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnStatement(array(0),$statement) === 1);

		// fetchColumnsStatement
		$sql = Base\Sql::select("*",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnsStatement(null,$statement) === array(1,2,3));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnsStatement('name_en',$statement) === array('james','james2','james3'));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchColumnsStatement(array(0),$statement) === array(1,2,3));

		// fetchSegmentStatement
		$sql = Base\Sql::select("*",$table);
		$statement = $pdo->statement($sql);
		assert($pdo->fetchSegmentStatement(array('[id] [name_%lang%]'),$statement) === '1 james');

		// fetchSegmentsStatement
		$sql = Base\Sql::select("*",$table,null,array('id'=>'desc'));
		$statement = $pdo->statement($sql);
		assert($pdo->fetchSegmentsStatement(array('[id] [name_%lang%]'),$statement) === array(3=>'3 james3',2=>'2 james2',1=>'1 james'));

		// query
		$statement = $pdo->query(Base\Sql::select("*",$table),null);
		assert($pdo->query("SELECT * FROMz $table",'debug')['sql'] === 'SELECT * FROMz main');
		assert($pdo->setDebug(true)->query("SELECT * FROMz $table")['sql'] === 'SELECT * FROMz main');
		assert($pdo->setDebug());
		assert(count($pdo->query("SELECT * FROM $table",true)) === 3);
		assert($pdo->query("SELECT * FROM $table",'rowCount') === 3);
		assert($pdo->query("SELECT * FROM $table",'rowCount') === 3);
		assert(count($pdo->query("SELECT * FROM $table",'assocs')) === 3);
		assert($pdo->query("SELECT * FROM $table",'assoc') === array('id'=>1,'name_en'=>'james','active'=>null,'dateAdd'=>10));
		assert($pdo->query("SELECT * FROM $table",'objs')[0] instanceof \stdClass);
		assert($pdo->query("SELECT * FROM $table",'obj') instanceof \stdClass);
		assert($pdo->query("SELECT * FROM $table",null) instanceof \PDOStatement);
		assert(!empty($pdo->query("SELECT * FROM $table",'columnMeta')['id']));
		assert($pdo->query("SELECT id, name_en FROM $table",'keyPair') === array(1=>'james'));
		assert($pdo->query("SELECT name_en, id FROM $table",'keyPairs') === array('james'=>1,'james2'=>2,'james3'=>3));
		assert($pdo->query("SELECT * FROM $table",'columns') === array(1,2,3));
		assert($pdo->query("SELECT * FROM $table",array('columns','arg'=>1)) === array('james','james2','james3'));
		assert($pdo->query("SELECT * FROM $table",'column') === 1);
		assert($pdo->query("SELECT * FROM $table",array('column','arg'=>1)) === 'james');
		assert(Base\Arr::isIndexed($pdo->query("SELECT * FROM $table",'nums')[0]));
		assert(Base\Arr::isIndexed($pdo->query("SELECT * FROM $table",'num')));
		assert(count($pdo->query("SELECT * FROM $table",'boths')[0]) === 8);
		assert(count($pdo->query("SELECT * FROM $table",'both')) === 8);
		assert(count($pdo->query("SELECT * FROM $table",'named')) === 4);
		assert(count($pdo->query("SELECT * FROM $table",'nameds')[0]) === 4);
		$x = ($pdo->query("SELECT * FROM $table",'lazy'));
		assert($pdo->query("SELECT * FROM $table",'assocsUnique')[1] === array('name_en'=>'james','active'=>null,'dateAdd'=>10));
		assert($pdo->query("SELECT * FROM $table",array('columnsGroup','arg'=>3)) === array(10=>array(1,3),11=>array(2)));
		assert($pdo->query("SELECT * FROM $table",array('columnsGroup','arg'=>0)) === array(1=>array(1),2=>array(2),3=>array(3)));
		assert($pdo->query("INSERT INTO $table VALUES(4,'james4',null,14)") === 4);
		assert($pdo->query("DELETE FROM $table WHERE id = 4") === 1);
		assert(count($pdo->query("SELECT * FROM $table","info")) === 10);
		assert($pdo->query("SELECT * FROM $table WHERE id = 3",array('beforeAfter'=>'assoc')) instanceof \PDOStatement);

		// statement

		// queryBeforeAfter

		// preparedStatement

		// outputStatementSelectShow

		// make
		assert($pdo->make('select',array('*',$table,array('id'=>3)))[0]['id'] === 3);
		assert($pdo->make('select',array('*',$table,array('id'=>3)),'assoc')['id'] === 3);

		// makeSelect
		assert($pdo->makeSelect(array('*',$table,array('id'=>3)))[0]['id'] === 3);
		assert($pdo->makeSelect(array(true,$table,array(array('id','>',1),'and','(',array('id','=',2),'or',array('id','=',3))),'debug')['emulate'] === "SELECT * FROM `main` WHERE `id` > 1 AND (`id` = 2 OR `id` = 3)");
		assert($pdo->makeSelect(array('*',$table),'keyPairs') === $pdo->makeSelect(array(array('id','name_en'),$table),'keyPairs'));
		assert($pdo->makeSelect(array('*',$table),array('columns','arg'=>'name_en')) === $pdo->makeSelect(array('*',$table),array('columns','arg'=>1)));
		assert(count($pdo->makeSelect(array(true,$table,'group'=>array('dateAdd')))) === 2);
		assert($pdo->setDebug(true)->makeSelect(array(true,$table,'join'=>array('table'=>'session','on'=>array(array($table.'.id','`=`','session.id')))))['sql'] === 'SELECT * FROM `main` JOIN `session` ON(main.`id` = session.`id`)');
		$pdo->setDebug();

		// makeShow
		assert(in_array($table,$pdo->makeShow(array('TABLES'),'columns'),true));

		// makeInsert
		assert($pdo->makeInsert(array($table,array('id'=>6,'name_en'=>'ok','dateAdd'=>time()))) === 6);
		assert($pdo->makeInsert(array($table,array('id'=>7,'name_en'=>'ok3','dateAdd'=>time())),'rowCount') === 1);
		assert($pdo->makeInsert(array($table,array('id'=>8,'name_en'=>'ok4','dateAdd'=>time())),'rowCount') === 1);
		assert($pdo->makeInsert(array($table,array('id'=>100,'name_en'=>'OK')),array('beforeAfter'=>'assoc')) === array('before'=>null,'query'=>100,'after'=>array('id'=>100,'name_en'=>'OK','active'=>null,'dateAdd'=>null)));

		// makeUpdate
		assert($pdo->makeUpdate(array($table,array('name_en'=>'ok2'),6)) === 1);
		assert($pdo->makeUpdate(array($table,array('name_en'=>'ok2'),6)) === 0);
		assert($pdo->makeUpdate(array($table,array('name_en'=>'ok3'),6),array('beforeAfter'=>'assoc'))['before']['name_en'] === 'ok2');

		// makeDelete
		assert($pdo->makeDelete(array($table,array('id'=>6))) === 1);
		assert($pdo->makeDelete(array($table,array('id'=>6))) === 0);
		assert($pdo->makeDelete(array($table,array('id'=>array(7,8)))) === 2);
		assert($pdo->makeDelete(array($table,100),array('beforeAfter'=>'assoc')) === array('before'=>array('id'=>100,'name_en'=>'OK','active'=>null,'dateAdd'=>null),'query'=>1,'after'=>null));
		assert($pdo->alterAutoIncrement($table,0) instanceof \PdoStatement);

		// prepareRollback
		assert($pdo->prepareRollback('update',Base\Sql::make('update',array($table,array('name'=>'bla'),2)))['rollback']['id'] === 2);

		// makeCreate
		assert(($x = $pdo->makeCreate(array($table2,array(array('id','int','length'=>12,'null'=>null,'autoIncrement'=>true,'unsigned'=>true),array('name_en','varchar','length'=>200,'default'=>"L'article de james")),array(array('key','name_en'),array('primary','id'))))) instanceof \PDOStatement);
		assert($x->columnCount() === 0);

		// makeAlter
		assert($pdo->makeAlter(array($table2,array('james','varchar','length'=>100),array('unique','james'),array('name_en','rename'=>'namez','varchar','length'=>200,'default'=>'james'),null,array('name_en'))) instanceof \PDOStatement);

		// makeTruncate
		assert($pdo->makeInsert(array($table2,array('id'=>6,'namez'=>'ok'))) === 6);
		assert($pdo->makeTruncate(array($table2)) instanceof \PDOStatement);

		// makeDrop
		assert($pdo->makeDrop(array($table2)) instanceof \PDOStatement);

		// select
		assert(count($pdo->select(true,$table,null,array('id'=>'DESC'),"1,2")) === 2);
		assert(count($pdo->select(true,$table,array(array('name_en','like','james2')))) === 1);
		assert(count($pdo->select(true,$table,array(array('id','findInSet',2)))) === 1);
		assert(count($pdo->select(true,$table,array(array('id','>',1),'and','(',array('id','=',2),'or',array('id','=',3),')'))) === 2);
		assert(count($pdo->select(true,$table,array('id'=>array(1,2,4)))) === 2);
		assert(count($pdo->select(true,$table,array(array('id','notIn',array(2,3))))) === 1);
		assert(count($pdo->select(true,$table,null,array('id'=>'DESC'),"1,2")) === 2);
		assert($pdo->insert($table,array('id'=>4,'name_en'=>'james','dateAdd'=>Base\Date::mk(2017,1,5))) === 4);
		assert(count($pdo->select(true,$table,array(array('dateAdd','day',Base\Date::mk(2017,1,5))))) === 1);
		assert(count($pdo->select(true,$table,array(array('dateAdd','day',Base\Date::mk(2017,1,6))))) === 0);
		assert(count($pdo->select(true,$table,array(array('dateAdd','year',Base\Date::mk(2017,3))))) === 1);
		assert(count($pdo->select(true,$table,array(array('dateAdd','month',Base\Date::mk(2017,1,20))))) === 1);
		assert(count($pdo->select(true,$table,array(array('dateAdd','year',Base\Date::mk(2018,3))))) === 0);
		assert(count($pdo->select(true,$table,array(array('name_en','%like','ja'),'or',array('name_en','like%','s2')))) === 4);
		assert(count($pdo->select(true,$table,array(array('name_en','%like','ja'),'and',array('name_en','like%','s2')))) === 1);
		assert(count($pdo->select(true,$table,array(array('id','findInSet',array(1,2))))) === 0);
		assert(count($pdo->select(true,$table,array(array('id','or|findInSet',array(1,2))))) === 2);
		assert($pdo->delete($table,array('id'=>4)) === 1);

		// selectNum
		assert(Base\Arr::isIndexed($pdo->selectNum(true,$table)));

		// selectNums
		assert(count($pdo->selectNums(true,$table)) === 3);

		// selectAssoc
		assert($pdo->selectAssoc('id',$table) === array('id'=>1));
		assert($pdo->selectAssoc('id',$table,1000) === null);

		// selectAssocs
		assert(count($pdo->selectAssocs(array('id','name_en'),$table)[0]) === 2);
		assert($pdo->selectAssocs('id',$table,1000) === array());

		// selectAssocsUnique
		assert($pdo->selectAssocsUnique("*",$table)[2]['name_en'] === 'james2');

		// selectAssocsKey
		assert($pdo->selectAssocsKey("name_en","*",$table)['james']['name_en'] === 'james');
		assert($pdo->selectAssocsKey(1,"*",$table)['james']['name_en'] === 'james');

		// selectAssocsPrimary
		assert($pdo->selectAssocsPrimary("*",$table)[1]['id'] === 1);

		// selectNumsKey
		assert($pdo->selectNumsKey(1,"*",$table)['james'][1] === 'james');

		// selectObjsKey
		assert($pdo->selectObjsKey('name_en','*',$table)['james'] instanceof \stdclass);
		assert($pdo->selectObjsKey(1,'*',$table)['james'] instanceof \stdclass);

		// selectColumnIndex
		assert($pdo->selectColumnIndex(0,'*',$table,null,array('id'=>'desc')) === 3);

		// selectColumnsIndex
		assert($pdo->selectColumnsIndex(0,'*',$table,null,array('id'=>'desc')) === array(3,2,1));

		// selectColumnsGroup
		assert(count($pdo->selectColumnsGroup(3,'*',$table)) === 2);

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
		assert($pdo->showColumn(1,"COLUMNS FROM $table") === "int(11) unsigned");

		// showColumns
		assert($pdo->showColumns('Field',"COLUMNS FROM $table") === array('id','name_en','active','dateAdd'));
		assert($pdo->showColumns(2,"COLUMNS FROM $table") === array('NO','YES','YES','YES'));

		// showkeyValue
		assert($pdo->showkeyValue('Field','Type',"COLUMNS FROM $table") === array('id'=>'int(11) unsigned'));
		assert($pdo->showkeyValue(0,1,"COLUMNS FROM $table") === array('id'=>'int(11) unsigned'));

		// showkeyValues
		assert(count($pdo->showkeyValues('Field','Type',"COLUMNS FROM $table")) === 4);
		Base\Sql::setShortcut("pe","pe");
		assert(count($pdo->showkeyValues('Field','Ty[pe]',"COLUMNS FROM $table")) === 4);
		Base\Sql::unsetShortcut("pe");
		assert($pdo->showkeyValues('Field','Ty[pe]',"COLUMNS FROM $table") === array());

		// showCount
		assert($pdo->showCount("COLUMNS FROM $table") === 4);

		// showColumnCount
		assert($pdo->showColumnCount("COLUMNS FROM $table") === 6);

		// insert
		assert($pdo->insert($table,array('id'=>9,'name_en'=>'NINE')) === 9);
		assert($pdo->insert($table) === null);
		assert($pdo->insert($table,array()) === 10);
		assert($pdo->delete($table,10) === 1);

		// inserts
		assert($pdo->inserts($table,array('id','name_en'),array(99,'OK'),array(100,'YEP')) === array(99,100));
		assert($pdo->delete($table,99) === 1);
		assert($pdo->delete($table,100) === 1);
		$pdo->alterAutoIncrement($table);

		// insertCount
		assert($pdo->insertCount($table,array('id'=>11,'name_en'=>'NINE')) === 1);

		// insertBeforeAfter
		assert($pdo->insertBeforeAfter($table,array('id'=>12,'name_en'=>'douze')) === array('before'=>null,'query'=>12,'after'=>array('id'=>12,'name_en'=>'douze','active'=>null,'dateAdd'=>null)));
		assert($pdo->delete($table,12) === 1);
		assert($pdo->insertBeforeAfter($table,array()) === array('before'=>null,'query'=>13,'after'=>null));
		assert($pdo->delete($table,13) === 1);

		// insertBeforeAfters
		assert($pdo->insertBeforeAfters($table,array('id'=>12,'name_en'=>'douze')) === array('before'=>array(),'query'=>12,'after'=>array(array('id'=>12,'name_en'=>'douze','active'=>null,'dateAdd'=>null))));
		assert($pdo->delete($table,12) === 1);

		// update
		assert($pdo->update($table) === null);
		assert($pdo->update($table,array('id'=>10),array('name_en'=>'NINE'),array('id'=>'asc'),1) === 1);
		assert($pdo->update($table,array('id'=>12),array('name_en'=>'NINE'),array('id'=>'asc'),1) === 1);

		// updateBeforeAfter
		assert($pdo->updateBeforeAfter($table,array('name_en'=>'james'),array('id'=>12))['after']['name_en'] === 'james');
		assert($pdo->updateBeforeAfter($table,array('name_en'=>'james'),array('id'=>12))['query'] === 0);

		// updateBeforeAfters
		assert($pdo->updateBeforeAfters($table,array('name_en'=>'james'),array('id'=>12))['after'][0]['name_en'] === 'james');

		// delete
		assert($pdo->delete($table) === null);
		assert($pdo->delete($table,array('id'=>12),array('id'=>'asc'),1) === 1);

		// deleteBeforeAfter
		assert($pdo->deleteBeforeAfter($table,11) === array('before'=>array('id'=>11,'name_en'=>'NINE','active'=>null,'dateAdd'=>null),'query'=>1,'after'=>null));

		// deleteBeforeAfters
		assert($pdo->deleteBeforeAfters($table,11) === array('before'=>array(),'query'=>0,'after'=>array()));

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
		assert($pdo->insert($table,array('id'=>1,'name_en'=>'james','dateAdd'=>10)));
		assert($pdo->insert($table,array('id'=>2,'name_en'=>'james2','dateAdd'=>11)));
		assert($pdo->insert($table,array('id'=>3,'name_en'=>'james3','dateAdd'=>10)));
		assert($pdo->selectCount($table,null,array('id'=>'desc'),3) === 3);
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
		assert($pdo->selectDistinct('name_en',$table) === array('james','james2','james3'));
		assert($pdo->selectDistinct('dateAdd',$table) === array(10,11));

		// selectColumn
		assert($pdo->selectColumn('id',$table,null,array('id'=>'desc')) === 3);
		assert($pdo->selectColumn(array('dateAdd','sum()'),$table) === 31);
		$pdo->setDebug();
		assert($pdo->selectColumn('id',$table,null,array('id'=>'desc'),array(2,3))['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC LIMIT 2 OFFSET 3');
		assert($pdo->selectColumn('id',$table,null,array('id'=>'desc'))['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC LIMIT 1');
		assert($pdo->makeSelect(array(array('id'),$table,null,array('id'=>'desc')))['sql'] === 'SELECT `id` FROM `main` ORDER BY `id` DESC');
		$pdo->setDebug();

		// selectColumns
		assert($pdo->selectColumns('id',$table,null,array('id'=>'desc')) === array(3,2,1));
		assert($pdo->selectColumns(array('dateAdd','distinct()'),$table) === array(10,11));

		// selectKeyPair
		assert($pdo->selectKeyPair('id','name_en',$table,null,array('id'=>'desc')) === array(3=>'james3'));
		assert($pdo->selectKeyPair('id','name_en',$table,1000) === null);

		// selectKeyPairs
		assert($pdo->selectKeyPairs('name_en','id',$table,array('id'=>array(2,1))) === array('james'=>1,'james2'=>2));
		assert($pdo->selectKeyPairs('id','name_en',$table,1000) === array());
		assert($pdo->setDebug(true)->selectKeyPairs('id','name_[lang]','main',2)['sql'] === 'SELECT `id`, `name_en` FROM `main` WHERE `id` = 2');
		$pdo->setDebug();

		// selectPrimary
		assert($pdo->selectPrimary($table,array('id'=>array(3,2))) === 2);

		// selectPrimaries
		assert($pdo->selectPrimaries($table) === array(1,2,3));

		// selectPrimaryPair
		assert($pdo->selectPrimaryPair('name_en',$table) === array(1=>'james'));

		// selectPrimaryPairs
		assert($pdo->selectPrimaryPairs('name_en',$table,1) === array(1=>'james'));

		// selectSegment
		assert($pdo->selectSegment("[id] [name_en]",$table,1) === '1 james');

		// selectSegments
		assert($pdo->selectSegments("[id] [name_en]",$table,null,array('id'=>'desc')) === array(3=>'3 james3',2=>'2 james2',1=>'1 james'));

		// selectSegmentAssoc
		assert($pdo->selectSegmentAssoc("[name_en] [id] [id]",$table,1) === array('id'=>1,'name_en'=>'james'));

		// selectSegmentAssocs
		assert($pdo->selectSegmentAssocs("[name_en] [id] [id]",$table)[0] === array('id'=>1,'name_en'=>'james'));

		// selectSegmentAssocsKey
		assert($pdo->selectSegmentAssocsKey("[name_en] [id] [id]",$table)[1] === array('id'=>1,'name_en'=>'james'));

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

		// showTablesStatus
		assert(count($pdo->showTablesStatus()) > 6);
		assert(count($pdo->showTablesStatus('log%')) === 6);

		// showTableAutoIncrement
		assert($pdo->showTableAutoIncrement($table) === 4);

		// showTablesColumns
		assert(Base\Column::is($pdo->showTablesColumns()));

		// showTablesColumnsField
		assert(Base\Column::is($pdo->showTablesColumnsField()));

		// showTableColumn
		assert($pdo->setDebug(true)->showTableColumn('main','name_[lang]')['sql'] === "SHOW COLUMNS FROM `main` WHERE FIELD = 'name_en'");
		$pdo->setDebug();
		assert(count($pdo->showTableColumn('main','id')) === 6);

		// showTableColumnField
		assert($pdo->showTableColumnField('main','id') === 'id');
		assert($pdo->showTableColumnField('main','idzzz') === null);

		// showTableColumns
		assert($pdo->showTableColumns('main')['id']['Field'] === 'id');

		// showCountTableColumns
		assert($pdo->showCountTableColumns('main') === 4);

		// showTableColumnsField
		assert($pdo->showTableColumnsField('main') === array('id','name_en','active','dateAdd'));

		// updateColumn
		assert($pdo->updateColumn('name_en','james44',$table,1) === 1);
		assert($pdo->updateColumn('name_en','james44',$table,1) === 0);
		assert($pdo->selectAssocsPrimary("*",$table)[1]['name_en'] === 'james44');

		// updateIncrement
		assert($pdo->updateIncrement('dateAdd',1,$table,1) === 1);
		assert($pdo->updateIncrement('dateAdd',2,$table,1) === 1);
		assert($pdo->updateIncrement('dateAdd',0,$table,1) === null);
		assert($pdo->updateIncrement('name_en',2,$table,1) === null);
		assert($pdo->selectAssocsPrimary("*",$table)[1]['dateAdd'] === 13);

		// updateDecrement
		assert($pdo->updateDecrement('dateAdd',3,$table,1) === 1);
		assert($pdo->updateDecrement('dateAdd',4,$table,1) === 1);
		assert($pdo->updateDecrement('dateAdd',4,$table,1,array('beforeAfter'=>'assoc'))['before']['dateAdd'] === 6);
		assert($pdo->selectAssocsPrimary("*",$table)[1]['dateAdd'] === 2);

		// deleteTrim
		assert($pdo->insert($table,array('id'=>20)));
		assert($pdo->insert($table,array('id'=>21)));
		assert($pdo->insert($table,array('id'=>22)));
		assert($pdo->insert($table,array('id'=>23)));
		assert($pdo->insert($table,array('id'=>24)));
		assert($pdo->insert($table,array('id'=>25)));
		assert($pdo->insert($table,array('id'=>26)));
		assert($pdo->insert($table,array('id'=>27)));
		assert($pdo->deleteTrim($table,5) === 6);
		assert($pdo->selectCount($table) === 5);

		// alterAutoIncrement
		assert($pdo->alterAutoIncrement('main',20) instanceof \PDOStatement);

		// emulate
		$sql = Base\Sql::select("*",$table,array(2,'active'=>'bla'),true,4);
		assert($pdo->emulate($sql['sql'],$sql['prepare']) === "SELECT * FROM `main` WHERE `id` = 2 AND `active` = 'bla' ORDER BY `id` ASC LIMIT 4");

		// debug
		assert(count($pdo->debug(Base\Sql::select("*",$table,array(2,'active'=>'bla'),true,4))) === 7);

		// sql
		assert($pdo->sql() instanceof Orm\PdoSql);
		assert($pdo->sql()->clone()->db() === $pdo);

		// isDriver
		assert(Orm\Pdo::isDriver('mysql'));
		assert(!Orm\Pdo::isDriver('oracle'));

		// isOutput
		assert(Orm\Pdo::isOutput('select',true));
		assert(Orm\Pdo::isOutput('select','assoc'));
		assert(Orm\Pdo::isOutput('select',array('columns','arg'=>2)));
		assert(!Orm\Pdo::isOutput('update','assoc'));
		assert(!Orm\Pdo::isOutput('select','insertId'));
		assert(Orm\Pdo::isOutput('insert','insertId'));
		assert(Orm\Pdo::isOutput('insert','rowCount'));
		assert(Orm\Pdo::isOutput('insert',true));
		assert(Orm\Pdo::isOutput('insert','statement'));
		assert(!Orm\Pdo::isOutput('insert','assoc'));
		assert(Orm\Pdo::isOutput('select','*'));
		assert(Orm\Pdo::isOutput('create',true));
		assert(Orm\Pdo::isOutput('create','statement'));
		assert(Orm\Pdo::isOutput('create',null));
		assert(!Orm\Pdo::isOutput('create','assoc'));

		// parseDsn
		$dsn = "mysql:host=localhost;dbname=quid995";
		assert(count(Orm\Pdo::parseDsn($dsn,'utf8mb4')) === 7);
		assert(Orm\Pdo::parseDsn($dsn,'utf8')['dbname'] === 'quid995');

		// parseDataType
		assert(Orm\Pdo::parseDataType("str") === \Pdo::PARAM_STR);
		assert(Orm\Pdo::parseDataType(array()) === null);
		assert(Orm\Pdo::parseDataType(1.2) === \Pdo::PARAM_STR);

		// parseFetch
		assert(Orm\Pdo::parseFetch('assoc') === 2);
		assert(Orm\Pdo::parseFetch(\Pdo::FETCH_OBJ) === 5);
		assert(Orm\Pdo::parseFetch('assocz') === null);

		// output
		assert(Orm\Pdo::output('select',true) === array('method'=>'fetchAll','fetch'=>2,'type'=>'assocs'));
		assert(Orm\Pdo::output('insert','assoc') === null);
		assert(Orm\Pdo::output('select','insertId') === null);
		assert(Orm\Pdo::output('insert',true) === array('method'=>'lastInsertId','type'=>'insertId'));
		assert(Orm\Pdo::output('create',true) === array('type'=>'statement'));
		assert(Orm\Pdo::output('create','assoc') === null);
		assert(Orm\Pdo::output('create','statement') === array('type'=>'statement'));
		assert(Orm\Pdo::output('insert','insertId') === array('method'=>'lastInsertId','type'=>'insertId'));
		assert(Orm\Pdo::output('show','obj') === array('method'=>'fetchObject','selectLimit'=>1,'type'=>'obj'));
		assert(Orm\Pdo::output('select','objs') === array('method'=>'fetchAll','fetch'=>5,'type'=>'objs'));
		assert(Orm\Pdo::output('select',array('columns','arg'=>2)) === array('method'=>'fetchAll','fetch'=>7,'arg'=>array(2),'type'=>'columns'));
		assert(Orm\Pdo::output('select',array('beforeAfter'=>'assoc')) === array('type'=>'statement'));
		assert(Orm\Pdo::output('select',null) === array('type'=>'statement'));
		assert(Orm\Pdo::output('delete',true) === array('method'=>'rowCount','type'=>'rowCount'));
		assert(Orm\Pdo::output('update',true) === array('method'=>'rowCount','type'=>'rowCount'));
		assert(Orm\Pdo::output('delete',null) === array('type'=>'statement'));
		assert(Orm\Pdo::output('delete','statement') === array('type'=>'statement'));
		assert(Orm\Pdo::output('select','row') === null);
		assert(Orm\Pdo::output('select','segment')['fetch'] === 'segment');

		// outputKey
		assert(Orm\Pdo::outputKey(0,array(array(2,'test'),array(3,'test'))));
		$obj = new \stdclass;
		$obj->test = 2;
		$obj2 = new \stdclass;
		$obj2->test = 3;
		assert(Orm\Pdo::outputKey(0,array($obj,$obj2))[2] === $obj);

		// selectLimit
		assert(Orm\Pdo::selectLimit('assoc',array('what'=>'ok')) === array('what'=>'ok','limit'=>1));
		assert(Orm\Pdo::selectLimit('assocs',array('what'=>'ok')) === array('what'=>'ok'));

		// allDrivers
		assert(in_array('mysql',Orm\Pdo::allDrivers(),true));

		// setDefaultHistory
		$pdo::setDefaultHistory(true);

		// option
		assert(count($pdo->option()) === 8);

		// cleanup
		assert($pdo->truncate($table) instanceof \PDOStatement);
		assert($pdo->disconnect());
		
		return true;
	}
}
?>