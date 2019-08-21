<?php
declare(strict_types=1);
namespace Quid\Orm\Test;
use Quid\Orm;
use Quid\Base;

// classe
class Classe extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// prepare
		$db = Orm\Db::inst();
		$table = "ormDb";
		$tb = $db[$table];
		$classe = $db->classe();
		$tb->cols();
		
		// construct
		
		// setExtenders
		
		// extenders
		
		// tableClasse
		\assert($classe->tableClasse('ormDb')->count() === 11);
		
		// tableClasseCol
		\assert(\is_a($classe->tableClasseCol($tb,$tb['id']),Orm\Col::class,true));
		
		// tableClasseCell
		\assert(\is_a($classe->tableClasseCell($tb,$tb['id']),Orm\Cell::class,true));
		
		// default
		\assert(\is_a($classe->default('table'),Orm\Table::class,true));
		\assert(\is_a($classe->default('row'),Orm\Row::class,true));
		\assert(\is_a($classe->default('col'),Orm\Col::class,true));
		\assert(\is_a($classe->default('cell'),Orm\Cell::class,true));
		\assert(\is_a($classe->default('rows'),Orm\Rows::class,true));
		\assert(\is_a($classe->default('cols'),Orm\Cols::class,true));
		\assert(\is_a($classe->default('cells'),Orm\Cells::class,true));

		// find
		
		// colBefore

		// colAfter

		// colFromAttr

		// cell
		
		// extendersKeys
		
		return true;
	}
}
?>