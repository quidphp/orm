<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// cellsMap
// root class for a collection of cells
abstract class CellsMap extends Map
{
    // dynamique
    protected $mapIs = Cell::class; // classe d'objet permis


    // config
    protected static array $config = [];


    // offsetSet
    // arrayAccess offsetSet si la clé est null [] ou si la clé est un nom de cellule
    final public function offsetSet($key,$value):void
    {
        if($key === null)
        $this->add($value);

        else
        $this->set($key,$value);
    }


    // isVisible
    // retourne vrai si tous les champs sont visibles
    final public function isVisible(?Main\Session $session=null):bool
    {
        $hidden = $this->pair('isVisible',null,$session);
        return !in_array(false,$hidden,true);
    }


    // isHidden
    // retourne vrai si tous les champs sont cachés
    final public function isHidden(?Main\Session $session=null):bool
    {
        $hidden = $this->pair('isVisible',null,$session);
        return !in_array(true,$hidden,true);
    }


    // isRequired
    // retourne un objet cells avec toutes les cellules requises
    // ne retourne pas la clé primaire
    final public function isRequired(bool $value=true):self
    {
        return $this->filter(fn($cell) => $cell->isRequired($value));
    }


    // isStillRequired
    // retourne un objet cells avec toutes les cellules toujours requises
    // ne retourne pas la clé primaire
    final public function isStillRequired():self
    {
        return $this->filter(fn($cell) => !$cell->isPrimary() && $cell->isStillRequired());
    }


    // isStillRequiredEmpty
    // retourne vrai si l'objet isStillRequired est vide
    // ceci signifie que toutes les cellules requises ont une valeur
    final public function isStillRequiredEmpty():bool
    {
        return $this->isStillRequired()->isEmpty();
    }


    // hasChanged
    // retourne vrai si une des cellules de cells a changé
    // si une cellule a un committed callback, on considère qu'elle a changé
    final public function hasChanged():bool
    {
        return !empty($this->some(fn($cell) => $cell->hasChanged()));
    }


    // withoutPrimary
    // retourne un objet avec les cellules sans la clé primaire
    final public function withoutPrimary():self
    {
        return $this->gets(...$this->namesWithoutPrimary());
    }


    // notEmpty
    // retourne un objet avec toutes les cellules non vides
    final public function notEmpty():self
    {
        return $this->filter(fn($cell) => $cell->isNotEmpty());
    }


    // firstNotEmpty
    // retoure la première cellule non vide
    final public function firstNotEmpty():?Cell
    {
        return $this->notEmpty()->first();
    }


    // update
    // passe toutes les cellules, sauf la primaire, dans la méthode onUpdate
    final public function update(?array $option=null):self
    {
        foreach ($this->arr() as $cell)
        {
            if(!$cell->isPrimary())
            $return = $cell->update($option);
        }

        return $this;
    }


    // delete
    // passe toutes les cellules, sauf la primaire, dans la méthode onDelete, si existante
    final public function delete(?array $option=null):self
    {
        foreach ($this->arr() as $cell)
        {
            if(!$cell->isPrimary())
            $cell->delete($option);
        }

        return $this;
    }


    // changed
    // retourne un objet des cellules qui ont changés
    // si include est true, inclut aussi les colonne ayant l'attribut include
    final public function changed(bool $included=false,?array $option=null):self
    {
        $return = ($included === true)? $this->included($option):new static();

        foreach ($this->arr() as $cell)
        {
            if(!$return->in($cell) && $cell->hasChanged())
            $return->add($cell);
        }

        return $return;
    }


    // included
    // retourne un objet des cellules avec les included
    // si la cellule incluse n'a pas changé et qu'elle a attrInclude, set sa propre valeur pour lancer les callback onSet
    // les cellules required sont include par défaut
    final public function included(?array $option=null):self
    {
        $option = Base\Arr::plus($option,['preValidate'=>false]);
        return $this->filter(function($cell) use($option) {
            $return = false;

            if($cell->isIncluded($option['required'] ?? true))
            {
                $return = true;

                if(!$cell->hasChanged() && $cell->col()->hasAttrInclude())
                $cell->setSelf($option);
            }

            return $return;
        });
    }


    // writeFile
    // écrit les cellules dans l'objet file fourni en argument
    // par défaut le type est format, donc passe dans export
    // par exemple pour une ligne de csv
    final public function writeFile(Main\File $file,?array $option=null):self
    {
        $option = Base\Arr::plus(['type'=>'format'],$option);
        $array = [];

        foreach ($this->arr() as $cell)
        {
            if($option['type'] === 'format')
            $value = $cell->export($option);

            else
            $value = (string) $cell;

            $array = Base\Arr::merge($array,$value);
        }

        $file->write($array,$option);

        return $this;
    }
}
?>