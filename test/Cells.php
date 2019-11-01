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

// cells
// class for testing Quid\Orm\Cells
class Cells extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCells';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name_en','date','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',time(),10,11,12,13],[2,2,'james2',time(),20,21,22,23]) === [1,2]);
        $tb = $db[$table];
        $row = $tb[1];
        $row2 = $tb[2];
        $cells = $row->cells();
        foreach ($cells as $key => $value) { }
        $clone = $cells->clone();

        // construct

        // toString

        // onPrepareKey
        assert($cells->get($tb->col('id')) === $cells['id']);
        assert($cells->get(0)->name() === 'id');
        assert($cells->get(['LOL',1000,1])->name() === 'name_en');

        // onPrepareReturns
        assert($cells->gets('id','active','name_[lang]')->count() === 3);

        // cast
        assert(!empty($cells->_cast()));

        // offsetSet

        // isWhere
        assert($cells->isWhere([['id',true],['name_en',true],['dateAdd','=',10]]));
        assert(!$cells->isWhere([['id',true],['name_en',true],['dateAdd','>',10]]));

        // names
        assert(count($cells->names()) === 9);

        // namesWithoutPrimary
        assert(count($cells->namesWithoutPrimary()) === 8);
        assert($cells->namesWithoutPrimary()[0] === 'name_en');

        // db
        assert($cells->db() instanceof Orm\Db);

        // table
        assert($cells->table() instanceof Orm\Table);

        // row
        assert($cells->row() instanceof Orm\Row);

        // add

        // withoutPrimary
        assert(count($cells->withoutPrimary()) === 8);
        assert($cells->withoutPrimary() instanceof Orm\Cells);

        // isVisible
        assert($cells->isVisible());

        // isHidden
        assert(!$cells->isHidden());

        // isRequired
        assert($cells->isRequired()->isCount(3));

        // isStillRequired
        assert(!$cells->isStillRequiredEmpty());
        assert($cells->isStillRequired()->isCount(1));
        $cells['email']->set('test@test.com');
        assert($cells->isStillRequired()->isEmpty());

        // isStillRequiredEmpty
        assert($cells->isStillRequiredEmpty());

        // rules
        assert(count($cells->rules()) === 9);
        assert($cells->rules(true)['id']['validate'][0] === 'Must be an integer');
        assert($cells->rules(false,false) !== $cells->rules(false,true));

        // preValidatePrepare
        assert($cells->preValidatePrepare(['email'=>'ok']));

        // preValidate
        assert($cells->preValidate() === []);
        assert($cells->preValidate(['date'=>'12-03-2017']) === []);
        assert($cells->preValidate(['date'=>'']) === []);
        assert($cells->preValidate(['date'=>null]) === []);
        assert($cells->preValidate(['date'=>0])['date'] === ['dateToDay']);
        assert(count($cells->preValidate(['date'=>0],true,false)) === 9);

        // validate
        assert($cells->validate(false,false)['name_en'] === true);
        $cells['email']->set('testtest.com');
        assert($cells->validate()['email'] === ['email']);
        assert($cells->validate(true,true)['email'] === ['Must be a valid email (x@x.com)']);

        // required
        assert($cells->required(false,false)['id'] === true);
        assert($cells->required(true,false)['id'] === true);

        // unique
        assert($cells->unique() === []);

        // compare
        assert($cells->compare() === []);

        // completeValidation
        assert($cells->completeValidation()['email'] === ['email']);
        $cells['email']->set('test@test.com');
        assert(empty($cells->completeValidation()['email']));
        $cells['email']->set(null);
        assert($cells->required(true)['email'] === 'Cannot be empty');
        assert($cells->completeValidation()['email'] === ['required']);
        $cells['email']->set('testtest.com');

        // update
        assert($cells->hasChanged());
        assert($cells->update() === $cells);

        // delete
        assert($cells->delete() === $cells);

        // hasChanged
        assert($cells->hasChanged());

        // notEmpty
        assert($cells->notEmpty()->isCount(9));

        // firstNotEmpty
        assert($cells->firstNotEmpty()->name() === 'id');

        // set
        assert($cells->set('active',3)->keyValue()['active'] === 3);

        // sets
        assert($cells->sets(['active'=>4])['active']->value() === 4);
        assert($cells->hasChanged());

        // changed
        assert($cells->clone()->unset('id')->pair('reset')['active']->value() === 1);
        assert($cells->gets('active','dateModify')->pair('unset')['active']->value() === 6);
        assert($cells->changed()['dateModify'] instanceof Orm\Cell);
        $cells['name_[lang]'] = 'bla';
        $cells['active'] = 2;
        assert($cells['active'] instanceof Orm\Cell);
        assert(count($cells->changed(true)) === 6);

        // included
        assert($cells->included() instanceof Orm\Cells);

        // keyValue
        assert(count($cells->keyValue()) === 9);
        assert($cells->keyValue()['id'] === 1);

        // label
        assert($cells->label()['dateAdd'] === 'Date added');
        assert($cells->label(null,'fr')['dateAdd'] === "Date d'ajout");
        assert($cells->label('%:')['dateAdd'] === 'Date added:');

        // description

        // groupSetPriority
        assert($cells->groupSetPriority()[5] instanceof Orm\Cells);

        // form
        assert(count($cells->form()) === 9);
        assert(is_string($cells->form(true)));
        assert($cells->form()['active'] === "<input data-pattern='[0-9]' maxlength='1' name='active' type='text' value='2'/>");

        // formPlaceholder
        assert(strlen($cells->formPlaceholder()['id']) === 93);
        assert(is_string($cells->formPlaceholder(true)));

        // formComplex

        // formWrap
        assert(count($cells->formWrap(null)) === 9);
        assert(count($cells->formWrap('br')) === 9);
        assert(strlen(current($cells->formWrap('br','%:'))) === 136);
        assert(is_string($cells->formWrap('br',null,true)));

        // formPlaceholderWrap
        assert(strlen($cells->formPlaceholderWrap('br')['active']) === 171);
        assert(strlen($cells->formPlaceholderWrap('br')['id']) === 152);
        assert(is_string($cells->formPlaceholderWrap('br',null,true)));

        // formComplexWrap

        // segment
        assert($cells->segment('[name_%lang%] [active] + [id]') === 'bla 2 + 1');
        assert($cells->segment('[name_%lang%] [active] + [id]',true) === 'bla 2 + 1');

        // htmlStr
        assert($cells->htmlStr("<div class='%name%'>%label%: %value%</div>")['id'] === "<div class='id'>Id: 1</div>");
        assert(is_string($cells->htmlStr("<div class='%name%'>%label%: %value%</div>",true)));

        // writeFile

        // keyClassExtends
        assert(count($cells::keyClassExtends()) === 2);

        // getOverloadKeyPrepend

        // mapObj
        assert($cells->pair('form')['name_en'] === "<input data-required='1' maxlength='100' name='name_en' type='text' value='bla'/>");
        assert($cells->pairStr('label') === 'IdEnglish nameActiveEmailDateAdded byDate addedModified byLast modification');
        assert($cells->filter(['colKind'=>'char'])->isCount(2));
        assert(count($cells->group('colKind')) === 2);
        $sort = $clone->sortBy('name');
        assert($sort->first()->name() === 'active');
        assert($sort !== $clone);
        assert($clone->sortDefault() === $clone);

        // root
        assert(is_a($cells->classFqcn(),Orm\Cells::class,true));
        assert($cells->classNamespace() === "Quid\Core");
        assert($cells->className() === 'Cells');

        // readOnly
        assert($clone->empty()->isEmpty());
        assert($clone->add($cells)->count() === $cells->count());
        assert($clone->unset($cells)->isEmpty());

        // cleanup
        $cells->index(0)->row()->unlink();
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>