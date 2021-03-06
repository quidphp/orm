<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Main;

// tableClasse
// class required to identify which class needs to be used by the different ORM components of a table
class TableClasse extends Main\Map
{
    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['jsonSerialize','serialize','clone']; // méthodes permises


    // table
    // retourne la classe de la table, ne peut pas être vide
    final public function table():string
    {
        return $this->get('table');
    }


    // rows
    // retourne la classe de la rows, ne peut pas être vide
    final public function rows():string
    {
        return $this->get('rows');
    }


    // row
    // retourne la classe de la row, ne peut pas être vide
    final public function row():string
    {
        return $this->get('row');
    }


    // col
    // retourne la classe de la colonne
    final public function col($key):?string
    {
        return $this->get(['col',$key]);
    }


    // setCol
    // change la classe d'une colonne
    final public function setCol($key,string $class):self
    {
        $key = $this->onPrepareKey(['col',$key]);
        $data =& $this->arr();
        $data[$key] = $class;

        return $this;
    }


    // cols
    // retourne la classe de la cols, ne peut pas être vide
    final public function cols():string
    {
        return $this->get('cols');
    }


    // cell
    // retourne la classe de la cellule
    final public function cell($key):?string
    {
        return $this->get(['cell',$key]);
    }


    // setCell
    // conserve la classe d'une cellule
    final public function setCell($key,string $class):self
    {
        $key = $this->onPrepareKey(['cell',$key]);
        $data =& $this->arr();
        $data[$key] = $class;

        return $this;
    }


    // cells
    // retourne la classe de la cells, ne peut pas être vide
    final public function cells():string
    {
        return $this->get('cells');
    }
}
?>