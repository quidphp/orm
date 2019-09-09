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

// syntax
// class for testing Quid\Orm\Syntax
class Syntax extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // isQuery
        assert(Orm\Syntax::isQuery('select'));
        assert(!Orm\Syntax::isQuery('SELECTz'));

        // isQuote
        assert(Orm\Syntax::isQuote("'test'"));
        assert(!Orm\Syntax::isQuote("'test"));

        // hasTickOrSpace
        assert(Orm\Syntax::hasTickOrSpace('`test bla'));
        assert(Orm\Syntax::hasTickOrSpace('test bla'));
        assert(!Orm\Syntax::hasTickOrSpace('test'));

        // isTick
        assert(!Orm\Syntax::isTick('test'));
        assert(Orm\Syntax::isTick('`test`'));
        assert(Orm\Syntax::isTick('test.`test`'));

        // isParenthesis
        assert(Orm\Syntax::isParenthesis('('));
        assert(Orm\Syntax::isParenthesis('(',true));
        assert(!Orm\Syntax::isParenthesis(')',true));
        assert(Orm\Syntax::isParenthesis(')',false));

        // isKey
        assert(Orm\Syntax::isKey('unique'));
        assert(!Orm\Syntax::isKey('uniquez'));

        // isColType
        assert(!Orm\Syntax::isColType('unique'));
        assert(Orm\Syntax::isColType('tinyint'));
        assert(Orm\Syntax::isColType('mediumint'));

        // isWhereSymbol
        assert(Orm\Syntax::isWhereSymbol('!'));

        // isWhereSeparator
        assert(Orm\Syntax::isWhereSeparator('AND'));
        assert(!Orm\Syntax::isWhereSeparator('('));

        // isWhereTwo
        assert(Orm\Syntax::isWhereTwo(true));
        assert(Orm\Syntax::isWhereTwo('null'));
        assert(!Orm\Syntax::isWhereTwo('like'));
        assert(Orm\Syntax::isWhereTwo(234));

        // isOrderDirection
        assert(Orm\Syntax::isOrderDirection('asc'));
        assert(!Orm\Syntax::isOrderDirection('ascz'));

        // isReturnSelect
        $select = Orm\Syntax::select('*','table',3);
        $update = Orm\Syntax::update('table',['name'=>'bla'],3);
        assert(!Orm\Syntax::isReturnSelect($select));
        assert(Orm\Syntax::isReturnSelect($update));

        // isReturnRollback
        $select = Orm\Syntax::select('*','table',3);
        $update = Orm\Syntax::update('table',['name'=>'bla'],3);
        assert(!Orm\Syntax::isReturnRollback($select));
        assert(Orm\Syntax::isReturnRollback($update));

        // isReturnTableId
        assert(Orm\Syntax::isReturnTableId($select));
        assert(Orm\Syntax::isReturnTableId($update));

        // hasDot
        assert(Orm\Syntax::hasDot('test.`test`'));
        assert(!Orm\Syntax::hasDot('test'));

        // hasQueryClause
        assert(Orm\Syntax::hasQueryClause('select','table'));
        assert(Orm\Syntax::hasQueryClause('select','what'));
        assert(!Orm\Syntax::hasQueryClause('select','james'));

        // getQueryTypes
        assert(count(Orm\Syntax::getQueryTypes()) === 9);

        // getQueryRequired
        assert(Orm\Syntax::getQueryRequired('select') === ['what','table']);
        assert(Orm\Syntax::getQueryRequired('update') === ['table','updateSet','where']);
        assert(Orm\Syntax::getQueryRequired('updatez') === null);

        // getKeyWord
        assert(Orm\Syntax::getKeyWord('unique') === 'UNIQUE KEY');
        assert(Orm\Syntax::getKeyWord('uniquez') === null);

        // getColTypeAttr
        assert(count(Orm\Syntax::getColTypeAttr('varchar')) === 2);
        assert(Orm\Syntax::getColTypeAttr('varcharz') === null);

        // functionFormat
        assert(Orm\Syntax::functionFormat('lower') === 'LOWER');

        // getWhatFunction
        assert(count(Orm\Syntax::getWhatFunction('distinct')) === 2);
        assert(Orm\Syntax::getWhatFunction('SUM')['parenthesis'] === true);

        // getWhereSymbol
        assert(Orm\Syntax::getWhereSymbol('!') === '!=');
        assert(Orm\Syntax::getWhereSymbol('!=') === '!=');

        // getWhereMethod
        assert(Orm\Syntax::getWhereMethod('findInSet') === [Orm\Syntax::class,'whereFind']);
        assert(Orm\Syntax::getWhereMethod('findz') === null);

        // getWhereSeparator
        assert(Orm\Syntax::getWhereSeparator() === 'AND');
        assert(Orm\Syntax::getWhereSeparator('or') === 'OR');
        assert(Orm\Syntax::getWhereSeparator('AnD') === 'AND');
        assert(Orm\Syntax::getWhereSeparator('&&') === '&&');
        assert(Orm\Syntax::getWhereSeparator('xor') === 'XOR');

        // getOrderDirection
        assert(Orm\Syntax::getOrderDirection('desc') === 'DESC');
        assert(Orm\Syntax::getOrderDirection() === 'ASC');
        assert(Orm\Syntax::getOrderDirection('ASC') === 'ASC');
        assert(Orm\Syntax::getOrderDirection(true) === 'ASC');

        // invertOrderDirection
        assert(Orm\Syntax::invertOrderDirection('desc') === 'ASC');
        assert(Orm\Syntax::invertOrderDirection() === 'DESC');
        assert(Orm\Syntax::invertOrderDirection(true) === 'DESC');

        // getOrderMethod
        assert(Orm\Syntax::getOrderMethod('find') === [Orm\Syntax::class,'orderFind']);
        assert(Orm\Syntax::getOrderMethod('findz') === null);

        // getSetMethod
        assert(Orm\Syntax::getSetMethod('replace') === [Orm\Syntax::class,'setReplace']);

        // getQueryWord
        assert(Orm\Syntax::getQueryWord('select') === 'SELECT');
        assert(Orm\Syntax::getQueryWord('select','table') === 'FROM');
        assert(Orm\Syntax::getQueryWord('select','where') === 'WHERE');
        assert(Orm\Syntax::getQueryWord('drop','table',['dropExists'=>true]) === 'TABLE IF EXISTS');
        assert(Orm\Syntax::getQueryWord('drop','table') === 'TABLE');
        assert(Orm\Syntax::getQueryWord('create','table',['createNotExists'=>true]) === 'TABLE IF NOT EXISTS');

        // getReturn
        assert(Orm\Syntax::getReturn() === ['sql'=>'']);
        assert(Orm\Syntax::getReturn(['bla'=>'ok']) === ['sql'=>'']);
        assert(Orm\Syntax::getReturn(['sql'=>'ok']) === ['sql'=>'ok']);

        // returnMerge
        assert(count(Orm\Syntax::returnMerge(['sql'=>'test','prepare'=>['test'=>2],'james'=>true],['sql'=>'test2','prepare'=>['test'=>4,'test2'=>3]])['prepare']) === 2);
        assert(Orm\Syntax::returnMerge(['sql'=>'test','prepare'=>['test'=>2],'james'=>true],['sql'=>'test2','prepare'=>['test'=>4,'test2'=>3]])['sql'] === 'testtest2');

        // tick
        assert(Orm\Syntax::tick('test') === '`test`');
        assert(Orm\Syntax::tick('test.test2') === 'test.`test2`');
        assert(Orm\Syntax::tick('`test`.`test`') === '`test`.`test`');
        assert(Orm\Syntax::tick('test_[lang]') === '`test_en`');
        assert(Orm\Syntax::tick('test',['binary'=>true]) === 'BINARY `test`');
        assert(Orm\Syntax::tick('test',['function'=>'LOWER']) === 'LOWER(`test`)');
        assert(Orm\Syntax::tick('test',['function'=>'LOWER','binary'=>true]) === 'BINARY LOWER(`test`)');
        assert(Orm\Syntax::tick('(SELECT * FROM table)') === '(SELECT * FROM table)');
        assert(Orm\Syntax::tick('@rownum := @rownum + 1') === '@rownum := @rownum + 1');

        // untick
        assert(Orm\Syntax::untick('test.`test`') === 'test.test');
        assert(Orm\Syntax::untick('`test`.`test`') === 'test.test');
        assert(Orm\Syntax::untick('`test`') === 'test');

        // quote
        assert(Orm\Syntax::quote('test') === "'test'");
        assert(Orm\Syntax::quote(2) === 2);
        assert(Orm\Syntax::quote('test',[Base\Str::class,'upper']) === 'TEST');

        // quoteSet
        assert(Orm\Syntax::quoteSet(['test',2,3]) === "'test',2,3");
        assert(Orm\Syntax::quoteSet(['test','bla'],[Base\Str::class,'upper']) === 'TEST,BLA');

        // unquote
        assert(Orm\Syntax::unquote("'test'") === 'test');

        // parenthesis
        assert(Orm\Syntax::parenthesis('test') === '(test)');
        assert(Orm\Syntax::parenthesis('') === '');

        // comma
        assert(Orm\Syntax::comma('test') === ', ');
        assert(Orm\Syntax::comma('test',false) === ',');
        assert(Orm\Syntax::comma('',false) === '');

        // whereSeparator
        assert(Orm\Syntax::whereSeparator('z') === ' AND ');
        assert(Orm\Syntax::whereSeparator('z','or') === ' OR ');
        assert(Orm\Syntax::whereSeparator('','or') === '');
        assert(Orm\Syntax::whereSeparator(null,'or',false) === 'OR');

        // boolNull
        assert(Orm\Syntax::boolNull(true) === 1);
        assert(Orm\Syntax::boolNull(null) === 'NULL');

        // prepare
        assert(count(Orm\Syntax::prepare()) === 2);

        // prepareValue
        assert(Orm\Syntax::prepareValue(true) === 1);
        assert(Orm\Syntax::prepareValue(false) === 0);
        assert(Orm\Syntax::prepareValue(null) === 'NULL');
        assert(Orm\Syntax::prepareValue([1,2,3]) === '1,2,3');
        assert(Orm\Syntax::prepareValue(['test'=>2,'james'=>3]) === '{"test":2,"james":3}');
        assert(strlen(Orm\Syntax::prepareValue(new \Datetime('now'))) > 100);

        // value
        assert(strlen(Orm\Syntax::value('test',[],Orm\Syntax::option())['sql']) >= 8);
        assert(count(Orm\Syntax::value('test',['sql'=>':test_0','prepare'=>['test_0'=>2]],Orm\Syntax::option())['prepare']) === 2);
        assert(Orm\Syntax::value('test',[],['quoteCallable'=>[Base\Str::class,'upper']])['sql'] === 'TEST');
        assert(Orm\Syntax::value('test.bla',[],['quote'=>false])['sql'] === 'test.bla');
        assert(Orm\Syntax::value(null,[])['sql'] === 'NULL');
        assert(Orm\Syntax::value(true,[])['sql'] === '1');
        assert(Orm\Syntax::value(false,[])['sql'] === '0');
        assert(Orm\Syntax::value(1.2,[])['sql'] === '1.2');
        assert(Orm\Syntax::value(1,[])['sql'] === '1');
        assert(Orm\Syntax::value('test.james',[],['tick'=>true])['sql'] === 'test.`james`');
        assert(count(Orm\Syntax::value('james',['sql'=>'','prepare'=>[1,2,3]],['prepare'=>true])['prepare']) === 4);
        assert(Orm\Syntax::value('james@landre_ok',null,['quoteChar'=>['@','_']])['sql'] === "'james\@landre\_ok'");
        assert(current(Orm\Syntax::value('james@landre_ok',null,Orm\Syntax::option(['quoteChar'=>['@','_']]))['prepare']) === "james\@landre\_ok");

        // valueSet
        assert(count(Orm\Syntax::valueSet([1,2,'string',3],[],['prepare'=>true])['prepare']) === 1);
        assert(strlen(Orm\Syntax::valueSet([1,2,'string',3],[],['prepare'=>true])['sql']) >= 17);

        // makeSet
        assert(Orm\Syntax::makeSet([1,2,3,'TEST']) === '1,2,3,TEST');

        // makeDefault

        // addDefault
        assert(Orm\Syntax::addDefault(null) === [true]);
        assert(Orm\Syntax::addDefault(['test'=>true,true]) === ['test'=>true,true]);
        assert(Orm\Syntax::addDefault(['test'=>true] === ['test'=>true,true]));

        // removeDefault
        assert(Orm\Syntax::removeDefault(null) === []);
        assert(Orm\Syntax::removeDefault(['test'=>true,true]) === ['test'=>true]);

        // sql

        // what
        assert(Orm\Syntax::what('*')['sql'] === '*');
        assert(Orm\Syntax::what(['james.test','ok','what','james'=>'ok'])['sql'] === 'james.`test`, `ok`, `what`, `ok` AS `james`');
        assert(Orm\Syntax::what(['ok.lol','james.test'=>['test','distinct']])['sql'] === 'ok.`lol`, DISTINCT `test` AS james.`test`');
        assert(Orm\Syntax::what('SUM(`test`), SUM(`bla`) AS james.`test`')['sql'] === 'SUM(`test`), SUM(`bla`) AS james.`test`');
        assert(Orm\Syntax::what('id')['sql'] === 'id');
        assert(Orm\Syntax::what(['id','*','test.james'])['sql'] === '`id`, *, test.`james`');
        assert(Orm\Syntax::what(['id','name_[lang]','key_[lang]'])['sql'] === '`id`, `name_en`, `key_en`');
        assert(Orm\Syntax::what(true,Orm\Syntax::option())['sql'] === '*');
        assert(Orm\Syntax::what([true,'james.sql',true],Orm\Syntax::option())['sql'] === '*, james.`sql`, *');
        assert(Orm\Syntax::what([['test','distinct','ok'],['james','distinct','what']])['sql'] === 'DISTINCT `test` AS `ok`, DISTINCT `james` AS `what`');
        assert(Orm\Syntax::what([['test','ok'],['test2','ok2']])['sql'] === '`test` AS `ok`, `test2` AS `ok2`');
        assert(Orm\Syntax::what(['distinct()'=>'test'])['sql'] === 'DISTINCT `test`');
        assert(Orm\Syntax::what([['what','sum()']])['sql'] === 'SUM(`what`)');
        assert(Orm\Syntax::what([['what','sum()']])['cast'] === true);
        assert(empty(Orm\Syntax::what([['what','sum']])['cast']));
        assert(Orm\Syntax::what([['(SELECT * FROM TABLE)','test']])['sql'] === '(SELECT * FROM TABLE) AS `test`');

        // whatPrepare
        assert(Orm\Syntax::whatPrepare(['test','ok','*']) === [['test'],['ok'],['*']]);
        assert(Orm\Syntax::whatPrepare(['test'=>'james']) === [['james','test']]);
        assert(Orm\Syntax::whatPrepare(['test'=>['ok','james']]) === [['ok','james','test']]);
        assert(Orm\Syntax::whatPrepare([['ok','james']]) === [['ok','james']]);

        // whatOne
        assert(Orm\Syntax::whatOne('*')['sql'] === '*');
        assert(Orm\Syntax::whatOne('test')['sql'] === '`test`');

        // whatTwo
        assert(Orm\Syntax::whatTwo('test','james')['sql'] === '`test` AS `james`');
        assert(Orm\Syntax::whatTwo('test','sum()')['sql'] === 'SUM(`test`)');

        // whatThree
        assert(Orm\Syntax::whatThree('test','sum','test')['sql'] === 'SUM(`test`)');
        assert(Orm\Syntax::whatThree('test','sum','lol')['sql'] === 'SUM(`test`) AS `lol`');
        assert(Orm\Syntax::whatThree('test','distinct','lol')['sql'] === 'DISTINCT `test` AS `lol`');
        assert(Orm\Syntax::whatThree('test','sum()','lol')['sql'] === 'SUM(`test`) AS `lol`');
        assert(Orm\Syntax::whatThree('test','sum()','lol')['cast'] === true);

        // whatFromWhere
        assert(Orm\Syntax::whatFromWhere(['test'=>2,['id','in',[2,3,4]],'id'=>4],'t') === ['t.test','t.id']);
        assert(Orm\Syntax::whatFromWhere('test') === ['*']);

        // table
        assert(Orm\Syntax::table('test')['sql'] === '`test`');
        assert(Orm\Syntax::table('test')['table'] === 'test');
        assert(Orm\Syntax::table('`test`')['table'] === 'test');

        // join
        assert(strlen(Orm\Syntax::join(['table'=>'james','on'=>['active'=>1,'james.tst'=>'deux']],Orm\Syntax::option())['sql']) >= 51);
        assert(Orm\Syntax::join(['test','on'=>['active'=>4]],['table'=>'james'])['sql'] === '`test` ON(`active` = 4)');
        assert(Orm\Syntax::join(['on'=>['active'=>3],'table'=>'LOL'],['table'=>'james'])['sql'] === '`LOL` ON(`active` = 3)');
        assert(Orm\Syntax::join(['table'=>'lol','on'=>[['lol.id','`=`','session.id']]],Orm\Syntax::option())['sql'] === '`lol` ON(lol.`id` = session.`id`)');
        assert(Orm\Syntax::join(['table'=>'lol','on'=>[['lol.id','[=]','session.id']]],Orm\Syntax::option())['sql'] === '`lol` ON(lol.`id` = session.id)');
        assert(Orm\Syntax::join(['table'=>'lol','on'=>[['lol.id','=','session.id']]],Orm\Syntax::option(['prepare'=>false]))['sql'] === "`lol` ON(lol.`id` = 'session.id')");

        // innerJoin
        assert(count(Orm\Syntax::innerJoin(['james',['active'=>1,'james.tst'=>'deux']])) === 1);

        // outerJoin
        assert(empty(Orm\Syntax::outerJoin(['table'=>'james','on'=>['active'=>1,'james.tst'=>'deux']])['table']));

        // where
        assert(Orm\Syntax::where([[30,'`between`',['userAdd','userModify']]])['sql'] === '30 BETWEEN `userAdd` AND `userModify`');
        assert(Orm\Syntax::where([['id','`between`',[20,30]]])['sql'] === '`id` BETWEEN 20 AND 30');
        assert(Orm\Syntax::where([['id','`between`',['james',3]]])['sql'] === '`id` BETWEEN `james` AND 3');
        assert(Orm\Syntax::where([['name','findInSetOrNull',3]])['sql'] === '(FIND_IN_SET(3, `name`) OR `name` IS NULL)');
        assert(Orm\Syntax::where([['id','in',[]],['james','=',2]])['sql'] === '`james` = 2');
        assert(Orm\Syntax::where([['james','=',2],['id','in',[]]])['sql'] === '`james` = 2');
        assert(Orm\Syntax::where([true,'id'=>2],Orm\Syntax::option())['id'] === 2);
        assert(Orm\Syntax::where(['active'=>1])['sql'] === '`active` = 1');
        assert(strlen(Orm\Syntax::where(['active'=>1,'OR','(','james'=>'deux','(','ok'=>'lol'],Orm\Syntax::option())['sql']) >= 58);
        assert(Orm\Syntax::where("id=test AND james='2'")['sql'] === "id=test AND james='2'");
        assert(Orm\Syntax::where([['active','[=]','james.bla']])['sql'] === '`active` = james.bla');
        assert(Orm\Syntax::where(['active'=>[1,'james',3],['active','>','james2']])['sql'] === "`active` IN(1, 'james', 3) AND `active` > 'james2'");
        assert(Orm\Syntax::where([true,'id'=>3],Orm\Syntax::option())['sql'] === '`active` = 1 AND `id` = 3');
        assert(Orm\Syntax::where([true,3],Orm\Syntax::option())['sql'] === '`active` = 1 AND `id` = 3');
        assert(Orm\Syntax::where([true,[1,2,3],Orm\Syntax::option()],Orm\Syntax::option())['sql'] === '`active` = 1 AND `id` IN(1, 2, 3)');
        assert(Orm\Syntax::where([['active','[=]','james.bla']],Orm\Syntax::option())['sql'] === '`active` = james.bla');
        assert(Orm\Syntax::where(['active'=>null])['sql'] === '`active` IS NULL');
        assert(Orm\Syntax::where(['active'=>true])['sql'] === "(`active` != '' AND `active` IS NOT NULL)");
        assert(Orm\Syntax::where(['active'=>false])['sql'] === "(`active` = '' OR `active` IS NULL)");
        assert(Orm\Syntax::where(['active'=>[1,2,3]])['sql'] === '`active` IN(1, 2, 3)');
        assert(strlen(Orm\Syntax::where(['active'=>['test'=>'ok','lol'=>'yeah']])['sql']) >= 20);
        assert(Orm\Syntax::where([['active','=',null]])['sql'] === '`active` IS NULL');
        assert(Orm\Syntax::where([['active','=',true]])['sql'] === '`active` = 1');
        assert(Orm\Syntax::where([['active','=',false]])['sql'] === '`active` = 0');
        assert(Orm\Syntax::where(2,Orm\Syntax::option())['whereOnlyId'] === true);
        assert(Orm\Syntax::where([1,2,3],Orm\Syntax::option())['whereOnlyId'] === true);
        assert(Orm\Syntax::where(['id'=>2],Orm\Syntax::option())['id'] === 2);
        assert(Orm\Syntax::where(['id'=>[1,2,3]],Orm\Syntax::option())['whereOnlyId'] === true);
        assert(Orm\Syntax::where(['id'=>2,'james'=>'ok'],Orm\Syntax::option())['whereOnlyId'] === false);
        assert(Orm\Syntax::where(['id'=>[1,2,3],'james'=>'ok'],Orm\Syntax::option())['whereOnlyId'] === false);
        assert(Orm\Syntax::where([['id','=',2],['test','=','james']],Orm\Syntax::option())['whereOnlyId'] === false);
        assert(Orm\Syntax::where([['id','in',2]])['sql'] === '`id` IN(2)');
        assert(Orm\Syntax::where([['id','like',2]])['sql'] === "`id` LIKE concat('%', 2, '%')");
        assert(Orm\Syntax::where([['id','b|like',2]])['sql'] === "BINARY `id` LIKE concat('%', 2, '%')");
        assert(Orm\Syntax::where([['id','b,l|like',2]])['sql'] === "BINARY LOWER(`id`) LIKE concat('%', 2, '%')");
        assert(Orm\Syntax::where([['id','findInSet',[1,2,3]]])['sql'] === '(FIND_IN_SET(1, `id`) AND FIND_IN_SET(2, `id`) AND FIND_IN_SET(3, `id`))');
        assert(Orm\Syntax::where([['id','or|findInSet',[1,2,3]]])['sql'] === '(FIND_IN_SET(1, `id`) OR FIND_IN_SET(2, `id`) OR FIND_IN_SET(3, `id`))');
        assert(Orm\Syntax::where(['(',['ok','=',2]])['sql'] === '(`ok` = 2)');
        assert(Orm\Syntax::where([['james',null,'what']])['sql'] === '`james` IS NULL');
        assert(Orm\Syntax::where([['james','empty','what']])['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert(Orm\Syntax::where(['id'=>3,'&&','james'=>2,'XOR','lol'=>3])['sql'] === '`id` = 3 && `james` = 2 XOR `lol` = 3');
        assert(Orm\Syntax::where([['id','b|=','bla'],['id','b|in',[1,2,3]],['id','b|findInSet','OK']])['sql'] === "BINARY `id` = 'bla' AND BINARY `id` IN(1, 2, 3) AND FIND_IN_SET('OK', BINARY `id`)");
        assert(Orm\Syntax::where([['id','l,b|=','james']])['sql'] === "BINARY LOWER(`id`) = LOWER('james')");
        assert(Orm\Syntax::where([['username','l|notIn',['NOBODY','ADMIN']]])['sql'] === "LOWER(`username`) NOT IN(LOWER('NOBODY'), LOWER('ADMIN'))");
        assert(Orm\Syntax::where([['id','in',[]]])['sql'] === '');
        assert(Orm\Syntax::where([['id',23]])['sql'] === '`id` = 23');

        // whereDefault
        assert(Orm\Syntax::whereDefault([true,3],Orm\Syntax::option()) === ['active'=>1,1=>['id','=',3]]);
        assert(Orm\Syntax::whereDefault(true,Orm\Syntax::option()) === ['active'=>1]);
        assert(Orm\Syntax::whereDefault(2,Orm\Syntax::option()) === [['id','=',2]]);
        assert(Orm\Syntax::whereDefault([1,2,3],Orm\Syntax::option()) === [['id','in',[1,2,3]]]);
        assert(Orm\Syntax::whereDefault([true,'james'=>2],Orm\Syntax::option()) === ['active'=>1,'james'=>2]);
        assert(Orm\Syntax::whereDefault([true,'active'=>2],Orm\Syntax::option()) === ['active'=>2]);
        assert(Orm\Syntax::whereDefault([2],Orm\Syntax::option()) === [['id','=',2]]);

        // wherePrepare
        assert(count(Orm\Syntax::wherePrepare(['active'=>1])) === 1);
        assert(count(Orm\Syntax::wherePrepare(['active'=>1,'james'=>'deux'])) === 3);
        assert(count(Orm\Syntax::wherePrepare(['active'=>1,'OR','(','james'=>'deux','(','ok'=>'lol'])) === 9);
        assert(count(Orm\Syntax::wherePrepare([')','active'=>1])) === 1);
        assert(Orm\Syntax::wherePrepare([['active','=',1]]) === [['active','=',1]]);
        assert(Orm\Syntax::wherePrepare([['active',null]]) == [['active',null]]);
        assert(Orm\Syntax::wherePrepare([true,[1,2,3]]) === []);
        assert(count(Orm\Syntax::wherePrepare(['active'=>1,'(','james'=>2,')'])) === 5);
        assert(count(Orm\Syntax::wherePrepare(['active'=>1,'OR','(','james'=>2,')','lala'=>3])) === 7);
        assert(Orm\Syntax::wherePrepare([['active','=',false]]) === [['active','=',false]]);
        assert(count(Orm\Syntax::wherePrepare(['(',['ok','=',2]])) === 3);

        // wherePrepareOne
        assert(Orm\Syntax::wherePrepareOne('active',1) === [['active','=',1]]);
        assert(Orm\Syntax::wherePrepareOne('active',[1,2,3]) === [['active','in',[1,2,3]]]);
        assert(Orm\Syntax::wherePrepareOne(0,'(') === [['(']]);
        assert(Orm\Syntax::wherePrepareOne(0,'AND') === [['AND']]);
        assert(Orm\Syntax::wherePrepareOne(0,'or') === [['or']]);
        assert(Orm\Syntax::wherePrepareOne(0,['active','=',1]) === [['active','=',1]]);

        // whereCols
        assert(Orm\Syntax::whereCols([['id','=',3],'james'=>2,['id','=',4],['ok','in',[1,3,3]]]) === ['id','james','ok']);

        // whereAppend
        assert(Orm\Syntax::where(Orm\Syntax::whereAppend(true,['james'=>3],[['james','in',[1,2,3]]]))['sql'] === '`active` = 1 AND `james` = 3 AND `james` IN(1, 2, 3)');
        assert(Orm\Syntax::where(Orm\Syntax::whereAppend(true,['james'=>[3,2,1]],[['james','in',[1,2,3]]]))['sql'] === '`active` = 1 AND `james` IN(3, 2, 1) AND `james` IN(1, 2, 3)');
        assert(Orm\Syntax::where(Orm\Syntax::whereAppend(true,1))['sql'] === '`active` = 1 AND `id` = 1');

        // wherePrimary
        assert(Orm\Syntax::wherePrimary([['id','=',3]],Orm\Syntax::option()) === ['id'=>3,'whereOnlyId'=>true]);
        assert(Orm\Syntax::wherePrimary([['id','in',[1,2,'3']]],Orm\Syntax::option()) === ['id'=>[1,2,3],'whereOnlyId'=>true]);
        assert(Orm\Syntax::wherePrimary([['id','in',[1,'test',3]]],Orm\Syntax::option()) === null);
        assert(Orm\Syntax::wherePrimary([['id','=','3'],['ok','=','bla']],Orm\Syntax::option()) === ['id'=>3,'whereOnlyId'=>false]);

        // whereOne
        assert(Orm\Syntax::whereOne('and')['sql'] === ' AND ');
        assert(Orm\Syntax::whereOne('(')['sql'] === '(');

        // whereTwo
        assert(Orm\Syntax::whereTwo('james',null)['sql'] === '`james` IS NULL');
        assert(Orm\Syntax::whereTwo('james','notNull')['sql'] === '`james` IS NOT NULL');
        assert(Orm\Syntax::whereTwo('james',true)['sql'] === "(`james` != '' AND `james` IS NOT NULL)");
        assert(Orm\Syntax::whereTwo('james','notEmpty')['sql'] === "(`james` != '' AND `james` IS NOT NULL)");
        assert(Orm\Syntax::whereTwo('james',false)['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert(Orm\Syntax::whereTwo('james','empty')['sql'] === "(`james` = '' OR `james` IS NULL)");
        assert(Orm\Syntax::whereTwo('james',23)['sql'] === '`james` = 23');

        // whereThreeMethod

        // whereThree
        assert(Orm\Syntax::whereThree('james','=',null)['sql'] === '`james` IS NULL');
        assert(Orm\Syntax::whereThree('james','[=]',Orm\Syntax::select('*','jacynthe')['sql'])['sql'] === '`james` = SELECT * FROM `jacynthe`');
        assert(Orm\Syntax::whereThree('james','in',[1,2,3])['sql'] === '`james` IN(1, 2, 3)');
        assert(Orm\Syntax::whereThree('james','notIn',[1,2,3])['sql'] === '`james` NOT IN(1, 2, 3)');
        assert(Orm\Syntax::whereThree('james','`>=`','james.test')['sql'] === '`james` >= james.`test`');
        assert(Orm\Syntax::whereThree('james','`notIn`',[1,2,'mymethod.james'])['sql'] === '`james` NOT IN(1, 2, mymethod.`james`)');
        assert(Orm\Syntax::whereThree('james','[notIn]',[1,2,'mymethod.james'])['sql'] === '`james` NOT IN(1, 2, mymethod.james)');
        assert(Orm\Syntax::whereThree('james','[b,l|notIn]',[2,'ok','test.col'])['sql'] === 'BINARY LOWER(`james`) NOT IN(2, LOWER(ok), LOWER(test.col))');
        assert(Orm\Syntax::whereThree('james','`b,l,or|notFindInSet`',[2,'ok','test.col'])['sql'] === '(!FIND_IN_SET(2, BINARY LOWER(`james`)) OR !FIND_IN_SET(LOWER(`ok`), BINARY LOWER(`james`)) OR !FIND_IN_SET(LOWER(test.`col`), BINARY LOWER(`james`)))');
        assert(Orm\Syntax::whereThree('james','`b,l|notFindInSet`',[2,'ok','test.col'])['sql'] === '(!FIND_IN_SET(2, BINARY LOWER(`james`)) AND !FIND_IN_SET(LOWER(`ok`), BINARY LOWER(`james`)) AND !FIND_IN_SET(LOWER(test.`col`), BINARY LOWER(`james`)))');

        // whereIn
        assert(Orm\Syntax::whereIn('james',[2,'james',3],'in')['sql'] === "`james` IN(2, 'james', 3)");
        assert(Orm\Syntax::whereIn('james','test','notIn')['sql'] === "`james` NOT IN('test')");
        assert(Orm\Syntax::whereIn('james',['test'=>2],'notIn')['sql'] === '');

        // whereBetween
        assert(Orm\Syntax::whereBetween('james',[10,20],'between',['tick'=>true])['sql'] === '`james` BETWEEN 10 AND 20');
        assert(Orm\Syntax::whereBetween('james',[10,20],'notBetween',['tick'=>true])['sql'] === '`james` NOT BETWEEN 10 AND 20');

        // whereFind
        assert(Orm\Syntax::whereFind('james',3,'find')['sql'] === 'FIND_IN_SET(3, `james`)');
        assert(Orm\Syntax::whereFind('james','james2','notFind')['sql'] === "!FIND_IN_SET('james2', `james`)");
        assert(Orm\Syntax::whereFind('james',[3,'james2','james3'],'find')['sql'] === "(FIND_IN_SET(3, `james`) AND FIND_IN_SET('james2', `james`) AND FIND_IN_SET('james3', `james`))");
        assert(Orm\Syntax::whereFind('james',[3,'james2','james3'],'find',['separator'=>'or'])['sql'] === "(FIND_IN_SET(3, `james`) OR FIND_IN_SET('james2', `james`) OR FIND_IN_SET('james3', `james`))");

        // whereFindOrNull
        assert(Orm\Syntax::whereFindOrNull('james',3,'find')['sql'] === '(FIND_IN_SET(3, `james`) OR `james` IS NULL)');
        assert(Orm\Syntax::whereFindOrNull('james',[3,4,'jaems2'],'find')['sql'] === "(FIND_IN_SET(3, `james`) OR `james` IS NULL) AND (FIND_IN_SET(4, `james`) OR `james` IS NULL) AND (FIND_IN_SET('jaems2', `james`) OR `james` IS NULL)");

        // whereLike
        assert(Orm\Syntax::whereLike('james.bla','okkk','like')['sql'] === "james.`bla` LIKE concat('%', 'okkk', '%')");
        assert(Orm\Syntax::whereLike('james.bla','okkk','notLike',['binary'=>true])['sql'] === "BINARY james.`bla` NOT LIKE concat('%', 'okkk', '%')");
        assert(Orm\Syntax::whereLike('james.bla','okkk','notLike%',['binary'=>true])['sql'] === "BINARY james.`bla` NOT LIKE concat('%', 'okkk')");
        assert(strlen(Orm\Syntax::whereLike('james.bla',['bla',2,3],'%like')['sql']) === 109);
        assert(strlen(Orm\Syntax::whereLike('james.bla',['bla',2,3],'%like',['separator'=>'or'])['sql']) === 107);
        assert(Orm\Syntax::whereLike('james.bla','%','like')['sql'] === "james.`bla` LIKE concat('%', '\%', '%')");
        assert(Orm\Syntax::whereLike('james.bla','_','like')['sql'] === "james.`bla` LIKE concat('%', '\_', '%')");
        assert(Orm\Syntax::whereLike('james.bla','\\','like')['sql'] === "james.`bla` LIKE concat('%', '\\\\\\\\', '%')");
        assert(current(Orm\Syntax::whereLike('james.bla','%','like',Orm\Syntax::option())['prepare']) === "\%");
        assert(current(Orm\Syntax::whereLike('james.bla','_','like',Orm\Syntax::option())['prepare']) === "\_");
        assert(current(Orm\Syntax::whereLike('james.bla','\\','like',Orm\Syntax::option())['prepare']) === '\\\\');

        // whereDate
        assert(Orm\Syntax::whereDate('james',Base\Date::mk(2017,1,2),'year')['sql'] === '(`james` >= 1483246800 AND `james` <= 1514782799)');
        assert(Orm\Syntax::whereDate('james',Base\Date::mk(2017,2,2),'month')['sql'] === '(`james` >= 1485925200 AND `james` <= 1488344399)');
        assert(Orm\Syntax::whereDate('james',Base\Date::mk(2017,1,2),'day')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483419599)');
        assert(Orm\Syntax::whereDate('james',Base\Date::mk(2017,1,2),'hour')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483336799)');
        assert(Orm\Syntax::whereDate('james',Base\Date::mk(2017,1,2),'minute')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483333259)');
        assert(Orm\Syntax::whereDate('james',['2017-01-02','ymd'],'day')['sql'] === '');
        assert(Orm\Syntax::whereDate('james',[['2017-01-02','ymd']],'day')['sql'] === '(`james` >= 1483333200 AND `james` <= 1483419599)');
        assert(Orm\Syntax::whereDate('james',[Base\Date::mk(2017,1,2),Base\Date::mk(2017,1,3)],'month')['sql'] === '((`james` >= 1483246800 AND `james` <= 1485925199) AND (`james` >= 1483246800 AND `james` <= 1485925199))');
        assert(Orm\Syntax::whereDate('james',[Base\Date::mk(2017,1,2),Base\Date::mk(2017,1,3)],'month',['separator'=>'or'])['sql'] === '((`james` >= 1483246800 AND `james` <= 1485925199) OR (`james` >= 1483246800 AND `james` <= 1485925199))');

        // group
        assert(Orm\Syntax::group('test, test2.test, test')['sql'] === 'test, test2.test, test');
        assert(Orm\Syntax::group(['test.test2','james'])['sql'] === 'test.`test2`, `james`');
        assert(Orm\Syntax::group(true)['sql'] === '');

        // order
        assert(Orm\Syntax::order(['test'=>true,'james'=>true])['sql'] === '`test` ASC, `james` ASC');
        assert(Orm\Syntax::order(['test'=>'ASC','james','rand()','ok.test'=>'desc'],Orm\Syntax::option())['sql'] === '`test` ASC, `james` ASC, rand(), ok.`test` DESC');
        assert(Orm\Syntax::order('test ASC, james DESC, rand()',Orm\Syntax::option())['sql'] === 'test ASC, james DESC, rand()');
        assert(Orm\Syntax::order([['test','asc'],['order'=>'test2','direction'=>'asc']])['sql'] === '`test` ASC, `test2` ASC');
        assert(Orm\Syntax::order([['james','findInSet','test']])['sql'] === 'FIND_IN_SET(`test`, `james`)');
        assert(Orm\Syntax::order([[5,'findInSet','james']])['sql'] === 'FIND_IN_SET(`james`, `5`)');

        // orderPrepare
        assert(Orm\Syntax::orderPrepare(['rand()']) === [['rand()']]);
        assert(Orm\Syntax::orderPrepare(['test']) === [['test']]);
        assert(Orm\Syntax::orderPrepare(['test'=>true]) === [['test',true]]);
        assert(Orm\Syntax::orderPrepare(['test'=>'james']) === [['test','james']]);
        assert(Orm\Syntax::orderPrepare([['test','ASC']]) === [['test','ASC']]);

        // orderOne
        assert(Orm\Syntax::orderOne('rand()')['sql'] === 'rand()');
        assert(Orm\Syntax::orderOne('test')['sql'] === '`test` ASC');

        // orderOneTwo
        assert(Orm\Syntax::orderTwo('test','ASC')['sql'] === '`test` ASC');
        assert(Orm\Syntax::orderTwo('test','desc')['sql'] === '`test` DESC');
        assert(Orm\Syntax::orderTwo('test','james')['sql'] === '`test` ASC');

        // orderThree
        assert(Orm\Syntax::orderThree('james','find','lala.col')['sql'] === 'FIND_IN_SET(lala.`col`, `james`)');

        // orderFind
        assert(Orm\Syntax::orderFind('james','lala.col','find')['sql'] === 'FIND_IN_SET(lala.`col`, `james`)');

        // limit
        assert(Orm\Syntax::limit('1,2')['sql'] === '1,2');
        assert(Orm\Syntax::limit([1,2])['sql'] === '1 OFFSET 2');
        assert(Orm\Syntax::limit([1])['sql'] === '1');
        assert(Orm\Syntax::limit(1)['sql'] === '1');
        assert(Orm\Syntax::limit([true,2],Orm\Syntax::option())['sql'] === PHP_INT_MAX.' OFFSET 2');
        assert(Orm\Syntax::limit([true,true],Orm\Syntax::option())['sql'] === PHP_INT_MAX.' OFFSET '.PHP_INT_MAX);
        assert(Orm\Syntax::limit(0)['sql'] === '0');
        assert(Orm\Syntax::limit('0')['sql'] === '0');
        assert(Orm\Syntax::limit([0])['sql'] === '0');
        assert(Orm\Syntax::limit([1=>2])['sql'] === '2');
        assert(Orm\Syntax::limit([3=>8])['sql'] === '8 OFFSET 16');
        assert(Orm\Syntax::limit(['offset'=>3,'limit'=>10])['sql'] === '10 OFFSET 3');
        assert(Orm\Syntax::limit(['limit'=>10,'offset'=>3])['sql'] === '10 OFFSET 3');
        assert(Orm\Syntax::limit(['page'=>3,'limit'=>25])['sql'] === '25 OFFSET 50');

        // limitPrepare
        assert(Orm\Syntax::limitPrepare(['2,3']) === [3,2]);
        assert(Orm\Syntax::limitPrepare([4=>3]) === [3,9]);
        assert(Orm\Syntax::limitPrepare([2=>2]) === [2,2]);

        // limitPrepareOne
        assert(Orm\Syntax::limitPrepareOne(3,4) === [4,8]);

        // limitPrepareTwo
        assert(Orm\Syntax::limitPrepareTwo(['page'=>3,'limit'=>25]) === [25,50]);

        // insertSet
        assert(Orm\Syntax::insertSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '(`active`, `james`, `oK`, lol.`james`) VALUES (2, 3, NULL, 1)');
        assert(Orm\Syntax::insertSet(['activezzz','testzz'])['sql'] === '');
        assert(Orm\Syntax::insertSet([['wwactivezzz','wwwtestzz']])['sql'] === "(`wwactivezzz`) VALUES ('wwwtestzz')");
        assert(Orm\Syntax::insertSet([])['sql'] === '() VALUES ()');
        assert(Orm\Syntax::insertSet([['name','lower','TEST'],['id',4]])['sql'] === "(`name`, `id`) VALUES (LOWER('TEST'), 4)");

        // insertSetFields

        // setPrepare
        assert(Orm\Syntax::setPrepare(['what','test'=>'ok',['active','replace','ok','wow']])[1] === ['active','replace','ok','wow']);
        assert(count(Orm\Syntax::setPrepare(['active'=>false,'james'=>[1,2,3],'oK'=>null,'lol.james'=>true])) === 4);

        // setValues

        // updateSet
        assert(Orm\Syntax::updateSet([['active','lower','test'],['id',4]])['sql'] === "`active` = LOWER('test'), `id` = 4");
        assert(Orm\Syntax::updateSet([['active','replace','test','test2']])['sql'] === "`active` = REPLACE(`active`,'test','test2')");
        assert(Orm\Syntax::updateSet(['active'=>false,'james'=>[1,2,3],'oK'=>null,'lol.james'=>true])['sql'] === "`active` = 0, `james` = '1,2,3', `oK` = NULL, lol.`james` = 1");
        assert(Orm\Syntax::updateSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '`active` = 2, `james` = 3, `oK` = NULL, lol.`james` = 1');
        assert(count(Orm\Syntax::updateSet(['james'=>[1,2,'name']],Orm\Syntax::option())['prepare']) === 1);
        assert(Orm\Syntax::updateSet(['active'=>2,'james'=>3,'oK'=>null,'lol.james'=>true])['sql'] === '`active` = 2, `james` = 3, `oK` = NULL, lol.`james` = 1');
        assert(Orm\Syntax::updateSet(['active'=>null,'james'=>true,'ok'=>false])['sql'] === '`active` = NULL, `james` = 1, `ok` = 0');

        // setOne
        assert(Orm\Syntax::setOne(2)['sql'] === '2');

        // setTwo
        assert(Orm\Syntax::setTwo('lower',24)['sql'] === 'LOWER(24)');

        // setThree
        assert(Orm\Syntax::setThree('james','replace','from','to')['sql'] === "REPLACE(`james`,'from','to')");

        // setReplace
        assert(Orm\Syntax::setReplace('james','from','to','replace')['sql'] === "REPLACE(`james`,'from','to')");

        // col
        assert(Orm\Syntax::col(['james'],Orm\Syntax::option())['sql'] === '');
        assert(Orm\Syntax::col(['james','LOLLL'],Orm\Syntax::option())['sql'] === '');
        assert(Orm\Syntax::col(['james','varchar'],Orm\Syntax::option())['sql'] === '`james` VARCHAR(255) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['james','varchar','length'=>55,'default'=>'james','null'=>false],Orm\Syntax::option(['prepare'=>false]))['sql'] === "`james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option())['sql'] === '`james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['james','int','length'=>20,'default'=>3,'autoIncrement'=>true,'after'=>'james'],Orm\Syntax::option())['sql'] === '`james` INT(20) NULL DEFAULT 3 AUTO_INCREMENT AFTER `james`');
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option(['type'=>'addCol']))['sql'] === 'ADD COLUMN `james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option(['type'=>'alterCol']))['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['id','int','length'=>11,'autoIncrement'=>true,'null'=>null])['sql'] === '`id` INT(11) AUTO_INCREMENT');

        // makeCol
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option(['type'=>'createCol']))['sql'] === '`james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option(['type'=>'addCol']))['sql'] === 'ADD COLUMN `james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::col(['james','int'],Orm\Syntax::option(['type'=>'alterCol']))['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');

        // createCol
        assert(Orm\Syntax::createCol(['james','varchar','length'=>55,'default'=>'james','null'=>false],Orm\Syntax::option(['prepare'=>false]))['sql'] === "`james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert(Orm\Syntax::createCol([['james','varchar'],['name'=>'lol','type'=>'int']])['sql'] === '`james` VARCHAR(255) NULL DEFAULT NULL, `lol` INT(11) NULL DEFAULT NULL');

        // addCol
        assert(Orm\Syntax::addCol(['james','varchar','length'=>55,'default'=>'james','null'=>false],Orm\Syntax::option(['prepare'=>false]))['sql'] === "ADD COLUMN `james` VARCHAR(55) NOT NULL DEFAULT 'james'");
        assert(Orm\Syntax::addCol([['james','varchar'],['name'=>'lol','type'=>'int']])['sql'] === 'ADD COLUMN `james` VARCHAR(255) NULL DEFAULT NULL, ADD COLUMN `lol` INT(11) NULL DEFAULT NULL');

        // alterCol
        assert(Orm\Syntax::alterCol(['james','int'])['sql'] === 'CHANGE `james` `james` INT(11) NULL DEFAULT NULL');
        assert(Orm\Syntax::alterCol(['james','int','rename'=>'james2','length'=>25])['sql'] === 'CHANGE `james` `james2` INT(25) NULL DEFAULT NULL');

        // dropCol
        assert(Orm\Syntax::dropCol('test')['sql'] === 'test');
        assert(Orm\Syntax::dropCol(['test'])['sql'] === 'DROP COLUMN `test`');
        assert(Orm\Syntax::dropCol(['test_[lang]','test2.lala'])['sql'] === 'DROP COLUMN `test_en`, DROP COLUMN test2.`lala`');

        // key
        assert(Orm\Syntax::key(['key'=>'key','col'=>'test'])['sql'] === 'KEY (`test`)');
        assert(Orm\Syntax::key(['primary','test'])['sql'] === 'PRIMARY KEY (`test`)');
        assert(Orm\Syntax::key(['primary',null])['sql'] === '');
        assert(Orm\Syntax::key(['unique','test',['james.lol','ok']])['sql'] === 'UNIQUE KEY `test` (james.`lol`, `ok`)');
        assert(Orm\Syntax::key(['unique','test',['james.lol','ok']])['sql'] === 'UNIQUE KEY `test` (james.`lol`, `ok`)');
        assert(Orm\Syntax::key(['unique',null,['james.lol','ok']])['sql'] === '');
        assert(Orm\Syntax::key(['unique','ok'])['sql'] === 'UNIQUE KEY `ok` (`ok`)');
        assert(Orm\Syntax::key(['unique','ok','james'])['sql'] === 'UNIQUE KEY `ok` (`james`)');

        // makeKey
        assert(Orm\Syntax::makeKey(['primary','id'],Orm\Syntax::option(['type'=>'createKey']))['sql'] === 'PRIMARY KEY (`id`)');
        assert(Orm\Syntax::makeKey(['primary','id'],Orm\Syntax::option(['type'=>'addKey']))['sql'] === 'ADD PRIMARY KEY (`id`)');

        // createKey
        assert(Orm\Syntax::createKey(['test'])['sql'] === '');
        assert(Orm\Syntax::createKey(['primary','id'])['sql'] === 'PRIMARY KEY (`id`)');
        assert(Orm\Syntax::createKey(['unique','james',['id','james']])['sql'] === 'UNIQUE KEY `james` (`id`, `james`)');
        assert(Orm\Syntax::createKey(['key','id'])['sql'] === 'KEY (`id`)');
        assert(Orm\Syntax::createKey([['key','id'],['unique','james',['id','james']]])['sql'] === 'KEY (`id`), UNIQUE KEY `james` (`id`, `james`)');

        // addKey
        assert(Orm\Syntax::addKey('test bla')['sql'] === 'test bla');
        assert(Orm\Syntax::addKey(['test'])['sql'] === '');
        assert(Orm\Syntax::addKey(['primary','id'])['sql'] === 'ADD PRIMARY KEY (`id`)');
        assert(Orm\Syntax::addKey(['unique','james',['id','james']])['sql'] === 'ADD UNIQUE KEY `james` (`id`, `james`)');
        assert(Orm\Syntax::addKey([['key','id'],['unique','james',['id','james']]])['sql'] === 'ADD KEY (`id`), ADD UNIQUE KEY `james` (`id`, `james`)');

        // dropKey
        assert(Orm\Syntax::dropKey('test')['sql'] === 'test');
        assert(Orm\Syntax::dropKey(['test'])['sql'] === 'DROP KEY `test`');
        assert(Orm\Syntax::dropKey(['test_[lang]','test2.lala'])['sql'] === 'DROP KEY `test_en`, DROP KEY test2.`lala`');

        // createEnd
        assert(Orm\Syntax::createEnd(Orm\Syntax::option())['sql'] === ') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // prepareDefault

        // make
        assert(Orm\Syntax::make('select',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2])['sql'] === 'SELECT * FROM `user` WHERE `active` = 1 AND `james` = 2 ORDER BY `active` DESC LIMIT 2');
        assert(Orm\Syntax::make('select',['*','where'=>true]) === null);
        assert(Orm\Syntax::make('select',['*','table'=>null]) === null);
        assert(Orm\Syntax::make('select',['*','ok'])['sql'] === 'SELECT * FROM `ok`');
        assert(Orm\Syntax::make('select',['*','ok'])['type'] === 'select');
        assert(Orm\Syntax::make('select',['*','ok'])['table'] === 'ok');
        assert(strlen(Orm\Syntax::make('select',['join'=>['table'=>'lol','on'=>true],'*','ok','order'=>['type'=>'asc'],'where'=>true])['sql']) === 85);
        assert(strlen(Orm\Syntax::make('select',['outerJoin'=>['table'=>'lol','on'=>true],'*','ok','order'=>['type'=>'asc'],'where'=>true])['sql']) === 96);
        assert(Orm\Syntax::make('select',['*','user',['active'=>1,'james'=>'tes\'rttté'],['active'=>'DESC'],2],['prepare'=>false])['sql'] === "SELECT * FROM `user` WHERE `active` = 1 AND `james` = 'tes\'rttté' ORDER BY `active` DESC LIMIT 2");
        assert(Orm\Syntax::make('select',[true,'james3',['id'=>3]])['table'] === 'james3');
        assert(Orm\Syntax::make('select',[true,'james3',[true,'id'=>3]])['id'] === 3);
        assert(Orm\Syntax::make('select',[true,'james3',[true,'id'=>[1,2,3]]])['id'] === [1,2,3]);
        assert(Orm\Syntax::make('create',['james2',['james','int'],[['unique','lol','james'],['primary','id']]],['createNotExists'=>true])['sql'] === 'CREATE TABLE IF NOT EXISTS `james2` (`james` INT(11) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');
        assert(count(Orm\Syntax::make('select',[true,'james3',['name'=>'what'],'prepare'=>['test'=>'ok']])['prepare']) === 2);
        assert(Orm\Syntax::make('select',Orm\Syntax::makeParses('select',['*','table',2,'id',3]))['sql'] === 'SELECT * FROM `table` WHERE `id` = 2 ORDER BY id LIMIT 3');
        assert(Orm\Syntax::make('select',['what'=>'*','table'=>'ok','where'=>'id="2"'])['sql'] === 'SELECT * FROM `ok` WHERE id="2"');
        assert(Orm\Syntax::make('select',['*','james',null,null,0])['sql'] === 'SELECT * FROM `james` LIMIT 0');
        assert(count(Orm\Syntax::make('select',['*','james',[],null,0])) === 3);
        assert(Orm\Syntax::make('select',['*','james',['active'=>1,[12312312,'`between`',['from','to']]]])['sql'] === 'SELECT * FROM `james` WHERE `active` = 1 AND 12312312 BETWEEN `from` AND `to`');
        assert(strlen(Orm\Syntax::make('select',['*','james',['active'=>1,'date'=>Base\Date::timestamp()]])['sql']) === 64);

        // makeParses
        assert(Orm\Syntax::makeParses('select',['*','table',2,'id',3]) === ['what'=>'*','table'=>'table','where'=>2,'order'=>'id','limit'=>3]);

        // makeParse
        assert(Orm\Syntax::makeParse('select','what',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === '*');
        assert(Orm\Syntax::makeParse('select','where',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === ['active'=>1,'james'=>2]);
        assert(Orm\Syntax::makeParse('select','wherez',['*','user',['active'=>1,'james'=>2],['active'=>'DESC'],2]) === null);

        // makeSelectFrom
        $insert = ['table',['ok'=>2,'id'=>4]];
        $update = ['table',['james'=>'ok'],3,['name'=>'asc'],2];
        $delete = ['table',4,['name'=>'asc'],2];
        assert(Orm\Syntax::makeSelectFrom('update',$update)['sql'] === 'SELECT * FROM `table` WHERE `id` = 3 ORDER BY `name` ASC LIMIT 2');
        assert(Orm\Syntax::makeSelectFrom('delete',$delete)['sql'] === 'SELECT * FROM `table` WHERE `id` = 4 ORDER BY `name` ASC LIMIT 2');
        assert(Orm\Syntax::makeSelectFrom('insert',$insert)['sql'] === 'SELECT * FROM `table` WHERE `ok` = 2 AND `id` = 4 LIMIT 1');
        assert(Orm\Syntax::makeSelectFrom('insert',$insert,Orm\Syntax::option())['sql'] === 'SELECT * FROM `table` WHERE `ok` = 2 AND `id` = 4 ORDER BY `id` DESC LIMIT 1');

        // makeSelect
        assert(strlen(Orm\Syntax::makeSelect(['*','user',['active'=>'name'],['order'=>'Desc','active'],[4,4]])['sql']) >= 92);
        assert(count(Orm\Syntax::makeSelect(['*','user',[],['order'=>'Desc','active'],[4,4]])) === 3);

        // makeShow
        assert(Orm\Syntax::makeShow(['TABLES'])['sql'] === 'SHOW TABLES');

        // makeInsert
        assert(strlen(Orm\Syntax::makeInsert(['user',['active'=>1,'james'=>null,'OK.james'=>'LOLÉ']])['sql']) >= 77);
        assert(Orm\Syntax::makeInsert(['user',[]])['sql'] === 'INSERT INTO `user` () VALUES ()');

        // makeUpdate
        assert(Orm\Syntax::makeUpdate(['james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3])['sql'] === 'UPDATE `james` SET `james` = 2, lala.`ok` = NULL WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');

        // makeDelete
        assert(Orm\Syntax::makeDelete(['james',['active'=>1,'james'=>2],['id'],3])['sql'] === 'DELETE FROM `james` WHERE `active` = 1 AND `james` = 2 ORDER BY `id` ASC LIMIT 3');

        // makeCreate
        assert(Orm\Syntax::makeCreate(['james2',[['james','int'],['ok','varchar']],[['unique','lol','james'],['primary','id']]])['sql'] === 'CREATE TABLE `james2` (`james` INT(11) NULL DEFAULT NULL, `ok` VARCHAR(255) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // makeAlter
        assert(Orm\Syntax::makeAlter(['james',null,null,null,null,null])['sql'] === 'ALTER TABLE `james`');
        assert(Orm\Syntax::makeAlter(['james'])['sql'] === 'ALTER TABLE `james`');
        assert(Orm\Syntax::makeAlter(['james'])['table'] === 'james');

        // makeTruncate
        assert(Orm\Syntax::makeTruncate(['james'])['sql'] === 'TRUNCATE TABLE `james`');

        // makeDrop
        assert(Orm\Syntax::makeDrop(['okkk'])['sql'] === 'DROP TABLE `okkk`');

        // select
        assert(strlen(Orm\Syntax::select('*','user',['active'=>'name'],['order'=>'Desc','active'],[4,4])['sql']) >= 92);
        assert(strlen(Orm\Syntax::select([true,'james'=>['distinct','james']],'james_[lang]',[true,'or','(',2,[2,3,4],'james_[lang]'=>4,')',['james','findInSet',[5,6]]],true,true)['sql']) > 220);

        // show
        assert(Orm\Syntax::show('TABLES')['sql'] === 'SHOW TABLES');

        // insert
        assert(strlen(Orm\Syntax::insert('user',['active'=>1,'james'=>null,'OK.james'=>'LOLÉ'])['sql']) >= 77);

        // update
        assert(Orm\Syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3)['sql'] === 'UPDATE `james` SET `james` = 2, lala.`ok` = NULL WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');
        assert(Orm\Syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>1],['od'=>'desc'],3)['select']['sql'] === 'SELECT * FROM `james` WHERE `active` = 1 ORDER BY `od` DESC LIMIT 3');
        assert(count(Orm\Syntax::update('james',['james'=>2,'lala.ok'=>null],['active'=>'ok','id'=>5],['od'=>'desc'],3)['select']) === 6);
        assert(Orm\Syntax::select('*','james',[2])['sql'] === 'SELECT * FROM `james` WHERE `id` = 2');
        assert(Orm\Syntax::update('james',['james'=>2],[2])['sql'] === 'UPDATE `james` SET `james` = 2 WHERE `id` = 2');

        // delete
        assert(Orm\Syntax::delete('james',['active'=>1,'james'=>2],['id'],3)['sql'] === 'DELETE FROM `james` WHERE `active` = 1 AND `james` = 2 ORDER BY `id` ASC LIMIT 3');

        // create
        assert(Orm\Syntax::create('james2',[['james','int'],['ok','varchar']],[['unique','lol','james'],['primary','id']])['sql'] === 'CREATE TABLE `james2` (`james` INT(11) NULL DEFAULT NULL, `ok` VARCHAR(255) NULL DEFAULT NULL, UNIQUE KEY `lol` (`james`), PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4');

        // alter
        assert(Orm\Syntax::alter('james',['james','int'],['unique','lao',['james','id']])['sql'] === 'ALTER TABLE `james` ADD COLUMN `james` INT(11) NULL DEFAULT NULL, ADD UNIQUE KEY `lao` (`james`, `id`)');
        assert(Orm\Syntax::alter('james',null,['unique','lao',['james','id']],[['james','int'],['bla','varchar','rename'=>'LOL']])['sql'] === 'ALTER TABLE `james` ADD UNIQUE KEY `lao` (`james`, `id`), CHANGE `james` `james` INT(11) NULL DEFAULT NULL, CHANGE `bla` `LOL` VARCHAR(255) NULL DEFAULT NULL');
        assert(Orm\Syntax::alter('james',null,null,null,['test','ok'],'JAMES SQL')['sql'] === 'ALTER TABLE `james` DROP COLUMN `test`, DROP COLUMN `ok`, JAMES SQL');
        assert(Orm\Syntax::alter('james',null,null,null,null,null)['sql'] === 'ALTER TABLE `james`');

        // truncate
        assert(Orm\Syntax::truncate('james')['sql'] === 'TRUNCATE TABLE `james`');

        // drop
        assert(Orm\Syntax::drop('okkk')['sql'] === 'DROP TABLE `okkk`');

        // count
        assert(Orm\Syntax::selectCount('user')['sql'] === 'SELECT COUNT(`id`) FROM `user`');

        // makeSelectCount
        assert(Orm\Syntax::makeSelectCount(['my',2])['sql'] === 'SELECT COUNT(`id`) FROM `my` WHERE `id` = 2');

        // makeSelectAll
        assert(Orm\Syntax::makeSelectAll(['james',2])['sql'] === 'SELECT * FROM `james` WHERE `id` = 2');
        assert(Orm\Syntax::makeSelectAll(['james',['test'=>null]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NULL');
        assert(Orm\Syntax::makeSelectAll(['james',['test'=>true]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");
        assert(Orm\Syntax::makeSelectAll(['james',['test'=>false]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert(Orm\Syntax::makeSelectAll(['james',[['test',true]]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");
        assert(Orm\Syntax::makeSelectAll(['james',[['test','empty']]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert(Orm\Syntax::makeSelectAll(['james',[['test',null]]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NULL');
        assert(Orm\Syntax::makeSelectAll(['james',[['test','notNull']]])['sql'] === 'SELECT * FROM `james` WHERE `test` IS NOT NULL');
        assert(Orm\Syntax::makeSelectAll(['james',[['test',false]]])['sql'] === "SELECT * FROM `james` WHERE (`test` = '' OR `test` IS NULL)");
        assert(Orm\Syntax::makeSelectAll(['james',[['test','notEmpty']]])['sql'] === "SELECT * FROM `james` WHERE (`test` != '' AND `test` IS NOT NULL)");

        // makeSelectFunction
        assert(Orm\Syntax::makeSelectFunction('col','sum',['james',2])['sql'] === 'SELECT SUM(`col`) FROM `james` WHERE `id` = 2');

        // makeSelectDistinct
        assert(Orm\Syntax::makeSelectDistinct('col',['james',2])['sql'] === 'SELECT DISTINCT `col` FROM `james` WHERE `id` = 2');

        // makeSelectColumn
        assert(Orm\Syntax::makeSelectColumn('col',['james',2])['sql'] === 'SELECT `col` FROM `james` WHERE `id` = 2');
        assert(Orm\Syntax::makeSelectColumn(['what','sum()'],['james'])['sql'] === 'SELECT SUM(`what`) FROM `james`');

        // makeselectKeyPair
        assert(Orm\Syntax::makeselectKeyPair('col','col2',['james',2])['sql'] === 'SELECT `col`, `col2` FROM `james` WHERE `id` = 2');

        // makeselectPrimary
        assert(Orm\Syntax::makeselectPrimary(['table',2],['primary'=>'idsz'])['sql'] === 'SELECT idsz FROM `table` WHERE `idsz` = 2');

        // makeselectPrimaryPair
        assert(Orm\Syntax::makeselectPrimaryPair('col',['table',2])['sql'] === 'SELECT `id`, `col` FROM `table` WHERE `id` = 2');

        // makeSelectSegment
        assert(Orm\Syntax::makeSelectSegment('[col] + [name_%lang%] [col]  v [col] [id]',['james',2])['sql'] === 'SELECT `id`, `col`, `name_en` FROM `james` WHERE `id` = 2');
        assert(Orm\Syntax::makeSelectSegment('[col] + [name_%lang%] [id]',['james',2])['sql'] === 'SELECT `id`, `col`, `name_en` FROM `james` WHERE `id` = 2');

        // makeShowDatabase
        assert(Orm\Syntax::makeShowDatabase()['sql'] === 'SHOW DATABASES');
        assert(Orm\Syntax::makeShowDatabase('quid')['sql'] === "SHOW DATABASES LIKE 'quid'");

        // makeShowVariable
        assert(Orm\Syntax::makeShowVariable()['sql'] === 'SHOW VARIABLES');
        assert(Orm\Syntax::makeShowVariable('automatic')['sql'] === "SHOW VARIABLES LIKE 'automatic'");

        // makeShowTable
        assert(Orm\Syntax::makeShowTable()['sql'] === 'SHOW TABLES');
        assert(Orm\Syntax::makeShowTable('basePdo')['sql'] === "SHOW TABLES LIKE 'basePdo'");

        // makeShowTableStatus
        assert(Orm\Syntax::makeShowTableStatus()['sql'] === 'SHOW TABLE STATUS');
        assert(Orm\Syntax::makeShowTableStatus('test_[lang]')['sql'] === "SHOW TABLE STATUS LIKE 'test_en'");

        // makeShowTableColumn
        assert(Orm\Syntax::makeShowTableColumn('myTable','lol')['sql'] === "SHOW COLUMNS FROM `myTable` WHERE FIELD = 'lol'");
        assert(Orm\Syntax::makeShowTableColumn('myTable')['sql'] === 'SHOW COLUMNS FROM `myTable`');
        assert(Orm\Syntax::makeShowTableColumn('myTable')['table'] === 'myTable');

        // makeAlterAutoIncrement
        assert(Orm\Syntax::makeAlterAutoIncrement('table',3)['sql'] === 'ALTER TABLE `table` AUTO_INCREMENT = 3');
        assert(Orm\Syntax::makeAlterAutoIncrement('table',3)['table'] === 'table');

        // parseReturn
        assert(Orm\Syntax::parseReturn('SELECT * from james')['type'] === 'select');
        assert(Orm\Syntax::parseReturn(['UPDATE james',['ok'=>'lol']])['prepare'] === ['ok'=>'lol']);
        assert(Orm\Syntax::parseReturn(['sql'=>'UPDATE james','prepare'=>['ok'=>'lol']])['sql'] === 'UPDATE james');

        // type
        assert(Orm\Syntax::type('ALTER TABLE `james` DROP COLUMN `test`') === 'alter');
        assert(Orm\Syntax::type(' SELECT TABLE `james` DROP COLUMN `test`') === 'select');

        // emulate
        $sql = 'SELECT * FROM `user` WHERE `active` = :APCBIE18 AND `test` = 2';
        $prepare = ['APCBIE18'=>'name'];
        assert(Orm\Syntax::emulate($sql,$prepare) === "SELECT * FROM `user` WHERE `active` = 'name' AND `test` = 2");
        $sql = 'SELECT * FROM `user` WHERE `active` = :APCBIE18 AND `test` = 2';
        $prepare = ['APCBIE18'=>'na\me'];
        assert(Orm\Syntax::emulate($sql,$prepare,null,true) === 'SELECT * FROM `user` WHERE `active` = \'na\\me\' AND `test` = 2');
        assert(Orm\Syntax::emulate($sql,$prepare,null,false) === 'SELECT * FROM `user` WHERE `active` = \'na\\\\me\' AND `test` = 2');

        // debug
        assert(Orm\Syntax::debug(Orm\Syntax::select('*','james',['name'=>'ok']))['emulate'] === "SELECT * FROM `james` WHERE `name` = 'ok'");

        // shortcut
        assert(!empty(Orm\Syntax::allShortcuts()));
        assert(Orm\Syntax::shortcuts(['test'=>'name_[lang]']) === ['test'=>'name_en']);
        assert(Orm\Syntax::getShortcut('lang') === 'en');
        Orm\Syntax::setShortcut('james','ok');
        assert(Orm\Syntax::getShortcut('james') === 'ok');
        Orm\Syntax::unsetShortcut('james');
        assert(Orm\Syntax::shortcut('name_[lang]') === 'name_en');
        assert(Orm\Syntax::shortcut(['name_[lang]']) === ['name_en']);

        // option
        assert(count(Orm\Syntax::option(['primary'=>'iz'])) === 13);
        assert(Orm\Syntax::isOption('primary') === true);
        assert(Orm\Syntax::getOption('primary') === 'id');
        Orm\Syntax::setOption('test',true);
        Orm\Syntax::setOption('test2',true);
        Orm\Syntax::unsetOption('test');
        Orm\Syntax::unsetOption('test2');
        assert(Orm\Syntax::option(['test'=>2])['test'] === 2);
        assert(empty(Orm\Syntax::option()['test']));

        // cleanup

        return true;
    }
}
?>