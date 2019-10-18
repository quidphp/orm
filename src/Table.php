<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// table
// class to represent an existing table within a database
class Table extends Main\ArrObj implements Main\Contract\Import
{
    // trait
    use _dbAccess;
    use Main\_attr;
    use Main\_permission;


    // config
    public static $config = [
        'ignore'=>null, // défini si la table est ignoré
        'parent'=>null, // nom du parent de la classe table, possible aussi de mettre une classe
        'priority'=>null, // code de priorité de la table
        'search'=>true, // la table est cherchable
        'searchMinLength'=>3, // longueur minimum pour une recherche
        'label'=>null, // chemin label qui remplace le défaut dans lang
        'description'=>null, // chemin description qui remplace le défaut dans lang
        'key'=>['key',0], // colonne(s) utilisé pour key
        'active'=>'active', // colonne(s) utilisé pour déterminer si une ligne est active
        'name'=>['name_[lang]','name','id',0], // colonne(s) utilisé pour le nom d'une ligne
        'content'=>['content_[lang]','content'], // colonne(s) utilisé pour le contenu d'une ligne
        'relation'=>['what'=>true], // champs pour représenter le what, order et output de la relation, si what est true utilise la colonne via name
        'where'=>null, // where par défaut pour la table
        'filter'=>null, // filter par défaut pour la table
        'like'=>'like', // méthode à utiliser pour like
        'order'=>['order'=>'asc','date'=>'desc','name_[lang]'=>'asc','key'=>'asc','id'=>'desc'], // ordre et direction à utiliser par défaut, prend la première qui existe
        'orderCode'=>2, // code d'ordre pour les relations
        'limit'=>20, // limit à utiliser par défaut
        'panel'=>true, // si panel sont actifs ou non
        'inRelation'=>true, // active ou non la validation que la valeur des relations sont dans la relation
        'logSql'=>[ // défini si le type de requête à la table doit être loggé
            'select'=>false,
            'show'=>false,
            'insert'=>true,
            'update'=>true,
            'delete'=>true,
            'create'=>true,
            'alter'=>true,
            'truncate'=>true,
            'drop'=>true],
        'cols'=>null, // paramètre pour colonne, si value d'une colonne est pas vide, vérifie l'existence dans colsLoad
        'colsExists'=>true, // si l'existance des colonne doit être validés
        'permission'=>[
            '*'=>[
                'access'=>true,
                'select'=>true,
                'show'=>true,
                'insert'=>false,
                'update'=>false,
                'delete'=>false,
                'create'=>false,
                'alter'=>false,
                'truncate'=>false,
                'drop'=>false,
                'nullPlaceholder'=>false]]  // marque NULL comme placeholder si null (plutôt que -)
    ];


    // replaceMode
    protected static $replaceMode = ['=key','=active','=name','=content','relation','=where','=filter','=order']; // défini les config à ne pas merger récursivement


    // dynamique
    protected $name = null; // nom de la table
    protected $attr = []; // talbeau des attributs
    protected $relation = null; // conserve une copie de l'objet de relation de la table
    protected $cols = null; // objet des colonnes
    protected $colsReady = false; // se met à true lorsque les colonnes sont toutes chargés
    protected $rows = null; // objet des lignes
    protected $classe = null; // objet tableClassse


    // construct
    // construit l'objet table
    public function __construct(string $name,Db $db,TableClasse $classe,array $attr)
    {
        $this->setName($name);
        $this->setLink($db);
        $this->setClasse($classe);
        $this->makeAttr($attr);
        $this->cols = $this->colsNew()->readOnly(true);
        $this->rows = $this->rowsNew()->readOnly(true);

        return;
    }


    // toString
    // retourne la nom de la table
    public function __toString():string
    {
        return $this->name();
    }


    // onColsLoad
    // est appelé après colsLoad
    // par défaut est utilisé pour faire un check de l'existance des colonnes décritent dans config/cols si l'attribut colsExists est true
    // méthode protégé
    protected function onColsLoad():self
    {
        if($this->attr('colsExists') === true)
        {
            $array = $this->attr('cols');

            if(is_array($array) && !empty($array))
            {
                $cols = $this->cols();
                $missing = [];
                $configExists = Col::$config['exists'];

                foreach ($array as $key => $value)
                {
                    if(is_string($key) && !empty($value))
                    {
                        $exists = (is_bool($value))? $value:$configExists;

                        if(is_array($value))
                        {
                            if(array_key_exists('exists',$value) && is_bool($value['exists']))
                            $exists = $value['exists'];

                            if(array_key_exists('ignore',$value) && $value['ignore'] === true)
                            $exists = false;
                        }

                        if($exists === true && !$cols->exists($key))
                        $missing[] = $key;
                    }
                }

                if(!empty($missing))
                static::throw($this,...$missing);
            }
        }

        return $this;
    }


    // onMakeAttr
    // callback avant de mettre les attributs dans la propriété attr
    // méthode protégé
    protected function onMakeAttr(array $return):array
    {
        return $return;
    }


    // onCheckAttr
    // callback dès que les attributs ont été set
    // permet d'envoyer des exceptions si les attributs sont inadéquats pour la table
    protected function onCheckAttr():self
    {
        return $this;
    }


    // onTruncated
    // appelé après un truncate réussie via la méthode truncate
    // méthode protégé qui peut être étendu
    protected function onTruncated(array $option):self
    {
        return $this;
    }


    // onPermissionCan
    // callback avant chaque appel à permission can, vérifie que la table à la permission access
    protected function onPermissionCan($key,array $array):bool
    {
        return (array_key_exists('access',$array) && $array['access'] === true)? true:false;
    }


    // toArray
    // méthode utilisé pour obtenir du contenu tableau lors du remplacement via une méthode map
    public function toArray():array
    {
        return $this->keyValue(0,$this->attr('name'));
    }


    // cast
    // retourne la valeur cast
    public function _cast():string
    {
        return $this->name();
    }


    // attrAll
    // retourne le tableau des attributs
    // doit retourner une référence
    // est public car utilisé par cell
    public function &attrAll():array
    {
        return $this->attr;
    }


    // offsetGet
    // arrayAccess offsetGet fait appel à la méthode row si key est int, ou col si key est string
    // tente de charger la row si non existante
    // lance une exception si rien d'existant
    public function offsetGet($key)
    {
        $return = null;

        if(is_string($key))
        $return = $this->col($key);

        else
        $return = $this->row($key);

        if(!is_object($return))
        static::throw('arrayAccess','doesNotExist');

        return $return;
    }


    // offsetSet
    // arrayAccess offsetSet n'est pas permis pour la classe
    public function offsetSet($key,$value):void
    {
        static::throw('arrayAccess','setNotAllowed');

        return;
    }


    // offsetUnset
    // unlink une row ou envoie une exception si row non loader
    public function offsetUnset($key):void
    {
        if(is_int($key) && $this->hasRow($key))
        $this->row($key)->unlink();

        else
        static::throw('arrayAcces','doesNotExist');

        return;
    }


    // arr
    // retourne le tableau de rows
    protected function arr():array
    {
        return $this->rows()->toArray();
    }


    // isLinked
    // retourne vrai si la table est lié à l'objet db
    public function isLinked():bool
    {
        return ($this->hasDb() && $this->db()->table($this) === $this)? true:false;
    }


    // alive
    // retourne vrai si la table existe dans la base de données
    public function alive():bool
    {
        return ($this->db()->showTable($this) === $this->name())? true:false;
    }


    // shouldLogSql
    // retourne vrai si une requête pour la table devrait être loggé
    public function shouldLogSql(string $type):bool
    {
        $return = false;
        $log = $this->attr(['logSql',$type]);

        if($log === true)
        $return = true;

        return $return;
    }


    // permissionAll
    // retourne le tableau de la source des paramètres de rôles
    public function &permissionAll():array
    {
        return $this->attr['permission'];
    }


    // permissionDefaultRole
    // retourne le rôle courant
    public function permissionDefaultRole():Main\Role
    {
        return $this->db()->role();
    }


    // isSearchable
    // retourne vrai si la table est cherchable
    // si cols est true, il doit aussi y avoir une colonne cherchable dans la table
    public function isSearchable(bool $cols=true):bool
    {
        $return = ($this->attr('search') === true)? true:false;

        if($return === true && $cols === true)
        {
            $return = false;

            foreach ($this->cols() as $key => $value)
            {
                if($value->isSearchable())
                {
                    $return = true;
                    break;
                }
            }
        }

        return $return;
    }


    // isSearchTermValid
    // retourne vrai si le terme de la recherche est valide pour la table
    // valeur peut être scalar, un tableau à un ou plusieurs niveau
    // si c'est un tableau la longueur totale de l'ensemble des termes est considéré
    public function isSearchTermValid($value):bool
    {
        $return = false;
        $minLength = $this->searchMinLength();

        if(is_array($value))
        $value = Base\Arrs::implode('',$value);

        if(is_string($value) && strlen($value) >= $minLength)
        $return = true;

        return $return;
    }


    // sameTable
    // retourne vrai si l'objet et celui fourni ont la même table
    public function sameTable($table):bool
    {
        return ($this->db()->hasTable($table) && $this === $this->db()->table($table))? true:false;
    }


    // hasPanel
    // retourne vrai si la table a des panels
    public function hasPanel():bool
    {
        return ($this->attr('panel') === true)? true:false;
    }


    // setClasse
    // stock l'objet tableClasse
    // méthode protégé
    protected function setClasse(TableClasse $classe):self
    {
        $this->classe = $classe;

        return $this;
    }


    // classe
    // retourne l'objet tableClasse
    public function classe():TableClasse
    {
        return $this->classe;
    }


    // setLink
    // set la tables et db à l'objet
    // envoie une exception si l'objet table existe déjà
    // méthode protégé
    protected function setLink(Db $value):self
    {
        $this->setDb($value);

        if($this->db()->hasTable($this->name()))
        static::throw('alreadyInstantiated',$this->name());

        return $this;
    }


    // setName
    // change le nom de la table après validation
    // méthode protégé
    protected function setName(string $name):self
    {
        if(Base\Validate::isTable($name))
        $this->name = $name;

        else
        static::throw($name,'needsLowerCaseFirstChar','noComplexChars');

        return $this;
    }


    // name
    // retourne le nom de la table
    public function name():string
    {
        return $this->name;
    }


    // makeAttr
    // merge le tableau de propriété dbAttr avec le tableau static config et le tableau config de row
    // les clés avec valeurs null dans static config ne sont pas conservés
    // si l'attribut contient la clé du type de l'application, ceci aura priorité sur tout le reste (dernier merge)
    // lance onMakeAttr avant d'écrire dans la propriété
    // le merge est unidimensionnel sauf pour la clé cols
    protected function makeAttr(array $dbAttr):self
    {
        $db = $this->db();
        $rowClass = $this->rowClass();
        $rowAttr = $rowClass::config();
        $baseAttr = [];
        $tableAttr = $db->tableAttr($this);
        $callable = static::getInitCallable();

        foreach (static::$config as $key => $value)
        {
            if($value !== null)
            $baseAttr[$key] = $value;
        }

        $attr = $callable(static::class,$dbAttr,$baseAttr,$tableAttr,$rowAttr);
        $attr['parent'] = $this->makeAttrParent($attr['parent'] ?? null);

        $attr = $this->onMakeAttr($attr);
        $this->checkAttr($attr);
        $this->attr = $attr;
        $this->onCheckAttr();

        return $this;
    }


    // makeAttrParent
    // gère l'attribut parent si c'est un nom de classe de table ou de row
    protected function makeAttrParent(?string $return):?string
    {
        if(is_string($return) && Base\Classe::extendOne(Tables::keyClassExtends(),$return))
        $return = $return::className(true);

        return $return;
    }


    // checkAttr
    // fait un check sur les attributs, vérifie parent et priority
    // méthode protégé
    protected function checkAttr(array $attr):self
    {
        if(array_key_exists('parent',$attr))
        {
            if(is_string($attr['parent']))
            {
                if(!Base\Validate::isTable($attr['parent']))
                static::throw($this,'parentInvalidString');

                if($attr['parent'] === $this->name())
                static::throw($this,'parentCannotBeSelf');
            }

            elseif($attr['parent'] !== null)
            static::throw('invalidParent');
        }

        if(empty($attr['priority']) || !is_int($attr['priority']))
        static::throw('invalidPriority');

        return $this;
    }


    // parent
    // retourne le nom de parent de la table, ou null
    public function parent():?string
    {
        return $this->attr('parent');
    }


    // priority
    // retourne le code de priorité de la table
    public function priority():int
    {
        return $this->attr('priority');
    }


    // where
    // retourne le where par défaut pour la table, possible d'append un tableau
    public function where($value=null):array
    {
        return $this->commonWhereFilter('where',$value);
    }


    // filter
    // retourne le filter par défaut pour la table, possible d'append un tableau
    public function filter($value=null):array
    {
        return $this->commonWhereFilter('filter',$value);
    }


    // commonWhereFilter
    // méthode utilisé par where et filter
    // méthode protégé
    protected function commonWhereFilter(string $type,$value=null):array
    {
        $return = $this->attr($type);
        $true = false;
        $return = $this->commonWhereFilterArg($return,$true);
        $value = $this->commonWhereFilterArg($value,$true);

        if(empty($return))
        $return = $value;

        else
        $return = Syntax::whereAppend($return,$value);

        return $return;
    }


    // commonWhereFilterArg
    // méthode utilisé par commonWhereFilter pour traiter la valeur dans attribut ou l'argument value
    // les callables sont gérés
    // méthode protégé
    protected function commonWhereFilterArg($return,bool &$true):array
    {
        if(static::classIsCallable($return))
        $return = $return($this);

        if($true === false)
        {
            if($return === true)
            {
                $true = true;
                $return = $this->whereFilterTrue();
            }

            elseif(is_array($return) && in_array(true,$return,true))
            {
                $true = true;
                $return = Syntax::removeDefault($return);
                $return = Syntax::whereAppend($return,$this->whereFilterTrue());
            }
        }

        $return = Base\Call::digStaticMethod($return);
        $return = Syntax::removeDefault($return);

        return $return;
    }


    // whereFilterTrue
    // retourne where ou filter à utiliser si la valeur de l'attribut est true
    // retourne la colonne active à 1 si existante
    // retourne toutes les colonnes requises
    public function whereFilterTrue():array
    {
        $return = [];
        $active = $this->colActive();
        $required = $this->cols()->filter(['isRequired'=>true]);

        if(!empty($active))
        $return = [$active->name()=>1];

        if(!empty($required))
        {
            foreach ($required as $col)
            {
                $return[] = [$col->name(),true];
            }
        }

        return $return;
    }


    // whereFilter
    // retourne where et filter combiné
    public function whereFilter(?array $value=null,string $method='findInSet'):array
    {
        $return = (array) $this->where($value);
        $filter = $this->filter();

        if(!empty($filter))
        {
            foreach ($filter as $k => $v)
            {
                if(!empty($v))
                $return[] = [$k,$method,$v];

                else
                $return[] = [$k,null];
            }
        }

        return $return;
    }


    // whereAll
    // retourne une variable where a utilisé pour prendre toutes les lignes de la table
    public function whereAll():array
    {
        $return = [];
        $primary = $this->primary();
        $return[] = [$primary,'>=',1];

        return $return;
    }


    // like
    // retourne le pattern like à utiliser pour la table
    public function like():?string
    {
        return $this->attr('like');
    }


    // searchMinLength
    // retourne le longueur minimale pour une recherche dans la table
    public function searchMinLength():int
    {
        return $this->attr('searchMinLength');
    }


    // order
    // retourne l'ordre et direction à utiliser par défaut
    // prend la première colonne existente et qui est ordonnable
    // possible de retourner order sous forme associative ou non, par défaut oui
    // possible aussi de retourner seulement une valeur du tableau
    // envoie une exception si vide
    public function order($get=true)
    {
        $return = null;
        $order = $this->attr('order');

        if(is_array($order))
        {
            foreach ($order as $key => $value)
            {
                if($this->hasCol($key))
                {
                    $col = $this->col($key);

                    if($col->isOrderable())
                    {
                        $direction = strtolower($value);

                        if($get === true)
                        $return = [$col->name()=>strtolower($value)];

                        else
                        $return = ['order'=>$col,'direction'=>$direction];

                        if(is_string($get))
                        $return = (array_key_exists($get,$return))? $return[$get]:null;

                        break;
                    }
                }
            }
        }

        if(empty($return))
        static::throw();

        return $return;
    }


    // limit
    // retourne la limite par défaut
    public function limit():int
    {
        return $this->attr('limit');
    }


    // default
    // retourne les défaut à utiliser pour la classe base sql
    // défaut possible pour where et order
    // seuls les requêtes de type select, update ou delete peuvent utiliser les défaut
    public function default():?array
    {
        return ['where'=>$this->where(true),'order'=>$this->order()];
    }


    // status
    // retourne le tableau de status de la table
    // possible de mettre le résultat en cache
    public function status(bool $cache=true):array
    {
        return $this->cache(__METHOD__,function() {
            return $this->db()->showTableStatus($this);
        },$cache);
    }


    // engine
    // retourne l'engin utilisé par la table, tel que décrit dans table status
    public function engine(bool $cache=true):string
    {
        return Base\Arr::get('Engine',$this->status($cache));
    }


    // autoIncrement
    // retourne le autoIncrement de la table, tel que décrit dans table status
    // par défaut, n'utilise pas la cache
    public function autoIncrement(bool $cache=false):int
    {
        return Base\Arr::get('Auto_increment',$this->status($cache));
    }


    // collation
    // retourne la collation de la table, tel que décrit dans table status
    public function collation(bool $cache=true):string
    {
        return Base\Arr::get('Collation',$this->status($cache));
    }


    // updateTime
    // retourne la date de dernière mise à jour de la table
    // retounre un timestamp ou une date formatté
    public function updateTime($format=null,bool $cache=true)
    {
        $return = null;
        $value = Base\Arr::get('Update_time',$this->status($cache));
        if(is_string($value))
        {
            $return = Base\Date::time($value,'sql');

            if(is_int($return) && $format !== null)
            $return = Base\Date::format($format,$return);
        }

        return $return;
    }


    // primary
    // retourne la clé primaire de la table
    public function primary():string
    {
        return $this->db()->primary();
    }


    // isColLinked
    // retourne vrai si l'objet col est linked
    public function isColLinked(Col $col):bool
    {
        return ($this->cols->in($col))? true:false;
    }


    // hasCol
    // retourne vrai si la colonne existe dans la table
    public function hasCol(...$keys):bool
    {
        return $this->cols()->exists(...$keys);
    }


    // isColsReady
    // retourne vrai si l'objet colonne est entièrement chargé
    public function isColsReady():bool
    {
        return ($this->colsReady === true)? true:false;
    }


    // isColsEmpty
    // retourne vrai si cols est empty, donc n'a jamais été initialisé
    // ceci permet d'éviter la méthode cols si pas nécessaire
    public function isColsEmpty():bool
    {
        return $this->cols->isEmpty();
    }


    // setColsReady
    // permet de changer la valeur à l'attribut colsReady
    protected function setColsReady(bool $value=true):self
    {
        $this->colsReady = $value;

        return $this;
    }


    // colsNew
    // crée et retourne l'objet cols
    // si les colonnes n'ont pas encore été chargés, elles le seront
    public function colsNew():Cols
    {
        $return = null;
        $class = $this->classe()->cols();

        if(!empty($class))
        $return = new $class();
        else
        static::throw('noColsClass');

        return $return;
    }


    // colsCount
    // compte le nombre total de colonne dans la table
    // si count est true, fait une requête dans la base de donnée
    // si cache est true, le résultat de la requête est mis en cache
    public function colsCount(bool $count=false,bool $cache=false):int
    {
        $return = 0;

        if($this->isColsEmpty())
        {
            if($count === true)
            {
                $return = $this->cache(__METHOD__,function() {
                    return $this->db()->selectTableColumnCount($this);
                },$cache);
            }
        }

        else
        $return = $this->cols()->count();

        return $return;
    }


    // colsLoad
    // charge toutes les colonnes de la table, sauf celles ignorés
    // onColsLoad est appelé après la création de toutes les colonnes
    public function colsLoad():self
    {
        $this->checkLink();

        if($this->isColsEmpty())
        {
            $db = $this->db();
            $dbCols = $db->schema()->table($this);

            if(!empty($dbCols))
            {
                $priority = 0;
                $dbClasse = $db->classe();
                $increment = $db->getPriorityIncrement();
                $this->cols->readOnly(false);

                foreach ($dbCols as $value => $dbAttr)
                {
                    $dbAttr = ColSchema::prepareAttr($dbAttr);

                    if(is_string($value) && is_array($dbAttr))
                    {
                        $class = $dbClasse->tableClasseCol($this,$value,$dbAttr);

                        if(!empty($class))
                        {
                            $priority += $increment;
                            $dbAttr['priority'] = $priority;
                            $col = $this->colMake($class,$value,$dbAttr);
                            $dbClasse->tableClasseCell($this,$col);
                        }

                        else
                        static::throw('noColClass');
                    }

                    else
                    static::throw('invalidCol',$value);
                }

                $this->cols()->sortDefault()->readOnly(true);
                $this->onColsLoad();
                $this->setColsReady(true);
            }

            else
            static::throw('tableHasNoCol');
        }

        else
        static::throw('alreadyLoaded');

        return $this;
    }


    // colMake
    // construit et store un objet colonne
    // méthode protégé
    protected function colMake(string $class,string $value,array $dbAttr):Col
    {
        $return = new $class($value,$this,$dbAttr);

        if(!$return->isIgnored())
        $this->cols->add($return);

        return $return;
    }


    // colAttr
    // retourne un tableau des attributs de la colonne présent dans config de la table
    // peut retourner null, utiliser par dbClasse, a plus de priorité que db/colAttr
    public function colAttr(string $col):?array
    {
        $return = $this->attr['cols'][$col] ?? null;

        if(is_string($return))
        static::throw($this,$col,'stringNotAllowed',$return);

        $return = Base\Arr::replace($this->db()->colAttr($col),$return);

        return $return;
    }


    // cols
    // retourne l'objet des colonnes
    // charge les colonnes si l'objet cols est toujours vide
    public function cols(...$keys):Cols
    {
        $return = null;

        if($this->isColsEmpty())
        $this->colsLoad();

        $return = (empty($keys))? $this->cols:$this->cols->gets(...$keys);

        return $return;
    }


    // col
    // retourne l'objet d'une colonne
    // peut fournir un index, un tableau qui retournera la première existante, une string, une colonne ou une cellule
    // envoie une exception si non existant
    public function col($col):Col
    {
        $return = $this->cols()->get($col);

        if(!$return instanceof Col)
        static::throw($col);

        return $return;
    }


    // colPattern
    // retourne l'objet d'une colonne ou null
    // si un pattern est fourni, passe dans base/col addPattern
    // sinon si la colonne n'existe pas rajoute tous les patterns possibles dans le nom
    // sauf les patterns en lien avec la langue et qui n'est pas la langue courante
    public function colPattern(string $col,?string $pattern=null):?Col
    {
        $return = null;

        if(is_string($pattern))
        $col = ColSchema::addPattern($pattern,$col);

        elseif(!$this->hasCol($col))
        $col = ColSchema::possible($col,true);

        if(!empty($col) && $this->hasCol($col))
        $return = $this->col($col);

        return $return;
    }


    // colActive
    // retourne la colonne active
    // peut retourner null, n'envoie pas d'exception
    public function colActive():?Col
    {
        $return = null;
        $active = $this->attr('active');

        if(!empty($active) && $this->hasCol($active))
        $return = $this->col($active);

        return $return;
    }


    // colKey
    // retourne la colonne key ou envoie une exception si non existante
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    public function colKey(?string $lang=null):Col
    {
        $return = $this->col($this->attr('key'));

        if(is_string($lang) && !empty($return))
        $return = $this->colPattern($return->nameStripPattern(),$lang);

        return $return;
    }


    // colName
    // retourne la name key ou envoie une exception si non existante
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    public function colName(?string $lang=null):Col
    {
        $return = $this->col($this->attr('name'));

        if(is_string($lang) && !empty($return))
        $return = $this->colPattern($return->nameStripPattern(),$lang);

        return $return;
    }


    // colContent
    // retourne la colonne content ou envoie une exception si non existante
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    public function colContent(?string $lang=null):Col
    {
        $return = $this->col($this->attr('content'));

        if(is_string($lang) && !empty($return))
        $return = $this->colPattern($return->nameStripPattern(),$lang);

        return $return;
    }


    // isRowLinked
    // retourne vrai si l'objet row est linked
    public function isRowLinked(Row $row):bool
    {
        return ($this->rows->in($row))? true:false;
    }


    // hasRow
    // retourne vrai si la ligne existe dans la table
    // si la ligne n'existe pas, elle n'est pas chargé
    public function hasRow(...$keys):bool
    {
        return $this->rows()->exists(...$keys);
    }


    // isRowsEmpty
    // retourne vrai si la table ou rows est empty
    // si count est false, n'appele pas la méthode rows
    // cache est true par défaut
    public function isRowsEmpty(bool $count=false,bool $cache=true):bool
    {
        $return = false;

        if($count === false)
        $return = $this->rows->isEmpty();

        else
        $return = ($this->rowsCount($count,$cache) === 0)? true:false;

        return $return;
    }


    // isRowsNotEmpty
    // retourne vrai si rows n'est pas vide
    // si count est false, n'appele pas la méthode rows
    // cache est true par défaut
    public function isRowsNotEmpty(bool $count=false,bool $cache=true)
    {
        return ($this->isRowsEmpty($count,$cache))? false:true;
    }


    // rowsNew
    // crée et retourne l'objet rows
    public function rowsNew():Rows
    {
        $return = null;
        $class = $this->rowsClass();

        if(!empty($class))
        $return = new $class();
        else
        static::throw('noRowsClass');

        return $return;
    }


    // rowsCount
    // compte le nombre total de ligne dans la table
    // si count est true, fait une requête dans la base de donnée
    // si cache est true, le résultat de la requête est mis en cache
    public function rowsCount(bool $count=false,bool $cache=false,$where=null):int
    {
        $return = 0;

        if($count === true)
        {
            $return = $this->cache(__METHOD__,function() use($where) {
                return $this->db()->selectCount($this,$where);
            },$cache);
        }

        else
        $return = $this->rows()->count();

        return $return;
    }


    // rowsLoad
    // charge toutes les ligne de la table pas encore chargé
    // retourne l'objet rows
    public function rowsLoad():Rows
    {
        $return = $this->rows();

        if($return->isEmpty() || $this->rowsCount(true,true) !== $return->count())
        {
            $this->checkLink();
            $primaries = $return->primaries();
            $where = (!empty($primaries))? [['id','notIn',$primaries]]:null;
            $rows = $this->db()->selectAllsPrimary($this,$where,[$this->primary()=>'asc']);

            if(!empty($rows))
            {
                foreach ($rows as $key => $value)
                {
                    if(is_int($key) && is_array($value))
                    $this->rowMake($key,$value);
                }
            }
        }

        return $return;
    }


    // rowsValue
    // retourne un tableau avec les idées de row pour un maximum de situation
    // si row est string, utilisé la colKey
    public function rowsValue($row=null):array
    {
        $return = [];

        if($row === true || is_array($row))
        $return = $this->db()->selectColumns($this->primary(),$this,$row);

        elseif(is_string($row))
        $return = $this->db()->selectColumns($this->primary(),$this,[$this->colKey()->name()=>$row]);

        else
        $return = (array) $this->rowValue($row);

        return $return;
    }


    // rows
    // retourne l'objet rows de la table
    // ou un nouvel objet rows avec les rows donnés en argument
    // les lignes non existantes sont chargés
    // si values a un argument et c'est true, retourne le résultat de rowsLoad
    // si values a un argument et c'est false, retourne le résultat de rowsNew
    // si values est un paquet de ids, une seule requête sera faite
    public function rows(...$values):Rows
    {
        $return = null;

        if(count($values) === 1 && is_bool($values[0]))
        {
            if($values[0] === true)
            $return = $this->rowsLoad();

            elseif($values[0] === false)
            $return = $this->rowsNew();
        }

        elseif(empty($values))
        $return = $this->rows;

        else
        {
            $return = $this->rowsNew();
            $this->rowsMakeIds(...$values);

            foreach ($values as $value)
            {
                foreach ($this->rowsValue($value) as $id)
                {
                    if(is_int($id))
                    {
                        $row = $this->row($id);

                        if(!empty($row))
                        $return->add($row);
                    }
                }
            }
        }

        return $return;
    }


    // rowsMakeIds
    // permet d'optimiser le chargement de rows à partir d'un tableau de ids
    // méthode protégé
    protected function rowsMakeIds(...$values):self
    {
        if(Base\Arr::onlyNumeric($values))
        {
            foreach ($values as $k => $v)
            {
                if($this->hasRow($v))
                unset($values[$k]);
            }

            if(!empty($values))
            {
                $db = $this->db();
                $what = '*';
                $primary = $this->primary();
                $where = [];
                $where[] = [$primary,'in',$values];
                $assocs = $db->selectAssocsPrimary($what,$this,$where);

                if(!empty($assocs))
                {
                    foreach ($assocs as $id => $assoc)
                    {
                        $this->rowMake($id,$assoc);
                    }
                }
            }
        }

        return $this;
    }


    // rowsRefresh
    // retourne l'objet rows de la table avec toutes les lignes rafraîchit
    // ou un nouvel objet rows avec les rows donnés en argument rafraîchit
    // les rows non existantes ne sont pas rafraîchit donc pas chargé deux fois
    public function rowsRefresh(...$values):Rows
    {
        $return = (empty($values))? $this->rows:$this->rowsNew();

        if(!empty($values))
        {
            foreach ($values as $value)
            {
                $row = $this->rowRefresh($value);

                if(!empty($row))
                $return->add($row);
            }
        }

        else
        $return->refresh();

        return $return;
    }


    // rowsIn
    // retourne un nouvel objet rows avec les rows existantes donnés en argument
    // les rows non existante ne sont pas chargé
    public function rowsIn(...$values):Rows
    {
        return $this->rows()->gets(...$values);
    }


    // rowsInRefresh
    // retourne un nouvel objet rows avec les rows existantes donnés en argument
    // les rows non existante ne sont pas chargé, mais les rows retournés sont mis à jour
    public function rowsInRefresh(...$values):Rows
    {
        return $this->rows()->getsRefresh(...$values);
    }


    // rowsOut
    // retourne un nouvel objet rows avec les rows non existantes donnés en argument
    // les rows déjà existantes ne sont pas retournés
    public function rowsOut(...$values):Rows
    {
        $return = $this->rowsNew();

        if(!empty($values))
        {
            $rows = $this->rows();

            foreach ($values as $value)
            {
                if(!$rows->exists($value))
                {
                    $row = $this->row($value);

                    if(!empty($row))
                    $return->add($row);
                }
            }
        }

        return $return;
    }


    // rowsDelete
    // efface toutes les lignes de rows ou seulement celles donnés en argument
    // les lignes sont aussi unlink
    // retourne rows de table
    public function rowsDelete(...$values):Rows
    {
        $this->rows(...$values)->delete();

        return $this->rows();
    }


    // rowsUnlink
    // vide l'objet rows ou certaines rows de l'objet
    // les lignes présentes sont unlink
    // retourne rows de table
    public function rowsUnlink(...$values):Rows
    {
        $this->rows(...$values)->unlink();

        return $this->rows();
    }


    // rowsVisible
    // retourne un objet rows avec seulement les lignes visibles
    public function rowsVisible(...$values):Rows
    {
        return $this->rows(...$values)->filter(['isVisible'=>true]);
    }


    // rowsVisible
    // retourne un objet rows avec seulement les lignes visibles dans l'ordre par défaut de la table
    public function rowsVisibleOrder(...$values):Rows
    {
        return $this->rowsVisible(...$values)->order($this->order());
    }


    // rowsClass
    // retourne la classe à utiliser pour les rows
    public function rowsClass():string
    {
        return $this->classe()->rows();
    }


    // rowClass
    // retourne la classe à utiliser pour la row
    public function rowClass():string
    {
        return $this->classe()->row();
    }


    // rowMake
    // construit ou met à jour un objet row à partir de la valeur primaire
    // tableau data est facultatif
    // retourne la ligne ou null
    // les lignes ne sont pas ajoutés dans l'ordre
    // méthode protégé
    protected function rowMake(int $primary,?array $data=null):?Row
    {
        $return = null;
        $exception = false;

        if($primary > 0)
        {
            if(!$this->hasRow($primary))
            {
                $class = $this->rowClass();

                if(!empty($class))
                {
                    $rows = $this->rows();
                    $rows->readOnly(false);
                    $return = new $class($primary,$this);
                    $rows->add($return);

                    if(is_array($data) && !empty($data))
                    $return->cellsLoad($data);

                    if($return->cells()->isEmpty())
                    {
                        $return->refresh();

                        if(!$return->isLinked())
                        $return = null;
                    }

                    $rows->readOnly(true);
                }

                else
                static::throw('noClass');
            }

            elseif(is_array($data) && !empty($data))
            {
                $return = $this->row($primary);
                $return->cells()->sets($data);
            }

            else
            $exception = true;
        }

        else
        $exception = true;

        if($exception === true)
        static::throw('couldNotAddOrUpdate');

        return $return;
    }


    // rowValue
    // retourne le id de la row pour un maximum de situation
    // si row est string, utilisé la colKey
    // si whereTrue est à true, ajoute true dans le where s'il y a une requête à la db
    public function rowValue($row=null,bool $whereTrue=false):?int
    {
        $return = null;
        $where = null;

        if($row === true || is_array($row))
        $where = (array) $row;

        elseif(is_string($row))
        {
            $colKey = $this->colKey();
            if(!empty($colKey))
            $where = [$colKey->name()=>$row];
        }

        if(is_array($where))
        {
            if($whereTrue === true && !in_array(true,$where,true))
            $where[] = true;

            $row = $this->db()->selectColumn($this->primary(),$this,$where);
        }

        if(is_int($row))
        $return = $row;

        elseif($row instanceof Row)
        $return = $row->primary();

        elseif($row instanceof Cell)
        $return = $row->rowPrimary();

        return $return;
    }


    // row
    // retourne un objet row
    // si l'objet row n'est pas encore chargé, il le sera
    // si refresh est true, la row sera mis à jour avant d'être retourner
    // si inOut est true, retourne seulement une ligne déjà chargé
    // si inOut est false, retourne seulement une ligne non chargé
    // retourne null si non existant
    public function row($row,bool $refresh=false,?bool $inOut=null,bool $whereTrue=false):?Row
    {
        $return = null;
        $rows = $this->rows();
        $row = $this->rowValue($row,$whereTrue);

        if(is_int($row) && $row > 0)
        {
            $exists = $rows->exists($row);

            if($inOut !== false && $exists === true)
            {
                $return = $rows->get($row);

                if(!empty($return))
                {
                    $return->checkDb();

                    if($refresh === true)
                    $return->refresh();
                }
            }

            elseif($exists === false && $inOut !== true)
            $return = $this->rowMake($row);
        }

        return $return;
    }


    // rowVisible
    // comme row, mais retourne seulement si la row est visible
    // la row est chargé quand même dans rows
    public function rowVisible($row,bool $refresh=false,?bool $inOut=null,bool $whereTrue=false):?Row
    {
        $return = null;
        $row = $this->row($row,$refresh,$inOut,true);

        if(!empty($row) && $row->isVisible())
        $return = $row;

        return $return;
    }


    // rowRefresh
    // retourne une row et rafraîchit la si déjà existante
    public function rowRefresh($row,?bool $inOut=null,bool $whereTrue=false):?Row
    {
        return $this->row($row,true,$inOut,$whereTrue);
    }


    // rowIn
    // retourne une row qui existe déjà
    public function rowIn($row,bool $refresh=false,bool $whereTrue=false):?Row
    {
        return $this->row($row,$refresh,true,$whereTrue);
    }


    // rowInRefresh
    // retourne une row qui existe déjà et rafraîchit la
    public function rowInRefresh($row,bool $whereTrue=false):?Row
    {
        return $this->row($row,true,true,$whereTrue);
    }


    // rowOut
    // retourne une row qui n'existe pas déjà
    public function rowOut($row,bool $whereTrue=false):?Row
    {
        return $this->row($row,true,false,$whereTrue);
    }


    // checkRow
    // retourne l'objet row ou lance une exception si non existant
    // charge la row si non existante
    public function checkRow($row,bool $refresh=false,?bool $inOut=null,bool $whereTrue=false):Row
    {
        $return = $this->row($row,$refresh,$inOut,$whereTrue);

        if(!$return instanceof Row)
        static::throw();

        return $return;
    }


    // select
    // permet de faire une requête select avec output row sur la table
    // utilise true pour obtenir les valeurs par défaut
    public function select(...$values):?Row
    {
        return $this->db()->row($this,...$values);
    }


    // selectPrimary
    // permet de faire une requête select avec output de la clé primaire (id)
    // utilise true pour obtenir les valeurs par défaut
    public function selectPrimary(...$values):?int
    {
        return $this->db()->selectPrimary($this,...$values);
    }


    // selects
    // permet de faire une requête select avec output rows sur la table
    // utilise true pour obtenir les valeurs par défaut
    public function selects(...$values):Rows
    {
        return $this->db()->rows($this,...$values);
    }


    // selectPrimaries
    // permet de faire une requête select avec output tableau avec clés primaires (id)
    // utilise true pour obtenir les valeurs par défaut
    public function selectPrimaries(...$values):?array
    {
        return $this->db()->selectPrimaries($this,...$values);
    }


    // grab
    // permet de faire une requête selects en utilisant table where et order
    // si visible est true, affiche seulement les lignes qui passe la méthode isVisible
    public function grab($where=null,$limit=null,bool $visible=false):Rows
    {
        $return = $this->selects($this->where($where),$this->order(),$limit);

        if($visible === true)
        $return = $return->filter(['isVisible'=>true]);

        return $return;
    }


    // grabVisible
    // permet de faire une requête selects en utilisant table where et order
    // visible est true
    public function grabVisible($where=true,$limit=null):Rows
    {
        $return = null;
        $where = Syntax::addDefault($where);
        $return = $this->grab($where,$limit,true);

        return $return;
    }


    // insert
    // tente l'insertion d'une nouvelle ligne dans la table
    // insère les colonnes qui ont un insert ou qui sont dans le tableau set (après avoir passé dans onSet)
    // les valeurs par défaut des colonnes ne sont pas insérés si option/default est false, donc les valeurs par défaut défini dans col seront ignorés
    // envoie une exception si une des colonnes est toujours requises
    // lance le callback onInserted sur la ligne chargée, une fois l'insertion réussie (seulement si option/row est true)
    // si option/row est true, l'objet ligne sera retourné plutôt que le insertId, c'est le comportement par défaut
    // option preValidate permet de lancer les tests de prévalidation sur les valeurs, faux par défaut, ce test permet de valider les valeurs en provenance de post
    // par défaut l'événement est log, la validation a lieu, mais com est false
    // option reservePrimary permet de connaître l'id de la ligne avant de faire le insert
    public function insert(array $set=[],?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['row'=>true,'reservePrimary'=>true,'default'=>false,'log'=>true,'preValidate'=>false,'validate'=>true,'finalValidate'=>true,'com'=>false,'onCommitted'=>true],$option);
        $cols = $this->cols();
        $primary = $this->primary();

        try
        {
            $result = null;

            if(!empty($set) && !$cols->exists(...array_keys($set)))
            static::throw('columnsNoMatch');

            if($option['reservePrimary'] === true && empty($set[$primary]))
            {
                $reserved = $this->reservePrimary();
                if(is_int($reserved))
                $set[$primary] = $reserved;
            }

            $preValidate = true;
            if($option['preValidate'] === true)
            {
                $set = $cols->preValidatePrepare($set);
                $preValidate = $this->insertPreValidate($cols,$set,$option);
                if(is_array($preValidate))
                {
                    if(empty($preValidate))
                    static::throw('nothingValid');

                    else
                    $cols = $cols->gets(...$preValidate);
                }
            }

            $set = $cols->inserts($set,$option);
            $validate = ($option['validate'] === false || $this->insertValidate($cols,$set,$option))? true:false;

            if($preValidate === true && $validate === true)
            {
                $finalValidate = ($option['finalValidate'] === false || $this->insertFinalValidate($cols,$set,$option))? true:false;

                if($finalValidate === true)
                {
                    $db = $this->db();

                    if($option['log'] === false)
                    $db->off();

                    $result = $db->insert($this,$set);

                    if($option['log'] === false)
                    $db->on();
                }
            }
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        $this->insertAfter($result,$option);

        if(is_int($result))
        {
            $return = $result;

            if($option['row'] === true || $option['onCommitted'] === true)
            {
                $row = null;

                if(is_int($result) && $result > 0)
                {
                    $row = $this->row($return);

                    if($option['onCommitted'] === true && !empty($return) && is_array($set) && !empty($set))
                    $this->insertOnCommitted($row,$set,$option);
                }

                if($option['row'] === true)
                {
                    $return = $row;

                    if(!$return instanceof Row)
                    static::throw('databaseError');

                    else
                    $return->onInserted($option);
                }
            }
        }

        return $return;
    }


    // insertCom
    // méthode utilisé pour générer la communication pour une insertion
    // si le value est associatif, envoie dans com/prepareIn
    public function insertCom($value,string $type=null,?string $label=null,?array $replace=null,?array $attr=null,bool $prepend=false):Main\Com
    {
        $return = $this->db()->com();

        if(!empty($value))
        {
            $label = ($label === null)? $this->label():$label;
            $attr = Base\Attr::append(['insert','data'=>['table'=>$this,'action'=>'insert']],$attr);

            if(is_string($value))
            $value = [[$type,$value,$replace]];

            elseif(is_array($value) && Base\Arr::isAssoc($value))
            {
                foreach ($value as $k => $v)
                {
                    if($this->hasCol($k))
                    {
                        unset($value[$k]);
                        $k = $this->col($k)->label();
                        $value[$k] = $v;
                    }
                }

                $value = $return->prepareIn('neutral',$type,$value,$replace);
            }

            if(is_array($value) && !empty($value))
            {
                $method = ($prepend === true)? 'prepend':'append';
                $return->$method('neutral',$label,$replace,$attr,...$value);
            }
        }

        return $return;
    }


    // insertPreValidate
    // s'occupe de la pré-validation avant l'opération insert
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne true ou le tableau des colonnes qui ont passsés le test prévalidate
    // méthode protégé
    protected function insertPreValidate(Cols $cols,array $set,?array $option=null)
    {
        $return = true;
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
        $preValidate = $cols->preValidate($set,$option['com']);

        if(!empty($preValidate))
        {
            $return = Base\Arr::valuesStrip(array_keys($preValidate),$cols->names());

            if($option['com'] === true)
            $this->insertCom($preValidate,null,null,null,['table']);

            elseif($option['strict'] === true)
            static::throw('invalid',$this,...array_keys($preValidate));
        }

        return $return;
    }


    // insertValidate
    // s'occupe de la validation avant l'opération insert
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne un booléean
    // méthode protégé
    protected function insertValidate(Cols $cols,array $set,?array $option=null):bool
    {
        $return = true;
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
        $completeValidation = $cols->completeValidation($set,$option['com']);

        if(!empty($completeValidation))
        {
            $return = false;

            if($option['com'] === true)
            $this->insertCom($completeValidation,null,null,null,['table']);

            elseif($option['strict'] === true)
            static::throw($this,$completeValidation);
        }

        return $return;
    }


    // insertFinalValidate
    // s'occupe de la validation finale avant l'opération insert
    // utilise la classe row
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne un booléean
    // méthode protégé
    protected function insertFinalValidate(Cols $cols,array $set,?array $option=null):bool
    {
        $return = true;
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
        $rowClass = $this->rowClass();
        $finalValidation = $rowClass::insertFinalValidate($set,$option);

        if(!empty($finalValidation))
        {
            $return = false;

            if($option['com'] === true)
            $this->insertCom($finalValidation,null,null,null,['table']);

            elseif($option['strict'] === true)
            static::throw($this,$finalValidation);
        }

        return $return;
    }


    // insertAfter
    // gère la communication après la requête insert si option com est true
    // si option com est false et qu'il y a une exception attrapable, renvoie
    // méthode protégé
    protected function insertAfter($result,?array $option=null):self
    {
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);

        if($option['com'] === true)
        {
            $label = null;
            $attr = [];
            $in = [];
            $lang = $this->db()->lang();
            $name = $this->name();

            if(is_int($result) && $result > 0)
            {
                $key = ($lang->existsCom('pos',"insert/$name/success"))? $name:'*';
                $in[] = ['pos',"insert/$key/success"];
                $label = $this->db()->lang()->rowLabel($result,$this->name());
                $attr[] = 'row';
                $attr['data']['primary'] = $result;
            }

            else
            {
                $attr[] = 'table';

                if($result instanceof Main\Contract\Catchable)
                {
                    $key = ($lang->existsCom('neg',"insert/$name/exception"))? $name:'*';
                    $in[] = ['neg',"insert/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
                    $result->onCatched(['com'=>false]);
                }

                else
                {
                    $key = ($lang->existsCom('neg',"insert/$name/failure"))? $name:'*';
                    $in[] = ['neg',"insert/$key/failure"];
                }
            }

            $this->insertCom($in,null,$label,null,$attr,true);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($option['strict'] === true && !(is_int($result) && $result > 0))
        static::throw('insertFailed',$result);

        return $this;
    }


    // insertOnCommitted
    // lance le callback onCommitted sur toutes les colonnes
    protected function insertOnCommitted(Row $row,array $set,array $option):self
    {
        $cells = $row->cells(...array_keys($set));

        foreach ($cells as $key => $cell)
        {
            $v = $set[$key];
            $cell->col()->onCommitted($cell,true,$option);
        }

        return $this;
    }


    // label
    // retourne le label de la table
    public function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = $this->db()->lang();
        $path = $this->attr('label');
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,null,$lang,$option);
        else
        $return = $obj->tableLabel($this->name(),$lang,$option);

        return $return;
    }


    // description
    // retourne la description de la table
    public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = $this->db()->lang();
        $path = $this->attr('description');

        if($path !== false)
        {
            $option = Base\Arr::plus($option,['pattern'=>$pattern]);

            if(!empty($path))
            $return = $obj->same($path,$replace,$lang,$option);
            else
            $return = $obj->tableDescription($this->name(),$replace,$lang,$option);
        }

        return $return;
    }


    // allowsRelation
    // retourne vrai si la table supporte des relations
    public function allowsRelation():bool
    {
        return (!empty($this->attr('relation')))? true:false;
    }


    // relation
    // crée l'objet de relation de table ou retourne l'objet si déjà existant
    // ne peut pas retourner null
    public function relation():TableRelation
    {
        $return = $this->relation;

        if(empty($return))
        $return = $this->relation = TableRelation::newOverload($this);

        return $return;
    }


    // segment
    // fait une requête dans la table via une string contenant des segments
    // retourne un tableau avec la clé comme id et la valeur comme string avec segment
    // si get est true, les valeurs de chaque segment sont passés dans le onGet de la colonne
    public function segment(string $key,bool $get=false,...$values):?array
    {
        $return = [];

        if($get === false)
        $return = $this->db()->selectSegments($key,$this,...$values);

        else
        {
            $assoc = $this->db()->selectSegmentAssocsKey($key,$this,...$values);

            if(!empty($assoc))
            {
                $cols = $this->cols();

                foreach ($assoc as $k => $v)
                {
                    if(is_array($v))
                    $return[$k] = Base\Segment::sets(null,$cols->value($v,true),$key);
                }
            }
        }

        return $return;
    }


    // keyValue
    // retourne le contenu de la table en key pair
    // lorsque l'appel se fait dans table, une requete est fait à la base de données
    // utiliser keyValue dans l'objet rows pour retourner seulement les lignes chargés
    // si get est true, les valeurs du tableau sont passés dans le onGet de la colonne
    public function keyValue($key,$value,bool $get=false,...$values):?array
    {
        $return = [];
        $key = $this->col($key);
        $value = $this->col($value);
        $return = $this->db()->selectKeyPairs($key,$value,$this,...$values);

        if($get === true && !empty($return))
        {
            foreach ($return as $k => $v)
            {
                $return[$k] = $value->onGet($v,[]);
            }
        }

        return $return;
    }


    // search
    // permet de chercher pour une valeur dans toutes les colonnes cherchables de la table ou dans les colonnes fournis en troisième argument
    // possible de changer le mode en deuxième argument, par défaut c'est b,i|like
    // retourne un tableau avec les ids et non pas un objet rows
    // envoie une exception si aucune colonne cherchable
    public function search($search,?array $where=null,?array $whereAfter=null,?array $option=null)
    {
        $return = [];
        $option = Base\Arr::plus(['what'=>null,'method'=>null,'cols'=>null,'output'=>'columns','searchSeparator'=>null],$option);
        $what = (!empty($option['what']))? $option['what']:$this->primary();
        $method = (is_string($option['method']))? $option['method']:$this->like();

        if(!is_array($what))
        $what = [$what];

        if(is_scalar($search))
        $search = Base\Str::prepareSearch($search,$option['searchSeparator']);

        if(is_array($search) && $this->isSearchTermValid($search))
        {
            $cols = (!empty($option['cols']))? $option['cols']:$this->cols()->searchable();

            if(is_array($cols))
            $cols = $this->cols(...array_values($cols))->searchable();

            if($cols instanceof Cols && $cols->isNotEmpty())
            {
                $db = $this->db();
                $sql = $db->sql('select',$option['output']);
                $sql->whats(...array_values($what));
                $sql->table($this);
                $sql->whereOrMany($method,$cols,$search);

                if(is_array($where) && !empty($where))
                $sql->wheresOne($where);

                if(!empty($whereAfter))
                $sql->whereAfter(...array_values($whereAfter));

                $return = $sql->trigger();
            }

            else
            static::throw('noColsToSearchIn',$this);
        }

        else
        static::throw('invalidSearchTerm',$this);

        return $return;
    }


    // delete
    // fait une requête delete sur la table
    // les rows effacés sont unlink après le delete
    public function delete(...$values):?int
    {
        $return = null;
        $db = $this->db();
        $primaries = $this->selectPrimaries(...$values);

        if(!empty($primaries))
        {
            $return = $db->delete($this,...$values);
            $this->rowsUnlink(...$primaries);
        }

        return $return;
    }


    // deleteTrim
    // trim la table après une limite
    // les rows effacés sont unlinks
    public function deleteTrim(int $limit):?int
    {
        $return = null;
        $db = $this->db();
        $primaries = $db->getDeleteTrimPrimaries($this,$limit);

        if(!empty($primaries))
        {
            $return = $db->deleteTrim($this,$limit);
            $this->rowsUnlink(...$primaries);
        }

        return $return;
    }


    // truncate
    // truncate la table
    // les rows sont unlink
    // par défaut l'événement est log et com est false
    public function truncate(?array $option=null):bool
    {
        $return = false;
        $option = Base\Arr::plus(['log'=>true,'com'=>false],$option);
        $db = $this->db();
        $result = null;

        try
        {
            if($option['log'] === false)
            $db->off();

            $result = $db->truncate($this);

            if($option['log'] === false)
            $db->on();
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        finally
        {
            $this->truncateAfter($result,$option);
            $this->onTruncated($option);
            $this->rowsUnlink();
            $return = true;
        }

        return $return;
    }


    // truncateAfter
    // gère la communication après la requête truncate si option com est true
    // si option com est false et qu'il y a une exception attrapable, renvoie
    // méthode protégé
    protected function truncateAfter($result,?array $option=null):self
    {
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);

        if($option['com'] === true)
        {
            $attr = ['table','truncate','data'=>['table'=>$this,'action'=>'truncate']];
            $in = [];
            $lang = $this->db()->lang();
            $name = $this->name();

            if($result instanceof Main\Contract\Catchable)
            {
                $key = ($lang->existsCom('neg',"truncate/$name/exception"))? $name:'*';
                $in[] = ['neg',"truncate/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
                $result->onCatched(['com'=>false]);
            }

            elseif($result instanceof \PDOStatement)
            {
                $key = ($lang->existsCom('pos',"truncate/$name/success"))? $name:'*';
                $in[] = ['pos',"truncate/$key/success"];
            }

            else
            {
                $key = ($lang->existsCom('neg',"truncate/$name/system"))? $name:'*';
                $in[] = ['neg',"truncate/$key/system"];
            }

            if(!empty($in))
            $this->db()->com()->neutral($this->label(),null,$attr,...$in);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($option['strict'] === true && !$result instanceof \PDOStatement)
        static::throw('truncateFailed');

        return $this;
    }


    // reservePrimary
    // permet de réserve une clé primarie sur la table
    // la ligne est crée et effacée
    // retourne null si une des lignes n'a pas de valeur par défaut
    // par défaut false est false, donc l'insertion et suppression n'est pas loggé
    public function reservePrimary(?array $option=null):?int
    {
        $return = null;
        $option = Base\Arr::plus(['log'=>false],$option);
        $db = $this->db();
        $cols = $this->cols()->withoutPrimary();
        $hasDefaults = $cols->pair('hasDefault');

        if(!in_array(false,$hasDefaults,true))
        {
            if($option['log'] === false)
            $db->off();

            $return = $db->reservePrimary($this,$option);

            if($option['log'] === false)
            $db->on();
        }

        return $return;
    }


    // alter
    // alter la table
    public function alter():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // alterAutoIncrement
    // change le autoIncrement de la table
    public function alterAutoIncrement(int $value=0):self
    {
        $this->db()->alterAutoIncrement($this,$value);

        return $this;
    }


    // addKey
    // ajoute une clé à la table
    public function addKey():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // addCol
    // ajoute une colonne à la table
    public function addCol():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // drop
    // drop la table
    public function drop():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // dropKey
    // drop une clé de la table
    public function dropKey():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // total
    // retourne un tableau unidimensionnel sur le total des colonnes, lignes et cellules chargés pour la table
    // si count est true, compte le nombre total de ligne et de colonne, pas seulement celle chargé
    // si count et cache sont true, retourne les counts en cache si existant
    public function total(bool $count=false,bool $cache=false):array
    {
        $return = [];
        $row = $this->rowsCount($count,$cache);
        $col = $this->colsCount($count,$cache);

        $cell = ($row * $col);
        $return = ['row'=>$row,'col'=>$col,'cell'=>$cell];

        return $return;
    }


    // info
    // retourne un tableau multidimensionnel qui contient des informations sur les colonnes, lignes et cellules chargés pour la table
    // si l'objet colonne est vide, la table est vide et ne charge pas toutes les colonnes
    // retourne aussi les informations sur le statut de la colonne
    public function info(bool $count=false,bool $cache=false):array
    {
        $return = [];

        if($this->cols->isNotEmpty())
        {
            $row = $this->rows()->primaries();
            $col = $this->cols()->names();

            $return['col'] = $col;

            if(!empty($row))
            $return['row'] = $row;

            $return['total'] = $this->total($count,$cache);
            $return['status'] = $this->status();
        }

        return $return;
    }


    // sql
    // retourne un objet sql pour la table
    // output est rows
    // support pour what, search, where, filter, in, notIn, order, direction, page et limit
    // parfait pour une navigation pour page general
    // note: order id asc est ajouter par défaut, ceci avant de forcer un deuxième sort si la variable order est identique (ceci peut crée un problème dans le calcul de l'index pour navigation specifique)
    public function sql(?array $array=null,?array $option=null):Sql
    {
        $return = $this->db()->sql()->rows($this);
        $primary = $this->primary();

        $array = (array) Base\Obj::cast($array);
        $what = $array['what'] ?? null;
        $search = $array['search'] ?? null;
        $searchSeparator = $array['searchSeparator'] ?? null;
        $where = $array['where'] ?? null;
        $filter = $array['filter'] ?? null;
        $in = $array['in'] ?? null;
        $notIn = $array['notIn'] ?? null;
        $order = $array['order'] ?? null;
        $direction = $array['direction'] ?? null;
        $page = $array['page'] ?? null;
        $limit = $array['limit'] ?? null;

        if(!empty($what))
        {
            if(!is_array($what))
            $what = [$what];

            $return->whats(...array_values($what));
        }

        if(is_string($search) && strlen($search))
        {
            if($this->isSearchTermValid($search))
            {
                $like = $this->like();
                $searchable = $this->cols()->searchable();
                $search = Base\Str::prepareSearch($search,$searchSeparator);

                if(is_string($like) && $searchable->isNotEmpty())
                $return->whereOrMany($like,$searchable,$search);
            }
        }

        if(is_array($where) && !empty($where))
        $return->wheresOne($where);

        if(is_array($filter) && !empty($filter))
        {
            foreach ($filter as $key => $value)
            {
                $col = $this->col($key);

                if($col->canRelation())
                {
                    $findInSet = $col->attr('filterMethod');

                    if(is_string($findInSet))
                    $return->where($key,$findInSet,$value);
                }
            }
        }

        if(is_array($in) && !empty($in))
        $return->where($primary,'in',$in);

        if(is_array($notIn) && !empty($notIn))
        $return->where($primary,'notIn',$notIn);

        if(!empty($order) && !empty($direction))
        $return->order($order,$direction);

        if($order !== $primary)
        $return->order($primary,'asc');

        if(is_int($limit) && $limit > 0)
        {
            if(is_int($page) && $page > 0)
            $return->page($page,$limit);

            else
            $return->limit($limit);
        }

        return $return;
    }


    // hierarchy
    // retourne la hierarchie d'une table à partir d'une colonne
    public function hierarchy($col,bool $exists=true,$where=null,$order=null):array
    {
        $return = [];
        $col = $this->col($col);

        if(!empty($col))
        {
            $db = $this->db();
            $primary = $this->primary();
            $keyPairs = $db->selectKeyPairs($primary,$col,$this,$where,$order);

            if(is_array($keyPairs))
            $return = Base\Arrs::hierarchy($keyPairs,$exists);
        }

        return $return;
    }


    // sourceRewind
    // ramène le pointeur de la source au début
    public function sourceRewind():void
    {
        return;
    }


    // sourceOne
    // retourne une entrée de la source
    // i agit comme référence
    public function sourceOne($offset=true,$length=true,int &$i,?array $option=null)
    {
        $return = null;
        $limit = null;

        if($offset === true)
        $offset = $i;

        elseif(is_int($offset))
        $offset += $i;

        $limit = [$length,$offset];
        $primary = $this->selectPrimary(null,null,$limit);
        if(is_int($primary))
        {
            $row = $this->row($primary);
            if(!empty($row))
            $return = $row->cells()->keyValue();
        }

        return $return;
    }


    // targetInsert
    // fait une insertion sur la table, utilisé à partir de main/importer
    public function targetInsert(array $data,?array $option=null):bool
    {
        $return = false;
        $option = Base\Arr::plus($option,['row'=>false]);
        $db = $this->db();

        $db->off();
        $insert = $this->insert($data,$option);
        $db->on();

        if(is_int($insert))
        $return = true;

        return $return;
    }


    // targetUpdate
    // fait une mise à jour sur la table, utilisé à partir de main/importer
    public function targetUpdate(array $data,int $primary,?array $option=null):bool
    {
        $return = false;
        $row = $this->row($primary);
        $db = $this->db();

        if(!empty($row))
        {
            $db->off();
            $update = $row->setUpdateChangedIncludedValid($data);
            $db->on();
            $row->unlink();

            if(is_int($update))
            $return = true;
        }

        return $return;
    }


    // targetDelete
    // fait une suppresion sur la table, utilisé à partir de main/importer
    public function targetDelete(int $primary,?array $option=null):bool
    {
        $return = false;
        $row = $this->row($primary);
        $db = $this->db();

        if(!empty($row))
        {
            $db->off();
            $delete = $row->deleteOrDeactivate($option);
            $db->on();

            if(is_int($delete))
            $return = true;
        }

        return $return;
    }


    // targetTruncate
    // vide la table, utilisé à partir de main/importer
    public function targetTruncate(?array $option=null):bool
    {
        return $this->truncate($option);
    }


    // isIgnored
    // retourne vrai si la table est ignoré
    public static function isIgnored():bool
    {
        return (!empty(static::$config['ignore']) && static::$config['ignore'] === true)? true:false;
    }


    // initReplaceMode
    // retourne le tableau des clés à ne pas merger recursivement
    public static function initReplaceMode():array
    {
        return static::$replaceMode ?? [];
    }
}

// init
Table::__init();
?>