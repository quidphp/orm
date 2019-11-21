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

// colRelation
// class for testing Quid\Orm\ColRelation
class ColRelation extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCol';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','relationRange','relationStr','dateAdd','dateModify'],[1,0,0,time(),time()],[2,2,'lol',time() + 3000000,time() + 2000000]) === [1,2]);
        $tb = $db[$table];
        $user = $db['user'];
        $userId = $tb['user_id']->relation();
        $userIds = $tb['user_ids']->relation();
        $myRelation = $tb['myRelation']->relation();
        $other = $tb['other']->relation();
        $array = $tb['myRelation']->relation();
        $range = $tb['relationRange']->relation();
        $str = $tb['relationStr']->relation();
        $lang = $tb['relationLang']->relation();
        $multi = $tb['multi']->relation();
        $check = $tb['check']->relation();
        $rangeInt = $tb['rangeInt']->relation();
        $userAdd = $tb['userAdd']->relation();
        $userAdd2 = $user['userAdd']->relation();
        $dateAdd = $tb['dateAdd']->relation();
        $relationCall = $tb['relationCall']->relation();
        $userAdd->empty();

        // construct

        // prepare

        // mode
        assert($userId->mode() === 'enum');
        assert($userIds->mode() === 'set');
        assert($dateAdd->mode() === 'enum');
        assert($relationCall->mode() === 'enum');

        // attributes
        assert($userId->attributes() === 'user');
        assert($dateAdd->attributes() === 'date');

        // whereTable

        // col
        assert($userId->col() instanceof Orm\Col);
        assert($dateAdd->col() instanceof Orm\Col);

        // isEnum
        assert($userId->isEnum());
        assert(!$userIds->isEnum());
        assert($dateAdd->isEnum());

        // isSet
        assert($userIds->isSet());
        assert(!$userId->isSet());
        assert(!$dateAdd->isSet());

        // isType
        assert($array->isType('array'));

        // type
        assert($array->type() === 'array');
        assert($range->type() === 'range');
        assert($lang->type() === 'lang');
        assert($rangeInt->type() === 'range');
        assert($multi->type() === 'lang');
        assert($check->type() === 'range');
        assert($dateAdd->type() === 'date');
        assert($relationCall->type() === 'callable');

        // checkType
        assert($array->checkType() === 'array');
        assert($range->checkType() === 'range');
        assert($lang->checkType() === 'lang');
        assert($multi->checkType() === 'lang');
        assert($check->checkType() === 'range');
        assert($dateAdd->checkType() === 'date');

        // isRelationTable
        assert($userAdd->isRelationTable());
        assert(!$multi->isRelationTable());
        assert(!$dateAdd->isRelationTable());
        assert(!$relationCall->isRelationTable());

        // defaultOrderCode
        assert($userAdd->defaultOrderCode() === 2);
        assert($multi->defaultOrderCode() === 3);

        // allowedOrdering
        assert($userAdd->allowedOrdering() === ['key'=>true,'value'=>true]);
        assert($multi->allowedOrdering() === ['value'=>true]);
        assert($relationCall->allowedOrdering() === ['value'=>true]);

        // relationTable
        assert($userAdd->relationTable() instanceof Orm\Table);
        assert($userId->relationTable() instanceof Orm\Table);
        assert($dateAdd->relationTable() === null);

        // checkRelationTable
        assert($userIds->checkRelationTable() instanceof Orm\Table);

        // label
        assert($array->label() === 'My relation');
        assert($multi->label() === 'multi');
        assert($check->label() === 'check');
        assert($userAdd->label() === 'User');
        assert($dateAdd->label() === 'Date added');

        // size
        assert($array->size() === 4);
        assert($range->size() === 11);
        assert($lang->size() === 3);
        assert($multi->size() === 3);
        assert($check->size() === 11);
        assert($userId->size() === 5);
        assert($userAdd->size() === 5);
        assert($dateAdd->size() >= 2);
        assert($relationCall->size() === 3);

        // all
        assert($array->all() === ['test',3,4,9=>'ok']);
        assert(count($range->all()) === 11);
        assert(count($lang->all()) === 3);
        assert(count($multi->all()) === 3);
        assert(count($check->all()) === 11);
        assert(count($rangeInt->all()) === 8);
        assert($userAdd[1] === 'nobody (#1)');
        assert(count($userAdd->all()) !== 5);
        assert(count($userAdd2->all()) !== 5);
        assert(count($userId->all(false)) === 5);
        assert(count($userAdd->all()) === 5);
        assert(count($userAdd2->all()) === 5);
        assert(array_keys($userAdd->all(false)) === [5,4,3,2,1]);
        assert($range->all(false,['limit'=>3]) === [0,2=>2,4=>4]);
        assert($range->all(false,['limit'=>[2=>2]]) === [4=>4,6=>6]);
        assert($range->count() === 11);
        assert($range->size() === 11);
        assert($range->size(false) === 11);
        assert(count($userAdd2->all(false,['not'=>[1]])) === 4);
        assert(count($dateAdd->all()) >= 2);
        assert(key($lang->all(false,['order'=>4])) === 3);
        assert(key($userAdd->all(false,['order'=>1])) === 1);
        assert(key($userAdd->all(false,['order'=>2])) === 5);
        assert(key($userAdd->all(false,['order'=>3])) === 2);
        assert(key($userAdd->all(false,['order'=>4])) === 3);
        assert(key($userAdd->all(true,['order'=>1])) === 1);
        assert(key($userAdd->all(true,['order'=>2])) === 5);
        assert(key($userAdd->all(true,['order'=>3])) === 2);
        assert(key($userAdd->all(true,['order'=>4])) === 3);
        assert($relationCall->all() === ['test','test2','test3']);

        // exists
        assert(!$lang->exists('oken'));
        assert($lang->exists(2));
        assert($lang->exists(5,2));
        assert($userId->exists(1));
        assert($userId->exists(1,2));
        assert(!$userId->exists(1,2,800));

        // in
        assert($lang->in('oken'));
        assert(!$lang->in('okenz'));
        assert($multi->in('oken'));
        assert($multi->in('oken','bla'));
        assert(!$multi->in('oken','blaz'));
        assert($userId->in('nobody (#1)'));
        assert($userId->in('nobody (#1)','admin (#2)'));
        assert($userId->in('nobody (#1)','admin (#2)'));
        assert(!$userId->in('nobody (#1)','adminz (#2)'));

        // search
        assert($userId->search('nobo ody') === [1=>'nobody (#1)']);
        assert($userId->search('adm') === [2=>'admin (#2)']);
        assert($userId->search('zzz') === []);
        assert($lang->search('wll LEL') === [3=>'wllel']);
        assert($lang->search('wll + LEL',['searchSeparator'=>'+']) === [3=>'wllel']);
        assert($lang->search('wll') === [3=>'wllel']);
        assert($lang->search('cxzzcxzxc') === []);
        assert($lang->search('e',['limit'=>1]) === [2=>'oken']);
        assert($lang->search('e',['not'=>[2],'limit'=>1]) === [3=>'wllel']);
        assert($lang->empty() === $lang);
        assert($lang->search('e',['limit'=>[2=>1]]) === [3=>'wllel']);
        assert($lang->search('e',['limit'=>[1=>1]]) === [2=>'oken']);
        assert(key($lang->search('e',['order'=>2])) === 3);
        assert(key($lang->search('e',['order'=>3])) === 2);

        // searchCount
        assert($lang->searchCount('e') === 2);
        assert($lang->searchCount('e',['not'=>[2]]) === 1);
        assert($lang->searchCount('e',['limit'=>0,'not'=>[2]]) === 1);

        // notOrderLimit

        // keyValue
        assert($lang->keyValue(2) === [2=>'oken']);
        assert($userId->keyValue(2) === [2=>'admin (#2)']);
        assert($userIds->keyValue(2) === [2=>'admin (#2)']);
        assert(count($userIds->keyValue([1,2,3])) === 3);
        assert(count($userIds->keyValue([2,1,3,1000])) === 4);
        assert(count($userIds->keyValue([1,2,3,1000],true)) === 3);

        // one
        assert($lang->one(2) === 'oken');
        assert($lang->one(8) === null);
        assert($lang->one('oken') === null);
        assert($userAdd->one(2) === 'admin (#2)');
        assert($range->one(0) === 0);
        assert($str->one(0) === 'test');

        // many
        assert($multi->many(2) === [2=>'oken']);
        assert($multi->many([2,3]) === [2=>'oken',3=>'wllel']);
        assert($multi->many([2,3,1000]) === [2=>'oken',3=>'wllel',1000=>null]);
        assert($multi->many([2,3,1000],true) === [2=>'oken',3=>'wllel']);
        assert($userIds->many(1) === [1=>'nobody (#1)']);
        assert($userIds->many([2,1,1000]) === [2=>'admin (#2)',1=>'nobody (#1)',1000=>null]);
        assert($userIds->many([2,1,1000],true) === [2=>'admin (#2)',1=>'nobody (#1)']);

        // row
        assert($userAdd->row(2) instanceof Orm\Row);

        // rows
        assert($userAdd->rows(2) instanceof Orm\Rows);
        assert($userIds->rows([3,2,1]) instanceof Orm\Rows);
        assert($userIds->rows([3,2,1,100])->isCount(3));

        // get
        assert($multi->get('2,3') === [2=>'oken',3=>'wllel']);
        assert($lang->get(8) === null);
        assert($lang->get(2) === 'oken');

        // getStr
        assert($multi->getStr('2,3') === 'oken,wllel');
        assert($multi->getStr('2,3','-') === 'oken-wllel');
        assert($lang->getStr(2) === 'oken');
        assert($lang->getStr(8) === null);

        // getKeyValue
        assert($lang->getKeyValue(2) === [2=>'oken']);

        // getRow
        assert($userAdd->getRow(2) instanceof Orm\Row);
        assert($userIds->getRow([3,2,1]) instanceof Orm\Rows);
        assert($userAdd->getRow(1000) === null);
        assert($userIds->getRow(1000)->isEmpty());

        // relation 0
        assert($tb->selectPrimaries(['relationRange'=>0]) === [1]);
        assert($tb->selectPrimaries(['relationRange'=>2]) === [2]);
        assert($tb->selectPrimaries(['relationStr'=>0]) === [1]);
        assert($tb->selectPrimaries(['relationStr'=>'lol']) === [2]);

        // arrMap
        $clone = clone $lang;
        assert($clone !== $lang);
        assert($clone->toJson() === '{"2":"oken","3":"wllel","5":"bla"}');
        assert(count($clone->toArray()) === 3);
        assert(count($clone->_cast()) === 3);
        assert(!empty(serialize($clone)));
        assert($lang->empty() === $lang);
        assert(count($userId->all()) === 5);
        assert($userId[2] === 'admin (#2)');
        assert($userAdd2->isNotEmpty());
        assert($userId->isNotEmpty());
        assert($userId->empty() === $userId);
        assert($userAdd2->isEmpty());
        assert($userId->isEmpty());
        assert($userId->isCount(0));
        assert($userAdd2->count() === 0);
        assert($userId[2] === 'admin (#2)');
        assert($userAdd2->count() === 1);

        return true;
    }
}
?>