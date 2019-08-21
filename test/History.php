<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// dbHistory
class DbHistory extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$boot = $data['boot'];
		$credentials = $boot->attr('assert/db');
		$table = "main";
		$pdo = new Orm\Pdo(...$credentials);
		$history = $pdo->history()->empty();
		assert($pdo->truncate($table) instanceof \PDOStatement);
		assert($pdo->inserts($table,array('id','name_en','dateAdd'),array(1,'james',10),array(2,'james2',11),array(3,'james3',10)) === array(1,2,3));
		$pdo->selectAlls($table);
		$pdo->selectAlls($table,null,array('id'=>'desc'));

		// construct

		// invoke
		assert($history(-1)['sql'] === 'SELECT * FROM `main` ORDER BY `id` DESC');

		// toString

		// cast
		assert($history->_cast() === 6);

		// add

		// all
		assert(count($history->all()) === 6);
		assert(count($history->all('insert')) === 3);

		// uni
		assert($history->keyValue(null,true) !== $history->keyValue());
		assert(count($history->keyValue()) === 6);
		assert($history->keyValue('truncate') === array('TRUNCATE TABLE `main`'));

		// typeCount
		assert($history->typeCount('select') === array('query'=>2,'row'=>6,'column'=>8,'cell'=>24));
		assert($history->typeCount('truncate') === array('query'=>1));

		// typeIndex
		assert($history->typeIndex('truncate')['sql'] === 'TRUNCATE TABLE `main`');
		assert($history->typeIndex('truncate',2) === null);
		assert($history->typeIndex('select')['sql'] === 'SELECT * FROM `main` ORDER BY `id` DESC');
		assert($history->typeIndex('select',-1)['sql'] === 'SELECT * FROM `main` ORDER BY `id` DESC');
		assert($history->typeIndex('select',0)['sql'] === 'SELECT * FROM `main`');

		// total
		assert(count($history->total()) === 3);

		// cleanup
		assert($pdo->truncate($table) instanceof \PDOStatement);
		
		return true;
	}
}
?>