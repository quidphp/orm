<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Base;
use Quid\Orm;

// schema
// class for testing Quid\Orm\Schema
class Schema extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormDb';

        // construct
        $schema = new Orm\Schema(null,$db);

        // tables
        assert(count($schema->tables()) === 27);

        // table
        assert(count($schema->table('user')) === 13);
        assert(count($schema->table('user',false)) === 13);
        assert(count($schema->table('session')) === 11);

        // col
        assert(count($schema->col($table,'name_en')) === 9);
        assert($schema->col($table,'name_en',false)['Field'] === 'name_en');
        assert($schema->col($table,'name_en',false)['Collation'] === 'utf8mb4_general_ci');

        // all
        assert(count($schema->all()) === 27);

        // dbAccess
        assert(!empty($schema->get('user/id')));
        assert(!empty($schema->get(['session','data'])));
        assert($schema->db() instanceof Orm\Db);

        return true;
    }
}
?>