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
use Quid\Orm;

// lang
// extended class for an object containing language texts related to the database
class Lang extends Main\Lang
{
    // config
    public static $config = [
        'path'=>[
            'direction'=>'direction',
            'dbLabel'=>'db/label',
            'dbDescription'=>'db/description',
            'tableLabel'=>'table/label',
            'tableDescription'=>'table/description',
            'colLabel'=>'col/label/*',
            'colTableLabel'=>'col/label',
            'colDescription'=>'col/description/*',
            'colTableDescription'=>'col/description',
            'rowLabel'=>'row/label',
            'rowLabelName'=>'row/labelName',
            'rowDescription'=>'row/description',
            'panelLabel'=>'panel/label',
            'panelDescription'=>'panel/description',
            'compare'=>'compare',
            'validate'=>'validate',
            'required'=>'required',
            'editable'=>'editable',
            'unique'=>'unique']
    ];


    // onChange
    // ajout le shortcut dans orm/syntax
    final protected function onChange():void
    {
        parent::onChange();

        if($this->inInst())
        Orm\Syntax::setShortcut('lang',$this->currentLang());

        return;
    }


    // direction
    // retourne le texte pour une direction, asc ou desc
    final public function direction(string $key,$lang=null,?array $option=null):?string
    {
        return $this->text($this->getPath('direction',strtolower($key),null,$lang,$option));
    }


    // dbLabel
    // retourne le label d'une base de donnée
    // si la db n'existe pas, utilise def
    final public function dbLabel(string $tables,?string $lang=null,?array $option=null):?string
    {
        return $this->def($this->getPath('dbLabel',$tables),null,$lang,$option);
    }


    // dbDescription
    // retourne la description d'une base de donnée
    // par défaut, la méthode error n'est pas lancé et retournera null si aucune description
    final public function dbDescription(string $tables,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        return $this->safe($this->getPath('dbDescription',$tables),$replace,$lang,$option);
    }


    // tableLabel
    // retourne le label d'une table
    // si la table n'existe pas, utilise def
    final public function tableLabel(string $table,?string $lang=null,?array $option=null):?string
    {
        return $this->def($this->getPath('tableLabel',$table),null,$lang,$option);
    }


    // tableDescription
    // retourne la description d'une table
    // par défaut, la méthode error n'est plas lancé et retournera null si aucune description
    final public function tableDescription(string $table,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        return $this->safe($this->getPath('tableDescription',$table),$replace,$lang,$option);
    }


    // colLabel
    // retourne le label d'une colonne, la string de la table est facultative
    // s'il y a table recherche dans col/label/table/*, ensuite dans col/label/*/*
    // s'il y a une table et toujours introuvable, regarde si la colonne a un nom de relation, si oui retourne le nom de la table
    // si toujours introuvable, utilise def
    final public function colLabel(string $col,?string $table=null,?string $lang=null,?array $option=null):?string
    {
        $colLabel = $this->getPath('colLabel',$col);

        if(is_string($table))
        {
            $return = $this->safe($this->getPath('colTableLabel',[$table,$col]),null,$lang,$option);
            if(empty($return))
            {
                $return = $this->safe($colLabel,null,$lang,$option);

                if(empty($return))
                {
                    $table = Orm\ColSchema::table($col);

                    if(!empty($table))
                    $return = $this->tableLabel($table,$lang,$option);
                }
            }
        }

        if(empty($return))
        $return = $this->def($colLabel,null,$lang,$option);

        return $return;
    }


    // colDescription
    // retourne la description d'une colonne
    // utilise alt pour faire une recherche alternative dans col/description/* si introuvable sous le nom de la table, ou si pas de table donné
    // par défaut, la méthode error n'est plas lancé et la méthode retournera null si aucune description
    final public function colDescription(string $col,?string $table=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $colDescription = $this->getPath('colDescription',$col);

        if(is_string($table))
        $return = $this->alt($this->getPath('colTableDescription',[$table,$col]),$colDescription,$replace,$lang,Base\Arr::plus(['error'=>false],$option));
        else
        $return = $this->safe($colDescription,null,$lang,$option);

        return $return;
    }


    // rowLabel
    // retourne le label d'une row
    // le label de la table sera aussi cherché
    // une erreur sera envoyé si le texte et le texte alternatif n'existe pas
    final public function rowLabel(int $primary,string $table,?string $name=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $path = (is_string($name) && strlen($name) && $name !== (string) $primary)? 'rowLabelName':'rowLabel';
        $tableLabel = $this->tableLabel($table,$lang);
        $replace = ['primary'=>$primary,'table'=>$tableLabel,'name'=>$name];
        $return = $this->alt($this->getPath($path,$table),$this->getPath($path,'*'),$replace,$lang,$option);

        return $return;
    }


    // rowDescription
    // retourne la description d'une row
    // le label de la table sera aussi cherché
    // une erreur sera envoyé si le texte et le texte alternatif n'existe pas
    final public function rowDescription(int $primary,string $table,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $tableLabel = $this->tableLabel($table,$lang);
        $replace['primary'] = $primary;
        $replace['table'] = $tableLabel;
        $return = $this->alt($this->getPath('rowDescription',$table),$this->getPath('rowDescription','*'),$replace,$lang,Base\Arr::plus(['error'=>false],$option));

        return $return;
    }


    // panelLabel
    // retourne le label d'un panel
    // si le panel n'existe pas, utilise def
    final public function panelLabel(string $panel,?string $lang=null,?array $option=null):?string
    {
        return $this->def($this->getPath('panelLabel',$panel),null,$lang,$option);
    }


    // panelDescription
    // retourne la description d'un panel
    // par défaut, la méthode error n'est pas lancé et retournera null si aucune description
    final public function panelDescription(string $panel,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        return $this->safe($this->getPath('panelDescription',$panel),$replace,$lang,$option);
    }


    // validate
    // si value est null, retourne tout le tableau de contenu validate dans la langue
    // retourne null si inexistant
    // compatible avec base/lang
    final public function validate(?array $value=null,?string $lang=null,?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['path'=>null,'same'=>true],$option);

        if($value === null)
        $return = $this->pathAlternateTake('validate',$lang,$option['path']);

        elseif(is_array($value) && count($value) === 1)
        {
            $k = key($value);
            $v = current($value);
            $replace = null;
            $plural = null;
            $path = null;

            if($v instanceof \Closure)
            $v = $v('lang');

            if(is_array($v) && Base\Arr::isIndexed($v))
            $v = implode(', ',$v);

            if(is_numeric($k))
            $path = (is_string($v))? $this->pathAlternateValue('validate',$v,true,$option['path']):null;

            elseif(is_string($k) && $v !== null)
            {
                $path = $this->pathAlternateValue('validate',$k,true,$option['path']);
                $replace = ['%'=>$v];

                if(is_int($v) || is_array($v))
                $plural = $v;
            }

            if($path !== null)
            {
                if(empty($plural))
                $return = $this->same($path,$replace,$lang,$option);
                else
                $return = $this->plural($plural,$path,$replace,['s'=>'s'],$lang,$option);
            }
        }

        return $return;
    }


    // validates
    // retourne un tableau avec plusieurs textes d'erreur de validation
    // ne retourne rien pour les entrées non existantes (et pas d'erreur)
    final public function validates(array $values,?string $lang=null,?array $option=null):array
    {
        $return = [];

        foreach ($values as $key => $value)
        {
            if(is_numeric($key) && is_array($value) && count($value) === 1)
            {
                $key = key($value);
                $value = current($value);
            }

            $validate = $this->validate([$key=>$value],$lang,$option);

            if($validate !== null)
            $return[] = $validate;
        }

        return $return;
    }


    // compare
    // si value est null, retourne tout le tableau de contenu compare dans la langue
    // sinon retourne un texte d'erreur de validation
    // compatible avec base/lang
    // utilise def, donc aucune erreur envoyé si inexistant
    final public function compare(?array $value=null,?string $lang=null,?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['path'=>null],$option);

        if($value === null)
        $return = $this->pathAlternateTake('compare',$lang,$option['path']);

        elseif(is_array($value) && count($value) === 1)
        {
            $symbol = key($value);
            $v = current($value);

            if(is_string($symbol) && is_string($v))
            {
                $path = $this->pathAlternateValue('compare',$symbol,true,$option['path']);
                $replace = ['%'=>$v];
                $return = $this->def($path,$replace,$lang,$option);
            }
        }

        return $return;
    }


    // compares
    // retourne plusieurs textes d'erreur de comparaison
    // utilise def, donc aucune erreur envoyé si inexistant
    // retourne un tableau
    final public function compares(array $values,?string $lang=null,?array $option=null):array
    {
        $return = [];

        foreach ($values as $key => $value)
        {
            $return[] = $this->compare([$key=>$value],$lang,$option);
        }

        return $return;
    }


    // required
    // génère le message d'erreur pour champ requis
    // compatible avec base/lang
    final public function required($value=null,?string $lang=null,?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['path'=>null],$option);

        if($value === null)
        $return = $this->pathAlternateTake('required',$lang,$option['path']);

        else
        {
            $path = $this->pathAlternateValue('required','common',false,$option['path']);
            $return = $this->text($path,null,$lang,$option);
        }

        return $return;
    }


    // unique
    // génère le message d'erreur pour champ devant être unique
    // compatible avec base/lang
    final public function unique($value=null,?string $lang=null,?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['path'=>null],$option);

        if($value === null)
        $return = $this->pathAlternateTake('unique',$lang,$option['path']);

        else
        {
            $path = $this->pathAlternateValue('unique','common',false,$option['path']);
            $replace = ['%'=>''];

            if(is_array($value))
            {
                foreach ($value as $k => $v)
                {
                    if(is_scalar($v))
                    {
                        if(is_numeric($v))
                        $value[$k] = "#$v";
                    }

                    else
                    unset($value[$k]);
                }

                $value = implode(', ',$value);
            }

            if(is_scalar($value) && !is_bool($value))
            {
                $text = (is_numeric($value))? " (#$value)":" ($value)";
                $replace = ['%'=>$text];
            }

            $return = $this->text($path,$replace,$lang,$option);
        }

        return $return;
    }


    // editable
    // génère le message d'erreur pour champ editable
    // compatible avec base/lang
    final public function editable($value=null,?string $lang=null,?array $option=null)
    {
        $return = null;
        $option = Base\Arr::plus(['path'=>null],$option);

        if($value === null)
        $return = $this->pathAlternateTake('editable',$lang,$option['path']);

        else
        {
            $path = $this->pathAlternateValue('editable','common',false,$option['path']);
            $return = $this->text($path,null,$lang,$option);
        }

        return $return;
    }


    // pathAlternate
    // méthode utilisé par les méthodes de validation pour lang
    // permet d'aller vérifier si un chemin alternatif existe pour validate, compare, required ou unique avec sans valeur
    final public function pathAlternate(string $type,$alternate=null)
    {
        $return = null;

        if(!empty($type))
        {
            $base = $this->getPath($type);

            if(!empty($alternate))
            {
                $exists = Base\Arr::append($base,$alternate);

                if($this->exists($exists))
                $return = $exists;
            }

            if(empty($return))
            $return = $base;
        }

        return $return;
    }


    // pathAlternateTake
    // utilise pathAlternate et ensuite take
    final public function pathAlternateTake(string $type,?string $lang=null,$alternate=null)
    {
        $return = null;
        $path = $this->pathAlternate($type,$alternate);
        $return = $this->take($path,$lang);

        return $return;
    }


    // pathAlternateValue
    // méthode utilisé par les méthodes de validation pour lang
    // permet d'aller vérifier si un chemin alternatif existe pour validate, compare, required ou unique avec une valeur
    final public function pathAlternateValue(string $type,$value,bool $includeValue=true,$alternate=null)
    {
        $return = null;

        if(!empty($value) && !empty($type))
        {
            $base = $this->getPath($type);

            if(!empty($alternate))
            {
                $exists = Base\Arr::append($base,$alternate);

                if($includeValue === true)
                $exists = Base\Arr::append($exists,$value);

                if($this->exists($exists))
                $return = $exists;
            }

            if(empty($return))
            {
                $exists = Base\Arr::append($base,$value);

                if($this->exists($exists))
                $return = $exists;

                else
                $return = $value;
            }
        }

        return $return;
    }
}

// init
Lang::__init();
?>