<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// pdoSql
class PdoSql extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$boot = $data['boot'];
		$credentials = $boot->attr('assert/db');
		$table = "main";
		$pdo = new Orm\Pdo(...$credentials);
		$primary = $pdo->primary();
		assert($pdo->truncate($table) instanceof \PDOStatement);
		assert($pdo->inserts($table,array('id','active','name_en','dateAdd'),array(1,1,'james',10),array(2,2,'james2',20),array(3,3,'james3',30),array(4,4,'james4',40),array(5,5,'james5',50)) === array(1,2,3,4,5));

		// construct
		$sql = new Orm\PdoSql($pdo);
		$nav = $sql->clone()->what('*')->table($table)->page(2,2);

		// invoke
		assert($sql->setType('select')->whats('id','name_en')->table($table)('assoc') === array('id'=>1,'name_en'=>'james'));

		// toString
		assert(count($sql->toArray()) === 5);
		assert(!empty($sql->toJson()));

		// onPrepareKey

		// toArray

		// arr
		assert(count($sql->arr()) === 2);

		// cast
		assert($sql->_cast() === '(SELECT `id`, `name_en` FROM `main`)');
		$sql->empty();

		// primary
		assert($sql->primary() === 'id');

		// setType
		assert($sql->setType('select','assoc') instanceof Orm\PdoSql);
		assert($sql->getOutput() === 'assoc');
		$sql->setType('show');
		assert($sql->getOutput() === 'assoc');
		$sql->setType('insert');
		assert($sql->getOutput() === true);
		$sql->setType('select');

		// getType
		assert($sql->getType() === 'select');

		// setOutput
		assert($sql->setOutput(null));
		assert($sql->setOutput('*'));
		assert($sql->setOutput('debug'));
		assert($sql->setOutput(true));

		// getOutput
		assert($sql->getOutput() === true);

		// resetCount

		// getShortcut
		assert($sql->getShortcut('from') === 'table');
		$sql->insert();
		assert($sql->getShortcut('into') === 'table');
		assert($sql->getShortcut('from') === null);
		assert($sql->getShortcut('data') === 'insertSet');
		$sql->create();
		assert($sql->getShortcut('col') === 'createCol');
		assert($sql->setType('create') instanceof Orm\PdoSql);
		assert($sql->getShortcut('col') === 'createCol');
		assert($sql->setType('select') instanceof Orm\PdoSql);

		// getTable
		$sql->table($table);
		assert($sql->getTable() === $table);

		// checkTable
		assert($sql->checkTable() === $table);
		$sql->empty();

		// hasJoin
		$sql->select('*')->from('james');
		assert(!$sql->hasJoin());
		$sql->join('ok')->on("id",'=','james');
		assert($sql->hasJoin());

		// checkType
		assert($sql->checkType('select') === $sql);

		// checkClause

		// checkValue

		// checkShortcut

		// checkMake

		// do

		// one
		$sql->setType('select')->one('what',$primary,'count()')->one('table','table');
		assert($sql->get('what') === array(array('id','count()')));
		assert($sql->emulate() === "SELECT COUNT(`id`) FROM `table`");
		$sql->setType('insert')->one('insertSet',array('test'=>'ok'));

		// many
		$sql->setType('select')->many('what','james',array($primary,'count()'))->one('table','tablez');
		assert($sql->emulate() === "SELECT `james`, COUNT(`id`) FROM `tablez`");

		// prependOne
		$sql->select('test')->prependOne('what','ok')->table('OKz')->where(2)->where(true)->prependOne('where','active','>',3);
		assert($sql->emulate() === "SELECT `ok`, `test` FROM `OKz` WHERE `active` > 3 AND `id` = 2 AND `active` = 1");

		// prependMany
		$sql->empty()->table($table)->what('test')->prependMany('what','ok',array('test'=>'ok'),array('james','sum()'));
		assert($sql->emulate() === "SELECT SUM(`james`), `ok` AS `test`, `ok`, `test` FROM `main`");

		// exists
		assert($sql->setType('select')->empty()->whats('*','james','ok.lol')->table($table));
		assert(!$sql->exists('into'));
		assert($sql->exists('from','table'));

		// set
		assert($sql->setType('select')->empty()->whats('*','james','ok.lol')->table($table));
		assert($sql->get('what') === array('*','james','ok.lol'));
		assert($sql->set('what',array('*',$primary)) instanceof Orm\PdoSql);
		assert($sql->get('what') === array('*','id'));
		assert($sql->unset('what') instanceof Orm\PdoSql);
		assert($sql->get('what') === null);
		assert($sql->unset('from')->isEmpty());

		// make
		$sql->select('*')->table($table)->wheres(2,true,array('active'=>'name_en','Ok'=>2),'or','(',array('name_en','find',array(2,'lol')),'or',array('ok'=>'lol'),')');
		assert(count($sql->make()) === 6);

		// what
		$sql->empty()->what('test','sum()');

		// whats
		$sql->whats('james','ok',array('test2','count()'));

		// table
		$sql->table('OK');
		assert($sql->make()['sql'] === 'SELECT SUM(`test`), `james`, `ok`, COUNT(`test2`) FROM `OK`');

		// from
		$sql->from('james');
		assert($sql->make()['sql'] === 'SELECT SUM(`test`), `james`, `ok`, COUNT(`test2`) FROM `james`');

		// into
		assert($sql->insert()->into('james')->datas(array('test'=>null))->emulate() === "INSERT INTO `james` (`test`) VALUES (NULL)");

		// join
		$sql->select('*')->table($table)->join('james',array('id'=>'meh',array('james','[>]','test.ok')));
		assert($sql->emulate() === "SELECT * FROM `main` JOIN `james` ON(`id` = 'meh' AND `james` > test.ok)");
		$sql->select('*')->table($table)->join('james')->on('james','[=]','ok.lol')->on('james','`findInSet`','lol.lol');
		assert($sql->emulate() === "SELECT * FROM `main` JOIN `james` ON(`james` = ok.lol AND FIND_IN_SET(lol.`lol`, `james`))");

		// innerJoin
		$sql->select('*')->table($table)->innerJoin('james',array('id'=>'meh',array('james','[>]','test.ok')));
		assert($sql->emulate() === "SELECT * FROM `main` INNER JOIN `james` ON(`id` = 'meh' AND `james` > test.ok)");

		// outerJoin
		$sql->select('*')->table($table)->outerJoin('james',array('id'=>'meh',array('james','[>]','test.ok')));
		assert($sql->emulate() === "SELECT * FROM `main` LEFT OUTER JOIN `james` ON(`id` = 'meh' AND `james` > test.ok)");
		$sql->select('*')->table($table)->outerJoin('james')->on('james','[=]','ok.lol')->on('james','`findInSet`','lol.lol');
		assert($sql->emulate() === "SELECT * FROM `main` LEFT OUTER JOIN `james` ON(`james` = ok.lol AND FIND_IN_SET(lol.`lol`, `james`))");

		// on

		// ons

		// where
		$sql->select('*')->table($table)->where($primary,'>',2)->where('james',null)->where('ok','b|%like','lolz');
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `id` > 2 AND `james` IS NULL AND BINARY `ok` LIKE concat('lolz', '%')");
		$sql->select('*')->table($table)->where(2)->where(true)->where('active','=',2);
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `id` = 2 AND `active` = 1 AND `active` = 2");
		$sql->select('*')->table($table)->where(2)->where(true)->where('active','=',2)->wheres(array('active'=>3));
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `id` = 2 AND `active` = 3 AND `active` = 2");
		$sql->select('*')->table($table)->where('test',null)->where('test',true)->orders(array('id'=>'desc'))->order('test','asc');
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `test` IS NULL AND (`test` != '' AND `test` IS NOT NULL) ORDER BY `id` DESC, `test` ASC");

		// wheres
		$sql->select('*')->table($table)->wheres(2,true,array('active'=>'name_en','Ok'=>2),'or','(',array('name_en','findInSet',array(2,'lol')),'or',array('ok'=>'lol'),')');
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `id` = 2 AND `active` = 'name_en' AND `Ok` = 2 OR ((FIND_IN_SET(2, `name_en`) AND FIND_IN_SET('lol', `name_en`)) OR `ok` = 'lol')");

		// wheresOne
		$sql->select('*')->table($table)->wheresOne(array('active'=>1,array('james','in',array(1,23,3))));
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `active` = 1 AND `james` IN(1, 23, 3)");

		// whereSeparator

		// whereAnd

		// whereOr
		$sql->select('*')->table($table)->whereOr('=',array('test','james','deux'),2);
		assert($sql->emulate() === 'SELECT * FROM `main` WHERE (`test` = 2 OR `james` = 2 OR `deux` = 2)');

		// whereSeparatorMany
		$sql->select('*')->table($table)->whereSeparatorMany('XOR','||','=',array('test','james','deux'),array(1,2,3));
		assert($sql->emulate() === "SELECT * FROM `main` WHERE (`test` = 1 XOR `james` = 1 XOR `deux` = 1) || (`test` = 2 XOR `james` = 2 XOR `deux` = 2) || (`test` = 3 XOR `james` = 3 XOR `deux` = 3)");

		// whereAndMany
		$sql->select('*')->table($table)->whereAndMany('=',array('test','james','deux'),array(1,2,3));
		assert($sql->emulate() === "SELECT * FROM `main` WHERE (`test` = 1 AND `james` = 1 AND `deux` = 1) AND (`test` = 2 AND `james` = 2 AND `deux` = 2) AND (`test` = 3 AND `james` = 3 AND `deux` = 3)");
		$sql->select('*')->table($table)->whereAndMany('=',array('test','james','deux'),array(1,2,3),'or');
		assert($sql->emulate() === "SELECT * FROM `main` WHERE (`test` = 1 AND `james` = 1 AND `deux` = 1) OR (`test` = 2 AND `james` = 2 AND `deux` = 2) OR (`test` = 3 AND `james` = 3 AND `deux` = 3)");

		// whereOrMany
		$sql->select('*')->table($table)->whereOrMany('=',array('test','james','deux'),array(1,2,3));
		assert($sql->emulate() === "SELECT * FROM `main` WHERE (`test` = 1 OR `james` = 1 OR `deux` = 1) AND (`test` = 2 OR `james` = 2 OR `deux` = 2) AND (`test` = 3 OR `james` = 3 OR `deux` = 3)");
		$sql->select('*')->table($table)->whereOrMany('=',array('test','james','deux'),array(1,2,3),'or');
		assert($sql->emulate() === "SELECT * FROM `main` WHERE (`test` = 1 OR `james` = 1 OR `deux` = 1) OR (`test` = 2 OR `james` = 2 OR `deux` = 2) OR (`test` = 3 OR `james` = 3 OR `deux` = 3)");

		// whereAfter
		$sql->select('*')->table($table)->where('id',null)->whereAfter('name_en',2);
		assert($sql->emulate() === "SELECT * FROM `main` WHERE `id` IS NULL ORDER BY `name_en` ASC LIMIT 2");

		// group
		$sql->select('*')->table($table)->group('james','lavie');
		assert($sql->emulate() === 'SELECT * FROM `main` GROUP BY `james`, `lavie`');

		// order
		$sql->select('*')->table($table)->order($primary)->order($primary,'desc')->order('james',true);
		assert($sql->emulate() === "SELECT * FROM `main` ORDER BY `id` ASC, `id` DESC, `james` ASC");

		// orders
		$sql->select('*')->table($table)->orders($primary,array('james'=>'asc','lol'=>'desc'),true);
		assert($sql->emulate() === "SELECT * FROM `main` ORDER BY `id` ASC, `james` ASC, `lol` DESC, `id` ASC");

		// limit
		$sql->select('*')->table($table)->limit(2,3);
		assert($sql->emulate() === "SELECT * FROM `main` LIMIT 2 OFFSET 3");
		$sql->select('*')->table($table)->limit('2,3');
		assert($sql->emulate() === "SELECT * FROM `main` LIMIT 2,3");
		$sql->select('*')->table($table)->limit(array(2,3));
		assert($sql->emulate() === "SELECT * FROM `main` LIMIT 2 OFFSET 3");
		$sql->select('*')->table($table)->limit(array(true,3));
		assert($sql->emulate() === "SELECT * FROM `main` LIMIT ".PHP_INT_MAX." OFFSET 3");
		$sql->select('*')->table($table)->limit(array('page'=>4,'limit'=>2));
		assert($sql->emulate() === 'SELECT * FROM `main` LIMIT 2 OFFSET 6');
		$sql->select('*')->table($table)->limit(array('limit'=>2,'page'=>4));
		assert($sql->emulate() === 'SELECT * FROM `main` LIMIT 2 OFFSET 6');

		// page
		assert($sql->select("*")->table($table)->page(3,5)->emulate() === "SELECT * FROM `main` LIMIT 5 OFFSET 10");

		// insertSet
		$sql->insert($table)->insertSet('test',2)->insertSet('test',3);
		assert($sql->emulate() === "INSERT INTO `main` (`test`) VALUES (3)");

		// insertSets
		$sql->insert($table)->insertSets(array('test'=>2,'james'=>4))->insertSets(array('test'=>3));
		assert($sql->emulate() === "INSERT INTO `main` (`test`, `james`) VALUES (3, 4)");

		// updateSet
		$sql->update($table)->where(2)->updateSet('test',2)->updateSet('test',3);
		assert($sql->emulate() === "UPDATE `main` SET `test` = 3 WHERE `id` = 2");

		// updateSets
		$sql->update($table)->where(2)->where(true)->updateSets(array('test'=>2,'james'=>4))->updateSets(array('test'=>3));
		assert($sql->emulate() === "UPDATE `main` SET `test` = 3, `james` = 4 WHERE `id` = 2 AND `active` = 1");

		// data
		$sql->insert($table)->data('test',2)->data('test',3);
		assert($sql->emulate() === "INSERT INTO `main` (`test`) VALUES (3)");

		// datas
		$sql->update($table)->where(2)->where(true)->datas(array('test'=>2,'james'=>4))->updateSets(array('test'=>3));
		assert($sql->emulate() === "UPDATE `main` SET `test` = 3, `james` = 4 WHERE `id` = 2 AND `active` = 1");

		// col
		$sql->alter($table)->col(array('james','int'),array('ok','varchar'));
		assert($sql->emulate() === "ALTER TABLE `main` ADD COLUMN `james` INT(11) NULL DEFAULT NULL, ADD COLUMN `ok` VARCHAR(255) NULL DEFAULT NULL");

		// createCol
		$sql->create($table)->createCol(array('james','int'),array('ok','varchar'));
		assert($sql->emulate() === "CREATE TABLE `main` (`james` INT(11) NULL DEFAULT NULL, `ok` VARCHAR(255) NULL DEFAULT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

		// createKey
		$sql->create($table)->createCol(array('james','int'))->createKey(array('key','myKey'),array('primary',$primary));
		assert($sql->emulate() === "CREATE TABLE `main` (`james` INT(11) NULL DEFAULT NULL, KEY (`myKey`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

		// addCol
		$sql->alter($table)->addCol(array('james','int'),array('ok','varchar'));
		assert($sql->emulate() === "ALTER TABLE `main` ADD COLUMN `james` INT(11) NULL DEFAULT NULL, ADD COLUMN `ok` VARCHAR(255) NULL DEFAULT NULL");

		// addKey
		$sql->alter($table)->addKey(array('unique','ok',array('lol','Lol2')));
		assert($sql->emulate() === "ALTER TABLE `main` ADD UNIQUE KEY `ok` (`lol`, `Lol2`)");

		// alterCol
		$sql->alter($table)->alterCol(array('james','int','rename'=>'james2'));
		assert($sql->emulate() === "ALTER TABLE `main` CHANGE `james` `james2` INT(11) NULL DEFAULT NULL");

		// dropCol
		$sql->alter($table)->dropCol('test','lo.ol');
		assert($sql->emulate() === "ALTER TABLE `main` DROP COLUMN `test`, DROP COLUMN lo.`ol`");

		// dropKey
		$sql->alter($table)->dropKey('test','lo.ol');
		assert($sql->emulate() === "ALTER TABLE `main` DROP KEY `test`, DROP KEY lo.`ol`");

		// select
		assert($sql->select('*')->from('james')->emulate() === "SELECT * FROM `james`");
		assert($sql->select('*')->setOutput('assoc')->table($table)->make()['sql'] === 'SELECT * FROM `main` LIMIT 1');

		// assoc
		assert($sql->assoc('*')->from('james')->emulate() === "SELECT * FROM `james` LIMIT 1");

		// assocs
		assert($sql->assocs('*')->from('james')->emulate() === 'SELECT * FROM `james`');

		// show
		assert($sql->show('TABLES LIKE %lol%')->emulate() === "SHOW TABLES LIKE %lol%");

		// insert
		assert($sql->insert('james')->datas(array('test'=>2,'ok'=>'bla'))->emulate() === "INSERT INTO `james` (`test`, `ok`) VALUES (2, 'bla')");
		assert($sql->insert($table)->insertSets(array())->trigger() === 6);
		assert($sql->insert($table)->insertSets(array())->trigger() === 7);
		assert($sql->insert($table)->insertSets(array())->make()['sql'] === 'INSERT INTO `main` () VALUES ()');

		// update
		assert($sql->update('james')->datas(array('test'=>2,'ok'=>'bla'))->where(2)->emulate() === "UPDATE `james` SET `test` = 2, `ok` = 'bla' WHERE `id` = 2");

		// delete
		assert($sql->delete('james')->where('james',false)->emulate() === "DELETE FROM `james` WHERE (`james` = '' OR `james` IS NULL)");

		// create
		assert($sql->create('james')->col(array('james','varchar'))->emulate() === "CREATE TABLE `james` (`james` VARCHAR(255) NULL DEFAULT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4");

		// alter
		assert($sql->alter('james')->col(array('james','varchar'))->emulate() === "ALTER TABLE `james` ADD COLUMN `james` VARCHAR(255) NULL DEFAULT NULL");

		// truncate
		assert($sql->truncate('james')->emulate() === "TRUNCATE TABLE `james`");

		// drop
		assert($sql->drop('james')->emulate() === "DROP TABLE `james`");

		// parseLimit
		assert($nav->parseLimit() === array('offset'=>2,'limit'=>2,'page'=>2));

		// getOffset
		assert($nav->getOffset() === 2);

		// getLimit
		assert($nav->getLimit() === 2);

		// getPage
		assert($nav->getPage() === 2);

		// pageBase

		// isPage
		assert($nav->isPage());
		assert($nav->isPage(3));
		assert(!$nav->isPage(5));
		assert(!$nav->isPage(-1));

		// isPageFull
		assert($nav->isPageFull());
		assert(!$nav->isPageFull(4));

		// isSpecificInPage
		assert(!$nav->isSpecificInPage(1));
		assert($nav->isSpecificInPage(3));
		assert($nav->isSpecificInPage(1,1));

		// pageMax
		assert($nav->pageMax() === 4);

		// pageFromIndex
		assert($nav->pageFromIndex(0) === 1);
		assert($nav->pageFromIndex(2) === 2);
		assert($nav->pageFromIndex(-1) === null);

		// pages
		assert($nav->pages() === array(1,2,3,4));

		// pagesPosition
		assert($nav->pagesPosition() === array(1=>-1,2=>0,3=>1,4=>2));

		// pagesClose
		assert($nav->pagesClose(1) === array(1,2,3,4));

		// pageSpecificCount
		assert($nav->pageSpecificCount() === 2);
		assert($nav->pageSpecificCount(3) === 2);
		assert($nav->pageSpecificCount(4) === 1);

		// pageFirst
		assert($nav->pageFirst() === 1);

		// pagePrev
		assert($nav->pagePrev() === 1);
		assert($nav->pagePrev(1) === null);

		// pageNext
		assert($nav->pageNext() === 3);
		assert($nav->pageNext(4) === null);

		// pageLast
		assert($nav->pageLast() === 4);

		// general
		assert(count($nav->general()) === 9);

		// pagesWithSpecific
		assert(count($nav->pagesWithSpecific()) === 4);

		// pageWithSpecific
		assert($nav->pageWithSpecific(1) === array(1,2));
		assert($nav->pageWithSpecific() === array(3,4));

		// pageFirstSpecific
		assert($nav->pageFirstSpecific(4) === 7);
		assert($nav->pageFirstSpecific() === 3);

		// pageLastSpecific
		assert($nav->pageLastSpecific(3) === 6);
		assert($nav->pageLastSpecific() === 4);

		// specificIndex
		assert($nav->specificIndex(4) === 3);
		$nav2 = $pdo->sql('select')->what('*')->from($table)->where('id','>',1);
		assert($nav2->specificIndex(4) === 2);
		assert($nav2->specificIndex(5) === 3);
		$nav3 = $pdo->sql('select')->what('*')->from($table)->order('name_en','desc');
		assert($nav3->specificIndex(5) === 0);
		$nav4 = $pdo->sql('select')->what('*')->from($table)->where('id','>',2);
		assert($nav4->specificIndex(6) === 3);
		assert($nav4->specificIndex(1) === null);

		// specificPage
		assert($nav->specificPage(6) === 3);
		assert($nav->specificPage(2) === 1);

		// specificFirst
		assert($nav->specificFirst() === 1);

		// specificPrev
		assert($nav->specificPrev(3) === 2);

		// specificPrevInPage
		assert($nav->specificPrevInPage(3) === null);
		assert($nav->specificPrevInPage(2) === 1);
		assert($nav->specificPrevInPage(1) === null);

		// specificNext
		assert($nav->specificNext(3) === 4);

		// specificNextInPage
		assert($nav->specificNextInPage(3) === 4);
		assert($nav->specificNextInPage(4) === null);

		// specificLast
		assert($nav->specificLast() === 7);

		// specific
		assert(count($nav->specific(2)) === 9);
		assert($nav->specific(-1) === null);

		// trigger
		$sql->select('*')->table($table)->where($table[1]);
		assert($sql->trigger()[0]['id'] === 1);

		// triggerCount
		assert($sql->insert($table)->datas(array())->trigger() === 8);
		assert($sql->select('*')->from($table) === $sql);
		assert($sql->triggerCount() === 8);
		assert($sql->limit(1000) === $sql);
		assert($sql->triggerCount() === 8);
		assert($sql->getOutput() === true);
		assert($sql->limit(3) === $sql);
		assert($sql->triggerCount() === 3);

		// triggerTableCount
		assert($sql->triggerTableCount() === 8);

		// triggerWhatCount
		assert($sql->triggerWhatCount() === 8);

		// triggerRowCount
		assert($sql->triggerRowCount() === 3);

		// isTriggerCountEmpty
		assert(!$nav->isTriggerCountEmpty());

		// isTriggerCountNotEmpty
		assert($nav->isTriggerCountNotEmpty());

		// emulate
		$sql->select('*')->table($table)->wheres(2,true,array('active'=>'name_en','Ok'=>2),'or','(',array('name_en','findInSet',array(2,'lol')),'or',array('ok'=>'lol'),')');
		assert(strlen($sql->emulate()) === 156);

		// debug
		assert(count($sql->debug()) === 7);

		// map
		$sql->empty();
		$sql['what'] = '*';
		$sql['from'] = 'james';
		$sql['where'] = array('active'=>1);
		assert($sql->emulate() === 'SELECT * FROM `james` WHERE `active` = 1');
		unset($sql['where']);
		assert($sql->emulate() === 'SELECT * FROM `james`');
		assert(!isset($sql['addCol']));
		assert($sql->overwrite($sql->arr()));
		assert($sql->emulate() === 'SELECT * FROM `james`');
		assert(count($sql) === 2);
		$sql->insert('james');
		$sql['data'] = array('test'=>2,'ok'=>'lol');
		assert($sql->emulate() === "INSERT INTO `james` (`test`, `ok`) VALUES (2, 'lol')");
		$sql['data'] = array();
		assert($sql->emulate() === 'INSERT INTO `james` () VALUES ()');

		// cleanup
		assert($pdo->truncate($table) instanceof \PDOStatement);
		$sql = null;
		
		return true;
	}
}
?>