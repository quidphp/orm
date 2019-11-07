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

// truncate
// class used for a truncate operation on a table
class Truncate extends Orm\TableOperation
{
    // config
    public static $config = [
        'log'=>true,
        'com'=>false,
        'strict'=>true
    ];
    
    
    // trigger
    // truncate la table, les rows sont unlink
    // par défaut l'événement est log et com est false
    final public function trigger():bool
    {
        $return = false;
        $table = $this->table();
        $db = $this->db();
        $result = null;
        $attr = $this->attr();
        
        try
        {
            $log = $this->getAttr('log');
            
            if($log === false)
            $db->off();

            $result = $db->truncate($table);

            if($log === false)
            $db->on();
        }

        catch (Main\Contract\Catchable $result)
        {

        }

        finally
        {
            $this->after($result,$attr);
            
            Base\Call::bindTo($table,function() use($attr) {
                $this->onTruncated($attr);
            });
            
            $table->rowsUnlink();
            $return = true;
        }

        return $return;
    }
    
    
    // after
    // gère la communication après la requête truncate si com est true
    // si com est false et qu'il y a une exception attrapable, renvoie
    final protected function after($result):void
    {
        if($this->getAttr('com') === true)
        {
            $table = $this->table();
            $name = $table->name();
            $db = $this->db();
            $lang = $db->lang();
            
            $attr = ['table','truncate','data'=>['table'=>$table,'action'=>'truncate']];
            $in = [];

            if($result instanceof Main\Contract\Catchable)
            {
                $key = ($lang->existsCom('neg',"truncate/$name/exception"))? $name:'*';
                $in[] = ['neg',"truncate/$key/exception",['exception'=>$result->classFqcn(),'message'=>$result->getMessageArgs($lang)]];
                $result->catched(['com'=>false]);
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
            $db->com()->neutral($table->label(),null,$attr,...$in);
        }

        elseif($result instanceof Main\Contract\Catchable)
        throw $result;

        elseif($this->getAttr('strict') === true && !$result instanceof \PDOStatement)
        static::throw('truncateFailed');

        return;
    }
}
?>