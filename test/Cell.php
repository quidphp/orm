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

// cell
// class for testing Quid\Orm\Cell
class Cell extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCell';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','date','name','dateAdd','userAdd','dateModify','userModify','integer','enum','set','user_ids'],[1,time(),'james',10,2,12,13,12,5,'2,3',[2,1]],[2,time(),'james2',10,11,12,13,12,5,'2,4','2,3']) === [1,2]);
        $tb = $db[$table];
        $row = $tb[1];

        // construct
        $cell = $row->cell('name');
        $active = $row->cell('active');
        $dateAdd = $row->cell('dateAdd');
        $userAdd = $row->cell('userAdd');
        $media = $row->cell('media');
        $date = $row->cell('date');
        $dateStart = $row->cell('dateStart');
        $dateEnd = $row->cell('dateEnd');

        // toString

        // invoke
        assert($cell(true) === 'james');
        assert($cell(2) === 'ja');
        assert($cell() === $cell);

        // onCommitted

        // cast
        assert($cell->set(true) === $cell);
        assert($cell->value() === '1');
        assert($cell->set(2) === $cell);
        assert($cell->_cast() === '2');

        // is
        assert($cell->is('numeric'));

        // isNot
        assert($cell->isNot('array'));

        // isEqual
        assert($cell->isEqual('2'));

        // isNotEqual
        assert($cell->isNotEqual(2));

        // isCompare
        assert($cell->isCompare('>',1));
        assert(!$cell->isCompare('>',2));
        assert($cell->isCompare('>=',2));

        // isEmpty
        assert(!$cell->isEmpty());

        // isNotEmpty
        assert($cell->isNotEmpty());

        // isNull
        assert(!$cell->isNull());

        // isNotNull
        assert($cell->isNotNull());

        // isPrimary
        assert(!$cell->isPrimary());

        // isRequired
        assert($cell->isRequired());

        // isStillRequired
        assert(!$cell->isStillRequired());
        assert($cell->set(' ') === $cell);
        assert($cell->isStillRequired());
        assert($cell->value() === ' ');
        $cell->set(2);

        // isVisible
        assert($cell->isVisible());
        assert($dateAdd->isVisible());
        $dateAdd->set(0);
        assert(!$dateAdd->isVisible());

        // isVisibleGeneral
        assert($cell->isVisibleGeneral());
        assert($dateAdd->isVisibleGeneral());

        // isEditable
        assert($cell->isEditable() === true);

        // attrPermissionRolesObject

        // tag
        assert($cell->tag() === 'inputText');
        assert($dateAdd->tag() === 'inputText');
        assert($dateAdd->tag(null,true) === 'div');
        assert($dateAdd->tag(['tag'=>'span'],true) === 'span');

        // isFormTag
        assert($cell->isFormTag() === true);
        assert($dateAdd->isFormTag() === true);
        assert(!$dateAdd->isFormTag(['tag'=>'div']));

        // rules
        assert($cell->rules() === ['required'=>'required','schemaValidate'=>['string','maxLength'=>100]]);
        assert($cell->rules(true)['schemaValidate'][0] === 'Must be a string');
        assert(count($date->rules()) === 2);
        assert(count($date->rules(false,true)) === 3);

        // compare
        $dateStart->set(1235);
        $dateEnd->set(1234);
        assert($dateStart->compare(true) === ['Must be equal or smaller than End date']);
        $dateStart->set(1234);
        assert($dateStart->compare(true));
        $dateEnd->set(null);
        assert($dateStart->compare(true));
        $dateStart->set(null);
        $dateEnd->set(1234);
        assert($dateStart->compare(true));

        // required
        assert($cell->required());
        assert($cell->set(0) === $cell);
        assert($cell->required() === true);
        assert($cell->set('') === $cell);
        assert($cell->required() === 'required');

        // unique
        assert($cell->unique());

        // editable
        assert($cell->editable() === true);

        // validate
        assert($cell->validate() === true);

        // completeValidation
        assert($cell->completeValidation() === ['required']);
        assert($cell->set(2) === $cell);
        assert($cell->completeValidation() === true);

        // isWhere
        assert($cell->isWhere(['='=>'2']));
        assert($cell->isWhere([true,'notNull']));

        // isLinked
        assert($cell->isLinked());

        // alive
        assert($cell->alive());

        // sameRow
        assert($cell->sameRow($tb[1]->cell('id')));
        assert(!$cell->sameRow($tb[2]->cell('id')));

        // isIncluded
        assert($cell->isIncluded(false) === false);
        assert($cell->isIncluded());

        // hasChanged
        assert($cell->reset());
        assert(!$cell->hasChanged());
        $cell->set(2);
        assert($cell->hasChanged());

        // setCol

        // setRow

        // name
        assert($cell->name() === 'name');

        // col
        assert($cell->col() instanceof Orm\Col);

        // priority
        assert($cell->priority() === 30);

        // setPriority
        assert($cell->setPriority() === 5);

        // attrRef
        assert(count($cell->attr()) >= 60);
        assert($cell->getAttr('length') === 100);
        assert($cell->isAttrNotEmpty('length'));
        assert(!$cell->isAttrNotEmpty('lengthz'));

        // rowPrimary
        assert($cell->rowPrimary() === 1);

        // id
        assert($cell->id() === 1);

        // row
        assert($cell->row() instanceof Orm\Row);

        // label
        assert($cell->label() === 'Name');
        assert($cell->label('%:') === 'Name:');
        assert($cell->label(1) === 'N');
        assert($cell->label(5) === 'Name');

        // description

        // form
        assert(strlen($cell->form()) === 76);
        assert($dateAdd->set(1234235434) === $dateAdd);
        assert($dateAdd->form() === "<input inputmode='numeric' maxlength='11' name='dateAdd' type='text' value='1234235434'/>");
        assert($cell->form() === "<input data-required='1' maxlength='100' name='name' type='text' value='2'/>");
        assert($cell->form(['data-required'=>false]) === "<input data-required='0' maxlength='100' name='name' type='text' value='2'/>");
        assert($cell->form(['data'=>['required'=>false]]) === "<input data-required='0' maxlength='100' name='name' type='text' value='2'/>");

        // formHidden
        assert($cell->formHidden() === "<input data-required='1' name='name' type='hidden' value='2'/>");
        assert($cell->formHidden(['data-required'=>null]) === "<input name='name' type='hidden' value='2'/>");

        // formPlaceholder
        assert(strlen($cell->formPlaceholder('placeholder')) === 102);
        assert(strlen($cell->formPlaceholder()) === 95);

        // formWrap
        assert(strlen($cell->formWrap('br')) === 141);
        assert(strlen($cell->formWrap('br',3)) === 140);

        // formPlaceholderWrap
        assert(strlen($cell->formPlaceholderWrap('br',null,'placeholder')) === 167);
        assert(strlen($cell->formPlaceholderWrap('br')) === 160);

        // hasFormLabelId
        assert($cell->hasFormLabelId());
        assert($dateAdd->hasFormLabelId());
        assert(!$dateAdd->hasFormLabelId(null,true));

        // com

        // htmlExcerpt
        $cell->set('<b>okkkkk</b>');
        assert($cell->htmlExcerpt(4) === "o<span class='excerptSuffix'>...</span>");
        assert($cell->htmlExcerpt(2) === 'ok');

        // htmlOutput
        assert($cell->htmlOutput() === '&lt;b&gt;okkkkk&lt;/b&gt;');

        // htmlUnicode
        assert($cell->htmlUnicode() === '&lt;b&gt;okkkkk&lt;/b&gt;');

        // value
        $cell->set(2);
        assert($cell->value() === '2');
        $cell->set(true);
        assert($cell->value() === '1');
        $cell->set(2);

        // valueInitial
        assert($cell->valueInitial() === 'james');

        // get
        assert($cell->get() === '2abcde');
        assert($cell->set('ok')->value() === 'ok');
        assert($cell->value() === 'ok');
        assert($cell->get() === 'okabcde');
        assert($cell->get() === 'okabcde');
        assert($cell->set('okabcde')->value() === 'ok');
        $row = $tb->insert(['date'=>time(),'name'=>3,'user_id'=>1,'enum'=>3],['strict'=>true]);
        assert($row['name']->value() === '3');
        assert($row['name']->get() === '3abcde');

        // export
        assert($cell->export() === ['okabcde']);

        // exportCommon

        // exportOne
        assert($cell->exportOne() === 'okabcde');

        // pair
        assert($cell->pair() instanceof Orm\Cell);
        assert($cell->pair(1) === 'o');
        assert($cell->pair(true) === 'ok');
        assert($cell->pair(false) === 'ok');
        assert($userAdd->pair(true) instanceof Orm\Row);

        // set
        assert($cell->set(3) instanceof Orm\Cell);
        assert($date->set('03-03-2017',['preValidate'=>true]) === $date);

        // setInitial
        $cell->setInitial('ok');

        // setSelf

        // reset
        assert($cell->valueInitial() === 'ok');
        $cell->reset();
        assert($cell->value() === 'ok');

        // unset
        assert($cell->unset() instanceof Orm\Cell);
        assert($cell->value() === '');
        assert($cell->isEmpty());
        assert(!$cell->isNull());
        $cell->set(0);
        assert($cell->isEmpty());
        assert(!$cell->isNull());
        assert($cell->unset() instanceof Orm\Cell);
        assert($cell->valueInitial() === 'ok');
        $active->set(9);
        $row->update();
        $active->set(3);
        assert($active->unset()->value() === 2);

        // isUnique
        assert($active->isUnique() === false);
        assert($cell->isUnique() === true);

        // duplicate
        assert($active->duplicate() === [2,3]);
        assert($cell->duplicate() === []);

        // update

        // delete

        // refresh
        $row = $cell->row();
        $db->update($tb,['name'=>'ok'],$row);
        assert($cell->value() === '');
        assert($cell->refresh()->value() === 'ok');
        $db->update($tb,['name'=>''],$row);
        assert($cell->alive());
        assert($cell->refresh()->value() === '');
        assert($cell->set('ok') === $cell);

        // teardown

        // initReplaceMode

        // getOverloadKeyPrepend

        // route

        // colCell

        // tableAccess
        $active = $tb[2]['dateAdd'];
        assert(!$tb[2]->unlink()->hasDb());
        assert(!$active->hasDb());
        assert($cell->checkLink() === $cell);
        assert($cell->sameTable($tb));
        assert($cell->sameTable($tb[1]->cell('id')));

        // cleanup
        assert($row->unlink());
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>