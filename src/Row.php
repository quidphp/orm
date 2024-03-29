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

// row
// class to represent an existing row within a table
class Row extends Main\ArrObj
{
    // trait
    use _tableAccess;
    use Main\_attrPermission;


    // config
    protected static array $config = []; // les config de row sont mergés à celles de table, avec priorité


    // dynamique
    protected int $primary = 0; // int, clé primaire de la ligne
    protected ?Cells $cells = null; // objet cells


    // construct
    // construit l'objet table
    final public function __construct(int $primary,Table $table)
    {
        $this->setPrimary($primary);
        $this->setLink($table,true);
        $this->cells = $this->cellsNew()->readOnly(true);
    }


    // toString
    // retourne la cellule lié à nom sous forme de string
    final public function __toString():string
    {
        return Base\Str::cast($this->cellName()->value());
    }


    // onInit
    // appeler après le premier cellsLoad de la row
    // par défaut renvoie à onRefreshed
    protected function onInit():void
    {
        $this->onRefreshed();
    }


    // onRefreshed
    // appeler après chaque appel réussi à cellsLoad ou cellsRefresh
    protected function onRefreshed():void
    {
        return;
    }


    // onInserted
    // appelé après une insertion réussi dans core/table insert
    // méthode public qui peut être étendu
    protected function onInserted(array $option)
    {
        return $this->onCommitted($option);
    }


    // onUpdated
    // appelé après une update réussie via une méthode row/update
    protected function onUpdated(array $option)
    {
        return $this->onCommitted($option);
    }


    // onCommitted
    // appelé après une insertion ou update réussie via une méthode row/update
    protected function onCommitted(array $option)
    {
        return $this->onCommittedOrDeleted($option);
    }


    // onDeleted
    // appelé après une suppression réussie via row/delete
    // l'objet n'est pas encore inutilisable
    protected function onDeleted(array $option)
    {
        return $this->onCommittedOrDeleted($option);
    }


    // onCommittedOrDeleted
    // appelé après une insertion, update ou suppression réussie
    protected function onCommittedOrDeleted(array $option)
    {
        return;
    }


    // toArray
    // retourne les cellules de ligne sous un format tableau
    final public function toArray():array
    {
        return $this->cells()->toArray();
    }


    // cast
    // retourne la valeur cast
    final public function _cast():int
    {
        return $this->primary();
    }


    // offsetGet
    // arrayAccess offsetGet retourne une cellule
    // lance une exception si cellule non existante
    final public function offsetGet($key):mixed
    {
        return $this->cell($key);
    }


    // offsetSet
    // arrayAccess offsetGet appele la méthode set de la cellule
    // lance une exception si cellule non existante
    final public function offsetSet($key,$value):void
    {
        $this->cell($key)->set($value);
    }


    // offsetUnset
    // arrayAccess offsetGet appele la méthode unset de la cellule
    // lance une exception si cellule non existante
    final public function offsetUnset($key):void
    {
        $this->cell($key)->unset();
    }


    // arr
    // retourne le tableau de cells
    final protected function arr():array
    {
        return $this->cells()->toArray();
    }


    // isLinked
    // retourne vrai si la ligne est lié à l'objet db
    final public function isLinked():bool
    {
        return $this->hasDb() && $this->table()->isRowLinked($this);
    }


    // alive
    // retourne vrai si la ligne existe dans la table de la base de données
    final public function alive():bool
    {
        return $this->db()->selectCount($this->table(),$this) === 1;
    }


    // hasCell
    // retourne vrai si la celulle existe dans la ligne
    final public function hasCell(...$keys):bool
    {
        return $this->cells()->exists(...$keys);
    }


    // hasChanged
    // retourne vrai si une des cellules de la ligne a changé
    final public function hasChanged():bool
    {
        return $this->cells()->hasChanged();
    }


    // isUpdateable
    // retourne vrai si la row peut être updater
    public function isUpdateable(?array $option=null):bool
    {
        return $this->table()->hasPermission('update');
    }


    // isDeleteable
    // retourne vrai si la row peut être effacer
    // relationChilds est utilisé avec excludeSelf
    public function isDeleteable(?array $option=null):bool
    {
        $return = false;
        $option = Base\Arr::plus(['relationChilds'=>true],$option);

        if($this->table()->hasPermission('delete'))
        $return = (empty($option['relationChilds']) || !$this->hasRelationChilds(null,true));

        return $return;
    }


    // isOldest
    // retourne vrai si la ligne est la plus ancienne (par rapport au id)
    final public function isOldest($where=null):bool
    {
        $table = $this->table();
        $primary = $this->primary();
        $tablePrimary = $table->primary();

        return $table->selectPrimary($where,[$tablePrimary=>'asc']) === $primary;
    }


    // isNewest
    // retourne vrai si la ligne est la plus récente (par rapport au id)
    final public function isNewest($where=null):bool
    {
        $table = $this->table();
        $primary = $this->primary();
        $tablePrimary = $table->primary();

        return $table->selectPrimary($where,[$tablePrimary=>'desc']) === $primary;
    }


    // hasRelationChilds
    // retourne si la row a des enfants de relation
    // excluseSelf permet à une row qui s'est par exemple modifié elle-même de toujours s'effacer
    final public function hasRelationChilds($table=null,bool $excludeSelf=false):bool
    {
        $return = false;
        $childs = $this->relationChilds();

        if(!empty($childs))
        {
            $return = true;

            if($table instanceof Table)
            $table = $table->name();

            if(is_string($table))
            $return = (!empty($childs[$table]));

            if($return === true && $excludeSelf === true)
            {
                $primary = $this->primary();
                $tableName = $this->tableName();

                if(!empty($childs[$tableName]))
                {
                    foreach ($childs[$tableName] as $col => $primaries)
                    {
                        if(in_array($primary,$primaries,true))
                        {
                            $primaries = Base\Arr::valueStrip($primary,$primaries);
                            if(!empty($primaries))
                            $childs[$tableName][$col] = $primaries;
                            else
                            unset($childs[$tableName][$col]);
                        }
                    }

                    if(empty($childs[$tableName]))
                    unset($childs[$tableName]);

                    if(empty($childs))
                    $return = false;
                }
            }
        }

        return $return;
    }


    // sameRow
    // retourne vrai si l'objet et celui fourni ont la même ligne
    final public function sameRow($row):bool
    {
        return $this === $this->table()->row($row);
    }


    // setPrimary
    // change la ligne primaire de la ligne
    final protected function setPrimary(int $primary):void
    {
        if($primary <= 0)
        static::throw();

        $this->primary = $primary;
    }


    // primary
    // retourne la clé primaire de la ligne
    final public function primary():int
    {
        return $this->primary;
    }


    // id
    // retourne la clé primaire de la ligne
    final public function id():int
    {
        return $this->primary;
    }


    // attrRef
    // retourne le tableau des attributs
    // doit retourner une référence
    final protected function &attrRef():array
    {
        return $this->table()->attrRef();
    }


    // attrPermissionRolesObject
    // retourne les rôles courants
    protected function attrPermissionRolesObject():Main\Roles
    {
        return $this->table()->attrPermissionRolesObject();
    }


    // pointer
    // retourne le nom de la table et le primary
    final public function pointer(?string $separator=null):string
    {
        return Base\Str::toPointer($this->tableName(),$this->primary(),$separator);
    }


    // value
    // retourne un tableau avec les valeurs des cellules
    final public function value(...$keys):array
    {
        return $this->cells(...$keys)->keyValue();
    }


    // label
    // retourne le label de la row
    // possible d'inclure le nom et de mettre une longueur maximale au nom
    final public function label($pattern=null,?int $withName=null,?string $lang=null,?array $option=null):?string
    {
        $obj = $this->db()->lang();
        $option = Base\Arr::plus($option,['pattern'=>$pattern,'htmlOutput'=>false]);
        $table = $this->table();
        $name = null;

        if(is_int($withName))
        {
            $method = ($option['htmlOutput'] === true)? 'htmlOutput':'value';
            $name = (string) $this->cellName($lang)->$method();
            $name = Base\Str::excerpt($withName,$name);
        }

        return $obj->rowLabel($this->primary(),$table->name(),$name,$lang,$option);
    }


    // description
    // retourne la description de la row
    final public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $obj = $this->db()->lang();
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);
        $tableName = $this->tableName();
        return $obj->rowDescription($this->primary(),$tableName,$replace,$lang,$option);
    }


    // cellsNew
    // crée l'objet cells
    final protected function cellsNew():Cells
    {
        $class = $this->cellsClass() ?: static::throw('noCellsClass');
        return new $class();
    }


    // cellsLoad
    // crées les cellules de la row
    // les cellules sont crées dans l'ordre de priorité des colonnes
    // envoie une exception si le tableau data ne contient pas toutes les colonnes non ignorés
    final public function cellsLoad(array $data):self
    {
        if($this->cells->isNotEmpty())
        static::throw('cellsNotEmpty');

        $cols = $this->table()->cols();
        $names = $cols->keys();

        if(!Base\Arr::keysExists($names,$data))
        static::throw('invalidInitialData','provideAllColumns');

        $this->cells()->readOnly(false);

        foreach ($names as $key)
        {
            $col = $cols->get($key);
            $class = $this->cellClass($col) ?: static::throw('noClass');
            $this->cellMake($class,$col,$data[$key]);
        }

        $this->cells()->readOnly(true);
        $this->onInit();

        return $this;
    }


    // cellsRefresh
    // rafraîchit les valeurs commit des cellules
    // les cellules doivent déjà existés
    // pas besoin d'avoir toutes les cellules dans le tableau data
    // méthode permissive à cause de la possibilité d'ignorer des colonnes
    final public function cellsRefresh(array $data):self
    {
        $cells = $this->cells();

        if($cells->isEmpty())
        static::throw('cellsEmpty');

        foreach ($data as $key => $value)
        {
            if($cells->exists($key))
            {
                $cell = $cells->get($key);
                $cell->setInitial($value);
            }
        }

        $this->onRefreshed();

        return $this;
    }


    // cells
    // retourne l'objet des cellules
    final public function cells(...$keys):Cells
    {
        return (empty($keys))? $this->cells:$this->cells->gets(...$keys);
    }


    // cellsClass
    // retourne et la classe à utiliser pour les cells
    final public function cellsClass():string
    {
        return $this->table()->classe()->cells();
    }


    // cellClass
    // retourne et la classe à utiliser pour la cell
    final public function cellClass(Col $col):string
    {
        return $this->table()->classe()->cell($col);
    }


    // cellMake
    // construit et store un objet cellule
    final protected function cellMake(string $class,Col $col,$value):void
    {
        $cell = new $class($value,$col,$this);
        $this->cells->add($cell);
    }


    // cell
    // retourne l'objet d'une cellule ou envoie une exception si non existant
    final public function cell($cell):Cell
    {
        return static::typecheck($this->cells()->get($cell),Cell::class,$cell);
    }


    // cellPattern
    // retourne l'objet d'une cellule ou null
    // si un pattern est fourni, passe dans base/col addPattern
    // sinon si la cellule n'existe pas rajoute tous les patterns possibles dans le nom
    // sauf les patterns en lien avec la langue et qui n'est pas la langue courante
    final public function cellPattern(string $cell,?string $pattern=null):?Cell
    {
        $return = null;

        if(is_string($pattern))
        $cell = ColSchema::addPattern($pattern,$cell);

        elseif(!$this->hasCell($cell))
        $cell = ColSchema::possible($cell,true);

        if(!empty($cell) && $this->hasCell($cell))
        $return = $this->cell($cell);

        return $return;
    }


    // cellValue
    // retourne la valeur de l'objet d'une cellule
    final public function cellValue($cell,bool $get=false)
    {
        return $this->cell($cell)->pair(($get === true)? 'get':false);
    }


    // segment
    // permet de remplacer les segments d'une chaîne par le contenu des cellules
    // par défaut utilise value de cellule, si get est true utilise get
    final public function segment(string $value,bool $get=false):string
    {
        return $this->cells()->segment($value,$get);
    }


    // keyValue
    // retourne le contenu de la ligne sous une forme keyValue
    // si get est true, value est passé dans get plutôt que value
    final public function keyValue($key,$value,bool $get=false):array
    {
        $key = $this->cell($key)->value();
        $value = $this->cell($value);
        $value = ($get === true)? $value->get():$value->value();

        return [$key=>$value];
    }


    // relationKeyValue
    // retourne la row sous sa forme relation, tel que décrit dans tableRelation
    final public function relationKeyValue($output=true,bool $onGet=false)
    {
        return $this->table()->relation()->output($this->value(),$output,$onGet);
    }


    // relationChilds
    // retourne toutes les lignes enfants de la ligne
    final public function relationChilds():array
    {
        return $this->cache(__METHOD__,fn() => $this->db()->tables()->relationChilds($this->table(),$this));
    }


    // relationParents
    // retourne toutes les relations parents de la ligne
    // retourne un tableau dont les relations de même table, dans différents champs sont regroupés
    final public function relationParents():array
    {
        $return = [];
        $cells = $this->cells()->filter(fn($cell) => $cell->col()->relation()->isRelationTable());

        foreach ($cells as $cell)
        {
            $relation = $cell->col()->relation();
            $table = $relation->relationTable();
            $tableName = $table->name();
            $rows = $relation->getRow($cell);

            if(!empty($rows))
            {
                if($rows instanceof self)
                $rows = $rows->toRows();

                $array = [];
                foreach ($rows->toArray() as $key => $row)
                {
                    if($row !== $this)
                    $array[$key] = $row;
                }

                if(!empty($array))
                {
                    if(!array_key_exists($tableName,$return))
                    $return[$tableName] = [];

                    $return[$tableName] = Base\Arr::replace($return[$tableName],$array);
                }
            }
        }

        return $return;
    }


    // isActive
    // retourne vrai si la cellule active a la valeur donné en argument, par défaut 1
    // si active est non existante et que value est 1, retourne true
    final public function isActive(?int $value=1):bool
    {
        $return = false;
        $active = $this->cellActive();

        if(!empty($active) && $active->isEqual($value))
        $return = true;

        elseif($active === null && $value === 1)
        $return = true;

        return $return;
    }


    // deactivate
    // désactive la ligne, si la ligne a un champ active
    // envoie une exception si désactivation impossible
    final public function deactivate(?array $option=null):?int
    {
        $active = $this->cellActive();

        if(empty($active))
        static::throw('noActiveCell');

        $active->set(null);
        return $this->updateChanged($option);
    }


    // isVisible
    // retourne vrai si la row est visible
    // cela signifie que la ligne est active si elle a un champ active
    // et si toutes les cellules requises ont une valeur non vide
    // de même la permission view de la table doit être true
    public function isVisible():bool
    {
        return $this->table()->hasPermission('view') && $this->isActive() && $this->cells()->isStillRequiredEmpty();
    }


    // cellActive
    // retourne la cellule active, tel que défini dans la table
    // peut retourner null si non existante
    final public function cellActive():?Cell
    {
        $return = null;
        $active = $this->table()->colActive();

        if(!empty($active))
        $return = $this->cell($active);

        return $return;
    }


    // cellKey
    // retourne la cellule de key, tel que défini dans la table
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    // envoie une exception si non existante
    final public function cellKey(?string $lang=null):Cell
    {
        return $this->cell($this->table()->colKey($lang));
    }


    // cellName
    // retourne la cellule de nom, tel que défini dans la table
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    // envoie une exception si non existante
    final public function cellName(?string $lang=null):Cell
    {
        return $this->cell($this->table()->colName($lang));
    }


    // cellContent
    // retourne la cellule de contenu, tel que défini dans la table
    // possible de spécifier une langue, sinon langue courante ou pas de langue
    // envoie une exception si non existante
    final public function cellContent(?string $lang=null):Cell
    {
        return $this->cell($this->table()->colContent($lang));
    }


    // cellsDateCommit
    // cette méthode retourne tous les commits existants selon l'attribut dateCommit
    // un commit peut avoir une cellule pour date et une autre pour user
    final public function cellsDateCommit():array
    {
        $return = [];

        foreach ($this->table()->colsDateCommit() as $array)
        {
            $r = [];
            ['date'=>$date,'user'=>$user] = $array;
            $key = $date->name();

            $r['date'] = $this->cell($date);
            $r['user'] = null;

            if(!empty($user))
            $r['user'] = $this->cell($user);

            $return[$key] = $r;
        }

        return $return;
    }


    // cellsOwner
    // retourne un objet cells avec toutes les cellules représentant un propriétaire
    final public function cellsOwner():Cells
    {
        return $this->cells($this->table()->colsOwner());
    }


    // newestDateCommit
    // cette méthode retourne le commit le plus récent sur la row
    // retourne null ou un tableau avec date et user
    final public function newestDateCommit():?array
    {
        $return = null;
        $time = 0;

        foreach ($this->cellsDateCommit() as $array)
        {
            $cell = $array['date'];
            $value = $cell->value();

            if(is_int($value) && $value > $time)
            {
                $time = $value;
                $return = $array;
            }
        }

        return $return;
    }


    // oldestDateCommit
    // cette méthode retourne le commit le plus ancien sur la row
    // retourne null ou un tableau avec date et user
    final public function oldestDateCommit():?array
    {
        $return = null;
        $time = null;

        foreach ($this->cellsDateCommit() as $array)
        {
            $cell = $array['date'];
            $value = $cell->value();

            if(is_int($value) && ($time === null || $value < $time))
            {
                $time = $value;
                $return = $array;
            }
        }

        return $return;
    }


    // namePrimary
    // retourne le nom de la row avec la primary entre paranthèse
    final public function namePrimary(?string $pattern=null,?int $excerpt=null):string
    {
        $pattern ??= '%name% (#%primary%)';
        $name = $this->cellName()->value();

        if(is_int($excerpt))
        $name = Base\Str::excerpt($excerpt,$name);

        $replace['%name%'] = $name;
        $replace['%primary%'] = $this->primary();

        return Base\Str::replace($replace,$pattern);
    }


    // slugName
    // retourne le slug du nom de la row
    final public function slugName(?array $option=null):string
    {
        return Base\Slug::str($this->cellName(),$option);
    }


    // toRows
    // retourne la row courante dans un nouvel objet rows
    final public function toRows():Rows
    {
        return $this->table()->rowsNew()->add($this);
    }


    // refresh
    // charge les données de la row à partir de la base de donnée
    // si la ligne n'existe plus, unlink
    final public function refresh():self
    {
        $table = $this->table();
        $assoc = $this->db()->selectAll($table,$this);

        if(is_array($assoc) && !empty($assoc))
        {
            $cells = $this->cells();

            if($cells->isEmpty())
            $this->cellsLoad($assoc);
            else
            $this->cellsRefresh($assoc);
        }

        else
        $this->unlink();

        return $this;
    }


    // duplicate
    // permet de dupliquer la ligne
    final public function duplicate(?array $option=null)
    {
        $table = $this->table();
        $cells = $this->cells()->withoutPrimary()->filter(fn($cell) => $cell->getAttr('duplicate') === true);

        if($cells->isEmpty())
        static::throw('noCellsToDuplicate');

        $keyValue = [];
        $option = (array) $option;

        foreach ($cells as $key => $cell)
        {
            $col = $cell->col();
            $value = $col->callThis(fn() => $this->attrOrMethodCall('onDuplicate',$cell,$option));
            $keyValue[$key] = $value;
        }

        return $table->insert($keyValue,$option);
    }


    // before
    // retourne la ligne précédente à la courante, en utilisant le primary
    final public function before($where=null):?self
    {
        $table = $this->table();
        $primary = $table->primary();
        $whereid = [[$primary,'<',$this->primary()]];
        $where = $table->db()->syntaxCall('whereAppend',$where,$whereid);

        return $table->select($where,[$primary=>'desc'],1);
    }


    // after
    // retourne la ligne suivante à la courante, en utilisant le primary
    final public function after($where=null):?self
    {
        $table = $this->table();
        $primary = $table->primary();
        $whereid = [[$primary,'>',$this->primary()]];
        $where = $table->db()->syntaxCall('whereAppend',$where,$whereid);

        return $table->select($where,[$primary=>'asc'],1);
    }


    // related
    // retourne un ensemble de lignes en lien avec la ligne courante
    final public function related(array $cells,$where=true,$order=true,$limit=null):Rows
    {
        $table = $this->table();
        $primary = $table->primary();
        $cells = $this->cells(...array_values($cells));

        if(!is_array($where))
        $where = (array) $where;

        $where[] = [$primary,'!=',$this];
        foreach ($cells as $cell)
        {
            $where[] = [$cell->name(),'=',$cell->value()];
        }

        return $table->selects($where,$order,$limit);
    }


    // get
    // retourne un tableau avec les valeurs get des cellules
    final public function get(...$keys):array
    {
        return $this->cells(...$keys)->keyValue(true);
    }


    // set
    // permet de change le contenu de plusieurs cellules
    // possible de faire le test de prévalidation
    // option preValidate
    final public function set(array $data,?array $option=null):self
    {
        $option = Base\Arr::plus(['preValidate'=>false],$option);
        $cells = $this->cells();

        if($option['preValidate'] === true)
        {
            $set = $data;
            $data = $cells->preValidatePrepare($data);
            $data = $this->preValidate($data,$option);

            if(count($data) !== count($set))
            $option['partial'] = true;
            $option['preValidate'] = false;
        }

        $cells->sets($data,$option);

        return $this;
    }


    // preValidate
    // fait la prévalidation des données sur un tableau
    // option com et strict
    final public function preValidate(array $return,?array $option=null):array
    {
        $option = Base\Arr::plus(['com'=>false,'strict'=>true],$option);
        $cells = $this->cells();
        $preValidate = $cells->preValidate($return,true);

        if(!empty($preValidate))
        {
            $keys = array_keys($preValidate);

            if($option['com'] === true)
            $this->updateCom($preValidate);

            elseif($option['strict'] === true)
            static::throw($this->table(),...$keys);

            $return = Base\Arr::keysStrip($keys,$return);
        }

        return $return;
    }


    // setUpdateMethod
    // set les valeurs des cells et update
    // la différence est que set est enrobbé du même try catch que update
    // note: les données seront perdus si une exception attrapable est envoyé dans row/set
    // la méthode doit être défini
    // option log, com, preValidate
    final protected function setUpdateMethod(string $method,array $set,?array $option=null):?int
    {
        $return = null;
        $option = Base\Arr::plus(['log'=>true,'com'=>false,'preValidate'=>false],$option);
        Operation\Update::checkType($method);

        try
        {
            $this->set($set,$option);
            $return = $this->$method($option);
        }

        catch (Main\Contract\Catchable $result)
        {
            Operation\Update::newOverload($this,$option)->after($result);
        }

        return $return;
    }


    // setUpdate
    // set les valeurs des cells et update
    final public function setUpdate(array $set,?array $option=null):?int
    {
        return $this->setUpdateMethod('update',$set,$option);
    }


    // setUpdateChanged
    // set les valeurs des cells et setUpdateChangedIncluded
    final public function setUpdateChanged(array $set,?array $option=null):?int
    {
        return $this->setUpdateMethod('updateChanged',$set,$option);
    }


    // setUpdateValid
    // set les valeurs des cells et updateValid
    final public function setUpdateValid(array $set,?array $option=null):?int
    {
        return $this->setUpdateMethod('updateValid',$set,$option);
    }


    // update
    // sauve les cellules de la ligne ayant changés
    // toutes les cellules sont passés dans update avant
    // toutes les cellules sont passés dans updateBefore
    // seuls les cellules ayant changés sont envoyés à la db
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final public function update(?array $option=null):?int
    {
        return Operation\Update::newOverload($this,$option)->trigger('update');
    }


    // updateChanged
    // sauve toutes les cellules de la ligne ayant changé
    // possible de spécifier si on met les include
    // différence: update est seulement appelé si au moins une cellule a changé
    // seuls les cellules ayant changés sont envoyés à update et updateBefore
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final public function updateChanged(?array $option=null):?int
    {
        return Operation\Update::newOverload($this,$option)->trigger('updateChanged');
    }


    // updateValid
    // sauve toutes les cellules valide et ayant changés dans la ligne
    // différence: update est seulement appelé si au moins une cellule a changé
    // seuls les cellules valides sont envoyés à la db
    // un message de communication peut être généré pour indiquer que la sauvegarde est partielle
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final public function updateValid(?array $option=null):?int
    {
        return Operation\Update::newOverload($this,$option)->trigger('updateValid');
    }


    // updateCom
    // méthode utilisé pour générer la communication pour une sauvegarde
    // si le value est associatif, envoie dans com/prepareIn
    final public function updateCom($value,string $type=null,?string $label=null,?array $replace=null,?array $attr=null,bool $prepend=false):Main\Com
    {
        $return = $this->db()->com();

        if(!empty($value))
        {
            $label ??= $this->label();
            $attr = Base\Attr::append(['row','update','data'=>['primary'=>$this,'table'=>$this->table(),'action'=>'update']],$attr);

            if(is_string($value))
            $value = [[$type,$value,$replace]];

            elseif(is_array($value) && Base\Arr::isAssoc($value))
            {
                foreach ($value as $k => $v)
                {
                    if($this->hasCell($k))
                    {
                        unset($value[$k]);
                        $k = $this->cell($k)->label();
                        $value[$k] = $v;
                    }
                }

                $value = $return->prepareIn('neutral',$type,$value,$replace);
            }

            if(is_array($value) && !empty($value))
            {
                $method = ($prepend === true)? 'prepend':'append';
                $return->$method('neutral',$label,null,$attr,...$value);
            }
        }

        return $return;
    }


    // delete
    // efface une ligne de la base de donnée et délie l'objet de table
    // la ligne est ensuite empty et mis dans un état inutilisable
    // toutes les cellules sont passés dans delete, l'envoie d'une exception arrêtera le delete
    final public function delete(?array $option=null):?int
    {
        $option = Base\Arr::plus(['deleteAutoIncrement'=>$this->getAttr('deleteAutoIncrement')],$option);
        return Operation\Delete::newOverload($this,$option)->trigger();
    }


    // deleteOrDeactivate
    // tente d'effacer la ligne, si ce n'est pas possible désactive
    // exception envoyer si la désactivation est impossible
    final public function deleteOrDeactivate(?array $option=null):?int
    {
        $return = null;

        if($this->isDeleteable())
        $return = $this->delete($option);

        else
        $return = $this->deactivate($option);

        return $return;
    }


    // teardown
    // vide un objet ligne
    // l'objet devient inutilisable
    final public function teardown():self
    {
        $this->primary = 0;
        $this->table = null;
        $this->db = null;

        foreach ($this->cells() as $cell)
        {
            $cell->teardown();
        }

        $this->cells = null;

        return $this;
    }


    // unlink
    // termine un objet et délie le de la table
    final public function unlink():self
    {
        $rows = $this->table()->rows();
        $rows->readOnly(false);
        $rows->remove($this);
        $rows->readOnly(true);
        $this->teardown();

        return $this;
    }


    // writeFile
    // écrit la ligne dans l'objet file fourni en argument
    final public function writeFile(Main\File $file,?array $option=null):self
    {
        $cols = $this->table()->cols()->filter(fn($col) => $col->isExportable());
        $cells = $this->cells($cols);

        if($option['header'] === true)
        $cols->writeFile($file,$cells,$option);

        $cells->writeFile($file,$option);

        return $this;
    }


    // insertFinalValidate
    // gère la validation finale sur la row lors d'une insertion
    final public static function insertFinalValidate(array $set,array $option)
    {
        return static::commitFinalValidate($set,null,$option);
    }


    // updateFinalValidate
    // gère la validation finale sur la row lors d'une mise à jour
    final public function updateFinalValidate(Cells $cells,array $option)
    {
        return static::commitFinalValidate($cells->keyValue(),$this,$option);
    }


    // commitFinalValidate
    // gère la validation finale sur la row lors d'une insertion ou mise à jour
    public static function commitFinalValidate(array $set,?self $row,array $option)
    {
        return;
    }


    // initReplaceMode
    // retourne le tableau des clés à ne pas merger recursivement
    final public static function initReplaceMode():array
    {
        return Table::initReplaceMode();
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Row':null;
    }
}
?>