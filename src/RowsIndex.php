<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// rowsIndex
class RowsIndex extends Rows
{
	// trait
	use Main\Map\_sequential;


	// config
	public static $config = [];


	// map
	protected static $allow = ['add','unset','remove','empty','sequential','filter','sort','clone']; // méthodes permises
	protected static $after = ['sequential']; // sequential après chaque appel qui modifie, sequential ne crée pas de clone


	// isTable
	// retourne vrai si le rowsIndex contient au moins un élément de cette table
	public function isTable($value):bool
	{
		$return = false;

		foreach ($this->tables() as $table)
		{
			if((is_object($value) && $value === $table) || (is_string($value) && $value === $table->name()))
			{
				$return = true;
				break;
			}
		}

		return $return;
	}


	// sameTable
	// retourne vrai si toutes les lignes dans l'objet ont la même table
	public function sameTable():bool
	{
		$return = false;
		$data = $this->arr();
		$table = $this->table();

		if(!empty($table))
		{
			if(count($data) > 1)
			{
				$i = 0;
				foreach ($data as $key => $value)
				{
					if($i > 0)
					{
						$return = $value->sameTable($table);

						if($return === false)
						break;
					}

					$i++;
				}
			}

			else
			$return = true;
		}

		return $return;
	}


	// hasCell
	// retourne vrai si toutes les lignes dans l'objet ont la cellule
	public function hasCell($key):bool
	{
		$return = false;

		foreach ($this->arr() as $row)
		{
			$return = $row->hasCell($key);

			if($return === false)
			break;
		}

		return $return;
	}


	// primaries
	// retourne un tableau multidimensionnel avec toutes les ids de lignes séparés par le nom de table
	public function primaries():array
	{
		$return = [];

		foreach ($this->arr() as $value)
		{
			$id = $value->primary();
			$table = $value->table()->name();

			if(!array_key_exists($table,$return))
			$return[$table] = [];

			$return[$table][] = $id;
		}

		return $return;
	}


	// ids
	// retourne un tableau multidimensionnel avec tous les ids de lignes séparés par le nom de table
	public function ids():array
	{
		return $this->primaries();
	}


	// add
	// ajoute une ou plusieurs rows dans l'objet
	// valeurs doivent être des objets row
	// possible de fournir un ou plusieurs objets rows (ou row)
	// deux objets identiques ne peuvent pas être ajoutés dans rows
	// des objets de différentes tables peuvent être ajoutés dans rowsIndex
	// n'appel pas sequential (checkAfter) après chaque ajout, c'est inutile
	public function add(...$values):parent
	{
		$this->checkAllowed('add');
		$values = $this->prepareValues(...$values);
		$data =& $this->arr();

		foreach ($values as $value)
		{
			if(!$value instanceof Row)
			static::throw('requiresRow');

			if(!in_array($value,$data,true))
			$data[] = $value;

			else
			static::throw('alreadyIn');
		}

		return $this;
	}


	// filterByTable
	// retourne un objet rows avec toutes les lignes étant dans la table fourni en argument
	// l'objet retourné est dans la bonne classe rows pour la table
	public function filterByTable($table):?Rows
	{
		$return = null;
		$db = $this->tableDb($table);

		if(!empty($db))
		{
			$table = $db->table($table);
			$classe = $table->classe()->rows();

			if(!empty($classe))
			{
				$return = new $classe();
				foreach ($this->arr() as $value)
				{
					if($value->sameTable($table))
					$return->add($value);
				}
			}

			else
			static::throw('noClass');
		}

		return $return;
	}


	// groupByTable
	// retourne un tableau multidimensionnel avec toutes les lignes séparés par le nom de table
	// le id est toujours utilisé comme clé des tableaux
	// les objets retournés retournés sont dans les bonnes classes rows pour les tables
	public function groupByTable():array
	{
		$return = [];

		foreach ($this->arr() as $value)
		{
			$id = $value->primary();
			$table = $value->table();
			$tableName = $table->name();

			if(!array_key_exists($tableName,$return))
			{
				$classe = $table->classe()->rows();

				if(!empty($classe))
				$return[$tableName] = new $classe();

				else
				static::throw('noClass');
			}

			$return[$tableName]->add($value);
		}

		return $return;
	}


	// tables
	// retourne un tableau avec toutes les tables contenus dans l'objet
	public function tables():array
	{
		$return = [];

		foreach ($this->arr() as $value)
		{
			$table = $value->table();

			if(!in_array($table,$return,true))
			$return[] = $table;
		}

		return $return;
	}


	// tableNames
	// retourne un tableau avec tous les noms de tables présent dans l'objet
	public function tableNames():array
	{
		$return = [];

		foreach ($this->tables() as $value)
		{
			$return[] = $value->name();
		}

		return $return;
	}


	// tableDb
	// retourne l'objet db pour la première ligne d'une table spécifiée en argument
	public function tableDb($table):?Db
	{
		$return = null;

		foreach ($this->data as $value)
		{
			if($value->sameTable($table))
			{
				$return = $value->db();
				break;
			}
		}

		return $return;
	}


	// tableRemove
	// enlève de l'objet toutes les lignes appartenant à une table
	public function tableRemove($table):self
	{
		$table = $this->filterByTable($table);

		foreach ($table as $value)
		{
			$this->remove($value);
		}

		return $this->checkAfter();
	}


	// tableUnlink
	// délie et enlève toutes les lignes appartenant à une table
	// les lignes déliés sont retirés de cet objet, de rows table et prennent un statut inutilisable
	public function tableUnlink($table):self
	{
		$table = $this->filterByTable($table);

		foreach ($table as $value)
		{
			$this->remove($value);
			$value->unlink();
		}

		return $this->checkAfter();
	}


	// tableUpdate
	// sauve toutes les lignes d'une table dans l'objet
	// retourne un tableau avec les résultats pour chaque ligne
	public function tableUpdate($table):array
	{
		$return = $this->filterByTable($table)->update();
		$this->checkAfter();

		return $return;
	}


	// sequential
	// ramène les clés de la map séquentielle, numérique et en ordre
	// ne clone pas l'objet comme les méthodes sort
	public function sequential():parent
	{
		$this->checkAllowed('sequential');
		$data =& $this->arr();
		$data = array_values($data);

		return $this;
	}


	// alive
	// vérifie l'existence des ligne, fait une requête par table
	// retourne faux si une des tables n'a pas les lignes
	public function alive():bool
	{
		$return = false;

		foreach ($this->groupByTable() as $table => $rows)
		{
			$return = $rows->alive();

			if($return === false)
			break;
		}

		return $return;
	}


	// refresh
	// rafraichit les lignes, fait une requête par table
	public function refresh():Rows
	{
		foreach ($this->groupByTable() as $table => $rows)
		{
			$rows->refresh();
		}

		return $this->checkAfter();
	}
}
?>