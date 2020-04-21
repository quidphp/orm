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

// cols
// class for a collection of many columns within a same table
class Cols extends Main\MapObj
{
    // trait
    use Main\Map\_readOnly;
    use Main\Map\_sort;


    // config
    public static $config = [];


    // dynamique
    protected $mapAllow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
    protected $mapIs = Col::class; // classe d'objet permis
    protected $mapSortDefault = 'priority'; // défini la méthode pour sort par défaut


    // construct
    // construit un nouvel objet cols
    final public function __construct(...$values)
    {
        $this->add(...$values);

        return;
    }


    // toString
    // retourne les noms de colonnes séparés par des virgules
    final public function __toString():string
    {
        return implode(',',$this->names());
    }


    // onPrepareKey
    // prepare une clé pour les méthodes qui soumette une clé
    // peut fournir un index, un tableau qui retournera la première existante, une string, une colonne ou une cellule
    // support pour shortcut si string
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
    // les lignes sont toujours retournés dans un nouvel objet cols
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
    // arrayAccess offsetSet est seulement permis si la clé est null []
    final public function offsetSet($key,$value):void
    {
        if($key === null)
        $this->add($value);

        else
        static::throw('arrayAccess','onlyAllowedWithNullKey');

        return;
    }


    // names
    // retourne les noms de colonnes contenus dans l'objet
    final public function names():array
    {
        return $this->keys();
    }


    // namesWithoutPrimary
    // retourne les noms de colonnes contenus dans l'objet sans la colonne primaire
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


    // add
    // ajoute une ou plusieurs colonnes dans l'objet
    // valeurs doivent être des objets col
    // possible de fournir un objet cols
    // deux objets identiques ne peuvent pas être ajoutés dans cols
    // des objets de différentes tables ne peuvent être ajoutés dans cols
    final public function add(...$values):self
    {
        $this->checkAllowed('add');
        $values = $this->prepareValues(...$values);
        $firstTable = $this->table();
        $data =& $this->arr();

        foreach ($values as $value)
        {
            if(!$value instanceof Col)
            static::throw('requiresCol');

            $table = $value->table();
            $firstTable = (empty($firstTable))? $table:$firstTable;

            if($table !== $firstTable)
            static::throw('colMustBeFromSameTable');

            $name = $value->name();

            if(!array_key_exists($name,$data))
            $data[$name] = $value;

            else
            static::throw('colAlreadyIn',$name);
        }

        return $this->checkAfter();
    }


    // are
    // retourne vrai si le tableau est compatible avec les colonnes de la table
    // doit avoir exactement les mêmes noms et nombres de colonne, incluant la clé primaire
    final public function are(...$cols):bool
    {
        return Base\Arr::keysAre($this->prepareKeys(...$cols),$this->arr());
    }


    // withoutPrimary
    // retourne un objet cols avec les colonnes sans la clé primaire
    final public function withoutPrimary():?self
    {
        return $this->gets(...$this->namesWithoutPrimary());
    }


    // default
    // retourne un tableau associatif avec toutes les colonnes ayant une valeur par défaut
    // ne retourne pas la clé primaire
    final public function default():array
    {
        $return = [];

        foreach ($this->arr() as $key => $col)
        {
            if(!$col->isPrimary() && $col->hasDefault())
            $return[$key] = $col->default();
        }

        return $return;
    }


    // value
    // passe les valeurs de set dans les méthode onGet des colonnes
    // si onlyScalar est true, les valeurs de retour non scalaire ne sont pas conservés
    final public function value(array $set=[],bool $onlyScalar=false,bool $relation=false,?array $option=null):array
    {
        $return = [];
        $option = (array) $option;

        foreach ($set as $key => $value)
        {
            $col = $this->get($key);

            if(!empty($col))
            {
                if($relation === true && $col->isRelation())
                $value = $col->relation()->getStr($value,', ',false,true,$option);

                else
                {
                    $value = $col->callThis(function() use($value,$option) {
                        return $this->onGet($value,$option);
                    });
                }

                if($onlyScalar === false || is_scalar($value))
                $return[$key] = $value;
            }
        }

        return $return;
    }


    // isVisible
    // retourne vrai si tous les champs sont visibles
    final public function isVisible(?Main\Session $session=null):bool
    {
        $return = false;
        $args = [true,null,$session];
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
        $args = [true,null,$session];
        $hidden = $this->pair('isVisible',...$args);

        if(!in_array(true,$hidden,true))
        $return = true;

        return $return;
    }


    // isRequired
    // retourne un tableau associatif avec toutes les colonnes ainsi que leur valeur ou valeur par défaut
    // ne retourne pas la clé primaire
    final public function isRequired(array $set=[]):array
    {
        $return = [];

        foreach ($this->arr() as $key => $col)
        {
            if($col->isRequired())
            {
                $v = (array_key_exists($key,$set))? $set[$key]:$col->default();
                $return[$key] = $v;
            }
        }

        return $return;
    }


    // isStillRequired
    // retourne un tableau associatif avec toutes les colonnes toujours requises
    // ne retourne pas la clé primaire
    final public function isStillRequired(array $set=[]):array
    {
        $return = [];

        foreach ($this->arr() as $key => $col)
        {
            $v = (array_key_exists($key,$set))? $set[$key]:$col->default();

            if($col->isStillRequired($v))
            $return[$key] = $v;
        }

        return $return;
    }


    // isStillRequiredEmpty
    // retourne vrai si le tableau de isStillRequired est vide
    // ceci signifie que toutes les colonnes requises ont une valeur
    final public function isStillRequiredEmpty(array $set=[]):bool
    {
        return (empty($this->isStillRequired($set)))? true:false;
    }


    // rules
    // retourne toutes les règles de validations et required des colonnes
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
            $col = $this->checkGet($key);
            $return[$key] = $col->preValidatePrepare($value);
        }

        return $return;
    }


    // preValidate
    // retourne un tableau avec les résultats des pré-validations sur toutes les colonnes de l'objet
    // la pré-validation n'a pas lieu si la valeur est vide
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent le test ne sont pas retournés
    final public function preValidate(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('preValidate',$set,$lang,$filter);
    }


    // validate
    // retourne un tableau avec les résultats des validations sur toutes les colonnes de l'objet
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent le test ne sont pas retournés
    final public function validate(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('validate',$set,$lang,$filter);
    }


    // required
    // retourne une string pour chaque colonne qui ne passe pas le test required
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent le test ne sont pas retournés
    final public function required(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('required',$set,$lang,$filter);
    }


    // unique
    // retourne une string pour chaque colonne qui ne passe pas le test unique
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent le test ne sont pas retournés
    final public function unique(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('unique',$set,$lang,$filter);
    }


    // compare
    // retourne une tableau pour chaque colonne qui ne passe pas le test compare
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent le test ne sont pas retournés
    final public function compare(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('compare',$set,$lang,$filter,true);
    }


    // completeValidation
    // retourne un tableau avec les résultats de required et des validations sur toutes les colonnes de l'objet
    // possible de retourner le texte si lang est true
    // par défaut filter est true, donc les colonnes qui passent les tests ne sont pas retournés
    final public function completeValidation(array $set=[],bool $lang=false,bool $filter=true):array
    {
        return $this->triggerValidate('completeValidation',$set,$lang,$filter,true);
    }


    // triggerValidate
    // méthode protégé utilisé par preValidate, validate, required et completeValidation
    // si argSet est true, alors le tableau set est passé en deuxième argument
    final protected function triggerValidate(string $method,array $set=[],bool $lang=false,bool $filter=true,bool $argSet=false):array
    {
        $return = [];

        foreach ($this->arr() as $key => $col)
        {
            $set[$key] = (array_key_exists($key,$set))? $set[$key]:$col->default();
        }

        foreach ($this->arr() as $key => $col)
        {
            $v = $set[$key];

            if($argSet === true)
            $v = $col->$method($v,$set,$lang);
            else
            $v = $col->$method($v,$lang);

            if($filter === false || $v !== true)
            $return[$key] = $v;
        }

        return $return;
    }


    // included
    // retourne un objet avec les colonnes incluses par défaut
    // inclusion des required est true par défaut
    final public function included(?array $option=null):self
    {
        return $this->filter(['isIncluded'=>true],'insert',$option['required'] ?? true);
    }


    // insert
    // change la valeur d'une colonne et retourne la valeur
    // possible d'enrobber l'opération dans un tryCatch
    final public function insert($key,$value,array $set=[],?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['tryCatch'=>false],$option);
        $col = $this->checkGet($key);

        if($option['tryCatch'] === true)
        {
            try
            {
                $return = $col->insert($value,$set,$option);
            }

            catch (Main\CatchableException $e)
            {
                $e->catched(['com'=>false]);
                $col->setException($e);
            }
        }

        else
        $return = $col->insert($value,$set,$option);

        return $return;
    }


    // inserts
    // retourne le tableau d'insert avec toutes les valeurs de retour des colonnes ayant un callback onInsert, ayant l'attribut isIncluded ou étant dans le tableau set
    // si default est true dans option, les colonnes avec valeurs par défaut non incluses dans le tableau de retour sont ajoutés
    // ne retourne pas la clé primaire
    final public function inserts(array $set=[],?array $option=null):array
    {
        $return = [];
        $option = Base\Arr::plus(['default'=>false,'required'=>true],$option);
        $row = $set;

        foreach ($this->groupSetPriority() as $cols)
        {
            $included = $cols->included($option);

            foreach ($included->arr() as $key => $col)
            {
                if(!array_key_exists($key,$set))
                $return[$key] = $row[$key] = $this->insert($col,true,$row,$option);
            }

            foreach ($set as $key => $value)
            {
                if($cols->exists($key) && !array_key_exists($key,$return))
                {
                    $return[$key] = $row[$key] = $this->insert($key,$value,$row,$option);
                    unset($set[$key]);
                }
            }

            if($option['default'] === true)
            {
                foreach ($this->default() as $key => $value)
                {
                    if(!array_key_exists($key,$return))
                    $return[$key] = $row[$key] = $value;
                }
            }
        }

        return $return;
    }


    // label
    // retourne le label de toutes les colonnes
    final public function label($pattern=null,?string $lang=null,?array $option=null):array
    {
        return $this->pair('label',$pattern,$lang,$option);
    }


    // description
    // retourne la description de toutes les colonnes
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
    // génère les éléments formulaires pour toutes les colonnes
    final public function form(bool $str=false)
    {
        $return = $this->pair('form');
        return ($str === true)? implode($return):$return;
    }


    // formPlaceholder
    // génère les éléments formulaires avec placeholder pour toutes les colonnes
    // le placeholder est le label de la colonne
    final public function formPlaceholder(bool $str=false)
    {
        $return = $this->pair('formPlaceholder');
        return ($str === true)? implode($return):$return;
    }


    // formWrap
    // génère les éléments formWrap pour toutes les colonnes
    final public function formWrap(?string $wrap=null,$pattern=null,bool $str=false)
    {
        $return = $this->pair('formWrap',$wrap,$pattern);
        return ($str === true)? implode($return):$return;
    }


    // formPlaceholderWrap
    // génère les éléments formPlaceholderWrap pour toutes les colonnes
    // le placeholder est le label de la colonne, donc le label apparaît deux fois
    final public function formPlaceholderWrap(?string $wrap=null,$pattern=null,bool $str=false)
    {
        $return = $this->pair('formPlaceholderWrap',$wrap,$pattern);
        return ($str === true)? implode($return):$return;
    }


    // htmlStr
    // retourne un tableau avec chaque colonne passé dans la méthode html
    // la valeur est toujours celle par défaut
    // si str est true, retourne une string
    final public function htmlStr(string $html,bool $str=false)
    {
        $return = $this->pair('htmlStr',true,$html);
        return ($str === true)? implode($return):$return;
    }


    // orderable
    // retourne un objet cols avec toutes les colonnes ordonnables
    final public function orderable():self
    {
        return $this->filter(['isOrderable'=>true]);
    }


    // filterable
    // retourne un objet cols avec toutes les colonnes filtrable
    final public function filterable():self
    {
        return $this->filter(['isFilterable'=>true]);
    }


    // searchable
    // retourne un objet cols avec toutes les colonnes cherchables
    final public function searchable():self
    {
        return $this->filter(['isSearchable'=>true]);
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
        $return = false;

        if($this->isNotEmpty())
        {
            $return = true;

            foreach ($this as $col)
            {
                if(!$col->isSearchTermValid($value))
                {
                    $return = false;
                    break;
                }
            }
        }

        return $return;
    }


    // writeFile
    // écrit les colonnes dans l'objet file fourni en argument
    // par exemple pour une première ligne de csv
    final public function writeFile(Main\File $file,?Cells $cells=null,?array $option=null):self
    {
        $option = Base\Arr::plus(['type'=>'format'],$option);
        $array = [];

        foreach ($this as $key => $col)
        {
            if($option['type'] === 'format' && !empty($cells))
            {
                $cell = $cells->checkGet($key);
                $value = $col->export($cell,$option);
            }

            else
            $value = $col->name();

            $array = Base\Arr::append($array,$value);
        }

        $file->write($array,$option);

        return $this;
    }


    // keyClassExtends
    // retourne un tableau utilisé par onPrepareKey
    final public static function keyClassExtends():array
    {
        return [Col::getOverloadClass(),Cell::getOverloadClass()];
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Cols':null;
    }
}
?>