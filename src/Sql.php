<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Base;

// sql
class Sql extends PdoSql
{
	// config
	public static $config = [];
	
	
	// setOutput
	// change le output de la requête
	// si le output est de row, change what pour *
	public function setOutput($output=true):parent 
	{
		parent::setOutput($output);
		$db = $this->db();
		
		if($db::isRowOutput($output))
		$this->set('what','*');
		
		return $this;
	}
	
	
	// getTableObject
	// retourne l'objet table lié à l'objet sql si existant
	public function getTableObject():?Table 
	{
		$return = null;
		$db = $this->db();
		$table = $this->getTable();
		
		if(!empty($table) && $db->hasTable($table))
		$return = $db->table($table);
		
		return $return;
	}
	
	
	// checkTableObject
	// retourne l'objet table lié à l'objet sql
	// envoie une exception si non existant
	public function checkTableObject():Table 
	{
		$return = $this->getTableObject();
		
		if(empty($return))
		static::throw();
		
		return $return;
	}
	

	// checkMake
	// retourne le tableau make, si problème ou retour vide lance une exception
	// méthode protégé
	protected function checkMake($output,?array $option=null):?array
	{
		$return = null;
		$arr = $this->arr();
		$required = Base\Sql::getQueryRequired($this->getType());
		$db = $this->db();
		
		if(!empty($required) && !Base\Arr::keysExists($required,$arr))
		{
			$strip = Base\Arr::valuesStrip(array_keys($arr),$required);
			static::throw('missingRequiredClause',$strip);
		}
		
		elseif($db::isRowOutput($output) && !in_array('*',(array) $arr['what'] ?? null))
		static::throw('rowOutput','whatOnlyAccepts','*');
		
		elseif(empty($arr))
		static::throw('queryEmpty');
		
		else
		{
			$make = $this->make($output,$option);
			if(empty($make))
			static::throw('sqlReturnEmpty');
			
			else
			$return = $make;
		}
		
		return $return;
	}
	
	
	// row
	// vide l'objet, change le type pour select avec output row
	// argument est table
	public function row($value=null):self
	{
		$this->setType('select');
		$this->setOutput('row');
		
		if(!empty($value))
		$this->table($value);
		
		return $this;
	}
	
	
	// rows
	// vide l'objet, change le type pour select avec output rows
	// argument est table
	public function rows($value=null):self 
	{
		$this->setType('select');
		$this->setOutput('rows');
		
		if(!empty($value))
		$this->table($value);
		
		return $this;
	}
	
	
	// triggerTableCount
	// retourne le nombre de ligne dans la table, peu importe le reste de la requête
	// possible de mettre le retour en cache, via la classe core/table
	public function triggerTableCount(bool $cache=false):?int 
	{
		return $this->checkTableObject()->rowsCount(true,$cache);
	}
	
	
	// triggerRow
	// trigge l'objet sql et retourne un objet rows
	public function triggerRow():Row
	{
		return $this->set('what','*')->trigger('row');
	}
	
	
	// triggerRows
	// trigge l'objet sql et retourne un objet rows
	public function triggerRows():Rows
	{
		return $this->set('what','*')->trigger('rows');
	}
}

// config
Sql::__config();
?>