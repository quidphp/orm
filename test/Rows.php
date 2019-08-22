<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// rows
class Rows extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormRows";
		assert($db->truncate($table) instanceof \PDOStatement);
		assert($db->inserts($table,['id','active','name','dateAdd','userAdd','dateModify','userModify'],[1,1,'james',10,2,12,2],[2,1,'james2',20,3,22,3],[3,1,'james2',30,4,32,4]) === [1,2,3]);
		$tb = $db->table($table);
		$tb->rowsLoad();

		// construct
		$rows = $tb->rowsNew();
		$rows->add(...$tb->rows()->toArray());
		assert($rows->count() === 3);
		assert($rows !== $tb->rows());
		assert($tb->rows() instanceof Orm\Rows);
		assert($rows instanceof Orm\Rows);
		$a = $rows[1];
		$b = $rows[2];
		$c = $rows[3];
		assert($rows->unset($a,$b,3,2)->isEmpty());
		assert($rows->add(...$tb->rows()->toArray()));
		assert($rows->unset($c)->count() === 2);
		foreach ($rows as $key => $value) { };
		$rows[] = $c;

		// toString

		// onPrepareKey
		assert($rows->get(1) instanceof Orm\Row);
		assert($rows->get(2) instanceof Orm\Row);
		assert($rows->get($a) instanceof Orm\Row);
		assert($rows->get('bla') === null);
		assert($rows->get($a['id']) === $a);
		assert(!$rows->in($a['id']));
		assert($rows->get(['id'=>2])->primary() === 2);
		assert($rows->get(['id'=>1,'name'=>'james'])->primary() === 1);
		assert($rows->get(['id'=>2,'name'=>'james']) === null);

		// onPrepareReturns
		$gets = $rows->gets(1,2);
		assert($gets !== $rows);
		assert($gets->isCount(2));
		assert($rows->gets(1,2,1000,'OK')->isCount(2));
		assert(!$rows->in(1,2,$a));
		assert($rows->exists(1,2,$a));
		assert(!$rows->in(1,2,$a,1000));
		assert(!$rows->exists(1,2,$a,1000));
		assert($rows->index(0) === $a);
		assert($rows->indexes(-1)->get(3) instanceof Orm\Row);
		assert($rows->slice(2,3)->count() === 2);
		assert($rows->sliceIndex(0,3)->count() === 3);

		// cast
		assert($rows->_cast() === [1,2,3]);

		// offsetSet

		// isTable
		assert(!$rows->isTable('session'));
		assert($rows->isTable($table));
		assert($rows->isTable($tb));

		// hasChanged
		assert(!$rows->hasChanged());
		$rows[1]['name'] = 2;
		$rows[2]['name'] = 2;
		assert($rows->hasChanged());
		assert($rows->alive());

		// hasCell
		assert($rows->hasCell('active'));
		assert(!$rows->hasCell('activez'));

		// checkCell
		assert($rows->checkCell('active'));

		// primaries
		assert($rows->primaries() === [1,2,3]);

		// ids
		assert($rows->ids() === [1,2,3]);

		// db
		assert($rows->db() instanceof Orm\Db);

		// table
		assert($rows->table() instanceof Orm\Table);

		// addMode

		// add
		assert($rows->empty()->count() === 0);
		assert($rows->refresh() instanceof Orm\Rows);
		assert($rows->db() === null);
		assert($rows->table() === null);
		assert($rows->add($c)->get(3) instanceof Orm\Row);
		assert($rows->first()->primary() === 3);

		// addSort
		assert($rows->addSort($b,$a)->count() === 3);
		assert($rows->first()->primary() === 1);

		// getRefresh
		$uni = count($tb->db()->history()->keyValue());
		assert($rows->getRefresh(1) instanceof Orm\Row);
		assert(count($tb->db()->history()->keyValue()) === ($uni+1));

		// getsRefresh
		assert($rows->getsRefresh(1,2)->isCount(2));
		assert(count($tb->db()->history()->keyValue()) === ($uni+2));

		// label
		assert($db['user']->rows(1,2,3,4)->isCount(4));
		assert($db['user']->rows()->label()[1] === 'User #1');

		// description
		assert($db['user']->rows()->description()[1] === null);

		// changed
		$rows[1]['name'] = 2;
		$rows[2]['name'] = 2;
		assert($rows->changed()->count() === 2);

		// cell
		$rows[1]['name'] = 'james';
		$rows[2]['name'] = 'james2';
		$rows->get(3)->unlink();
		$rows->clean();
		assert(count($rows->cell('id')) === 2);

		// cellNotEmpty
		assert(count($rows->cellNotEmpty('name')) === 2);
		$rows[2]['name'] = '';
		assert(count($rows->cellNotEmpty('name')) === 1);

		// cellFirstNotEmpty
		assert($rows->cellFirstNotEmpty('name')->value() === 'james');
		$rows[2]['name'] = 'james2';

		// setCell
		$rows->setCell('name','james3');
		assert($rows->cellValue('name') === [1=>'james3',2=>'james3']);
		assert($rows->hasChanged());

		// resetCell
		$rows->resetCell('name');
		assert($rows->cellValue('name') === [1=>'james',2=>'james2']);
		assert(!$rows->hasChanged());

		// unsetCell
		$rows->unsetCell('name');
		assert($rows->cellValue('name') === [1=>null,2=>null]);
		assert($rows->hasChanged());
		$rows->setCell('name','james3');

		// cellValue
		assert($rows->cellValue('id') === [1=>1,2=>2]);
		assert($rows->cellValue('name') === [1=>'james3',2=>'james3']);
		assert(is_int($rows->cellValue('dateAdd',false)[1]));
		assert(is_string($rows->cellValue('dateAdd',true)[1]));

		// htmlStr
		assert(count($rows->htmlStr('name','%label%-%value%')) === 2);
		assert($rows->htmlStr('name','%label%-%value%',true) === "Name-james3Name-james3");

		// segment
		assert($rows->segment('[name] [id]') === [1=>'james3 1',2=>'james3 2']);

		// keyValue
		assert($rows->keyValue('id','name') === [1=>'james3',2=>'james3']);
		assert($rows->keyValue(0,3) === [1=>2,2=>3]);
		assert($rows->keyValue('id',['james','active']) === [1=>1,2=>1]);

		// where
		$rows->add($tb[3]);
		assert($rows->where([[$tb->col('name'),true]])->isCount(3));
		assert($rows->where([['name','empty']])->isEmpty());
		assert($rows->where([['name',false]])->isEmpty());
		assert($rows->where([['name','notEmpty']])->isCount(3));
		$tb[3]['name']->set(null);
		assert($rows->where([['name',null]])->isCount(1));
		assert($rows->where([['name','notNull']])->isCount(2));
		assert($rows->where(['name'=>'james3'])->isCount(2));
		assert($rows->where(['name'=>'james3','id'=>1])->isCount(1));
		assert($rows->where([['id','!',2]])->isCount(2));
		assert($rows->where([['id','>',1],'name'=>'james3'])->isCount(1));
		assert($rows->where([['id','>',1],'name'=>'james'])->isEmpty());
		$tb[3]['name']->set('james4');
		assert($rows->where([['dateAdd','>=',30]])->isCount(1));

		// order
		assert($rows->order(['userModify'=>'desc'])->first()->id() === 3);
		assert($rows->order(['userModify'=>true])->first()->id() === 1);
		assert($rows->order(['name'=>'asc','id'=>'desc'])->keys() === [2,1,3]);
		assert(count($rows->group('cellValue','name')) === 2);

		// limit
		assert($rows->limit(3)->first()->id() === 1);
		assert($rows->limit(1,2)->first()->id() === 2);
		assert($rows->limit(1,2)->isCount(2));
		$rows->unset(3);

		// alive
		assert($rows->alive());

		// refresh
		$uni = count($tb->db()->history()->keyValue());
		assert($rows->refresh() instanceof Orm\Rows);
		assert(count($tb->db()->history()->keyValue()) === ($uni+1));

		// clean
		assert($rows->clean()->count() === 2);

		// unlink
		$one = $tb[1];
		assert($rows->unlink()->count() === 0);
		assert(!$one->isLinked());
		$rows->add(...$tb->rowsLoad()->toArray());
		$rows->setCell('name','james3');
		assert($rows->unset($tb[3])->isCount(2));
		assert($rows->isCount(2));
		assert($tb->rows()->isCount(3));

		// update
		assert($rows->update() === [1=>1,2=>1]);

		// updateValid

		// updateChanged

		// updateChangedIncluded
		$rows->setCell('name','james4');
		assert($rows->updateChangedIncluded() === [1=>1,2=>1]);

		// updateChangedIncludedValid

		// updateAll
		assert(count($rows->updateAll()) === 2);

		// updateRowChanged
		$rows->setCell('name','james5');
		assert($rows->updateRowChanged() === [1=>1,2=>1]);
		$rows[1]['name'] = 'james6';
		assert($rows->updateRowChanged() === [1=>1]);
		assert($rows->updateRowChanged() === []);

		// delete
		assert($rows->delete() === 2);
		assert($rows->count() === 0);
		assert($rows->delete() === null);

		// writeFile

		// readOnly
		assert($tb->rows()->isReadOnly());
		assert(!$tb->rows()->clone()->isReadOnly());
		assert(!$rows->isReadOnly());
		assert($rows->add($tb->rows())->isCount(1));
		assert($rows->unset(3)->isEmpty());

		// cleanup
		$db->autoSave();
		assert($db->truncate($table) instanceof \PDOStatement);
		
		return true;
	}
}
?>