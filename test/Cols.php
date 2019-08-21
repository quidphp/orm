<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// cols
class Cols extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormCols";
		assert($db->truncate($table) instanceof \PDOStatement);
		assert($db->inserts($table,array('id','active','name_en','dateAdd','userAdd','dateModify','userModify'),array(1,1,'james',10,11,12,13),array(2,2,'james2',20,21,22,23)) === array(1,2));
		$tb = $db[$table];
		$tb2 = $db['ormDb'];
		$cols = $tb->cols();
		foreach ($cols as $key => $value) { };
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
		assert($cols->default() === array('name_en'=>'LOL','active'=>1,'email'=>'default@def.james','date'=>null,'userAdd'=>null,'dateAdd'=>null,'userModify'=>null,'dateModify'=>null));

		// value
		assert($cols->value(array('id'=>4,'dateAdd'=>123123213,'james'=>'OK'),true) === array('id'=>4,'dateAdd'=>'November 25, 1973 19:53:33'));

		// isVisible
		assert(!$cols->isVisible());

		// isHidden
		assert(!$cols->isHidden());

		// isRequired
		assert($cols->isRequired() === array('name_en'=>'LOL','email'=>'default@def.james','date'=>null));
		assert($cols->isRequired(array('email'=>null)) === array('name_en'=>'LOL','email'=>null,'date'=>null));

		// isStillRequired
		assert($cols->isStillRequired() === array('date'=>null));
		assert($cols->isStillRequired(array('email'=>null)) === array('email'=>null,'date'=>null));

		// isStillRequiredEmpty
		assert(!$cols->isStillRequiredEmpty());

		// rules
		assert(count($cols->rules()) === 9);
		assert($cols->rules(false,false) !== $cols->rules(false,true));

		// preValidatePrepare
		assert($cols->preValidatePrepare(array('email'=>'ok')) === array('email'=>'ok'));

		// preValidate
		assert($cols->preValidate() === array());
		assert($cols->preValidate(array('date'=>'as')) === array('date'=>array('dateToDay')));
		assert($cols->preValidate(array('date'=>'02-02-2017')) === array());

		// validate
		assert($cols->validate(array('name_en'=>NULL,'dateAdd'=>1234)) === array());
		assert($cols->validate(array('name_en'=>123)) === array('name_en'=>array('string')));
		assert($cols->validate(array('name_en'=>123),true)['name_en'] === array('Must be a string'));

		// required
		assert($cols->required(array('date'=>2,'email'=>'','name_en'=>'OK')) === array('email'=>'required'));
		assert($cols->required(array('name_en'=>''))['name_en'] === 'required');
		assert($cols->required(array('name_en'=>''),true)['name_en'] === 'Cannot be empty');

		// unique
		assert($cols->unique(array('email'=>'bla')) === array());

		// compare
		assert($cols->compare(array('email'=>'bla')) === array());

		// completeValidation
		assert($cols->completeValidation(array('email'=>''))['email'] === array('required','email'));
		assert($cols->completeValidation(array('email'=>'asd'))['email'] === array('email'));
		assert(count($cols->completeValidation(array('email'=>''),true,false)) === 9);

		// triggerValidate

		// included
		assert($cols->included()->isCount(5));

		// insert
		assert($cols->insert('name_en',2) === '2');

		// inserts
		assert(is_int($cols->inserts(array('test'=>2))['dateAdd']));
		assert(count($cols->inserts(array('name_en'=>2),array('required'=>false))) === 3);
		assert(count($cols->inserts(array('name_en'=>2))) === 5);
		assert(count($cols->inserts(array('name_en'=>2),array('default'=>true))) === 8);

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

		// formComplex

		// formWrap
		assert(count($cols->formWrap('br')) === 9);
		assert(strlen(current($cols->formWrap('br','%:'))) === 136);
		assert(is_string($cols->formWrap('br',null,true)));

		// formPlaceholderWrap
		assert(count($cols->formPlaceholderWrap(null)) === 9);
		assert(strlen($cols->formPlaceholderWrap('br')['id']) === 152);
		assert(is_string($cols->formPlaceholderWrap('br',null,true)));

		// formComplexWrap
		assert($cols->formComplexWrap('table')['userAdd'] === "<table><tr><td><label>Added by</label></td><td><div class='nothing'>Nothing</div></td></tr></table>");

		// htmlStr
		assert($cols->htmlStr("<div class='%name%'>%label%: %value%</div>")['name_en'] === "<div class='name_en'>English name: LOL</div>");
		assert(is_string($cols->htmlStr("<div class='%name%'>%label%: %value%</div>",true)));

		// general
		assert($cols->general()->isCount(6));

		// orderable
		assert($cols->orderable()->isCount(9));

		// filterable
		assert($cols->filterable()->isCount(6));

		// searchable
		assert(count($cols->searchable()) !== count($cols));

		// writeFile

		// keyClassExtends
		assert(count($cols::keyClassExtends()) === 2);

		// mapObj
		assert($cols->pair('isRequired')['id'] === false);
		assert($cols->filter(array('kind'=>'char'))->isCount(2));
		assert($cols->filter(array('value'=>1),true)->isCount(1));
		assert(!$cols->filter(array('value'=>1),false)->isCount(1));
		assert(count($cols->group('kind')) === 2);
		assert(count($cols->group('panel')) === 2);
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