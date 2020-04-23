<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// cells
// class for a collection of many cells within a same row
class Cells extends Main\MapObj
{
    // trait
    use Main\Map\_readOnly;
    use Main\Map\_sort;


    // config
    public static $config = [];


    // dynamique
    protected $mapAllow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
    protected $mapIs = Cell::class; // classe d'objet permis
    protected $mapSortDefault = 'priority'; // défini la méthode pour sort par défaut


    // construct
    // construit un nouvel objet cells
    final public function __construct(...$values)
    {
        $this->add(...$values);

        return;
    }


    // toString
    // retourne les noms de cellules séparés par des virgules
    final public function __toString():string
    {
        return implode(',',$this->names());
    }


    // onPrepareKey
    // prepare une clé pour les méthodes qui soumette une clé
    // peut fournir un index, un tableau qui retournera la première existante, une string, une colonne ou une cellule
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

        elseif($key instanceof Col || $key instanceof Cell)
        $return = $key->name();

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
    // prépare le retour pour indexes, gets, slice et slice index
    // les lignes sont toujours retournés dans un nouvel objet cells
    final protected function onPrepareReturns(array $array):self
    {
        $return = new static();

        foreach ($array as $value)
        {
            if(!empty($value))
            $return->add($value);
        }

        return $return;
    }


    // cast
    // retourne la valeur cast
    final public function _cast():array
    {
        return $this->names();
    }


    // offsetSet
    // arrayAccess offsetSet si la clé est null [] ou si la clé est un nom de cellule
    final public function offsetSet($key,$value):void
    {
        if($key === null)
        $this->add($value);

        else
        $this->set($key,$value);

        return;
    }


    // isWhere
    // retourne vrai si les cellules correspondent à la vérification where du tableau en argument
    // similaire à une syntaxe sql mais ne supporte pas les méthodes base/sql whereThree, ni les and, or et paranthèses
    final public function isWhere(array $array):bool
    {
        $return = false;
        $array = Base\Obj::cast($array);
        $db = $this->db();

        if(!empty($db))
        {
            foreach ($array as $key => $value)
            {
                foreach ($db->syntaxCall('wherePrepareOne',$key,$value) as $v)
                {
                    if(is_array($v) && count($v) >= 2 && is_string($v[0]))
                    {
                        $cell = $this->checkGet($v[0]);
                        $arr = (is_string($v[1]))? [$v[1]=>$v[2] ?? null]:[0=>$v[1]];
                        $return = $cell->isWhere($arr);

                        if($return === false)
                        break 2;
                    }

                    else
                    static::throw('unsupported');
                }
            }
        }

        return $return;
    }


    // names
    // retourne les noms de cellules contenus dans l'objet
    final public function names():array
    {
        return $this->keys();
    }


    // namesWithoutPrimary
    // retourne les noms de cellules contenus dans l'objet sans la cellule primaire
    final public function namesWithoutPrimary():array
    {
        $return = [];

        foreach ($this->arr() as $key => $value)
        {
            if(!$value->isPrimary())
            $return[] = $key;
        }

        return $return;
    }


    // db
    // retourne la db du premier objet
    final public function db():?Db
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->db();

        return $return;
    }


    // table
    // retourne la table du premier objet
    final public function table():?Table
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->table();

        return $return;
    }


    // row
    // retourne la row du premier objet
    final public function row():?Row
    {
        $return = null;
        $first = $this->first();
        if(!empty($first))
        $return = $first->row();

        return $return;
    }


    // add
    // ajoute une ou plusieurs cellules dans l'objet
    // accepte un objet cells
    // valeurs doivent être des objets cell
    // deux objets identiques ne peuvent pas être ajoutés dans cells
    // des objets de différentes tables ou différentes lignes ne peuvent être ajoutés dans cells
    final public function add(...$values):self
    {
        $this->checkAllowed('add');
        $values = $this->prepareValues(...$values);
        $firstTable = $this->table();
        $firstRow = $this->row();
        $data =& $this->arr();

        foreach ($values as $value)
        {
            if(!$value instanceof Cell)
            static::throw('requiresCell');

            $table = $value->table();
            $row = $value->row();

            $firstTable = (empty($firstTable))? $table:$firstTable;
            $firstRow = (empty($firstRow))? $row:$firstRow;

            if($table !== $firstTable)
            static::throw('cellMustBeFromSameTable');

            if($row !== $firstRow)
            static::throw('cellMustBeFromSameRow');

            $name = $value->name();

            if(!array_key_exists($name,$data))
            $data[$name] = $value;

            else
            static::throw('cellAlreadyIn',$name);
        }

        return $this->checkAfter();
    }


    // withoutPrimary
    // retourne un objet avec les cellules sans la clé primaire
    final public function withoutPrimary():self
    {
        return $this->gets(...$this->namesWithoutPrimary());
    }


    // isVisible
    // retourne vrai si tous les champs sont visibles
    final public function isVisible(?Main\Session $session=null):bool
    {
        $return = false;
        $args = [null,$session];
        $hidden = $this->pair('isVisible',...$args);

        if(!in_array(false,$hidden,true))
        $return = true;

        return $return;
    }


    // isHidden
    // retourne vrai si tous les champs sont cachés
    final public function isHidden(?Main\Session $session=null):bool
    {
        $return = false;
        $args = [null,$session];
        $hidden = $this->pair('isVisible',...$args);

        if(!in_array(true,$hidden,true))
        $return = true;

        return $return;
    }


    // isRequired
    // retourne un objet cells avec toutes les cellules requises
    // ne retourne pas la clé primaire
    final public function isRequired(bool $value=true):self
    {
        return $this->filter(['isRequired'=>$value]);
    }


    // isStillRequired
    // retourne un objet cells avec toutes les cellules toujours requises
    // ne retourne pas la clé primaire
    final public function isStillRequired():self
    {
        $return = new static();

        foreach ($this->isRequired() as $key => $cell)
        {
            if(!$cell->isPrimary() && $cell->isStillRequired())
            $return->add($cell);
        }

        return $return;
    }


    // isStillRequiredEmpty
    // retourne vrai si l'objet isStillRequired est vide
    // ceci signifie que toutes les cellules requises ont une valeur
    final public function isStillRequiredEmpty():bool
    {
        return $this->isStillRequired()->isEmpty();
    }


    // rules
    // retourne toutes les règles de validations et required des cellules
    // n'a pas de lien avec les valeurs courantes des cellules
    // possible de retourner les textes si lang est true
    final public function rules(bool $lang=false,bool $preValidate=false):array
    {
        return $this->pair('rules',$lang,$preValidate);
    }


    // preValidatePrepare
    // prépare un tableau de valeur en vue d'une prévalidation
    final public function preValidatePrepare(array $return):array
    {
        foreach ($return as $key => $value)
        {
            $cell = $this->checkGet($key);
            $return[$key] = $cell->col()->preValidatePrepare($value);
        }

        return $return;
    }


    // preValidate
    // permet de pré-valider un tableau de valeur avant de set dans la cellule
    final public function preValidate(array $set=[],bool $lang=false,bool $filter=true):array
    {
        $return = [];

        foreach ($this->arr() as $key => $cell)
        {
            $v = true;

            if(array_key_exists($key,$set))
            {
                $v = $set[$key];
                $v = $cell->col()->preValidate($v,$lang);
            }

            if($filter === false || $v !== true)
            $return[$key] = $v;
        }

        return $return;
    }


    // validate
    // retourne un tableau avec les résultats des validations sur toutes les cellules de l'objet
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les cellules qui passent le test ne sont pas retournés
    // par défaut cache est true, donc retourne la cache de la validation de la cellule
    final public function validate(bool $lang=false,bool $filter=true):array
    {
        $return = $this->pair('validate',$lang);

        if($filter === true)
        $return = Base\Arr::cleanNullBool($return);

        return $return;
    }


    // required
    // retourne une string pour chaque cellule qui ne passe pas le test required
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les cellules qui passent le test ne sont pas retournés
    final public function required(bool $lang=false,bool $filter=true):array
    {
        $return = $this->pair('required',$lang);

        if($filter === true)
        $return = Base\Arr::cleanNullBool($return);

        return $return;
    }


    // unique
    // retourne une string pour chaque cellule qui ne passe pas le test unique
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les cellules qui passent le test ne sont pas retournés
    final public function unique(bool $lang=false,bool $filter=true):array
    {
        $return = $this->pair('unique',$lang);

        if($filter === true)
        $return = Base\Arr::cleanNullBool($return);

        return $return;
    }


    // compare
    // retourne une tableau pour chaque cellule qui ne passe pas le test compare
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les cellules qui passent le test ne sont pas retournés
    final public function compare(bool $lang=false,bool $filter=true):array
    {
        $return = $this->pair('compare',$lang);

        if($filter === true)
        $return = Base\Arr::cleanNullBool($return);

        return $return;
    }


    // completeValidation
    // retourne un tableau avec les résultats de required et des validations sur toutes les cellules de l'objet
    // possible de retourner le texte si lang est true
    // par défaut cache est true, donc retourne la cache de la validation de la cellule
    // par défaut filter est true, donc les cellules qui passent les tests ne sont pas retournés
    final public function completeValidation(bool $lang=false,bool $filter=true):array
    {
        $return = $this->pair('completeValidation',$lang);

        if($filter === true)
        $return = Base\Arr::cleanNullBool($return);

        return $return;
    }


    // update
    // passe toutes les cellules, sauf la primaire, dans la méthode onUpdate
    final public function update(?array $option=null):self
    {
        foreach ($this->arr() as $key => $cell)
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
        foreach ($this->arr() as $key => $cell)
        {
            if(!$cell->isPrimary())
            $cell->delete($option);
        }

        return $this;
    }


    // hasChanged
    // retourne vrai si une des cellules de cells a changé
    // si une cellule a un committed callback, on considère qu'elle a changé
    final public function hasChanged():bool
    {
        $return = false;

        foreach ($this->arr() as $cell)
        {
            if($cell->hasChanged())
            {
                $return = true;
                break;
            }
        }

        return $return;
    }


    // notEmpty
    // retourne un objet avec toutes les cellules non vides
    final public function notEmpty():self
    {
        return $this->filter(['isNotEmpty'=>true]);
    }


    // firstNotEmpty
    // retoure la première cellule non vide
    final public function firstNotEmpty():?Cell
    {
        return $this->notEmpty()->first();
    }


    // set
    // change la valeur d'une cellule
    // possible d'enrobber l'opération dans un tryCatch
    // possible de faire une prévalidation via option
    final public function set($key,$value,?array $option=null):parent
    {
        $option = Base\Arr::plus(['tryCatch'=>false],$option);
        $cell = $this->checkGet($key);

        if($option['tryCatch'] === true)
        {
            try
            {
                $cell->set($value,$option);
            }

            catch (Main\CatchableException $e)
            {
                $e->catched(['com'=>false]);
                $cell->setException($e);
            }
        }

        else
        $cell->set($value,$option);

        return $this->checkAfter();
    }


    // sets
    // change la valeur de toutes les cellules
    // les cellules sont regroupés par setPriority avant de faire le loop
    // possible de faire une prévalidation via option
    final public function sets(array $keyValue,?array $option=null):parent
    {
        foreach ($this->groupSetPriority() as $cells)
        {
            foreach ($keyValue as $key => $value)
            {
                if($cells->exists($key))
                {
                    $this->set($key,$value,$option);
                    unset($keyValue[$key]);
                }
            }
        }

        return $this->checkAfter();
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
        $return = new static();
        $option = Base\Arr::plus($option,['preValidate'=>false]);

        foreach ($this->arr() as $cell)
        {
            if($cell->isIncluded($option['required'] ?? true))
            {
                if(!$cell->hasChanged() && $cell->col()->hasAttrInclude())
                $cell->setSelf($option);

                $return->add($cell);
            }
        }

        return $return;
    }


    // keyValue
    // retourne les clés et valeurs des cellules de la ligne sous forme de tableau associatif
    // possible de retourner le résultat de get si get est true, sinon ce sera value
    final public function keyValue(bool $get=false):array
    {
        return $this->pair(($get === true)? 'get':'value');
    }


    // label
    // retourne un tableau avec toutes les label des cellules
    final public function label($pattern=null,?string $lang=null,?array $option=null):array
    {
        return $this->pair('label',$pattern,$lang,$option);
    }


    // description
    // retourne un tableau avec toutes les descriptions des cellules
    final public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):array
    {
        return $this->pair('description',$pattern,$replace,$lang,$option);
    }


    // groupSetPriority
    // retourne un tableau avec les cellules regroupés par setPriority
    final public function groupSetPriority():array
    {
        $return = $this->group('setPriority');
        $return = Base\Arr::keysSort($return,true);

        return $return;
    }


    // form
    // génère les éléments formulaires pour toutes les cellules
    final public function form(bool $str=false)
    {
        $return = $this->pair('form');
        return ($str === true)? implode($return):$return;
    }


    // formPlaceholder
    // génère les éléments formulaires avec placeholder pour toutes les cellules
    // le placeholder est le label de la cellule
    final public function formPlaceholder(bool $str=false)
    {
        $return = $this->pair('formPlaceholder');
        return ($str === true)? implode($return):$return;
    }


    // formWrap
    // génère les éléments formWrap pour toutes les cellules
    final public function formWrap(?string $wrap=null,$pattern=null,bool $str=false)
    {
        $return = $this->pair('formWrap',$wrap,$pattern);
        return ($str === true)? implode($return):$return;
    }


    // formPlaceholderWrap
    // génère les éléments formPlaceholderWrap pour toutes les cellules
    // le placeholder est le label de la cellule, donc le label apparaît deux fois
    final public function formPlaceholderWrap(?string $wrap=null,$pattern=null,bool $str=false)
    {
        $return = $this->pair('formPlaceholderWrap',$wrap,$pattern);
        return ($str === true)? implode($return):$return;
    }


    // segment
    // permet de remplacer les segments d'une chaîne par le contenu des cellules
    // par défaut utilise value de cellule, si get est true utilise get
    final public function segment(string $value,bool $get=false):string
    {
        $return = '';
        $segments = Base\Segment::get(null,$value);

        if(!empty($segments))
        $return = Base\Segment::sets(null,$this->keyValue($get),$value);

        return $return;
    }


    // htmlStr
    // retourne un tableau avec chaque cellule passé dans la méthode html
    // si str est true, retourne une string
    final public function htmlStr(string $html,bool $str=false,?array $option=null)
    {
        $return = $this->pair('htmlStr',$html,$option);

        if($str === true)
        $return = implode($return);

        return $return;
    }


    // writeFile
    // écrit les cellules dans l'objet file fourni en argument
    // par défaut le type est format, donc passe dans export
    // par exemple pour une ligne de csv
    final public function writeFile(Main\File $file,?array $option=null):self
    {
        $option = Base\Arr::plus(['context'=>'noHtml','type'=>'format'],$option);
        $array = [];

        foreach ($this as $key => $cell)
        {
            if($option['type'] === 'format')
            $value = $cell->export($option);

            else
            $value = (string) $cell;

            $array = Base\Arr::append($array,$value);
        }

        $file->write($array,$option);

        return $this;
    }


    // keyClassExtends
    // retourne un tableau utilisé par onPrepareKey
    final public static function keyClassExtends():array
    {
        return [Cell::getOverloadClass(),Col::getOverloadClass()];
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Cells':null;
    }
}
?>