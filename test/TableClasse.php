<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// tableClasse
class TableClasse extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormDb";
		$tb = $db[$table];
		$classe = $tb->classe();

		// table
		assert(is_a($classe->table(),Orm\Table::Class,true));

		// rows
		assert(is_a($classe->rows(),Orm\Rows::Class,true));

		// row
		assert(is_a($classe->row(),Orm\Row::Class,true));

		// col
		assert(is_a($classe->col($tb['id']),Orm\Col::Class,true));

		// setCol

		// cols
		assert(is_a($classe->cols(),Orm\Cols::Class,true));

		// cell
		assert(is_a($classe->cell($tb['id']),Orm\Cell::Class,true));

		// setCell

		// cells
		assert(is_a($classe->cells(),Orm\Cells::Class,true));
		
		return true;
	}
}
?>