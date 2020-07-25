<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;

// rowsMap
// root class for a collection of rows
abstract class RowsMap extends Map
{
    // dynamique
    protected $mapIs = Row::class; // classe d'objet permis


    // config
    protected static array $config = [];


    // offsetSet
    // arrayAccess offsetSet est seulement permis si la clé est null []
    final public function offsetSet($key,$value):void
    {
        if($key !== null)
        static::throw('arrayAccess','onlyAllowedWithNullKey');

        $this->add($value);
    }


    // hasChanged
    // retourne vrai si une des lignes a changé
    final public function hasChanged():bool
    {
        return $this->some(fn($value) => $value->hasChanged());
    }


    // checkCell
    // envoie une exception si toutes les lignes n'ont pas la cellule spécifiée en argument
    final public function checkCell($key):bool
    {
        return $this->hasCell($key) ?: static::throw($key);
    }


    // getRefresh
    // retourne un objet row ou null si non existant
    // met la row à jour avant de retourner
    final public function getRefresh($value):?Row
    {
        $return = $this->get($value);

        if($return instanceof Row)
        $return->refresh();

        return $return;
    }


    // getsRefresh
    // retourne un nouvel objet rows avec les lignes refresh
    final public function getsRefresh(...$values):self
    {
        return $this->gets(...$values)->refresh();
    }


    // changed
    // retourne un nouvel objet rows avec les lignes qui ont changés
    final public function changed():self
    {
        return $this->filter(fn($value) => $value->hasChanged());
    }


    // cell
    // permet de retourner un tableau key->cell pour toutes les lignes dans l'objet
    // envoie une exception si la cellule n'existe pas
    final public function cell($key):CellsIndex
    {
        $return = CellsIndex::newOverload();

        foreach ($this->arr() as $row)
        {
            $cell = $row->cell($key);
            $return->add($cell);
        }

        return $return;
    }


    // cellNotEmpty
    // retourne un tableau key->cell pour toutes les lignes de l'objet
    // retourne seulement la cellule si elle n'est pas vide
    final public function cellNotEmpty($key):CellsIndex
    {
        $return = CellsIndex::newOverload();

        foreach ($this->arr() as $row)
        {
            $cell = $row->cell($key);

            if($cell->isNotEmpty())
            $return->add($cell);
        }

        return $return;
    }


    // cellFirstNotEmpty
    // retoure la première cellule spécifé en argument non vide des lignes
    final public function cellFirstNotEmpty($key):?Cell
    {
        $return = null;
        $this->checkCell($key);

        foreach ($this->arr() as $id => $row)
        {
            $cell = $row->cell($key);

            if(!empty($cell) && $cell->isNotEmpty())
            {
                $return = $cell;
                break;
            }
        }

        return $return;
    }


    // setCell
    // permet de changer la valeur de la cellule dans toutes les lignes
    // envoie une exception si la cellule n'existe pas
    final public function setCell($key,$value):self
    {
        foreach ($this->cell($key) as $cell)
        {
            $cell->set($value);
        }

        return $this->checkAfter();
    }


    // resetCell
    // permet de reset la valeur de la cellule dans toutes les lignes
    // la valeur est ramené à sa dernière valeur commit
    // envoie une exception si la cellule n'existe pas
    final public function resetCell($key):self
    {
        foreach ($this->cell($key) as $cell)
        {
            $cell->reset();
        }

        return $this->checkAfter();
    }


    // unsetCell
    // permet de changer la valeur de la cellule dans toutes les lignes
    // la valeur de changement est mis à null
    // envoie une exception si la cellule n'existe pas
    final public function unsetCell($key):self
    {
        foreach ($this->cell($key) as $cell)
        {
            $cell->unset();
        }

        return $this->checkAfter();
    }


    // cellValue
    // permet de retourner un tableau key->cellValue pour toutes les lignes dans l'objet
    // par défaut utilise value de cellule, si get est true utilise get
    final public function cellValue($value,bool $get=false):array
    {
        return Base\Arr::map($this->arr(),fn($row) => $row->cellValue($value,$get));
    }


    // segment
    // permet de remplacer les segments d'une chaîne par le contenu des cellules pour toutes les lignes
    // par défaut utilise value de cellule, si get est true utilise get
    final public function segment(string $value,bool $get=false,bool $str=false)
    {
        $return = Base\Arr::map($this->arr(),fn($row) => $row->segment($value,$get));

        if($str === true)
        $return = implode($return);

        return $return;
    }


    // keyValue
    // retourne un tableau associatif avec le contenu keyValue de toutes les lignes de l'objet
    // si get est true, value est passé dans get plutôt que value
    final public function keyValue($key,$value,bool $get=false):array
    {
        $return = [];

        foreach ($this->arr() as $row)
        {
            $return = Base\Arr::replace($return,$row->keyValue($key,$value,$get));
        }

        return $return;
    }


    // where
    // permet de filtrer des rows selon un tableau sql where
    // similaire à une syntaxe sql mais ne supporte pas les méthodes base/sql whereThree, ni les and, or et paranthèses
    final public function where(array $array):self
    {
        return $this->filter(fn($value) => $value->cells()->isWhere($array));
    }


    // order
    // clone et sort l'objet de rows
    // support un sort par multiple cellule et direction
    // fournir un tableau cellule -> direction
    final public function order(array $array):self
    {
        $return = $this->clone();
        $return->checkAllowed('sort');

        if($return->isNotEmpty())
        {
            $data =& $return->arr();

            $sorts = [];
            foreach ($array as $key => $value)
            {
                $this->checkCell($key);
                $sorts[] = ['cellValue',$value,$key];
            }

            $data = Base\Obj::sorts($sorts,$data);
        }

        return $return->checkAfter();
    }


    // limit
    // clone et filtre l'objet par une limit et possiblement un offset
    // similaire à une syntaxe sql
    final public function limit(int $value,?int $value2=null):self
    {
        $limit = $value2 ?? $value;
        $offset = (is_int($value2))? $value:0;

        return $this->sliceIndex($offset,$limit);
    }


    // clean
    // enlève de l'objet toutes les lignes déliées
    // cette méthode ne tient pas compte de readOnly
    final public function clean():self
    {
        $data =& $this->arr();

        foreach ($data as $key => $value)
        {
            if(!$value->isLinked())
            unset($data[$key]);
        }

        return $this->checkAfter();
    }


    // unlink
    // unlink toutes les lignes de l'objet rows
    // les lignes sont retirés de cet objet, de rows table et prennent un statut inutilisable
    // cette méthode ne tient pas compte de readOnly
    // possible de donner un objet rows avec des lignes à ne pas unlink
    final public function unlink(?self $rows=null):self
    {
        $data =& $this->arr();

        foreach ($data as $key => $value)
        {
            if($value->isLinked())
            {
                if($rows === null || !$rows->in($value))
                {
                    $value->unlink();
                    unset($data[$key]);
                }
            }
        }

        return $this->checkAfter();
    }


    // update
    // sauve toutes les lignes dans l'objet
    // toutes les cellules sont passés dans onUpdate avant
    // seuls les cellules ayant changés sont envoyés à la db
    // retourne un tableau avec les résultats pour chaque ligne
    final public function update(?array $option=null):array
    {
        $return = $this->pair('update',$option);
        $this->checkAfter();

        return $return;
    }


    // updateChanged
    // sauve toutes les lignes dans l'objet: sans les include
    // différence: onUpdate est seulement appelé si au moins une cellule a changé
    // seuls les cellules ayant changés sont envoyés à la db
    // retourne un tableau avec les résultats pour chaque ligne
    final public function updateChanged(?array $option=null):array
    {
        $return = $this->pair('updateChanged',$option);
        $this->checkAfter();

        return $return;
    }


    // updateValid
    // sauve toutes les lignes dans l'objet: avec les include
    // différence: update est seulement appelé si au moins une cellule a changé
    // seuls les cellules valides sont envoyés à la db
    // retourne un tableau avec les résultats pour chaque ligne
    final public function updateValid(?array $option=null):array
    {
        $return = $this->pair('updateValid',$option);
        $this->checkAfter();

        return $return;
    }


    // updateRowChanged
    // sauve seulement les lignes ayant changés
    // toutes les cellules sont passés dans onUpdate avant
    // seuls les cellules ayant changés sont envoyés à la db
    // retourne un tableau avec les résultats pour les lignes ayant changés
    final public function updateRowChanged(?array $option=null):array
    {
        $return = [];

        foreach ($this->arr() as $key => $row)
        {
            if($row->hasChanged())
            $return[$key] = $row->update($option);
        }

        $this->checkAfter();

        return $return;
    }


    // delete
    // efface les lignes, fait une requête pour l'ensemble
    // les lignes effacés sont teardown, effacé de l'objet table et de cet objet rows
    // retourne un tableau avec les résultats pour chaque ligne
    final public function delete(?array $option=null)
    {
        $return = null;

        foreach ($this->arr() as $row)
        {
            $delete = $row->delete($option);

            if(is_int($delete))
            $return += $delete;
        }

        $this->clean();
        $this->checkAfter();

        return $return;
    }
}
?>