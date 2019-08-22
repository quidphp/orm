<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// schema
class Schema extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();

		// construct
		$schema = new Orm\Schema(null,$db);

		// tables
		assert(count($schema->tables()) === 26);

		// table
		assert(count($schema->table('user')) === 13);
		assert(count($schema->table('user',false)) === 13);
		assert(count($schema->table('session')) === 11);

		// col
		assert(count($schema->col('page','name_fr')) === 6);
		assert($schema->col('page','name_fr',false)['Field'] === 'name_fr');

		// all
		assert(count($schema->all()) === 26);

		// dbAccess
		assert(!empty($schema->get('user/id')));
		assert(!empty($schema->get(array('session','data'))));
		assert($schema->db() instanceof Orm\Db);
		
		return true;
	}
}
?>