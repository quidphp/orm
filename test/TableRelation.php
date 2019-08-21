<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// tableRelation
class TableRelation extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormTable";
		$table2 = 'page';
		assert($db->truncate($table) instanceof \PDOStatement);
		assert($db->truncate($table2) instanceof \PDOStatement);
		assert($db->inserts($table,array('id','active','name_en','dateAdd','userAdd','dateModify','userModify'),array(1,1,'james',10,11,12,13),array(2,2,'james2',20,21,22,23)) === array(1,2));
		assert($db->inserts($table2,array('id','active','name_en','content_en'),array(1,1,'test','ok'),array(2,2,'test2','ok2')));
		$tb = $db[$table];
		$tb2 = $db[$table2];
		$insert = $tb->insert(array('date'=>time(),'name_fr'=>'nomFr'));
		$insert2 = $tb->insert(array('date'=>time(),'name_en'=>'LOL2','name_fr'=>'nomFr'));
		$user = $db['user']->relation();
		$session = $db['session']->relation();
		$rel = $tb->relation();
		$rel2 = $tb2->relation();

		// construct
		assert($rel instanceof Orm\TableRelation);

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
		assert($user->size(false) === 4);
		assert($rel2->size() === 2);

		// tableAccess
		assert($rel->db() instanceof Orm\Db);
		assert($rel->table() === $tb);
		assert($rel2->table() === $tb2);

		// get
		assert($rel->get(2) === 'December 31, 1969 19:00:20 james2 _ 2');
		assert($rel2->get(1) === 'test');

		// gets
		assert(array_keys($rel->gets(array(1,3,2))) === array(1,3,2));
		assert(count($rel->gets(array(3,2,1))) === 3);
		assert(count($rel->gets(array(1,2,3))) === 3);
		assert($user->gets(array(3,1)) === array(3=>'editor (#3)',1=>'nobody (#1)'));
		assert($user->gets(array(1,3)) === array(1=>'nobody (#1)',3=>'editor (#3)'));
		assert($rel2->gets(array(1,2)) === array(1=>'test',2=>'test2'));

		// all
		assert(array_keys($rel->all()) === array(2,1,3,4));
		assert(count($rel->all()) === 4);
		assert($user->all(false) === array(4=>'inactive (#4)',3=>'editor (#3)',2=>'admin (#2)',1=>'nobody (#1)'));
		assert($user->all() === array(4=>'inactive (#4)',3=>'editor (#3)',2=>'admin (#2)',1=>'nobody (#1)'));
		assert($user->all(false,array('limit'=>2)) === array(4=>'inactive (#4)',3=>'editor (#3)'));
		assert($user->count() === 2);
		assert($rel2->all(false) === array(1=>'test',2=>'test2'));

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
		assert($user->search('nob',array('limit'=>1)) === array(1=>'nobody (#1)'));
		assert($user->search('adm min') === array(2=>'admin (#2)'));
		assert($user->search('adm + min',array('searchSeparator'=>'+')) === array(2=>'admin (#2)'));
		assert($user->search('well') === array());
		assert($rel2->search('test') === array(1=>'test',2=>'test2'));
		assert($rel2->search('test2') === array(2=>'test2'));

		// defaultOrderCode
		assert($user->defaultOrderCode() === 2);

		// getOrder
		assert($user->getOrder() === array('id'=>'desc'));
		assert($user->getOrder(array('james'=>'asc')) === array('james'=>'asc'));
		assert($user->getOrder(1) === array('id'=>'asc'));
		assert($user->getOrder(2) === array('id'=>'desc'));
		assert($user->getOrder(3) === array('username'=>'asc'));
		assert($user->getOrder(4) === array('username'=>'desc'));

		// allowOrderingByValue
		assert($user->allowedOrdering() === array('key'=>true,'value'=>true));
		assert($rel->allowedOrdering() === array('key'=>true,'value'=>true));

		// getOrderFieldOutput
		assert($user->getOrderFieldOutput() === 'username');
		assert($rel->getOrderFieldOutput() === 'dateAdd');

		// makeOutput

		// output

		// outputAdd

		// outputMethod

		// attr
		assert($rel->attr() === array('what'=>array('id','name_en','dateAdd'),'appendPrimary'=>true,'onGet'=>true,'output'=>array('[dateAdd] [name_en] _ [id]'),'order'=>array('name_en'=>'desc'),'where'=>array()));
		assert($user->attr()['order'] === array('id'=>'desc'));
		assert($user->attr()['what'] === array('username','email','id'));
		assert($session->attr()['what'] === array('id'));

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