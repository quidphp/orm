<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Base;

// _tableAccess
trait _tableAccess
{
	// trait
	use _dbAccess;
	
	
	// dynamique
	protected $table = null; // objet table

	
	// setLink
	// set la table et db à l'objet
	// envoie une exception si l'objet existe déjà
	// méthode protégé
	protected function setLink(Table $value,bool $checkLink=false):self 
	{
		$this->setDb($value->db());
		$this->table = $value->name();
		
		if($checkLink === true && $this->isLinked())
		static::throw('alreadyInstantiated');
		
		return $this;
	}
	
	
	// tableName
	// retourne la propriété protégé table
	public function tableName():string 
	{
		return $this->table;
	}
	
	
	// tables
	// retourne l'objet tables
	public function tables():Tables 
	{
		return $this->db()->tables();
	}
	
	
	// table
	// retourne l'objet table
	public function table():Table
	{
		return $this->db()->table($this->table);
	}
	
	
	// sameTable
	// retourne vrai si l'objet et celui fourni ont la même table
	public function sameTable($table):bool 
	{
		return ($this->db()->hasTable($table) && $this->table() === $this->db()->table($table))? true:false;
	}
}
?>