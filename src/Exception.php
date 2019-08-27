<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// exception
class Exception extends Main\Exception
{
	// config
	public static $config = [
		'code'=>33, // code de l'exception
		'query'=>false // affiche la query
	];


	// dynamique
	protected $query = null; // conserve la requête sql sous forme de string


	// setQuery
	// lie la query à l'exception
	// méthode protégé
	public function setQuery(string $value)
	{
		$this->query = $value;

		return $this;
	}


	// getQuery
	// retourne la query
	public function getQuery():?string
	{
		return $this->query;
	}


	// content
	// retourne la query si showQuery est true, sinon retourne null
	public function content():?string
	{
		return (static::$config['query'] === true)? $this->query:null;
	}


	// showQuery
	// affiche ou non la requête sql dans le message
	public static function showQuery(bool $value):void
	{
		static::$config['query'] = $value;

		return;
	}
}

// config
Exception::__config();
?>