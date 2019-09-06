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

// syntax
// class with static methods to generate SQL syntax strings (compatible with MySQL and MariaDB)
class Syntax extends Main\Root
{
	// trait
	use Base\_option;
	use Base\_shortcut;


	// config
	public static $config = [
		'option'=>[ // tableau d'options
			'primary'=>'id', // nom de la clé primaire
			'prepare'=>true, // prépare la valeur ou non, priorité sur quote
			'quote'=>true, // quote la valeur
			'quoteCallable'=>null, // callable pour quote, plutôt que celle par défaut
			'tick'=>false, // tick la valeur
			'makeSelectFrom'=>true, // fait des select à partir de insert, update ou delete, la base pour le rollback
			'createNotExists'=>false, // mot à ajouter pour requête create
			'dropExists'=>false, // mot à ajouter pour requête drop
			'charset'=>'utf8mb4', // charset
			'collate'=>'utf8mb4_general_ci', // collate
			'engine'=>'MyISAM', // engine pour create
			'defaultCallable'=>null, // callable pour aller chercher les default selon la table
			'default'=>[
				'what'=>'*', // what par défaut
				'where'=>['active'=>1], // where par défaut
				'order'=>['id'=>'ASC'], // ordre par défaut
				'limit'=>PHP_INT_MAX]], // limite par défaut
		'prepared'=>[
			'replace'=>':', // séparateur pour la chaîne replace
			'random'=>[6,'alpha']], // argument pour random
		'key'=>[ // types de clés
			'key'=>'KEY',
			'unique'=>'UNIQUE KEY',
			'primary'=>'PRIMARY KEY'],
		'col'=>[ // types de colonnes et attributs par défaut
			'int'=>['length'=>11,'null'=>true],
			'tinyint'=>['length'=>1,'null'=>true],
			'smallint'=>['length'=>5,'null'=>true],
			'mediumint'=>['length'=>10,'null'=>true],
			'bigint'=>['length'=>20,'null'=>true],
			'char'=>['length'=>255,'null'=>true],
			'varchar'=>['length'=>255,'null'=>true],
			'text'=>['null'=>true],
			'tinytext'=>['null'=>true],
			'mediumtext'=>['null'=>true],
			'longtext'=>['null'=>true]],
		'what'=>[ // config pour what
			'separator'=>'()', // séparateur pour fonction
			'default'=>['parenthesis'=>true], // function par défaut
			'function'=>[ // liste de functions avec syntaxe spéciale
				'distinct'=>['key'=>'DISTINCT','parenthesis'=>false]]],
		'where'=>[ // config pour where
			'symbol'=>['>'=>'>','>='=>'>=','<'=>'<','<='=>'<=','!'=>'!=','!='=>'!=','='=>'='], // symbol
			'separator'=>[ // sépateur par défaut et tous les séparateurs
				'default'=>'AND',
				'all'=>['AND','&&','OR','||','XOR']],
			'likeQuoteChar'=>['%','_','\\'], // caractère à quote pour un like
			'method'=>[ // méthodes callback pour where à 3 arguments
				'in'=>[self::class,'whereIn'],
				'notIn'=>[self::class,'whereIn'],
				'between'=>[self::class,'whereBetween'],
				'notBetween'=>[self::class,'whereBetween'],
				'findInSet'=>[self::class,'whereFind'],
				'findInSetOrNull'=>[self::class,'whereFindOrNull'],
				'notFindInSet'=>[self::class,'whereFind'],
				'like'=>[self::class,'whereLike'],
				'%like'=>[self::class,'whereLike'],
				'like%'=>[self::class,'whereLike'],
				'notLike'=>[self::class,'whereLike'],
				'%notLike'=>[self::class,'whereLike'],
				'notLike%'=>[self::class,'whereLike'],
				'year'=>[self::class,'whereDate'],
				'month'=>[self::class,'whereDate'],
				'day'=>[self::class,'whereDate'],
				'hour'=>[self::class,'whereDate'],
				'minute'=>[self::class,'whereDate']]],
		'order'=>[ // config pour order
			'default'=>'ASC', // direction par défaut
			'direction'=>['ASC','DESC'],
			'method'=>[ // méthodes callback pour order à 3 arguments
				'find'=>[self::class,'orderFind'],
				'notFind'=>[self::class,'orderFind'],
				'findInSet'=>[self::class,'orderFind'],
				'notFindInSet'=>[self::class,'orderFind']]],
		'set'=>[ // config pour insertSet et updateSet
			'method'=>[
				'replace'=>[self::class,'setReplace']]],
		'query'=>[
			'select'=>[ // config pour select
				'what'=>['required'=>true,'key'=>0],
				'table'=>['required'=>true,'word'=>'FROM','key'=>1],
				'join'=>['word'=>'JOIN'],
				'innerJoin'=>['word'=>'INNER JOIN'],
				'outerJoin'=>['word'=>'LEFT OUTER JOIN'],
				'where'=>['word'=>'WHERE','key'=>2],
				'group'=>['word'=>'GROUP BY'],
				'order'=>['word'=>'ORDER BY','key'=>3],
				'limit'=>['word'=>'LIMIT','key'=>4]],
			'show'=>[ // config pour show
				'what'=>['required'=>true,'key'=>0],
				'table'=>['word'=>'FROM','key'=>1],
				'where'=>['key'=>2],
				'order'=>['word'=>'ORDER BY','key'=>3],
				'limit'=>['word'=>'LIMIT','key'=>4]],
			'insert'=>[ // config pour insert
				'table'=>['required'=>true,'word'=>'INTO','key'=>0],
				'insertSet'=>['required'=>true,'key'=>1]],
			'update'=>[ // config pour update
				'table'=>['required'=>true,'key'=>0],
				'updateSet'=>['required'=>true,'word'=>'SET','key'=>1],
				'where'=>['required'=>true,'word'=>'WHERE','key'=>2],
				'order'=>['word'=>'ORDER BY','key'=>3],
				'limit'=>['word'=>'LIMIT','key'=>4]],
			'delete'=>[ // config pour delete
				'table'=>['required'=>true,'word'=>'FROM','key'=>0],
				'where'=>['required'=>true,'word'=>'WHERE','key'=>1],
				'order'=>['word'=>'ORDER BY','key'=>2],
				'limit'=>['word'=>'LIMIT','key'=>3]],
			'create'=>[ // config pour create
				'table'=>['required'=>true,'word'=>'TABLE','key'=>0],
				'createCol'=>['key'=>1,'required'=>true,'comma'=>true,'parenthesisOpen'=>true],
				'createKey'=>['key'=>2]],
			'alter'=>[ // config pour alter
				'table'=>['required'=>true,'word'=>'TABLE','key'=>0],
				'addCol'=>['key'=>1,'comma'=>true],
				'addKey'=>['key'=>2,'comma'=>true],
				'alterCol'=>['key'=>3,'comma'=>true],
				'dropCol'=>['key'=>4,'comma'=>true],
				'dropKey'=>['key'=>5],
				'sql'=>['key'=>6]],
			'truncate'=>[ // config pour truncate
				'table'=>['required'=>true,'word'=>'TABLE','key'=>0]],
			'drop'=>[ // config pour drop
				'table'=>['required'=>true,'word'=>'TABLE','key'=>0]]]
	];


	// prepared
	protected static $prepared = 0; // valeur qui s'incrémente à chaque appel de prepare


	// _construct
	// pas de possibilité de construire l'objet
	private function __construct()
	{
		return;
	}


	// isQuery
	// retourne vrai si la valeur est un type de query
	public static function isQuery($value):bool
	{
		return (is_string($value) && array_key_exists($value,static::$config['query']) && is_array(static::$config['query'][$value]))? true:false;
	}


	// isQuote
	// retourne vrai si la valeur est quote
	public static function isQuote($value):bool
	{
		return (is_string($value) && Base\Str::isStartEnd("'","'",$value))? true:false;
	}


	// hasTickOrSpace
	// retourne vrai si la valeur a un tick ou un espace
	public static function hasTickOrSpace($value):bool
	{
		return (is_string($value) && (strpos($value,' ') !== false || strpos($value,'`') !== false))? true:false;
	}


	// isTick
	// retourne vrai si la valeur est enrobbé de tick
	public static function isTick($value):bool
	{
		$return = false;

		if(is_string($value) && strlen($value) > 2)
		{
			if(static::hasDot($value))
			$return = static::isTick(Base\Str::explodeIndex(-1,'.',$value,2,true,true));

			elseif(Base\Str::isStartEnd('`','`',$value))
			$return = true;
		}

		return $return;
	}


	// isParenthesis
	// retourne vrai si la valeur est une parenthese
	// openClose permet de spécifier si on recherche open ou close parenthesis
	public static function isParenthesis($value,?bool $openClose=null):bool
	{
		$return = false;

		if($openClose === true && $value === '(')
		$return = true;

		elseif($openClose === false && $value === ')')
		$return = true;

		elseif($openClose === null && in_array($value,['(',')'],true))
		$return = true;

		return $return;
	}


	// isKey
	// retourne vrai si le type de clé existe
	public static function isKey($value):bool
	{
		return (is_string($value) && array_key_exists($value,static::$config['key']))? true:false;
	}


	// isColType
	// retourne vrai si le type de colonne existe
	public static function isColType($value):bool
	{
		return (is_string($value) && array_key_exists($value,static::$config['col']))? true:false;
	}


	// isWhereSymbol
	// retourne vrai si la valeur est un symbol where
	public static function isWhereSymbol($value):bool
	{
		return (is_string($value) && array_key_exists($value,static::$config['where']['symbol']))? true:false;
	}


	// isWhereSeparator
	// retourne vrai si la valeur est un séparateur where
	public static function isWhereSeparator($value):bool
	{
		return (is_string($value) && in_array(strtoupper($value),static::$config['where']['separator']['all'],true))? true:false;
	}


	// isWhereTwo
	// retourne vrai si la valeur est une des méthodes whereTwo
	public static function isWhereTwo($value):bool
	{
		return (in_array($value,[null,'null','notNull',false,'empty',true,'notEmpty'],true) || is_int($value))? true:false;
	}


	// isOrderDirection
	// retourne vrai si la valeur est une direction
	public static function isOrderDirection($value):bool
	{
		return (is_string($value) && in_array(strtoupper($value),static::$config['order']['direction'],true))? true:false;
	}


	// isReturnSelect
	// retourne vrai si la valeur de retour contient un select, par exemple après un insert, update ou select
	public static function isReturnSelect($value):bool
	{
		$return = false;

		if(is_array($value) && !empty($value['sql']) && array_key_exists('select',$value) && is_array($value['select']) && !empty($value['select']['sql']))
		$return = true;

		return $return;
	}


	// isReturnRollback
	// retourne vrai si la valeur de retour permet de préparer un rollback
	public static function isReturnRollback($value):bool
	{
		$return = false;

		if(static::isReturnSelect($value))
		{
			if(array_key_exists('table',$value['select']) && is_string($value['select']['table']) && array_key_exists('id',$value['select']) && is_int($value['select']['id']))
			$return = true;
		}

		return $return;
	}


	// isReturnTableId
	// retourne vrai si la valeur de retour contient table et id
	public static function isReturnTableId($value):bool
	{
		$return = false;

		if(is_array($value) && !empty($value['sql']))
		{
			if(array_key_exists('table',$value) && is_string($value['table']) && array_key_exists('id',$value) && is_int($value['id']))
			$return = true;
		}

		return $return;
	}


	// hasDot
	// retourne vrai si la valeur a un dot
	public static function hasDot($value):bool
	{
		return is_string($value) && strlen($value) > 1 && strpos($value,'.') > 0;
	}


	// hasQueryClause
	// retourne vrai si la valeur est un type de query et que key est supporté dans la valeur
	public static function hasQueryClause($value,$key):bool
	{
		$return = false;

		if(is_string($value) && is_string($key) && array_key_exists($value,static::$config['query']) && is_array(static::$config['query'][$value]))
		{
			if(array_key_exists($key,static::$config['query'][$value]))
			$return = true;
		}

		return $return;
	}


	// getQueryTypes
	// retourne tous les types de requêtes
	public static function getQueryTypes():array
	{
		return array_keys(static::$config['query']);
	}


	// getQueryRequired
	// retourne les champs requis du type de requête
	public static function getQueryRequired(string $value):?array
	{
		$return = null;

		if(array_key_exists($value,static::$config['query']) && is_array(static::$config['query'][$value]))
		{
			$return = [];

			foreach (static::$config['query'][$value] as $key => $value)
			{
				if(is_string($key) && is_array($value) && !empty($value['required']))
				{
					$return[] = $key;
				}
			}
		}

		return $return;
	}


	// getKeyWord
	// retourne le nom de la clé ou null
	public static function getKeyWord(string $value):?string
	{
		$return = null;

		if(array_key_exists($value,static::$config['key']))
		$return = static::$config['key'][$value];

		return $return;
	}


	// getColTypeAttr
	// retourne les attributs par défaut d'une colonne ou null
	public static function getColTypeAttr(string $value):?array
	{
		$return = null;

		if(array_key_exists($value,static::$config['col']))
		$return = static::$config['col'][$value];

		return $return;
	}


	// functionFormat
	// retourne le nom de la fonction formattée
	public static function functionFormat(string $value):string
	{
		return strtoupper($value);
	}


	// getWhatFunction
	// retourne la function what si existante
	public static function getWhatFunction(string $value):?array
	{
		$return = null;

		if(!empty($value))
		{
			$return = static::$config['what']['default'];

			if(array_key_exists($value,static::$config['what']['function']))
			$return = Base\Arr::plus($return,static::$config['what']['function'][$value]);
			else
			$return['key'] = static::functionFormat($value);
		}

		return $return;
	}


	// getWhereSymbol
	// retourne le symbol where ou null
	public static function getWhereSymbol(string $value):?string
	{
		return (array_key_exists($value,static::$config['where']['symbol']))? static::$config['where']['symbol'][$value]:null;
	}


	// getWhereMethod
	// retourne la callable d'une méthode where
	public static function getWhereMethod(string $value):?callable
	{
		return (array_key_exists($value,static::$config['where']['method']) && static::classIsCallable(static::$config['where']['method'][$value]))? static::$config['where']['method'][$value]:null;
	}


	// getWhereSeparator
	// retourne le séparateur pour where, si pas de valeur retourner le séparateur par défaut
	public static function getWhereSeparator(?string $value=null):string
	{
		return (is_string($value) && static::isWhereSeparator($value))? strtoupper($value):static::$config['where']['separator']['default'];
	}


	// getOrderDirection
	// retourne la direction d'ordre ou la direction d'ordre par défaut
	public static function getOrderDirection($value=null):string
	{
		$return = static::$config['order']['default'];

		if(is_string($value) && static::isOrderDirection($value))
		$return = $value;

		$return = strtoupper($return);

		return $return;
	}


	// invertOrderDirection
	// retourne la direction d'ordre inverse à celle donné en argument
	public static function invertOrderDirection($value=null):string
	{
		$return = static::getOrderDirection($value);
		$directions = static::$config['order']['direction'];

		if(!empty($return) && !empty($directions))
		{
			$key = array_search($return,$directions,true);
			unset($directions[$key]);
			$return = current($directions);
		}

		return $return;
	}


	// getOrderMethod
	// retourne la callable d'une méthode order
	public static function getOrderMethod(string $value):?callable
	{
		return (array_key_exists($value,static::$config['order']['method']) && static::classIsCallable(static::$config['order']['method'][$value]))? static::$config['order']['method'][$value]:null;
	}


	// getSetMethod
	// retourne la callable d'une méthode set
	public static function getSetMethod(string $value):?callable
	{
		return (array_key_exists($value,static::$config['set']['method']) && static::classIsCallable(static::$config['set']['method'][$value]))? static::$config['set']['method'][$value]:null;
	}


	// getQueryWord
	// retourne le mot de la query ou de la clé de la query
	// option dropExists et createNotExists
	public static function getQueryWord(string $type,?string $key=null,?array $option=null):?string
	{
		$return = null;

		if(array_key_exists($type,static::$config['query']))
		{
			if($key === null)
			$return = strtoupper($type);

			elseif(is_string($key))
			{
				$array = static::$config['query'][$type];

				if(!empty($array[$key]['word']))
				{
					$return = $array[$key]['word'];

					if($type === 'drop' && !empty($option['dropExists']))
					$return .= ' IF EXISTS';

					elseif($type === 'create' && !empty($option['createNotExists']))
					$return .= ' IF NOT EXISTS';
				}
			}
		}

		return $return;
	}


	// getReturn
	// retourne le tableau de retour
	public static function getReturn(?array $return=null):array
	{
		return (is_array($return) && array_key_exists('sql',$return) && is_string($return['sql']))? $return:['sql'=>''];
	}


	// returnMerge
	// merge des tableaux return ensemble
	public static function returnMerge(array ...$values):array
	{
		$return = ['sql'=>''];

		foreach ($values as $value)
		{
			foreach ($value as $k => $v)
			{
				if(is_string($k))
				{
					if($k === 'sql' && is_string($v))
					$return['sql'] .= $v;

					elseif($k === 'prepare' && is_array($v))
					$return['prepare'] = (empty($return['prepare']))? $v:Base\Arr::replace($return['prepare'],$v);

					else
					$return[$k] = $v;
				}
			}
		}

		return $return;
	}


	// tick
	// enrobe la valeur de tick
	// si la chaine contient un point, alors seul le dernier element aura un tick
	// transforme les shortcuts
	// pas de tick autour d'une valeur enrobbé de paranthèse
	public static function tick(string $return,?array $option=null):string
	{
		$return = static::shortcut($return);

		if(strlen($return) && !static::isTick($return))
		{
			if(static::hasDot($return) !== false)
			{
				$x = Base\Str::explodeTrimClean('.',$return,2);
				$key = Base\Arr::keyLast($x);

				if(strlen($x[$key]))
				$x[$key] = Base\Str::wrapStartEnd('`','`',$x[$key]);

				$return = implode('.',$x);
			}

			elseif(!Base\Str::isStartEnd('(',')',$return) && strpos($return,'@') !== 0)
			$return = Base\Str::wrapStartEnd('`','`',$return);

			if(!empty($return) && !empty($option))
			{
				if(!empty($option['function']))
				$return = static::functionFormat($option['function'])."($return)";

				if(!empty($option['binary']))
				$return = 'BINARY '.$return;
			}
		}

		return $return;
	}


	// untick
	// dérobe la valeur de tick
	public static function untick(string $return):string
	{
		if(strlen($return) && static::isTick($return))
		{
			if(static::hasDot($return))
			{
				$x = Base\Str::explodeTrimClean('.',$return,2);

				foreach ($x as $i => $v)
				{
					$x[$i] = Base\Str::stripStartEnd('`','`',$x[$i]);
				}

				$return = implode('.',$x);
			}

			elseif(Base\Str::isStartEnd('`','`',$return))
			$return = Base\Str::stripStartEnd('`','`',$return);
		}

		return $return;
	}


	// quote
	// quote une valeur
	// les variables scalar non string ne sont pas quote
	// possible de passer une callable pour quote, sinon utilise str quote et addslashes
	// possible de remplacer les double \\ par \ (false par défaut)
	public static function quote($value,?callable $callable=null,bool $replaceDoubleEscape=false)
	{
		$return = '';

		if(is_scalar($value))
		{
			if(is_string($value))
			{
				if(static::classIsCallable($callable))
				$return = $callable($value);

				else
				{
					$value = addslashes($value);
					$return = Base\Str::quote($value,false);
				}

				if(is_string($return) && $replaceDoubleEscape === true)
				$return = str_replace('\\\\','\\',$return);
			}

			else
			$return = $value;
		}

		return $return;
	}


	// quoteSet
	// construit un champ set séparé par ,
	// chaque element est envoyé à la méthode quote
	public static function quoteSet(array $value,?callable $callable=null):string
	{
		$return = '';

		if(Base\Arr::isSet($value))
		{
			foreach ($value as $v)
			{
				$return .= (strlen($return))? ',':'';
				$return .= static::quote($v,$callable);
			}
		}

		return $return;
	}


	// unquote
	// unquote une valeur
	public static function unquote(string $return):string
	{
		return Base\Str::unquote(stripslashes($return),true,false);
	}


	// parenthesis
	// enrobe la valeur de parenthese si pas vide
	public static function parenthesis(string $return):string
	{
		return (strlen($return))? "($return)":'';
	}


	// comma
	// retourne une virgule si la valeur n'est pas vide
	public static function comma(string $value,bool $space=true):string
	{
		return (strlen($value))? ($space === true)? ', ':',':'';
	}


	// whereSeparator
	// retourne le séparateur where entouré d'espaces si la valeur n'est pas string vide
	public static function whereSeparator(?string $value=null,?string $separator=null,bool $space=true):string
	{
		$return = '';

		if($value === null || strlen($value))
		{
			$return = static::getWhereSeparator($separator);

			if($space === true)
			$return = ' '.$return.' ';
		}

		return $return;
	}


	// boolNull
	// prepare des valeurs bools et null
	// retourne int ou string
	public static function boolNull($value)
	{
		$return = null;

		if($value === null)
		$return = 'NULL';

		elseif($value === true)
		$return = 1;

		elseif($value === false)
		$return = 0;

		return $return;
	}


	// prepare
	// retourne un tableau avec les strings prepare et replace ou null
	// un nombre est ajouté à la fin de la clé préparé, ce nombre est incrémenté à chaque appel réussi
	public static function prepare():?array
	{
		$return = null;
		$prepare = Base\Str::random(...static::$config['prepared']['random']);

		if(!empty($prepare))
		{
			$prepare .= (string) static::$prepared;
			static::$prepared++;
		}

		if(is_string($prepare))
		$return = [$prepare,static::$config['prepared']['replace'].$prepare];

		return $return;
	}


	// prepareValue
	// prépare la valeur avant d'append
	public static function prepareValue($return)
	{
		if(is_bool($return) || $return === null)
		$return = static::boolNull($return);

		elseif(Base\Arr::isSet($return))
		$return = static::makeSet($return);

		elseif(is_array($return) || is_object($return) || is_resource($return))
		$return = Base\Str::cast($return);

		return $return;
	}


	// value
	// ajoute une valeur dans sql et une slice dans prepare
	// peut retourner une valeur préparé, quote ou tick
	// possible de mettre des quoteChar
	public static function value($value,?array $return=null,?array $option=null):array
	{
		$return = static::getReturn($return);

		if(is_bool($value) || $value === null)
		{
			$option['prepare'] = false;
			$option['quote'] = false;
			$option['tick'] = false;
		}

		elseif(empty($option['prepare']) && empty($option['tick']) && !isset($option['quote']))
		$option['quote'] = true;

		$value = static::prepareValue($value);

		if(is_int($value) || is_float($value))
		$return['sql'] .= $value;

		elseif(is_string($value))
		{
			$sql = $value;

			if(!empty($option['prepare']))
			{
				$prepare = static::prepare();
				if(!empty($prepare))
				{
					if(!empty($option['quoteChar']))
					$value = Base\Str::quoteChar($value,$option['quoteChar']);

					$sql = $prepare[1];
					$return['prepare'][$prepare[0]] = $value;
				}
			}

			else
			{
				if(!empty($option['tick']))
				$sql = static::tick($value);

				elseif(!empty($option['quote']))
				$sql = static::quote($value,$option['quoteCallable'] ?? null);

				if(!empty($option['quoteChar']))
				$sql = Base\Str::quoteChar($sql,$option['quoteChar']);
			}

			if(!empty($option['function']))
			$sql = static::functionFormat($option['function'])."($sql)";

			$return['sql'] .= $sql;
		}

		return $return;
	}


	// valueSet
	// append les valeurs scalar d'un tableau
	// construit un set avec les valeurs string quotés
	public static function valueSet(array $value,?array $return=null,?array $option=null):array
	{
		$return = static::getReturn($return);

		if(Base\Arr::isSet($value))
		{
			$i = 0;
			foreach ($value as $v)
			{
				if(is_scalar($v))
				{
					$return['sql'] .= ($i > 0)? static::comma($return['sql']):'';
					$return = static::value($v,$return,$option);
					$i++;
				}
			}
		}

		return $return;
	}


	// makeSet
	// crée un set à partir d'un tableau
	public static function makeSet(array $value):string
	{
		$return = '';

		foreach ($value as $v)
		{
			if(is_scalar($v))
			{
				$return .= static::comma($return,false);
				$return .= Base\Str::cast($v);
			}
		}

		return $return;
	}


	// makeDefault
	// gère le remplacement de la valeur par défaut, représenté par true
	// le remplacement se fait si la valeur est true ou true est présent dans le premier niveau d'un tableau
	// méthode protégé
	protected static function makeDefault(string $type,$return,array $option)
	{
		if(array_key_exists($type,$option))
		{
			$default = $option[$type];

			if($return === true)
			$return = $default;

			elseif(is_array($return) && $default !== null)
			{
				$do = true;
				while ($do === true)
				{
					$do = false;

					foreach ($return as $key => $value)
					{
						if(is_numeric($key) && $value === true)
						{
							if(is_scalar($default))
							$return[$key] = $default;

							elseif(is_array($default))
							{
								$return = Base\Arr::splice($key,$key,$return,$default);
								$do = true;
								break;
							}
						}
					}
				}
			}
		}

		return $return;
	}


	// addDefault
	// est cast à l'entrée
	// permet d'ajouter true dans un tableau si non existant
	// true représente la valeur par défaut
	public static function addDefault($return):array
	{
		$return = (array) Base\Obj::cast($return);
		$add = true;

		foreach ($return as $key => $value)
		{
			if(is_numeric($key) && $value === true)
			{
				$add = false;
				break;
			}
		}

		if($add === true)
		$return[] = true;

		return $return;
	}


	// removeDefault
	// enlève true dans un tableau si existant
	// true représente la valeur par défaut
	public static function removeDefault($return):array
	{
		$return = (array) $return;

		foreach ($return as $key => $value)
		{
			if(is_numeric($key) && $value === true)
			unset($return[$key]);
		}

		return $return;
	}


	// sql
	// décortique une entrée sql
	// utilisé pour simplement ajouté du sql à la requête
	public static function sql($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		return $return;
	}


	// what
	// décortique une entrée what
	// une string est passé directement en sql
	public static function what($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($option['default']) && is_array($option['default']))
		$value = static::makeDefault('what',$value,$option['default']);

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			foreach (static::whatPrepare($value) as $prepare)
			{
				if(!empty($prepare) && is_string($prepare[0]))
				{
					$merge = [];
					$count = count($prepare);

					if($count === 1)
					$merge = static::whatOne($prepare[0],$option);

					elseif($count === 2 && is_string($prepare[1]))
					$merge = static::whatTwo($prepare[0],$prepare[1],$option);

					elseif($count === 3 && is_string($prepare[1]) && is_string($prepare[2]))
					$merge = static::whatThree($prepare[0],$prepare[1],$prepare[2],$option);

					if(array_key_exists('sql',$merge) && is_string($merge['sql']) && strlen($merge['sql']))
					{
						$return['sql'] .= static::comma($return['sql']);
						$return = static::returnMerge($return,$merge);
					}
				}
			}
		}

		return $return;
	}


	// whatPrepare
	// prépare plusieurs entrée de what
	// retourne un tableau multidimensionnel
	public static function whatPrepare(array $array,?array $option=null):array
	{
		$return = [];

		foreach ($array as $k => $v)
		{
			if(!empty($v))
			{
				if(is_numeric($k))
				{
					if(is_string($v))
					$return[] = [$v];

					elseif(is_array($v) && count($v) <= 3)
					$return[] = array_values($v);
				}

				elseif(is_string($k))
				{
					if(is_string($v))
					$return[] = [$v,$k];

					elseif(is_array($v) && count($v) <= 2)
					$return[] = Base\Arr::append(array_values($v),$k);
				}
			}
		}

		return $return;
	}


	// whatOne
	// construit une entrée what à une variable
	public static function whatOne(string $key,?array $option=null):array
	{
		$return = ['sql'=>''];

		if($key === '*')
		$return['sql'] .= $key;

		elseif(!empty($key))
		$return['sql'] .= static::tick($key);

		return $return;
	}


	// whatTwo
	// construit une entrée what à deux variables
	// si as finit par des paranthèses vides (), c'est considéré comme une function
	public static function whatTwo(string $key,string $as,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($key) && !empty($as))
		{
			$separator = static::$config['what']['separator'] ?? null;

			if(!empty($separator) && Base\Str::isEnd($separator,$as))
			$return = static::whatThree($key,Base\Str::stripEnd($separator,$as),$key,$option);

			else
			$return['sql'] .= static::tick($key).' AS '.static::tick($as);
		}

		return $return;
	}


	// whatThree
	// construit une entrée what à trois variables
	public static function whatThree(string $key,string $function,string $as,?array $option=null):array
	{
		$return = ['sql'=>''];
		$separator = static::$config['what']['separator'] ?? null;

		if(!empty($separator) && Base\Str::isEnd($separator,$function))
		$function = Base\Str::stripEnd($separator,$function);

		$function = static::getWhatFunction($function);

		if(!empty($key) && !empty($function) && !empty($as))
		{
			$as = ($as !== $key)? ' AS '.static::tick($as):'';
			$return['sql'] .= $function['key'];

			if($function['parenthesis'] === true)
			$return['sql'] .= static::parenthesis(static::tick($key)).$as;

			else
			$return['sql'] .= ' '.static::tick($key).$as;

			$return['cast'] = true;
		}

		return $return;
	}


	// whatFromWhere
	// retourne un tableau avec toutes les colonnes dans le where
	// possible d'ajouter un prefix avant le nom de chaque colonne
	// si where n'est pas array, retourne *
	public static function whatFromWhere($where,?string $prefix=null):array
	{
		$return = [];

		if(is_array($where))
		{
			$cols = static::whereCols($where);

			if(!empty($cols))
			{
				foreach ($cols as $col)
				{
					if(is_string($col))
					{
						$col = (is_string($prefix))? "$prefix.$col":$col;
						$return[] = $col;
					}
				}
			}
		}

		else
		$return[] = '*';

		return $return;
	}


	// table
	// décortique une entrée table
	// permet une seule table, selon l'index donné en deuxième argument
	// la table est tick si elle ne contient pas d'espace ni de tick
	// la table est retourné dans la clé table si elle ne contient pas d'espace
	public static function table(string $value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($value))
		{
			$tickOrSpace = static::hasTickOrSpace($value);
			$return['sql'] .= ($tickOrSpace === false)? static::tick($value):$value;

			if(strpos($value,' ') === false)
			$return['table'] = static::untick($value);
		}

		return $return;
	}


	// join
	// décortique une entrée join
	public static function join($value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$option['type'] = (empty($option['type']))? 'join':$option['type'];

		if(in_array($option['type'],['join','innerJoin','outerJoin'],true))
		{
			if(is_string($value))
			$return['sql'] .= $value;

			elseif(is_array($value) && !empty($value))
			{
				$table = Base\Arr::keysFirstValue(['table',0],$value);
				$on = Base\Arr::keysFirstValue(['on',1],$value);

				if(!empty($table) && !empty($on))
				{
					$table = static::table($table,$option);
					$on = static::where($on,$option);

					if(!empty($table['sql']) && !empty($on['sql']))
					{
						$on['sql'] = ' ON('.$on['sql'].')';

						if(array_key_exists('table',$table))
						unset($table['table']);

						if(array_key_exists('id',$on))
						unset($on['id']);

						$return = static::returnMerge($return,$table,$on);
					}
				}
			}
		}

		return $return;
	}


	// innerJoin
	// décortique une entrée inner join
	public static function innerJoin($value,?array $option=null):array
	{
		return static::join($value,Base\Arr::plus($option,['type'=>'innerJoin']));
	}


	// outerJoin
	// décortique une entrée outer join
	public static function outerJoin($value,?array $option=null):array
	{
		return static::join($value,Base\Arr::plus($option,['type'=>'outerJoin']));
	}


	// where
	// décortique une entrée where
	public static function where($value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$value = static::whereDefault($value,$option);

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			$where = static::wherePrepare($value,$option);
			$last = null;
			$toMerge = null;

			if(!empty($option['primary']))
			$return = Base\Arr::plus($return,static::wherePrimary($where,$option));

			foreach ($where as $prepare)
			{
				if(!empty($prepare) && is_scalar($prepare[0]))
				{
					$isSeparator = (count($prepare) === 1 && static::isWhereSeparator($prepare[0]))? true:false;
					if($isSeparator === true && empty($last))
					continue;

					$merge = [];
					$count = count($prepare);

					if($count === 1)
					$merge = static::whereOne($prepare[0],$option);

					elseif($count >= 2 && static::isWhereTwo($prepare[1]))
					$merge = static::whereTwo($prepare[0],$prepare[1],$option);

					elseif($count === 3 && is_string($prepare[1]))
					$merge = static::whereThree($prepare[0],$prepare[1],$prepare[2],$option);

					if(array_key_exists('sql',$merge) && is_string($merge['sql']) && strlen($merge['sql']))
					{
						if(!empty($toMerge))
						{
							$return = static::returnMerge($return,$toMerge);
							$toMerge = null;
						}

						if($isSeparator === true)
						$toMerge = $merge;

						else
						$return = static::returnMerge($return,$merge);
					}

					$last = $merge['sql'] ?? null;
				}
			}
		}

		return $return;
	}


	// whereDefault
	// gère la variable where par défaut
	public static function whereDefault($return,?array $option=null)
	{
		if(!empty($option['default']) && is_array($option['default']))
		$return = static::makeDefault('where',$return,$option['default']);

		if(!empty($option['primary']) && !empty($return) && !is_string($return))
		{
			if(is_array($return) && Base\Arr::onlyNumeric($return))
			$return = [$return];

			$return = (array) $return;

			foreach ($return as $key => $value)
			{
				if(is_numeric($key))
				{
					if(is_numeric($value))
					$return[$key] = [$option['primary'],'=',$value];

					elseif(is_array($value) && Base\Arr::onlyNumeric($value))
					{
						if(count($value) === 1)
						$return[$key] = [$option['primary'],'=',current($value)];
						else
						$return[$key] = [$option['primary'],'in',$value];
					}
				}
			}
		}

		return $return;
	}


	// wherePrepare
	// prépare plusieurs entrée du tableau where
	// gère les mots séparateurs et les ouvertures fermetures de paranthèses
	// retourne un tableau multidimensionnel
	public static function wherePrepare(array $array,?array $option=null):array
	{
		$return = [];
		$wasSeparator = null;
		$needSeparator = false;
		$parenthesisOpen = 0;
		$separator = static::getWhereSeparator();

		foreach ($array as $key => $value)
		{
			foreach (static::wherePrepareOne($key,$value,$option) as $v)
			{
				if(is_array($v) && !empty($v))
				{
					// séparateur ou parenthèse
					if(count($v) === 1)
					{
						if(static::isParenthesis($v[0]))
						{
							if($v[0] === '(')
							{
								$needSeparator = false;

								if($wasSeparator === null && !empty($return))
								$return[] = [$separator];

								$parenthesisOpen++;
							}

							elseif($v[0] === ')')
							{
								if($parenthesisOpen === 0)
								continue;
								else
								$parenthesisOpen--;
							}

							$wasSeparator = null;
						}

						elseif(static::isWhereSeparator($v[0]))
						{
							$wasSeparator = $v[0];
							$needSeparator = false;
						}
					}

					else
					{
						$wasSeparator = null;

						if($needSeparator === true)
						$return[] = [$separator];

						else
						$needSeparator = true;
					}

					$return[] = $v;
				}
			}
		}

		while ($parenthesisOpen > 0)
		{
			$return[] = [')'];
			$parenthesisOpen--;
		}

		return $return;
	}


	// wherePrepareOne
	// prépare une entrée du tableau where
	// retourne un tableau multidimensionnel
	public static function wherePrepareOne($key,$value,?array $option=null):array
	{
		$return = [];

		if(is_string($key))
		{
			if($value === null)
			$return[] = [$key,null];

			elseif($value === true)
			$return[] = [$key,true];

			elseif($value === false)
			$return[] = [$key,false];

			elseif(Base\Arr::isSet($value))
			$return[] = [$key,'in',$value];

			else
			$return[] = [$key,'=',$value];
		}

		elseif(is_numeric($key))
		{
			if(is_array($value) && !Base\Arr::onlyNumeric($value) && in_array(count($value),[2,3],true))
			$return[] = array_values($value);

			elseif(is_string($value))
			{
				if(static::isWhereSeparator($value))
				$return[] = [$value];

				elseif(static::isParenthesis($value))
				$return[] = [$value];
			}
		}

		return $return;
	}


	// whereCols
	// retourne toutes les colonnes uniques trouvés dans un tableau where
	public static function whereCols(array $array):array
	{
		$return = [];

		foreach (static::wherePrepare($array) as $key => $value)
		{
			if(is_array($value) && count($value) > 1)
			{
				$current = current($value);

				if(is_string($current) && !in_array($current,$return,true))
				$return[] = $current;
			}
		}

		return $return;
	}


	// whereAppend
	// append plusieurs valeurs where et retourne un grand tableau multidimensionnel utilisable par la méthode where pour générer le sql
	// les options par défaut sont utilisés
	public static function whereAppend(...$values)
	{
		$return = [];
		$merge = [];
		$option = static::option();

		foreach ($values as $key => $value)
		{
			$value = static::whereDefault($value,$option);

			if(is_array($value))
			{
				$prepare = static::wherePrepare($value,$option);

				if(!empty($prepare) && is_array($prepare))
				$merge[] = $prepare;
			}
		}

		if(!empty($merge))
		$return = Base\Arr::append(...$merge);

		return $return;
	}


	// wherePrimary
	// retourne la ou les id à partir d'un tableau where prepare
	// retourne aussi la mention si la primary est la seule chose dans where
	// les id sont cast en int qu'il soit scalar ou array
	public static function wherePrimary(array $array,?array $option=null):?array
	{
		$return = null;

		if(!empty($option['primary']))
		{
			foreach ($array as $key => $value)
			{
				if(is_array($value) && !empty($value[0]) && $value[0] === $option['primary'])
				{
					if(!empty($value[1]) && is_string($value[1]) && in_array($value[1],['=','in'],true))
					{
						if(!empty($value[2]) && (is_numeric($value[2]) || (is_array($value[2]) && Base\Arr::onlyNumeric($value[2]))))
						{
							$return = [];

							if(is_numeric($value[2]))
							$value[2] = (int) $value[2];

							if(is_array($value[2]))
							$value[2] = Base\Arr::cast($value[2]);

							$return['id'] = $value[2];
							$return['whereOnlyId'] = (count($array) === 1)? true:false;

							break;
						}
					}
				}
			}
		}

		return $return;
	}


	// whereOne
	// construit une entrée where à une variable
	public static function whereOne(string $key):array
	{
		$return = ['sql'=>''];

		if(static::isParenthesis($key))
		$return['sql'] .= $key;

		elseif(static::isWhereSeparator($key))
		$return['sql'] .= static::whereSeparator(null,$key);

		return $return;
	}


	// whereTwo
	// construit une entrée where à deux variables
	// whereTwo accepte maintenant un int aussi
	public static function whereTwo(string $key,$value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$tick = static::tick($key);

		if(is_scalar($value) || $value === null)
		{
			if(in_array($value,[null,'null'],true))
			$return['sql'] .= $tick.' IS NULL';

			elseif($value === 'notNull')
			$return['sql'] .= $tick.' IS NOT NULL';

			elseif(in_array($value,[false,'empty'],true))
			$return['sql'] .= static::parenthesis("$tick = '' OR $tick IS NULL");

			elseif(in_array($value,[true,'notEmpty'],true))
			$return['sql'] .= static::parenthesis("$tick != '' AND $tick IS NOT NULL");

			elseif(is_int($value))
			{
				$return['sql'] .= "$tick = ";
				$return = static::value($value,$return,$option);
			}
		}

		return $return;
	}


	// whereThreeMethod
	// parse la méthode utilisé dans where Three
	// la méthode peut être entre [], à ce moment pas de quote ni de prepare
	// la méthode peut être entre ``, à ce moment tick mais ne quote et ne prepare pas
	// si la méthode commence par une chaîne x,y,z|, analyse les caractères
	// support pour b binary, i insensitive et or or
	protected static function whereThreeMethod(string $method,?array $option=null)
	{
		$return = [];
		$option = (array) $option;

		if(strpos($method,'[') !== false && Base\Segment::isWrapped(null,$method))
		{
			$method = Base\Segment::strip(null,$method);
			$option['quote'] = false;
			$option['prepare'] = false;
		}

		elseif(strpos($method,'`') !== false && Base\Segment::isWrapped('``',$method))
		{
			$method = Base\Segment::strip('``',$method);
			$option['tick'] = true;
			$option['quote'] = false;
			$option['prepare'] = false;
		}

		if(strpos($method,'|') !== false)
		{
			$x = explode('|',$method);
			if(count($x) === 2 && !empty($x[1]))
			{
				foreach (explode(',',$x[0]) as $v)
				{
					if($v === 'b')
					$option['binary'] = true;

					elseif($v === 'l')
					$option['function'] = 'lower';

					elseif($v === 'u')
					$option['function'] = 'upper';

					elseif($v === 'or')
					$option['separator'] = 'or';
				}

				$method = $x[1];
			}
		}

		$return = ['method'=>$method,'option'=>$option];

		return $return;
	}


	// whereThree
	// construit une entrée where à trois variables
	public static function whereThree($key,string $method,$value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$parse = static::whereThreeMethod($method,$option);
		$method = $parse['method'];
		$option = $parse['option'];

		if(static::isWhereSymbol($method))
		{
			$symbol = static::getWhereSymbol($method);

			if($symbol === '=' && $value === null)
			$return = static::whereTwo($key,$value,$option);

			else
			{
				$return['sql'] .= static::tick($key,$option).' '.$symbol.' ';
				$return = static::value($value,$return,$option);
			}
		}

		else
		{
			$callable = static::getWhereMethod($method);

			if(!empty($callable))
			$return = $callable($key,$value,$method,$option);
		}

		return $return;
	}


	// whereIn
	// construit une entrée where in à trois variables
	public static function whereIn(string $key,$value,string $method='in',?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_scalar($value))
		$value = [$value];

		if(!empty($value) && Base\Arr::isSet($value))
		{
			$word = null;

			if($method === 'in')
			$word = 'IN';

			elseif($method === 'notIn')
			$word = 'NOT IN';

			if(is_string($word))
			{
				$return['sql'] .= static::tick($key,$option)." $word(";
				$return = static::valueSet($value,$return,$option);
				$return['sql'] .= ')';
			}
		}

		return $return;
	}


	// whereBetween
	// construit une entrée where between à trois variables
	// value doit être un tableau contenant deux valeurs -> min et max
	public static function whereBetween($key,array $value,string $method='between',?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_array($value) && count($value) === 2)
		{
			$word = null;
			$value = array_values($value);

			if($method === 'between')
			$word = 'BETWEEN';

			elseif($method === 'notBetween')
			$word = 'NOT BETWEEN';

			if(is_string($word))
			{
				$return = static::value($key,$return,$option);
				$return['sql'] .= " $word ";
				$return = static::value($value[0],$return,$option);
				$return['sql'] .= static::whereSeparator($return['sql'],'and');
				$return = static::value($value[1],$return,$option);
			}
		}

		return $return;
	}


	// whereFind
	// construit une entrée where find à trois variables
	// si l'argument est un tableau, un loop est construit
	public static function whereFind(string $key,$value,string $method='find',?array $option=null):array
	{
		$return = ['sql'=>''];
		$separator = static::getWhereSeparator($option['separator'] ?? null);
		$value = (array) $value;
		$word = null;

		if(in_array($method,['find','findInSet'],true))
		$word = 'FIND_IN_SET';

		elseif(in_array($method,['notFind','notFindInSet'],true))
		$word = '!FIND_IN_SET';

		if(is_string($word) && !empty($value))
		{
			$count = 0;

			foreach ($value as $v)
			{
				if(is_scalar($v))
				{
					$return['sql'] .= static::whereSeparator($return['sql'],$separator);
					$return['sql'] .= "$word(";
					$return = static::value($v,$return,$option);
					$return['sql'] .= ', '.static::tick($key,$option).')';
					$count++;
				}
			}

			if(!empty($return['sql']) && $count > 1)
			$return['sql'] = static::parenthesis($return['sql']);
		}

		return $return;
	}


	// whereFindOrNull
	// construit une entrée where findOrEmpty à trois variables
	// comme whereFind mais la différence est que chaque valeur accepte aussi null
	// si l'argument est un tableau, un loop est construit
	public static function whereFindOrNull(string $key,$value,string $method='find',?array $option=null):array
	{
		$return = ['sql'=>''];
		$separator = static::getWhereSeparator($option['separator'] ?? null);
		$method = 'find';
		$or = 'or';
		$value = (array) $value;

		foreach ($value as $v)
		{
			$merge = static::whereFind($key,$v,$method,$option);
			$merge2 = static::whereTwo($key,null);
			$new = ['sql'=>''];

			if(!empty($merge['sql']) && !empty($merge2['sql']))
			{
				$new = static::returnMerge($new,$merge);
				$new['sql'] .= static::whereSeparator($new['sql'],$or);
				$new = static::returnMerge($new,$merge2);
				$new['sql'] = static::parenthesis($new['sql']);

				if(!empty($new['sql']))
				{
					if(!empty($return['sql']))
					$return['sql'] .= static::whereSeparator($return['sql'],$separator);
					$return = static::returnMerge($return,$new);
				}
			}
		}

		return $return;
	}


	// whereLike
	// construit une entrée where like à trois variables
	// si l'argument est un tableau, un loop est construit
	// la méthode va quoteChar les caractères % et _, comme l'indique la document SQL
	public static function whereLike(string $key,$value,string $method='like',?array $option=null):array
	{
		$return = ['sql'=>''];
		$quoteChar = static::$config['where']['likeQuoteChar'];
		$option = Base\Arr::plus($option,['quoteChar'=>$quoteChar]);
		$separator = static::getWhereSeparator($option['separator'] ?? null);
		$value = (array) $value;
		$word = null;
		$concat = null;

		if(in_array($method,['like','%like','like%'],true))
		$word = 'LIKE';

		elseif(in_array($method,['notLike','%notLike','notLike%'],true))
		$word = 'NOT LIKE';

		if(in_array($method,['%like','%notLike'],true))
		$concat = "concat(@, '%')";

		elseif(in_array($method,['like','notLike'],true))
		$concat = "concat('%', @, '%')";

		elseif(in_array($method,['like%','notLike%'],true))
		$concat = "concat('%', @)";

		if(is_string($word) && is_string($concat) && is_array($value) && !empty($value))
		{
			$tick = static::tick($key,$option);
			$count = 0;
			$base = "$tick $word ";

			foreach ($value as $v)
			{
				if(is_scalar($v))
				{
					$sql = $base;

					$current = static::value($v,[],$option);

					if(!empty($current['sql']))
					{
						$current['sql'] = $base.str_replace('@',$current['sql'],$concat);

						$return['sql'] .= static::whereSeparator($return['sql'],$separator);
						$return = static::returnMerge($return,$current);
						$count++;
					}
				}
			}

			if(!empty($return['sql']) && $count > 1)
			$return['sql'] = static::parenthesis($return['sql']);
		}

		return $return;
	}


	// whereDate
	// construit une entrée where date à trois variables
	public static function whereDate(string $key,$value,string $method='month',?array $option=null):array
	{
		$return = ['sql'=>''];
		$separator = static::getWhereSeparator($option['separator'] ?? null);
		$arg = null;
		$floorCeil = null;
		$count = 0;

		if(is_numeric($value))
		$value = [(int) $value];

		elseif(is_array($value))
		$value = array_values($value);

		if(!empty($value))
		{
			foreach ($value as $v)
			{
				if(!is_array($v))
				$v = [$v];

				if($method === 'year')
				$floorCeil = Base\Date::floorCeilYear(...$v);

				elseif($method === 'month')
				$floorCeil = Base\Date::floorCeilMonth(...$v);

				elseif($method === 'day')
				$floorCeil = Base\Date::floorCeilDay(...$v);

				elseif($method === 'hour')
				$floorCeil = Base\Date::floorCeilHour(...$v);

				elseif($method === 'minute')
				$floorCeil = Base\Date::floorCeilMinute(...$v);

				if(!empty($floorCeil) && is_int($floorCeil['floor']) && is_int($floorCeil['ceil']))
				{
					$current = ['sql'=>''];
					$return['sql'] .= static::whereSeparator($return['sql'],$separator);

					$current['sql'] .= static::tick($key,$option).' >= ';
					$current = static::value($floorCeil['floor'],$current,$option);
					$current['sql'] .= ' AND '.static::tick($key).' <= ';
					$current = static::value($floorCeil['ceil'],$current,$option);
					$current['sql'] = static::parenthesis($current['sql']);

					$return = static::returnMerge($return,$current);
					$count++;
				}
			}

			if(!empty($return['sql']) && $count > 1)
			$return['sql'] = static::parenthesis($return['sql']);
		}

		return $return;
	}


	// group
	// décortique une entrée group
	public static function group($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			foreach ($value as $v)
			{
				if(is_string($v))
				{
					$return['sql'] .= static::comma($return['sql']);
					$return['sql'] .= static::tick($v);
				}
			}
		}

		return $return;
	}


	// order
	// décortique une entrée order
	public static function order($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($option['default']) && is_array($option['default']))
		$value = static::makeDefault('order',$value,$option['default']);

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			foreach (static::orderPrepare($value) as $prepare)
			{
				if(!empty($prepare) && is_scalar($prepare[0]))
				{
					$merge = [];
					$count = count($prepare);
					$prepare[0] = Base\Str::cast($prepare[0]);

					if($count === 1)
					$merge = static::orderOne($prepare[0],$option);

					elseif($count === 2 && is_scalar($prepare[1]))
					$merge = static::orderTwo($prepare[0],$prepare[1],$option);

					elseif($count === 3 && is_string($prepare[1]))
					$merge = static::orderThree($prepare[0],$prepare[1],$prepare[2],$option);

					if(array_key_exists('sql',$merge) && is_string($merge['sql']) && strlen($merge['sql']))
					{
						$return['sql'] .= static::comma($return['sql']);
						$return = static::returnMerge($return,$merge);
					}
				}
			}
		}

		return $return;
	}


	// orderPrepare
	// prépare plusieurs entrée de order
	// retourne un tableau multidimensionnel
	public static function orderPrepare(array $array,?array $option=null):array
	{
		$return = [];

		foreach ($array as $k => $v)
		{
			if(!empty($v))
			{
				if(is_numeric($k) && is_string($v))
				$return[] = [$v];

				elseif(is_string($k) && is_scalar($v))
				$return[] = [$k,$v];

				elseif(is_array($v) && count($v) <= 3)
				$return[] = array_values($v);
			}
		}

		return $return;
	}


	// orderOne
	// construit une entrée order à une variable
	public static function orderOne(string $key,?array $option=null):array
	{
		$return = ['sql'=>''];

		if($key === 'rand()')
		$return['sql'] = $key;

		elseif(!empty($key))
		$return['sql'] = static::tick($key).' '.static::getOrderDirection(null);

		return $return;
	}


	// orderTwo
	// construit une entrée order à deux variables
	public static function orderTwo(string $key,$value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($key) && is_scalar($value))
		$return['sql'] = static::tick($key).' '.static::getOrderDirection($value);

		return $return;
	}


	// orderThree
	// construit une entrée order à trois variables
	public static function orderThree(string $key,string $method,$value,?array $option=null):array
	{
		$return = [];

		$callable = static::getOrderMethod($method);
		if(!empty($callable))
		$return = $callable($key,$value,$method,$option);

		return $return;
	}


	// orderFind
	// construit une entrée order find à trois variables
	public static function orderFind(string $key,$value,string $method='find',?array $option=null):array
	{
		$return = [];
		$value = $value;
		$word = null;

		if(in_array($method,['find','findInSet'],true))
		$word = 'FIND_IN_SET';

		elseif(in_array($method,['notFind','notFindInSet'],true))
		$word = '!FIND_IN_SET';

		if(is_string($word) && is_string($value))
		{
			$return['sql'] = "$word(";
			$return['sql'] .= static::tick($value);
			$return['sql'] .= ', '.static::tick($key).')';
		}

		return $return;
	}


	// limit
	// décortique une entrée limit
	// la méthode utilise la syntaxe avec le mot OFFSET plutôt que la syntaxe courte
	public static function limit($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($option['default']) && is_array($option['default']))
		$value = static::makeDefault('limit',$value,$option['default']);

		if(is_int($value))
		$value = [$value];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			$value = static::limitPrepare($value);

			if(!empty($value) && array_key_exists(0,$value) && is_numeric($value[0]))
			{
				$return['sql'] .= Base\Str::cast($value[0]);

				if(array_key_exists(1,$value) && is_numeric($value[1]) && $value[1] > 0)
				{
					$return['sql'] .= ' OFFSET ';
					$return['sql'] .= Base\Str::cast($value[1]);
				}
			}
		}

		return $return;
	}


	// limitPrepare
	// prépare un tableau limit
	// si le tableau a 1 niveau et que la clé est numérique sans être 0, key est considéré comme page et current comme limit
	public static function limitPrepare(array $value):array
	{
		$return = [];
		$count = count($value);
		$array = null;

		if($count === 1)
		$array = static::limitPrepareOne(key($value),current($value));

		elseif($count === 2)
		$array = static::limitPrepareTwo($value);

		if(is_array($array))
		$return = Base\Arr::cast($array);

		return $return;
	}


	// limitPrepareOne
	// prépare un tableau limit si la valeur n'avait qu'une entrée
	// si le tableau a 1 niveau et que la clé est numérique sans être 0, key est considéré comme page et current comme limit
	public static function limitPrepareOne($key,$value):array
	{
		$return = [];

		if(is_int($key) && $key > 0 && is_int($value))
		{
			$return = Base\Nav::limitPage($key,$value);
			$return = array_reverse($return,false);
		}

		elseif(is_array($value))
		$return = array_values($value);

		elseif(is_numeric($value))
		$return = [$value];

		elseif(is_string($value))
		{
			$return = Base\Str::explodeTrimClean(',',$value,2);
			$return = array_reverse($return,false);
		}

		return $return;
	}


	// limitPrepareTwo
	// prépare un tableau limit si la valeur avait deux entrées
	public static function limitPrepareTwo(array $value):array
	{
		$return = [];
		$limit = $value['limit'] ?? Base\Arr::valueFirst($value);

		if(is_int($limit))
		{
			$offset = $value['offset'] ?? Base\Arr::valueLast($value);
			$page = $value['page'] ?? null;

			if(is_int($page))
			{
				$return = Base\Nav::limitPage($page,$limit);
				$return = array_reverse($return,false);
			}

			else
			$return = [$limit,$offset];
		}

		return $return;
	}


	// insertSet
	// créer une requête set pour INSERT avec noms de champs
	// possible de créer un insertSet vide en fournissant un tableau vide
	// ne permet pas de faire plusieurs insertions par requête, car il y a support pour callable dans set et aussi le rollback ne marchait pas correctement
	public static function insertSet($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(empty($value))
		$return['sql'] = '() VALUES ()';

		elseif(is_array($value))
		{
			$prepare = static::setPrepare($value);
			$fields = static::insertSetFields($prepare);
			$merge = static::setValues($prepare,false,$option);

			if(strlen($fields['sql']))
			{
				$return['sql'] .= static::parenthesis($fields['sql']);
				$return['sql'] .= ' ';
			}

			if(strlen($merge['sql']))
			{
				$return['sql'] .= 'VALUES ';
				$merge['sql'] = static::parenthesis($merge['sql']);
				$return = static::returnMerge($return,$merge);
			}
		}

		return $return;
	}


	// insertSetFields
	// génère le sql pour les fields lors d'une insertion
	public static function insertSetFields(array $value):array
	{
		$return = ['sql'=>''];

		foreach ($value as $array)
		{
			if(!empty($array) && is_string($array[0]))
			{
				$return['sql'] .= static::comma($return['sql']);
				$return['sql'] .= static::tick($array[0]);
			}
		}

		return $return;
	}


	// setPrepare
	// prépare plusieurs entrée de insert ou update set
	// retourne un tableau multidimensionnel
	public static function setPrepare(array $array):array
	{
		$return = [];

		foreach ($array as $k => $v)
		{
			if(is_string($k))
			$return[] = [$k,$v];

			elseif(is_numeric($k) && is_array($v) && in_array(count($v),[2,3,4],true))
			$return[] = array_values($v);
		}

		return $return;
	}


	// setValues
	// méthode utilisé par insertSet et updateSet pour générer les valeurs
	// si tick est true, alors met le nom du champ avant la valeur avec un =
	public static function setValues(array $value,bool $tick=false,?array $option=null):array
	{
		$return = ['sql'=>''];

		foreach ($value as $prepare)
		{
			if(!empty($prepare) && is_string($prepare[0]))
			{
				$merge = [];
				$count = count($prepare);

				if($count === 2)
				$merge = static::setOne($prepare[1],$option);

				elseif($count === 3 && is_string($prepare[1]))
				$merge = static::setTwo($prepare[1],$prepare[2],$option);

				elseif($count === 4 && is_string($prepare[1]))
				$merge = static::setThree($prepare[0],$prepare[1],$prepare[2],$prepare[3],$option);

				if(array_key_exists('sql',$merge) && is_string($merge['sql']) && strlen($merge['sql']))
				{
					$return['sql'] .= static::comma($return['sql']);

					if($tick === true)
					$return['sql'] .= static::tick($prepare[0]).' = ';

					$return = static::returnMerge($return,$merge);
				}
			}
		}

		return $return;
	}


	// updateSet
	// créer une requête de type SET pour update
	// ne supporte pas les valeurs par défaut
	public static function updateSet($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value))
		{
			$prepare = static::setPrepare($value);
			$return = static::setValues($prepare,true,$option);
		}

		return $return;
	}


	// setOne
	// construit une entrée insertSet ou updateSet à une variable
	public static function setOne($value,?array $option=null):array
	{
		return static::value($value,null,$option);
	}


	// setTwo
	// construit une entrée insertSet ou updateSet à deux variables
	public static function setTwo(string $method,$value,?array $option=null):array
	{
		$return = ['sql'=>''];

		$return['sql'] .= static::functionFormat($method);
		$return['sql'] .= '(';
		$return = static::value($value,$return,$option);
		$return['sql'] .= ')';

		return $return;
	}


	// setThree
	// construit une entrée insertSet ou updateSet à trois variables
	public static function setThree(string $key,string $method,$value1,$value2,?array $option=null):array
	{
		$return = [];

		$callable = static::getSetMethod($method);
		if(!empty($callable))
		{
			$merge = $callable($key,$value1,$value2,$method,$option);
			$return = static::returnMerge($return,$merge);
		}

		return $return;
	}


	// setReplace
	// construit une entrée set replace à quatres variables
	// permet de faire un remplacement sur une colonne
	public static function setReplace(string $key,$from,$to,string $method,?array $option=null):array
	{
		$return = ['sql'=>''];

		$return['sql'] .= 'REPLACE';
		$return['sql'] .= '(';
		$return['sql'] .= static::tick($key);
		$return['sql'] .= ',';
		$return = static::value($from,$return,$option);
		$return['sql'] .= ',';
		$return = static::value($to,$return,$option);
		$return['sql'] .= ')';

		return $return;
	}


	// col
	// obtient le sql de création d'une col
	public static function col(array $value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$option = static::option(Base\Arr::plus($option,['prepare'=>false]));
		$name = Base\Arr::keysFirstValue(['name',0],$value);
		$type = Base\Arr::keysFirstValue(['type',1],$value);

		if(is_string($name) && !empty($name) && is_string($type) && static::isColType($type))
		{
			$attr = Base\Arr::plus(static::getColTypeAttr($type),$value);
			$type = strtoupper($type);

			if(!empty($option['type']) && ($option['type'] === 'addCol' || $option['type'] === 'alterCol'))
			{
				if($option['type'] === 'addCol')
				$return['sql'] .= 'ADD COLUMN '.static::tick($name);

				elseif($option['type'] === 'alterCol')
				{
					$return['sql'] .= 'CHANGE '.static::tick($name).' ';
					$return['sql'] .= (!empty($attr['rename']) && is_string($attr['rename']))? static::tick($attr['rename']):static::tick($name);
				}
			}

			else
			$return['sql'] .= static::tick($name);

			// type
			$return['sql'] .= ' '.$type;

			// length
			if(!empty($attr['length']) && is_numeric($attr['length']))
			{
				$length = Base\Str::cast($attr['length']);
				$return['sql'] .= static::parenthesis($length);
			}

			// unsigned
			if(!empty($attr['unsigned']))
			$return['sql'] .= ' unsigned';

			// zerofill
			if(!empty($attr['zerofill']))
			$return['sql'] .= ' zerofill';

			// charset
			if(!empty($attr['charset']))
			{
				$attr['charset'] = ($attr['charset'] === true && !empty($option['charset']))? $option['charset']:$attr['charset'];
				if(is_string($attr['charset']))
				$return['sql'] .= ' CHARACTER SET '.$attr['charset'];
			}

			// collate
			if(!empty($attr['collate']))
			{
				$attr['collate'] = ($attr['collate'] === true && !empty($option['collate']))? $option['collate']:$attr['collate'];
				if(is_string($attr['collate']))
				$return['sql'] .= ' COLLATE '.$attr['collate'];
			}

			// null
			if(array_key_exists('null',$attr))
			{
				if($attr['null'] === false)
				$return['sql'] .= ' NOT NULL';

				elseif($attr['null'] === true)
				$return['sql'] .= ' NULL';
			}

			// default
			if(array_key_exists('default',$attr) && $attr['default'] !== null && $attr['default'] !== 'NULL')
			$return['sql'] .= ' DEFAULT '.static::value($attr['default'],null,$option)['sql'];

			// default null
			elseif(!empty($attr['null']))
			$return['sql'] .= ' DEFAULT NULL';

			// auto increment
			if(!empty($attr['autoIncrement']))
			$return['sql'] .= ' AUTO_INCREMENT';

			// after
			if(!empty($attr['after']) && is_string($attr['after']))
			$return['sql'] .= ' AFTER '.static::tick($attr['after']);
		}

		return $return;
	}


	// makeCol
	// méthode utilisé par createCol, addCol et alterCol
	public static function makeCol($value,array $option):array
	{
		$return = ['sql'=>''];

		if(!empty($option['type']) && in_array($option['type'],['createCol','addCol','alterCol'],true))
		{
			if(is_string($value))
			$return['sql'] .= $value;

			elseif(is_array($value) && !empty($value))
			{
				if(!Base\Column::is($value))
				$value = [$value];

				foreach ($value as $v)
				{
					if(is_array($v))
					{
						$col = static::col($v,$option);
						if(!empty($col['sql']))
						{
							$return['sql'] .= static::comma($return['sql']);
							$return = static::returnMerge($return,$col);
						}
					}
				}
			}
		}

		return $return;
	}


	// createCol
	// ajoute une col pour un create
	public static function createCol($value,?array $option=null):array
	{
		return static::makeCol($value,Base\Arr::plus($option,['type'=>'createCol']));
	}


	// addCol
	// ajoute une col pour un alter
	public static function addCol($value,?array $option=null):array
	{
		return static::makeCol($value,Base\Arr::plus($option,['type'=>'addCol']));
	}


	// alterCol
	// change un col pour un alter
	// il faut remettre l'ensemble des paramètres de la colonne (type, longueur)
	public static function alterCol($value,?array $option=null):array
	{
		return static::makeCol($value,Base\Arr::plus($option,['type'=>'alterCol']));
	}


	// dropCol
	// drop une col pour un alter
	public static function dropCol($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			foreach ($value as $v)
			{
				if(is_string($v) && !empty($v))
				{
					$return['sql'] .= static::comma($return['sql']);
					$return['sql'] .= 'DROP COLUMN '.static::tick($v);
				}
			}
		}

		return $return;
	}


	// key
	// obtient le sql de création d'une key
	public static function key(array $value,?array $option=null):array
	{
		$return = ['sql'=>''];
		$key = Base\Arr::keysFirstValue(['key',0],$value);

		if(!empty($key))
		{
			$keyWord = static::getKeyWord($key);

			if(!empty($keyWord))
			{
				$name = null;

				if($key === 'unique')
				{
					$name = Base\Arr::keysFirstValue(['name',1],$value);
					$col = Base\Arr::keysFirstValue(['col',2],$value);
					$col = ($col === null)? $name:$col;
				}

				else
				$col = Base\Arr::keysFirstValue(['col',1],$value);

				if(!empty($col) && (is_string($name) || $key !== 'unique'))
				{
					$v = '';
					$col = (array) $col;

					foreach ($col as $c)
					{
						if(is_string($c))
						{
							$v .= static::comma($v);
							$v .= static::tick($c);
						}
					}

					if(strlen($v))
					{
						if(!empty($option['type']) && $option['type'] === 'addKey')
						$return['sql'] .= 'ADD ';

						$return['sql'] .= $keyWord;

						if(is_string($name))
						$return['sql'] .= ' '.static::tick($name);

						$return['sql'] .= ' '.static::parenthesis($v);
					}
				}
			}
		}

		return $return;
	}


	// makeKey
	// méthode utilisé par createKey et alterKey
	public static function makeKey($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(!empty($option['type']) && in_array($option['type'],['createKey','addKey'],true))
		{
			if(is_string($value))
			$return['sql'] .= $value;

			elseif(is_array($value) && !empty($value))
			{
				if(!Base\Column::is($value))
				$value = [$value];

				foreach ($value as $v)
				{
					if(is_array($v))
					{
						$key = static::key($v,$option);
						if(!empty($key['sql']))
						{
							$return['sql'] .= static::comma($return['sql']);
							$return = static::returnMerge($return,$key);
						}
					}
				}
			}
		}

		return $return;
	}


	// createKey
	// ajoute une key pour un create
	public static function createKey($value,?array $option=null):array
	{
		return static::makeKey($value,Base\Arr::plus($option,['type'=>'createKey']));
	}


	// addKey
	// ajoute une key pour un alter
	public static function addKey($value,?array $option=null):array
	{
		return static::makeKey($value,Base\Arr::plus($option,['type'=>'addKey']));
	}


	// dropKey
	// drop une key pour un alter
	public static function dropKey($value,?array $option=null):array
	{
		$return = ['sql'=>''];

		if(is_string($value))
		$return['sql'] .= $value;

		elseif(is_array($value) && !empty($value))
		{
			foreach ($value as $v)
			{
				if(is_string($v) && !empty($v))
				{
					$return['sql'] .= static::comma($return['sql']);
					$return['sql'] .= 'DROP KEY '.static::tick($v);
				}
			}
		}

		return $return;
	}


	// createEnd
	// génère la fin de la string sql de create avec fermeture de parenthèse
	// option engine et charset
	public static function createEnd(?array $option=null):array
	{
		$return = ['sql'=>''];
		$return['sql'] .= ')';

		// engine
		if(!empty($option['engine']))
		$return['sql'] .= ' ENGINE='.$option['engine'];

		// charset
		if(!empty($option['charset']))
		$return['sql'] .= ' DEFAULT CHARSET='.$option['charset'];

		return $return;
	}


	// prepareDefault
	// arrange les défauts pour le tableau d'option
	// les défaut seulement pour requête select, update ou delete
	// possibilité d'avoir une option defaultCallable pour aller chercher des options pour une table via une callable
	// est cast par défaut à la sortie
	// méthode protégé
	protected static function prepareDefault(string $type,array $option):?array
	{
		$return = null;

		if(!empty($option['table']) && in_array($type,['select','update','delete'],true))
		{
			$return = [];

			if(!empty($option['default']) && is_array($option['default']))
			$return = $option['default'];

			if(!empty($option['defaultCallable']))
			{
				$default = $option['defaultCallable']($option['table']);
				$return = Base\Arr::plus($return,$default);
			}

			$return = Base\Obj::cast($return);
		}

		return $return;
	}


	// make
	// construit une requête sql à partir d'un type et un array de valeur
	public static function make(string $type,array $array,?array $option=null):?array
	{
		$return = null;
		$option = static::option($option);
		$array = Base\Obj::cast($array);
		$parses = static::makeParses($type,$array);

		if(!empty($parses))
		{
			$required = static::getQueryRequired($type);
			if(empty($required) || Base\Arr::keysExists($required,$parses))
			{
				$return = ['sql'=>static::getQueryWord($type,null,$option),'type'=>$type];
				$option['table'] = (!empty($parses['table']) && is_string($parses['table']) && !static::hasTickOrSpace($parses['table']))? static::untick($parses['table']):null;
				$option['default'] = static::prepareDefault($type,$option);

				if(!empty($array['prepare']))
				$return['prepare'] = $array['prepare'];

				foreach ($parses as $key => $value)
				{
					$word = static::getQueryWord($type,$key,$option);
					$merge = static::$key($value,$option);
					$param = static::$config['query'][$type][$key];

					if(array_key_exists('sql',$merge))
					{
						if(strlen($merge['sql']))
						{
							if(!empty($comma))
							$return['sql'] .= static::comma($return['sql']);

							elseif(!empty($return['sql']))
							$return['sql'] .= ' ';

							if(!empty($param['parenthesisOpen']))
							$return['sql'] .= '(';

							if(!empty($word))
							$return['sql'] .= $word.' ';

							$return = static::returnMerge($return,$merge);

							$comma = (!empty($param['comma']))? true:false;
						}
					}

					else
					{
						$return = null;
						break;
					}
				}

				if($return !== null)
				{
					if($type === 'create')
					$return = static::returnMerge($return,static::createEnd($option));

					elseif(in_array($type,['insert','update','delete'],true) && !empty($option['makeSelectFrom']))
					{
						$select = static::makeSelectFrom($type,$array,$option);
						if(!empty($select))
						$return['select'] = $select;
					}
				}
			}
		}

		return $return;
	}


	// makeParses
	// parse l'ensemble d'un tableau pour un type de requête
	public static function makeParses(string $type,array $array):?array
	{
		$return = null;

		if(static::isQuery($type))
		{
			$return = [];

			foreach (static::$config['query'][$type] as $key => $value)
			{
				$parse = static::makeParse($type,$key,$array);

				if($parse !== null)
				$return[$key] = $parse;
			}
		}

		return $return;
	}


	// makeParse
	// retourne la valeur d'une partie du tableau make selon le type et clé
	public static function makeParse(string $type,string $key,array $array)
	{
		$return = null;

		if(static::isQuery($type))
		{
			$data = static::$config['query'][$type];

			if(array_key_exists($key,$data) && is_array($data[$key]))
			{
				$keys = [$key];
				if(array_key_exists('key',$data[$key]))
				$keys[] = $data[$key]['key'];

				$return = Base\Arr::keysFirstValue($keys,$array);
			}
		}

		return $return;
	}


	// makeSelectFrom
	// fait une requête select à partir d'une requête insert, update ou delete
	public static function makeSelectFrom(string $type,array $array,?array $option=null)
	{
		$return = null;

		if(in_array($type,['insert','update','delete'],true))
		{
			$array = Base\Obj::cast($array);
			$values = ['what'=>'*'];

			if($type === 'insert')
			{
				$insertSet = static::makeParse($type,'insertSet',$array);

				if(!empty($insertSet) && is_array($insertSet))
				{
					$values['table'] = static::makeParse($type,'table',$array);
					$values['where'] = $insertSet;

					if(!empty($option['primary']))
					$values['order'] = [$option['primary']=>'desc'];

					$values['limit'] = 1;
				}
			}

			elseif($type === 'update' || $type === 'delete')
			{
				$values['table'] = static::makeParse($type,'table',$array);
				$values['where'] = static::makeParse($type,'where',$array);
				$values['order'] = static::makeParse($type,'order',$array);
				$values['limit'] = static::makeParse($type,'limit',$array);
			}

			if(!empty($values['table']))
			$return = static::makeSelect($values,$option);
		}

		return $return;
	}


	// makeSelect
	// fait une requête de type select
	public static function makeSelect(array $value,?array $option=null):?array
	{
		return static::make('select',$value,$option);
	}


	// makeShow
	// fait une requête de type show
	public static function makeShow(array $value,?array $option=null):?array
	{
		return static::make('show',$value,$option);
	}


	// makeInsert
	// fait une requête de type insert
	public static function makeInsert(array $value,?array $option=null):?array
	{
		return static::make('insert',$value,$option);
	}


	// makeUpdate
	// fait une requête de type update
	public static function makeUpdate(array $value,?array $option=null):?array
	{
		return static::make('update',$value,$option);
	}


	// makeDelete
	// fait une requête de type delete
	public static function makeDelete(array $value,?array $option=null):?array
	{
		return static::make('delete',$value,$option);
	}


	// makeCreate
	// fait une requête de type create
	public static function makeCreate(array $value,?array $option=null):?array
	{
		return static::make('create',$value,$option);
	}


	// makeAlter
	// fait une requête de type alter
	public static function makeAlter(array $value,?array $option=null):?array
	{
		return static::make('alter',$value,$option);
	}


	// makeTruncate
	// fait une requête de type truncate
	public static function makeTruncate(array $value,?array $option=null):?array
	{
		return static::make('truncate',$value,$option);
	}


	// makeDrop
	// fait une requête de type drop
	public static function makeDrop(array $value,?array $option=null):?array
	{
		return static::make('drop',$value,$option);
	}


	// select
	// fait une requête de type select
	// les arguments sont pack
	public static function select(...$values):?array
	{
		return static::makeSelect($values);
	}


	// show
	// fait une requête de type show
	// les arguments sont pack
	public static function show(...$values):?array
	{
		return static::makeShow($values);
	}


	// insert
	// fait une requête de type insert
	// les arguments sont pack
	public static function insert(...$values):?array
	{
		return static::makeInsert($values);
	}


	// update
	// fait une requête de type update
	// les arguments sont pack
	public static function update(...$values):?array
	{
		return static::makeUpdate($values);
	}


	// delete
	// fait une requête de type delete
	// les arguments sont pack
	public static function delete(...$values):?array
	{
		return static::makeDelete($values);
	}


	// create
	// fait une requête de type create
	// les arguments sont pack
	public static function create(...$values):?array
	{
		return static::makeCreate($values);
	}


	// alter
	// fait une requête de type alter
	// les arguments sont pack
	public static function alter(...$values):?array
	{
		return static::makeAlter($values);
	}


	// truncate
	// fait une requête de type truncate
	// les arguments sont pack
	public static function truncate(...$values):?array
	{
		return static::makeTruncate($values);
	}


	// drop
	// fait une requête de type drop
	// les arguments sont pack
	public static function drop(...$values):?array
	{
		return static::makeDrop($values);
	}


	// selectCount
	// fait une requête de type select count, pas besoin de donner what
	public static function selectCount(...$values):?array
	{
		return static::makeSelectCount($values);
	}


	// makeSelectCount
	// génère une requête select count, what est la function count
	public static function makeSelectCount(array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[[($primary = static::option($option)['primary']),'count',$primary]]),$option);
	}


	// makeSelectAll
	// génère une requête select all, what est *
	public static function makeSelectAll(array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,'*'),$option);
	}


	// makeSelectFunction
	// génère une requête select function, what est est une colonne avec function
	public static function makeSelectFunction($what,string $function,array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[[Base\Obj::cast($what,1),$function,Base\Obj::cast($what,1)]]),$option);
	}


	// makeSelectDistinct
	// génère une requête select distinct, what est est une colonne pour passage à la function distinct
	public static function makeSelectDistinct($what,array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[[Base\Obj::cast($what,1),'distinct',Base\Obj::cast($what,1)]]),$option);
	}


	// makeSelectColumn
	// génère une requête select column, what est est une seule colonne
	public static function makeSelectColumn($what,array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[$what]),$option);
	}


	// makeSelectKeyPair
	// génère une requête select keyValue, what est deux colonnes key et pair
	public static function makeSelectKeyPair($key,$pair,array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[Base\Obj::cast($key,1),Base\Obj::cast($pair,1)]),$option);
	}


	// makeselectPrimary
	// génère une requête select id, what est option primary
	public static function makeselectPrimary(array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,static::option($option)['primary']),$option);
	}


	// makeselectPrimaryPair
	// génère une requête select idPair, what est option primary avec pair une autre colonne
	public static function makeselectPrimaryPair($pair,array $value,?array $option=null):?array
	{
		return static::makeSelect(Base\Arr::unshift($value,[static::option($option)['primary'],Base\Obj::cast($pair,1)]),$option);
	}


	// makeSelectSegment
	// génère une requête selectSegment, what est généré automatiquement à partir d'une string segment []
	// les segments identiques sont ignorés, le segment id est toujours inclu et comme premier what
	// ceci va permettre à pdo de mettre le id comme clé
	public static function makeSelectSegment(string $key,array $value,?array $option=null):?array
	{
		$return = null;
		$option = static::option($option);
		$segment = Base\Segment::get(null,$key,true);

		if(!empty($segment))
		{
			$segment = Base\Arr::unshift($segment,$option['primary']);
			$segment = Base\Arr::unique($segment);
			$return = static::makeSelect(Base\Arr::unshift($value,$segment),$option);
		}

		return $return;
	}


	// makeShowDatabase
	// fait une requête de type show pour obtenir le nom d'une ou plusieurs databases
	// value peut être une string qui représente like
	public static function makeShowDatabase($value=null,?array $option=null):?array
	{
		$return = null;
		$value = Base\Obj::cast($value,2);
		$option = static::option(Base\Arr::plus($option,['prepare'=>false]));
		$array['what'] = 'DATABASES';

		if(is_string($value))
		$array['where'] = 'LIKE '.static::value(static::shortcut($value),null,$option)['sql'];

		$return = static::makeShow($array,$option);

		return $return;
	}


	// makeShowVariable
	// fait une requête de type show pour obtenir le nom d'une ou plusieurs variables
	// value peut être une string qui représente like
	public static function makeShowVariable($value=null,?array $option=null):?array
	{
		$return = null;
		$value = Base\Obj::cast($value,2);
		$option = static::option(Base\Arr::plus($option,['prepare'=>false]));
		$array['what'] = 'VARIABLES';

		if(is_string($value))
		$array['where'] = 'LIKE '.static::value($value,null,$option)['sql'];

		$return = static::makeShow($array,$option);

		return $return;
	}


	// makeShowTable
	// fait une requête de type show pour obtenir le nom d'une ou plusieurs tables
	// value peut être une string qui représente like
	public static function makeShowTable($value=null,?array $option=null):?array
	{
		$return = null;
		$value = Base\Obj::cast($value,2);
		$option = static::option(Base\Arr::plus($option,['prepare'=>false]));
		$array['what'] = 'TABLES';

		if(is_string($value))
		$array['where'] = 'LIKE '.static::value(static::shortcut($value),null,$option)['sql'];

		$return = static::makeShow($array,$option);

		return $return;
	}


	// makeShowTableStatus
	// fait une requête de type show pour obtenir le statut d'une ou plusieurs tables
	// value peut être une string qui représente like
	public static function makeShowTableStatus($value=null,?array $option=null):?array
	{
		$return = null;
		$value = Base\Obj::cast($value,2);
		$option = static::option(Base\Arr::plus($option,['prepare'=>false]));
		$array['what'] = 'TABLE STATUS';

		if(is_string($value))
		$array['where'] = 'LIKE '.static::value(static::shortcut($value),null,$option)['sql'];

		$return = static::makeShow($array,$option);

		return $return;
	}


	// makeShowTableColumn
	// fait une requête de type show pour obtenir la description d'une ou plusieurs colonnes dans une table
	// value est le nom de la colonne
	public static function makeShowTableColumn($table,$value=null,?array $option=null):?array
	{
		$return = null;
		$table = Base\Obj::cast($table,1);
		$value = Base\Obj::cast($value,2);
		$option = static::option(Base\Arr::plus($option,['prepare'=>false,'full'=>false]));

		if(!empty($table))
		{
			$table = static::shortcut($table);

			$array['what'] = '';
			if($option['full'] === true)
			$array['what'] .= 'FULL ';
			$array['what'] .= 'COLUMNS';
			$array['table'] = $table;

			if(is_string($value))
			{
				$array['where'] = 'WHERE FIELD = ';
				$array['where'] .= static::value(static::shortcut($value),null,$option)['sql'];
			}

			$return = static::makeShow($array,$option);
		}

		return $return;
	}


	// makeAlterAutoIncrement
	// fait une requête de type alter pour changer le autoincrement d'une table
	public static function makeAlterAutoIncrement($table,int $value=0,?array $option=null):?array
	{
		$return = null;
		$table = Base\Obj::cast($table,1);

		if(!empty($table))
		{
			$table = static::shortcut($table);
			$array['table'] = $table;
			$array['sql'] = 'AUTO_INCREMENT = ';
			$array['sql'] .= Base\Str::cast($value);
			$return = static::makeAlter($array,$option);

			if(!empty($return))
			$return['table'] = $table;
		}

		return $return;
	}


	// parseReturn
	// valide le format d'un tableau de retour sql
	// ajoute la clé type si non existante
	public static function parseReturn($value):?array
	{
		$return = null;

		if(is_string($value))
		$value = ['sql'=>$value];

		if(is_array($value))
		{
			if(array_key_exists(0,$value) && empty($value['sql']))
			{
				$value['sql'] = $value[0];
				unset($value[0]);
			}

			if(array_key_exists(1,$value) && empty($value['prepare']))
			{
				$value['prepare'] = $value[1];
				unset($value[1]);
			}

			if(!empty($value['sql']) && is_string($value['sql']))
			{
				$return = $value;

				if(empty($return['type']))
				$return['type'] = static::type($value['sql']);
			}
		}

		return $return;
	}


	// type
	// retourne le type de requête à partir d'une string sql
	public static function type(string $value):?string
	{
		$return = null;
		$value = Base\Str::wordExplodeIndex(0,$value);

		if(is_string($value))
		{
			$value = strtolower($value);

			if(static::isQuery($value))
			$return = $value;
		}

		return $return;
	}


	// emulate
	// émule une requête sql avec tableau prepare
	// toutes les valeurs du tableau prepare sont quote, même si numérique ou null
	// replaceDoubleEscape est true par défaut, va remplacer tous les double \\ par \, comme emulate est surtout utilisé pour de l'affichage ou debug
	public static function emulate(string $return,?array $prepare=null,?callable $callable=null,bool $replaceDoubleEscape=true):string
	{
		if(is_array($prepare))
		{
			foreach ($prepare as $k => $v)
			{
				if(is_string($k) && is_scalar($v))
				{
					$replace = static::quote($v,$callable,$replaceDoubleEscape);

					$return = preg_replace_callback("/(?!'|\"):$k(?!'|\")/",function($match) use ($replace) {
						return $replace;
					},$return);
				}
			}
		}

		return $return;
	}


	// debug
	// retourne le maximum d'informations à partir du tableau de retour sql
	public static function debug($value,?callable $callable=null,bool $replaceDoubleEscape=true):?array
	{
		$return = static::parseReturn($value);

		if(!empty($return))
		$return['emulate'] = static::emulate($return['sql'],$return['prepare'] ?? null,$callable,$replaceDoubleEscape);

		return $return;
	}
}
?>