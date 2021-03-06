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

// cells
// class for a collection of many cells within a same row
class Cells extends CellsMap
{
    // config
    protected static array $config = [];


    // dynamique
    protected ?array $mapAllow = ['add','unset','remove','empty','filter','sort','clone']; // méthodes permises
    protected ?string $mapSortDefault = 'priority'; // défini la méthode pour sort par défaut


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


    // isWhere
    // retourne vrai si les cellules correspondent à la vérification where du tableau en argument
    // similaire à une syntaxe sql mais ne supporte pas les méthodes base/sql whereThree, ni les and, or et paranthèses
    final public function isWhere(array $array):bool
    {
        $return = false;
        $array = Base\Obj::cast($array);
        $table = $this->table();

        if(!empty($table))
        {
            $db = $table->db();

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

            $firstTable = $firstTable ?: $table;
            $firstRow = $firstRow ?: $row;

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


    // set
    // change la valeur d'une cellule
    // possible d'enrobber l'opération dans un tryCatch
    // possible de faire une prévalidation via option
    final public function set($key,$value,?array $option=null):self
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
    final public function sets(array $keyValue,?array $option=null):self
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


    // keyValue
    // retourne les clés et valeurs des cellules de la ligne sous forme de tableau associatif
    // possible de retourner le résultat de get si get est true, sinon ce sera value
    final public function keyValue(bool $get=false):array
    {
        return $this->pair(($get === true)? 'get':'value');
    }


    // groupSetPriority
    // retourne un tableau avec les cellules regroupés par setPriority
    final public function groupSetPriority():array
    {
        $return = $this->group('setPriority');
        $return = Base\Arr::keysSort($return,true);

        return $return;
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


    // keyClassExtends
    // retourne un tableau utilisé par onPrepareKey
    final public static function keyClassExtends():array
    {
        return [Cell::classOverload(),Col::classOverload()];
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Cells':null;
    }
}
?>