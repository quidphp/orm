<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;
use Quid\Base;

// history
class History extends Main\Map
{
	// config
	public static $config = array();
	
	
	// map
	protected static $is = 'array'; // les valeurs doivent passés ce test de validation ou exception
	protected static $allow = array('push','empty'); // méthodes permises
	
	
	// invoke
	// retourne un index de l'historique
	public function __invoke(...$args) 
	{
		return $this->index(...$args);
	}
	
	
	// toString
	// affiche le dump du tableau des requêtes uni
	public function __toString():string
	{
		return Base\Debug::varGet($this->keyValue());
	}
	
	
	// cast
	// cast de l'historique, retourne le count
	public function _cast()
	{
		return $this->count();
	}
	
	
	// add
	// ajoute un statement dans l'historique db
	public function add(array $value,\PDOStatement $statement):self
	{
		if(!empty($value['type']))
		{
			if(array_key_exists('cast',$value))
			unset($value['cast']);
			
			if(Db::isOutput($value['type'],'rowCount'))
			$value['row'] = $statement->rowCount();
			
			if(Db::isOutput($value['type'],'columnCount'))
			{
				$value['column'] = $statement->columnCount();
				$value['cell'] = $value['row'] * $value['column'];
			}
			
			$this->push($value);
		}
		
		return $this;
	}
	
	
	// all
	// retourne des donnés de l'historique
	// possibilité de filtrer par type
	public function all(?string $type=null,bool $reverse=false):array
	{
		$return = array();
		$data = $this->arr();
		
		if(is_string($type))
		{
			foreach ($data as $value) 
			{
				if(is_array($value) && !empty($value['type']) && $value['type'] === $type)
				$return[] = $value;
			}
		}
		
		else
		$return = $data;
		
		if($reverse === true)
		$return = array_reverse($return,false);
		
		return $return;
	}
	
	
	// keyValue
	// retourne un tableau unidimensionnel d'historique
	// emule la requête si nécessaire
	public function keyValue(?string $type=null,bool $reverse=false):array
	{
		$return = array();
		
		foreach ($this->all($type,$reverse) as $value)
		{
			if(is_array($value) && array_key_exists('sql',$value))
			{
				$sql = $value['sql'];
				if(!empty($value['prepare']))
				$sql = Base\Sql::emulate($sql,$value['prepare']);
				
				$return[] = $sql;
			}
		}
		
		return $return;
	}
	

	// typeCount
	// retourne les données counts de l'historique
	// le type est requis
	public function typeCount(string $type):array
	{
		$return = array();
		
		foreach ($this->all($type) as $value) 
		{
			if(is_array($value) && !empty($value))
			{
				if(!array_key_exists('query',$return))
				$return['query'] = 1;
				else
				$return['query']++;
				
				foreach (array('row','column','cell') as $v) 
				{
					if(array_key_exists($v,$value) && is_int($value[$v]))
					{
						if(!array_key_exists($v,$return))
						$return[$v] = 0;
						
						$return[$v] += $value[$v];
					}
				}
			}
		}
		
		return $return;
	}
	
	
	// typeIndex
	// retourne un index de l'historique filtre par type ou null si non existant
	// par défaut index est le dernier, plus récent
	public function typeIndex(string $type,int $index=-1):?array 
	{
		return Base\Arr::index($index,$this->all($type));
	}
	

	// total
	// retourne les données counts de l'historique pour tous les types
	public function total():array
	{
		$return = array();
		
		foreach (Base\Sql::getQueryTypes() as $type) 
		{
			$array = $this->typeCount($type);
			
			if(!empty($array))
			$return[$type] = $array;
		}
		
		return $return;
	}
}
?>