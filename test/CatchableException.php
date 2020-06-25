<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Main;
use Quid\Orm;

// catchableException
// class for testing Quid\Orm\CatchableException
class CatchableException extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // construct
        $e = new Orm\CatchableException('blabla');

        // setQuery
        assert($e->setQuery('query') === $e);

        // getQuery
        assert($e->getQuery() === 'query');

        // content
        assert($e->content() === 'query');

        // showQuery
        Orm\CatchableException::showQuery(true);
        assert($e instanceof Main\Contract\Catchable);

        // exception
        assert($e->getCode() === 34);
        assert($e->getMessage() === 'blabla');

        return true;
    }
}
?>