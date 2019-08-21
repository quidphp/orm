<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// cells
class Cells extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormCells";
		assert($db->truncate($table) instanceof \PDOStatement);
		assert($db->inserts($table,array('id','active','name_en','date','dateAdd','userAdd','dateModify','userModify'),array(1,1,'james',time(),10,11,12,13),array(2,2,'james2',time(),20,21,22,23)) === array(1,2));
		$tb = $db[$table];
		$row = $tb[1];
		$row2 = $tb[2];
		$cells = $row->cells();
		foreach ($cells as $key => $value) { };
		$clone = $cells->clone();

		// construct

		// toString

		// onPrepareKey
		assert($cells->get($tb->col('id')) === $cells['id']);
		assert($cells->get(0)->name() === 'id');
		assert($cells->get(array('LOL',1000,1))->name() === 'name_en');

		// onPrepareReturns
		assert($cells->gets('id','active','name_[lang]')->count() === 3);

		// cast
		assert(!empty($cells->_cast()));

		// offsetSet

		// isWhere
		assert($cells->isWhere(array(array('id',true),array('name_en',true),array('dateAdd','=',10))));
		assert(!$cells->isWhere(array(array('id',true),array('name_en',true),array('dateAdd','>',10))));

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
		assert($cells->preValidatePrepare(array('email'=>'ok')));

		// preValidate
		assert($cells->preValidate() === array());
		assert($cells->preValidate(array('date'=>'12-03-2017')) === array());
		assert($cells->preValidate(array('date'=>'')) === array());
		assert($cells->preValidate(array('date'=>null)) === array());
		assert($cells->preValidate(array('date'=>0))['date'] === array('dateToDay'));
		assert(count($cells->preValidate(array('date'=>0),true,false)) === 9);

		// validate
		assert($cells->validate(false,false)['name_en'] === true);
		$cells['email']->set('testtest.com');
		assert($cells->validate()['email'] === array('email'));
		assert($cells->validate(true,true)['email'] === array('Must be a valid email (x@x.com)'));

		// required
		assert($cells->required(false,false)['id'] === true);
		assert($cells->required(true,false)['id'] === true);

		// unique
		assert($cells->unique() === array());

		// compare
		assert($cells->compare() === array());

		// completeValidation
		assert($cells->completeValidation()['email'] === array('email'));
		$cells['email']->set('test@test.com');
		assert(empty($cells->completeValidation()['email']));
		$cells['email']->set(null);
		assert($cells->required(true)['email'] === 'Cannot be empty');
		assert($cells->completeValidation()['email'] === array('required'));
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
		assert($cells->sets(array('active'=>4))['active']->value() === 4);
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
		assert($cells->description()['id'] === 'Primary and unique key. Required');
		assert($cells->description('%:')['id'] === 'Primary and unique key. Required:');

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
		assert(strlen($cells->formComplex()['active']) === 175);

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
		assert(strlen($cells->formComplexWrap()['active']) === 196);

		// segment
		assert($cells->segment("[name_%lang%] [active] + [id]") === 'bla 2 + 1');
		assert($cells->segment("[name_%lang%] [active] + [id]",true) === 'bla 2 + 1');

		// htmlStr
		assert($cells->htmlStr("<div class='%name%'>%label%: %value%</div>")['id'] === "<div class='id'>Id: 1</div>");
		assert(is_string($cells->htmlStr("<div class='%name%'>%label%: %value%</div>",true)));

		// writeFile

		// keyClassExtends
		assert(count($cells::keyClassExtends()) === 2);

		// mapObj
		assert($cells->pair('form')['name_en'] === "<input data-required='1' maxlength='100' name='name_en' type='text' value='bla'/>");
		assert($cells->pairStr('label') === "IdEnglish nameActiveEmailDateAdded byDate addedModified byLast modification");
		assert($cells->filter(array('colKind'=>'char'))->isCount(2));
		assert(count($cells->group('colKind')) === 2);
		$sort = $clone->sortBy('name');
		assert($sort->first()->name() === 'active');
		assert($sort !== $clone);
		assert($clone->sortDefault() === $clone);

		// root
		assert(is_a($cells->classFqcn(),Orm\Cells::class,true));
		assert($cells->classNamespace() === "Quid\Core");
		assert($cells->classRoot() === 'Quid');
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