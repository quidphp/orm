<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;

// rowOperation
// abstract class used for a complex operation on a table row
abstract class RowOperation extends Operation
{
    // config
    public static $config = [];


    // dynamique
    protected $row = null; // conserve la row pour l'opération


    // construct
    // construit l'objet de l'opération
    final public function __construct(Row $row,?array $attr=null)
    {
        $this->makeAttr($attr);
        $this->setRow($row);

        return;
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