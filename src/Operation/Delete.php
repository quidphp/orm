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

// delete
// class used for a delete operation on a table row
class Delete extends Orm\RowOperation
{
    // config
    public static $config = [
        'log'=>true,
        'com'=>false,
        'strict'=>true
    ];


    // trigger
    // efface une ligne de la base de donnée et délie l'objet de table
    // la ligne est ensuite empty et mis dans un état inutilisable
    // toutes les cellules sont passés dans delete, l'envoie d'une exception arrêtera le delete
    // par défaut l'événement est log et com est false
    final public function trigger():?int
    {
        $return = null;
        $row = $this->row();
        $table = $this->table();
        $db = $this->db();
        $result = null;
        $attr = $this->attr();

        try
        {
            $log = $this->getAttr('log');

            if(!$row->isDeleteable($attr))
            static::catchable(null,'notDeleteable',$row);

            $row->cells()->delete($attr);

            if($log === false)
            $db->off();

            $result = $db->delete($table,$row);

            if($log === false)
            $db->on();
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        finally
        {
            $this->after($result);

            if(is_int($result))
            {
                if($result === 1)
                {
                    $row->callThis(function() use($attr) {
                        $this->onDeleted($attr);
                    });
                }

                $row->unlink();

                $return = $result;
            }
        }

        return $return;
    }


    // after
    // gère la communication après la requête delete si com est true
    // si com est false et qu'il y a une exception attrapable, renvoie
    final protected function after($result):void
    {
        if($this->getAttr('com') === true)
        {
            $lang = $this->db()->lang();
            $row = $this->row();
            $name = $row->tableName();
            $in = [];

            if($result === 1)
            {
                $key = ($lang->existsCom('pos',"delete/$name/success"))? $name:'*';
                $in[] = ['pos',"delete/$key/success"];
            }

            elseif($result === 0)
            {
                $key = ($lang->existsCom('neg',"delete/$name/notFound"))? $name:'*';
                $in[] = ['neg',"delete/$key/notFound"];
            }

            elseif(is_int($result) && $result > 1)
            {
                $key = ($lang->existsCom('neg',"delete/$name/tooMany"))? $name:'*';
                $in[] = ['neg',"delete/$key/tooMany"];
            }

            elseif($result instanceof Main\Contract\Catchable)
            {
                $key = ($lang->existsCom('neg',"delete/$name/exception"))? $name:'*';
                $in[] = ['neg',"delete/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
                $result->catched(['com'=>false]);
            }

            else
            {
                $key = ($lang->existsCom('neg',"delete/$name/system"))? $name:'*';
                $in[] = ['neg',"delete/$key/system"];
            }

            $this->com($in,null,null,null,true);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($this->getAttr('strict') === true && !in_array($result,[0,1],true))
        static::throw('deleteFailed',$result,'strictMode');

        return;
    }


    // com
    // méthode utilisé pour générer la communication pour une suppression
    // si le value est associatif, envoie dans com/prepareIn
    final protected function com(array $value,?string $label=null,?array $replace=null,?array $attr=null,bool $prepend=false):void
    {
        $com = $this->db()->com();

        if(!empty($value))
        {
            $row = $this->row();
            $table = $this->table();

            $label = ($label === null)? $row->label():$label;
            $attr = Base\Attr::append(['row','delete','data'=>['table'=>$table,'primary'=>$row,'action'=>'delete']],$attr);

            if(Base\Arr::isAssoc($value))
            $value = $com->prepareIn('neutral','neg',$value);

            if(!empty($value))
            {
                $method = ($prepend === true)? 'prepend':'append';
                $com->$method('neutral',$label,$replace,$attr,...$value);
            }
        }

        return;
    }
}
?>