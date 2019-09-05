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

// tableClasse
// class for testing Quid\Orm\TableClasse
class TableClasse extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = 'ormDb';
		$tb = $db[$table];
		$classe = $tb->classe();

		// table
		assert(is_a($classe->table(),Orm\Table::class,true));

		// rows
		assert(is_a($classe->rows(),Orm\Rows::class,true));

		// row
		assert(is_a($classe->row(),Orm\Row::class,true));

		// col
		assert(is_a($classe->col($tb['id']),Orm\Col::class,true));

		// setCol

		// cols
		assert(is_a($classe->cols(),Orm\Cols::class,true));

		// cell
		assert(is_a($classe->cell($tb['id']),Orm\Cell::class,true));

		// setCell

		// cells
		assert(is_a($classe->cells(),Orm\Cells::class,true));

		return true;
	}
}
?>