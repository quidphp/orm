<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Orm\Operation;
use Quid\Base;
use Quid\Main;
use Quid\Orm;

// update
// class used for an update operation on a table row
class Update extends Orm\RowOperation
{
    // config
    protected static array $config = [
        'partial'=>false,
        'log'=>true,
        'com'=>false,
        'strict'=>true,
        'finalValidate'=>true,
        'onCommitted'=>true,
        'include'=>true, // ceci est utilisé pour updateChanged
        'catchException'=>false
    ];


    // types
    protected static array $types = ['update','updateChanged','updateValid']; // types de processus possible


    // trigger
    // lance l'opération update
    // le type doit être fourni
    final public function trigger(string $type):?int
    {
        $return = null;
        static::checkType($type);
        $return = $this->$type();

        return $return;
    }


    // update
    // sauve les cellules de la ligne ayant changés
    // toutes les cellules sont passés dans update avant
    // toutes les cellules sont passés dans updateBefore
    // seuls les cellules ayant changés sont envoyés à la db
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final protected function update():?int
    {
        $return = null;
        $attr = $this->attr();
        $cells = $this->cells();
        $cells->update($attr);
        $return = $this->beforeAssoc($cells,true);

        return $return;
    }


    // updateChanged
    // sauve toutes les cellules de la ligne ayant changé
    // possible de mettre les include ou non, par défaut oui
    // différence: update est seulement appelé si au moins une cellule a changé
    // seuls les cellules ayant changés sont envoyés à update et updateBefore
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final protected function updateChanged():?int
    {
        $return = null;
        $include = $this->getAttr('include');

        $attr = $this->attr();
        $cells = $this->cells();
        $changed = $cells->changed($include,$attr);

        if($changed->isNotEmpty())
        {
            $cells->update($attr);
            $cells = $cells->changed($include,$attr);
            $return = $this->beforeAssoc($cells,true);
        }

        return $return;
    }


    // updateValid
    // sauve toutes les cellules valide et ayant changés dans la ligne
    // différence: update est seulement appelé si au moins une cellule a changé
    // seuls les cellules valides sont envoyés à la db
    // un message de communication peut être généré pour indiquer que la sauvegarde est partielle
    // retourne 0 si rien n'a changé, null s'il y a une erreur lors du update
    final protected function updateValid():?int
    {
        $return = null;
        $attr = $this->attr();
        $cells = $this->cells();
        $changed = $cells->changed(true,$attr);
        $noChange = true;

        if($changed->isNotEmpty())
        {
            $valid = $this->beforeValid($changed);

            if(!($valid === null || $valid->changed(false,$attr)->isEmpty()))
            {
                $noChange = false;
                if($valid->count() !== $changed->count())
                {
                    $this->setAttr('partial',true);
                    $attr = $this->attr();
                }

                $cells = $cells->update($attr)->changed(true,$attr);

                foreach ($cells as $key => $cell)
                {
                    if(!$valid->exists($key) && !$changed->exists($key))
                    $valid->add($cell);
                }

                $return = $this->beforeAssoc($valid,true);
            }
        }

        if($noChange === true)
        {
            $return = 0;
            $this->after($return,null);
        }

        return $return;
    }


    // beforeValid
    // utilisé par updateValid et updateValid pour filtrer les cellules non valide
    // un message est ajouté si com est true
    final protected function beforeValid(Orm\Cells $return):?Orm\Cells
    {
        $row = $this->row();
        $com = $this->getAttr('com');
        $completeValidation = $return->completeValidation($com);

        if(!empty($completeValidation))
        {
            if($com === true)
            $row->updateCom($completeValidation);

            elseif($this->getAttr('strict') === true)
            static::throw('invalid',$this->table(),$row,$completeValidation);

            $names = Base\Arr::valuesStrip(array_keys($completeValidation),$return->names());

            if(!empty($names))
            $return = $return->gets(...$names);

            else
            $return = null;
        }

        return $return;
    }


    // beforeAssoc
    // méthode protégé utilisé avant d'envoyer à assoc
    // gère validate et finalValidate
    final protected function beforeAssoc(Orm\Cells $cells,bool $changed=true):?int
    {
        $return = null;

        if($this->beforeValid($cells) === $cells)
        {
            $return = 0;
            $proceed = true;

            if($this->getAttr('finalValidate') === true)
            $proceed = $this->beforeFinalValidate($cells);

            if($proceed === true)
            {
                $array = [];
                $attr = $this->attr();
                $loop = ($changed === true)? $cells->changed(true,$attr):$cells->withoutPrimary();
                $array = $loop->keyValue();

                $return = $this->assoc($array);
            }
        }

        return $return;
    }


    // beforeFinalValidate
    // méthode protégé, gère la validation final avant le update
    // prendre note que final validation bloque entièrement l'update de la ligne, pas seulement les cellules en problème
    final protected function beforeFinalValidate(Orm\Cells $cells):bool
    {
        $return = true;
        $row = $this->row();
        $attr = $this->attr();
        $finalValidation = $row->updateFinalValidate($cells,$attr);

        if(!empty($finalValidation))
        {
            $return = false;

            if($this->getAttr('com') === true)
            $row->updateCom($finalValidation);

            elseif($this->getAttr('strict') === true)
            static::throw('invalid',$this->table(),$row,$finalValidation);
        }

        return $return;
    }


    // assoc
    // sauve la ligne via un tableau associatif
    // la validation n'a pas lieu à partir de cette méthode, il faut utiliser une des autres variantes de update
    // si la requête réussi commit les valeurs dans les cellules
    // exception envoyé si on tente de changer la valeur de la clé primaire
    // par défaut l'événement est log, la validation a lieu, mais com est false
    final protected function assoc(array $set):?int
    {
        $return = null;
        $db = $this->db();
        $attr = $this->attr();
        $row = $this->row();
        $table = $this->table();
        $cells = $this->cells();
        $result = null;

        try
        {
            if(empty($set))
            $result = 0;

            elseif(!$cells->exists(...array_keys($set)))
            static::throw('columnsNoMatch');

            elseif(array_key_exists($db->primary(),$set))
            static::throw('cannotSetPrimaryCell');

            elseif(!$row->isUpdateable($attr))
            static::catchable(null,'notUpdatable',$row,$table);

            else
            {
                $catchException = $this->getAttr('catchException');
                $log = $this->getAttr('log');

                if($log === false)
                $db->off();

                if($catchException === true)
                $db->setExceptionClass(true);

                $result = $db->update($table,$set,$row);

                if($catchException === true)
                $db->setExceptionClass(false);

                if($log === false)
                $db->on();
            }
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        finally
        {
            $this->after($result,$set);

            if(is_int($result))
            {
                $row->callThis(function() use($result,$attr,$set) {
                    if($result === 1)
                    $this->onUpdated($attr);

                    $this->cellsRefresh($set);
                });

                $return = $result;
            }
        }

        return $return;
    }


    // after
    // gère la communication après la requête update si com est true
    // si com est false et qu'il y a une exception attrapable, renvoie
    final public function after($result,?array $set=null):void
    {
        if($this->getAttr('com') === true)
        {
            $in = [];
            $row = $this->row();
            $lang = $this->db()->lang();
            $name = $this->table()->name();

            if($result === 1)
            {
                if($this->getAttr('partial') === true)
                {
                    $key = ($lang->existsCom('pos',"update/$name/partial"))? $name:'*';
                    $in[] = ['pos',"update/$key/partial"];
                }

                else
                {
                    $key = ($lang->existsCom('pos',"update/$name/success"))? $name:'*';
                    $in[] = ['pos',"update/$key/success"];
                }
            }

            elseif($result === 0)
            {
                $key = ($lang->existsCom('pos',"update/$name/noChange"))? $name:'*';
                $in[] = ['pos',"update/$key/noChange"];
            }

            elseif(is_int($result) && $result > 1)
            {
                $key = ($lang->existsCom('neg',"update/$name/tooMany"))? $name:'*';
                $in[] = ['neg',"update/$key/tooMany"];
            }

            elseif($result instanceof Main\Contract\Catchable)
            {
                $key = ($lang->existsCom('neg',"update/$name/exception"))? $name:'*';
                $in[] = ['neg',"update/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
                $result->catched(['com'=>false]);
            }

            else
            {
                $key = ($lang->existsCom('neg',"update/$name/system"))? $name:'*';
                $in[] = ['neg',"update/$key/system"];
            }

            $row->updateCom($in,null,null,null,null,true);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($this->getAttr('strict') === true && !in_array($result,[0,1],true))
        static::throw('updateFailed',$result,'strictMode');

        if($this->getAttr('onCommitted') === true && in_array($result,[0,1],true) && is_array($set) && !empty($set))
        $this->committed($set);

        return;
    }


    // committed
    // lance le callback onCommitted sur toutes les colonnes qui ont changés
    final protected function committed(array $set):void
    {
        $cells = $this->cells(...array_keys($set));
        $attr = $this->attr();

        foreach ($cells as $key => $cell)
        {
            if($cell->hasChanged())
            $cell->callThis(fn() => $this->onCommitted(false,$attr));
        }

        return;
    }


    // checkType
    // envoie une exception si le type donné en argument n'existe pas
    final public static function checkType(string $value):void
    {
        if(!in_array($value,static::$types,true))
        static::throw($value);

        return;
    }
}
?>