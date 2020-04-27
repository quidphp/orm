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

// syntax
// class for testing Quid\Orm\Syntax
class Syntax extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // mysql
        $syntax = Orm\Syntax\Mysql::class;

        // isQuery
        assert($syntax::isQuery('select'));
        assert(!$syntax::isQuery('SELECTz'));

        // isQuote
        assert($syntax::isQuote("'test'"));
        assert(!$syntax::isQuote("'test"));

        // hasTickOrSpace
        assert($syntax::hasTickOrSpace('`test bla'));
        assert($syntax::hasTickOrSpace('test bla'));
        assert(!$syntax::hasTickOrSpace('test'));

        // isTick
        assert(!$syntax::isTick('test'));
        assert($syntax::isTick('`test`'));
        assert($syntax::isTick('test.`test`'));

        // isParenthesis
        assert($syntax::isParenthesis('('));
        assert($syntax::isParenthesis('(',true));
        assert(!$syntax::isParenthesis(')',true));
        assert($syntax::isParenthesis(')',false));

        // isKey
        assert($syntax::isKey('unique'));
        assert(!$syntax::isKey('uniquez'));

        // isColType
        assert(!$syntax::isColType('unique'));
        assert($syntax::isColType('tinyint'));
        assert($syntax::isColType('mediumint'));

        // isWhereSymbol
        assert($syntax::isWhereSymbol('!'));

        // isWhereSeparator
        assert($syntax::isWhereSeparator('AND'));
        assert(!$syntax::isWhereSeparator('('));

        // isWhereTwo
        assert($syntax::isWhereTwo(true));
        assert($syntax::isWhereTwo('null'));
        assert(!$syntax::isWhereTwo('like'));
        assert($syntax::isWhereTwo(234));

        // isOrderDirection
        assert($syntax::isOrderDirection('asc'));
        assert(!$syntax::isOrderDirection('ascz'));

        // isReturnSelect
        $select = $syntax::select('*','table',3);
        $update = $syntax::update('table',['name'=>'bla'],3);
        assert(!$syntax::isReturnSelect($select));
        assert($syntax::isReturnSelect($update));

        // isReturnRollback
        $select = $syntax::select('*','table',3);
        $update = $syntax::update('table',['name'=>'bla'],3);
        assert(!$syntax::isReturnRollback($select));
        assert($syntax::isReturnRollback($update));

        // isReturnTableId
        assert($syntax::isReturnTableId($select));
        assert($syntax::isReturnTableId($update));

        // hasDot
        assert($syntax::hasDot('test.`test`'));
        assert(!$syntax::hasDot('test'));

        // hasQueryClause
        assert($syntax::hasQueryClause('select','table'));
        assert($syntax::hasQueryClause('select','what'));
        assert(!$syntax::hasQueryClause('select','james'));

        // getQueryTypes
        assert(count($syntax::getQueryTypes()) === 9);

        // getQueryRequired
        assert($syntax::getQueryRequired('select') === ['what','table']);
        assert($syntax::getQueryRequired('update') === ['table','updateSet','where']);
        assert($syntax::getQueryRequired('updatez') === null);

        // getKeyWord
        assert($syntax::getKeyWord('unique') === 'UNIQUE KEY');
        assert($syntax::getKeyWord('uniquez') === null);

        // getColTypeAttr
        assert(count($syntax::getColTypeAttr('varchar')) === 2);
        assert($syntax::getColTypeAttr('varcharz') === null);

        // functionFormat
        assert($syntax::functionFormat('lower') === 'LOWER');

        // getWhatFunction
        assert(count($syntax::getWhatFunction('distinct')) === 2);
        assert($syntax::getWhatFunction('SUM')['parenthesis'] === true);

        // getWhereSymbol
        assert($syntax::getWhereSymbol('!') === '!=');
        assert($syntax::getWhereSymbol('!=') === '!=');

        // getWhereMethod
        assert($syntax::getWhereMethod('findInSet') === [Orm\Syntax::class,'whereFind']);
        assert($syntax::getWhereMethod('findz') === null);

        // getWhereSeparator
        assert($syntax::getWhereSeparator() === 'AND');
        assert($syntax::getWhereSeparator('or') === 'OR');
        assert($syntax::getWhereSeparator('AnD') === 'AND');
        assert($syntax::getWhereSeparator('&&') === '&&');
        assert($syntax::getWhereSeparator('xor') === 'XOR');

        // getOrderDirection
        assert($syntax::getOrderDirection('desc') === 'DESC');
        assert($syntax::getOrderDirection() === 'ASC');
        assert($syntax::getOrderDirection('ASC') === 'ASC');
        assert($syntax::getOrderDirection(true) === 'ASC');

        // invertOrderDirection
        assert($syntax::invertOrderDirection('desc') === 'ASC');
        assert($syntax::invertOrderDirection() === 'DESC');
        assert($syntax::invertOrderDirection(true) === 'DESC');

        // getOrderMethod
        assert($syntax::getOrderMethod('find') === [Orm\Syntax::class,'orderFind']);
        assert($syntax::getOrderMethod('findz') === null);

        // getSetMethod
        assert($syntax::getSetMethod('replace') === [Orm\Syntax::class,'setReplace']);

        // getQueryWord
        assert($syntax::getQueryWord('select') === 'SELECT');
        assert($syntax::getQueryWord('select','table') === 'FROM');
        assert($syntax::getQueryWord('select','where') === 'WHERE');
        assert($syntax::getQueryWord('drop','table',['dropExists'=>true]) === 'TABLE IF EXISTS');
        assert($syntax::getQueryWord('drop','table') === 'TABLE');
        assert($syntax::getQueryWord('create','table',['createNotExists'=>true]) === 'TABLE IF NOT EXISTS');

        // getReturn
        assert($syntax::getReturn() === ['sql'=>'']);
        assert($syntax::getReturn(['bla'=>'ok']) === ['sql'=>'']);
        assert($syntax::getReturn(['sql'=>'ok']) === ['sql'=>'ok']);

        // returnMerge
        assert(count($syntax::returnMerge(['sql'=>'test','prepare'=>['test'=>2],'james'=>true],['sql'=>'test2','prepare'=>['test'=>4,'test2'=>3]])['prepare']) === 2);
        assert($syntax::returnMerge(['sql'=>'test','prepare'=>['test'=>2],'james'=>true],['sql'=>'test2','prepare'=>['test'=>4,'test2'=>3]])['sql'] === 'testtest2');

        // tick
        assert($syntax::tick('test') === '`test`');
        assert($syntax::tick('test.test2') === 'test.`test2`');
        assert($syntax::tick('`test`.`test`') === '`test`.`test`');
        assert($syntax::tick('test_[lang]') === '`test_en`');
        assert($syntax::tick('test',['binary'=>true]) === 'BINARY `test`');
        assert($syntax::tick('test',['function'=>'LOWER']) === 'LOWER(`test`)');
        assert($syntax::tick('test',['function'=>'LOWER','binary'=>true]) === 'BINARY LOWER(`test`)');
        assert($syntax::tick('(SELECT * FROM table)') === '(SELECT * FROM table)');
        assert($syntax::tick('@rownum := @rownum + 1') === '@rownum := @rownum + 1');

        // untick
        assert($syntax::untick('test.`test`') === 'test.test');
        assert($syntax::untick('`test`.`test`') === 'test.test');
        assert($syntax::untick('`test`') === 'test');

        // quote
        assert($syntax::quote('test') === "'test'");
        assert($syntax::quote(2) === 2);
        assert($syntax::quote('test',fn($value) => Base\Str::upper($value)) === 'TEST');

        // quoteSet
        assert($syntax::quoteSet(['test',2,3]) === "'test',2,3");
        assert($syntax::quoteSet(['test','bla'],fn($value) => Base\Str::upper($value)) === 'TEST,BLA');

        // unquote
        assert($syntax::unquote("'test'") === 'test');

        // parenthesis
        assert($syntax::parenthesis('test') === '(test)');
        assert($syntax::parenthesis('') === '');

        // comma
        assert($syntax::comma('test') === ', ');
        assert($syntax::comma('test',false) === ',');
        assert($syntax::comma('',false) === '');

        // whereSeparator
        assert($syntax::whereSeparator('z') === ' AND ');
        assert($syntax::whereSeparator('z','or') === ' OR ');
        assert($syntax::whereSeparator('','or') === '');
        assert($syntax::whereSeparator(null,'or',false) === 'OR');

        // boolNull
        assert($syntax::boolNull(true) === 1);
        assert($syntax::boolNull(null) === 'NULL');

        // prepare
        assert(count($syntax::prepare()) === 2);

        // prepareValue
        assert($syntax::prepareValue(0) === '0');
        assert($syntax::prepareValue(1) === 1);
        assert($syntax::prepareValue(1.2) === 1.2);
        assert($syntax::prepareValue(true) === 1);
        assert($syntax::prepareValue(false) === 0);
        assert($syntax::prepareValue(null) === 'NULL');
        assert($syntax::prepareValue([1,2,3]) === '1,2,3');
        assert($syntax::prepareValue(['test'=>2,'james'=>3]) === '{"test":2,"james":3}');
        assert(strlen($syntax::prepareValue(new \Datetime('now'))) > 100);

        // value
        assert(strlen($syntax::value('test',[],$syntax::option())['sql']) >= 8);
        assert(count($syntax::value('test',['sql'=>':test_0','prepare'=>['test_0'=>2]],$syntax::option())['prepare']) === 2);
        assert($syntax::value('test',[],['quoteClosure'=>fn($value) => Base\Str::upper($value)])['sql'] === 'TEST');
        assert($syntax::value('test.bla',[],['quote'=>false])['sql'] === 'test.bla');
        assert($syntax::value(null,[])['sql'] === 'NULL');
        assert($syntax::value(true,[])['sql'] === '1');
        assert($syntax::value(false,[])['sql'] === '0');
        assert($syntax::value(1.2,[])['sql'] === '1.2');
        assert($syntax::value(1,[])['sql'] === '1');
        assert($syntax::value('test.james',[],['tick'=>true])['sql'] === 'test.`james`');
        assert(count($syntax::value('james',['sql'=>'','prepare'=>[1,2,3]],['prepare'=>true])['prepare']) === 4);
        assert($syntax::value('james@landre_ok',null,['quoteChar'=>['@','_']])['sql'] === "'james\@landre\_ok'");
        assert(current($syntax::value('james@landre_ok',null,$syntax::option(['quoteChar'=>['@','_']]))['prepare']) === "james\@landre\_ok");

        // valueSet
        assert(count($syntax::valueSet([1,2,'string',3],[],['prepare'=>true])['prepare']) === 1);
        assert(strlen($syntax::valueSet([1,2,'string',3],[],['prepare'=>true])['sql']) >= 17);

        // makeSet
        assert($syntax::makeSet([1,2,3,'TEST']) === '1,2,3,TEST');

        // makeDefault

        // addDefault
        assert($syntax::addDefault(null) === [true]);
        assert($syntax::addDefault(['test'=>true,true]) === ['test'=>true,true]);
        assert($syntax::addDefault(['test'=>true] === ['test'=>true,true]));

        // removeDefault
        assert($syntax::removeDefault(null) === []);
        assert($syntax::removeDefault(['test'=>true,true]) === ['test'=>true]);

        // sql

        // what
        assert($syntax::what('*')['sql'] === '*');
        assert($syntax::what(['james.test','ok','what','james'=>'ok'])['sql'] === 'james.`test`, `ok`, `what`, `ok` AS `james`');
        assert($syntax::what(['ok.lol','james.test'=>['test','distinct']])['sql'] === 'ok.`lol`, DISTINCT `test` AS james.`test`');
        assert($syntax::what('SUM(`test`), SUM(`bla`) AS james.`test`')['sql'] === 'SUM(`test`), SUM(`bla`) AS james.`test`');
        assert($syntax::what('id')['sql'] === 'id');
        assert($syntax::what(['id','*','test.james'])['sql'] === '`id`, *, test.`james`');
        assert($syntax::what(['id','name_[lang]','key_[lang]'])['sql'] === '`id`, `name_en`, `key_en`');
        assert($syntax::what(true,$syntax::option())['sql'] === '*');
        assert($syntax::what([true,'james.sql',true],$syntax::option())['sql'] === '*, james.`sql`, *');
        assert($syntax::what([['test','distinct','ok'],['james','distinct','what']])['sql'] === 'DISTINCT `test` AS `ok`, DISTINCT `james` AS `what`');
        assert($syntax::what([['test','ok'],['test2','ok2']])['sql'] === '`test` AS `ok`, `test2` AS `ok2`');
        assert($syntax::what(['distinct()'=>'test'])['sql'] === 'DISTINCT `test`');
        assert($syntax::what([['what','sum()']])['sql'] === 'SUM(`what`)');
        assert($syntax::what([['what','sum()']])['cast'] === true);
        assert(empty($syntax::what([['what','sum']])['cast']));
        assert($syntax::what([['(SELECT * FROM TABLE)','test']])['sql'] === '(SELECT * FROM TABLE) AS `test`');
        assert($syntax::what(['count()'=>'[DISTINCT `test`]'])['sql'] === 'COUNT(DISTINCT `test`)');

        // whatPrepare
        assert($syntax::whatPrepare(['test','ok','*']) === [['test'],['ok'],['*']]);
        assert($syntax::whatPrepare(['test'=>'james']) === [['james','test']]);
        assert($syntax::whatPrepare(['test'=>['ok','james']]) === [['ok','james','test']]);
        assert($syntax::whatPrepare([['ok','james']]) === [['ok','james']]);

        // whatOne
        assert($syntax::whatOne('*')['sql'] === '*');
        assert($syntax::whatOne('test')['sql'] === '`test`');

        // whatTwo
        assert($syntax::whatTwo('test','james')['sql'] === '`test` AS `james`');
        assert($syntax::whatTwo('test','sum()')['sql'] === 'SUM(`test`)');

        // whatThree
        assert($syntax::whatThree('test','sum','test')['sql'] === 'SUM(`test`)');
        assert($syntax::whatThree('test','sum','lol')['sql'] === 'SUM(`test`) AS `lol`');
        assert($syntax::whatThree('test','distinct','lol')['sql'] === 'DISTINCT `test` AS `lol`');
        assert($syntax::whatThree('test','sum()','lol')['sql'] === 'SUM(`test`) AS `lol`');
        assert($syntax::whatThree('test','sum()','lol')['cast'] === true);

        // whatFromWhere
        assert($syntax::whatFromWhere(['test'=>2,['id','in',[2,3,4]],'id'=>4],'t') === ['t.test','t.id']);
        assert($syntax::whatFromWhere('test') === ['*']);

        // table
        assert($syntax::table('test')['sql'] === '`test`');
        assert($syntax::table('test')['table'] === 'test');
        assert($syntax::table('`test`')['table'] === 'test');

        // join
        assert(strlen($syntax::join(['table'=>'james','on'=>['active'=>1,'james.tst'=>'deux']],$syntax::option())['sql']) >= 51);
        assert($syntax::join(['test','on'=>['active'=>4]],['table'=>'james'])['sql'] === '`test` ON(`active` = 4)');
        assert($syntax::join(['on'=>['active'=>3],'table'=>'LOL'],['table'=>'james'])['sql'] === '`LOL` ON(`active` = 3)');
        assert($syntax::join(['table'=>'lol','on'=>[['lol.id','`=`','session.id']]],$syntax::option())['sql'] === '`lol` ON(lol.`id` = session.`id`)');
        assert($syntax::join(['table'=>'lol','on'=>[['lol.id','[=]','session.id']]],$syntax::option())['sql'] === '`lol` ON(lol.`id` = session.id)');
        assert($syntax::join(['table'=>'lol','on'=>[['lol.id','=','session.id']]],$syntax::option(['prepare'=>false]))['sql'] === "`lol` ON(lol.`id` = 'session.id')");

        // innerJoin
        assert(count($syntax::innerJoin(['james',['active'=>1,'james.tst'=>'deux']])) === 1);

        // outerJoin
        assert(empty($syntax::outerJoin(['table'=>'james','on'=>['active'=>1,'james.tst'=>'deux']])['table']));

        // where
        assert($syntax::where([[30,'`between`',['userAdd','userModify']]])['sql'] === '30 BETWEEN `userAdd` AND `userModify`');
        assert($syntax::where([['id','`between`',[20,30]]])['sql'] === '`id` BETWEEN 20 AND 30');
        assert($syntax::where([['id','`between`',['james',3]]])['sql'] === '`id` BETWEEN `james` AND 3');
        assert($syntax::where([['name','findInSetOrNull',3]])['sql'] === '(FIND_IN_SET(3, `name`) OR `name` IS NULL)');
        assert($syntax::where([['id','in',[]],['james','=',2]])['sql'] === '`james` = 2');
        assert($syntax::where([['james','=',2],['id','in',[]]])['sql'] === '`james` = 2');
        assert($syntax::where([true,'id'=>2],$syntax::option())['id'] === 2);
        assert($syntax::where(['active'=>1])['sql'] === '`active` = 1');
        assert(strlen($syntax::where(['active'=>1,'OR','(','james'=>'deux','(','ok'=>'lol'],$syntax::option())['sql']) >= 58);
        assert($syntax::where("id=test AND james='2'")['sql'] === "id=test AND james='2'");
        assert($syntax::where([['active','[=]','james.bla']])['sql'] === '`active` = james.bla');
        assert($syntax::where(['active'=>[1,'james',3],['active','>','james2']])['sql'] === "`active` IN(1, 'james', 3) AND `active` > 'james2'");
        assert($syntax::where([true,'id'=>3],$syntax::option())['sql'] === '`active` = 1 AND `id` = 3');
        assert($syntax::where([true,3],$syntax::option())['sql'] === '`active` = 1 AND `id` = 3');
        assert($syntax::where([true,[1,2,3],$syntax::option()],$syntax::option())['sql'] === '`active` = 1 AND `id` IN(1, 2, 3)');
        assert($syntax::where([['active','[=]','james.bla']],$syntax::option())['sql'] === '`active` = james.bla');
        assert($syntax::where(['active'=>null])['sql'] === '`active` IS NULL');
        assert($syntax::where(['active'=>true])['sql'] === "(`active` != '' AND `active` IS NOT NULL)");
        assert($syntax::where(['active'=>false])['sql'] === "(`active` = '' OR `active` IS NULL)");
        assert($syntax::where(['active'=>[1,2,3]])['sql'] === '`active` IN(1, 2, 3)');
        assert(strlen($syntax::where(['active'=>['test'=>'ok','lol'=>'yeah']])['sql']) >= 20);
        assert($syntax::where([['active','=',null]])['sql'] === '`active` IS NULL');
        assert($syntax::where([['active','=',true]])['sql'] === '`active` = 1');
        assert($syntax::where([['active','=',false]])['sql'] === '`active` = 0');
        assert($syntax::where(2,$syntax::option())['whereOnlyId'] === true);
        assert($syntax::where([1,2,3],$syntax::option())['whereOnlyId'] === true);
        assert($syntax::where(['id'=>2],$syntax::option())['id'] === 2);
        assert($syntax::where(['id'=>[1,2,3]],$syntax::option())['whereOnlyId'] === true);
        assert($syntax::where(['id'=>2,'james'=>'ok'],$syntax::option())['whereOnlyId'] === false);
        assert($syntax::where(['id'=>[1,2,3],'james'=>'ok'],$syntax::option())['whereOnlyId'] === false);
        assert($syntax::where([['id','=',2],['test','=','james']],$syntax::option())['whereOnlyId'] === false);
        assert($syntax::where([['id','in',2]])['sql'] === '`id` IN(2)');
        assert($syntax::where([['id','like',2]])['sql'] === "`id` LIKE concat('%', 2, '%')");
        assert($syntax::where([['id','b|like',2]])['sql'] === "BINARY `id` LIKE concat('%', 2, '%')");
        assert($syntax::where([['id','b,l|like',2]])['sql'] === "BINARY LOWER(`id`) LIKE concat('%', 2, '%')");
        assert($syntax::where([['id','findInSet',[1,2,3]]])['sql'] === '(FIND_IN_SET(1, `id`) AND FIND_IN_SET(2, `id`) AND FIND_IN_SET(3, `id`))');
        assert($syntax::where([['id','or|findInSet',[1,2,3]]])['sql'] === '(FIND_IN_SET(1, `id`) OR FIND_IN_SET(2, `id`) OR FIND_IN_SET(3, `id`))');
        assert($syntax::where(['(',['ok','=',2]])['sql'] === '(`ok` = 2)');
        assert($syntax::where([['james',null,'what']])['sql'] === '`james` IS NULL');
        assert($syntax::where([['james','empty','what']])['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert($syntax::where(['id'=>3,'&&','james'=>2,'XOR','lol'=>3])['sql'] === '`id` = 3 && `james` = 2 XOR `lol` = 3');
        assert($syntax::where([['id','b|=','bla'],['id','b|in',[1,2,3]],['id','b|findInSet','OK']])['sql'] === "BINARY `id` = 'bla' AND BINARY `id` IN(1, 2, 3) AND FIND_IN_SET('OK', BINARY `id`)");
        assert($syntax::where([['id','l,b|=','james']])['sql'] === "BINARY LOWER(`id`) = LOWER('james')");
        assert($syntax::where([['username','l|notIn',['NOBODY','ADMIN']]])['sql'] === "LOWER(`username`) NOT IN(LOWER('NOBODY'), LOWER('ADMIN'))");
        assert($syntax::where([['id','in',[]]])['sql'] === '');
        assert($syntax::where([['id',23]])['sql'] === '`id` = 23');
        assert($syntax::where([['id','or|>',[2,4,5]],['james','or|=',['test','test2']]])['sql'] === "(`id` > 2 OR `id` > 4 OR `id` > 5) AND (`james` = 'test' OR `james` = 'test2')");
        assert($syntax::where([['id','or|>',[2]],['james','or|=',['test']]])['sql'] === "`id` > 2 AND `james` = 'test'");
        assert($syntax::where([['id','or|>',2],['james','or|=','test,test2']])['sql'] === "`id` > 2 AND `james` = 'test,test2'");
        assert($syntax::where([['id','or|!=',[null,true,2,false]]])['sql'] === '(`id` != NULL OR `id` != 1 OR `id` != 2 OR `id` != 0)');
        assert($syntax::where([['id','>',0]])['sql'] === "`id` > '0'");
        assert($syntax::where([['id','>',1]])['sql'] === '`id` > 1');
        assert($syntax::where([['id','>',1.2]])['sql'] === '`id` > 1.2');

        // whereDefault
        assert($syntax::whereDefault([true,3],$syntax::option()) === ['active'=>1,1=>['id','=',3]]);
        assert($syntax::whereDefault(true,$syntax::option()) === ['active'=>1]);
        assert($syntax::whereDefault(2,$syntax::option()) === [['id','=',2]]);
        assert($syntax::whereDefault([1,2,3],$syntax::option()) === [['id','in',[1,2,3]]]);
        assert($syntax::whereDefault([true,'james'=>2],$syntax::option()) === ['active'=>1,'james'=>2]);
        assert($syntax::whereDefault([true,'active'=>2],$syntax::option()) === ['active'=>2]);
        assert($syntax::whereDefault([2],$syntax::option()) === [['id','=',2]]);

        // wherePrepare
        assert(count($syntax::wherePrepare(['active'=>1])) === 1);
        assert(count($syntax::wherePrepare(['active'=>1,'james'=>'deux'])) === 3);
        assert(count($syntax::wherePrepare(['active'=>1,'OR','(','james'=>'deux','(','ok'=>'lol'])) === 9);
        assert(count($syntax::wherePrepare([')','active'=>1])) === 1);
        assert($syntax::wherePrepare([['active','=',1]]) === [['active','=',1]]);
        assert($syntax::wherePrepare([['active',null]]) == [['active',null]]);
        assert($syntax::wherePrepare([true,[1,2,3]]) === []);
        assert(count($syntax::wherePrepare(['active'=>1,'(','james'=>2,')'])) === 5);
        assert(count($syntax::wherePrepare(['active'=>1,'OR','(','james'=>2,')','lala'=>3])) === 7);
        assert($syntax::wherePrepare([['active','=',false]]) === [['active','=',false]]);
        assert(count($syntax::wherePrepare(['(',['ok','=',2]])) === 3);

        // wherePrepareOne
        assert($syntax::wherePrepareOne('active',1) === [['active','=',1]]);
        assert($syntax::wherePrepareOne('active',[1,2,3]) === [['active','in',[1,2,3]]]);
        assert($syntax::wherePrepareOne(0,'(') === [['(']]);
        assert($syntax::wherePrepareOne(0,'AND') === [['AND']]);
        assert($syntax::wherePrepareOne(0,'or') === [['or']]);
        assert($syntax::wherePrepareOne(0,['active','=',1]) === [['active','=',1]]);

        // whereCols
        assert($syntax::whereCols([['id','=',3],'james'=>2,['id','=',4],['ok','in',[1,3,3]]]) === ['id','james','ok']);

        // whereAppend
        assert($syntax::where($syntax::whereAppend(true,['james'=>3],[['james','in',[1,2,3]]]))['sql'] === '`active` = 1 AND `james` = 3 AND `james` IN(1, 2, 3)');
        assert($syntax::where($syntax::whereAppend(true,['james'=>[3,2,1]],[['james','in',[1,2,3]]]))['sql'] === '`active` = 1 AND `james` IN(3, 2, 1) AND `james` IN(1, 2, 3)');
        assert($syntax::where($syntax::whereAppend(true,1))['sql'] === '`active` = 1 AND `id` = 1');

        // wherePrimary
        assert($syntax::wherePrimary([['id','=',3]],$syntax::option()) === ['id'=>3,'whereOnlyId'=>true]);
        assert($syntax::wherePrimary([['id','in',[1,2,'3']]],$syntax::option()) === ['id'=>[1,2,3],'whereOnlyId'=>true]);
        assert($syntax::wherePrimary([['id','in',[1,'test',3]]],$syntax::option()) === null);
        assert($syntax::wherePrimary([['id','=','3'],['ok','=','bla']],$syntax::option()) === ['id'=>3,'whereOnlyId'=>false]);

        // whereOne
        assert($syntax::whereOne('and')['sql'] === ' AND ');
        assert($syntax::whereOne('(')['sql'] === '(');

        // whereTwo
        assert($syntax::whereTwo('james',null)['sql'] === '`james` IS NULL');
        assert($syntax::whereTwo('james','notNull')['sql'] === '`james` IS NOT NULL');
        assert($syntax::whereTwo('james',true)['sql'] === "(`james` != '' AND `james` IS NOT NULL)");
        assert($syntax::whereTwo('james','notEmpty')['sql'] === "(`james` != '' AND `james` IS NOT NULL)");
        assert($syntax::whereTwo('james',false)['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert($syntax::whereTwo('james','empty')['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert($syntax::whereTwo('james',23)['sql'] === '`james` = 23');

        // whereThreeMethod

        // whereThree
        $arr = [1,'bla'=>'ok','welp'];
        assert($syntax::whereThree('james','=',[1,2,3])['sql'] === "`james` = '1,2,3'");
        assert(count($syntax::whereThree('james','=',$arr,$syntax::option())['prepare']) === 1);
        assert($syntax::whereThree('james','or|=',[1,2,3])['sql'] === '(`james` = 1 OR `james` = 2 OR `james` = 3)');
        assert(count($syntax::whereThree('james','or|=',$arr,$syntax::option())['prepare']) === 1);
        assert($syntax::whereThree('james','=',null)['sql'] === '`james` IS NULL');
        assert($syntax::whereThree('james','[=]',$syntax::select('*','jacynthe')['sql'])['sql'] === '`james` = SELECT * FROM `jacynthe`');
        assert($syntax::whereThree('james','in',[1,2,3])['sql'] === '`james` IN(1, 2, 3)');
        assert($syntax::whereThree('james','notIn',[1,2,3])['sql'] === '`james` NOT IN(1, 2, 3)');
        assert($syntax::whereThree('james','`>=`','james.test')['sql'] === '`james` >= james.`test`');
        assert($syntax::whereThree('james','`notIn`',[1,2,'mymethod.james'])['sql'] === '`james` NOT IN(1, 2, mymethod.`james`)');
        assert($syntax::whereThree('james','[notIn]',[1,2,'mymethod.james'])['sql'] === '`james` NOT IN(1, 2, mymethod.james)');
        assert($syntax::whereThree('james','[b,l|notIn]',[2,'ok','test.col'])['sql'] === 'BINARY LOWER(`james`) NOT IN(2, LOWER(ok), LOWER(test.col))');
        assert($syntax::whereThree('james','`b,l,or|notFindInSet`',[2,'ok','test.col'])['sql'] === '(!FIND_IN_SET(2, BINARY LOWER(`james`)) OR !FIND_IN_SET(LOWER(`ok`), BINARY LOWER(`james`)) OR !FIND_IN_SET(LOWER(test.`col`), BINARY LOWER(`james`)))');
        assert($syntax::whereThree('james','`b,l|notFindInSet`',[2,'ok','test.col'])['sql'] === '(!FIND_IN_SET(2, BINARY LOWER(`james`)) AND !FIND_IN_SET(LOWER(`ok`), BINARY LOWER(`james`)) AND !FIND_IN_SET(LOWER(test.`col`), BINARY LOWER(`james`)))');

        // whereIn
        assert($syntax::whereIn('james',[2,'james',3],'in')['sql'] === "`james` IN(2, 'james', 3)");
        assert($syntax::whereIn('james','test','notIn')['sql'] === "`james` NOT IN('test')");
        assert($syntax::whereIn('james',['test'=>2],'notIn')['sql'] === '');

        // whereBetween
        assert($syntax::whereBetween('james',[10,20],'between',['tick'=>true])['sql'] === '`james` BETWEEN 10 AND 20');
        assert($syntax::whereBetween('james',[10,20],'notBetween',['tick'=>true])['sql'] === '`james` NOT BETWEEN 10 AND 20');

        // whereFind
        assert($syntax::whereFind('james','what,ok','find')['sql'] === "FIND_IN_SET('what,ok', `james`)");
        assert($syntax::whereFind('james',3,'find')['sql'] === 'FIND_IN_SET(3, `james`)');
        assert($syntax::whereFind('james','james2','notFind')['sql'] === "!FIND_IN_SET('james2', `james`)");
        assert($syntax::whereFind('james',[3,'james2','james3'],'find')['sql'] === "(FIND_IN_SET(3, `james`) AND FIND_IN_SET('james2', `james`) AND FIND_IN_SET('james3', `james`))");
        assert($syntax::whereFind('james',[3,'james2','james3'],'find',['separator'=>'or'])['sql'] === "(FIND_IN_SET(3, `james`) OR FIND_IN_SET('james2', `james`) OR FIND_IN_SET('james3', `james`))");

        // whereFindOrNull
        assert($syntax::whereFindOrNull('james',3,'find')['sql'] === '(FIND_IN_SET(3, `james`) OR `james` IS NULL)');
        assert($syntax::whereFindOrNull('james',[3,4,'jaems2'],'find')['sql'] === "(FIND_IN_SET(3, `james`) OR `james` IS NULL) AND (FIND_IN_SET(4, `james`) OR `james` IS NULL) AND (FIND_IN_SET('jaems2', `james`) OR `james` IS NULL)");

        // whereLike
        assert($syntax::whereLike('james.bla','okkk','like')['sql'] === "james.`bla` LIKE concat('%', 'okkk', '%')");
        assert($syntax::whereLike('james.bla','okkk','notLike',['binary'=>true])['sql'] === "BINARY james.`bla` NOT LIKE concat('%', 'okkk', '%')");
        assert($syntax::whereLike('james.bla','okkk','notLike%',['binary'=>true])['sql'] === "BINARY james.`bla` NOT LIKE concat('%', 'okkk')");
        assert(strlen($syntax::whereLike('james.bla',['bla',2,3],'%like')['sql']) === 109);
        assert(strlen($syntax::whereLike('james.bla',['bla',2,3],'%like',['separator'=>'or'])['sql']) === 107);
        assert($syntax::whereLike('james.bla','%','like')['sql'] === "james.`bla` LIKE concat('%', '\%', '%')");
        assert($syntax::whereLike('james.bla','_','like')['sql'] === "james.`bla` LIKE concat('%', '\_', '%')");
        assert($syntax::whereLike('james.bla','\\','like')['sql'] === "james.`bla` LIKE concat('%', '\\\\\\\\', '%')");
        assert(current($syntax::whereLike('james.bla','%','like',$syntax::option())['prepare']) === "\%");
        assert(current($syntax::whereLike('james.bla','_','like',$syntax::option())['prepare']) === "\_");
        assert(current($syntax::whereLike('james.bla','\\','like',$syntax::option())['prepare']) === '\\\\');

        // whereDate
        assert($syntax::whereDate('james',Base\Datetime::mk(2017,1,2),'year')['sql'] === '(`james` >= 1483246800 AND `james` <= 1514782799)');
        assert($syntax::whereDate('james',Base\Datetime::mk(2017,2,2),'month')['sql'] === '(`james` >= 1485925200 AND `james` <= 1488344399)');
        assert($syntax::whereDate('james',Base\Datetime::mk(2017,1,2),'day')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483419599)');
        assert($syntax::whereDate('james',Base\Datetime::mk(2017,1,2),'hour')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483336799)');
        assert($syntax::whereDate('james',Base\Datetime::mk(2017,1,2),'minute')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483333259)');
        assert($syntax::whereDate('james',['2017-01-02','ymd'],'day')['sql'] === '');
        assert($syntax::whereDate('james',[['2017-01-02','ymd']],'day')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483419599)');
        assert($syntax::whereDate('james',[Base\Datetime::mk(2017,1,2),Base\Datetime::mk(2017,1,3)],'month')['sql'] === '((`james` >= 1483246800 AND `james` <= 1485925199) AND (`james` >= 1483246800 AND `james` <= 1485925199))');
        assert($syntax::whereDate('james',[Base\Datetime::mk(2017,1,2),Base\Datetime::mk(2017,1,3)],'month',['separator'=>'or'])['sql'] === '((`james` >= 1483246800 AND `james` <= 1485925199) OR (`james` >= 1483246800 AND `james` <= 1485925199))');

        // group
        assert($syntax::group('test, test2.test, test')['sql'] === 'test, test2.test, test');
        assert($syntax::group(['test.test2','james'])['sql'] === 'test.`test2`, `james`');
        assert($syntax::group(true)['sql'] === '');

        // order
        assert($syntax::order(['test'=>true,'james'=>true])['sql'] === '`test` ASC, `james` ASC');
        assert($syntax::order(['test'=>'ASC','james','rand()','ok.test'=>'desc'],$syntax::option())['sql'] === '`test` ASC, `james` ASC, rand(), ok.`test` DESC');
        assert($syntax::order('test ASC, james DESC, rand()',$syntax::option())['sql'] === 'test ASC, james DESC, rand()');
        assert($syntax::order([['test','asc'],['order'=>'test2','direction'=>'asc']])['sql'] === '`test` ASC, `test2` ASC');
        assert($syntax::order([['james','findInSet','test']])['sql'] === 'FIND_IN_SET(`test`, `james`)');
        assert($syntax::order([[5,'findInSet','james']])['sql'] === 'FIND_IN_SET(`james`, `5`)');

        // orderPrepare
        assert($syntax::orderPrepare(['rand()']) === [['rand()']]);
        assert($syntax::orderPrepare(['test']) === [['test']]);
        assert($syntax::orderPrepare(['test'=>true]) === [['test',true]]);
        assert($syntax::orderPrepare(['test'=>'james']) === [['test','james']]);
        assert($syntax::orderPrepare([['test','ASC']]) === [['test','ASC']]);

        // orderOne
        assert($syntax::orderOne('rand()')['sql'] === 'rand()');
        assert($syntax::orderOne('test')['sql'] === '`test` ASC');

        // orderOneTwo
        assert($syntax::orderTwo('test','ASC')['sql'] === '`test` ASC');
        assert($syntax::orderTwo('test','desc')['sql'] === '`test` DESC');
        assert($syntax::orderTwo('test','james')['sql'] === '`test` ASC');

        // orderThree
        assert($syntax::orderThree('james','find','lala.col')['sql'] === 'FIND_IN_SET(lala.`col`, `james`)');

        // orderFind
        assert($syntax::orderFind('james','lala.col','find')['sql'] === 'FIND_IN_SET(lala.`col`, `james`)');

        // limit
        assert($syntax::limit('1,2')['sql'] === '1,2');
        assert($syntax::limit([1,2])['sql'] === '1 OFFSET 2');
        assert($syntax::limit([1])['sql'] === '1');
        assert($syntax::limit(1)['sql'] === '1');
        assert($syntax::limit([true,2],$syntax::option())['sql'] === PHP_INT_MAX.' OFFSET 2');
        assert($syntax::limit([true,true],$syntax::option())['sql'] === PHP_INT_MAX.' OFFSET '.PHP_INT_MAX);
        assert($syntax::limit(0)['sql'] === '0');
        assert($syntax::limit('0')['sql'] === '0');
        assert($syntax::limit([0])['sql'] === '0');
        assert($syntax::limit([1=>2])['sql'] === '2');
        assert($syntax::limit([3=>8])['sql'] === '8 OFFSET 16');
        assert($syntax::limit(['offset'=>3,'limit'=>10])['sql'] === '10 OFFSET 3');
        assert($syntax::limit(['limit'=>10,'offset'=>3])['sql'] === '10 OFFSET 3');
        assert($syntax::limit(['page'=>3,'limit'=>25])['sql'] === '25 OFFSET 50');

        // limitPrepare
        assert($syntax::limitPrepare(['2,3']) === [3,2]);
        assert($syntax::limitPrepare([4=>3]) === [3,9]);
        assert($syntax::limitPrepare([2=>2]) === [2,2]);

        // limitPrepareOne
        assert($syntax::limitPrepareOne(3,4) === [4,8]);

        // limitPrepareTwo
        assert($syntax::limitPrepareTwo(['page'=>3,'limit'=>25]) === [25,50]);

        // insertSet
        assert($syntax::insertSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '(`active`, `james`, `oK`, lol.`james`) VALUES (2, 3, NULL, 1)');
        assert($syntax::insertSet(['activezzz','testzz'])['sql'] === '');
        assert($syntax::insertSet([['wwactivezzz','wwwtestzz']])['sql'] === "(`wwactivezzz`) VALUES ('wwwtestzz')");
        assert($syntax::insertSet([])['sql'] === '() VALUES ()');
        assert($syntax::insertSet([['name','lower','TEST'],['id',4]])['sql'] === "(`name`, `id`) VALUES (LOWER('TEST'), 4)");

        // insertSetFields

        // setPrepare
        assert($syntax::setPrepare(['what','test'=>'ok',['active','replace','ok','wow']])[1] === ['active','replace','ok','wow']);
        assert(count($syntax::setPrepare(['active'=>false,'james'=>[1,2,3],'oK'=>null,'lol.james'=>true])) === 4);

        // setValues

        // updateSet
        assert($syntax::updateSet([['active','lower','test'],['id',4]])['sql'] === "`active` = LOWER('test'), `id` = 4");
        assert($syntax::updateSet([['active','replace','test','test2']])['sql'] === "`active` = REPLACE(`active`,'test','test2')");
        assert($syntax::updateSet(['active'=>false,'james'=>[1,2,3],'oK'=>null,'lol.james'=>true])['sql'] === "`active` = 0, `james` = '1,2,3', `oK` = NULL, lol.`james` = 1");
        assert($syntax::updateSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '`active` = 2, `james` = 3, `oK` = NULL, lol.`james` = 1');
        assert(count($syntax::updateSet(['james'=>[1,2,'name']],$syntax::option())['prepare']) === 1);
        assert($syntax::updateSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '`active` = 2, `james` = 3, `oK` = NULL, lol.`james` = 1');
        assert($syntax::updateSet(['active'=>null,'james'=>true,'ok'=>false])['sql'] === '`active` = NULL, `james` = 1, `ok` = 0');

        // setOne
        assert($syntax::setOne(2)['sql'] === '2');

        // setTwo
        assert($syntax::setTwo('lower',24)['sql'] === 'LOWER(24)');

        // setThree
        assert($syntax::setThree('james','replace','from','to')['sql'] === "REPLACE(`james`,'from','to')");

        // setReplace
        assert($syntax::setReplace('james','from','to','replace')['sql'] === "REPLACE(`james`,'from','to')");

        // col
        assert($syntax::col(['james'],$syntax::option())['sql'] === '');
        assert($syntax::col(['james','LOLLL'],$syntax::option())['sql'] === '');
        assert($syntax::col(['james','varchar'],$syntax::option())['sql'] === '`james` VARCHAR(255) NULL DEFAULT NULL');
        assert($syntax::col(['james','varchar','length'=>55,'default'=>'james','null'=>false],$syntax::option(['prepare'=>false]))['sql'] === "`james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert($syntax::col(['james','int'],$syntax::option())['sql'] === '`james` INT(11) NULL DEFAULT NULL');
        assert($syntax::col(['james','int','length'=>20,'default'=>3,'autoIncrement'=>true,'after'=>'james'],$syntax::option())['sql'] === '`james` INT(20) NULL DEFAULT 3 AUTO_INCREMENT AFTER `james`');
        assert($syntax::col(['james','int'],$syntax::option(['type'=>'addCol']))['sql'] === 'ADD COLUMN `james` INT(11) NULL DEFAULT NULL');
        assert($syntax::col(['james','int'],$syntax::option(['type'=>'alterCol']))['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');
        assert($syntax::col(['id','int','length'=>11,'autoIncrement'=>true,'null'=>null])['sql'] === '`id` INT(11) AUTO_INCREMENT');

        // makeCol
        assert($syntax::col(['james','int'],$syntax::option(['type'=>'createCol']))['sql'] === '`james` INT(11) NULL DEFAULT NULL');
        assert($syntax::col(['james','int'],$syntax::option(['type'=>'addCol']))['sql'] === 'ADD COLUMN `james` INT(11) NULL DEFAULT NULL');
        assert($syntax::col(['james','int'],$syntax::option(['type'=>'alterCol']))['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');

        // createCol
        assert($syntax::createCol(['james','varchar','length'=>55,'default'=>'james','null'=>false],$syntax::option(['prepare'=>false]))['sql'] === "`james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert($syntax::createCol([['james','varchar'],['name'=>'lol','type'=>'int']])['sql'] === '`james` VARCHAR(255) NULL DEFAULT NULL, `lol` INT(11) NULL DEFAULT NULL');

        // addCol
        assert($syntax::addCol(['james','varchar','length'=>55,'default'=>'james','null'=>false],$syntax::option(['prepare'=>false]))['sql'] === "ADD COLUMN `james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert($syntax::addCol([['james','varchar'],['name'=>'lol','type'=>'int']])['sql'] === 'ADD COLUMN `james` VARCHAR(255) NULL DEFAULT NULL, ADD COLUMN `lol` INT(11) NULL DEFAULT NULL');

        // alterCol
        assert($syntax::alterCol(['james','int'])['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');
        assert($syntax::alterCol(['james','int','rename'=>'james2','length'=>25])['sql'] === 'CHANGE `james` `james2` INT(25) NULL DEFAULT NULL');

        // dropCol
        assert($syntax::dropCol('test')['sql'] === 'test');
        assert($syntax::dropCol(['test'])['sql'] === 'DROP COLUMN `test`');
        assert($syntax::dropCol(['test_[lang]','test2.lala'])['sql'] === 'DROP COLUMN `test_en`, DROP COLUMN test2.`lala`');

        // key
        assert($syntax::key(['key'=>'key','col'=>'test'])['sql'] === 'KEY (`test`)');
        assert($syntax::key(['primary','test'])['sql'] === 'PRIMARY KEY (`test`)');
        assert($syntax::key(['primary',null])['sql'] === '');
        assert($syntax::key(['unique','test',['james.lol','ok']])['sql'] === 'UNIQUE KEY `test` (james.`lol`, `ok`)');
        assert($syntax::key(['unique','test',['james.lol','ok']])['sql'] === 'UNIQUE KEY `test` (james.`lol`, `ok`)');
        assert($syntax::key(['unique',null,['james.lol','ok']])['sql'] === '');
        assert($syntax::key(['unique','ok'])['sql'] === 'UNIQUE KEY `ok` (`ok`)');
        assert($syntax::key(['unique','ok','james'])['sql'] === 'UNIQUE KEY `ok` (`james`)');

        // makeKey
        assert($syntax::makeKey(['primary','id'],$syntax::option(['type'=>'createKey']))['sql'] === 'PRIMARY KEY (`id`)');
        assert($syntax::makeKey(['primary','id'],$syntax::option(['type'=>'addKey']))['sql'] === 'ADD PRIMARY KEY (`id`)');

        // createKey
        assert($syntax::createKey(['test'])['sql'] === '');
        assert($syntax::createKey(['primary','id'])['sql'] === 'PRIMARY KEY (`id`)');
        assert($syntax::createKey(['unique','james',['id','james']])['sql'] === 'UNIQUE KEY `james` (`id`, `james`)');
        assert($syntax::createKey(['key','id'])['sql'] === 'KEY (`id`)');
        assert($syntax::createKey([['key','id'],['unique','james',['id','james']]])['sql'] === 'KEY (`id`), UNIQUE KEY `james` (`id`, `james`)');

        // addKey
        assert($syntax::addKey('test bla')['sql'] === 'test bla');
        assert($syntax::addKey(['test'])['sql'] === '');
        assert($syntax::addKey(['primary','id'])['sql'] === 'ADD PRIMARY KEY (`id`)');
        assert($syntax::addKey(['unique','james',['id','james']])['sql'] === 'ADD UNIQUE KEY `james` (`id`, `james`)');
        assert($syntax::addKey([['key','id'],['unique','james',['id','james']]])['sql'] === 'ADD KEY (`id`), ADD UNIQUE KEY `james` (`id`, `james`)');

        // dropKey
        assert($syntax::dropKey('test')['sql'] === 'test');
        assert($syntax::dropKey(['test'])['sql'] === 'DROP KEY `test`');
        assert($syntax::dropKey(['test_[lang]','test2.lala'])['sql'] === 'DROP KEY `test_en`, DROP KEY test2.`lala`');

        // createEnd
        assert($syntax::createEnd($syntax::option())['sql'] === ') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // prepareDefault

        // make
        assert($syntax::make('select',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2])['sql'] === 'SELECT * FROM `user` WHERE `active` = 1 AND `james` = 2 ORDER BY `active` DESC LIMIT 2');
        assert($syntax::make('select',['*','where'=>true]) === null);
        assert($syntax::make('select',['*','table'=>null]) === null);
        assert($syntax::make('select',['*','ok'])['sql'] === 'SELECT * FROM `ok`');
        assert($syntax::make('select',['*','ok'])['type'] === 'select');
        assert($syntax::make('select',['*','ok'])['table'] === 'ok');
        assert(strlen($syntax::make('select',['join'=>['table'=>'lol','on'=>true],'*','ok','order'=>['type'=>'asc'],'where'=>true])['sql']) === 85);
        assert(strlen($syntax::make('select',['outerJoin'=>['table'=>'lol','on'=>true],'*','ok','order'=>['type'=>'asc'],'where'=>true])['sql']) === 96);
        assert($syntax::make('select',['*','user',['active'=>1,'james'=>'tes\'rttté'],['active'=>'DESC'],2],['prepare'=>false])['sql'] === "SELECT * FROM `user` WHERE `active` = 1 AND `james` = 'tes\'rttté' ORDER BY `active` DESC LIMIT 2");
        assert($syntax::make('select',[true,'james3',['id'=>3]])['table'] === 'james3');
        assert($syntax::make('select',[true,'james3',[true,'id'=>3]])['id'] === 3);
        assert($syntax::make('select',[true,'james3',[true,'id'=>[1,2,3]]])['id'] === [1,2,3]);
        assert($syntax::make('create',['james2',['james','int'],[['unique','lol','james'],['primary','id']]],['createNotExists'=>true])['sql'] === 'CREATE TABLE IF NOT EXISTS `james2` (`james` INT(11) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');
        assert(count($syntax::make('select',[true,'james3',['name'=>'what'],'prepare'=>['test'=>'ok']])['prepare']) === 2);
        assert($syntax::make('select',$syntax::makeParses('select',['*','table',2,'id',3]))['sql'] === 'SELECT * FROM `table` WHERE `id` = 2 ORDER BY id LIMIT 3');
        assert($syntax::make('select',['what'=>'*','table'=>'ok','where'=>'id="2"'])['sql'] === 'SELECT * FROM `ok` WHERE id="2"');
        assert($syntax::make('select',['*','james',null,null,0])['sql'] === 'SELECT * FROM `james` LIMIT 0');
        assert(count($syntax::make('select',['*','james',[],null,0])) === 3);
        assert($syntax::make('select',['*','james',['active'=>1,[12312312,'`between`',['from','to']]]])['sql'] === 'SELECT * FROM `james` WHERE `active` = 1 AND 12312312 BETWEEN `from` AND `to`');
        assert(strlen($syntax::make('select',['*','james',['active'=>1,'date'=>Base\Datetime::now()]])['sql']) === 64);

        // makeParses
        assert($syntax::makeParses('select',['*','table',2,'id',3]) === ['what'=>'*','table'=>'table','where'=>2,'order'=>'id','limit'=>3]);

        // makeParse
        assert($syntax::makeParse('select','what',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === '*');
        assert($syntax::makeParse('select','where',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === ['active'=>1,'james'=>2]);
        assert($syntax::makeParse('select','wherez',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === null);

        // makeSelectFrom
        $insert = ['table',['ok'=>2,'id'=>4]];
        $update = ['table',['james'=>'ok'],3,['name'=>'asc'],2];
        $delete = ['table',4,['name'=>'asc'],2];
        assert($syntax::makeSelectFrom('update',$update)['sql'] === 'SELECT * FROM `table` WHERE `id` = 3 ORDER BY `name` ASC LIMIT 2');
        assert($syntax::makeSelectFrom('delete',$delete)['sql'] === 'SELECT * FROM `table` WHERE `id` = 4 ORDER BY `name` ASC LIMIT 2');
        assert($syntax::makeSelectFrom('insert',$insert)['sql'] === 'SELECT * FROM `table` WHERE `ok` = 2 AND `id` = 4 LIMIT 1');
        assert($syntax::makeSelectFrom('insert',$insert,$syntax::option())['sql'] === 'SELECT * FROM `table` WHERE `ok` = 2 AND `id` = 4 ORDER BY `id` DESC LIMIT 1');

        // makeSelect
        assert(strlen($syntax::makeSelect(['*','user',['active'=>'name'],['order'=>'Desc','active'],[4,4]])['sql']) >= 92);
        assert(count($syntax::makeSelect(['*','user',[],['order'=>'Desc','active'],[4,4]])) === 3);

        // makeShow
        assert($syntax::makeShow(['TABLES'])['sql'] === 'SHOW TABLES');

        // makeInsert
        assert(strlen($syntax::makeInsert(['user',['active'=>1,'james'=>null,'OK.james'=>'LOLÉ']])['sql']) >= 77);
        assert($syntax::makeInsert(['user',[]])['sql'] === 'INSERT INTO `user` () VALUES ()');

        // makeUpdate
        assert($syntax::makeUpdate(['james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3])['sql'] === 'UPDATE `james` SET `james` = 2, lala.`ok` = NULL WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');

        // makeDelete
        assert($syntax::makeDelete(['james',['active'=>1,'james'=>2],['id'],3])['sql'] === 'DELETE FROM `james` WHERE `active` = 1 AND `james` = 2 ORDER BY `id` ASC LIMIT 3');

        // makeCreate
        assert($syntax::makeCreate(['james2',[['james','int'],['ok','varchar']],[['unique','lol','james'],['primary','id']]])['sql'] === 'CREATE TABLE `james2` (`james` INT(11) NULL DEFAULT NULL, `ok` VARCHAR(255) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // makeAlter
        assert($syntax::makeAlter(['james',null,null,null,null,null])['sql'] === 'ALTER TABLE `james`');
        assert($syntax::makeAlter(['james'])['sql'] === 'ALTER TABLE `james`');
        assert($syntax::makeAlter(['james'])['table'] === 'james');

        // makeTruncate
        assert($syntax::makeTruncate(['james'])['sql'] === 'TRUNCATE TABLE `james`');

        // makeDrop
        assert($syntax::makeDrop(['okkk'])['sql'] === 'DROP TABLE `okkk`');

        // select
        assert(strlen($syntax::select('*','user',['active'=>'name'],['order'=>'Desc','active'],[4,4])['sql']) >= 92);
        assert(strlen($syntax::select([true,'james'=>['distinct','james']],'james_[lang]',[true,'or','(',2,[2,3,4],'james_[lang]'=>4,')',['james','findInSet',[5,6]]],true,true)['sql']) > 220);

        // show
        assert($syntax::show('TABLES')['sql'] === 'SHOW TABLES');

        // insert
        assert(strlen($syntax::insert('user',['active'=>1,'james'=>null,'OK.james'=>'LOLÉ'])['sql']) >= 77);

        // update
        assert($syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3)['sql'] === 'UPDATE `james` SET `james` = 2, lala.`ok` = NULL WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');
        assert($syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3)['select']['sql'] === 'SELECT * FROM `james` WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');
        assert(count($syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>'ok','id'=>5],['od'=>'desc'],3)['select']) === 6);
        assert($syntax::select('*','james',[2])['sql'] === 'SELECT * FROM `james` WHERE `id` = 2');
        assert($syntax::update('james',['james'=>2],[2])['sql'] === 'UPDATE `james` SET `james` = 2 WHERE `id` = 2');

        // delete
        assert($syntax::delete('james',['active'=>1,'james'=>2],['id'],3)['sql'] === 'DELETE FROM `james` WHERE `active` = 1 AND `james` = 2 ORDER BY `id` ASC LIMIT 3');

        // create
        assert($syntax::create('james2',[['james','int'],['ok','varchar']],[['unique','lol','james'],['primary','id']])['sql'] === 'CREATE TABLE `james2` (`james` INT(11) NULL DEFAULT NULL, `ok` VARCHAR(255) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // alter
        assert($syntax::alter('james',['james','int'],['unique','lao',['james','id']])['sql'] === 'ALTER TABLE `james` ADD COLUMN `james` INT(11) NULL DEFAULT NULL, ADD UNIQUE KEY `lao` (`james`, `id`)');
        assert($syntax::alter('james',null,['unique','lao',['james','id']],[['james','int'],['bla','varchar','rename'=>'LOL']])['sql'] === 'ALTER TABLE `james` ADD UNIQUE KEY `lao` (`james`, `id`), CHANGE `james` `james` INT(11) NULL DEFAULT NULL, CHANGE `bla` `LOL` VARCHAR(255) NULL DEFAULT NULL');
        assert($syntax::alter('james',null,null,null,['test','ok'],'JAMES SQL')['sql'] === 'ALTER TABLE `james` DROP COLUMN `test`, DROP COLUMN `ok`, JAMES SQL');
        assert($syntax::alter('james',null,null,null,null,null)['sql'] === 'ALTER TABLE `james`');

        // truncate
        assert($syntax::truncate('james')['sql'] === 'TRUNCATE TABLE `james`');

        // drop
        assert($syntax::drop('okkk')['sql'] === 'DROP TABLE `okkk`');

        // count
        assert($syntax::selectCount('user')['sql'] === 'SELECT COUNT(`id`) FROM `user`');

        // makeSelectCount
        assert($syntax::makeSelectCount(['my',2])['sql'] === 'SELECT COUNT(`id`) FROM `my` WHERE `id` = 2');

        // makeSelectAll
        assert($syntax::makeSelectAll(['james',2])['sql'] === 'SELECT * FROM `james` WHERE `id` = 2');
        assert($syntax::makeSelectAll(['james',['test'=>null]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NULL');
        assert($syntax::makeSelectAll(['james',['test'=>true]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");
        assert($syntax::makeSelectAll(['james',['test'=>false]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert($syntax::makeSelectAll(['james',[['test',true]]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");
        assert($syntax::makeSelectAll(['james',[['test','empty']]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert($syntax::makeSelectAll(['james',[['test',null]]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NULL');
        assert($syntax::makeSelectAll(['james',[['test','notNull']]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NOT NULL');
        assert($syntax::makeSelectAll(['james',[['test',false]]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert($syntax::makeSelectAll(['james',[['test','notEmpty']]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");

        // makeSelectFunction
        assert($syntax::makeSelectFunction('col','sum',['james',2])['sql'] === 'SELECT SUM(`col`) FROM `james` WHERE `id` = 2');

        // makeSelectDistinct
        assert($syntax::makeSelectDistinct('col',['james',2])['sql'] === 'SELECT DISTINCT `col` FROM `james` WHERE `id` = 2');

        // makeSelectCountDistinct
        assert($syntax::makeSelectCountDistinct('col',['james',1])['sql'] === 'SELECT COUNT(DISTINCT `col`) FROM `james` WHERE `id` = 1');

        // makeSelectColumn
        assert($syntax::makeSelectColumn('col',['james',2])['sql'] === 'SELECT `col` FROM `james` WHERE `id` = 2');
        assert($syntax::makeSelectColumn(['what','sum()'],['james'])['sql'] === 'SELECT SUM(`what`) FROM `james`');

        // makeselectKeyPair
        assert($syntax::makeselectKeyPair('col','col2',['james',2])['sql'] === 'SELECT `col`, `col2` FROM `james` WHERE `id` = 2');

        // makeselectPrimary
        assert($syntax::makeselectPrimary(['table',2],['primary'=>'idsz'])['sql'] === 'SELECT idsz FROM `table` WHERE `idsz` = 2');

        // makeselectPrimaryPair
        assert($syntax::makeselectPrimaryPair('col',['table',2])['sql'] === 'SELECT `id`, `col` FROM `table` WHERE `id` = 2');

        // makeSelectSegment
        assert($syntax::makeSelectSegment('[col] + [name_%lang%] [col]  v [col] [id]',['james',2])['sql'] === 'SELECT `id`, `col`, `name_en` FROM `james` WHERE `id` = 2');
        assert($syntax::makeSelectSegment('[col] + [name_%lang%] [id]',['james',2])['sql'] === 'SELECT `id`, `col`, `name_en` FROM `james` WHERE `id` = 2');

        // makeShowDatabase
        assert($syntax::makeShowDatabase()['sql'] === 'SHOW DATABASES');
        assert($syntax::makeShowDatabase('quid')['sql'] === "SHOW DATABASES LIKE 'quid'");

        // makeShowVariable
        assert($syntax::makeShowVariable()['sql'] === 'SHOW VARIABLES');
        assert($syntax::makeShowVariable('automatic')['sql'] === "SHOW VARIABLES WHERE Variable_name LIKE 'automatic'");

        // makeShowTable
        assert($syntax::makeShowTable()['sql'] === 'SHOW TABLES');
        assert($syntax::makeShowTable('basePdo')['sql'] === "SHOW TABLES LIKE 'basePdo'");

        // makeShowTableStatus
        assert($syntax::makeShowTableStatus()['sql'] === 'SHOW TABLE STATUS');
        assert($syntax::makeShowTableStatus('test_[lang]')['sql'] === "SHOW TABLE STATUS LIKE 'test_en'");

        // makeShowTableColumn
        assert($syntax::makeShowTableColumn('myTable','lol')['sql'] === "SHOW FULL COLUMNS FROM `myTable` WHERE FIELD = 'lol'");
        assert($syntax::makeShowTableColumn('myTable')['sql'] === 'SHOW FULL COLUMNS FROM `myTable`');
        assert($syntax::makeShowTableColumn('myTable')['table'] === 'myTable');

        // makeAlterAutoIncrement
        assert($syntax::makeAlterAutoIncrement('table',3)['sql'] === 'ALTER TABLE `table` AUTO_INCREMENT = 3');
        assert($syntax::makeAlterAutoIncrement('table',3)['table'] === 'table');

        // parseReturn
        assert($syntax::parseReturn('SELECT * from james')['type'] === 'select');
        assert($syntax::parseReturn(['UPDATE james',['ok'=>'lol']])['prepare'] === ['ok'=>'lol']);
        assert($syntax::parseReturn(['sql'=>'UPDATE james','prepare'=>['ok'=>'lol']])['sql'] === 'UPDATE james');

        // type
        assert($syntax::type('ALTER TABLE `james` DROP COLUMN `test`') === 'alter');
        assert($syntax::type(' SELECT TABLE `james` DROP COLUMN `test`') === 'select');

        // emulate
        $sql = 'SELECT * FROM `user` WHERE `active` = :APCBIE18 AND `test` = 2';
        $prepare = ['APCBIE18'=>'name'];
        assert($syntax::emulate($sql,$prepare) === "SELECT * FROM `user` WHERE `active` = 'name' AND `test` = 2");
        $sql = 'SELECT * FROM `user` WHERE `active` = :APCBIE18 AND `test` = 2';
        $prepare = ['APCBIE18'=>'na\me'];
        assert($syntax::emulate($sql,$prepare,null,true) === 'SELECT * FROM `user` WHERE `active` = \'na\\me\' AND `test` = 2');
        assert($syntax::emulate($sql,$prepare,null,false) === 'SELECT * FROM `user` WHERE `active` = \'na\\\\me\' AND `test` = 2');

        // debug
        assert($syntax::debug($syntax::select('*','james',['name'=>'ok']))['emulate'] === "SELECT * FROM `james` WHERE `name` = 'ok'");

        // getOverloadKeyPrepend
        assert(Orm\Syntax::getOverloadKeyPrepend() === null);
        assert($syntax::getOverloadKeyPrepend() === 'Syntax');

        // shortcut
        assert(!empty($syntax::allShortcuts()));
        assert($syntax::shortcuts(['test'=>'name_[lang]']) === ['test'=>'name_en']);
        assert($syntax::getShortcut('lang') === 'en');
        $syntax::setShortcut('james','ok');
        assert($syntax::getShortcut('james') === 'ok');
        $syntax::unsetShortcut('james');
        assert($syntax::shortcut('name_[lang]') === 'name_en');
        assert($syntax::shortcut(['name_[lang]']) === ['name_en']);

        // option
        assert(count($syntax::option(['primary'=>'iz'])) === 13);
        assert($syntax::isOption('primary') === true);
        assert($syntax::getOption('primary') === 'id');
        $syntax::setOption('test',true);
        $syntax::setOption('test2',true);
        $syntax::unsetOption('test');
        $syntax::unsetOption('test2');
        assert($syntax::option(['test'=>2])['test'] === 2);
        assert(empty($syntax::option()['test']));

        // cleanup

        return true;
    }
}
?>