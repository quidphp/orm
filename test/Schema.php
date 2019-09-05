<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/test/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Orm;
use Quid\Base;

// schema
// class for testing Quid\Orm\Schema
class Schema extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = 'ormDb';

		// construct
		$schema = new Orm\Schema(null,$db);

		// tables
		assert(count($schema->tables()) === 25);

		// table
		assert(count($schema->table('user')) === 13);
		assert(count($schema->table('user',false)) === 13);
		assert(count($schema->table('session')) === 11);

		// col
		assert(count($schema->col($table,'name_en')) === 6);
		assert($schema->col($table,'name_en',false)['Field'] === 'name_en');

		// all
		assert(count($schema->all()) === 25);

		// dbAccess
		assert(!empty($schema->get('user/id')));
		assert(!empty($schema->get(['session','data'])));
		assert($schema->db() instanceof Orm\Db);

		return true;
	}
}
?>