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

// tables
// class for a collection of many tables within a same database
class Tables extends Main\MapObj implements Main\Contract\Hierarchy
{
    // trait
    use Main\Map\_readOnly;
    use Main\Map\_sort;


    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
    protected $mapIs = Table::class; // classe d'objet permis
    protected ?string $mapSortDefault = 'priority'; // défini la méthode pour sort par défaut


    // construct
    // construit un nouvel objet tables
    final public function __construct(...$values)
    {
        if(!empty($values))
        $this->add(...$values);
    }


    // toString
    // retourne les noms de tables séparés par des virgules
    final public function __toString():string
    {
        return implode(',',$this->keys());
    }


    // onPrepareKey
    // prepare une clé pour les méthodes qui soumette une clé
    // support pour shortcut
    // possibilité de donner un nom de classe aussi
    final protected function onPrepareKey($key)
    {
        $return = null;

        if(is_string($key))
        {
            if(array_key_exists($key,$this->data))
            $return = $key;

            elseif(strpos($key,'\\') !== false && Base\Classe::extendOne(static::keyClassExtends(),$key))
            $return = $key::className(true);

            else
            {
                $key = Syntax::shortcut($key);
                if(array_key_exists($key,$this->data))
                $return = $key;
            }
        }

        elseif(is_int($key))
        $return = Base\Arr::index($key,$this->keys());

        elseif($key instanceof Table)
        $return = $key->name();

        elseif($key instanceof Row || $key instanceof Col || $key instanceof Cell)
        $return = $key->tableName();

        elseif(is_array($key))
        {
            foreach ($key as $k)
            {
                $return = $this->onPrepareKey($k);

                if(!empty($return))
                break;
            }
        }

        else
        $return = parent::onPrepareKey($key);

        return $return;
    }


    // onPrepareReturns
    // prépare le retour pour gets
    final protected function onPrepareReturns(array $array):self
    {
        $array = Base\Arr::clean($array);
        return new static(...array_values($array));
    }


    // cast
    // retourne la valeur cast
    final public function _cast():array
    {
        return $this->keys();
    }


    // offsetSet
    // arrayAccess offsetSet est seulement permis si la clé est null []
    final public function offsetSet($key,$value):void
    {
        if($key === null)
        $this->add($value);

        else
        static::throw('arrayAccess','onlyAllowedWithNullKey');
    }


    // hasChanged
    // retourne vrai si une des lignes des tables a changé
    final public function hasChanged():bool
    {
        return !empty($this->some(fn($value) => $value->rows()->hasChanged()));
    }


    // db
    // retourne la db du premier objet table
    final public function db():?Db
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->db();

        return $return;
    }


    // add
    // append une ou plusieurs tables dans l'objet
    // valeurs doivent être des objets table ou tables
    // deux objets identiques ne peuvent pas être ajoutés dans tables
    // des objets de différentes base de données ne peuvent être ajoutés dans tables
    final public function add(...$values):self
    {
        $this->checkAllowed('add');
        $values = $this->prepareValues(...$values);
        $firstDb = $this->db();
        $data =& $this->arr();

        foreach ($values as $value)
        {
            if(!$value instanceof Table)
            static::throw('requiresTable');

            $db = $value->db();
            $firstDb = $firstDb ?: $db;

            if($firstDb !== $db)
            static::throw('tableMustBeFromSameDb');

            $name = $value->name();

            if(!array_key_exists($name,$data))
            $data[$name] = $value;

            else
            static::throw('tableAlreadyIn',$name);
        }

        return $this->checkAfter();
    }


    // labels
    // retourne les labels de toutes les tables et de toutes les colonnes
    // pas de support pour pattern
    final public function labels(?string $lang=null,?array $option=null):array
    {
        $return = [];

        foreach ($this->arr() as $key => $value)
        {
            $return[$key]['table'] = $value->label(null,$lang,$option);
            $return[$key]['cols'] = $value->cols()->pair('label',null,$lang,$option);
        }

        return $return;
    }


    // descriptions
    // retourne les labels de toutes les tables et de toutes les descriptions
    // pas de support pour pattern
    final public function descriptions(?array $replace=null,?string $lang=null,?array $option=null):array
    {
        $return = [];

        foreach ($this->arr() as $key => $value)
        {
            $return[$key]['table'] = $value->description(null,$replace,$lang,$option);
            $return[$key]['cols'] = $value->cols()->pair('description',null,$replace,$lang,$option);
        }

        return $return;
    }


    // hasPermission
    // permet de filtre les tables par une ou plusieurs permissions
    final public function hasPermission(string ...$types):self
    {
        return $this->filter(fn($table) => $table->hasPermission(...$types));
    }


    // search
    // permet de chercher pour une valeur dans toutes les tables et toutes les colonnes cherchables
    // possible de changer la méthode en deuxième argument
    // retourne un tableau avec les ids et non pas un objet rows
    // ne retourne pas une table si aucun résultat trouvé
    final public function search($search,?array $option=null):array
    {
        $return = [];

        foreach ($this->searchable() as $key => $table)
        {
            $primary = $table->primary();
            $sqlArray = Base\Arr::plus($option,['search'=>$search,'what'=>$primary]);

            $sql = $table->sql($sqlArray);
            $result = $sql->trigger('columns');

            if(!empty($result))
            $return[$key] = $result;
        }

        return $return;
    }


    // changed
    // retourne un objet rowsIndex avec toutes les lignes de table qui ont changé
    final public function changed():RowsIndex
    {
        $return = RowsIndex::newOverload();

        foreach ($this->arr() as $key => $value)
        {
            $changed = $value->rows()->changed();

            if($changed->isNotEmpty())
            $return->add($changed);
        }

        return $return;
    }


    // total
    // retourne un tableau unidimensionnel sur le total des tables, colonnes, lignes et cellules chargés pour toutes les tables
    // si count est true, compte le nombre total de ligne et de colonne, pas seulement celle chargé
    // si count et cache sont true, retourne les counts en cache si existant
    final public function total(bool $count=false,bool $cache=false):array
    {
        $return = [];

        $return['table'] = $this->count();
        $return['col'] = 0;
        $return['row'] = 0;
        $return['cell'] = 0;

        foreach ($this->arr() as $key => $value)
        {
            $total = $value->total($count,$cache);
            $return = Base\Num::combine('+',$return,$total);
        }

        return $return;
    }


    // info
    // retourne un tableau multidimensionnel qui contient des informations sur le nombre de colonnes, lignes et cellules chargés pour toutes les tables
    final public function info(bool $count=false,bool $cache=false):array
    {
        return $this->pair('info',$count,$cache);
    }


    // searchable
    // retourne un objet tables avec toutes les tables cherchables
    final public function searchable():self
    {
        return $this->filter(fn($table) => $table->isSearchable());
    }


    // searchMinLength
    // retourne la plus grande longueur de recherche minimale
    final public function searchMinLength():int
    {
        $return = 0;

        foreach ($this->arr() as $value)
        {
            $minLength = $value->searchMinLength();

            if($minLength > $return)
            $return = $minLength;
        }

        return $return;
    }


    // isSearchTermValid
    // retourne vrai si le terme de la recherche est valide pour toutes les tables
    // valeur peut être scalar, un tableau à un ou plusieurs niveaux
    final public function isSearchTermValid($value):bool
    {
        return $this->every(fn($table) => $table->isSearchTermValid($value));
    }


    // truncate
    // permet de lancer la requête sql truncate sur toutes les tables contenus dans l'objet
    final public function truncate(?array $option=null):array
    {
        return $this->pair('truncate',$option);
    }


    // keyParent
    // retourne un tableau unidimensionnel avec le nom de la table comme clé et le nom du parent comme valeur
    // si aucun parent, la valeur est null
    final public function keyParent():array
    {
        return $this->pair('parent');
    }


    // hierarchy
    // retourne le tableau de la hiérarchie des éléments de l'objet
    // si existe est false, les parents de table non existants sont conservés
    final public function hierarchy(bool $exists=true):array
    {
        return Base\Arrs::hierarchy($this->keyParent(),$exists);
    }


    // childsRecursive
    // retourne un tableau avec tous les enfants de l'élément de façon récursive
    // si existe est false, les parents de table non existants sont conservés
    final public function childsRecursive($value,bool $exists=true):?array
    {
        $return = null;
        $hierarchy = $this->hierarchy($exists);

        if($value instanceof Table)
        $value = $value->name();

        if(is_string($value) && !empty($hierarchy))
        {
            $key = Base\Arrs::keyPath($value,$hierarchy);
            if($key !== null)
            $return = Base\Arrs::get($key,$hierarchy);
        }

        return $return;
    }


    // tops
    // retourne un objet des éléments n'ayant pas de parent
    // ne retourne pas les tables non existantes
    final public function tops():self
    {
        return $this->filter(fn($v) => $this->parent($v) === null);
    }


    // parent
    // retourne l'objet d'un élément parent ou null
    // ne retourne pas les tables non existantes
    final public function parent($value):?Table
    {
        $return = null;
        $value = $this->get($value);

        if(!empty($value))
        {
            $parent = $value->parent();
            if(is_string($parent))
            $return = $this->get($parent);
        }

        return $return;
    }


    // top
    // retourne le plus haut parent d'un élément ou null
    // ne retourne pas les tables non existantes
    final public function top($value):?Table
    {
        $return = null;
        $value = $this->get($value);

        if(!empty($value))
        {
            $target = $value;

            while ($parent = $this->parent($target))
            {
                $target = $parent;
            }

            if($target !== $value)
            $return = $target;
        }

        return $return;
    }


    // parents
    // retourne un objet avec tous les parents de l'élément
    // ne retourne pas les tables non existantes
    final public function parents($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            while ($parent = $this->parent($value))
            {
                $return->add($parent);
                $value = $parent;
            }
        }

        return $return;
    }


    // breadcrumb
    // retourne un objet inversé de tous les parents de l'élément et l'objet courant
    // ne retourne pas les tables non existantes
    final public function breadcrumb($value):self
    {
        $return = $this->parents($value);

        if($return->isNotEmpty())
        $return = $return->reverse(true);

        $value = $this->get($value);
        if(!empty($value))
        $return->add($value);

        return $return;
    }


    // siblings
    // retourne un objet des éléments ayant le même parent que la valeur donnée
    // ne retourne pas les tables non existantes
    final public function siblings($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            $parent = $this->parent($value);

            foreach ($this->arr() as $k => $v)
            {
                if($v !== $value && $this->parent($v) === $parent)
                $return->add($v);
            }
        }

        return $return;
    }


    // childs
    // retourne un objet avec les enfants de l'élément donné en argument
    // ne retourne pas les tables non existantes
    final public function childs($value):self
    {
        $return = new static();
        $value = $this->get($value);

        if(!empty($value))
        {
            foreach ($this->arr() as $k => $v)
            {
                if($this->parent($v) === $value)
                $return->add($v);
            }
        }

        return $return;
    }


    // relationChilds
    // retourne tous les ids ou les lignes qui sont des enfants de relation d'une ligne
    final public function relationChilds($table,$primary):array
    {
        $return = [];
        $table = $this->get($table);
        $primary = ($primary instanceof Row)? $primary->primary():$primary;

        if($table instanceof Table && is_int($primary))
        {
            foreach ($this->arr() as $key => $value)
            {
                $cols = $value->cols()->filter(fn($col) => $col->isRelation());
                $cols = $cols->filter(fn($col) => $col->relationTable() === $table);

                if($cols->isNotEmpty())
                {
                    foreach ($cols as $col)
                    {
                        $colName = $col->name();
                        $primaries = $col->primaries($primary);

                        if(!empty($primaries))
                        $return[$key][$colName] = $primaries;
                    }
                }
            }
        }

        return $return;
    }


    // keyClassExtends
    // retourne un tableau utilisé par onPrepareKey
    final public static function keyClassExtends():array
    {
        return [Row::classOverload(),Table::classOverload(),Rows::classOverload(),Cells::classOverload(),Cols::classOverload()];
    }
}
?>