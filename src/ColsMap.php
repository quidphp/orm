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

// colsMap
// root class for a collection of cols
abstract class ColsMap extends Map
{
    // dynamique
    protected $mapIs = Col::class; // classe d'objet permis


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


    // isVisible
    // retourne vrai si tous les champs sont visibles
    final public function isVisible(?Main\Session $session=null):bool
    {
        $args = [true,null,$session];
        $hidden = $this->pair('isVisible',...$args);

        return !in_array(false,$hidden,true);
    }


    // isHidden
    // retourne vrai si tous les champs sont cachés
    final public function isHidden(?Main\Session $session=null):bool
    {
        $args = [true,null,$session];
        $hidden = $this->pair('isVisible',...$args);

        return !in_array(true,$hidden,true);
    }


    // included
    // retourne un objet avec les colonnes incluses par défaut
    // inclusion des required est true par défaut
    final public function included(?array $option=null):self
    {
        return $this->filter(fn($col) => $col->isIncluded('insert',$option['required'] ?? true));
    }


    // searchable
    // retourne un objet cols avec toutes les colonnes cherchables
    final public function searchable():self
    {
        return $this->filter(fn($col) => $col->isSearchable());
    }


    // searchMinLength
    // retourne la longueur de recherche minimale pour les colonnes
    final public function searchMinLength():?int
    {
        $return = null;

        foreach ($this as $col)
        {
            $minLength = $col->searchMinLength();

            if($return === null || $minLength > $return)
            $return = $minLength;
        }

        return $return;
    }


    // isSearchTermValid
    // retourne vrai si un terme de recherche est valide pour toutes les colonnes de l'objet
    final public function isSearchTermValid($value):bool
    {
        return $this->every(fn($col) => $col->isSearchTermValid($value));
    }


    // writeFile
    // écrit les colonnes dans l'objet file fourni en argument
    // par exemple pour une première ligne de csv
    final public function writeFile(Main\File $file,?Cells $cells=null,?array $option=null):self
    {
        $option = Base\Arr::plus(['type'=>'format'],$option);
        $array = [];

        foreach ($this as $col)
        {
            if($option['type'] === 'format' && !empty($cells))
            {
                $cell = $cells->checkGet($col);
                $value = $col->export($cell,$option);
            }

            else
            $value = $col->name();

            $array = Base\Arr::merge($array,$value);
        }

        $file->write($array,$option);

        return $this;
    }
}
?>