<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm;

// rowOperation
// abstract class used for a complex operation on a table row
abstract class RowOperation extends Operation
{
    // config
    protected static array $config = [];


    // dynamique
    protected Row $row; // conserve la row pour l'opération


    // construct
    // construit l'objet de l'opération
    final public function __construct(Row $row,?array $attr=null)
    {
        $this->makeAttr($attr);
        $this->setRow($row);

        return;
    }


    // isValidTimestamp
    // retourne vrai si le timestamp fourni est plus récent que le dernier dateCommit
    // utilise valueInitial car le timestamp peut avoir changé dans les include
    final public function isValidTimestamp(int $value):bool
    {
        $return = false;
        $row = $this->row();
        $commit = $row->newestDateCommit();
        $initial = (!empty($commit))? $commit['date']->valueInitial():null;

        if(empty($initial) || $initial < $value)
        $return = true;

        return $return;
    }


    // setRow
    // lie une row à l'objet
    final protected function setRow(Row $row):void
    {
        $this->row = $row;

        return;
    }


    // row
    // retourne la row lié à l'opération
    final protected function row():Row
    {
        return $this->row;
    }


    // cells
    // retourne l'objet cells de la row
    final protected function cells():Cells
    {
        return $this->row()->cells();
    }


    // table
    // retourne l'objet table de la row
    final protected function table():Table
    {
        return $this->row()->table();
    }


    // db
    // retourne la db de l'opération
    final public function db():Db
    {
        return $this->row()->db();
    }
}
?>