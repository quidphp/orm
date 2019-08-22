<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;
use Quid\Base;

// rows
class Rows extends Main\Map
{
	// trait
	use Main\Map\_obj, Main\Map\_readOnly, Main\Map\_sort;
	
	
	// config
	public static $config = [];
	
	
	// map
	protected static $allow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
	protected static $sortDefault = 'primary'; // défini la méthode pour sort par défaut
	
	
	// construct
	// construit un nouvel objet rows
	public function __construct(...$values) 
	{
		$this->add(...$values);
		
		return;
	}
	
	
	// toString
	// retourne les ids séparés par des virgules
	public function __toString():string
	{
		return implode(',',$this->primaries());
	}
	
	
	// onPrepareKey
	// prepare une clé pour les méthodes qui soumette une clé
	// peut fournir un tableau qui sera utilisé comme where
	protected function onPrepareKey($key) 
	{
		$return = null;
		
		if(is_int($key))
		$return = $key;
		
		elseif($key instanceof Row)
		$return = $key->primary();
		
		elseif($key instanceof Cell)
		$return = $key->row()->primary();
		
		elseif(is_array($key))
		{
			foreach ($this->arr() as $k => $v) 
			{
				if($v->cells()->isWhere($key))
				{
					$return = $k;
					break;
				}
			}
		}
		
		else
		$return = parent::onPrepareKey($key);
		
		return $return;
	}
	
	
	// onPrepareReturns
	// prépare le retour pour indexes, gets, slice et slice index
	// les lignes sont toujours retournés dans un nouvel objet rows
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
		return $this->primaries();
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
	

	// isTable
	// retourne vrai si la row contient des éléments de cette table
	public function isTable($value):bool 
	{
		$return = false;
		$table = $this->table();
		
		if(!empty($table) && ((is_object($value) && $value === $table) || (is_string($value) && $value === $table->name())))
		$return = true;
		
		return $return;
	}
	
	
	// hasChanged
	// retourne vrai si une des lignes a changé
	public function hasChanged():bool 
	{
		$return = false;
		
		foreach ($this->arr() as $key => $value) 
		{
			if($value->hasChanged())
			{
				$return = true;
				break;
			}
		}
		
		return $return;
	}
	
	
	// hasCell
	// retourne vrai si toutes les lignes dans l'objet ont la cellule
	public function hasCell($key):bool 
	{
		$return = false;
		$first = $this->first();
		
		if(!empty($first) && $first->hasCell($key))
		$return = true;
		
		return $return;
	}
	
	
	// checkCell
	// envoie une exception si toutes les lignes n'ont pas la cellule spécifiée en argument
	public function checkCell($key):bool 
	{
		$return = $this->hasCell($key);
		
		if($return === false)
		static::throw($key);
		
		return $return;
	}
	
	
	// primaries
	// retourne les clés primaires  contenus dans l'objet
	public function primaries():array
	{
		return $this->keys();
	}
	
	
	// ids
	// retourne les ids contenus dans l'objet
	public function ids():array
	{
		return $this->keys();
	}
	
	
	// db
	// retourne la db du premier objet
	public function db():?Db
	{
		$return = null;
		$first = $this->first();
		if(!empty($first))
		$return = $first->db();
		
		return $return;
	}
	
	
	// table
	// retourne la table du premier objet
	public function table():?Table 
	{
		$return = null;
		$first = $this->first();
		if(!empty($first))
		$return = $first->table();
		
		return $return;
	}
	
	
	// addMode
	// ajoute une ou plusieurs rows dans l'objet
	// valeurs doivent être des objets row
	// possible de fournir un objet rows
	// deux objets identiques ne peuvent pas être ajoutés dans rows
	// des objets de différentes tables ne peuvent être ajoutés dans rows
	// un mode doit être spécifié en premier argument (inOrder ou ordre d'ajout)
	public function addMode(bool $inOrder=false,...$values):self
	{
		$this->checkAllowed('add');
		$values = $this->prepareValues(...$values);
		$firstTable = $this->table();
		$data =& $this->arr();
		
		if($inOrder === true)
		$this->sortDefault();
		
		foreach ($values as $value)
		{
			if(!$value instanceof Row)
			static::throw('requiresRow');
			
			$table = $value->table();
			$firstTable = (empty($firstTable))? $table:$firstTable;
			
			if($table !== $firstTable)
			static::throw('rowMustBeFromSameTable');
			
			$primary = $value->primary();
			
			if(!array_key_exists($primary,$data))
			{
				if($inOrder === true)
				$data = Base\Arr::insertInOrder([$primary=>$value],$data);
				
				else
				$data[$primary] = $value;
			}
			
			else
			static::throw('rowAlreadyIn',$primary);
		}
		
		return $this->checkAfter();
	}
	
	
	// add
	// ajoute une ou plusieurs rows dans l'objet
	// possible de fournir un objet rows
	// l'ordre d'ajout des rows est gardé intacte, aucune sort
	public function add(...$values):self
	{
		return $this->addMode(false,...$values);
	}
	
	
	// addSort
	// ajoute une ou plusieurs rows dans l'objet
	// possible de fournir un objet rows
	// les rows sont toujours ajoutés dans l'ordre naturel des ids, donc si la row 1 est ajouté après la 3 elle aura quand même la première position
	public function addSort(...$values):self
	{
		return $this->addMode(true,...$values);
	}
	
	
	// getRefresh
	// retourne un objet row ou null si non existant
	// met la row à jour avant de retourner
	public function getRefresh($value):?Row 
	{
		$return = $this->get($value);
		
		if($return instanceof Row)
		$return->refresh();
		
		return $return;
	}
	
	
	// getsRefresh
	// retourne un nouvel objet rows avec les lignes refresh
	public function getsRefresh(...$values):self
	{
		return $this->gets(...$values)->refresh();
	}
	
	
	// label
	// retourne les noms de toutes les lignes
	public function label($pattern=null,?string $lang=null,?array $option=null):array 
	{
		return $this->pair('label',$pattern,$lang,$option);
	}
	
	
	// description
	// retourne les descriptions de toutes les lignes
	public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):array
	{
		return $this->pair('description',$pattern,$replace,$lang,$option);
	}
	
	
	// changed
	// retourne un nouvel objet rows avec les lignes qui ont changés
	public function changed():self 
	{
		$return = new static();
		
		foreach ($this->arr() as $key => $value) 
		{
			if($value->hasChanged())
			$return->add($value);
		}
		
		return $return;
	}
	
	
	// cell
	// permet de retourner un tableau id->cell pour toutes les lignes dans l'objet
	// envoie une exception si la cellule n'existe pas
	public function cell($key):array 
	{
		$return = [];
		$this->checkCell($key);
		
		foreach ($this->arr() as $id => $row) 
		{
			$cell = $row->cell($key);
			
			if(!empty($cell))
			$return[$id] = $cell;
		}
		
		return $return;
	}
	
	
	// cellNotEmpty
	// retourne un tableau id->cell pour toutes les lignes de l'objet
	// retourne seulement la cellule si elle n'est pas vide
	public function cellNotEmpty($key):array
	{
		$return = [];
		
		foreach ($this->cell($key) as $k => $v) 
		{
			if($v->isNotEmpty())
			$return[$k] = $v;
		}
		
		return $return;
	}
	
	
	// cellFirstNotEmpty
	// retoure la première cellule spécifé en argument non vide des lignes
	public function cellFirstNotEmpty($key):?Cell 
	{
		$return = null;
		$this->checkCell($key);
		
		foreach ($this->arr() as $id => $row) 
		{
			$cell = $row->cell($key);
			
			if(!empty($cell) && $cell->isNotEmpty())
			{
				$return = $cell;
				break;
			}
		}
		
		return $return;
	}
	

	// setCell
	// permet de changer la valeur de la cellule dans toutes les lignes
	// envoie une exception si la cellule n'existe pas
	public function setCell($key,$value):self
	{
		foreach ($this->cell($key) as $id => $cell) 
		{
			$cell->set($value);
		}
		
		return $this->checkAfter();
	}
	
	
	// resetCell
	// permet de reset la valeur de la cellule dans toutes les lignes
	// la valeur est ramené à sa dernière valeur commit
	// envoie une exception si la cellule n'existe pas
	public function resetCell($key):self
	{
		foreach ($this->cell($key) as $id => $cell) 
		{
			$cell->reset();
		}
		
		return $this->checkAfter();
	}
	
	
	// unsetCell
	// permet de changer la valeur de la cellule dans toutes les lignes
	// la valeur de changement est mis à null
	// envoie une exception si la cellule n'existe pas
	public function unsetCell($key):self
	{
		foreach ($this->cell($key) as $id => $cell) 
		{
			$cell->unset();
		}
		
		return $this->checkAfter();
	}
	
	
	// cellValue
	// permet de retourner un tableau id->cellValue pour toutes les lignes dans l'objet
	// par défaut utilise value de cellule, si get est true utilise get
	public function cellValue($value,bool $get=false):array 
	{
		$return = [];
		
		foreach ($this->arr() as $key => $row) 
		{
			$return[$key] = $row->cellValue($value,$get);
		}
		
		return $return;
	}
	
	
	// htmlStr
	// permet de passer le htmlStr sur une cellule dans chaque ligne
	// éventuellement cette méthode devra être remplacé par la classe tablesCells
	public function htmlStr($key,string $html,bool $str=false)
	{
		$return = [];
		$cells = $this->cell($key);
		
		foreach ($cells as $id => $cell) 
		{
			$return[$id] = $cell->htmlStr($html);
		}
		
		if($str === true)
		$return = implode($return);
		
		return $return;
	}
	
	
	// segment
	// permet de remplacer les segments d'une chaîne par le contenu des cellules pour toutes les lignes
	// par défaut utilise value de cellule, si get est true utilise get
	public function segment(string $value,bool $get=false,bool $str=false)
	{
		$return = [];
		
		foreach ($this->arr() as $key => $row) 
		{
			$return[$key] = $row->segment($value,$get);
		}
		
		if($str === true)
		$return = implode($return);
		
		return $return;
	}
	
	
	// keyValue
	// retourne un tableau associatif avec le contenu keyValue de toutes les lignes de l'objet
	// si get est true, value est passé dans get plutôt que value
	public function keyValue($key,$value,bool $get=false):array 
	{
		$return = [];
		
		foreach ($this->arr() as $row) 
		{
			$return = Base\Arr::append($return,$row->keyValue($key,$value,$get));
		}
		
		return $return;
	}
	
	
	// where
	// permet de filtrer des rows selon un tableau sql where
	// similaire à une syntaxe sql mais ne supporte pas les méthodes base/sql whereThree, ni les and, or et paranthèses
	public function where(array $array):self 
	{
		$return = new static();
		
		foreach ($this->arr() as $value) 
		{
			if($value->cells()->isWhere($array))
			$return->add($value);
		}
		
		return $return;
	}
	
	
	// order
	// clone et sort l'objet de rows
	// support un sort par multiple cellule et direction
	// fournir un tableau cellule -> direction
	public function order(array $array):self 
	{
		$return = $this->clone();
		$return->checkAllowed('sort');
		
		if($return->isNotEmpty())
		{
			$data =& $return->arr();
			
			$sorts = [];
			foreach ($array as $key => $value) 
			{
				$this->checkCell($key);
				$sorts[] = ['cellValue',$value,$key];
			}
			
			$data = Base\Obj::sorts($sorts,$data);
		}
		
		return $return->checkAfter();
	}
	
	
	// limit
	// clone et filtre l'objet par une limit et possiblement un offset
	// similaire à une syntaxe sql
	public function limit(int $value,?int $value2=null):self 
	{
		$limit = (is_int($value2))? $value2:$value;
		$offset = (is_int($value2))? $value:0;
		$return = $this->sliceIndex($offset,$limit);
		
		return $return;
	}
	
	
	// alive
	// retourne vrai si toutes les lignes existent, fait une requête pour la table
	public function alive():bool 
	{
		$return = false;
		$db = $this->db();
		$table = $this->table();
		$ids = $this->primaries();
		
		if(!empty($db) && !empty($table) && !empty($ids))
		{
			$count = $db->selectCount($table,$ids);

			if($count === $this->count())
			$return = true;
		}
		
		return $return;
	}
	
	
	// refresh
	// rafraichit les lignes, fait une requête pour la table
	public function refresh():self
	{
		$db = $this->db();
		$table = $this->table();
		$ids = $this->primaries();
		
		if(!empty($db) && !empty($table) && !empty($ids))
		{
			$assocs = $db->selectAllsPrimary($table,$ids);

			if(!empty($assocs))
			{
				foreach ($assocs as $id => $values) 
				{
					$row = $this->get($id);
					
					if(!empty($row))
					$row->cellsRefresh($values);
				}
			}
		}
		
		return $this->checkAfter();
	}
	
	
	// clean
	// enlève de l'objet toutes les lignes déliées
	// cette méthode ne tient pas compte de readOnly
	public function clean():self
	{
		$data =& $this->arr();
		
		foreach ($data as $key => $value) 
		{
			if(!$value->isLinked())
			unset($data[$key]);
		}
		
		return $this->checkAfter();
	}
	
	
	// unlink
	// unlink toutes les lignes de l'objet rows
	// les lignes sont retirés de cet objet, de rows table et prennent un statut inutilisable
	// cette méthode ne tient pas compte de readOnly
	// possible de donner un objet rows avec des lignes à ne pas unlink
	public function unlink(?Rows $rows=null):self
	{
		$data =& $this->arr();

		foreach ($data as $key => $value) 
		{
			if($value->isLinked())
			{
				if($rows === null || !$rows->in($value))
				{
					$value->unlink();
					unset($data[$key]);
				}
			}
		}
		
		return $this->checkAfter();
	}
	
	
	// update
	// sauve toutes les lignes dans l'objet
	// toutes les cellules sont passés dans onUpdate avant
	// seuls les cellules ayant changés sont envoyés à la db
	// retourne un tableau avec les résultats pour chaque ligne
	public function update(?array $option=null):array
	{
		$return = $this->pair('update',$option);
		$this->checkAfter();
		
		return $return;
	}

	
	// updateValid
	// sauve toutes les lignes dans l'objet
	// toutes les cellules sont passés dans update avant
	// seuls les cellules valides sont envoyés à la db
	// un message de communication est généré pour indiquer que la sauvegarde est partielle
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	// com est true par défaut
	public function updateValid(?array $option=null):array
	{
		$return = $this->pair('updateValid',$option);
		$this->checkAfter();
		
		return $return;
	}
	
	
	// updateChanged
	// sauve toutes les lignes dans l'objet: sans les include
	// différence: onUpdate est seulement appelé si au moins une cellule a changé
	// seuls les cellules ayant changés sont envoyés à la db
	// retourne un tableau avec les résultats pour chaque ligne
	public function updateChanged(?array $option=null):array
	{
		$return = $this->pair('updateChanged',$option);
		$this->checkAfter();
		
		return $return;
	}
	
	
	// updateChangedIncluded
	// sauve toutes les lignes dans l'objet: avec les include
	// différence: onUpdate est seulement appelé si au moins une cellule a changé
	// seuls les cellules ayant changés sont envoyés à la db
	// retourne un tableau avec les résultats pour chaque ligne
	public function updateChangedIncluded(?array $option=null):array
	{
		$return = $this->pair('updateChangedIncluded',$option);
		$this->checkAfter();
		
		return $return;
	}
	
	
	// updateChangedIncludedValid
	// sauve toutes les lignes dans l'objet: avec les include
	// différence: update est seulement appelé si au moins une cellule a changé
	// seuls les cellules valides sont envoyés à la db
	// retourne un tableau avec les résultats pour chaque ligne
	public function updateChangedIncludedValid(?array $option=null):array
	{
		$return = $this->pair('updateChangedIncludedValid',$option);
		$this->checkAfter();
		
		return $return;
	}
	
	
	// updateAll
	// sauve toutes les lignes dans l'objet
	// toutes les cellules sont passés dans onUpdate avant
	// toutes les cellules sont envoyés à la db
	// retourne un tableau avec les résultats pour chaque ligne
	public function updateAll(?array $option=null):array
	{
		$return = $this->pair('updateAll',$option);
		$this->checkAfter();
		
		return $return;
	}
	
	
	// updateRowChanged
	// sauve seulement les lignes ayant changés
	// toutes les cellules sont passés dans onUpdate avant
	// seuls les cellules ayant changés sont envoyés à la db
	// retourne un tableau avec les résultats pour les lignes ayant changés
	public function updateRowChanged(?array $option=null):array
	{
		$return = [];
		
		foreach ($this->arr() as $id => $row) 
		{
			if($row->hasChanged())
			$return[$id] = $row->update($option);
		}
		
		$this->checkAfter();
		
		return $return;
	}
	
	
	// delete
	// efface les lignes, fait une requête pour l'ensemble
	// les lignes effacés sont terminate, effacé de l'objet table et de cet objet rows
	// retourne un tableau avec les résultats pour chaque ligne
	public function delete(?array $option=null)
	{
		$return = null;
		
		foreach ($this->arr() as $id => $row) 
		{
			$delete = $row->delete($option);
			
			if(is_int($delete))
			$return += $delete;
		}
		
		$this->clean();
		$this->checkAfter();
		
		return $return;
	}
	
	
	// writeFile
	// écrit plusieurs lignes dans l'objet file fourni en argument
	public function writeFile(Main\File $file,?array $option=null):self
	{
		$option = Base\Arr::plus(['header'=>false],$option);
		
		foreach ($this->arr() as $row) 
		{
			$row->writeFile($file,$option);
			
			if($option['header'] === true)
			$option['header'] = false;
		}
		
		return $this;
	}
}
?>