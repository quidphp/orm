<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Main;
use Quid\Orm;

// col
// class for testing Quid\Orm\Col
class Col extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCol';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name','password','email','dateAdd','userAdd','dateModify','userModify'],[1,1,'james','james','james@gmail.com',10,11,12,13],[2,2,'james2','james2','james2@gmail.com',20,21,22,23]) === [1,2]);
        $tb = $db[$table];
        $col = $tb['name'];
        $id = $tb->cols()->get('id');
        $active = $tb->cols()->get('active');
        $def = $tb->cols()->get('def');
        $dateAdd = $tb->cols()->get('dateAdd');
        $dateModify = $tb['dateModify'];
        $email = $tb->cols()->get('email');
        $password = $tb->cols()->get('password');
        $date = $tb['date'];
        $userId = $tb['user_id'];
        $userIds = $tb['user_ids'];
        $myRelation = $tb['myRelation'];
        $other = $tb['other'];
        $array = $tb['myRelation'];
        $range = $tb['relationRange'];
        $lang = $tb['relationLang'];
        $multi = $tb['multi'];
        $check = $tb['check'];
        $media = $tb['media'];
        $dateStart = $tb['dateStart'];
        $dateEnd = $tb['dateEnd'];
        $float = $tb['float'];

        // construct

        // toString

        // invoke
        assert($other() === $other);
        assert($other('name') === 'other');
        assert($other('direction',false) === 'ASC');
        assert($other('direction',true) === 'asc');

        // onMakeAttr

        // onCheckAttr

        // onInsert

        // onCommit

        // onUpdate

        // onSet

        // onGet

        // onDuplicate

        // onExport

        // onCellInit

        // onCellSet

        // onDelete

        // onCommitted

        // cast
        assert($col->_cast() === 'name');

        // isLinked
        assert($col->isLinked());

        // alive
        assert($col->alive());

        // isIgnored
        assert(!$dateAdd->isIgnored());

        // isPrimary
        assert(!$col->isPrimary());
        assert($id->isPrimary());

        // isKindInt
        assert(!$col->isKindInt());
        assert($dateAdd->isKindInt());

        // isKindChar
        assert($col->isKindChar());
        assert(!$dateAdd->isKindChar());

        // isKindText
        assert(!$col->isKindText());

        // acceptsNull
        assert($col->acceptsNull());
        assert(!$id->acceptsNull());

        // hasAttrInclude
        assert(!$col->hasAttrInclude('insert'));

        // isIncluded
        assert($col->isIncluded('insert',false) === false);
        assert($col->isIncluded('insert'));

        // isRequired
        assert($col->isRequired() === true);

        // isStillRequired
        assert($col->isStillRequired(null));

        // shouldRemoveWhiteSpace
        assert($col->shouldRemoveWhiteSpace('required') === true);
        assert($col->shouldRemoveWhiteSpace('castz') === false);
        assert(is_bool($col->shouldRemoveWhiteSpace('cast')));

        // isExportable
        assert($col->isExportable());

        // hasCompare
        assert(!$col->hasCompare());
        assert($dateStart->hasCompare());

        // showDetailsMaxLength

        // isDate
        assert(!$email->isDate());
        assert($dateAdd->isDate());

        // isRelation
        assert(!$email->isRelation());
        assert($userId->isRelation());
        assert($userIds->isRelation());
        assert($myRelation->isRelation());
        assert($other->isRelation());
        assert($multi->isRelation());
        assert($range->isRelation());
        assert(!$dateAdd->isRelation());

        // canRelation
        assert($dateAdd->canRelation());

        // isEnum
        assert(!$email->isEnum());
        assert($userId->isEnum());
        assert(!$userIds->isEnum());
        assert($myRelation->isEnum());
        assert($other->isEnum());
        assert($array->isEnum());
        assert($range->isEnum());
        assert($lang->isEnum());
        assert(!$check->isEnum());

        // isSet
        assert(!$email->isSet());
        assert(!$userId->isSet());
        assert($userIds->isSet());
        assert(!$myRelation->isSet());
        assert(!$other->isSet());
        assert($multi->isSet());
        assert($check->isSet());

        // isMedia
        assert(!$email->isMedia());

        // isGeneral
        assert($col->isGeneral());

        // generalExcerptMin
        assert($col->generalExcerptMin() === 100);

        // valueExcerpt
        assert($col->valueExcerpt('test') === 'test');

        // hasDefault
        assert($col->hasDefault());
        assert($email->hasDefault());
        assert($def->hasDefault());
        assert(!$dateModify->hasDefault());

        // hasNullDefault
        assert($col->hasNullDefault());
        assert(!$email->hasNullDefault());
        assert(!$def->hasNullDefault());
        assert(!$dateModify->hasNullDefault());

        // hasNullPlaceholder
        assert($col->hasNullPlaceholder());
        assert(!$id->hasNullPlaceholder());

        // hasNotEmptyDefault
        assert(!$col->hasNotEmptyDefault());
        assert($def->hasNotEmptyDefault());
        assert($email->hasNotEmptyDefault());
        assert(!$dateModify->hasNotEmptyDefault());

        // hasOnInsert
        assert(!$col->hasOnInsert());
        assert($dateAdd->hasOnInsert());

        // hasOnUpdate
        assert(!$dateAdd->hasOnUpdate());
        assert($dateModify->hasOnUpdate());

        // attrPermissionRolesObject
        assert($col->attrPermissionRolesObject() instanceof Main\Roles);

        // value
        assert($col->value(true) === null);
        assert($dateAdd->value(1234) === 1234);

        // get
        assert($col->get(123) === 123);

        // export

        // exportOne

        // placeholder
        assert($col->placeholder(null) === 'Name');
        assert($col->placeholder('abcde') === 'abcde');

        // isSearchable
        assert($col->isSearchable());
        assert(!$email->isSearchable());

        // isSearchTermValid
        assert(!$col->isSearchTermValid('2'));
        assert($id->isSearchTermValid('2'));

        // searchMinLength
        assert($col->searchMinLength() === 3);
        assert($id->searchMinLength() === 1);

        // isOrderable
        assert($col->isOrderable());

        // isFilterable
        assert(is_bool($col->isFilterable()));

        // isFilterEmptyNotEmpty
        assert(is_bool($col->isFilterEmptyNotEmpty()));

        // isVisible
        assert($col->isVisible(null));
        assert(!$dateAdd->isVisible(null));
        assert($dateAdd->isVisible(12345));
        assert(!$col->isVisible(null,['tag'=>'inputHidden']));

        // isVisibleGeneral
        assert($col->isVisibleGeneral());
        assert($dateAdd->isVisibleGeneral());

        // isVisibleCommon

        // roleValidateCommon

        // isEditable
        assert($id->isEditable() === false);
        assert($col->isEditable() === true);

        // filterMethod
        assert($col->filterMethod() === 'or|=');
        assert($email->filterMethod() === 'or|=');
        assert($dateAdd->filterMethod() === 'or|day');
        assert($multi->filterMethod() === 'or|findInSet');

        // direction
        assert($col->direction() === 'ASC');
        assert($col->direction(true) === 'asc');

        // tag
        assert($col->tag() === 'inputText');
        assert($dateAdd->tag() === 'inputText');
        assert($range->tag() === 'inputText');
        assert($lang->tag() === 'inputText');
        assert($multi->tag() === 'textarea');
        assert($check->tag() === 'textarea');
        assert($col->tag(['tag'=>'div']) === 'div');
        assert($col->tag(['tag'=>'textarea']) === 'textarea');
        assert($col->tag(['tag'=>'inputEmail']) === 'inputEmail');

        // isPlainTag
        assert($col->isPlainTag(['tag'=>'div']));
        assert(!$email->isPlainTag());

        // isFormTag
        assert($email->isFormTag());
        assert($col->isFormTag());
        assert($dateAdd->isFormTag());
        assert($range->isFormTag());
        assert($lang->isFormTag());
        assert($multi->isFormTag());
        assert($check->isFormTag());
        assert(!$col->isFormTag(['tag'=>'div']));
        assert($col->isFormTag(['tag'=>'textarea']));

        // pair
        assert($lang->pair() === $lang);
        assert($lang->pair('name') === 'relationLang');

        // rulePreValidate
        assert($col->rulePreValidate() === []);
        assert($date->rulePreValidate() === ['dateToDay']);
        assert($date->rulePreValidate(true) === ['Must be a valid date (MM-DD-YYYY)']);

        // ruleValidate
        assert($col->ruleValidate() === ['string','maxLength'=>100]);
        assert($col->ruleValidate(true)[0] === 'Must be a string');

        // rulePreValidateCommon

        // attrCompare
        assert($col->attrCompare() === []);
        assert($dateStart->attrCompare()['<='] instanceof Orm\Col);

        // ruleCompare
        assert($col->ruleCompare() === []);
        assert($dateStart->ruleCompare() === ['<='=>$dateEnd]);
        assert($dateStart->ruleCompare(true)[0] === 'Must be equal or smaller than End date');

        // ruleRequired
        assert($col->ruleRequired() === 'required');
        assert($col->ruleRequired(true) === 'Cannot be empty');

        // ruleUnique
        assert($col->ruleUnique() === 'unique');
        assert($col->ruleUnique(true) === 'Must be unique');
        assert($email->ruleUnique(true) === null);

        // ruleEditable
        assert($col->ruleEditable() === null);
        assert($col->ruleEditable(true) === null);
        assert($range->ruleEditable() === 'editable');
        assert($range->ruleEditable(true) === 'Cannot be modified');

        // ruleMaxLength
        assert($col->ruleMaxLength() === ['maxLength'=>100]);
        assert($col->ruleMaxLength(true) === 'Length must be at maximum 100 characters');

        // rules
        assert($col->rules() === ['required'=>'required','unique'=>'unique','validate'=>['string','maxLength'=>100]]);
        assert($col->rules(true)['unique'] === 'Must be unique');
        assert(count($col->rules(true)) === 3);
        assert(count($date->rules()) === 2);
        assert(count($date->rules(false,true)) === 3);
        assert($dateStart->rules(true)['compare'][0] === 'Must be equal or smaller than End date');
        assert($dateStart->rules(false)['compare']['<='] === $dateEnd);
        assert($range->rules()['editable'] === 'editable');

        // rulePattern
        assert($col->rulePattern() === null);
        assert($password->rulePattern() === 'password');
        assert($col->rulePattern(true) === null);
        assert(($password->rulePattern(true)) === 'Must be a password with a letter, a number and at least 5 characters long.');
        assert($date->rulePattern() === null);

        // rulesWrapClosure

        // ruleLangOption
        assert($col->ruleLangOption() === ['path'=>['tables','ormCol','name']]);

        // pattern
        assert($col->pattern() === null);
        assert(!empty($password->pattern()));
        assert($email->pattern() === "^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$");

        // preValidatePrepare
        assert($col->preValidatePrepare('ok') === 'ok');

        // preValidate
        assert($col->preValidate('ok'));
        assert($date->preValidate(null));
        assert($date->preValidate('ok') === ['dateToDay']);
        assert($date->preValidate('02-02-2017'));

        // validate
        assert($col->validate('OK'));
        assert($col->validate('OKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOK') === [['maxLength'=>100]]);
        assert($col->validate(123) === ['string']);
        assert($col->validate(123,true) === ['Must be a string']);
        assert($col->validate('OKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOKsaddsadsaadsOK',true) === ['Length must be at maximum 100 characters']);

        // triggerValidate

        // compare
        assert($dateStart->compare(1234));
        assert($dateStart->compare(1234,['dateEnd'=>1245]));
        assert($dateStart->compare(1234,['dateEnd'=>1233])['<='] === $dateEnd);
        assert($dateStart->compare(1234,['dateEnd'=>1233],true) === ['Must be equal or smaller than End date']);
        assert($dateStart->compare(1234,['dateEnd'=>1234],true));

        // isUnique
        assert(!$col->isUnique('james',2));
        assert($col->isUnique('james',1));

        // unique
        assert($col->unique('james',2,true) === 'Must be unique (#1)');
        assert($col->unique('james',1) === true);

        // duplicate
        assert($col->duplicate('james') === [1]);
        assert($col->duplicate('james',[1,2]) === []);
        assert($col->duplicate('james',[2]) === [1]);
        assert($col->duplicate('james',1) === []);
        assert($col->duplicate(null) === []);

        // distinctMethod

        // distinct
        assert($col->distinct() === ['james','james2']);

        // distinctCount
        assert($col->distinctCount() === 2);

        // replace
        assert($email->replace('gmail.com','hotmail.com') === 2);
        assert($email->replace('gmail.com','hotmail.com') === 0);

        // required
        assert($col->required(2) === true);
        assert($col->required(0) === true);
        assert($col->required('') === 'required');

        // completeValidation
        assert($col->completeValidation(2) === ['string']);
        assert($col->completeValidation(0) === ['string']);
        assert($col->completeValidation('') === ['required']);
        assert($col->completeValidation('',[],true) === ['Cannot be empty']);

        // makeCompleteValidation

        // setName

        // name
        assert($col->name() === 'name');

        // nameStripPattern
        assert($col->nameStripPattern() === null);

        // langCode
        assert($col->langCode() === null);

        // makeAttr

        // attrCallback

        // attrParseCallable
        assert($col->attrParseCallable('onGet') === null);
        assert($dateAdd->attrParseCallable('onGet')['args'][0] === 'long');

        // priority
        assert($col->priority() === 40);

        // setPriority
        assert($col->setPriority() === 5);

        // type
        assert($col->type() === 'varchar');
        assert($id->type() === 'int');
        assert($dateAdd->type() === 'int');

        // kind
        assert($col->kind() === 'char');
        assert($id->kind() === 'int');
        assert($dateAdd->kind() === 'int');

        // group
        assert($col->group() === 'char');
        assert($dateAdd->group() === 'date');
        assert($userIds->group() === 'relation');
        assert($media->group() === 'media');
        assert($id->group() === 'primary');

        // length
        assert($dateAdd->length() === 11);
        assert($col->length() === 100);

        // unsigned
        assert($col->unsigned() === null);
        assert($dateAdd->unsigned());
        assert($dateModify->unsigned() === false);

        // shouldBeUnique
        assert($col->shouldBeUnique());
        assert(!$dateAdd->shouldBeUnique());

        // default
        assert($col->default() === null);
        assert($col->default() === null);
        assert($email->default() === 'default@def.james');
        assert($dateModify->default() === 0);

        // kindDefault
        assert($col->kindDefault() === '');
        assert($id->kindDefault() === 0);
        assert($dateAdd->kindDefault() === 0);

        // autoCast
        assert($id->autoCast('3') === 3);
        assert($id->autoCast('000111') === 111);
        assert($id->autoCast('000,111') === 0);
        assert($id->autoCast(0.2) === 0);
        assert($id->autoCast(0) === 0);
        assert($id->autoCast('') === 0);
        assert($id->autoCast(null) === 0);
        assert($id->autoCast([1,2,3]) === '[1,2,3]');
        assert($dateAdd->autoCast('1,3') === 1);
        assert($dateAdd->autoCast(1.3) === 1);
        assert($dateAdd->autoCast('') === null);
        assert($col->autoCast([1,2,3]) === '[1,2,3]');
        assert($col->autoCast(0) === '0');
        assert($col->autoCast('') === null);
        assert($float->autoCast(2) === 2.0);
        assert($float->autoCast('3,1') === 3.1);
        assert($id->autoCast(true) === 1);
        assert($id->autoCast(false) === 0);
        assert($dateAdd->autoCast(true) === 1);
        assert($dateAdd->autoCast(false) === 0);
        assert($col->autoCast(true) === '1');
        assert($col->autoCast(false) === '0');
        assert($float->autoCast(true) === (float) 1);
        assert($float->autoCast(false) === (float) 0);

        // insertCallable
        assert(is_int($dateAdd->insertCallable(null,[],[])));
        assert($col->insertCallable(null,[],[]) === null);

        // updateCallable

        // insert
        assert(is_int($dateAdd->insert(null,[])));

        // patternType
        assert($col->patternType() === null);
        assert($userId->patternType() === 'enum');

        // label
        assert($dateAdd->label() === 'Date added');
        assert($dateAdd->label(null,'fr') === "Date d'ajout");
        assert($id->label() === 'Id');
        assert($active->label() === 'Active');
        assert($dateAdd->label('%:') === 'Date added:');
        assert($dateAdd->label(2) === 'Da');

        // description
        assert($dateAdd->description() === 'Perfect');
        assert($dateAdd->description(null,null,'fr') === 'Parfait');
        assert($email->description() === 'Ma description');
        assert($email->description('%:') === 'Ma description:');
        assert($dateAdd->description(2,null,'fr') === 'Pa');

        // details
        assert(count($col->details()) === 3);
        assert($email->details() === ['Cannot be empty','Length must be at maximum 100 characters']);
        assert($email->details(false) === ['required',['maxLength'=>100]]);

        // makeDetails
        assert($col->makeDetails() === []);

        // collation
        assert($email->collation() === 'utf8mb4_general_ci');
        assert($dateAdd->collation() === null);

        // panel
        assert($email->panel() === 'default');

        // formAttr
        assert($email->formAttr() === ['data-required'=>true,'data-pattern'=>'email','maxlength'=>100,'name'=>'email']);
        assert($email->formAttr(['data-required'=>true,'data-pattern'=>'email','maxlength'=>100,'name'=>'email']) === ['data-required'=>true,'data-pattern'=>'^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$','maxlength'=>100,'name'=>'email']);
        assert($email->formAttr(['placeholder'=>'myplace','data-required'=>'ok'])['data-required'] === 'ok');
        assert(count($email->formAttr(['placeholder'=>'myplace','data-required'=>'ok'])) === 5);
        assert(count($email->formAttr(['placeholder'=>'myplace','ok','ok2','name'=>'james2','data-required'=>'ok'])) === 7);

        // form
        assert($col->form('val',['name'=>'test','data-required'=>null]) === "<input name='test' maxlength='100' type='text' value='val'/>");
        assert($col->form() === "<input data-required='1' maxlength='100' name='name' type='text'/>");
        assert($dateAdd->form() === "<input maxlength='11' name='dateAdd' type='text'/>");
        assert($email->form() === "<input data-required='1' data-pattern='^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$' maxlength='100' name='email' type='text' value='default@def.james'/>");
        assert($email->form('') === "<input data-required='1' data-pattern='^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$' maxlength='100' name='email' type='text' value=''/>");
        assert($email->form(false) === "<input data-required='1' data-pattern='^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$' maxlength='100' name='email' type='text' value='0'/>");
        assert($email->form('test@gmail.com') === "<input data-required='1' data-pattern='^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$' maxlength='100' name='email' type='text' value='test@gmail.com'/>");
        assert($password->form() === "<input data-required='1' data-pattern='^(?=.{5,30})(?=.*\d)(?=.*[A-z]).*' maxlength='100' name='password' type='password' value='lol2'/>");
        assert(strlen($col->form([1=>'no',2=>'yes'],['tag'=>'checkbox'])) === 249);
        assert($col->form([1=>'no',2=>'yes'],['name'=>'ok','tag'=>'select','data-required'=>null]) === "<select name='ok'><option value='1'>no</option><option value='2'>yes</option></select>");
        assert(strlen($col->form([1=>'no',2=>'yes'],['data-required'=>null,'name'=>'ok','tag'=>'radio'])) === 191);

        // formHidden
        assert($email->formHidden() === "<input data-required='1' name='email' type='hidden'/>");
        assert($email->formHidden(true,['data-required'=>null]) === "<input name='email' type='hidden' value='default@def.james'/>");

        // formPlaceholder
        assert($email->formPlaceholder(true,'myPlaceholder') === "<input placeholder='myPlaceholder' data-required='1' data-pattern='^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,4})+$' maxlength='100' name='email' type='text' value='default@def.james'/>");
        assert(strlen($email->formPlaceholder(true)) === 194);

        // emptyPlaceholder
        assert($dateAdd->emptyPlaceholder(null) === 'NULL');
        assert($dateAdd->emptyPlaceholder('') === '-');
        assert($dateAdd->emptyPlaceholder('bla') === null);

        // formWrap
        assert(strlen($email->formWrap('br',null,true,['name'=>'notEmail'])) === 251);
        assert(strlen($email->formWrap('br',null,true,['type'=>'text','name'=>'notEmail'])) === 251);
        assert(strlen($email->formWrap('br',null,true,['tag'=>'textarea','name'=>'notEmail'])) === 227);
        assert(strlen($email->formWrap('table')) === 279);
        assert(strlen($email->formWrap('table',null,'james@ok')) === 270);
        assert(strlen($email->formWrap('table','%:','james@ok')) === 271);
        assert(strlen($email->formWrap('table',4,'james@ok')) === 269);

        // formPlaceholderWrap
        assert(strlen($email->formPlaceholderWrap('br',null,'james@ok','placehol')) === 256);
        assert(strlen($email->formPlaceholderWrap('br','%:','james@ok','placehol')) === 257);
        assert(strlen($email->formPlaceholderWrap('br')) === 262);
        assert(strlen($email->formPlaceholderWrap('br',3)) === 260);

        // makeFormWrap

        // hasFormLabelId
        assert($col->hasFormLabelId());
        assert($dateAdd->hasFormLabelId());
        assert(!$dateAdd->hasFormLabelId(null,true));

        // com

        // setCommittedCallback

        // htmlExcerpt
        assert($email->htmlExcerpt(10) === "default<span class='excerptSuffix'>...</span>");
        assert($email->htmlExcerpt(10,'<b>ok</b>') === 'ok');

        // htmlOutput
        assert($email->htmlOutput() === 'default@def.james');
        assert($email->htmlOutput('<b>ok</b>') === '&lt;b&gt;ok&lt;/b&gt;');

        // htmlUnicode
        assert($email->htmlUnicode() === 'default@def.james');

        // htmlReplace
        assert(count($email->htmlReplace(true)) === 6);

        // htmlStr
        assert($email->htmlStr(true,"<div class='%name%'>%label%: %value%</div>") === "<div class='email'>Email: default@def.james</div>");

        // relation
        assert($lang->relation() instanceof Orm\ColRelation);
        assert($date->relation() instanceof Orm\ColRelation);

        // isRelationSearchRequired
        assert(is_bool($lang->isRelationSearchRequired()));

        // primaries
        assert($id->primaries(1) === [1]);

        // cell
        assert($email->cell() === null);

        // alter

        // drop

        // isFilterEmptyNotEmptyValue
        assert($email::isFilterEmptyNotEmptyValue('00'));
        assert(!$email::isFilterEmptyNotEmptyValue('bla'));

        // initReplaceMode

        // getOverloadKeyPrepend
        assert($col::getOverloadKeyPrepend() === null);

        // attr
        assert(count($col->attr()) >= 61);
        assert($col->isAttrNotEmpty('kind'));
        assert(!$col->isAttrNotEmpty('kindz'));

        // cleanup
        assert($db->truncate($table) instanceof \PDOStatement);

        return true;
    }
}
?>