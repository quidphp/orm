<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;

// relation
abstract class Relation extends Main\ArrMap
{
	// trait
	use _tableAccess;
	
	
	// config
	public static $config = [];
	
	
	// set
	// set pas permis
	public function set():void
	{
		static::throw('notAllowed');
		
		return;
	}
	
	
	// unset
	// unset pas permis
	public function unset():void
	{
		static::throw('notAllowed');
		
		return;
	}
}
?>