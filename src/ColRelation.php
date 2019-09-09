<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;

// colRelation
// class to access the relation data of a column
class ColRelation extends Relation
{
    // config
    public static $config = [];


    // dynamique
    protected $mode = null; // mode de la relation
    protected $type = null; // garde en cache le type de la relation
    protected $col = null; // objet colonne de la relation


    // construct
    // construit l'objet de relation de colonne
    public function __construct(Col $col)
    {
        $this->setLink($col->table(),false);
        $this->prepare($col);

        return;
    }


    // prepare
    // vérifie les attributs de relation en provenance de la colonne
    // change le mode de la relation
    // si le type de relation est table, la propriété data est une référence des données de relation de la table (donc partagé par toutes les colonnes en relation avec la même table)
    // méthode protégé
    protected function prepare(Col $col):self
    {
        if(!$col->isRelation() && !$col->isDate())
        static::throw($col,'isNotRelation');

        $this->col = $col->name();
        $this->mode = ($col->isSet())? 'set':'enum';

        if(empty($this->attr()))
        static::throw('noRelationConfig');

        if($this->checkType() === 'table')
        $this->data =& $this->relationTable()->relation()->arr();

        return $this;
    }


    // mode
    // retourne le mode de la relation (enum ou set)
    public function mode():string
    {
        return $this->mode;
    }


    // attr
    // retourne les attributs de la relation de la colonne
    public function attr()
    {
        $return = null;
        $col = $this->col();

        if($col->isRelation())
        $return = $col->attr('relation');

        else
        $return = 'date';

        return $return;
    }


    // whereTable
    // retourne le where dans les attributs si existnat
    public function whereTable()
    {
        $return = null;
        $attr = $this->attr();

        if(is_array($attr) && array_key_exists('where',$attr))
        {
            $return = $attr['where'];

            if(!empty($return) && static::classIsCallable($return))
            $return = $return($this);
        }

        return $return;
    }


    // col
    // retourne la colonne de la relation
    public function col():Col
    {
        return $this->table()->col($this->col);
    }


    // isEnum
    // retourne vrai si la relation est enum
    public function isEnum():bool
    {
        return ($this->mode === 'enum')? true:false;
    }


    // isSet
    // retourne vrai si la relation est set
    public function isSet():bool
    {
        return ($this->mode === 'set')? true:false;
    }


    // type
    // retourne le type de relation de la colonne
    // le type est gardé en cache dans la propriété type de l'objet
    public function type():?string
    {
        $return = $this->type;

        if(empty($return))
        {
            $db = $this->db();
            $col = $this->col();
            $attr = $this->attr();

            if(!empty($attr))
            {
                if(is_array($attr) && array_key_exists('table',$attr))
                $attr = $attr['table'];

                if(is_string($attr) && $db->hasTable($attr) && $db->table($attr)->allowsRelation())
                $return = 'table';

                elseif($attr === 'date' && $col->isDate())
                $return = 'date';

                elseif(is_string($attr))
                $return = 'lang';

                elseif(is_int($attr) || (is_array($attr) && Base\Arr::keysAre(['min','max','inc'],$attr)))
                $return = 'range';

                elseif(static::classIsCallable($attr))
                $return = 'callable';

                elseif(is_array($attr))
                $return = 'array';

                $this->type = $return;
            }
        }

        return $return;
    }


    // checkType
    // envoie une exception si le type de la relation est indéfini
    public function checkType():string
    {
        $return = $this->type();

        if(!is_string($return))
        static::throw($this);

        return $return;
    }


    // isRelationTable
    // retourne vrai si le type de relation est table
    public function isRelationTable():bool
    {
        return ($this->type() === 'table')? true:false;
    }


    // defaultOrderCode
    // retourne le code d'ordre par défaut pour la relation
    public function defaultOrderCode():?int
    {
        $return = $this->col()->attr('orderCode');

        if(!is_int($return))
        {
            $type = $this->type();

            if($type === 'table')
            {
                $table = $this->relationTable();
                $return = $table->relation()->defaultOrderCode();
            }

            elseif($type === 'date')
            $return = 2;

            else
            $return = 3;
        }

        return $return;
    }


    // allowedOrdering
    // retourne un tableau définissant si la relation peut être ordonner par clé et ou valeur
    public function allowedOrdering():array
    {
        $return = [];
        $type = $this->type();

        if($type === 'table')
        {
            $table = $this->relationTable();
            $return = $table->relation()->allowedOrdering();
        }

        elseif($type === 'date')
        {
            $return['key'] = true;
            $return['value'] = true;
        }

        else
        $return['value'] = true;

        return $return;
    }


    // relationTable
    // retourne la table de relation si existante
    public function relationTable():?Table
    {
        return ($this->type() === 'table')? $this->db()->table($this->attr()):null;
    }


    // checkRelationTable
    // envoie une exception s'il n'est pas possible de retourner la table de la relation
    public function checkRelationTable():Table
    {
        $return = $this->relationTable();

        if(!$return instanceof Table)
        static::throw($this->col());

        return $return;
    }


    // label
    // retourne le label à donner à la relation, par défaut utilise celui de la colonne
    // pour une relation table, le label de la table sera retourné
    public function label():?string
    {
        $return = null;
        $type = $this->checkType();

        if($type === 'table')
        $return = $this->checkRelationTable()->label();

        else
        $return = $this->col()->label();

        return $return;
    }


    // size
    // retourne le nombre d'éléments dans la relation
    public function size(bool $cache=true):int
    {
        $return = 0;
        $type = $this->checkType();

        if($type === 'table')
        {
            $option = ['where'=>$this->whereTable()];
            $return = $this->checkRelationTable()->relation()->size($cache,$option);
        }

        else
        $return = count($this->all($cache));

        return $return;
    }


    // all
    // retourne un tableau avec toutes les relations existantes
    // si la référence vient de la table, la propriété relation sera une référence de la propriété relation de table
    // le retour de cette méthode est mis en cache par défaut
    // pour un tableau lang, order par clé si toutes les clés sont numériques
    public function all(bool $cache=true,?array $option=null):array
    {
        $return = [];
        $option = Base\Arr::plus(['not'=>null,'limit'=>null],$option);
        $data =& $this->arr();
        $type = $this->checkType();

        if($cache === true)
        $return = $data;

        if(empty($return) || $cache === false)
        {
            $col = $this->col();
            $attr = $this->attr();

            if($type === 'table')
            {
                $option = Base\Arr::plus(['where'=>$this->whereTable()],$option);
                $return = $this->checkRelationTable()->relation()->all($cache,$option);
            }

            else
            {
                $new = [];

                if($type === 'array')
                $new = $attr;

                elseif($type === 'callable')
                $new = $attr($this);

                elseif($type === 'range')
                {
                    if(is_int($attr))
                    $attr = ['min'=>1,'max'=>$attr,'inc'=>1];

                    $range = Base\Arr::range($attr['min'],$attr['max'],$attr['inc']);
                    $new = array_combine($range,$range);
                }

                elseif($type === 'lang')
                {
                    $lang = $this->db()->lang();
                    $new = $lang->relation($attr);
                }

                elseif($type === 'date')
                {
                    $min = $col->dateMin();
                    $max = $col->dateMax();

                    if(is_int($min) && is_int($max))
                    $new = Base\Date::months($max,$min,1,2);
                }

                $return = $new;
            }
        }

        if(!is_array($return))
        static::throw();

        if($cache === true)
        $data = $return;

        if(($type !== 'table' || $cache === true) && is_array($return))
        $return = $this->notOrderLimit($return,$option);

        return $return;
    }


    // exists
    // retourne vrai si la ou les clés existent dans la relation
    // cache est true par défaut
    public function exists(...$keys):bool
    {
        $return = false;
        $type = $this->checkType();
        $cache = true;

        if($type === 'table')
        $return = $this->checkRelationTable()->relation()->existsWhere($this->whereTable(),...$keys);

        elseif(!empty($keys))
        {
            $all = $this->all($cache);
            if(Base\Arr::keysExists($keys,$all))
            $return = true;
        }

        return $return;
    }


    // in
    // retourne vrai si la ou les valeurs existent dans la relation
    // cache est true par défaut
    public function in(...$values):bool
    {
        $return = false;
        $type = $this->checkType();
        $cache = true;

        if($type === 'table')
        $return = $this->checkRelationTable()->relation()->inWhere($this->whereTable(),...$values);

        elseif(!empty($values))
        {
            $all = $this->all($cache);
            if(Base\Arr::ins($values,$all))
            $return = true;
        }

        return $return;
    }


    // search
    // permet de faire une recherche dans la relation, que ce soit une relation tableau ou table
    // recherche insensible à la case et avec support pour un search separator
    // par défaut cache est false
    public function search(string $value,?array $option=null):?array
    {
        $return = null;
        $option = Base\Arr::plus(['searchSeparator'=>null],$option);
        $type = $this->checkType();

        if(strlen($value))
        {
            if($type === 'table')
            {
                $table = $this->checkRelationTable();
                $option = Base\Arr::plus(['where'=>$this->whereTable()],$option);
                $return = $table->relation()->search($value,$option);
            }

            else
            {
                $all = $this->all(false,Base\Arr::plus($option,['limit'=>null]));
                $return = Base\Arr::valuesSearch($value,$all,false,false,true,$option['searchSeparator']);

                if(is_array($return))
                $return = $this->notOrderLimit($return,$option);
            }
        }

        return $return;
    }


    // notOrderLimit
    // gère not, order et limit pour un tableau de retour
    protected function notOrderLimit(array $return,?array $option=null):array
    {
        if(is_array($option))
        {
            if(!empty($option['not']) && is_array($option['not']))
            $return = Base\Arr::unsets($option['not'],$return);

            if(!empty($option['order']) && is_int($option['order']))
            $return = Base\Arr::sort($return,$option['order']);

            if(!empty($option['limit']))
            $return = Base\Nav::slice($option['limit'],$return);
        }

        return $return;
    }


    // keyValue
    // retourne la relation sous forme de tableau key -> value
    public function keyValue($value,bool $found=false,bool $cache=true,?array $option=null):?array
    {
        $return = null;
        $type = $this->checkType();

        if(!Base\Validate::isReallyEmpty($value))
        {
            if(!is_array($value))
            $value = [$value];

            if($type === 'table')
            {
                $table = $this->checkRelationTable();
                $option = Base\Arr::plus($option,['where'=>$this->whereTable()]);
                $return = $table->relation()->gets($value,$found,$cache,$option);
            }

            else
            {
                if($found === true)
                $return = Base\Arr::getsExists($value,$this->all($cache));
                else
                $return = Base\Arr::gets($value,$this->all($cache));
            }
        }

        return $return;
    }


    // one
    // retourne la valeur d'un élément de relation
    public function one($value,bool $cache=true,?array $option=null)
    {
        $return = null;

        if($this->isEnum())
        {
            $relation = $this->keyValue($value,false,$cache,$option);

            if(is_array($relation) && !empty($relation))
            $return = current($relation);
        }

        else
        static::throw('onlyForEnum');

        return $return;
    }


    // many
    // retourne la valeur d'un élément de relation (peut conte nir plusiuers éléments)
    public function many($value,bool $found=false,bool $cache=true,?array $option=null):?array
    {
        $return = null;

        if($this->isSet() || is_array($value))
        $return = $this->keyValue($value,$found,$cache,$option);

        else
        static::throw('onlyForSet');

        return $return;
    }


    // row
    // retourne la valeur de la relation sous forme de row
    // envoie une exception si le type de relation n'est pas table ou enum
    public function row($value):?Row
    {
        $return = null;

        if($this->isEnum())
        {
            $table = $this->checkRelationTable();

            if(!empty($value) && is_scalar($value))
            $return = $table->row($value);
        }

        else
        static::throw('useRowsForSet');

        return $return;
    }


    // rows
    // retourne la valeur de la relation sous forme de rows
    // envoie une exception si le type de relation n'est pas table
    public function rows($value):Rows
    {
        $return = null;
        $table = $this->checkRelationTable();

        if(!empty($value) && is_array($value))
        $return = $table->rows(...array_values($value));

        elseif(!empty($value) && is_scalar($value))
        $return = $table->rows($value);

        else
        $return = $table->rows(false);

        return $return;
    }


    // get
    // retourne la valeur d'une ou plusieurs relations, selon le type (enum ou set)
    // la valeur est passé dans onGet de la colonne
    public function get($value=true,bool $found=false,bool $cache=true,?array $option=null)
    {
        $return = null;
        $col = $this->col();
        $value = $col->get($value,$option);

        if($this->isSet() || is_array($value))
        $return = $this->many($value,$found,$cache,$option);

        else
        $return = $this->one($value,$cache,$option);

        return $return;
    }


    // getStr
    // retourne la valeur d'une ou plusieurs relations sous forme de string, selon le type (enum ou set)
    // le séparateur est spécifié en deuxième argument
    public function getStr($value=true,string $separator=',',bool $found=false,bool $cache=true,?array $option=null):?string
    {
        $return = $this->get($value,$found,$cache,$option);

        if(is_array($return))
        $return = implode($separator,$return);

        if(is_scalar($return))
        $return = (string) $return;

        return $return;
    }


    // getKeyValue
    // retourne la valeur d'une ou plusieurs relations sous une forme clé-valeur
    // la valeur est passé dans onGet de la colonne
    public function getKeyValue($value=true,bool $found=false,bool $cache=true,?array $option=null):?array
    {
        $return = null;
        $col = $this->col();
        $value = $col->get($value,$option);
        $return = $this->keyValue($value,$found,$cache);

        return $return;
    }


    // getRow
    // retourne la valeur d'une ou plusieurs relations, selon le type (enum ou set)
    // la valeur est passé dans onGet de la colonne
    // le retour est un objet row ou rows
    public function getRow($value=true,?array $option=null)
    {
        $return = null;
        $col = $this->col();
        $value = $col->get($value);

        if($this->isSet())
        {
            if(!is_array($value))
            $value = Base\Set::onGet($value);

            $return = $this->rows($value);
        }

        else
        $return = $this->row($value);

        return $return;
    }
}
?>