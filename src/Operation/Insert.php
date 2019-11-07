<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm\Operation;
use Quid\Orm;
use Quid\Main;
use Quid\Base;

// insert
// class used for a insert operation on a table
class Insert extends Orm\TableOperation
{
    // config
    public static $config = [
        'row'=>true,
        'reservePrimary'=>true,
        'default'=>false,
        'log'=>true,
        'preValidate'=>false,
        'validate'=>true,
        'finalValidate'=>true,
        'com'=>false,
        'onCommitted'=>true,
        'strict'=>true
    ];
    
    
    // trigger
    // tente l'insertion d'une nouvelle ligne dans la table
    // insère les colonnes qui ont un insert ou qui sont dans le tableau set (après avoir passé dans onSet)
    // les valeurs par défaut des colonnes ne sont pas insérés si default est false, donc les valeurs par défaut défini dans col seront ignorés
    // envoie une exception si une des colonnes est toujours requises
    // lance le callback onInserted sur la ligne chargée, une fois l'insertion réussie (seulement si row est true)
    // si row est true, l'objet ligne sera retourné plutôt que le insertId, c'est le comportement par défaut
    // preValidate permet de lancer les tests de prévalidation sur les valeurs, faux par défaut, ce test permet de valider les valeurs en provenance de post
    // défaut l'événement est log, la validation a lieu, mais com est false
    // reservePrimary permet de connaître l'id de la ligne avant de faire le insert
    final public function trigger(array $set)
    {
        $return = null;
        $table = $this->table();
        $cols = $table->cols();
        $db = $this->db();
        $primary = $table->primary();
        $attr = $this->attr();

        try
        {
            $result = null;

            if(!empty($set) && !$cols->exists(...array_keys($set)))
            static::throw('columnsNoMatch');

            if($this->getAttr('reservePrimary') === true && empty($set[$primary]))
            {
                $reserved = $table->reservePrimary();
                if(is_int($reserved))
                $set[$primary] = $reserved;
            }

            $preValidate = true;
            if($this->getAttr('preValidate') === true)
            {
                $set = $cols->preValidatePrepare($set);
                $preValidate = $this->preValidate($cols,$set);
                if(is_array($preValidate))
                {
                    if(empty($preValidate))
                    static::throw('nothingValid');

                    else
                    $cols = $cols->gets(...$preValidate);
                }
            }

            $set = $cols->inserts($set,$attr);
            $validate = ($this->getAttr('validate') === false || $this->validate($cols,$set))? true:false;

            if($preValidate === true && $validate === true)
            {
                $finalValidate = ($this->getAttr('finalValidate') === false || $this->finalValidate($cols,$set))? true:false;

                if($finalValidate === true)
                {
                    $log = $this->getAttr('log');

                    if($log === false)
                    $db->off();

                    $result = $db->insert($table,$set);

                    if($log === false)
                    $db->on();
                }
            }
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        $this->after($result);

        if(is_int($result))
        {
            $return = $result;
            $outputRow = $this->getAttr('row');
            $onCommitted = $this->getAttr('onCommitted');
            
            if($outputRow === true || $onCommitted === true)
            {
                $row = null;

                if(is_int($result) && $result > 0)
                {
                    $row = $table->row($return);

                    if($onCommitted === true && !empty($return) && is_array($set) && !empty($set))
                    $this->committed($row,$set);
                }

                if($outputRow === true)
                {
                    $return = $row;

                    if(!$return instanceof Orm\Row)
                    static::throw('databaseError');

                    else
                    {
                        Base\Call::bindTo($return,function() use($attr) {
                            $this->onInserted($attr);
                        });
                    }
                }
            }
        }

        return $return;
    }
    
    
    // preValidate
    // s'occupe de la pré-validation avant l'opération insert
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne true ou le tableau des colonnes qui ont passsés le test prévalidate
    final protected function preValidate(Orm\Cols $cols,array $set)
    {
        $return = true;
        $com = $this->getAttr('com');
        $table = $this->table();
        $preValidate = $cols->preValidate($set,$com);

        if(!empty($preValidate))
        {
            $return = Base\Arr::valuesStrip(array_keys($preValidate),$cols->names());

            if($com === true)
            $table->insertCom($preValidate,null,null,null,['table']);

            elseif($this->getAttr('strict') === true)
            static::throw($table,...array_keys($preValidate));
        }

        return $return;
    }


    // validate
    // s'occupe de la validation avant l'opération insert
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne un booléean
    final protected function validate(Orm\Cols $cols,array $set):bool
    {
        $return = true;
        $com = $this->getAttr('com');
        $table = $this->table();
        $completeValidation = $cols->completeValidation($set,$com);

        if(!empty($completeValidation))
        {
            $return = false;

            if($com === true)
            $table->insertCom($completeValidation,null,null,null,['table']);

            elseif($this->getAttr('strict') === true)
            static::throw($table,$completeValidation);
        }

        return $return;
    }


    // finalValidate
    // s'occupe de la validation finale avant l'opération insert
    // utilise la classe row
    // peut ajouter les erreurs à l'objet de communication ou envoyer une exception si strict est true
    // retourne un booléean
    final protected function finalValidate(Orm\Cols $cols,array $set):bool
    {
        $return = true;
        $table = $this->table();
        $rowClass = $table->rowClass();
        $finalValidation = $rowClass::insertFinalValidate($set,$this->attr());

        if(!empty($finalValidation))
        {
            $return = false;

            if($this->getAttr('com') === true)
            $table->insertCom($finalValidation,null,null,null,['table']);

            elseif($this->getAttr('strict') === true)
            static::throw($table,$finalValidation);
        }

        return $return;
    }


    // after
    // gère la communication après la requête insert si com est true
    // si com est false et qu'il y a une exception attrapable, renvoie
    final protected function after($result):void
    {
        if($this->getAttr('com') === true)
        {
            $label = null;
            $attr = [];
            $in = [];
            $table = $this->table();
            $db = $this->db();
            $lang = $db->lang();
            $name = $table->name();

            if(is_int($result) && $result > 0)
            {
                $key = ($lang->existsCom('pos',"insert/$name/success"))? $name:'*';
                $in[] = ['pos',"insert/$key/success"];
                $label = $lang->rowLabel($result,$name);
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
                    $result->catched(['com'=>false]);
                }

                else
                {
                    $key = ($lang->existsCom('neg',"insert/$name/failure"))? $name:'*';
                    $in[] = ['neg',"insert/$key/failure"];
                }
            }

            $table->insertCom($in,null,$label,null,$attr,true);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($this->getAttr('strict') === true && !(is_int($result) && $result > 0))
        static::throw('insertFailed',$result);

        return;
    }


    // committed
    // lance le callback onCommitted sur toutes les colonnes
    final protected function committed(Orm\Row $row,array $set):void
    {
        $attr = $this->attr();
        $cells = $row->cells(...array_keys($set));

        foreach ($cells as $key => $cell)
        {
            $v = $set[$key];
            $col = $cell->col();
            
            Base\Call::bindTo($col,function() use($cell,$attr) {
                $this->onCommitted($cell,true,$attr);
            });
        }

        return;
    }
}
?>