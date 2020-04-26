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

// cols
// class for testing Quid\Orm\Cols
class Cols extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCols';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name_en','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,11,12,13],[2,2,'james2',20,21,22,23]) === [1,2]);
        $tb = $db[$table];
        $tb2 = $db['ormDb'];
        $cols = $tb->cols();
        foreach ($cols as $key => $value) { }
        $clone = $cols->clone();

        // construct

        // toString

        // onPrepareKey
        assert($cols->get($tb[1]['id']) === $cols['id']);
        assert($cols->in($tb->col('id')));

        // onPrepareReturns

        // cast
        assert($cols->_cast()[0] === 'id');

        // offsetSet

        // names
        assert(count($cols->names()) === 9);

        // namesWithoutPrimary
        assert(count($cols->namesWithoutPrimary()) === 8);
        assert($cols->namesWithoutPrimary()[0] === 'name_en');

        // db
        assert($cols->db() instanceof Orm\Db);

        // table
        assert($cols->table() instanceof Orm\Table);

        // add

        // are
        assert($cols->are('id','date','email','name_en','active','dateAdd','dateModify','userAdd','userModify'));
        assert(!$cols->are('idz','email','name_en','active','dateAdd','dateModify','userAdd','userModify'));

        // withoutPrimary
        assert(count($cols->withoutPrimary()) === 8);

        // default
        assert($cols->default() === ['name_en'=>'LOL','active'=>1,'email'=>'default@def.james','date'=>null,'userAdd'=>null,'dateAdd'=>null,'userModify'=>null,'dateModify'=>null]);

        // value
        assert($cols->value(['id'=>4,'dateAdd'=>123123213,'james'=>'OK'],true) === ['id'=>4,'dateAdd'=>'November 25, 1973 19:53:33']);

        // isVisible
        assert(!$cols->isVisible());

        // isHidden
        assert(!$cols->isHidden());

        // isRequired
        assert($cols->isRequired() === ['name_en'=>'LOL','email'=>'default@def.james','date'=>null]);
        assert($cols->isRequired(['email'=>null]) === ['name_en'=>'LOL','email'=>null,'date'=>null]);

        // isStillRequired
        assert($cols->isStillRequired() === ['date'=>null]);
        assert($cols->isStillRequired(['email'=>null]) === ['email'=>null,'date'=>null]);

        // isStillRequiredEmpty
        assert(!$cols->isStillRequiredEmpty());

        // rules
        assert(count($cols->rules()) === 9);
        assert($cols->rules(false,false) !== $cols->rules(false,true));

        // preValidatePrepare
        assert($cols->preValidatePrepare(['email'=>'ok']) === ['email'=>'ok']);

        // preValidate
        assert($cols->preValidate() === []);
        assert($cols->preValidate(['date'=>'as']) === ['date'=>['dateToDay']]);
        assert($cols->preValidate(['date'=>'02-02-2017']) === []);

        // validate
        assert($cols->validate(['name_en'=>null,'dateAdd'=>1234]) === []);
        assert($cols->validate(['name_en'=>123]) === ['name_en'=>['string']]);
        assert($cols->validate(['name_en'=>123],true)['name_en'] === ['Must be a string']);

        // required
        assert($cols->required(['date'=>2,'email'=>'','name_en'=>'OK']) === ['email'=>'required']);
        assert($cols->required(['name_en'=>''])['name_en'] === 'required');
        assert($cols->required(['name_en'=>''],true)['name_en'] === 'Cannot be empty');

        // unique
        assert($cols->unique(['email'=>'bla']) === []);

        // compare
        assert($cols->compare(['email'=>'bla']) === []);

        // completeValidation
        assert($cols->completeValidation(['email'=>''])['email'] === ['required','email']);
        assert($cols->completeValidation(['email'=>'asd'])['email'] === ['email']);
        assert(count($cols->completeValidation(['email'=>''],true,false)) === 9);

        // triggerValidate

        // included
        assert($cols->included()->isCount(5));

        // insert
        assert($cols->insert('name_en',2) === '2');

        // inserts
        assert(is_int($cols->inserts(['test'=>2])['dateAdd']));
        assert(count($cols->inserts(['name_en'=>2],['required'=>false])) === 3);
        assert(count($cols->inserts(['name_en'=>2])) === 5);
        assert(count($cols->inserts(['name_en'=>2],['default'=>true])) === 8);

        // label
        assert($cols->label()['active'] === 'Active');
        assert($cols->label('%:')['active'] === 'Active:');

        // description
        assert($cols->description()['dateAdd'] === 'Perfect');
        assert($cols->description('%:')['dateAdd'] === 'Perfect:');

        // groupSetPriority
        assert($cols->groupSetPriority()[5] instanceof Orm\Cols);

        // form
        assert(count($cols->form()) === 9);
        assert(is_string($cols->form(true)));

        // formPlaceholder
        assert(strlen($cols->formPlaceholder()['id']) === 93);
        assert(is_string($cols->formPlaceholder(true)));

        // formWrap
        assert(count($cols->formWrap('br')) === 9);
        assert(strlen(current($cols->formWrap('br','%:'))) === 136);
        assert(is_string($cols->formWrap('br',null,true)));

        // formPlaceholderWrap
        assert(count($cols->formPlaceholderWrap(null)) === 9);
        assert(strlen($cols->formPlaceholderWrap('br')['id']) === 152);
        assert(is_string($cols->formPlaceholderWrap('br',null,true)));

        // htmlStr
        assert(is_string($cols->htmlStr("<div class='%name%'>%label%: %value%</div>")['name_en']));
        assert(is_string($cols->htmlStr("<div class='%name%'>%label%: %value%</div>",true)));

        // orderable
        assert($cols->orderable()->isCount(9));

        // filterable
        assert($cols->filterable()->isMinCount(5));

        // searchable
        assert(count($cols->searchable()) !== count($cols));

        // searchMinLength
        assert($cols->searchMinLength() === 3);

        // isSearchTermValid
        assert($cols->isSearchTermValid('avbc'));
        assert(!$cols->isSearchTermValid('a'));

        // writeFile

        // keyClassExtends
        assert(count($cols::keyClassExtends()) === 2);

        // getOverloadKeyPrepend
        assert($cols::getOverloadKeyPrepend() === null);

        // mapObj
        assert($cols->pair('isRequired')['id'] === false);
        assert($cols->filter(fn($col) => $col->kind() === 'char')->isCount(2));
        assert($cols->filter(fn($col) => $col->value() === 1)->isCount(1));
        assert(!$cols->filter(fn($col) => $col->value() !== 1)->isCount(1));
        assert(count($cols->group('kind')) === 2);
        $sort = $clone->sortBy('name',false);
        assert($sort->first()->name() === 'userModify');
        assert($sort !== $clone);
        assert($clone === $clone->sortDefault());

        // readOnly
        assert($clone->empty()->isEmpty());
        assert($clone->add($cols)->count() === $cols->count());
        assert($clone->unset('id')->count() !== $cols->count());
        assert($clone->remove($clone['name_[lang]'],$clone['active'])->isCount(6));
        assert($clone->unset($cols)->isEmpty());

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>