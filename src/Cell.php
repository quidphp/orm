<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Main;

// cell
// class to represent an existing cell within a row
class Cell extends Main\Root
{
    // trait
    use _colCell;
    use _tableAccess;
    use Main\_attrPermission;


    // config
    public static $config = [];


    // dynamique
    protected $value = []; // contient la valeur de base et de changement de la cellule
    protected $col = null; // conserve l'objet de la colonne, ceci ne crée pas une référence récursive
    protected $row = null; // lien vers la row


    // construct
    // construit l'objet table
    public function __construct($value,Col $col,Row $row)
    {
        $table = $col->table();

        if($table === $row->table())
        {
            $this->setCol($col);
            $this->setRow($row);
            $this->setLink($table,true);
            $this->setInitial($value);
        }

        else
        static::throw('tableForColAndRowAreDifferent');

        return;
    }


    // toString
    // retourne la valeur de la cellule via la méthode output
    // sécuritaire pour sortie html
    public function __toString():string
    {
        return Base\Str::cast($this->value());
    }


    // invoke
    // appel de l'objet, renvoie vers pair
    public function __invoke(...$args)
    {
        return $this->pair(...$args);
    }


    // onCommitted
    // callback après une mise à jour réussie
    // ne retourne rien
    public function onCommitted(bool $insert=false,array $option)
    {
        if($this->hasCommittedCallback('onCommitted'))
        {
            $callback = $this->getCommittedCallback('onCommitted');
            $callback($this,$insert,$option);
        }

        $this->clearCommittedCallback();
        $this->clearException();
        $this->col()->onCommitted($this,$insert,$option);

        return;
    }


    // cast
    // retourne la valeur
    public function _cast()
    {
        return $this->value();
    }


    // is
    // retourne vrai si la valeur remplit la condition de validation
    public function is($value):bool
    {
        return Base\Validate::is($value,$this->value());
    }


    // isNot
    // retourne vrai si la valeur ne remplit pas la condition de validation
    public function isNot($value):bool
    {
        return Base\Validate::isNot($value,$this->value());
    }


    // isEqual
    // retourne vrai si la valeur est égale à l'argument
    public function isEqual($value):bool
    {
        return ($value === $this->value())? true:false;
    }


    // isNotEqual
    // retourne vrai si la valeur n'est pas égale à l'argument
    public function isNotEqual($value):bool
    {
        return ($value !== $this->value())? true:false;
    }


    // isCompare
    // permet de comparer la valeur de cellule avec un symbol et une valeur
    public function isCompare(string $symbol,$value):bool
    {
        return Base\Validate::compare($this->value(),$symbol,$value);
    }


    // isEmpty
    // retourne vrai si la valeur est vide
    public function isEmpty():bool
    {
        return (empty($this->value()))? true:false;
    }


    // isNotEmpty
    // retourne vrai si la valeur n'est pas vide
    public function isNotEmpty():bool
    {
        return (!empty($this->value()))? true:false;
    }


    // isNull
    // retourne vrai si la valeur est null
    public function isNull():bool
    {
        return ($this->value() === null)? true:false;
    }


    // isNotNull
    // retourne vrai si la valeur n'est pas null
    public function isNotNull():bool
    {
        return ($this->value() !== null)? true:false;
    }


    // isPrimary
    // retourne vrai si la colonne de la cellule est la clé primaire
    public function isPrimary():bool
    {
        return ($this->col()->isPrimary())? true:false;
    }


    // acceptsNull
    // retourne vrai si la colonne de la cellule accepte null
    public function acceptsNull():bool
    {
        return $this->col()->acceptsNull();
    }


    // isRequired
    // retourne vrai si la colonne de la cellule est requise
    public function isRequired():bool
    {
        return $this->col()->isRequired();
    }


    // isStillRequired
    // retourne vrai si la cellule est toujours requise, donc la valeur est vide
    // utilise la méthode validate isReallyEmpty pour déterminer si une valeur est vide
    public function isStillRequired():bool
    {
        return $this->col()->isStillRequired($this);
    }


    // isDate
    // retourne vrai si la colonne est de type date
    public function isDate():bool
    {
        return $this->col()->isDate();
    }


    // isRelation
    // retourne vrai si la colonne est de type relation
    public function isRelation():bool
    {
        return $this->col()->isRelation();
    }


    // isEnum
    // retourne vrai si la colonne est de type relation enum
    public function isEnum():bool
    {
        return $this->col()->isEnum();
    }


    // isSet
    // retourne vrai si la colonne est de type relation set
    public function isSet():bool
    {
        return $this->col()->isSet();
    }


    // isMedia
    // retourne vrai si la colonne est de type media
    public function isMedia():bool
    {
        return $this->col()->isMedia();
    }


    // isVisible
    // retourne vrai si la cellule est visible, prend en compte la valeur de la cellule
    public function isVisible(?array $attr=null,?Main\Session $session=null):bool
    {
        return $this->col()->isVisible($this,$attr,$session);
    }


    // isVisibleGeneral
    // retourne vrai si la cellule est visible, ne tient pas compte de la valeur de la cellule
    public function isVisibleGeneral(?array $attr=null):bool
    {
        return $this->col()->isVisibleGeneral($attr);
    }


    // isEditable
    // retourne vrai si la colonne est editable, si non donc pas de modification après insertion
    public function isEditable():bool
    {
        return $this->col()->isEditable();
    }


    // attrPermissionRolesObject
    // retourne les rôles courant
    protected function attrPermissionRolesObject():Main\Roles
    {
        return $this->col()->attrPermissionRolesObject();
    }


    // generalExcerptMin
    // retourne la longueur de l'excerpt pour general
    public function generalExcerptMin():?int
    {
        return $this->col()->generalExcerptMin();
    }


    // group
    // retourne le groupe de la colonne
    public function group():string
    {
        return $this->col()->group();
    }


    // tag
    // retourne la tag de la cellule
    public function tag(?array $attr=null,bool $complex=false):string
    {
        $return = null;

        if($complex === false || !empty($attr['tag']) || $this->isEditable())
        $return = $this->col()->tag($attr,$complex);

        else
        $return = 'div';

        return $return;
    }


    // isFormTag
    // retourne vrai si la tag de la colonne est de type form
    public function isFormTag(?array $attr=null,bool $complex=false):bool
    {
        return Base\Html::isFormTag($this->tag($attr,$complex));
    }


    // rules
    // retourne toutes les règles de validations et required de la cellule
    // n'a pas de lien avec la valeur courante de la cellule
    // possible de retourner les textes si lang est true
    public function rules(bool $lang=false,bool $preValidate=false)
    {
        $return = $this->col()->rules($lang,$preValidate);
        $exception = $this->ruleException($lang);
        if(!empty($exception))
        $return['exception'] = $exception;

        return $return;
    }


    // compare
    // retourne vrai si la valeur de la cellule passe le test de comparaison
    // possible de retourner le texte si lang est true
    public function compare(bool $lang=false)
    {
        return $this->col()->compare($this,$this->row(),$lang);
    }


    // required
    // retourne vrai si la valeur de la cellule passe le test required de la colonne
    // sinon retourne required pour envoyer dans lang
    // possible de retourner le texte si lang est true
    public function required(bool $lang=false)
    {
        return $this->col()->required($this,$lang);
    }


    // unique
    // retourne vrai si la valeur de la cellule passe le test unique de la colonne
    // sinon retourne unique pour envoyer dans lang
    // possible de retourner le texte si lang est true
    public function unique(bool $lang=false)
    {
        return $this->col()->unique($this,$this->rowPrimary(),$lang);
    }


    // editable
    // retourne vrai si la cellule est éditable ou si la valeur n'a pas changé
    // sinon retourne editable pour envoyer dans lang
    // possible de retourner le texte si lang est true
    public function editable(bool $lang=false)
    {
        return ($this->isEditable() || !$this->hasChanged())? true:$this->col()->ruleEditable($lang);
    }


    // validate
    // valide une valeur de cellule contre les règles de validation de la colonne
    // retourne true si ok, sinon retourne un tableau avec les détails sur les validations non passés
    // les règles de validation ne s'applique pas si la valeur est celle par défaut ou null, si null est accepté
    // possible de retourner les textes si lang est true
    // si cache est true, retoure la propriété validate qui garde en cache la dernière validation
    public function validate(bool $lang=false)
    {
        return $this->col()->validate($this,$lang);
    }


    // completeValidation
    // retourne vrai si la valeur de la cellule passe les test srequired et validation de la colonne
    // sinon retourne un tableau avec les détails des tests non passés
    // possible de retourner les textes si lang est true
    public function completeValidation(bool $lang=false)
    {
        $array = [];
        $array['exception'] = $this->exception($lang);
        $array['required'] = $this->required($lang);
        $array['validate'] = $this->validate($lang);
        $array['compare'] = $this->compare($lang);
        $array['unique'] = function() use($lang) {
            return $this->unique($lang);
        };
        $array['editable'] = $this->editable($lang);

        return $this->col()->makeCompleteValidation($array);
    }


    // isColKindInt
    // retourne vrai si la colonne de la cellule est de type int
    public function isColKindInt():bool
    {
        return $this->col()->isKindInt();
    }


    // isColKindChar
    // retourne vrai si la colonne de la cellule est de type char
    public function isColKindChar():bool
    {
        return $this->col()->isKindChar();
    }


    // isColKindText
    // retourne vrai si la colonne de la cellule est de type text
    public function isColKindText():bool
    {
        return $this->col()->isKindText();
    }


    // isWhere
    // retourne vrai si la cellule répond à la validation where
    // similaire à une syntaxe sql mais ne supporte pas les méthodes base/sql whereThree, ni les and, or et paranthèses
    // envoie une exception si une méthode n'est pas supporté
    // utilisé par cells isWhere
    public function isWhere(array $array):bool
    {
        $return = false;
        $db = $this->db();

        foreach ($array as $method => $value)
        {
            $method = (is_numeric($method))? $value:$method;

            if(is_string($method) && $db->syntaxCall('isWhereSymbol',$method))
            $return = $this->isCompare($method,$value);

            elseif(in_array($method,[null,'null'],true))
            $return = $this->isNull();

            elseif($method === 'notNull')
            $return = $this->isNotNull();

            elseif(in_array($method,[false,'empty'],true))
            $return = $this->isEmpty();

            elseif(in_array($method,[true,'notEmpty'],true))
            $return = $this->isNotEmpty();

            else
            static::throw('unsupportedMethod',$method);

            if($return === false)
            break;
        }

        return $return;
    }


    // hasDefault
    // retourne vrai si la colonne de la cellule a une valeur par défaut
    public function hasDefault():bool
    {
        return $this->col()->hasDefault();
    }


    // isLinked
    // retourne vrai si la cellule est lié à l'objet db
    public function isLinked():bool
    {
        return ($this->hasDb() && $this->row()->cells()->in($this))? true:false;
    }


    // alive
    // retourne vrai si la cellule existe dans la base de donnée
    public function alive():bool
    {
        return (!empty($this->db()->selectColumns($this->col(),$this->table(),$this->row())))? true:false;
    }


    // sameRow
    // retourne vrai si l'objet et celui fourni ont la même ligne
    public function sameRow($row):bool
    {
        return ($this->row() === $this->table()->row($row))? true:false;
    }


    // isIncluded
    // retourne vrai si l'inclusion de la  colonne est forcé lors des loop insert ou update
    public function isIncluded(bool $required=true):bool
    {
        return $this->col()->isIncluded('update',$required);
    }


    // hasChanged
    // retourne vrai si la valeur de la cellule a changé depuis son dernier commit
    // retourne vrai si la cellule a un committed callback
    public function hasChanged():bool
    {
        $return = false;

        if($this->hasCommittedCallback('onCommitted') || $this->hasException())
        $return = true;

        elseif(array_key_exists('initial',$this->value) && array_key_exists('change',$this->value))
        {
            if($this->value['change'] !== $this->value['initial'])
            $return = true;
        }

        return $return;
    }


    // setCol
    // change la colonne de la cellule
    // méthode protégé
    protected function setCol(Col $col):void
    {
        $this->col = $col->name();

        return;
    }


    // setRow
    // change la ligne de la cellule
    // méthode protégé
    protected function setRow(Row $row):void
    {
        $this->row = $row->primary();

        return;
    }


    // name
    // retourne le nom de la colonne
    public function name():string
    {
        return $this->col()->name();
    }


    // col
    // retourne l'objet col
    public function col():Col
    {
        return $this->table()->col($this->col);
    }


    // priority
    // retourne le code de priorité de la colonne
    public function priority():int
    {
        return $this->col()->priority();
    }


    // setPriority
    // retourne le code de priorité de la colonne pour le onSet
    public function setPriority():int
    {
        return $this->col()->setPriority();
    }


    // colType
    // retourne le type de la colonne de la cellule
    public function colType()
    {
        return $this->col()->type();
    }


    // colKind
    // retourne le kind de la colonne de la cellule
    public function colKind()
    {
        return $this->col()->kind();
    }


    // colLength
    // retourne la length de la colonne de la cellule, si spécifié
    public function colLength():?int
    {
        return $this->col()->length();
    }


    // colDefault
    // retourne la valeur par défaut de la colonne de la cellule
    // préférable d'appeler hasDefault avant pour être certain qu'il y a un réelement attribut défaut de spécifié
    // retourne aussi int 0 et string vide si pas d'attribut défaut spécifié
    // par défaut retourne null
    public function colDefault()
    {
        return $this->col()->default();
    }


    // colUnique
    // retorune vrai si la colonne doit avoir une valeur unique
    public function colUnique():bool
    {
        return $this->col()->shouldBeUnique();
    }


    // attrRef
    // retourne le tableau des attributs
    // doit retourner une référence
    protected function &attrRef():array
    {
        return $this->col()->attrRef();
    }


    // rowPrimary
    // retourne le id de la clé primaire de ligne
    public function rowPrimary():int
    {
        return $this->row;
    }


    // id
    // retourne le id de la clé primaire de ligne
    public function id():int
    {
        return $this->row;
    }


    // row
    // retourne l'objet row
    public function row():Row
    {
        return $this->table()->checkRow($this->row);
    }


    // label
    // retourne le label de la cellule
    public function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        return $this->col()->label($pattern,$lang,$option);
    }


    // description
    // retourne la description de la cellule
    public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        return $this->col()->description($pattern,$replace,$lang,$option);
    }


    // details
    // retourne les détails de la cellule
    public function details(bool $lang=true):array
    {
        return $this->col()->details($lang);
    }


    // form
    // génère un élément de formulaire pour la cellule
    // possible de merge un tableau attribut sur celui de la cellule
    public function form(?array $attr=null,?array $option=null):string
    {
        return $this->col()->form($this,$attr,$option);
    }


    // formHidden
    // génère un élément de formulaire pour la cellule
    // force que le type du input soit hidden
    public function formHidden(?array $attr=null,?array $option=null):string
    {
        return $this->col()->formHidden($this,$attr,$option);
    }


    // formPlaceholder
    // génère un élément de formulaire pour la cellule
    // comme la méthode form, mais le premier argument est une string pour le placeholder
    public function formPlaceholder(?string $placeholder=null,?array $attr=null,?array $option=null):string
    {
        return $this->col()->formPlaceholder($this,$placeholder,$attr,$option);
    }


    // formWrap
    // génère la celulle dans un formWrap incluant le label et l'élément de formulaire
    // un id commun au label et élément de formulaire sera automatiquement ajouté
    // les formWrap sont définis dans les config de la classe base/html
    public function formWrap(?string $wrap=null,$pattern=null,?array $attr=null,?array $replace=null,?array $option=null):string
    {
        return $this->col()->formWrap($wrap,$pattern,$this,$attr,$replace,$option);
    }


    // formPlaceholderWrap
    // génère la celulle dans un formWrap incluant le label et l'élément de formulaire avec le placeholder
    // un id commun au label et élément de formulaire sera automatiquement ajouté
    // les formWrap sont définis dans les config de la classe base/html
    public function formPlaceholderWrap(?string $wrap=null,$pattern=null,?string $placeholder=null,?array $attr=null,?array $replace=null,?array $option=null):string
    {
        return $this->col()->formPlaceholderWrap($wrap,$pattern,$this,$placeholder,$attr,$replace,$option);
    }


    // hasFormLabelId
    // retourne vrai si l'élément de formulaire de la colonne doit avoir un id dans le label
    public function hasFormLabelId(?array $attr=null,bool $complex=false):bool
    {
        return $this->col()->hasFormLabelId($attr,$complex);
    }


    // com
    // permet d'insérer de la com à partir d'une cellule
    // la com sera inséré dans la row
    public function com($value,?string $type=null,?array $replace=null):self
    {
        $this->col()->com($value,$this,$type,$replace);

        return $this;
    }


    // htmlExcerpt
    // fonction pour faire un résumé sécuritaire
    // removeLineBreaks, removeUnicode, excerpt par length (rtrim et suffix), trim, stripTags, encode (specialChars)
    // mb est true par défaut
    public function htmlExcerpt(?int $length,?array $option=null):string
    {
        return $this->col()->htmlExcerpt($length,$this,$option);
    }


    // htmlOutput
    // output une string html de façon sécuritaire
    // removeLineBreaks, removeUnicode, trim et encode (specialchars)
    // mb est true par défaut
    public function htmlOutput(?array $option=null):string
    {
        return $this->col()->htmlOutput($this,$option);
    }


    // htmlUnicode
    // removeLineBreaks, trim et convert (specialchars)
    // conserve unicode
    public function htmlUnicode(?array $option=null):string
    {
        return $this->col()->htmlUnicode($this,$option);
    }


    // htmlReplace
    // retourne le tableau de remplacement, utilisé par la méthode html
    public function htmlReplace(?array $option=null):array
    {
        return $this->col()->htmlReplace($this,$option);
    }


    // htmlStr
    // retourne une string html avec les valeurs entre paranthèses remplacés
    // remplace name, label, value, get et output
    public function htmlStr(string $return,?array $option=null):string
    {
        return $this->col()->htmlStr($this,$return,$option);
    }


    // value
    // retourne la valeur de la cellule
    // peut être la valeur de changement ou la valeur courante
    public function value()
    {
        $return = null;

        if(array_key_exists('change',$this->value))
        $return = $this->value['change'];

        else
        $return = $this->valueInitial();

        return $return;
    }


    // valueInitial
    // retourne la valuer initiale de la cellule
    // peu importe les changements
    public function valueInitial()
    {
        return $this->value['initial'] ?? null;
    }


    // get
    // retourne la valeur formatté de la cellule
    // si la valeur est scalar, elle est cast avant d'être envoyé dans onGet
    public function get(?array $option=null)
    {
        $return = null;

        $value = $this->value();
        $option = (array) $option;
        $option['cell'] = $this;

        if(is_scalar($value))
        $value = Base\Scalar::cast($value);

        $onGet = $this->col()->onGet($this,$option);

        if($onGet !== $this)
        $value = $onGet;

        $return = $value;

        return $return;
    }


    // export
    // retourne la valeur pour l'exportation
    // doit retourner un tableau
    public function export(?array $option=null):array
    {
        return $this->exportCommon($this->get($option),$option);
    }


    // exportCommon
    // méthode protégé utilisé par la méthode export des différentes classes de cellule
    protected function exportCommon($value,?array $option=null):array
    {
        return $this->col()->onExport('cell',$value,$this,$option);
    }


    // exportOne
    // retourne la valeur pour l'exportation
    // retourne la première valeur du tableau export
    public function exportOne(?array $option=null)
    {
        $return = null;
        $array = $this->export($option);

        if(!empty($array))
        $return = current($array);

        return $return;
    }


    // pair
    // si value est true, retourne le htmlOutput de cellule
    // si value est false, c'est value
    // si value est int, retourne le htmlExcerpt de cellule
    // si value est string c'est une méthode pouvant avoir des arguments
    public function pair($value=null,...$args)
    {
        $return = $this;

        if($value === true)
        $return = $return->htmlOutput();

        elseif($value === false)
        $return = $return->value();

        elseif(is_int($value))
        $return = $return->htmlExcerpt($value);

        elseif(is_string($value))
        $return = $return->$value(...$args);

        return $return;
    }


    // set
    // change la valeur de la cellule
    // passe la valeur dans col/onSet et ensuite col/autoCast
    // lance le callback onCellSet dans col après le changement et force la validation
    // une exception peut être envoyer si preValidate dans option est true et que la nouvelle valeur ne passe pas le test
    // option preValidate avec preValidatePrepare
    public function set($value,?array $option=null):self
    {
        $option = (array) $option;
        $this->clearCommittedCallback();
        $this->clearException();

        $col = $this->col();
        $row = $this->row();

        if(!empty($option['preValidate']) && $option['preValidate'] === true)
        {
            $value = $col->preValidatePrepare($value);
            $preValidate = $col->preValidate($value);
            if(is_array($preValidate))
            static::throw('preValidate',$this->name(),$preValidate);
        }

        $onSet = $col->onSet($value,$row->get(),$this,$option);

        if($onSet !== $this)
        $value = $onSet;

        $value = $col->autoCast($value);

        $this->value['change'] = $value;
        $col->onCellSet($this);

        return $this;
    }


    // setInitial
    // change la valeur initiale de la cellule
    // efface la valeur de changement de la cellule
    // lance le callbacks onCellInit dans col
    // validate est mis à true par défaut lors de setInitial
    public function setInitial($value):self
    {
        $col = $this->col();

        if(array_key_exists('change',$this->value))
        unset($this->value['change']);

        $this->value['initial'] = $value;

        $this->clearCommittedCallback();
        $this->clearException();

        $col->onCellInit($this);

        return $this;
    }


    // setSelf
    // attribute la valeur actuelle à la valeur de changement
    // utiliser dans cells included, permet de lancer les onSet même sans changement de valeur
    public function setSelf(?array $option=null)
    {
        return $this->set($this->value(),$option);
    }


    // reset
    // ramène la valeur de la cellule à sa dernière valeur commit
    // enlève la valeur de changement et remet validate à true
    // lance le callback onCellSet dans col
    public function reset():self
    {
        if(array_key_exists('change',$this->value))
        unset($this->value['change']);

        $this->col()->onCellSet($this);

        return $this;
    }


    // unset
    // ramène la valeur de changement de la cellule à sa valeur par défaut
    // changement seulement si la dernière valeur commit n'est pas défaut, sinon simplement un reset
    public function unset():self
    {
        $initial = $this->valueInitial();
        $default = $this->colDefault();

        if($initial !== $default)
        $this->set($default);

        else
        $this->reset();

        return $this;
    }


    // isUnique
    // retourne vrai si la valeur de la cellule est unique parmis toutes les autres cellules
    public function isUnique():bool
    {
        return $this->col()->isUnique($this,$this->rowPrimary());
    }


    // duplicate
    // retourne un tableau avec les ids de la table dont la colonne ont la même valeur que la cellule
    // null n'est pas une value qui peut avoir des duplicatas
    public function duplicate():?array
    {
        return $this->col()->duplicate($this,$this->rowPrimary());
    }


    // update
    // la cellule est passé dans la méthode updateCallable de la colonne, si existante
    // ceci est appelé avant la mise à jour de la ligne
    // méthode public car appelé dans row
    public function update(?array $option=null):self
    {
        $this->col()->updateCallable($this,(array) $option);

        return $this;
    }


    // delete
    // la cellule est passé dans la méthode delete de la colonne, si existante
    // ceci est appelé avant l'effacement de la ligne
    // méthode public car appelé dans row
    public function delete(?array $option=null):self
    {
        $this->col()->onDelete($this,(array) $option);

        return $this;
    }


    // refresh
    // ramène la valeur de la cellule à celle présentement dans la base de donnée
    // envoie une exception si la ligne n'existe plus
    public function refresh():self
    {
        $table = $this->table();
        $value = $this->db()->selectColumns($this->col(),$table,$this->row());

        if(!empty($value) && is_array($value))
        $this->setInitial(current($value));

        else
        static::throw('rowDoesNotExists');

        return $this;
    }


    // terminate
    // vide un objet cell
    // l'objet devient inutilisable
    public function terminate():self
    {
        $this->value = [];
        $this->col = null;
        $this->row = null;
        $this->db = null;
        $this->table = null;

        return $this;
    }


    // initReplaceMode
    // retourne le tableau des clés à ne pas merger recursivement
    public static function initReplaceMode():array
    {
        return Col::initReplaceMode();
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Cell':null;
    }
}
?>