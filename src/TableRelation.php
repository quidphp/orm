<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;

// tableRelation
// class to access the relation data of a table
class TableRelation extends Relation
{
    // config
    protected static array $config = [];


    // construct
    // construit l'objet de relation de table
    final public function __construct(Table $table)
    {
        $this->setLink($table,false);
        $this->makeAttr($table);
    }


    // makeAttr
    // applique les attributs de relation en provenance de la table
    // si what est true, prend le nom de la colonne via la méthode colName
    // seul what est nécessaire
    final protected function makeAttr($table,bool $config=true):void
    {
        if(!$table->allowsRelation())
        static::throw($this,'doesNotSupportRelation');

        $attr = $table->getAttr('relation');
        if(is_scalar($attr))
        $attr = ['what'=>$attr];

        if(!is_array($attr))
        $attr = [];

        if(!array_key_exists('what',$attr))
        $attr['what'] = true;

        if($attr['what'] === true)
        {
            $colName = $table->colName();
            $value = [];
            if(!empty($colName))
            $value[] = $colName->name();

            $attr['what'] = $value;
        }

        if(!is_array($attr['what']))
        $attr['what'] = [$attr['what']];

        if(empty($attr['what']))
        static::throw('whatCannotBeEmpty');

        if(array_key_exists('method',$attr) && is_string($attr['method']))
        $attr = $this->prepareAttrWithMethod($table,$attr);

        else
        $attr = $this->prepareAttrWithWhat($table,$attr);

        foreach ($attr as $key => $value)
        {
            if(!empty($value) && static::isCallable($value))
            {
                $value = $value($this);
                $attr[$key] = Base\Obj::cast($value);
            }

            if($key === 'where' && !is_array($attr[$key]))
            $attr[$key] = (array) $attr[$key];
        }

        $this->attr = $attr;
    }


    // prepareAttrWithWhat
    // prépare les attributs pour une relation de table standard avec what
    final protected function prepareAttrWithWhat(Table $table,array $attr):array
    {
        $primary = $table->primary();

        $attr['output'] ??= null;
        if($attr['output'] === null)
        $attr['output'] = $attr['what'];
        if(!is_array($attr['output']))
        $attr['output'] = [$attr['output']];

        $attr['where'] ??= null;
        $attr['order'] ??= $table->order();
        $attr['onGet'] ??= false;

        return $attr;
    }


    // prepareAttrWithMethod
    // prépare les attributs pour une relation de table avec output de méthode
    final protected function prepareAttrWithMethod(Table $table,array $attr):array
    {
        $return = [];
        $primary = $table->primary();

        if(array_key_exists('method',$attr) && is_string($attr['method']))
        {
            $attr['where'] ??= null;
            $attr['order'] ??= [$primary=>'asc'];

            $return = $attr;
        }

        else
        static::throw('methodMustBeString');

        return $return;
    }


    // searchMinLength
    // retourne la longueur minimale de la recherche
    final public function searchMinLength():int
    {
        return $this->table()->searchMinLength();
    }


    // shouldCache
    // retourne si l'argument cache doit être respecté
    final public function shouldCache(bool $return,$option=null)
    {
        $option = (array) $option;
        $option = Base\Arr::clean($option);
        return (!empty($option))? false:$return;
    }


    // isOutputMethod
    // retourne vrai si la output est une méthode de row
    final public function isOutputMethod(?string $method=null):bool
    {
        if($method === null)
        {
            $attr = $this->attr();
            $method ??= $attr['method'] ?? null;
        }

        return is_string($method);
    }


    // size
    // compte le nombre de relation
    final public function size(bool $cache=true,?array $option=null):int
    {
        $cache = $this->shouldCache($cache,$option);
        $option = Base\Arr::plus($this->attr(),$option);
        $where = $option['where'] ?? null;

        return $this->table()->rowsCount(true,$cache,$where);
    }


    // get
    // retourne une relation dans la table
    // une clé primaire doit être fourni
    // par défaut les relations sont conservés en cache dans l'objet relation
    final public function get(int $primary,bool $cache=true,?array $option=null)
    {
        $return = null;
        $relations = $this->gets([$primary],false,$cache,$option);

        if(!empty($relations))
        $return = current($relations);

        return $return;
    }


    // gets
    // retourne plusieurs relations dans la table
    // seul les relations non chargés le sont
    // par défaut les relations sont conservés en cache dans l'objet relation
    final public function gets(array $primaries,bool $found=false,bool $cache=true,?array $option=null):array
    {
        $return = [];
        $attr = $this->attr();
        $cache = $this->shouldCache($cache,$option);
        $option = Base\Arr::plus($attr,$option);
        $what = $option['what'];
        $where = $option['where'] ?? null;
        $method = (isset($option['method']) && is_string($option['method']))? $option['method']:null;

        $data =& $this->arr();
        $isMethod = $this->isOutputMethod($method);

        if($cache === true && !empty($data))
        $return = Base\Arr::getsExists($primaries,$data);

        if(count($return) !== count($primaries))
        {
            $missing = (!empty($return))? Base\Arr::valuesStrip(array_keys($return),$primaries):$primaries;

            if(empty($missing))
            static::throw();

            if(!empty($return) && $found === false)
            $return = Base\Arr::gets($primaries,$data);

            $db = $this->db();
            $table = $this->table();
            $primary = $table->primary();
            $where[] = [$primary,'in',$missing];

            if($isMethod === true)
            $result = $db->rows($table,$where);

            else
            {
                $what = Base\Arr::merge($primary,$what);
                $result = $db->selectAssocsUnique($what,$table,$where);
            }

            if(is_array($result) || $result instanceof Rows)
            {
                foreach ($result as $key => $value)
                {
                    $return[$key] = $this->makeOutput($value,$option);

                    if($cache === true)
                    $data[$key] = $return[$key];
                }

                $return = Base\Arr::getsExists($primaries,$return);
            }
        }

        return $return;
    }


    // all
    // retourne un tableau avec toutes les relations de la table
    // par défaut les relations sont conservés en cache dans l'objet relation
    // il y a un problème si tu charges les relations via relation ou relations et ensuite relationAll avec la cache (l'ordre ne sera pas respecté)
    // retourne une référence
    final public function &all(bool $cache=true,?array $option=null):array
    {
        $data =& $this->arr();
        $cache = $this->shouldCache($cache,$option);
        $attr = $this->attr();

        $option = Base\Arr::plus($attr,$option);
        $where = $option['where'] ?? [];
        $order = $this->getOrder($option['order'],$option);
        $limit = $option['limit'] ?? null;
        $not = (isset($option['not']) && is_array($option['not']))? $option['not']:null;
        $method = (isset($option['method']) && is_string($option['method']))? $option['method']:null;

        $isMethod = $this->isOutputMethod($method);

        if(!($cache === true && count($data) === $this->size($cache)))
        {
            $attr = $this->attr();

            if(!empty($attr))
            {
                $new = [];
                $db = $this->db();
                $table = $this->table();
                $primary = $table->primary();

                if(!empty($not))
                $where[] = [$primary,'notIn',$not];

                if($cache === true && !empty($data))
                $where[] = [$primary,'notIn',array_keys($data)];

                if($isMethod === true)
                $result = $db->rows($table,$where,$order,$limit);

                else
                {
                    $what = Base\Arr::merge($primary,$attr['what']);
                    $result = $db->selectAssocsUnique($what,$table,$where,$order,$limit);
                }

                if(is_array($result) || $result instanceof Rows)
                {
                    $new = [];

                    if($cache === true && !empty($data))
                    $new = $data;

                    foreach ($result as $key => $value)
                    {
                        $new[$key] = $this->makeOutput($value,$option);

                        if($cache === true)
                        $data[$key] = $new[$key];
                    }

                    if($cache === false)
                    $data = $new;
                }
            }
        }

        return $data;
    }


    // exists
    // retourne vrai si la ou les clés existent dans la relation
    final public function exists(...$primaries):bool
    {
        return $this->existsWhere(null,...$primaries);
    }


    // existsWhere
    // retourne vrai si la ou les clés existent dans la relation
    // prend compte de where
    // cache est true par défaut
    final public function existsWhere($where=null,...$primaries):bool
    {
        $return = false;

        if(!empty($primaries))
        {
            $cache = $this->shouldCache(true,$where);
            $data = null;

            if($where === null)
            $data = $this->arr();

            if(!empty($data) && Base\Arr::keysExists($primaries,$data))
            $return = true;

            elseif($this->size() > 0)
            {
                $db = $this->db();
                $attr = $this->attr();
                $table = $this->table();
                $primary = $table->primary();
                $where = Base\Arr::plus($attr['where'],$where);
                $where[] = [$primary,'in',$primaries];
                $count = count($primaries);
                $return = ($db->selectCount($table,$where) === $count);
            }
        }

        return $return;
    }


    // in
    // retourne vrai si la ou les valeurs existent dans la relation
    final public function in(...$values):bool
    {
        return $this->inWhere(null,...$values);
    }


    // inWhere
    // retourne vrai si la ou les valeurs existent dans la relation
    // prend compte de where
    // cette méthode utilisera all si pas trouvé dans les relations existantes, donc la cache est true par défaut
    final public function inWhere($where=null,...$values):bool
    {
        $return = false;

        if(!empty($values))
        {
            $cache = $this->shouldCache(true,$where);
            $data = null;

            if($where === null)
            $data = $this->arr();

            if(!empty($data) && Base\Arr::ins($values,$data))
            $return = true;

            elseif($this->size() > 0)
            {
                $all = $this->all(true);
                $return = (!empty($all) && Base\Arr::ins($values,$all));
            }
        }

        return $return;
    }


    // search
    // permet de faire une recherche dans la relation
    // la méthode renvoie à la méthode search dans core/table, donc les mêmes règles s'appliquent (minimum searchTerm, support pour +)
    // n'utilise pas la cache de relation
    final public function search(string $value,?array $option=null):?array
    {
        $return = null;
        $result = $this->searchResult($value,$option);

        if($result !== null)
        {
            $return = [];

            foreach ($result as $key => $value)
            {
                $return[$key] = $this->makeOutput($value,$option);
            }
        }

        return $return;
    }


    // searchCount
    // retourne le nombre de résultat d'une recherche dans la relation
    // n'utilise pas la cache de relation
    final public function searchCount(string $value,?array $option=null):?int
    {
        $return = null;
        $option = Base\Arr::plus($option,['limit'=>null,'method'=>null]);
        $result = $this->searchResult($value,$option);

        if($result !== null)
        $return = count($result);

        return $return;
    }


    // searchResult
    // utilisé par search et searchCount
    final protected function searchResult(string $value,?array $option=null)
    {
        $return = null;
        $attr = $this->attr();
        $option = Base\Arr::plus($attr,$option);
        $table = $this->table();

        if(strlen($value) && $this->size() > 0)
        {
            $return = [];
            $sqlArray  =[];
            $primary = $table->primary();
            $cols = $option['what'] ?? $table->cols()->searchable();
            $method = (isset($option['method']) && is_string($option['method']))? $option['method']:null;
            $isMethod = $this->isOutputMethod($method);

            $what = ($isMethod === true)? '*':Base\Arr::merge($primary,$cols);
            $output = ($isMethod === true)? 'rows':'assocsUnique';

            $where = $option['where'] ?? [];
            $whereNot = (isset($option['not']) && is_array($option['not']))? $option['not']:null;
            if(!empty($whereNot))
            $where[] = [$primary,'notIn',$whereNot];

            $sqlArray['what'] = $what;
            $sqlArray['where'] = $where;
            $sqlArray['order'] = $this->getOrder($option['order'],$option);
            $sqlArray['limit'] = $option['limit'] ?? null;
            $sqlArray['search'] = $value;
            $sqlArray['searchCols'] = $cols;
            $sqlArray['searchSeparator'] = $option['searchSeparator'] ?? null;
            $sqlArray['searchMethod'] = $option['searchMethod'] ?? null;
            $sqlArray['searchTermValid'] = $option['searchTermValid'] ?? true;

            $sql = $table->sql($sqlArray);
            $result = $sql->trigger($output);

            if(is_array($result) || $result instanceof Rows)
            $return = $result;
        }

        return $return;
    }


    // defaultOrderCode
    // retourne le code d'ordre par défaut pour la relation
    final public function defaultOrderCode():?int
    {
        return $this->table()->getAttr('orderCode');
    }


    // getOrder
    // génère le order pour la relation de table
    // support pour order 1, 2, 3 et 4
    final public function getOrder($order=null,?array $attr=null):?array
    {
        $return = null;
        $attr ??= $this->attr();
        $attrOrder = $attr['order'] ?? null;
        $table = $this->table();

        if(is_array($order))
        $return = $order;

        elseif(is_int($order))
        {
            $table = $this->table();
            $primary = $table->primary();
            $allowed = $this->allowedOrdering($attr);
            $field = $this->getOrderFieldOutput($attr);

            if($order === 1 && !empty($allowed['key']))
            $return = [$primary=>'asc'];

            elseif($order === 2 && !empty($allowed['key']))
            $return = [$primary=>'desc'];

            elseif($order === 3 && is_string($field) && !empty($allowed['value']))
            $return = [$field=>'asc'];

            elseif($order === 4 && is_string($field) && !empty($allowed['value']))
            $return = [$field=>'desc'];
        }

        elseif(is_array($attrOrder))
        $return = $attrOrder;

        elseif($attrOrder === null)
        $return = $table->order();

        return $return;
    }


    // allowedOrdering
    // retourne un tableau définissant si la relation peut être ordonner par clé et ou valeur
    final public function allowedOrdering(?array $attr=null):array
    {
        $return = ['key'=>true];

        if(is_string($this->getOrderFieldOutput($attr)))
        $return['value'] = true;

        return $return;
    }


    // getOrderFieldOutput
    // retourne le champ à utiliser pour l'ordonnage de nom
    // envoie une exception si aucun champ trouvé
    final public function getOrderFieldOutput(?array $attr=null):?string
    {
        $return = $this->table()->colName()->name();
        $attr ??= $this->attr();

        if($this->isOutputMethod() === false)
        {
            $output = $attr['output'] ?? null;
            $field = null;

            if(!empty($output))
            {
                if(is_array($output))
                $field = current($output);

                elseif(is_string($output))
                $field = $output;

                if(is_string($field) && !empty($field))
                {
                    if(strpos($field,'[') !== false && strpos($field,'_[') === false)
                    {
                        $segment = Base\Segment::get(null,$field);
                        if(is_array($segment) && !empty($segment))
                        $return = current($segment);
                    }

                    else
                    $return = $field;
                }
            }
        }

        return $return;
    }


    // makeOutput
    // méthode privé pour généré un output via output ou outputMethod
    final protected function makeOutput($value,?array $option=null):?string
    {
        $return = null;
        $attr = $this->attr();
        $method = $attr['method'] ?? null;
        $option = Base\Arr::plus($attr,$option);
        $method = (isset($option['method']) && is_string($option['method']))? $option['method']:$method;

        $output = $option['output'] ?? null;
        $onGet = $option['onGet'] ?? false;
        $isMethod = $this->isOutputMethod($method);

        if($isMethod === true)
        $return = $this->outputMethod($value,$method);

        else
        $return = $this->output($value,$output,$onGet);

        return $return;
    }


    // output
    // gère le output d'un tableau associatif contenant les données sur la relation
    // possible de passer le tableau dans onGet, par défaut c'est faux
    // output peut être une string, un array ou null
    final public function output(array $array,$output,bool $onGet=false):?string
    {
        $return = null;

        if($onGet === true)
        {
            $cols = $this->table()->cols();
            $array = $cols->value($array,true,true);
        }

        if($output === true)
        {
            $attr = $this->attr();
            $output = $attr['output'] ?? null;
        }

        if(is_array($output))
        {
            $r = '';

            foreach ($output as $out)
            {
                $v = null;

                if($out === null)
                $v = $array;

                elseif(is_string($out) && strpos($out,'[') !== false)
                $v = Base\Segment::sets(null,$array,$out);

                elseif(is_string($out))
                $v = Base\Arr::get($out,$array);

                if(!empty($v))
                {
                    if(is_array($v))
                    {
                        foreach ($v as $kk => $vv)
                        {
                            $r = $this->outputAdd($r,$kk,$vv);
                        }
                    }

                    else
                    $r = $this->outputAdd($r,$out,$v);
                }
            }

            $return = $r;
        }

        if(is_scalar($return))
        $return = (string) $return;

        if(empty($return) || !is_string($return))
        $return = null;

        return $return;
    }


    // outputAdd
    // utilisé par la méthode output pour ajouter un élément à la string de sortie
    final protected function outputAdd(string $return,$key,$value,string $separator=' - '):string
    {
        if(is_scalar($value))
        {
            $table = $this->table();
            $primary = $table->primary();
            $value = (string) $value;

            if(strlen($value))
            {
                if($key === $primary && strlen($return))
                $return = static::appendPrimary($return,$value);

                else
                {
                    $return .= (strlen($return))? $separator:'';
                    $return .= $value;
                }
            }
        }

        return $return;
    }


    // outputMethod
    // gère le output d'une relation avec output method
    final public function outputMethod(Row $row,string $method):?string
    {
        $return = $row->$method();
        $return = Base\Obj::cast($return);

        if(is_scalar($return))
        $return = (string) $return;

        return $return;
    }
}
?>