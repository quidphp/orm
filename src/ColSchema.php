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

// colSchema
// class used to parse the information schema of a column
class ColSchema extends Main\Map
{
    // config
    protected static array $config = [
        'intMax'=>[ // détermine les valeurs maximales pour unt int
            'int'=>2147483647],
        'textLength'=>[
            'tinytext'=>255,
            'text'=>65535,
            'mediumtext'=>16777215,
            'longtext'=>4294967295],
        'patternChars'=>['_','*'], // caractères pour définir les patterns
        'pattern'=>[ // pattern pour les noms de colonnes, défini par exemple le nom de table d'une relation
            'en'=>['*_en'],
            'fr'=>['*_fr'],
            'enum'=>['*_id'],
            'set'=>['*_ids']],
        'relation'=>['enum','set'] // détermine les patterns considérés comme relation
    ];


    // dynamique
    protected ?array $mapAllow = ['jsonSerialize','serialize','clone']; // méthodes permises


    // _construct
    // construit un nouvel objet col Schema
    final public function __construct(array $value,?array $attr=null)
    {
        $this->makeAttr($attr);
        $data = static::prepareAttr($value);
        $this->makeOverwrite($data);
    }


    // isUnsigned
    // retourne vrai si la colonne est unsigned
    final public function isUnsigned():?bool
    {
        return ($this->isKindInt())? ($this->get('unsigned') === true):null;
    }


    // acceptsNull
    // retourne vrai si la colonne accepte null
    final public function acceptsNull():bool
    {
        return $this->get('null') === true;
    }


    // isKindInt
    // retourne vrai si le schema est de kind int
    final public function isKindInt():bool
    {
        return $this->kind() === 'int';
    }


    // isKindChar
    // retourne vrai si le schema est de kind char
    final public function isKindChar():bool
    {
        return $this->kind() === 'char';
    }


    // isKindText
    // retourne vrai si le schema est de kind text
    final public function isKindText():bool
    {
        return $this->kind() === 'text';
    }


    // isKindCharOrText
    // retourne vrai si le schema est de kind char ou text
    final public function isKindCharOrText():bool
    {
        return in_array($this->kind(),['char','text'],true);
    }


    // hasDefault
    // retourne vrai si la colonne a une valeur par défaut
    final public function hasDefault():bool
    {
        return $this->default() !== null || $this->acceptsNull();
    }


    // hasNullDefault
    // retourne vrai si la colonne a une valeur par défaut null
    final public function hasNullDefault():bool
    {
        return $this->hasDefault() && $this->default() === null;
    }


    // hasNotEmptyDefault
    // retourne vrai si la colonne a une valeur par défaut qui n'est pas vide
    final public function hasNotEmptyDefault()
    {
        return $this->hasDefault() && !empty($this->default());
    }


    // unique
    // retourne vrai si la valeur de la colonne doit être unique
    final public function unique():bool
    {
        return $this->get('unique') === true;
    }


    // checkStructure
    // envoie une exception si la structure est invalide
    final public function checkStructure(Col $col,?array $check=null):void
    {
        $data = $this->data;

        if(empty($data['type']) || !is_string($data['type']))
        static::throw($col,'invalidType');

        if(!empty($check) && !Base\Arr::hasSlices($check,$data))
        static::throw($col,$col->table(),'checkFailed');
    }


    // type
    // retourne le type du schema de la colonne
    final public function type():string
    {
        return $this->get('type');
    }


    // kind
    // retourne le kind du schema de la colonne
    final public function kind():string
    {
        return $this->get('kind');
    }


    // name
    // retourne le nom de la colonne
    final public function name():string
    {
        return $this->get('name');
    }


    // nameStripPattern
    // retourne le nom de la colonne sans le pattern
    final public function nameStripPattern(?array $pattern=null):?string
    {
        return static::stripPattern($this->name(),$pattern);
    }


    // nameLangCode
    // retourne le code de langue à partir du nom
    final public function nameLangCode():?string
    {
        return static::langCode($this->name());
    }


    // default
    // retourne le default de la colonne
    final public function default()
    {
        return $this->get('default');
    }


    // length
    // retourne la longueur du schema de la colonne
    final public function length():?int
    {
        return $this->get('length');
    }


    // collation
    // retourne la collation du schema de la colonne
    final public function collation():?string
    {
        return $this->get('collate');
    }


    // validate
    // retourne les règles de validation du schéma
    final public function validate():?array
    {
        return $this->get('validate');
    }


    // relation
    // retourne les règles de relation du schema
    final public function relation():?string
    {
        return $this->get('relation');
    }


    // kindDefault
    // retourne le défaut selon le kind
    final public function kindDefault()
    {
        $return = null;
        $kind = $this->kind();

        if($kind === 'int')
        $return = 0;

        elseif($kind === 'char')
        $return = '';

        elseif($kind === 'text')
        $return = '';

        return $return;
    }


    // patternType
    // retourne le pattern type à partir du nom de la colonne
    final public function patternType():?string
    {
        return self::patternTypeFromName($this->name());
    }


    // formTag
    // retourne la tag à utiliser pour toutes les méthodes form
    // la tag peut être dans le tableau ou sinon déduit via le kind
    final public function formTag(?array $attr=null):?string
    {
        $return = null;

        if(!empty($attr) && array_key_exists('tag',$attr) && is_string($attr['tag']))
        $return = $attr['tag'];

        else
        $return = static::kindTag($this->kind());

        return $return;
    }


    // hasPattern
    // retourne vrai si le nom de colonne a un pattern
    final public static function hasPattern($value):bool
    {
        return is_string($value) && static::pattern($value) !== null;
    }


    // isRelation
    // retourne vrai si la colonne a le pattern relation
    final public static function isRelation($value,bool $isPatternType=false):bool
    {
        $return = false;
        $value = ($isPatternType === true)? $value:static::patternTypeFromName($value);

        if(!empty($value) && in_array($value,static::$config['relation'],true))
        $return = true;

        return $return;
    }


    // pattern
    // retourne un tableau avec la clé et la valeur du pattern à partir d'un nom de colonne
    final public static function pattern(string $value):?array
    {
        $return = null;
        $chars = static::$config['patternChars'];

        if(strpos($value,$chars[0]) !== false)
        {
            foreach (static::$config['pattern'] as $key => $pattern)
            {
                if(is_string($key) && is_array($pattern))
                {
                    foreach ($pattern as $v)
                    {
                        if(is_string($v) && Base\Str::isPattern($v,$value,$chars[1]))
                        {
                            $return = [$key,$v];
                            break;
                        }
                    }
                }
            }
        }

        return $return;
    }


    // addPattern
    // permet d'ajouter le pattern à un nom de colonne sans pattern
    // si le pattern ne contient pas le *, ajoute le
    final public static function addPattern(string $pattern,string $value):?string
    {
        $return = null;
        $chars = $char = static::$config['patternChars'];
        $char = $chars[1];

        if(array_key_exists($pattern,static::$config['pattern']))
        {
            $pattern = static::$config['pattern'][$pattern];
            if(is_array($pattern))
            $pattern = current($pattern);
        }

        if(is_string($pattern))
        {
            if(strpos($pattern,$char) === false)
            $pattern = $char.$chars[0].$pattern;

            $return = Base\Str::addPattern($pattern,$value,$char);
        }

        return $return;
    }


    // stripPattern
    // retourne le nom du champ du schema de la colonne sans la partie pattern
    // est utilisé pour déterminer le pattern
    final public static function stripPattern(string $value,?array $pattern=null):?string
    {
        $return = null;
        $pattern = ($pattern === null)? static::pattern($value):$pattern;

        if(!empty($pattern))
        $return = Base\Str::stripPattern($pattern[1],$value,static::$config['patternChars'][1]);

        return $return;
    }


    // patternTypeFromName
    // retourne la clé du pattern déterminer à partir du nom de colonne
    // c'est la clé de pattern
    final public static function patternTypeFromName(string $value):?string
    {
        $return = null;
        $pattern = static::pattern($value);

        if(!empty($pattern))
        $return = $pattern[0];

        return $return;
    }


    // langCode
    // retourne le patternType si c'est une langue
    final public static function langCode(string $value):?string
    {
        $return = null;
        $type = static::patternTypeFromName($value);

        if(is_string($type) && Base\Lang::is($type))
        $return = $type;

        return $return;
    }


    // table
    // retourne le nom de table à partir d'une colonne relation
    // doit match un des pattern relation dans static config
    final public static function table(string $value):?string
    {
        $return = null;
        $pattern = static::pattern($value);

        if(!empty($pattern) && in_array($pattern[0],static::$config['relation'],true))
        $return = Base\Str::stripPattern($pattern[1],$value,static::$config['patternChars'][1]);

        return $return;
    }


    // possible
    // retourne tous les noms possibles que pourrait prendre un nom de colonne si le pattern n'est pas fourni
    // retourne un tableau
    final public static function possible(string $value,bool $currentLang=false):array
    {
        $return = [];
        $char = static::$config['patternChars'][1];

        if(!empty($value))
        {
            foreach (static::$config['pattern'] as $key => $pattern)
            {
                if($currentLang === true && Base\Lang::is($key) && !Base\Lang::isCurrent($key))
                continue;

                if(is_string($key) && is_array($pattern))
                {
                    foreach ($pattern as $v)
                    {
                        if(is_string($v))
                        $return[] = str_replace($char,$value,$v);
                    }
                }
            }
        }

        return $return;
    }


    // prepareAttr
    // prépare un tableau attribut colonne à partir du tableau fourni par sql
    // si default est null, l'attribut null doit être à YES pour être conservé
    // relation est déduit via la méthode pattern
    // peut retourner null si le kind est inconnu
    final public static function prepareAttr(array $value):?array
    {
        $return = null;

        if(array_key_exists('Field',$value) && array_key_exists('Type',$value))
        {
            $return = static::parseType($value['Type']);

            if(is_array($return))
            {
                $return['name'] = $value['Field'];

                if(array_key_exists('Null',$value))
                {
                    if($value['Null'] === 'YES')
                    $return['null'] = true;
                    else
                    $return['null'] = false;
                }

                if(array_key_exists('Default',$value))
                {
                    if(is_numeric($value['Default']))
                    $value['Default'] = Base\Num::cast($value['Default']);

                    if(is_scalar($value['Default']) || ($value['Default'] === null && !empty($return['null'])))
                    $return['default'] = $value['Default'];
                }

                if(array_key_exists('Key',$value))
                {
                    if($value['Key'] === 'PRI' && array_key_exists('Extra',$value) && $value['Extra'] === 'auto_increment')
                    {
                        $return['key'] = 'primary';
                        $return['null'] = null;
                    }

                    elseif($value['Key'] === 'UNI')
                    $return['unique'] = true;
                }

                if(array_key_exists('Collation',$value) && is_string($value['Collation']))
                $return['collate'] = $value['Collation'];

                $return['validate'] = static::parseValidate($return);

                $pattern = static::pattern($value['Field']);
                if(!empty($pattern))
                {
                    $type = $pattern[0];

                    if(static::isRelation($type,true))
                    {
                        $return[$type] = true;
                        $return['relation'] = static::stripPattern($value['Field'],$pattern);
                    }
                }
            }
        }

        return $return;
    }


    // parseType
    // retourne un tableau d'attribut en fonction de la string type fourni par sql
    // peut retourner null si le kind est inconnu
    final public static function parseType(string $value):?array
    {
        $return = null;
        $length = null;
        $segment = Base\Segment::get('()',$value);
        if(is_array($segment) && array_key_exists(0,$segment) && is_numeric($segment[0]))
        $length = (int) $segment[0];

        foreach (Base\Str::wordExplode($value) as $key => $value)
        {
            $value = Base\Str::keepAlpha($value);

            if(strlen($value))
            {
                if($key === 0)
                {
                    $return['type'] = $value;
                    $return['kind'] = null;

                    if(strpos($value,'char') !== false)
                    $return['kind'] = 'char';

                    elseif(strpos($value,'int') !== false)
                    $return['kind'] = 'int';

                    elseif(strpos($value,'float') === 0)
                    $return['kind'] = 'float';

                    elseif(strpos($value,'text') !== false)
                    {
                        $return['kind'] = 'text';
                        $length = static::textLength($value);
                    }
                }

                elseif($value === 'unsigned')
                $return['unsigned'] = true;

                elseif($value === 'zerofill')
                $return['zerofill'] = true;
            }
        }

        if(empty($return['kind']))
        $return = null;

        if(is_array($return) && is_int($length))
        $return['length'] = $length;

        return $return;
    }


    // parseValidate
    // gère les règles de validation selon le kind et le length
    final public static function parseValidate(array $array):array
    {
        $return = [];

        if(!empty($array['kind']))
        {
            if($array['kind'] === 'int')
            {
                $return[] = 'int';

                $int = static::parseValidateInt($array);
                if(!empty($int))
                $return = Base\Arr::merge($return,$int);
            }

            if($array['kind'] === 'float')
            {
                $return[] = 'float';

                $float = static::parseValidateUnsigned($array);
                if(!empty($float))
                $return = Base\Arr::merge($return,$float);
            }

            elseif($array['kind'] === 'char')
            $return[] = 'string';

            elseif($array['kind'] === 'text')
            $return[] = 'string';

            if(array_key_exists('length',$array) && is_int($array['length']))
            $return['maxLength'] = $array['length'];
        }

        return $return;
    }


    // parseValidateInt
    // retourne la valeur maximale d'un int, selon le type et le statut unsigned
    final public static function parseValidateInt(array $array):?array
    {
        $return = null;

        if(array_key_exists('type',$array))
        {
            $return = static::parseValidateUnsigned($array);
            $type = $array['type'];
            $unsigned = $array['unsigned'] ?? false;

            if(array_key_exists($type,static::$config['intMax']) && is_int(static::$config['intMax'][$type]))
            {
                $max = static::$config['intMax'][$type];

                if($unsigned === true)
                $return['<='] = ($max * 2);

                else
                {
                    $return['>='] = -$max;
                    $return['<='] = $max;
                }
            }
        }

        return $return;
    }


    // parseValidateUnsigned
    // gère unsigned pour float et int
    final public static function parseValidateUnsigned(array $array):?array
    {
        $return = null;

        if(array_key_exists('unsigned',$array) && $array['unsigned'] === true)
        $return = ['>='=>0];

        return $return;
    }


    // kindTag
    // retourne le input par défaut selon le kind
    final public static function kindTag(string $kind):?string
    {
        $return = null;

        if($kind === 'int')
        $return = 'inputText';

        elseif($kind === 'float')
        $return = 'inputText';

        elseif($kind === 'char')
        $return = 'inputText';

        elseif($kind === 'text')
        $return = 'textarea';

        return $return;
    }


    // textLength
    // retourne la longueur maximale pour un champ texte
    final public static function textLength(string $value):?int
    {
        return (array_key_exists($value,static::$config['textLength']))? static::$config['textLength'][$value]:null;
    }
}
?>