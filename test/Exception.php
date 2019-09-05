<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/test/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Orm;
use Quid\Main;
use Quid\Base;

// exception
// class for testing Quid\Orm\Exception
class Exception extends Base\Test
{
	// trigger
	public static function trigger(array $data):bool
	{
		// construct
		$e = new Orm\Exception('blabla');

		// setQuery
		assert($e->setQuery('query') === $e);

		// getQuery
		assert($e->getQuery() === 'query');

		// content
		assert($e->content() === 'query');

		// showQuery
		Orm\Exception::showQuery(true);
		assert(!$e instanceof Main\Contract\Catchable);

		// exception
		assert($e->getCode() === 33);
		assert($e->getMessage() === 'blabla');

		return true;
	}
}
?>