<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Main;
use Quid\Orm;

// sql
// class for testing Quid\Orm\Sql
class Sql extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormSql';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,11,12,13],[2,2,'james2',20,21,22,23],[3,3,'james3',30,31,32,33],[4,4,'james4',40,41,42,43],[5,5,'james5',50,51,52,53]) === [1,2,3,4,5]);
        $tables = $db->tables();
        $tb = $db[$table];
        $cols = $tb->cols()->gets('id','name');
        $nav = $db->sql('select')->what('*')->table($table)->page(2,2);

        // construct
        $sql = new Orm\Sql($db);
        $lang = new Main\Lang(['fr','en']);
        $lang->overwrite($sql->setType('select')->whats('id','name')->table($tb)('keyPairs'));
        assert($lang[1] === 'james');
        assert($lang[2] === 'james2');
        assert(!empty(serialize($sql)));

        // setOutput

        // primary
        assert($sql->primary() === 'id');

        // getTableObject
        $sql->empty();
        assert($sql->getTableObject() === null);
        assert($sql->table('ormTable'));
        assert($sql->getTableObject() instanceof Orm\Table);

        // checkTableObject
        assert($sql->checkTableObject() instanceof Orm\Table);

        // filter

        // checkMake

        // row

        // rows
        assert($sql->rows($table)->make()['sql'] === 'SELECT * FROM `ormSql`');

        // fromArray

        // triggerTableCount
        assert($sql->triggerTableCount() === 5);

        // triggerRow
        assert($sql->select()->table($table)->where(2)->triggerRow() instanceof Orm\Row);

        // triggerRows
        assert($sql->select()->table($table)->where(2)->triggerRows() instanceof Orm\Rows);

        // triggerRowsChunk
        $sql->select()->table($table)->limit(3)->triggerRowsChunk(2,function($row,int $index,int $page) {
            assert(in_array($page,[1,2,3],true));

            if($row->id() === 4)
            return false;
            else
            return true;
        });

        // main
        $sql->setType('select')->one('what',$tb->col('id'),'count()')->one('table','table');
        assert($sql->get('what') === [['id','count()']]);
        assert($sql->emulate() === 'SELECT COUNT(`id`) FROM `table`');
        assert($sql->setType('select')->empty()->whats('*','james','ok.lol')->table($tb));
        assert($sql->get('what') === ['*','james','ok.lol']);
        assert($sql->set('what',['*',$tb->col('id')]) instanceof Orm\Sql);
        assert($sql->get('what') === ['*','id']);
        $sql->select('*')->table($tb)->where($tb->col('id'),'>',2)->where('james',null)->where('ok','b|%like','lolz');
        assert($sql->emulate() === "SELECT * FROM `ormSql` WHERE `id` > 2 AND `james` IS NULL AND BINARY `ok` LIKE concat('lolz', '%')");
        $sql->select('*')->table($tb)->whereSeparator('&&','=',$cols,2);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` WHERE (`id` = 2 && `name` = 2)');
        $sql->select('*')->table($tb)->whereSeparator('xor','=',$cols,2);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` WHERE (`id` = 2 XOR `name` = 2)');
        $sql->select('*')->table($tb)->whereAnd('=',$cols,2);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` WHERE (`id` = 2 AND `name` = 2)');
        $sql->select('*')->table($tb)->whereAnd('=',$cols,null);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` WHERE (`id` IS NULL AND `name` IS NULL)');
        $sql->select('*')->table($tb)->whereAnd('empty',$cols);
        assert($sql->emulate() === "SELECT * FROM `ormSql` WHERE ((`id` = '' OR `id` IS NULL) AND (`name` = '' OR `name` IS NULL))");
        $sql->select('*')->table($tb)->whereAnd('like',$cols,'james');
        assert($sql->emulate() === "SELECT * FROM `ormSql` WHERE (`id` LIKE concat('%', 'james', '%') AND `name` LIKE concat('%', 'james', '%'))");
        $sql->select('*')->table($tb)->whereAnd('or|like',$cols,['james','james2']);
        assert(strlen($sql->emulate()) === 194);
        $sql->select('*')->table($tb)->whereOr(null,$cols);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` WHERE (`id` IS NULL OR `name` IS NULL)');
        $sql->select('*')->table($tb)->order($tb->col('id'))->order($tb->col('id'),'desc')->order('james',true);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` ORDER BY `id` ASC, `id` DESC, `james` ASC');
        $sql->select('*')->table($tb)->orders($tb->col('id'),['james'=>'asc','lol'=>'desc'],true);
        assert($sql->emulate() === 'SELECT * FROM `ormSql` ORDER BY `id` ASC, `james` ASC, `lol` DESC, `id` ASC');
        $sql->create($tb)->createCol(['james','int'])->createKey(['key','myKey'],['primary',$tb->col('id')]);
        assert($sql->emulate() === 'CREATE TABLE `ormSql` (`james` INT(11) NULL DEFAULT NULL, KEY (`myKey`), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $sql->select('*')->setOutput('rows')->table($table)->where($tb[1]);
        assert($sql->trigger()->isCount(1));
        $sql->select()->setOutput('row')->table($table)->where('name','=','james');
        assert(($row = $sql->trigger()) instanceof Orm\Row);
        $sql->select()->setOutput('rowOut')->table($table)->where('name','=','james');
        assert($sql->trigger() === null);
        $row->unlink();
        $sql->row()->table($table)->where('name','=','james');
        assert($sql->trigger() instanceof Orm\Row);
        assert($sql->setOutput('assoc')->what('*')->trigger('row') instanceof Orm\Row);

        // cleanup
        assert($db->truncate($tb) instanceof \PDOStatement);
        $sql = null;

        return true;
    }
}
?>