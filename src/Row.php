<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;
use Quid\Base;

// row
// class to represent an existing row within a table
class Row extends Main\ArrObj
{
	// trait
	use _tableAccess;


	// config
	public static $config = []; // les config de row sont mergés à celles de table, avec priorité


	// dynamique
	protected $primary = null; // int, clé primaire de la ligne
	protected $cells = null; // objet cells


	// construct
	// construit l'objet table
	public function __construct(int $primary,Table $table)
	{
		$this->setPrimary($primary);
		$this->setLink($table,true);
		$this->cells = $this->cellsNew()->readOnly(true);

		return;
	}


	// toString
	// retourne la cellule lié à nom sous forme de string
	public function __toString():string
	{
		return Base\Str::cast($this->cellName());
	}


	// onInit
	// appeler après le premier cellsLoad de la row
	// par défaut renvoie à onRefreshed
	// méthode protégé qui peut être étendu
	protected function onInit():self
	{
		return $this->onRefreshed();
	}


	// onRefreshed
	// appeler après chaque appel réussi à cellsLoad ou cellsRefresh
	// méthode protégé qui peut être étendu
	protected function onRefreshed():self
	{
		return $this;
	}


	// onInserted
	// appelé après une insertion réussi dans core/table insert
	// méthode public qui peut être étendu
	public function onInserted(array $option)
	{
		return $this->onCommitted($option);
	}


	// onUpdated
	// appelé après une update réussie via une méthode row/update
	// méthode protégé qui peut être étendu
	protected function onUpdated(array $option)
	{
		return $this->onCommitted($option);
	}


	// onCommitted
	// appelé après une insertion ou update réussie via une méthode row/update
	// méthode protégé qui peut être étendu
	protected function onCommitted(array $option)
	{
		return $this->onCommittedOrDeleted($option);
	}


	// onDeleted
	// appelé après une suppression réussie via row/delete
	// méthode protégé qui peut être étendu
	protected function onDeleted(array $option)
	{
		return $this->onCommittedOrDeleted($option);
	}


	// onCommittedOrDeleted
	// appelé après une insertion, update ou suppression réussie
	// méthode protégé qui peut être étendu
	protected function onCommittedOrDeleted(array $option)
	{
		return $this;
	}


	// toArray
	// retourne les cellules de ligne sous un format tableau
	public function toArray():array
	{
		return $this->cells()->toArray();
	}


	// cast
	// retourne la valeur cast
	public function _cast():int
	{
		return $this->primary();
	}


	// offsetGet
	// arrayAccess offsetGet retourne une cellule
	// lance une exception si cellule non existante
	public function offsetGet($key)
	{
		return $this->cell($key);
	}


	// offsetSet
	// arrayAccess offsetGet appele la méthode set de la cellule
	// lance une exception si cellule non existante
	public function offsetSet($key,$value):void
	{
		$this->cell($key)->set($value);

		return;
	}


	// offsetUnset
	// arrayAccess offsetGet appele la méthode unset de la cellule
	// lance une exception si cellule non existante
	public function offsetUnset($key):void
	{
		$this->cell($key)->unset();

		return;
	}


	// arr
	// retourne le tableau de cells
	protected function arr():array
	{
		return $this->cells()->toArray();
	}


	// isLinked
	// retourne vrai si la ligne est lié à l'objet db
	public function isLinked():bool
	{
		return ($this->hasDb() && $this->table()->isRowLinked($this))? true:false;
	}


	// alive
	// retourne vrai si la ligne existe dans la table de la base de données
	public function alive():bool
	{
		return ($this->db()->selectCount($this->table(),$this) === 1)? true:false;
	}


	// hasCell
	// retourne vrai si la celulle existe dans la ligne
	public function hasCell(...$keys):bool
	{
		return $this->cells()->exists(...$keys);
	}


	// hasChanged
	// retourne vrai si une des cellules de la ligne a changé
	public function hasChanged():bool
	{
		return $this->cells()->hasChanged();
	}


	// isUpdateable
	// retourne vrai si la row peut être updater
	public function isUpdateable(?array $option=null):bool
	{
		return true;
	}


	// isDeleteable
	// retourne vrai si la row peut être effacer
	// relationChilds est utilisé avec excludeSelf
	public function isDeleteable(?array $option=null):bool
	{
		return ($this->hasRelationChilds(null,true) === true)? false:true;
	}


	// hasRelationChilds
	// retourne si la row a des enfants de relation
	// excluseSelf permet à une row qui s'est par exemple modifié elle-même de toujours s'effacer
	public function hasRelationChilds($table=null,bool $excludeSelf=false):bool
	{
		$return = false;
		$childs = $this->relationChilds();

		if(!empty($childs))
		{
			$return = true;

			if($table instanceof Table)
			$table = $table->name();

			if(is_string($table))
			$return = (!empty($childs[$table]))? true:false;

			if($return === true && $excludeSelf === true)
			{
				$primary = $this->primary();
				$tableName = $this->tableName();

				if(!empty($childs[$tableName]))
				{
					foreach ($childs[$tableName] as $col => $primaries)
					{
						if(in_array($primary,$primaries,true))
						{
							$primaries = Base\Arr::valueStrip($primary,$primaries);
							if(!empty($primaries))
							$childs[$tableName][$col] = $primaries;
							else
							unset($childs[$tableName][$col]);
						}
					}

					if(empty($childs[$tableName]))
					unset($childs[$tableName]);

					if(empty($childs))
					$return = false;
				}
			}
		}

		return $return;
	}


	// sameRow
	// retourne vrai si l'objet et celui fourni ont la même ligne
	public function sameRow($row):bool
	{
		return ($this === $this->table()->row($row))? true:false;
	}


	// setPrimary
	// change la ligne primaire de la ligne
	// méthode protégé
	protected function setPrimary(int $primary):self
	{
		if($primary > 0)
		$this->primary = $primary;

		else
		static::throw();

		return $this;
	}


	// primary
	// retourne la clé primaire de la ligne
	public function primary():int
	{
		return $this->primary;
	}


	// id
	// retourne la clé primaire de la ligne
	public function id():int
	{
		return $this->primary;
	}


	// attr
	// retourne le tableau d'attribut ou une valeur du tableau attr de la table
	public function attr($key=null)
	{
		return $this->table()->attr($key);
	}


	// attrCall
	// retourne un attribut de la table, lance la callable si existante
	public function attrCall($key,...$args)
	{
		return $this->col()->attrCall($key,...$args);
	}


	// attrNotEmpty
	// retourne vrai si l'attribut de la table n'est pas vide
	public function attrNotEmpty($key):bool
	{
		return $this->table()->attrNotEmpty($key);
	}


	// pointer
	// retourne le nom de la table et le primary
	public function pointer(?string $separator=null):string
	{
		return Base\Str::toPointer($this->tableName(),$this->primary(),$separator);
	}


	// value
	// retourne un tableau avec les valeurs des cellules
	public function value(...$keys):array
	{
		return $this->cells(...$keys)->keyValue();
	}


	// get
	// retourne un tableau avec les valeurs get des cellules
	public function get(...$keys):array
	{
		return $this->cells(...$keys)->keyValue(true);
	}


	// set
	// permet de change le contenu de plusieurs cellules
	// possible de faire le test de prévalidation
	// option preValidate
	public function set(array $data,?array $option=null):self
	{
		$option = Base\Arr::plus(['preValidate'=>false],$option);
		$cells = $this->cells();

		if($option['preValidate'] === true)
		{
			$set = $data;
			$data = $cells->preValidatePrepare($data);
			$data = $this->preValidate($data,$option);

			if(count($data) !== count($set))
			$option['partial'] = true;
			$option['preValidate'] = false;
		}

		$cells->sets($data,$option);

		return $this;
	}


	// label
	// retourne le label de la row
	public function label($pattern=null,?string $lang=null,?array $option=null):?string
	{
		$return = null;
		$obj = $this->db()->lang();
		$option = Base\Arr::plus($option,['pattern'=>$pattern]);
		$tableName = $this->tableName();
		$return = $obj->rowLabel($this->primary(),$tableName,$lang,$option);

		return $return;
	}


	// description
	// retourne la description de la row
	public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
	{
		$return = null;
		$obj = $this->db()->lang();
		$option = Base\Arr::plus($option,['pattern'=>$pattern]);
		$tableName = $this->tableName();
		$return = $obj->rowDescription($this->primary(),$tableName,$replace,$lang,$option);

		return $return;
	}


	// cellsNew
	// crée l'objet cells
	// méthode protégé
	protected function cellsNew():Cells
	{
		$return = null;

		$class = $this->cellsClass();
		if(!empty($class))
		$return = new $class();
		else
		static::throw('noCellsClass');

		return $return;
	}


	// cellsLoad
	// crées les cellules de la row
	// les cellules sont crées dans l'ordre de priorité des colonnes
	// envoie une exception si le tableau data ne contient pas toutes les colonnes non ignorés
	public function cellsLoad(array $data):self
	{
		if($this->cells->isEmpty())
		{
			$cols = $this->table()->cols();
			$names = $cols->names();

			if(Base\Arr::keysExists($names,$data))
			{
				$this->cells()->readOnly(false);

				foreach ($names as $key)
				{
					$col = $cols->get($key);
					$class = $this->cellClass($col);

					if(!empty($class))
					$this->cellMake($class,$col,$data[$key]);

					else
					static::throw('noClass');
				}

				$this->cells()->readOnly(true);
				$this->onInit();
			}

			else
			static::throw('invalidInitialData','provideAllColumns');
		}

		else
		static::throw('cellsNotEmpty');

		return $this;
	}


	// cellsRefresh
	// rafraîchit les valeurs commit des cellules
	// les cellules doivent déjà existés
	// pas besoin d'avoir toutes les cellules dans le tableau data
	// méthode permissive à cause de la possibilité d'ignorer des colonnes
	public function cellsRefresh(array $data):self
	{
		$cells = $this->cells();

		if(!$cells->isEmpty())
		{
			foreach ($data as $key => $value)
			{
				if($cells->exists($key))
				{
					$cell = $cells->get($key);
					$cell->setInitial($value);
				}
			}

			$this->onRefreshed();
		}

		else
		static::throw('cellsEmpty');

		return $this;
	}


	// cells
	// retourne l'objet des cellules
	public function cells(...$keys):Cells
	{
		return (empty($keys))? $this->cells:$this->cells->gets(...$keys);
	}


	// cellsClass
	// retourne et la classe à utiliser pour les cells
	public function cellsClass():string
	{
		return $this->table()->classe()->cells();
	}


	// cellClass
	// retourne et la classe à utiliser pour la cell
	public function cellClass(Col $col):string
	{
		return $this->table()->classe()->cell($col);
	}


	// cellMake
	// construit et store un objet cellule
	// méthode protégé
	protected function cellMake(string $class,Col $col,$value):self
	{
		$cell = new $class($value,$col,$this);
		$this->cells->add($cell);

		return $this;
	}


	// cell
	// retourne l'objet d'une cellule ou envoie une exception si non existant
	public function cell($cell):Cell
	{
		$return = $this->cells()->get($cell);

		if(!$return instanceof Cell)
		static::throw($cell);

		return $return;
	}


	// cellPattern
	// retourne l'objet d'une cellule ou null
	// si un pattern est fourni, passe dans base/col addPattern
	// sinon si la cellule n'existe pas rajoute tous les patterns possibles dans le nom
	// sauf les patterns en lien avec la langue et qui n'est pas la langue courante
	public function cellPattern(string $cell,?string $pattern=null):?Cell
	{
		$return = null;

		if(is_string($pattern))
		$cell = ColSchema::addPattern($pattern,$cell);

		elseif(!$this->hasCell($cell))
		$cell = ColSchema::possible($cell,true);

		if(!empty($cell) && $this->hasCell($cell))
		$return = $this->cell($cell);

		return $return;
	}


	// cellValue
	// retourne la valeur de l'objet d'une cellule
	public function cellValue($cell,bool $get=false)
	{
		return $this->cell($cell)->pair(($get === true)? 'get':false);
	}


	// segment
	// permet de remplacer les segments d'une chaîne par le contenu des cellules
	// par défaut utilise value de cellule, si get est true utilise get
	public function segment(string $value,bool $get=false):string
	{
		return $this->cells()->segment($value,$get);
	}


	// keyValue
	// retourne le contenu de la ligne sous une forme keyValue
	// si get est true, value est passé dans get plutôt que value
	public function keyValue($key,$value,bool $get=false):array
	{
		$return = [];
		$key = $this->cell($key)->value();
		$value = $this->cell($value);
		$value = ($get === true)? $value->get():$value->value();
		$return = [$key=>$value];

		return $return;
	}


	// relationKeyValue
	// retourne la row sous sa forme relation, tel que décrit dans tableRelation
	public function relationKeyValue($output=true,bool $onGet=false)
	{
		return $this->table()->relation()->output($this->value(),$output,$onGet);
	}


	// relationChilds
	// retourne toutes les lignes enfants de la ligne
	public function relationChilds():array
	{
		return $this->cache(__METHOD__,function() {
			return $this->db()->tables()->relationChilds($this->table(),$this);
		});
	}


	// isActive
	// retourne vrai si la cellule active a la valeur donné en argument, par défaut 1
	// si active est non existante et que value est 1, retourne true
	public function isActive(?int $value=1):bool
	{
		$return = false;
		$active = $this->cellActive();

		if(!empty($active) && $active->isEqual($value))
		$return = true;

		elseif($active === null && $value === 1)
		$return = true;

		return $return;
	}


	// deactivate
	// désactive la ligne, si la ligne a un champ active
	// envoie une exception si désactivation impossible
	public function deactivate(?array $option=null):?int
	{
		$return = null;
		$active = $this->cellActive();

		if(!empty($active))
		{
			$active->set(null);
			$return = $this->updateChangedIncluded($option);
		}

		else
		static::throw('noActiveCell');

		return $return;
	}


	// isVisible
	// retourne vrai si la row est visible
	// cela signifie que la ligne est active si elle a un champ active
	// et si toutes les cellules requises ont une valeur non vide
	public function isVisible():bool
	{
		return ($this->isActive() && $this->cells()->isStillRequiredEmpty())? true:false;
	}


	// cellActive
	// retourne la cellule active, tel que défini dans la table
	// peut retourner null si non existante
	public function cellActive():?Cell
	{
		$return = null;
		$active = $this->table()->colActive();

		if(!empty($active))
		$return = $this->cell($active);

		return $return;
	}


	// cellKey
	// retourne la cellule de key, tel que défini dans la table
	// possible de spécifier une langue, sinon langue courante ou pas de langue
	// envoie une exception si non existante
	public function cellKey(?string $lang=null):Cell
	{
		return$this->cell($this->table()->colKey($lang));
	}


	// cellName
	// retourne la cellule de nom, tel que défini dans la table
	// possible de spécifier une langue, sinon langue courante ou pas de langue
	// envoie une exception si non existante
	public function cellName(?string $lang=null):Cell
	{
		return $this->cell($this->table()->colName($lang));
	}


	// cellContent
	// retourne la cellule de contenu, tel que défini dans la table
	// possible de spécifier une langue, sinon langue courante ou pas de langue
	// envoie une exception si non existante
	public function cellContent(?string $lang=null):Cell
	{
		return $this->cell($this->table()->colContent($lang));
	}


	// namePrimary
	// retourne le nom de la row avec la primary entre paranthèse
	public function namePrimary(?string $pattern=null):string
	{
		$return = '';
		$pattern = (is_string($pattern))? $pattern:'%name% (#%primary%)';
		$replace['%name%'] = $this->cellName();
		$replace['%primary%'] = $this->primary();
		$return = Base\Str::replace($replace,$pattern);

		return $return;
	}


	// slugName
	// retourne le slug du nom de la row
	public function slugName(?array $option=null):string
	{
		return Base\Slug::str($this->cellName(),$option);
	}


	// toRows
	// retourne la row courante dans un nouvel objet rows
	public function toRows():Rows
	{
		return $this->table()->rowsNew()->add($this);
	}


	// refresh
	// charge les données de la row à partir de la base de donnée
	// si la ligne n'existe plus, unlink
	public function refresh():self
	{
		$table = $this->table();
		$assoc = $this->db()->selectAll($table,$this);

		if(is_array($assoc) && !empty($assoc))
		{
			$cells = $this->cells();

			if($cells->isEmpty())
			$this->cellsLoad($assoc);
			else
			$this->cellsRefresh($assoc);
		}

		else
		$this->unlink();

		return $this;
	}


	// preValidate
	// fait la prévalidation des données sur un tableau
	// option com et strict
	public function preValidate(array $return,?array $option=null):array
	{
		$option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
		$cells = $this->cells();
		$preValidate = $cells->preValidate($return,true);

		if(!empty($preValidate))
		{
			if($option['com'] === true)
			$this->updateCom($preValidate);

			elseif($option['strict'] === true)
			static::throw($this->table(),...array_keys($preValidate));

			$return = Base\Arr::keysStrip(array_keys($preValidate),$return);
		}

		return $return;
	}


	// setUpdateMethod
	// set les valeurs des cells et update
	// la différence est que set est enrobbé du même try catch que update
	// note: les données seront perdus si une exception attrapable est envoyé dans row/set
	// la méthode doit être défini
	// option log, com, preValidate
	public function setUpdateMethod(string $method,array $set,?array $option=null):?int
	{
		$return = null;
		$option = Base\Arr::plus(['log'=>true,'com'=>false,'preValidate'=>false],$option);

		if(in_array($method,['update','updateValid','updateChanged','updateChangedIncluded','updateChangedIncludedValid','updateAll'],true))
		{
			try
			{
				$this->set($set,$option);
				$return = $this->$method($option);
			}

			catch (Main\Contract\Catchable $result)
			{
				$this->updateAfter($result,null,$option);
			}
		}

		else
		static::throw();

		return $return;
	}


	// setUpdate
	// set les valeurs des cells et update
	public function setUpdate(array $set,?array $option=null):?int
	{
		return $this->setUpdateMethod('update',$set,$option);
	}


	// setUpdateValid
	// set les valeurs des cells et updateValid
	public function setUpdateValid(array $set,?array $option=null):?int
	{
		return $this->setUpdateMethod('updateValid',$set,$option);
	}


	// setUpdateChangedIncluded
	// set les valeurs des cells et setUpdateChangedIncluded
	public function setUpdateChangedIncluded(array $set,?array $option=null):?int
	{
		return $this->setUpdateMethod('setUpdateChangedIncluded',$set,$option);
	}


	// setUpdateChangedIncludedValid
	// set les valeurs des cells et updateChangedIncludedValid
	public function setUpdateChangedIncludedValid(array $set,?array $option=null):?int
	{
		return $this->setUpdateMethod('updateChangedIncludedValid',$set,$option);
	}


	// duplicate
	// permet de dupliquer la ligne
	public function duplicate(?array $option=null)
	{
		$return = null;
		$table = $this->table();
		$cells = $this->cells()->withoutPrimary()->filter(['attr'=>true],'duplicate');

		if($cells->isNotEmpty())
		{
			$keyValue = [];
			$rowGet = $this->get();
			$option = (array) $option;

			foreach ($cells as $key => $cell)
			{
				$value = $cell->value();
				$value = $cell->col()->onDuplicate($value,$rowGet,$cell,$option);
				$keyValue[$key] = $value;
			}

			$return = $table->insert($keyValue,$option);
		}

		else
		static::throw('noCellsToDuplicate');

		return $return;
	}


	// update
	// sauve les cellules de la ligne ayant changés
	// toutes les cellules sont passés dans update avant
	// toutes les cellules sont passés dans updateBefore
	// seuls les cellules ayant changés sont envoyés à la db
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function update(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$cells->update($option);
		$return = $this->updateBeforeAssoc($cells,true,$option);

		return $return;
	}


	// updateValid
	// sauve toutes les cellules valide de la ligne
	// toutes les cellules sont passés dans update avant
	// seuls les cellules valides sont envoyés à la db
	// un message de communication est généré pour indiquer que la sauvegarde est partielle
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function updateValid(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$cells->update($option);
		$cells = $this->updateBeforeValid($cells,$option);

		if($cells->count() !== $this->cells()->count())
		$option['partial'] = true;

		$return = $this->updateBeforeAssoc($cells,true,$option);

		return $return;
	}


	// updateChanged
	// sauve toutes les cellules de la ligne ayant changé: sans les include
	// différence: update est seulement appelé si au moins une cellule a changé
	// seuls les cellules ayant changés sont envoyés à update et updateBefore
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function updateChanged(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$changed = $cells->changed(false,$option);

		if($changed->isNotEmpty())
		{
			$cells->update($option);
			$cells = $cells->changed(false,$option);
			$return = $this->updateBeforeAssoc($cells,true,$option);
		}

		return $return;
	}


	// updateChangedIncluded
	// sauve toutes les cellules de la ligne ayant changé: avec les include (et required)
	// différence: update est seulement appelé si au moins une cellule a changé
	// seuls les cellules ayant changés sont envoyés à update et updateBefore
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function updateChangedIncluded(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$changedIncluded = $cells->changed(true,$option);

		if($changedIncluded->isNotEmpty())
		{
			$cells->update($option);
			$cells = $cells->changed(true,$option);
			$return = $this->updateBeforeAssoc($cells,true,$option);
		}

		return $return;
	}


	// updateChangedIncludedValid
	// sauve toutes les cellules valide et ayant changés dans la ligne
	// différence: update est seulement appelé si au moins une cellule a changé
	// seuls les cellules valides sont envoyés à la db
	// un message de communication peut être généré pour indiquer que la sauvegarde est partielle
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function updateChangedIncludedValid(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$changed = $cells->changed(true,$option);
		$noChange = true;

		if($changed->isNotEmpty())
		{
			$valid = $this->updateBeforeValid($changed,$option);

			if(!($valid === null || $valid->changed(false,$option)->isEmpty()))
			{
				$noChange = false;
				if($valid->count() !== $changed->count())
				$option['partial'] = true;
				$cells = $cells->update($option)->changed(true,$option);

				foreach ($cells as $key => $cell)
				{
					if(!$valid->exists($key) && !$changed->exists($key))
					$valid->add($cell);
				}

				$return = $this->updateBeforeAssoc($valid,true,$option);
			}
		}

		if($noChange === true)
		{
			$return = 0;
			$this->updateAfter($return,null,$option);
		}

		return $return;
	}


	// updateAll
	// sauve toutes les cellules de la ligne
	// toutes les cellules sont passés dans update avant
	// toutes les cellules sont passés dans updateBefore
	// toutes les cellules sont envoyés à la db
	// retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
	public function updateAll(?array $option=null):?int
	{
		$return = null;
		$cells = $this->cells();
		$cells->update($option);
		$return = $this->updateBeforeAssoc($cells,false,$option);

		return $return;
	}


	// updateBeforeValid
	// utilisé par updateValid et updateChangedIncludedValid pour filtrer les cellules non valide
	// un message est ajouté si option com est true
	// méthode protégé
	protected function updateBeforeValid(Cells $return,?array $option=null):?Cells
	{
		$option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
		$completeValidation = $return->completeValidation($option['com']);

		if(!empty($completeValidation))
		{
			if($option['com'] === true)
			$this->updateCom($completeValidation);

			elseif($option['strict'] === true)
			static::throw('invalid',$this->table(),$this,Base\Json::encode($completeValidation));

			$names = Base\Arr::valuesStrip(array_keys($completeValidation),$return->names());

			if(!empty($names))
			$return = $return->gets(...$names);

			else
			$return = null;
		}

		return $return;
	}


	// updateBeforeAssoc
	// méthode protégé utilisé par update, updateValid, updateAll et updateChangedIncluded avant d'envoyer à updateAssoc
	// gère validate et finalValidate
	protected function updateBeforeAssoc(Cells $cells,bool $changed=true,?array $option=null):?int
	{
		$return = null;
		$option = Base\Arr::plus(['finalValidate'=>true],$option);

		if($this->updateBeforeValid($cells,$option) === $cells)
		{
			$return = 0;
			$proceed = true;

			if($option['finalValidate'] === true)
			$proceed = $this->updateBeforeFinalValidate($cells,$option);

			if($proceed === true)
			{
				$array = [];
				$loop = ($changed === true)? $cells->changed(true,$option):$cells->withoutPrimary();
				$array = $loop->keyValue();

				$return = $this->updateAssoc($array,$option);
			}
		}

		return $return;
	}


	// updateBeforeFinalValidate
	// méthode protégé, gère la validation final avant le update
	// prendre note que final validation bloque entièrement l'update de la ligne, pas seulement les cellules en problème
	protected function updateBeforeFinalValidate(Cells $cells,?array $option=null):bool
	{
		$return = true;
		$option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
		$finalValidation = $this->updateFinalValidate($cells,$option);

		if(!empty($finalValidation))
		{
			$return = false;

			if($option['com'] === true)
			$this->updateCom($finalValidation);

			elseif($option['strict'] === true)
			static::throw('invalid',$this->table(),$this,Base\Json::encode($finalValidation));
		}

		return $return;
	}


	// updateAssoc
	// sauve la ligne via un tableau associatif
	// la validation n'a pas lieu à partir de cette méthode, il faut utiliser une des autres variantes de update
	// si la requête réussi commit les valeurs dans les cellules
	// exception envoyé si on tente de changer la valeur de la clé primaire
	// par défaut l'événement est log, la validation a lieu, mais com est false
	public function updateAssoc(array $set,?array $option=null):?int
	{
		$return = null;
		$option = Base\Arr::plus(['log'=>true,'com'=>false],$option);
		$db = $this->db();
		$table = $this->table();
		$cells = $this->cells();
		$result = null;

		try
		{
			if(empty($set))
			$result = 0;

			elseif(!$cells->exists(...array_keys($set)))
			static::throw('columnsNoMatch');

			elseif(array_key_exists($db->primary(),$set))
			static::throw('cannotSetPrimaryCell');

			elseif(!$this->isUpdateable($option))
			static::catchable(null,'notUpdatable',$this);

			else
			{
				if($option['log'] === false)
				$db->off();

				$result = $db->update($table,$set,$this);

				if($option['log'] === false)
				$db->on();
			}
		}

		catch (Main\Contract\Catchable $result)
		{

		}

		finally
		{
			$this->updateAfter($result,$set,$option);

			if(is_int($result))
			{
				if($result === 1)
				$this->onUpdated($option);

				$this->cellsRefresh($set);
				$return = $result;
			}
		}

		return $return;
	}


	// updateCom
	// méthode utilisé pour générer la communication pour une sauvegarde
	// si le value est associatif, envoie dans com/prepareIn
	public function updateCom($value,string $type=null,?string $label=null,?array $replace=null,?array $attr=null,bool $prepend=false):Main\Com
	{
		$return = $this->db()->com();

		if(!empty($value))
		{
			$label = ($label === null)? $this->label():$label;
			$attr = Base\Attr::append(['row','update','data'=>['primary'=>$this,'table'=>$this->table(),'action'=>'update']],$attr);

			if(is_string($value))
			$value = [[$type,$value,$replace]];

			elseif(is_array($value) && Base\Arr::isAssoc($value))
			{
				foreach ($value as $k => $v)
				{
					if($this->hasCell($k))
					{
						unset($value[$k]);
						$k = $this->cell($k)->label();
						$value[$k] = $v;
					}
				}

				$value = $return->prepareIn('neutral',$type,$value,$replace);
			}

			if(is_array($value) && !empty($value))
			{
				$method = ($prepend === true)? 'prepend':'append';
				$return->$method('neutral',$label,null,$attr,...$value);
			}
		}

		return $return;
	}


	// updateAfter
	// gère la communication après la requête update si option com est true
	// si option com est false et qu'il y a une exception attrapable, renvoie
	// méthode protégé
	protected function updateAfter($result,?array $set=null,?array $option=null):self
	{
		$option = Base\Arr::plus(['com'=>false,'strict'=>true,'partial'=>false,'onCommitted'=>true],$option);

		if($option['com'] === true)
		{
			$in = [];
			$lang = $this->db()->lang();
			$name = $this->tableName();

			if($result === 1)
			{
				if($option['partial'] === true)
				{
					$key = ($lang->existsCom('pos',"update/$name/partial"))? $name:'*';
					$in[] = ['pos',"update/$key/partial"];
				}

				else
				{
					$key = ($lang->existsCom('pos',"update/$name/success"))? $name:'*';
					$in[] = ['pos',"update/$key/success"];
				}
			}

			elseif($result === 0)
			{
				$key = ($lang->existsCom('pos',"update/$name/noChange"))? $name:'*';
				$in[] = ['pos',"update/$key/noChange"];
			}

			elseif(is_int($result) && $result > 1)
			{
				$key = ($lang->existsCom('neg',"update/$name/tooMany"))? $name:'*';
				$in[] = ['neg',"update/$key/tooMany"];
			}

			elseif($result instanceof Main\Contract\Catchable)
			{
				$key = ($lang->existsCom('neg',"update/$name/exception"))? $name:'*';
				$in[] = ['neg',"update/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
				$result->onCatched(['com'=>false]);
			}

			else
			{
				$key = ($lang->existsCom('neg',"update/$name/system"))? $name:'*';
				$in[] = ['neg',"update/$key/system"];
			}

			$this->updateCom($in,null,null,null,null,true);
		}

		elseif($result instanceof Main\Contract\Catchable)
		throw $result;

		elseif($option['strict'] === true && !in_array($result,[0,1],true))
		static::throw('updateFailed',$result,'strictMode');

		if($option['onCommitted'] === true && in_array($result,[0,1],true) && is_array($set) && !empty($set))
		$this->updateOnCommitted($set,$option);

		return $this;
	}


	// updateOnCommitted
	// lance le callback onCommitted sur toutes les colonnes qui ont changés
	protected function updateOnCommitted(array $set,array $option):self
	{
		$cells = $this->cells(...array_keys($set));

		foreach ($cells as $key => $cell)
		{
			if($cell->hasChanged())
			$cell->onCommitted(false,$option);
		}

		return $this;
	}


	// delete
	// efface une ligne de la base de donnée et délie l'objet de table
	// la ligne est ensuite empty et mis dans un état inutilisable
	// toutes les cellules sont passés dans delete, l'envoie d'une exception arrêtera le delete
	// par défaut l'événement est log et com est false
	public function delete(?array $option=null):?int
	{
		$return = null;
		$option = Base\Arr::plus(['log'=>true,'com'=>false],$option);
		$db = $this->db();
		$table = $this->table();
		$result = null;

		try
		{
			if(!$this->isDeleteable($option))
			static::catchable(null,'notDeleteable',$this);

			$this->cells()->delete($option);

			if($option['log'] === false)
			$db->off();

			$result = $db->delete($table,$this);

			if($option['log'] === false)
			$db->on();
		}

		catch (Main\Contract\Catchable $result)
		{

		}

		finally
		{
			$this->deleteAfter($result,$option);

			if(is_int($result))
			{
				if($result === 1)
				$this->onDeleted($option);

				$this->unlink();

				$return = $result;
			}
		}

		return $return;
	}


	// deleteCom
	// méthode utilisé pour générer la communication pour une suppression
	// si le value est associatif, envoie dans com/prepareIn
	public function deleteCom(array $value,?string $label=null,?array $replace=null,?array $attr=null,bool $prepend=false):Main\Com
	{
		$return = $this->db()->com();

		if(!empty($value))
		{
			$label = ($label === null)? $this->label():$label;
			$attr = Base\Attr::append(['row','delete','data'=>['table'=>$this->table(),'primary'=>$this,'action'=>'delete']],$attr);

			if(Base\Arr::isAssoc($value))
			$value = $return->prepareIn('neutral','neg',$value);

			if(!empty($value))
			{
				$method = ($prepend === true)? 'prepend':'append';
				$return->$method('neutral',$label,$replace,$attr,...$value);
			}
		}

		return $return;
	}


	// deleteAfter
	// gère la communication après la requête delete si option com est true
	// si option com est false et qu'il y a une exception attrapable, renvoie
	// méthode protégé
	protected function deleteAfter($result,?array $option=null):self
	{
		$option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);

		if($option['com'] === true)
		{
			$in = [];
			$lang = $this->db()->lang();
			$name = $this->tableName();

			if($result === 1)
			{
				$key = ($lang->existsCom('pos',"delete/$name/success"))? $name:'*';
				$in[] = ['pos',"delete/$key/success"];
			}

			elseif($result === 0)
			{
				$key = ($lang->existsCom('neg',"delete/$name/notFound"))? $name:'*';
				$in[] = ['neg',"delete/$key/notFound"];
			}

			elseif(is_int($result) && $result > 1)
			{
				$key = ($lang->existsCom('neg',"delete/$name/tooMany"))? $name:'*';
				$in[] = ['neg',"delete/$key/tooMany"];
			}

			elseif($result instanceof Main\Contract\Catchable)
			{
				$key = ($lang->existsCom('neg',"delete/$name/exception"))? $name:'*';
				$in[] = ['neg',"delete/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
				$result->onCatched(['com'=>false]);
			}

			else
			{
				$key = ($lang->existsCom('neg',"delete/$name/system"))? $name:'*';
				$in[] = ['neg',"delete/$key/system"];
			}

			$this->deleteCom($in,null,null,null,true);
		}

		elseif($result instanceof Main\Contract\Catchable)
		throw $result;

		elseif($option['strict'] === true && !in_array($result,[0,1],true))
		static::throw('deleteFailed',$result,'strictMode');

		return $this;
	}


	// deleteOrDeactivate
	// tente d'effacer la ligne, si ce n'est pas possible désactive
	// exception envoyer si la désactivation est impossible
	public function deleteOrDeactivate(?array $option=null):?int
	{
		$return = null;

		if($this->isDeleteable())
		$return = $this->delete($option);

		else
		$return = $this->deactivate($option);

		return $return;
	}


	// terminate
	// vide un objet ligne
	// l'objet devient inutilisable
	public function terminate():self
	{
		$this->primary = 0;
		$this->table = null;
		$this->db = null;

		foreach ($this->cells() as $cell)
		{
			$cell->terminate();
		}

		return $this;
	}


	// unlink
	// termine un objet et délie le de la table
	public function unlink():self
	{
		$rows = $this->table()->rows();
		$rows->readOnly(false);
		$rows->remove($this);
		$rows->readOnly(true);

		$this->terminate();

		return $this;
	}


	// writeFile
	// écrit la ligne dans l'objet file fourni en argument
	public function writeFile(Main\File $file,?array $option=null):self
	{
		$cols = $this->table()->cols()->filter(['isExportable'=>true]);
		$cells = $this->cells($cols);

		if($option['header'] === true)
		$cols->writeFile($file,$cells,$option);

		$cells->writeFile($file,$option);

		return $this;
	}


	// insertFinalValidate
	// gère la validation finale sur la row lors d'une insertion
	public static function insertFinalValidate(array $set,array $option)
	{
		return static::commitFinalValidate($set,null,$option);
	}


	// updateFinalValidate
	// gère la validation finale sur la row lors d'une mise à jour
	public function updateFinalValidate(Cells $cells,array $option)
	{
		return static::commitFinalValidate($cells->keyValue(),$this,$option);
	}


	// commitFinalValidate
	// gère la validation finale sur la row lors d'une insertion ou mise à jour
	public static function commitFinalValidate(array $set,?self $row,array $option)
	{
		return;
	}


	// configReplaceMode
	// retourne le tableau des clés à ne pas merger recursivement
	public static function configReplaceMode():array
	{
		return Table::configReplaceMode();
	}
}
?>