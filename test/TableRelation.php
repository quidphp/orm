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

// tableRelation
// class for testing Quid\Orm\TableRelation
class TableRelation extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormTable';
        $table2 = 'ormTableSibling';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name_en','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,11,12,13],[2,2,'james2',20,21,22,23]) === [1,2]);
        assert($db->inserts($table2,['id','active','name_en','content_en'],[1,1,'test','ok'],[2,2,'test2','ok2']));
        $tb = $db[$table];
        $tb2 = $db[$table2];
        $insert = $tb->insert(['date'=>time(),'name_fr'=>'nomFr']);
        $insert2 = $tb->insert(['date'=>time(),'name_en'=>'LOL2','name_fr'=>'nomFr']);
        $user = $db['user']->relation();
        $session = $db['session']->relation();
        $rel = $tb->relation();
        $rel2 = $tb2->relation();

        // construct
        assert($rel instanceof Orm\TableRelation);

        // attrAll

        // makeAttr

        // prepareAttrWithWhat

        // prepareAttrWithMethod

        // prepareOption

        // shouldCache

        // isOutputMethod
        assert(!$rel->isOutputMethod());
        assert($rel2->isOutputMethod());

        // size
        assert($rel->size(false) === 4);
        assert($user->size(false) === 5);
        assert($rel2->size() === 2);

        // tableAccess
        assert($rel->db() instanceof Orm\Db);
        assert($rel->table() === $tb);
        assert($rel2->table() === $tb2);

        // get
        assert($rel->get(2) === 'December 31, 1969 19:00:20 james2 _ 2');
        assert($rel2->get(1) === 'test');

        // gets
        assert(array_keys($rel->gets([1,3,2])) === [1,3,2]);
        assert(count($rel->gets([3,2,1])) === 3);
        assert(count($rel->gets([1,2,3])) === 3);
        assert($user->gets([3,1]) === [3=>'user (#3)',1=>'nobody (#1)']);
        assert($user->gets([1,3]) === [1=>'nobody (#1)',3=>'user (#3)']);
        assert($rel2->gets([1,2]) === [1=>'test',2=>'test2']);

        // all
        assert(array_keys($rel->all()) === [2,1,3,4]);
        assert(count($rel->all()) === 4);
        assert($user->all(false) === [5=>'cli (#5)',4=>'inactive (#4)',3=>'user (#3)',2=>'admin (#2)',1=>'nobody (#1)']);
        assert($user->all() === [5=>'cli (#5)',4=>'inactive (#4)',3=>'user (#3)',2=>'admin (#2)',1=>'nobody (#1)']);
        assert($user->all(false,['limit'=>2]) === [5=>'cli (#5)',4=>'inactive (#4)']);
        assert($user->count() === 2);
        assert($rel2->all(false) === [1=>'test',2=>'test2']);

        // exists
        assert($user->exists(3,4,1));
        $user->empty();
        assert($user->exists(3,4,1));
        assert(!$user->exists(3,4,100));
        assert(!$rel2->exists(1,2,3));
        assert($rel2->exists(1,2));

        // existsWhere

        // in
        assert($user->in('admin (#2)','nobody (#1)'));
        $user->empty();
        assert($user->in('admin (#2)','nobody (#1)'));
        assert(!$user->in('admin (#2)','nobodyz (#1)'));
        assert($rel2->in('test2'));

        // inWhere

        // search
        assert($user->search('nob',['limit'=>1]) === [1=>'nobody (#1)']);
        assert($user->search('adm min') === [2=>'admin (#2)']);
        assert($user->search('adm + min',['searchSeparator'=>'+']) === [2=>'admin (#2)']);
        assert($user->search('well') === []);
        assert($rel2->search('test') === [1=>'test',2=>'test2']);
        assert($rel2->search('test2') === [2=>'test2']);

        // searchCount
        assert($rel2->searchCount('test') === 2);
        assert($user->searchCount('nob') === 1);

        // searchResult

        // defaultOrderCode
        assert($user->defaultOrderCode() === 2);

        // getOrder
        assert($user->getOrder() === ['id'=>'desc']);
        assert($user->getOrder(['james'=>'asc']) === ['james'=>'asc']);
        assert($user->getOrder(1) === ['id'=>'asc']);
        assert($user->getOrder(2) === ['id'=>'desc']);
        assert($user->getOrder(3) === ['username'=>'asc']);
        assert($user->getOrder(4) === ['username'=>'desc']);

        // allowOrdering
        assert($user->allowedOrdering() === ['key'=>true,'value'=>true]);
        assert($rel->allowedOrdering() === ['key'=>true,'value'=>true]);

        // getOrderFieldOutput
        assert($user->getOrderFieldOutput() === 'username');
        assert($rel->getOrderFieldOutput() === 'dateAdd');

        // makeOutput

        // output

        // outputAdd

        // outputMethod

        // outputPrimary
        assert(Orm\TableRelation::outputPrimary(2,'test') === 'test (#2)');

        // attr
        assert($rel->attr() === ['what'=>['id','name_en','dateAdd'],'appendPrimary'=>true,'onGet'=>true,'output'=>['[dateAdd] [name_en] _ [id]'],'order'=>['name_en'=>'desc'],'where'=>[]]);
        assert($user->attr()['order'] === ['id'=>'desc']);
        assert($user->attr()['what'] === ['username','email','id']);
        assert($session->attr()['what'] === ['id']);

        // arrMap
        assert($user->isNotEmpty());
        assert($user->empty() === $user);
        assert($user->isEmpty());
        assert($user[1] === 'nobody (#1)');

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->truncate($table2) instanceof \PDOStatement);

        return true;
    }
}
?>