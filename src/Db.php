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

// db
// class used to query the database and to link the results to the different ORM components
class Db extends Pdo implements \ArrayAccess, \Countable, \Iterator
{
    // trait
    use Main\_arrObj;


    // config
    protected static array $config = [
        'permission'=>true, // la permission est vérifié avant la requête
        'autoSave'=>false, // active ou désactive le autoSave au closeDown
        'log'=>true, // si les requêtes sont log
        'revert'=>null, // permet de conserver une clé à revert après une requête
        'logMethod'=>'logCloseDownCliNow', // méthode à utiliser pour log
        'logClass'=>[ // classe à utiliser pour logger ces différents types de requêtes
            'select'=>null,
            'show'=>null,
            'insert'=>null,
            'update'=>null,
            'delete'=>null,
            'create'=>null,
            'alter'=>null,
            'truncate'=>null,
            'drop'=>null],
        'classe'=>[ // option pour l'objet classe
            'default'=>[], // classe par défaut
            'colKind'=>[], // classe pour colonne selon le kind
            'colAttr'=>[]], // classe pour colonne selon un attribut
        'classeClosure'=>null, // possible de mettre uen closure comme classe (permet de gérer la cache dans boot)
        'schemaClosure'=>null, // possible de mettre une closure comme schema (permet de gérer la cache dans boot)
        'tables'=>[], // paramètre par défaut pour les tables
        'cols'=>[], // paramètre par défaut pour les colonnes
        'priorityIncrement'=>10, // incrémentation de la priorité lors de la création des tables et des colonnes
        'output'=>[
            'all'=>[ // configuration des output spécifique à db
                'row'=>['onlySelect'=>true,'selectLimit'=>1],
                'rowRefresh'=>['onlySelect'=>true,'selectLimit'=>1],
                'rowIn'=>['onlySelect'=>true,'selectLimit'=>1],
                'rowInRefresh'=>['onlySelect'=>true,'selectLimit'=>1],
                'rowOut'=>['onlySelect'=>true,'selectLimit'=>1],
                'rows'=>['onlySelect'=>true],
                'rowsRefresh'=>['onlySelect'=>true],
                'rowsIn'=>['onlySelect'=>true],
                'rowsInRefresh'=>['onlySelect'=>true],
                'rowsOut'=>['onlySelect'=>true]],
            'row'=>['row','rowRefresh','rowIn','rowInRefresh','rowOut','rows','rowsRefresh','rowsIn','rowsInRefresh','rowsOut']] // liste des méthodes en lien avec row/rows
    ];


    // dynamique
    protected ?Classe $classe = null; // propriété qui contient l'objet classe
    protected ?Schema $schema = null; // propriété qui contient l'objet schema
    protected ?Tables $tables = null; // propriété qui contient l'objet tables
    protected ?Main\Lang $lang = null; // propriété qui contient l'objet lang
    protected ?Main\Com $com = null; // propriété qui contient l'objet com
    protected ?Main\Roles $roles = null; // propriété qui contient l'objet roles
    protected ?string $exception = null; // propriété qui conserve la classe d'exception à utiliser
    protected array $permission = [ // permissions racine de la base de donnée, les permissions des tables peuvent seulement mettre false des valeurs true, pas l'inverse
        'select'=>true,
        'show'=>true,
        'insert'=>true,
        'update'=>true,
        'delete'=>true,
        'create'=>true,
        'alter'=>true,
        'truncate'=>true,
        'drop'=>true];


    // construct
    // construction de la classe
    final public function __construct(string $dsn,string $password,Main\Extenders $extenders,Main\Roles $roles,?array $attr=null)
    {
        $this->makeAttr($attr);
        $this->setDsn($dsn);
        $this->setSyntax();
        $this->setRoles($roles);
        $this->connect($password,$extenders);
    }


    // onBeforeMakeStatement
    // callback avant la création du statement dans makeStatement
    final protected function onBeforeMakeStatement(array $value):void
    {
        if($this->getAttr('permission') === true && !empty($value['type']))
        {
            if(!empty($value['table']) && !empty($this->tables))
            {
                if(!$this->hasPermission($value['type'],$value['table']))
                static::throw($value['type'],$value['table'],'notAllowed');
            }

            elseif(!$this->hasPermission($value['type']))
            static::throw($value['type'],'notAllowed');
        }
    }


    // onAfterMakeStatement
    // callback après la création du statement dans makeStatement
    final protected function onAfterMakeStatement(array $value,\PdoStatement $statement):void
    {
        if(!empty($value['type']))
        {
            parent::onAfterMakeStatement($value,$statement);

            if($this->getAttr('log') === true)
            {
                $log = $this->getAttr('logClass/'.$value['type']);
                $logMethod = $this->getAttr('logMethod',true);

                if(!empty($log) && !empty($logMethod))
                {
                    $go = false;

                    if(!empty($value['table']))
                    {
                        $table = $this->table($value['table']);
                        if($table->shouldLogSql($value['type']))
                        $go = true;
                    }

                    else
                    $go = true;

                    if($go === true)
                    $log::$logMethod($value['type'],$value);
                }
            }
        }
    }


    // onCloseDown
    // méthode appelé à la fermeture
    // permet de sauvegarder toutes les lignes avec des changements non sauvegardés
    // méthode publique, car envoyé dans base/response
    final protected function onCloseDown():self
    {
        if($this->getAttr('autoSave') === true)
        $this->autoSave();

        return $this;
    }


    // connect
    // connect à une base de donnée
    final public function connect(string $password,...$args):self
    {
        parent::connect($password);
        $this->setInst();
        $this->makeSchema();
        $this->makeTables(...$args);

        return $this;
    }


    // disconnect
    // deconnect d'une base de donnée
    final public function disconnect():self
    {
        parent::disconnect();
        $this->unsetInst();
        $this->classe = null;
        $this->schema = null;
        $this->tables = null;
        $this->lang = null;
        $this->roles = null;

        return $this;
    }


    // arr
    // retourne le tableau pour le trait ArrObj
    // ce n'est pas une référence, offset set et unset sont désactivés
    final protected function arr():array
    {
        return $this->tables()->toArray();
    }


    // offsetGet
    // arrayAccess offsetGet retourne une table
    // lance une exception si table non existante
    #[\ReturnTypeWillChange]
    final public function offsetGet($key)
    {
        return $this->table($key);
    }


    // offsetSet
    // arrayAccess offsetSet n'est pas permis pour la classe
    final public function offsetSet($key,$value):void
    {
        static::throw('arrayAccess','notAllowed');
    }


    // offsetUnset
    // arrayAccess offsetUnset n'est pas permis pour la classe
    final public function offsetUnset($key):void
    {
        static::throw('arrayAccess','notAllowed');
    }


    // off
    // met log et rollback false
    // conserve la valeur de log et rollback dans l'option revert
    final public function off():self
    {
        $log = $this->getAttr('log');
        $rollback = $this->getAttr('rollback');
        $this->setAttr('log',false);
        $this->setAttr('rollback',false);
        $this->setAttr('revert',['log'=>$log,'rollback'=>$rollback]);

        return $this;
    }


    // on
    // remet log et rollback à la dernière valeur conservé dans option/revert
    final public function on():self
    {
        $revert = $this->getAttr('revert');
        if(is_array($revert) && array_key_exists('log',$revert) && array_key_exists('rollback',$revert))
        {
            $this->setAttr('log',$revert['log']);
            $this->setAttr('rollback',$revert['rollback']);
        }
        $this->setAttr('revert',null);

        return $this;
    }


    // hasPermission
    // retourne vrai si la db a la permission pour le type de requête
    // si vrai, peut chercher dans l'objet table pour une permission supplémentaire, envoie une exception si la table n'existe pas
    final public function hasPermission(string $type,$table=null):bool
    {
        $return = true;

        if(array_key_exists($type,$this->permission) && $this->permission[$type] === false)
        $return = false;

        if($return === true && $table !== null)
        {
            $table = $this->table($table);
            $roles = $this->roles();
            $return = $table->rolesHasPermission($type,$roles);
        }

        return $return;
    }


    // checkPermission
    // envoie une exception si la permission est fausse
    final public function checkPermission(string $type,$table=null):self
    {
        if($this->hasPermission($type,$table) !== true)
        static::throw();

        return $this;
    }


    // permission
    // retourne le tableau des permissions racines de la base de données
    final public function permission():array
    {
        return $this->permission;
    }


    // setPermission
    // change la valeur de option permission, si value est null, toggle
    final public function setPermission(?bool $value=null):self
    {
        if($value === null)
        $value = ($this->getAttr('permission') === true)? false:true;

        return $this->setAttr('permission',$value);
    }


    // setLog
    // change la valeur de option log, si value est null, toggle
    final public function setLog(?bool $value=null):self
    {
        if($value === null)
        $value = ($this->getAttr('log') === true)? false:true;

        return $this->setAttr('log',$value);
    }


    // statementException
    // lance une exception de db attrapable en cas d'erreur sur le statement
    final public function statementException(?array $option,\Exception $exception,...$values):void
    {
        $class = $this->getExceptionClass();
        $message = $exception->getMessage();
        $exception = new $class($message,null,$option);

        if(!empty($values[0]) && is_array($values[0]) && !empty($values[0]['sql']))
        $exception->setQuery($this->syntaxCall('emulate',$values[0]['sql'],$values[0]['prepare'] ?? null));

        throw $exception;
    }


    // getExceptionClass
    // retourne la classe d'exception courante à utiliser pour l'objet
    final public function getExceptionClass():string
    {
        return $this->exception ?? Exception::class;
    }


    // setExceptionClass
    // change la classe courante pour exception
    final public function setExceptionClass($value):void
    {
        if(is_bool($value))
        $value = ($value === true)? CatchableException::class:Exception::class;

        if(!is_string($value) || !is_subclass_of($value,\Exception::class,true))
        static::throw();

        $this->exception = $value;
    }


    // makeTables
    // créer les objets dbClasse et tables
    // enregistre la méthode onCloseDown
    final protected function makeTables(Main\Extenders $extenders):void
    {
        if(!empty($this->tables))
        static::throw('alreadyExists');

        $this->tables = Tables::newOverload();
        $this->makeClasse($extenders);
        $this->tablesLoad();
        $this->tables()->sortDefault()->readOnly(true);
        Base\Response::onCloseDown(fn() => $this->onCloseDown());
    }


    // tablesLoad
    // charge toutes les tables
    // il n'est pas possible de rafraîchir si l'objet contient déjà des tables, mais n'envoie pas d'erreur
    // une table peut être ignoré via la classe de la table
    final protected function tablesLoad():void
    {
        if($this->tables()->isEmpty())
        {
            $showTables = $this->schema()->tables() ?: static::throw('noTables');
            $classe = $this->classe();
            $showTables = Base\Arr::camelCaseParent($showTables);
            $priority = 0;
            $increment = $this->getPriorityIncrement();

            foreach ($showTables as $value => $parent)
            {
                $tableClasse = $classe->tableClasse($value);
                $class = $tableClasse->table() ?: static::throw('classEmpty');

                if(!$class::isIgnored())
                {
                    $priority += $increment;
                    $attr = ['priority'=>$priority];
                    if(!empty($parent))
                    $attr['parent'] = $parent;

                    $this->tableMake($class,$value,$tableClasse,$attr);
                }
            }
        }
    }


    // tablesColsLoad
    // charge toutes les tables et les colonnes
    final public function tablesColsLoad():self
    {
        if($this->tables()->isEmpty())
        $this->tablesLoad();

        $tables = $this->tables();
        foreach ($tables as $table)
        {
            if($table->isColsEmpty())
            $table->colsLoad();
        }

        return $this;
    }


    // tableMake
    // crée un objet table et ajoute le à tables
    final protected function tableMake(string $class,string $value,TableClasse $tableClasse,array $attr):void
    {
        $value = new $class($value,$this,$tableClasse,$attr);

        if($value->getAttr('ignore') !== true)
        $this->tables()->add($value);
    }


    // tables
    // retourne l'objet tables
    final public function tables():Tables
    {
        return $this->tables;
    }


    // makeClasse
    // génère l'objet classe de db
    final protected function makeClasse(Main\Extenders $extenders):void
    {
        $closure = $this->getAttr('classeClosure');

        if(!empty($closure))
        $this->classe = $this->callThis($closure,$extenders);

        else
        $this->classe = Classe::newOverload($extenders,$this->getAttr('classe'));
    }


    // classe
    // retourne l'objet classe
    final public function classe():Classe
    {
        return $this->classe;
    }


    // makeSchema
    // créer l'objet schema
    final protected function makeSchema():void
    {
        $closure = $this->getAttr('schemaClosure');

        if(!empty($closure))
        {
            $content = $closure($this);

            if(is_array($content) && !empty($content))
            $this->schema = Schema::newOverload($content,$this);
        }

        if(empty($this->schema))
        $this->schema = Schema::newOverload(null,$this);
    }


    // schema
    // retourne l'objet schema
    final public function schema():Schema
    {
        if(empty($this->schema))
        $this->makeSchema();

        return $this->schema;
    }


    // setLang
    // lit ou enlève un objet lang à db
    final public function setLang(?Main\Lang $value):self
    {
        $this->lang = $value;

        return $this;
    }


    // hasLang
    // retourne vrai si l'objet db a un objet lang lié
    final public function hasLang():bool
    {
        return $this->lang instanceof Main\Lang;
    }


    // lang
    // retourne l'objet lang ou envoie une exception si non existant
    final public function lang():Main\Lang
    {
        return static::typecheck($this->lang,Main\Lang::class);
    }


    // label
    // retourne le label d'une base de donnée
    final public function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        return $this->lang()->dbLabel($this->dbName(),$lang,Base\Arr::plus($option,['pattern'=>$pattern]));
    }


    // description
    // retourne la description d'une base de donnée
    final public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        return $this->lang()->dbDescription($this->dbName(),$replace,$lang,Base\Arr::plus($option,['pattern'=>$pattern]));
    }


    // getSqlOption
    // retourne les options pour la classe base sql
    final public function getSqlOption(?array $option=null):array
    {
        return Base\Arr::plus(parent::getSqlOption($option),['defaultCallable'=>[$this,'getTableDefault']]);
    }


    // setRoles
    // lit un objet rôles à db
    final public function setRoles(Main\Roles $value):self
    {
        $this->roles = $value;

        return $this;
    }


    // role
    // retourne l'objet roles ou envoie une exception si non existant
    final public function roles():Main\Roles
    {
        return static::typecheck($this->roles,Main\Roles::class);
    }


    // role
    // retourne l'objet role, soit le role principal
    final public function role():Main\Role
    {
        return $this->roles()->main();
    }


    // setCom
    // lit ou enlève un objet com à db
    final public function setCom(?Main\Com $value):self
    {
        $this->com = $value;

        return $this;
    }


    // hasCom
    // retourne vrai si l'objet db a un objet com lié
    final public function hasCom():bool
    {
        return $this->com instanceof Main\Com;
    }


    // com
    // retourne l'objet com ou envoie une exception si non existant
    final public function com():Main\Com
    {
        return static::typecheck($this->com,Main\Com::class);
    }


    // getTableDefault
    // retourne les défauts pour la classe base sql en fonction de la table
    final public function getTableDefault(string $table):?array
    {
        return (!empty($this->tables))? $this->table($table)->default():null;
    }


    // hasTable
    // retourne vrai si la ou les tables sont dans l'objet tables
    final public function hasTable(...$values):bool
    {
        return (!empty($this->tables))? $this->tables()->exists(...$values):false;
    }


    // table
    // retourne un objet table ou envoie une exception si inexistant
    final public function table($table):Table
    {
        return static::typecheck($this->tables()->get($table),Table::class,$table,'doesNotExist');
    }


    // query
    // méthode query pour la classe db
    // gère les requêtes avec output row et rows
    // pour les autres requêtes, renvoie à la classe core/pdo
    final public function query($value,$output=true)
    {
        $return = null;
        $rows = [];

        if(is_string($output) && $this->isRowOutput($output))
        {
            if(!is_array($value))
            static::throw($output,'queryInvalid');

            if(empty($value['type']) || !$this->isOutput($value['type'],$output))
            static::throw($output,'invalidForType');

            if(empty($value['table']))
            static::throw($output,'requiresTable');

            $type = $this->getRowOutputType($output) ?: static::throw('emptyType');
            $value = $this->prepareRow($value,$type);

            if(!array_key_exists('id',$value))
            static::throw($output,'requiresId');

            $table = $this->table($value['table']);

            if($type === 'row')
            $return = $table->$output($value['id']);

            elseif($type === 'rows')
            {
                if(empty($value['id']))
                $return = $table->rowsNew();

                else
                {
                    $ids = array_values((array) $value['id']);
                    $return = $table->$output(...$ids);
                }
            }
        }

        else
        $return = parent::query($value,$output);

        return $return;
    }


    // fromPointer
    // retourne la row ou null à partir d'un pointer
    // possible de fournir un tableau de tables valides en troisième argument
    final public function fromPointer(string $value,?array $validTables=null,?string $separator=null):?Row
    {
        $return = null;
        $pointer = Base\Str::pointer($value,$separator);

        if(!empty($pointer))
        {
            if(empty($validTables) || in_array($pointer[0],$validTables,true))
            {
                if($this->hasTable($pointer[0]))
                $return = $this->table($pointer[0])->row($pointer[1]);
            }
        }

        return $return;
    }


    // prepareRow
    // prépare le tableau de requête pour toutes les méthodes row et rows
    // va chercher le ou les ids si nécessaires
    // envoie une exception si le type est incorrect
    final public function prepareRow(array $return,string $type):array
    {
        if(!in_array($type,['row','rows'],true))
        static::throw();

        if(empty($return['id']) || empty($return['whereOnlyId']))
        {
            $output = ($type === 'rows')? 'columns':'column';
            $return['id'] = $this->query($return,$output);
        }

        return $return;
    }


    // row
    // retourne un objet row ou null après avoir traité un tableau pour une requête sql
    final public function row(...$values):?Row
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'row');
    }


    // rowRefresh
    // retourne un objet row ou null après avoir traité un tableau pour une requête sql
    // s'il y a une row, elle ira chercher les dernières valeurs dans la base de donnée
    final public function rowRefresh(...$values):?Row
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowRefresh');
    }


    // rowIn
    // retourne un objet row ou null après avoir traité un tableau pour une requête sql
    // retourne seulement la row si elle a déjà été chargé
    final public function rowIn(...$values):?Row
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowIn');
    }


    // rowInRefresh
    // retourne un objet row ou null après avoir traité un tableau pour une requête sql
    // retourne seulement la row si elle a déjà été chargé, la ligne se mettra à jour avant d'être retourner
    final public function rowInRefresh(...$values):?Row
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowInRefresh');
    }


    // rowOut
    // retourne un objet row ou null après avoir traité un tableau pour une requête sql
    // retourne seulement la row si elle n'est pas chargé
    final public function rowOut(...$values):?Row
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowOut');
    }


    // rows
    // retourne un objet rows ou null après avoir traité un tableau pour une requête sql
    final public function rows(...$values):?Rows
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rows');
    }


    // rowsRefresh
    // retourne un objet rows ou null après avoir traité un tableau pour une requête sql
    // s'il y a des rows, les lignes se mettront à jour avant d'être retourner
    final public function rowsRefresh(...$values):?Rows
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowsRefresh');
    }


    // rowsIn
    // retourne un objet rows ou null après avoir traité un tableau pour une requête sql
    // les rows sont seulement retournés si elles existent déjà
    final public function rowsIn(...$values):?Rows
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowsIn');
    }


    // rowsInRefresh
    // retourne un objet rows ou null après avoir traité un tableau pour une requête sql
    // les rows sont seulement retournés si elles existent déjà et se mettront à jour avant d'être retourner
    final public function rowsInRefresh(...$values):?Rows
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowsInRefresh');
    }


    // rowsOut
    // retourne un objet rows ou null après avoir traité un tableau pour une requête sql
    // les rows sont seulement retournés si elles n'existent pas déjà
    final public function rowsOut(...$values):?Rows
    {
        return $this->query($this->syntaxCall('makeSelect',Base\Arr::unshift($values,[$this->primary()]),$this->getSqlOption()),'rowsOut');
    }


    // sql
    // retourne un objet sql lié à la base de données
    final public function sql(?string $type=null,$output=true):PdoSql
    {
        return Sql::newOverload($this,$type,$output);
    }


    // reservePrimaryDelete
    // méthode protégé utilisé par reservePrimary pour effacer la ligne venant d'être ajouté
    // permission est mise à off et ensuite true, pas besoin d'avoir la permission delete pour effacer la ligne vide dans cette situation
    final protected function reservePrimaryDelete(string $value,int $primary,array $option):?int
    {
        $return = null;
        $this->setPermission(false);
        $return = parent::reservePrimaryDelete($value,$primary,$option);
        $this->setPermission(true);

        return $return;
    }


    // setAutoSave
    // change la valeur de option autoSave, si value est null, toggle
    final public function setAutoSave(?bool $value=null):self
    {
        if($value === null)
        $value = ($this->getAttr('autoSave') === true)? false:true;

        return $this->setAttr('autoSave',$value);
    }


    // autoSave
    // sauve toutes les lignes ayant changés dans la base de donnée
    final public function autoSave():array
    {
        $return = [];

        $changed = $this->tables()->changed();

        if($changed->isNotEmpty())
        $return = $changed->updateChanged();

        return $return;
    }


    // tableAttr
    // retourne un tableau des attributs de la table présent dans config de la db
    // peut retourner null, utiliser par table/setAttr
    // à plus de priorité que les attributs de db mais moins que ceux de row
    final public function tableAttr($table):?array
    {
        $return = null;

        if($table instanceof Table)
        $table = $table->name();

        if(is_string($table))
        $return = $this->getAttr(['tables',$table]);

        return $return;
    }


    // colAttr
    // retourne un tableau des attributs de la colonne présent dans config de la db
    // peut retourner null, utiliser par dbClasse, a moins de priorité que table/colAttr
    final public function colAttr(string $col):?array
    {
        $return = $this->getAttr(['cols',$col]);

        if(is_string($return))
        static::throw($col,'stringNotAllowed',$return);

        return $return;
    }


    // info
    // retourne un tableau d'information sur la connexion db
    // inclut le overview de tables si tables est true
    final public function info():array
    {
        $return = parent::info();
        $return['tablesInfo'] = $this->tables()->info();

        return $return;
    }


    // getPriorityIncrement
    // retourne l'incrémentation de priorité souhaité
    final public function getPriorityIncrement():int
    {
        return $this->getAttr('priorityIncrement');
    }


    // isRowOutput
    // retourne vrai si le type de output est row/rows
    final public function isRowOutput($value):bool
    {
        return is_string($value) && in_array($value,$this->getAttr(['output','row']),true);
    }


    // getRowOutputType
    // retourne le type pour row output (row ou rows)
    final public function getRowOutputType(string $value):?string
    {
        return ($this->isRowOutput($value) && strpos($value,'rows') === 0)? 'rows':'row';
    }
}

// init
Db::__init();
?>