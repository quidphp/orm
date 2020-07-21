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

// pdoSql
// class used to build an sql query in a object-oriented way, not linked to ORM components
class PdoSql extends Main\Map
{
    // trait
    use _dbAccess;


    // config
    protected static array $config = [
        'shortcut'=>[ // tableau des shortcuts de clause, utiliser par les méthodes array acces et aussi certaines méthodes shortcuts
            'into'=>['insert'=>'table'],
            'from'=>['select'=>'table','delete'=>'table'],
            'data'=>['insert'=>'insertSet','update'=>'updateSet'],
            'col'=>['create'=>'createCol','alter'=>'addCol']],
        'default'=>'select' // type par défaut
    ];


    // dynamique
    protected ?array $mapAllow = ['set','unset','empty','overwrite','serialize','jsonSerialize','clone']; // méthodes permises
    protected ?string $type = null; // type de la requête
    protected $output = null; // output de la requête
    protected array $count = []; // contient une cache des différents count


    // construct
    // construit l'objet sql
    final public function __construct(Pdo $db,?string $type=null,$output=true)
    {
        $this->makeAttr(null);
        $this->setDb($db);
        $this->setType($type);
        $this->setOutput($output);
    }


    // invoke
    // appel de la classe, renvoie vers trigger
    final public function __invoke(...$args)
    {
        return $this->trigger(...$args);
    }


    // toString
    // retourne l'émulation de la requête
    final public function __toString():string
    {
        return $this->emulate() ?? '';
    }


    // onPrepareKey
    // prépare une clé pour une méthode comme get et slice
    // peut envoyer une exception
    final protected function onPrepareKey($return)
    {
        $return = $this->getShortcut($return) ?? $return;
        $this->checkClause($return);

        return $return;
    }


    // toArray
    // méthode utilisé pour obtenir du contenu tableau lors du remplacement via une méthode map
    // seulement pour des requêtes select ou show
    final public function toArray():array
    {
        $return = [];

        if(in_array($this->getType(),['select','show'],true))
        $return = $this->trigger();
        else
        static::throw('onlyForSelectAndShow');

        return $return;
    }


    // arr
    // retourne une référence du tableau data
    final public function &arr():array
    {
        return $this->data;
    }


    // cast
    // retourne la valeur cast enrobbé de paranthèse
    final public function _cast():?string
    {
        $return = $this->emulate();

        if(!empty($return))
        $return = "($return)";

        return $return;
    }


    // primary
    // retourne la clé primaire par défaut de l'objet db
    public function primary():string
    {
        return $this->db()->primary();
    }


    // syntaxCall
    // permet d'appeler une méthode sur la classe de syntaxe de la db
    final public function syntaxCall(string $method,...$args)
    {
        return $this->db()->syntaxCall($method,...$args);
    }


    // setType
    // change le type de l'objet sql
    // l'objet est vidé
    // le output est ramené à true si output courant incompatible avec le nouveau type
    final public function setType(?string $type,$output=null):self
    {
        if($type === null)
        $type = $this->getAttr('default');

        if($this->syntaxCall('isQuery',$type))
        {
            $db = $this->db();
            $this->empty();
            $this->resetCount();
            $this->type = $type;

            if(!$db->isOutput($type,$this->getOutput()))
            $this->setOutput(true);

            if($output !== null)
            $this->setOutput($output);
        }

        else
        static::throw();

        return $this;
    }


    // getType
    // retourne le type de l'objet
    final public function getType():string
    {
        return $this->type;
    }


    // setOutput
    // change le output de la requête
    public function setOutput($output=true):self
    {
        $db = $this->db();

        if($db->isOutput($this->getType(),$output))
        $this->output = $output;

        else
        static::throw($output,'invalidFor',$this->getType());

        return $this;
    }


    // getOutput
    // retourne le output de la requête
    final public function getOutput()
    {
        return $this->output;
    }


    // resetCount
    // reset le tableau de cache pour les count
    final protected function resetCount():void
    {
        $this->count = [];
    }


    // getShortcut
    // retourne le nom de la méthode lié au shortcut
    final public function getShortcut(string $value):?string
    {
        return $this->getAttr(['shortcut',$value,$this->getType()]);
    }


    // getTable
    // retourne le nom de la table lié à l'objet sql si existant
    final public function getTable():?string
    {
        $return = null;
        $table = $this->get('table');

        if(is_string($table))
        $return = $table;

        return $return;
    }


    // checkTable
    // retourne le nom de la table lié à l'objet sql
    // envoie une exception si non existant
    final public function checkTable():string
    {
        $return = $this->getTable();

        if(empty($return))
        static::throw();

        return $return;
    }


    // hasJoin
    // retourne vrai si l'objet a une entrée join et que table est set
    final public function hasJoin():bool
    {
        $return = false;

        if($this->getType() === 'select')
        {
            $arr = $this->arr();
            $join = Base\Arr::keysFirst(['join','innerJoin','outerJoin'],$arr);

            if($join !== null && !empty($arr[$join]) && !empty($arr[$join]['table']))
            $return = true;
        }

        return $return;
    }


    // checkType
    // envoie une exception si le type de l'objet n'est pas celui donné en argument
    final public function checkType(string $value):self
    {
        if($this->getType() !== $value)
        static::throw($value);

        return $this;
    }


    // checkClause
    // retourne vrai si la clause est valide avec le type, sinon lance une exception
    final protected function checkClause($value):void
    {
        if(is_string($value))
        {
            $type = $this->getType();
            $output = $this->getOutput();

            if(!$this->syntaxCall('hasQueryClause',$type,$value))
            {
                if($value === 'on')
                {
                    if(!$this->hasJoin())
                    static::throw($value,'noJoinStartedOn',$type);
                }

                else
                static::throw($value,'invalidFor',$type);
            }
        }

        else
        static::throw('requiresString');
    }


    // checkValue
    // retourne vrai si la valeur de la clause est valide, sinon lance une exception
    // cette validation se fait sur une entrée d'une clause
    final protected function checkValue(string $clause,$value):void
    {
        if(in_array($clause,['table','group','dropCol','dropKey'],true) && (!is_string($value) || !strlen($value)))
        static::throw($clause,'requires','stringWithLength');

        elseif($clause === 'insertSet' && !Base\Arr::isAssoc($value))
        static::throw($clause,'requires','associativeArray');

        elseif($clause === 'updateSet' && (!Base\Arr::isAssoc($value) || !count($value)))
        static::throw($clause,'requires','associativeArrayWithCount');

        elseif(in_array($clause,['createCol','createKey','addCol','addKey','alterCol'],true) && (!is_array($value) || !count($value)))
        static::throw($clause,'requires','arrayWithCount');
    }


    // checkShortcut
    // retourne la méthode à appeler si le shortcut est valide avec le type, sinon lance une exception
    final protected function checkShortcut(string $value):?string
    {
        $return = $this->getShortcut($value);

        if($return === null)
        static::throw($value,'invalidFor',$this->getType());

        return $return;
    }


    // checkMake
    // retourne le tableau make, si problème ou retour vide lance une exception
    protected function checkMake($output,?array $option=null):?array
    {
        $return = null;
        $arr = $this->arr();
        $required = $this->syntaxCall('getQueryRequired',$this->getType());

        if(!empty($required) && !Base\Arr::keysExists($required,$arr))
        {
            $strip = Base\Arr::valuesStrip(array_keys($arr),$required);
            static::throw('missingRequiredClause',$strip);
        }

        elseif(empty($arr))
        static::throw('queryEmpty');

        else
        {
            $make = $this->make($output,$option);
            if(empty($make))
            static::throw('sqlReturnEmpty');

            else
            $return = $make;
        }

        return $return;
    }


    // do
    // permet d'append ou prepend une entrée à une clause
    // si la valeur est un tableau avec un count de 1, enlève le tableau
    // la cache des count est reset à chaque appel à cette méthode
    final protected function do(string $clause,$value,bool $prepend=false):void
    {
        $this->resetCount();
        $arr =& $this->arr();
        if(is_array($value) && array_key_exists(0,$value) && count($value) === 1)
        $value = current($value);

        $value = Base\Obj::cast($value);

        $this->checkClause($clause);
        $this->checkValue($clause,$value);

        if($clause === 'on')
        {
            $join = Base\Arr::keysFirst(['join','innerJoin','outerJoin'],$arr);

            if($join !== null)
            {
                if(!array_key_exists($clause,$arr[$join]) || !is_array($arr[$join][$clause]))
                $arr[$join][$clause] = [];

                $target =& $arr[$join][$clause];
            }
        }

        else
        {
            if(!array_key_exists($clause,$arr) || !is_array($arr[$clause]))
            $arr[$clause] = [];

            $target =& $arr[$clause];
        }

        if(isset($target) && is_array($target))
        {
            if(in_array($clause,['table','limit','join','innerJoin','outerJoin'],true))
            $target = $value;

            elseif(in_array($clause,['insertSet','updateSet'],true))
            $target = Base\Arr::replace($target,$value);

            elseif(in_array($clause,['what','where','order','group','createCol','createKey','addCol','addKey','alterCol','dropCol','dropKey','on'],true))
            {
                if(Base\Arr::isAssoc($value))
                {
                    if($prepend === true)
                    $target = Base\Arr::merge($value,$target);
                    else
                    $target = Base\Arr::merge($target,$value);
                }

                else
                {
                    if($prepend === true)
                    $target = Base\Arr::merge((is_array($value))? [$value]:$value,$target);

                    else
                    $target[] = $value;
                }
            }
        }

        else
        static::throw('noValidTargetReference',$clause);
    }


    // one
    // append une entrée à une clause
    final public function one(string $clause,...$value):self
    {
        $this->do($clause,$value,false);

        return $this;
    }


    // many
    // permet d'append plusieurs entrées à une clause
    final public function many(string $clause,...$values):self
    {
        foreach ($values as $value)
        {
            $this->do($clause,$value,false);
        }

        return $this;
    }


    // prependOne
    // prepend une entrée à une clause
    final public function prependOne(string $clause,...$value):self
    {
        $this->do($clause,$value,true);

        return $this;
    }


    // prependMany
    // prepend plusieurs entrées à une clause
    final public function prependMany(string $clause,...$values):self
    {
        foreach ($values as $value)
        {
            $this->do($clause,$value,true);
        }

        return $this;
    }


    // exists
    // retourne vrai si les clauses existent dans le tableau
    // n'envoie pas d'exception
    final public function exists(...$keys):bool
    {
        foreach ($keys as &$key)
        {
            $key = $this->getShortcut($key) ?? $key;
        }

        return Base\Arr::keysExists($keys,$this->arr());
    }


    // set
    // change ou ajoute le contenu d'une clause
    final public function set($key,$value):self
    {
        $key = $this->getShortcut($key) ?? $key;
        $this->checkClause($key);
        $value = Base\Obj::cast($value);

        return parent::set($key,$value);
    }


    // make
    // fait la requête via la classe BaseSql
    final public function make($output=null,?array $option=null):?array
    {
        $return = null;
        $data = $this->arr();
        $db = $this->db();
        $output ??= $this->getOutput();

        if($this->getType() === 'select')
        $data = $db->selectLimit($output,$data);

        $return = $db->syntaxCall('make',$this->getType(),$data,$option);

        return $return;
    }


    // what
    // permet d'ajouter une entrée à une clause what
    // valide pour select et show
    final public function what(...$value):self
    {
        return $this->one('what',...$value);
    }


    // whats
    // permet d'ajouter plusieurs entrées à une clause what
    // valide pour select et show
    final public function whats(...$values):self
    {
        return $this->many('what',...$values);
    }


    // table
    // permet d'ajouter du contenu à une clause table
    // valide pour tous les types
    final public function table($value):self
    {
        return $this->one('table',Base\Obj::cast($value,1));
    }


    // from
    // permet d'ajouter du contenu à une clause table
    // valide pour select et delete
    final public function from($value):self
    {
        $shortcut = $this->checkShortcut('from');
        $this->one($shortcut,Base\Obj::cast($value,1));

        return $this;
    }


    // into
    // permet d'ajouter du contenu à une clause table
    // valide pour insert
    final public function into($value):self
    {
        $shortcut = $this->checkShortcut('into');
        $this->one($shortcut,Base\Obj::cast($value,1));

        return $this;
    }


    // join
    // permet d'ajouter une entrée à une clause join
    // seule la table est obligatoire, valide pour select
    final public function join($table,?array $values=null):self
    {
        return $this->one('join',['table'=>Base\Obj::cast($table,1)])->ons($values);
    }


    // innerJoin
    // permet d'ajouter une entrée à une clause innerJoin
    // seule la table est obligatoire, valide pour select
    final public function innerJoin($table,?array $values=null):self
    {
        return $this->one('innerJoin',['table'=>Base\Obj::cast($table,1)])->ons($values);
    }


    // outerJoin
    // permet d'ajouter une entrée à une clause outerJoin
    // seule la table est obligatoire, valide pour select
    final public function outerJoin($table,?array $values=null):self
    {
        return $this->one('outerJoin',['table'=>Base\Obj::cast($table,1)])->ons($values);
    }


    // on
    // permet d'append une entrée on à une clause join, innerJoin ou outerJoin
    final public function on(...$value):self
    {
        return $this->one('on',...$value);
    }


    // ons
    // permet d'append plusieurs entrées on à une clause join, innerJoin ou outerJoin
    final public function ons(...$values):self
    {
        return $this->many('on',...$values);
    }


    // where
    // permet d'ajouter une entrée à une clause where
    // valide pour select, show, update et delete
    final public function where(...$value):self
    {
        return $this->one('where',...$value);
    }


    // wheres
    // permet d'ajouter plusieurs entrées à une clause where
    // valide pour select, show, update et delete
    final public function wheres(...$values):self
    {
        return $this->many('where',...$values);
    }


    // wheresOne
    // permet d'ajouter plusieurs entrées clause where via un seul argument
    final public function wheresOne($values):self
    {
        if(!is_array($values))
        $values = [$values];

        if(is_array($values))
        {
            foreach ($values as $key => $value)
            {
                if(is_string($key))
                $this->where([$key=>$value]);
                else
                $this->where($value);
            }
        }

        return $this;
    }


    // whereSeparator
    // permet de faire un where sur plusieurs colonnes avec une même méthode et une même valeur
    // le séparateur entre chaque colonne doit être défini en premier argument
    // par défaut une parenthèse enroberra ces entrées where
    final public function whereSeparator(string $separator,$method,$cols,$value=null,bool $parenthesis=true):self
    {
        if($this->syntaxCall('isWhereSeparator',$separator))
        {
            $cols = Base\Obj::cast($cols,6);

            if($parenthesis === true)
            $this->where('(');

            $i = 0;
            foreach ($cols as $col)
            {
                if($i > 0)
                $this->where($separator);

                $this->where($col,$method,$value);

                $i++;
            }

            if($parenthesis === true)
            $this->where(')');
        }

        else
        static::throw();

        return $this;
    }


    // whereAnd
    // permet de faire un where sur plusieurs colonnes avec une même méthode et une même valeur
    // le séparateur entre chaque colonne est and
    // par défaut une parenthèse enroberra ces entrées where
    final public function whereAnd($method,$cols,$value=null,bool $parenthesis=true):self
    {
        return $this->whereSeparator('and',$method,$cols,$value,$parenthesis);
    }


    // whereOr
    // permet de faire un where sur plusieurs colonnes avec une même méthode et une même valeur
    // le séparateur entre chaque colonne est or
    // par défaut une parenthèse enroberra ces entrées where
    final public function whereOr($method,$cols,$value=null,bool $parenthesis=true):self
    {
        return $this->whereSeparator('or',$method,$cols,$value,$parenthesis);
    }


    // whereSeparatorMany
    // comme whereSeparator, mais chaque valeur du tableau passe dans le loop de façon indépendante
    // possible de spécifier un séparateur et un séparateur interne
    final public function whereSeparatorMany(string $separator,string $innerSeparator,$method,$cols,array $values,bool $parenthesis=true):self
    {
        if($this->syntaxCall('isWhereSeparator',$innerSeparator))
        {
            $i = 0;
            foreach ($values as $value)
            {
                if($i > 0)
                $this->where($innerSeparator);

                $this->whereSeparator($separator,$method,$cols,$value,$parenthesis);
                $i++;
            }
        }

        else
        static::throw();

        return $this;
    }


    // whereAndMany
    // comme whereAnd, mais chaque valeur du tableau passe dans le loop de façon indépendante
    // possible de spécifier un séparateur entre les whereAnd
    final public function whereAndMany($method,$cols,array $values,string $innerSeparator='and',bool $parenthesis=true):self
    {
        return $this->whereSeparatorMany('and',$innerSeparator,$method,$cols,$values,$parenthesis);
    }


    // whereOrMany
    // comme whereOr, mais chaque valeur du tableau passe dans le loop de façon indépendante
    // possible de spécifier un séparateur entre les whereOr
    final public function whereOrMany($method,$cols,array $values,string $innerSeparator='and',bool $parenthesis=true):self
    {
        return $this->whereSeparatorMany('or',$innerSeparator,$method,$cols,$values,$parenthesis);
    }


    // whereAfter
    // permet d'ajouter les clauses après where
    // value 0 est order et value 1 est limit
    final public function whereAfter(...$values):self
    {
        foreach ($values as $key => $value)
        {
            if($key === 0)
            $this->order($value);

            elseif($key === 1)
            $this->limit($value);
        }

        return $this;
    }


    // group
    // permet d'ajouter une ou plusieurs entrées à une clause group
    // valide pour select
    final public function group(...$values):self
    {
        return $this->many('group',...Base\Obj::casts(1,...$values));
    }


    // order
    // permet d'ajouter une entrée à une clause order
    // valide pour select, show, update et delete
    final public function order(...$value):self
    {
        return $this->one('order',...$value);
    }


    // orders
    // permet d'ajouter plusieurs entrées à une clause order
    // valide pour select, show, update et delete
    final public function orders(...$values):self
    {
        return $this->many('order',...$values);
    }


    // limit
    // permet d'ajouter une entrée à une clause limit
    // valide pour select, show, update et delete
    final public function limit(...$value):self
    {
        return $this->one('limit',...$value);
    }


    // page
    // permet d'ajouter une entrée à une clause limit via deux valeur numériques: page et limit
    // envoie une exception si page n'est pas au moins 1
    final public function page(int $page,int $limit):self
    {
        if($page <= 0)
        static::throw('pageMustBeAtLeast1');

        return $this->limit([$page=>$limit]);
    }


    // insertSet
    // permet d'ajouter une entrée à une clause insertSet
    // valide pour insert
    final public function insertSet($key,$value):self
    {
        return $this->one('insertSet',[Base\Obj::cast($key,1)=>$value]);
    }


    // insertSets
    // permet d'ajouter plusieurs entrées à une clause insertSet via un tableau associatif
    // valide pour insert
    final public function insertSets(array $value):self
    {
        return $this->many('insertSet',$value);
    }


    // updateSet
    // permet d'ajouter une entrée à une clause updateSet
    // valide pour update
    final public function updateSet($key,$value):self
    {
        return $this->one('updateSet',[Base\Obj::cast($key,1)=>$value]);
    }


    // updateSets
    // permet d'ajouter plusieurs entrées à une clause updateSet via un tableau associatif
    // valide pour update
    final public function updateSets(array $value):self
    {
        return $this->many('updateSet',$value);
    }


    // data
    // raccourci pour insertSet et updateSet selon le type
    final public function data($key,$value):self
    {
        $key = Base\Obj::cast($key,1);
        $method = $this->checkShortcut('data');

        if(!empty($method))
        $this->$method($key,$value);

        return $this;
    }


    // datas
    // raccourci pour insertSets et updateSets selon le type
    // comme date mais rajoute un s à la méthode
    final public function datas(array ...$values):self
    {
        $method = $this->checkShortcut('data');

        if(!empty($method))
        {
            $method .= 's';
            $this->$method(...$values);
        }

        return $this;
    }


    // col
    // raccourci pour createCol ou addCol selon le type
    final public function col(array ...$values):self
    {
        $method = $this->checkShortcut('col');

        if(!empty($method))
        $this->$method(...$values);

        return $this;
    }


    // createCol
    // permet d'ajouter plusieurs entrées à une clause createCol
    // valide pour create
    final public function createCol(array ...$values):self
    {
        return $this->many('createCol',...$values);
    }


    // createKey
    // permet d'ajouter plusieurs entrées à une clause createKey
    // valide pour create
    final public function createKey(array ...$values):self
    {
        return $this->many('createKey',...$values);
    }


    // addCol
    // permet d'ajouter plusieurs entrées à une clause addCol
    // valide pour alter
    final public function addCol(array ...$values):self
    {
        return $this->many('addCol',...$values);
    }


    // addKey
    // permet d'ajouter plusieurs entrées à une clause addKey
    // valide pour alter
    final public function addKey(array ...$values):self
    {
        return $this->many('addKey',...$values);
    }


    // alterCol
    // permet d'ajouter plusieurs entrées à une clause alterCol
    // valide pour alter
    final public function alterCol(array ...$values):self
    {
        return $this->many('alterCol',...$values);
    }


    // dropCol
    // permet d'ajouter plusieurs entrées à une clause dropCol
    // valide pour alter
    final public function dropCol(...$values):self
    {
        return $this->many('dropCol',...Base\Obj::casts(1,...$values));
    }


    // dropKey
    // permet d'ajouter plusieurs entrées à une clause dropKey
    // valide pour alter
    final public function dropKey(...$values):self
    {
        return $this->many('dropKey',...Base\Obj::casts(1,...$values));
    }


    // select
    // vide l'objet, change le type pour select
    // argument est whats
    final public function select(...$values):self
    {
        $this->setType('select');

        if(!empty($values))
        $this->whats(...$values);

        return $this;
    }


    // assoc
    // vide l'objet, change le type pour select avec output assoc
    // argument est whats
    final public function assoc(...$values):self
    {
        $this->setType('select');
        $this->setOutput('assoc');

        if(!empty($values))
        $this->whats(...$values);

        return $this;
    }


    // assocs
    // vide l'objet, change le type pour select avec output assocs
    // argument est whats
    final public function assocs(...$values):self
    {
        $this->setType('select');
        $this->setOutput('assocs');

        if(!empty($values))
        $this->whats(...$values);

        return $this;
    }


    // show
    // vide l'objet, change le type pour show
    // si c'est une string, envoie dans set/what
    final public function show($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('show');

        if(is_string($value))
        $this->set('what',$value);

        return $this;
    }


    // insert
    // vide l'objet, change le type pour insert
    // argument est into
    final public function insert($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('insert');

        if(is_string($value))
        $this->into($value);

        return $this;
    }


    // update
    // vide l'objet, change le type pour update
    // argument est table
    final public function update($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('update');

        if(is_string($value))
        $this->table($value);

        return $this;
    }


    // delete
    // vide l'objet, change le type pour delete
    // argument est from
    final public function delete($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('delete');

        if(is_string($value))
        $this->from($value);

        return $this;
    }


    // create
    // vide l'objet, change le type pour create
    // argument est table
    final public function create($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('create');

        if(is_string($value))
        $this->table($value);

        return $this;
    }


    // alter
    // vide l'objet, change le type pour alter
    // argument est table
    final public function alter($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('alter');

        if(is_string($value))
        $this->table($value);

        return $this;
    }


    // truncate
    // vide l'objet, change le type pour truncate
    // argument est table
    final public function truncate($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('truncate');

        if(is_string($value))
        $this->table($value);

        return $this;
    }


    // drop
    // vide l'objet, change le type pour drop
    // argument est table
    final public function drop($value=null):self
    {
        $value = Base\Obj::cast($value,2);
        $this->setType('drop');

        if(is_string($value))
        $this->table($value);

        return $this;
    }


    // parseLimit
    // parse la valeur limit de l'objet sql
    // retourne la limit, le offset et la page si disponible
    // possible de spécifier une clé et retourne seulement une des trois valeurs
    final public function parseLimit(?string $key=null)
    {
        $return = null;
        $limit = $this->get('limit');

        if(!empty($limit))
        {
            $return = Base\Nav::parseLimit($limit);

            if(is_string($key) && !empty($return))
            $return = (array_key_exists($key,$return))? $return[$key]:null;
        }

        if($return === null)
        static::throw('invalidLimit');

        return $return;
    }


    // getOffset
    // retourne la valeur offset de l'objet sql
    final public function getOffset():?int
    {
        return $this->parseLimit('offset');
    }


    // getLimit
    // retourne la valeur limit de l'objet sql
    final public function getLimit():?int
    {
        return $this->parseLimit('limit');
    }


    // getPage
    // retourne la page courant à partir de la clause limite de l'objet sql
    final public function getPage():?int
    {
        return $this->parseLimit('page');
    }


    // pageBase
    // méthode protégé qui fait des appels à la classe base/nav
    final protected function pageBase(string $method,?int $page=null,bool $cache=true,...$args)
    {
        $limit = $this->parseLimit();
        $page = (is_int($page))? $page:$limit['page'];
        return Base\Nav::$method($page,$this->triggerWhatCount($cache),$limit['limit'],...$args);
    }


    // isPage
    // retourne vrai si la page existe
    final public function isPage(?int $page=null,bool $cache=true):bool
    {
        return $this->pageBase('isPage',$page,$cache);
    }


    // isPageFull
    // retourne vrai si la page existe et est pleine
    final public function isPageFull(?int $page=null,bool $cache=true):bool
    {
        return $this->pageBase('isPageFull',$page,$cache);
    }


    // isSpecificInPage
    // retourne vrai si le id est dans la page
    final public function isSpecificInPage($value,?int $page=null,bool $cache=true):bool
    {
        $return = false;
        $page = (is_int($page))? $page:$this->getPage();
        $specificPage = $this->specificPage($value,$cache);

        if(is_int($specificPage) && $specificPage === $page)
        $return = true;

        return $return;
    }


    // pageMax
    // retourne la page maximale pour la requête
    final public function pageMax(bool $cache=true):?int
    {
        return Base\Nav::pageMax($this->triggerWhatCount($cache),$this->parseLimit('limit'));
    }


    // pageFromIndex
    // retourne un numéro de page à partir d'un index de valeur
    final public function pageFromIndex(int $index,bool $cache=true):?int
    {
        return Base\Nav::pageFromIndex($index,$this->triggerWhatCount($cache),$this->parseLimit('limit'));
    }


    // pages
    // retourne un tableau avec tous les numéros de page
    final public function pages(bool $cache=true):array
    {
        return Base\Nav::pages($this->triggerWhatCount($cache),$this->parseLimit('limit'));
    }


    // pagesPosition
    // retourne un tableau contenant toutes les pages et la position par rapport à la page courante
    final public function pagesPosition(?int $page=null,bool $cache=true):?array
    {
        return $this->pageBase('pagesPosition',$page,$cache);
    }


    // pagesClose
    // retourne un tableau contenant les pages entourant la page courante
    final public function pagesClose(?int $page=null,int $amount=3,bool $cache=true):?array
    {
        $return = null;
        $limit = $this->parseLimit();
        $page = (is_int($page))? $page:$limit['page'];
        $return = Base\Nav::pagesClose($page,$this->triggerWhatCount($cache),$limit['limit'],$amount);

        return $return;
    }


    // pageSpecificCount
    // retourne le nombre d'éléments contenu dans une page
    final public function pageSpecificCount(?int $page=null,bool $cache=true):?int
    {
        return $this->pageBase('pageSpecificCount',$page,$cache);
    }


    // pageFirst
    // retourne la première page
    final public function pageFirst(bool $cache=true):?int
    {
        return Base\Nav::pageFirst($this->triggerWhatCount($cache),$this->parseLimit('limit'));
    }


    // pagePrev
    // retourne la page précédente
    final public function pagePrev(?int $page=null,bool $cache=true):?int
    {
        return $this->pageBase('pagePrev',$page,$cache);
    }


    // pageNext
    // retourne la page suivante
    final public function pageNext(?int $page=null,bool $cache=true):?int
    {
        return $this->pageBase('pageNext',$page,$cache);
    }


    // pageLast
    // retourne la dernière page
    final public function pageLast(bool $cache=true):?int
    {
        return Base\Nav::pageLast($this->triggerWhatCount($cache),$this->parseLimit('limit'));
    }


    // general
    // retourne un tableau contenant un maximum d'informations relatives aux pages
    // first et last seulement retourné si différent de prev/next
    final public function general(?int $page=null,int $amount=3,bool $cache=true):?array
    {
        return $this->pageBase('general',$page,$cache,$amount);
    }


    // pagesWithSpecific
    // retourne un tableau multidimensionnel avec les pages et les ids contenus dans chaque page
    final public function pagesWithSpecific():?array
    {
        $return = null;
        $primary = $this->primary();
        $limit = $this->getLimit();

        $sql = clone $this;
        $sql->unset('what');
        $sql->unset('limit');
        $sql->what($primary);
        $sql->setOutput('columns');
        $ids = $sql->trigger();

        if(!empty($ids))
        $return = Base\Nav::pagesWithSpecific($ids,$limit);

        return $return;
    }


    // pageWithSpecific
    // retourne les ids contenus dans une page
    final public function pageWithSpecific(?int $value=null):?array
    {
        $return = null;
        $value = (is_int($value))? $value:$this->getPage();

        if(is_int($value))
        {
            $primary = $this->primary();
            $limit = $this->getLimit();

            $sql = clone $this;
            $sql->unset('what');
            $sql->what($primary);
            $sql->page($value,$limit);
            $sql->setOutput('columns');

            $return = $sql->trigger();
        }

        else
        static::throw();

        return $return;
    }


    // pageFirstSpecific
    // retourne le premier id contenu dans la page
    final public function pageFirstSpecific(?int $value=null):?int
    {
        $return = null;
        $content = $this->pageWithSpecific($value);

        if(is_array($content))
        $return = current($content);

        return $return;
    }


    // pageLastSpecific
    // retourne le dernier id contenu dans la page
    final public function pageLastSpecific(?int $value=null):?int
    {
        $return = null;
        $content = $this->pageWithSpecific($value);

        if(is_array($content))
        $return = Base\Arr::valueLast($content);

        return $return;
    }


    // specificIndex
    // retourne le offset d'un id à l'intérieur de la requête
    final public function specificIndex($value)
    {
        $return = null;
        $value = Base\Obj::cast($value,4);
        $table = $this->checkTable();
        $primary = $this->primary();
        $where = $this->get('where');
        $order = $this->get('order');

        $tableName = $this->syntaxCall('tick',$table).' t';
        $what = ['t.'.$primary];
        if(!empty($where))
        $what = Base\Arr::mergeUnique($what,$this->syntaxCall('whatFromWhere',$where,'t'));
        $what[] = ['@rownum := @rownum + 1','position'];

        $innerSql = clone $this;
        $innerSql->select(...$what);
        $innerSql->table($tableName);
        $innerSql->set('join','(SELECT @rownum := 0) r');
        $innerSql->set('where',$where);
        $innerSql->set('order',$order);
        $innerSql = $innerSql->_cast().' x';

        $sql = clone $this;
        $sql->select('x.position');
        $sql->table($innerSql);
        $sql->set('where',$where);
        $sql->where($primary,'=',$value);
        $sql->setOutput('column');

        $position = $sql->trigger();

        if(is_numeric($position))
        {
            $position = (int) $position;
            if($position > 0)
            $return = ($position - 1);
        }

        return $return;
    }


    // specificPage
    // retourne le numéro de page d'un id spécifique dans la requête
    final public function specificPage($value,bool $cache=true):?int
    {
        $return = null;
        $value = $this->specificIndex($value);

        if(is_int($value))
        {
            $whatCount = $this->triggerWhatCount($cache);
            $limit = $this->getLimit();
            $return = Base\Nav::pageFromIndex($value,$whatCount,$limit);
        }

        return $return;
    }


    // specificFirst
    // retourne le premier id qui serait retourné par la requête
    final public function specificFirst():?int
    {
        $return = null;
        $primary = $this->primary();

        $sql = clone $this;
        $sql->unset('what');
        $sql->what($primary);
        $sql->limit(1);
        $sql->setOutput('column');
        $return = $sql->trigger();

        return $return;
    }


    // specificPrev
    // retourne le id précédent la valeur donnée en argument
    // value peut être un index si isIndex est true
    final public function specificPrev($value,?int $index=null):?int
    {
        $return = null;

        if($index === null)
        $index = $this->specificIndex($value);

        if(is_int($index) && $index > 0)
        {
            $primary = $this->primary();
            $offset = ($index - 1);

            $sql = clone $this;
            $sql->unset('what');
            $sql->unset('limit');
            $sql->what($primary);
            $sql->limit(1,$offset);
            $sql->setOutput('column');
            $return = $sql->trigger();
        }

        return $return;
    }


    // specificPrevInPage
    // retourne le id précédant dans la même page
    // value peut être un index si isIndex est true
    final public function specificPrevInPage($value,?int $index=null,bool $cache=true):?int
    {
        $return = null;
        $page = $this->specificPage($value,$cache);
        $prev = $this->specificPrev($value,$index);

        if(is_int($page) && is_int($prev) && $this->specificPage($prev,$cache) === $page)
        $return = $prev;

        return $return;
    }


    // specificNext
    // retourne le id suivant la valeur donnée en argument
    // value peut être un index si isIndex est true
    final public function specificNext($value,?int $index=null):?int
    {
        $return = null;

        if($index === null)
        $index = $this->specificIndex($value);

        if(is_int($index))
        {
            $offset = ($index + 1);
            $primary = $this->primary();

            $sql = clone $this;
            $sql->unset('what');
            $sql->unset('limit');
            $sql->what($primary);
            $sql->limit(1,$offset);
            $sql->setOutput('column');

            $return = $sql->trigger();
        }

        return $return;
    }


    // specificNextInPage
    // retourne le id suivant dans la même page
    // value peut être un index si isIndex est true
    final public function specificNextInPage($value,?int $index=null,bool $cache=true):?int
    {
        $return = null;
        $page = $this->specificPage($value,$cache);
        $next = $this->specificNext($value,$index);

        if(is_int($page) && is_int($next) && $this->specificPage($next,$cache) === $page)
        $return = $next;

        return $return;
    }


    // specificLast
    // retourne le dernier id qui serait retourné par la requête
    final public function specificLast(bool $cache=true):?int
    {
        $return = null;
        $primary = $this->primary();
        $offset = $this->triggerWhatCount($cache);

        if(is_int($offset) && $offset > 0)
        {
            $offset = ($offset - 1);

            $sql = clone $this;
            $sql->unset('what');
            $sql->unset('limit');
            $sql->what($primary);
            $sql->limit(1,$offset);
            $sql->setOutput('column');
            $return = $sql->trigger();
        }

        return $return;
    }


    // specific
    // retourne un tableau avec un maximum d'information sur un id à l'intérieur d'une requête
    // retourne le offset, premier, précédent, suivant et dernier
    // first et last seulement retourné si différent de prev/next
    final public function specific($value,bool $cache=true):?array
    {
        $return = null;
        $value = Base\Obj::cast($value,4);
        $this->checkType('select');
        $index = $this->specificIndex($value);

        if(is_int($index))
        {
            $return = null;
            $first = $this->specificFirst();
            $prev = $this->specificPrev($value,$index);
            $next = $this->specificNext($value,$index);
            $last = $this->specificLast();

            $return['value'] = $value;
            $return['index'] = $index;
            $return['position'] = ($index + 1);
            $return['total'] = $this->triggerWhatCount($cache);
            $return['page'] = $this->pageFromIndex($index);

            $return['first'] = ($first !== $value && $first !== $prev)? $first:null;
            $return['prev'] = $prev;
            $return['next'] = $next;
            $return['last'] = ($last !== $value && $last !== $next)? $last:null;
        }

        return $return;
    }


    // fromArray
    // méthode utilisé pour paramétrer un objet sql à partir d'un tableau
    // support pour what,  where, filter, in, notIn, order, direction, page et limit
    // parfait pour une navigation pour page general
    // note: order id asc est ajouter par défaut, ceci avant de forcer un deuxième sort si la variable order est identique (ceci peut crée un problème dans le calcul de l'index pour navigation specifique)
    public function fromArray(array $array):self
    {
        $array = Base\Obj::cast($array);
        $what = $array['what'] ?? null;
        $where = $array['where'] ?? null;
        $filter = $array['filter'] ?? null;
        $in = $array['in'] ?? null;
        $notIn = $array['notIn'] ?? null;
        $order = $array['order'] ?? null;
        $direction = $array['direction'] ?? null;
        $page = $array['page'] ?? null;
        $limit = $array['limit'] ?? null;
        $primary = $this->primary();

        if(!empty($what))
        {
            if(!is_array($what))
            $what = [$what];

            $this->whats(...array_values($what));
        }

        if(is_array($where) && !empty($where))
        $this->wheresOne($where);

        if(is_array($filter) && !empty($filter))
        $this->filter($filter);

        if(is_array($in) && !empty($in))
        $this->where($primary,'in',$in);

        if(is_array($notIn) && !empty($notIn))
        $this->where($primary,'notIn',$notIn);

        if(!empty($order))
        {
            if(!empty($direction))
            $this->order($order,$direction);

            elseif(is_array($order))
            $this->order($order);
        }

        if($order !== $primary)
        $this->order($primary,'asc');

        if(is_int($limit) && $limit > 0)
        {
            if(is_int($page) && $page > 0)
            $this->page($page,$limit);

            else
            $this->limit($limit);
        }

        return $this;
    }


    // trigger
    // lance la requête et retourne le résultat
    // possibilité de changer le output et les options pour le trigger, sans affecter l'objet
    final public function trigger($output=null,?array $option=null)
    {
        $return = null;
        $db = $this->db();
        $output ??= $this->getOutput();
        $make = $this->checkMake($output,$option);
        $return = $db->query($make,$output);

        return $return;
    }


    // triggerCount
    // pour les requêtes de type select
    // permet de retourner un count via la meilleure façon pour la requête
    final public function triggerCount(bool $cache=false):?int
    {
        return (empty($this->get('limit')))? $this->triggerWhatCount($cache):$this->triggerRowCount($cache);
    }


    // triggerTableCount
    // retourne le nombre de ligne dans la table, peu importe le reste de la requête
    // possible de mettre le retour en cache
    public function triggerTableCount(bool $cache=false):?int
    {
        $return = null;
        $this->checkType('select');
        $table = $this->checkTable();

        if($cache === true)
        $return = $this->count['what'] ?? null;

        if($return === null)
        {
            $db = $this->db();
            $return = $db->selectCount($table);

            if($cache === true)
            $this->count['table'] = $return;
        }

        return $return;
    }


    // triggerWhatCount
    // pour les requêtes de type select
    // permet de retourner un count en utlisant la méthode count dans what
    // ne tient pas compte de la clause limite
    final public function triggerWhatCount(bool $cache=false):?int
    {
        $return = null;
        $this->checkType('select');

        if($cache === true)
        $return = $this->count['what'] ?? null;

        if($return === null)
        {
            $sql = clone $this;
            $primary = $this->primary();

            $sql->set('what',[[$primary,'count()']]);
            $sql->unset('limit');
            $sql->setOutput('column');

            $return = $sql->trigger();

            if($cache === true)
            $this->count['what'] = $return;
        }

        return $return;
    }


    // triggerRowCount
    // pour les requêtes de type select
    // permet de retourner un count en utilisant le output rowCount
    // tient compte de la clause limite
    final public function triggerRowCount(bool $cache=false):?int
    {
        $return = null;
        $this->checkType('select');

        if($cache === true)
        $return = $this->count['row'] ?? null;

        if($return === null)
        {
            $sql = clone $this;
            $primary = $this->primary();

            $sql->set('what',[$primary]);
            $sql->setOutput('rowCount');

            $return = $sql->trigger();

            if($cache === true)
            $this->count['row'] = $return;
        }

        return $return;
    }


    // isTriggerCountEmpty
    // retourne vrai si la requête sql contient des lignes
    // par défaut, garde en cache
    final public function isTriggerCountEmpty(bool $cache=true):bool
    {
        return empty($this->triggerCount($cache));
    }


    // isTriggerCountNotEmpty
    // retourne vrai si la requête sql ne contient pas de lignes
    // par défaut, garde en cache
    final public function isTriggerCountNotEmpty(bool $cache=true):bool
    {
        return !empty($this->triggerCount($cache));
    }


    // emulate
    // retourne la version émulée de la requête
    final public function emulate(?array $option=null):?string
    {
        $return = null;
        $make = $this->make($option);

        if(!empty($make))
        $return = $this->db()->emulate($make['sql'],$make['prepare'] ?? null);

        return $return;
    }


    // debug
    // retourne le tableau de débogagge
    final public function debug(?array $option=null):?array
    {
        return $this->db()->debug($this->make($option));
    }
}
?>