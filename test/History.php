<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Orm;
use Quid\Base;

// history
// class for testing Quid\Orm\History
class History extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // prepare
        $boot = $data['boot'];
        $credentials = $boot->attr('assert/db');
        $table = 'main';
        $pdo = new Orm\Pdo(...$credentials);
        $history = $pdo->history()->empty();
        assert($pdo->truncate($table) instanceof \PDOStatement);
        assert($pdo->inserts($table,['id','name_en','dateAdd'],[1,'james',10],[2,'james2',11],[3,'james3',10]) === [1,2,3]);
        $pdo->selectAlls($table);
        $pdo->selectAlls($table,null,['id'=>'desc']);

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
        assert($history->keyValue('truncate') === ['TRUNCATE TABLE `main`']);

        // typeCount
        assert($history->typeCount('select') === ['query'=>2,'row'=>6,'column'=>8,'cell'=>24]);
        assert($history->typeCount('truncate') === ['query'=>1]);

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