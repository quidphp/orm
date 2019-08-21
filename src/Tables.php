<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;
use Quid\Base;

// tables
class Tables extends Main\Map implements Main\Contract\Hierarchy
{
	// trait
	use Main\Map\_obj, Main\Map\_readOnly, Main\Map\_sort;
	
	
	// config
	public static $config = array();
	
	
	// map
	protected static $allow = array('add','unset','remove','empty','filter','sort','clone'); // méthodes permises
	protected static $sortDefault = 'priority'; // défini la méthode pour sort par défaut
	
	
	// construct
	// construit un nouvel objet tables
	public function __construct(...$values) 
	{
		if(!empty($values))
		$this->add(...$values);
		
		return;
	}
	
	
	// toString
	// retourne les noms de tables séparés par des virgules
	public function __toString():string
	{
		return implode(',',$this->names());
	}
	
	
	// onPrepareKey
	// prepare une clé pour les méthodes qui soumette une clé
	// support pour shortcut
	// possibilité de donner un nom de classe aussi
	protected function onPrepareKey($key) 
	{
		$return = null;
		
		if(is_string($key))
		{
			if(array_key_exists($key,$this->data))
			$return = $key;
			
			elseif(strpos($key,'\\') !== false && Base\Classe::extendOne(static::keyClassExtends(),$key))
			$return = $key::className(true);
			
			else
			{
				$key = Base\Sql::shortcut($key);
				if(array_key_exists($key,$this->data))
				$return = $key;
			}
		}
		
		elseif(is_int($key))
		$return = Base\Arr::index($key,$this->keys());
		
		elseif($key instanceof Table)
		$return = $key->name();
		
		elseif($key instanceof Row || $key instanceof Col || $key instanceof Cell)
		$return = $key->tableName();
		
		elseif(is_array($key))
		{
			foreach ($key as $k) 
			{
				$return = $this->onPrepareKey($k);
				
				if(!empty($return))
				break;
			}
		}
		
		else
		$return = parent::onPrepareKey($key);
		
		return $return;
	}
	
	
	// onPrepareReturns
	// prépare le retour pour gets
	protected function onPrepareReturns(array $array):self
	{
		$return = new static();

		foreach ($array as $value) 
		{
			if(!empty($value))
			$return->add($value);
		}
		
		return $return;
	}
	
	
	// cast
	// retourne la valeur cast
	public function _cast():array 
	{
		return $this->names();
	}
	
	
	// offsetSet
	// arrayAccess offsetSet est seulement permis si la clé est null []
	public function offsetSet($key,$value):void
	{
		if($key === null)
		$this->add($value);
		
		else
		static::throw("arrayAccess","onlyAllowedWithNullKey");
		
		return;
	}
	
	
	// hasChanged
	// retourne vrai si une des lignes des tables a changé
	public function hasChanged():bool 
	{
		$return = false;
		
		foreach ($this->arr() as $key => $value) 
		{
			if($value->rows()->hasChanged())
			{
				$return = true;
				break;
			}
		}
		
		return $return;
	}
	
	
	// names
	// retourne les noms des tables contenus dans l'objet
	public function names():array
	{
		return $this->keys();
	}
	
	
	// db
	// retourne la db du premier objet table
	public function db():?Db
	{
		$return = null;
		$first = $this->first();
		if(!empty($first))
		$return = $first->db();
		
		return $return;
	}
	
	
	// add
	// append une ou plusieurs tables dans l'objet
	// valeurs doivent être des objets table ou tables
	// deux objets identiques ne peuvent pas être ajoutés dans tables
	// des objets de différentes base de données ne peuvent être ajoutés dans tables
	public function add(...$values):self
	{
		$this->checkAllowed('add');
		$values = $this->prepareValues(...$values);
		$firstDb = $this->db();
		$data =& $this->arr();
		
		foreach ($values as $value)
		{
			if(!$value instanceof Table)
			static::throw('requiresTable');
			
			$db = $value->db();
			$firstDb = (empty($firstDb))? $db:$firstDb;
			
			if($firstDb !== $db)
			static::throw('tableMustBeFromSameDb');
			
			$name = $value->name();
			
			if(!array_key_exists($name,$data))
			$data[$name] = $value;
			
			else
			static::throw('tableAlreadyIn',$name);
		}
		
		return $this->checkAfter();
	}
	

	// label
	// retourne les noms de toutes les tables
	public function label($pattern=null,?string $lang=null,?array $option=null):array 
	{
		return $this->pair('label',$pattern,$lang,$option);
	}
	
	
	// description
	// retourne les descriptions de toutes les tables
	public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):array
	{
		return $this->pair('description',$pattern,$replace,$lang,$option);
	}
	
	
	// labels
	// retourne les labels de toutes les tables et de toutes les colonnes
	// pas de support pour pattern
	public function labels(?string $lang=null,?array $option=null):array
	{
		$return = array();
		
		foreach ($this->arr() as $key => $value) 
		{
			$return[$key]['table'] = $value->label(null,$lang,$option);
			$return[$key]['cols'] = $value->cols()->label(null,$lang,$option);
		}
		
		return $return;
	}
	
	
	// descriptions
	// retourne les labels de toutes les tables et de toutes les descriptions
	// pas de support pour pattern
	public function descriptions(?array $replace=null,?string $lang=null,?array $option=null):array 
	{
		$return = array();
		
		foreach ($this->arr() as $key => $value) 
		{
			$return[$key]['table'] = $value->description(null,$replace,$lang,$option);
			$return[$key]['cols'] = $value->cols()->description(null,$replace,$lang,$option);
		}
		
		return $return;
	}
	
	
	// hasPermission
	// permet de filtre les tables par une ou plusieurs permissions
	public function hasPermission(string ...$types):self 
	{
		return $this->filter(array('hasPermission'=>true),...$types);
	}
	
	
	// search
	// permet de chercher pour une valeur dans toutes les tables et toutes les colonnes cherchables
	// possible de changer la méthode en deuxième argument
	// retourne un tableau avec les ids et non pas un objet rows
	// ne retourne pas une table si aucun résultat trouvé
	public function search($search,?string $method=null,...$values):array 
	{
		$return = array();
		
		foreach ($this->searchable() as $key => $value) 
		{
			$result = $value->search($search,$method,...$values);
			
			if(!empty($result))
			$return[$key] = $result;
		}
		
		return $return;
	}
	
	
	// changed
	// retourne un objet rowsIndex avec toutes les lignes de table qui ont changé
	public function changed():RowsIndex 
	{
		$return = RowsIndex::newOverload();
		
		foreach ($this->arr() as $key => $value) 
		{
			$changed = $value->rows()->changed();
			
			if($changed->isNotEmpty())
			$return->add($changed);
		}
		
		return $return;
	}
	
	
	// total
	// retourne un tableau unidimensionnel sur le total des tables, colonnes, lignes et cellules chargés pour toutes les tables
	// si count est true, compte le nombre total de ligne et de colonne, pas seulement celle chargé
	// si count et cache sont true, retourne les counts en cache si existant
	public function total(bool $count=false,bool $cache=false):array
	{
		$return = array();
		
		$return['table'] = $this->count();
		$return['col'] = 0;
		$return['row'] = 0;
		$return['cell'] = 0;
		
		foreach ($this->arr() as $key => $value) 
		{
			$total = $value->total($count,$cache);
			$return = Base\Number::combine('+',$return,$total);
		}
		
		return $return;
	}
	
	
	// info
	// retourne un tableau multidimensionnel qui contient des informations sur le nombre de colonnes, lignes et cellules chargés pour toutes les tables
	public function info(bool $count=false,bool $cache=false):array
	{
		return $this->pair('info',$count,$cache);
	}
	
	
	// searchable
	// retourne un objet tables avec toutes les tables cherchables
	public function searchable(bool $cols=true):self
	{
		return $this->filter(array('isSearchable'=>true),$cols);
	}
	
	
	// searchMinLength
	// retourne la plus grande longueur de recherche minimale
	public function searchMinLength():int 
	{
		$return = 0;
		
		foreach ($this->arr() as $value) 
		{
			$minLength = $value->searchMinLength();
			
			if($minLength > $return)
			$return = $minLength;
		}

		return $return;
	}
	
	
	// isSearchTermValid
	// retourne vrai si le terme de la recherche est valide pour toutes les tables
	// valeur peut être scalar, un tableau à un ou plusieurs niveaux
	public function isSearchTermValid($value):bool
	{
		$return = false;
		
		foreach ($this->arr() as $table) 
		{
			$return = $table->isSearchTermValid($value);
			
			if($return === false)
			break;
		}
		
		return $return;
	}
	
	
	// keyParent
	// retourne un tableau unidimensionnel avec le nom de la table comme clé et le nom du parent comme valeur
	// si aucun parent, la valeur est null
	public function keyParent():array 
	{
		return $this->pair('parent');
	}
	
	
	// truncate
	// permet de lancer la requête sql truncate sur toutes les tables contenus dans l'objet
	public function truncate(?array $option=null):array 
	{
		return $this->pair('truncate',$option);
	}
	
	
	// hierarchy
	// retourne le tableau de la hiérarchie des éléments de l'objet
	// si existe est false, les parents de table non existants sont conservés
	public function hierarchy(bool $exists=true):array 
	{
		return Base\Arrs::hierarchy($this->keyParent(),$exists);
	}
	
	
	// childsRecursive
	// retourne un tableau avec tous les enfants de l'élément de façon récursive
	// si existe est false, les parents de table non existants sont conservés
	public function childsRecursive($value,bool $exists=true):?array 
	{
		$return = null;
		$hierarchy = $this->hierarchy($exists);
		
		if($value instanceof Table)
		$value = $value->name();
		
		if(is_string($value) && !empty($hierarchy))
		{
			$key = Base\Arrs::keyPath($value,$hierarchy);
			if($key !== null)
			$return = Base\Arrs::get($key,$hierarchy);
		}
		
		return $return;
	}
	
	
	// tops
	// retourne un objet des éléments n'ayant pas de parent
	// ne retourne pas les tables non existantes
	public function tops():self
	{
		$return = new static();
		
		foreach ($this->arr() as $k => $v) 
		{
			if($this->parent($v) === null)
			$return->add($v);
		}
		
		return $return;
	}
	
	
	// parent
	// retourne l'objet d'un élément parent ou null
	// ne retourne pas les tables non existantes
	public function parent($value):?Table
	{
		$return = null;
		$value = $this->get($value);
		
		if(!empty($value))
		{
			$parent = $value->parent();
			if(is_string($parent))
			$return = $this->get($parent);
		}
		
		return $return;
	}
	
	
	// top
	// retourne le plus haut parent d'un élément ou null
	// ne retourne pas les tables non existantes
	public function top($value):?Table
	{
		$return = null;
		$value = $this->get($value);
		
		if(!empty($value))
		{
			$target = $value;
			
			while ($parent = $this->parent($target)) 
			{
				$target = $parent;
			}
			
			if($target !== $value)
			$return = $target;
		}
		
		return $return;
	}
	
	
	// parents
	// retourne un objet avec tous les parents de l'élément
	// ne retourne pas les tables non existantes
	public function parents($value):self
	{
		$return = new static();
		$value = $this->get($value);
		
		if(!empty($value))
		{
			while ($parent = $this->parent($value)) 
			{
				$return->add($parent);
				$value = $parent;
			}
		}
		
		return $return;
	}
	
	
	// breadcrumb
	// retourne un objet inversé de tous les parents de l'élément et l'objet courant
	// ne retourne pas les tables non existantes
	public function breadcrumb($value):self
	{
		$return = $this->parents($value);
		
		if($return->isNotEmpty())
		$return = $return->reverse(true);
		
		$value = $this->get($value);
		if(!empty($value))
		$return->add($value);
		
		return $return;
	}
	
	
	// siblings
	// retourne un objet des éléments ayant le même parent que la valeur donnée
	// ne retourne pas les tables non existantes
	public function siblings($value):self
	{
		$return = new static();
		$value = $this->get($value);
		
		if(!empty($value))
		{
			$parent = $this->parent($value);
			
			foreach ($this->arr() as $k => $v) 
			{
				if($v !== $value && $this->parent($v) === $parent)
				$return->add($v);
			}
		}
		
		return $return;
	}
	
	
	// childs
	// retourne un objet avec les enfants de l'élément donné en argument
	// ne retourne pas les tables non existantes
	public function childs($value):self
	{
		$return = new static();
		$value = $this->get($value);
		
		if(!empty($value))
		{
			foreach ($this->arr() as $k => $v) 
			{
				if($this->parent($v) === $value)
				$return->add($v);
			}
		}
		
		return $return;
	}
	
	
	// relationChilds
	// retourne tous les ids ou les lignes qui sont des enfants de relation d'une ligne
	public function relationChilds($table,$primary):array
	{
		$return = array();
		$table = $this->get($table);
		$primary = ($primary instanceof Row)? $primary->primary():$primary;
		
		if($table instanceof Table && is_int($primary))
		{
			foreach ($this->arr() as $key => $value) 
			{
				$cols = $value->cols()->filter(array('isRelation'=>true));
				$cols = $cols->filter(array('relationTable'=>$table));
				
				if($cols->isNotEmpty())
				{
					foreach ($cols as $col) 
					{
						$colName = $col->name();
						$primaries = $col->primaries($primary);
						
						if(!empty($primaries))
						$return[$key][$colName] = $primaries;
					}
				}
			}
		}
		
		return $return;
	}
	
	
	// keyClassExtends
	// retourne un tableau utilisé par onPrepareKey
	public static function keyClassExtends():array
	{
		return array(Row::class,Table::class,Rows::class,Cells::class,Cols::class);
	}
}
?>