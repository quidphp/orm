<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;

// classe
class Classe extends Main\Map
{
	// trait
	use Main\_option;
	
	
	// config
	public static $config = array(
		'option'=>array(
			'default'=>array( // classe par défaut
				'table'=>Table::class,
				'col'=>Col::class,
				'row'=>Row::class,
				'cell'=>Cell::class,
				'cols'=>Cols::class,
				'rows'=>Rows::class,
				'cells'=>Cells::class),
			'colGroup'=>array(), // classe pour colonne selon le group
			'colAttr'=>array()), // classe pour colonne selon un attribut
		'extendersKeys'=>array('table','rows','row','cols','col','cells','cell'), // défini les clés à garder de l'extenders
	);
	
	
	// map
	protected static $allow = array('jsonSerialize','serialize','clone'); // méthodes permises
	
	
	// dynamique
	protected $extenders = null; // propriété pour conserver l'objet extenders
	
	
	// construct
	// construit l'objet classe
	public function __construct(Main\Extenders $extenders,?array $option=null) 
	{
		$this->option($option);
		$this->setExtenders($extenders);
		
		return;
	}
	
	
	// setExtenders
	// garde une copie de l'objet extenders
	// méthode protégé
	protected function setExtenders(Main\Extenders $extenders):self 
	{
		$keys = static::extendersKeys();
		$this->extenders = $extenders->filter(function($value,$key) use($keys) {
			return (in_array($key,$keys,true))? true:false;
		});;
		
		return $this;
	}
	
	
	// extenders
	// retourne l'objet extenders
	public function extenders():Main\Extenders 
	{
		return $this->extenders;
	}
	
	
	// tableClasse
	// retourne un tableau avec toutes les classes pour une table (sauf col et cell)
	// gère la cache
	public function tableClasse($table,bool $cache=true):TableClasse 
	{
		$return = null;
		
		if(!is_string($table) && !$table instanceof Table)
		static::throw('invalidTable');
		
		if($cache === true)
		$return = $this->get($table);
		
		if(empty($return))
		{			
			$array = array();
			
			foreach (static::extendersKeys() as $key) 
			{
				if(!in_array($key,array('col','cell'),true))
				$array[$key] = $this->find($key,$table);
			}
			
			$return = TableClasse::newOverload($array);
			
			if($cache === true)
			{
				if($table instanceof Table)
				$table = $table->name();
				
				$data =& $this->arr();
				$data[$table] = $return;
			}
		}
		
		return $return;
	}
	
	
	// tableClasseCol
	// retourne la classe d'une colonne et ajoute dans l'objet tableClasse
	// gère la cache
	public function tableClasseCol(Table $table,$col,?array $attr=null,bool $cache=true):string
	{
		$return = null;
		
		if(!is_string($col) && !$col instanceof Col)
		static::throw('invalidCol');
		
		$tableClasse = $this->tableClasse($table);
		
		if($cache === true)
		$return = $tableClasse->col($col);
		
		if(empty($return))
		{
			$attr = (array) $attr;
			$return = $this->find('col',$table,$col,$attr);
			
			if($cache === true)
			$tableClasse->setCol($col,$return);
		}
		
		return $return;
	}
	
	
	// tableClasseCell
	// retourne la classe d'une cellule et ajoute dans l'objet tableClasse
	// gère la cache
	public function tableClasseCell(Table $table,Col $col,bool $cache=true):string
	{
		$return = null;
		$tableClasse = $this->tableClasse($table);
		
		if($cache === true)
		$return = $tableClasse->cell($col);
		
		if(empty($return))
		{
			$return = $this->find('cell',$table,$col);
			
			if($cache === true)
			$tableClasse->setCell($col,$return);
		}
		
		return $return;
	}
	
	
	// default
	// doit retourner une string, sinon une exception sera lancé
	public function default(string $type):string 
	{
		return $this->getOption(array('default',$type));
	}
	
	
	// find
	// doit toujours retourner quelque chose, sinon une exception est envoyé
	// retourne la classe à utiliser à partir d'un type et d'arguments
	// méthode protégé
	protected function find(string $type,$table,...$args):string 
	{
		$return = null;
		$default = $this->default($type);
		
		if(!in_array($type,static::extendersKeys(),true))
		static::throw('invalidType',$type);
		
		if($type === 'col')
		$return = $this->colBefore($table,...$args);
		
		else
		{
			if($type === 'cell')
			$return = $this->cell(...$args);
			
			else
			{
				if($table instanceof Table)
				$table = $table->name();
				
				if(!is_string($table))
				static::throw('invalidTable');
				
				$ucTable = ucfirst($table);
				$extenders = $this->extenders();
				$extender = $extenders->get($type);
				
				$return = $extender->get($ucTable);
			}
		}
		
		if(empty($return))
		{
			if($type === 'col')
			$return = $this->colAfter($table,...$args);
			
			if(empty($return))
			$return = $default;
		}
		
		$return = $return::getOverloadClass();
		
		if(!is_a($return,$default,true))
		static::throw($table,$return,'mustBeOrExtend',$default);
		
		return $return;
	}
	
	
	// colBefore
	// retourne la classe a utilisé pour une colonne à partir des config de la table
	// cette classe a priorité sur tout le reste pour trouver la colonne
	// méthode protégé
	protected function colBefore(Table $table,string $col,array $attr):?string 
	{
		return $this->colFromAttr($table->colAttr($col),false);
	}
	
	
	// colAfter
	// retourne le classe a utilisé pour une colonne à partir de son nom ou ses attributs
	// cette méthode est seulement appelé si le loop ne retourne rien, donc cette classe est retourné plutôt que default
	// le group de la colonne est considéré à la fin
	// une exception peut être envoyé
	// méthode protégé
	protected function colAfter(Table $table,string $col,array $attr):?string 
	{
		$return = null;
		$patternType = ColSchema::patternType($col);
		
		if(!empty($patternType))
		{
			$db = $table->db();
			$return = $this->colFromAttr($db->colAttr($patternType));
		}
		
		if(empty($return))
		$return = $this->colFromAttr($table->colAttr($col),true);
		
		if(empty($return) && array_key_exists('group',$attr) && is_string($attr['group']))
		$return = $this->getOption(array('colGroup',$attr['group']));
		
		return $return;
	}
	
	
	// colFromAttr
	// méthode statique qui permet de parse une valeur attribut de colonne en provenance de db ou table
	// retourne null ou le nom de la classe à utiliser
	// méthode protégé
	protected function colFromAttr($value,bool $defaultEnum=false):?string 
	{
		$return = null;
		
		if(!empty($value) && is_array($value))
		{
			if(array_key_exists('class',$value) && is_string($value['class']))
			$return = $value['class'];
			
			elseif(array_key_exists('media',$value))
			$return = $this->getOption(array('colAttr','media'));
			
			elseif(array_key_exists('relation',$value))
			{
				if(array_key_exists('set',$value) && $value['set'] === true)
				$return = $this->getOption(array('colAttr','set'));
				
				elseif((array_key_exists('enum',$value) && $value['enum'] === true) || $defaultEnum === true)
				$return = $this->getOption(array('colAttr','enum'));
			}
		}

		return $return;
	}
	
	
	// cell
	// retourne la classe à utiliser pour une cellule
	// est toujours dans la colonne
	// méthode protégé
	protected function cell(Col $col):?string 
	{
		return $col->cell();
	}
	
	
	// extendersKeys
	// retourne les clés de l'extender pour la db
	public static function extendersKeys():array 
	{
		return static::$config['extendersKeys'];
	}
}
?>