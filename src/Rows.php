<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;

// rows
// class for a collection of many rows within a same table
class Rows extends RowsMap
{
    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
    protected ?string $mapSortDefault = 'primary'; // défini la méthode pour sort par défaut


    // onPrepareKey
    // prepare une clé pour les méthodes qui soumette une clé
    // peut fournir un tableau qui sera utilisé comme where
    final protected function onPrepareKey($key)
    {
        $return = null;

        if(is_int($key))
        $return = $key;

        elseif($key instanceof Row)
        $return = $key->primary();

        elseif($key instanceof Cell)
        $return = $key->row()->primary();

        elseif(is_array($key))
        $return = $this->findKey(fn($v) => $v->cells()->isWhere($key));

        else
        $return = parent::onPrepareKey($key);

        return $return;
    }


    // hasCell
    // retourne vrai si toutes les lignes dans l'objet ont la cellule
    public function hasCell($key):bool
    {
        $first = $this->first();
        return !empty($first) && $first->hasCell($key);
    }


    // isTable
    // retourne vrai si la row contient des éléments de cette table
    public function isTable($value):bool
    {
        $table = $this->table();
        return !empty($table) && ((is_object($value) && $value === $table) || (is_string($value) && $value === $table->name()));
    }


    // table
    // retourne la table des rows
    final public function table():?Table
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->table();

        return $return;
    }


    // addMode
    // ajoute une ou plusieurs rows dans l'objet
    // valeurs doivent être des objets row
    // possible de fournir un objet rows
    // deux objets identiques ne peuvent pas être ajoutés dans rows
    // des objets de différentes tables ne peuvent être ajoutés dans rows
    // un mode doit être spécifié en premier argument (inOrder ou ordre d'ajout)
    final protected function addMode(bool $inOrder=false,...$values):self
    {
        $this->checkAllowed('add');
        $values = $this->prepareValues(...$values);
        $firstTable = $this->table();
        $data =& $this->arr();

        if($inOrder === true)
        $this->sortDefault();

        foreach ($values as $value)
        {
            if(!$value instanceof Row)
            static::throw('requiresRow');

            $table = $value->table();
            $firstTable = $firstTable ?: $table;

            if($table !== $firstTable)
            static::throw('rowMustBeFromSameTable');

            $primary = $value->primary();

            if(array_key_exists($primary,$data))
            static::throw('rowAlreadyIn',$primary);

            if($inOrder === true)
            $data = Base\Arr::insertInOrder([$primary=>$value],$data);

            else
            $data[$primary] = $value;
        }

        return $this->checkAfter();
    }


    // add
    // ajoute une ou plusieurs rows dans l'objet
    // possible de fournir un objet rows
    // l'ordre d'ajout des rows est gardé intacte, aucune sort
    public function add(...$values):self
    {
        return $this->addMode(false,...$values);
    }


    // addSort
    // ajoute une ou plusieurs rows dans l'objet
    // possible de fournir un objet rows
    // les rows sont toujours ajoutés dans l'ordre naturel des ids, donc si la row 1 est ajouté après la 3 elle aura quand même la première position
    final public function addSort(...$values):self
    {
        return $this->addMode(true,...$values);
    }


    // alive
    // retourne vrai si toutes les lignes existent, fait une requête pour la table
    public function alive():bool
    {
        $return = false;
        $table = $this->table();
        $ids = $this->keys();

        if(!empty($table) && !empty($ids))
        {
            $db = $table->db();
            $count = $db->selectCount($table,$ids);
            $return = ($count === $this->count());
        }

        return $return;
    }


    // refresh
    // rafraichit les lignes, fait une requête pour la table
    public function refresh():self
    {
        $table = $this->table();
        $ids = $this->keys();

        if(!empty($table) && !empty($ids))
        {
            $db = $table->db();
            $assocs = $db->selectAllsPrimary($table,$ids);

            if(!empty($assocs))
            {
                foreach ($assocs as $id => $values)
                {
                    $row = $this->get($id);

                    if(!empty($row))
                    $row->cellsRefresh($values);
                }
            }
        }

        return $this->checkAfter();
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Rows':null;
    }
}
?>