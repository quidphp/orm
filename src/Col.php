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

// col
// class to represent an existing column within a table
class Col extends Main\Root
{
    // trait
    use _colCell;
    use _tableAccess;
    use Main\_attrPermission;


    // config
    public static $config = [
        'ignore'=>null, // défini si la colonne est ignoré
        'cell'=>null, // détermine la class a utilisé pour la cell, si null laisse le loop de dbclasse faire son oeuvre
        'type'=>null, // type de la colonne
        'kind'=>null, // kind de la colonne
        'group'=>null, // group de la colonne
        'length'=>null, // détermine la longueur de la colonne
        'unsigned'=>null, // détermine si la colonne est un chiffre non signé
        'unique'=>null, // détermine si la valeur de la colonne doit être unique
        'null'=>null, // détermine si la colonne accepte null
        'default'=>null, // valeur par défaut de la colonne, si null doit accepter null (sinon pas de défaut)
        'priority'=>null, // code de priorité de la colonne
        'setPriority'=>5, // priority pour onSet, plus le chiffre est petit plus le onSet est appelé rapidement sur la colonne
        'search'=>null, // la colonne est cherchable
        'searchMinLength'=>null, // longueur minimale pour une recherche, si null prend l'attribut de la table
        'filter'=>false, // la colonne est filtrable
        'filterMethod'=>'or|=', // méthode utilisé lors du findInSet
        'filterEmptyNotEmpty'=>false, // affiche empty not empty dans le filtre
        'order'=>true, // la colonne est ordonnable
        'label'=>null, // chemin label qui remplace le défaut dans lang
        'description'=>null, // chemin description qui remplace le défaut dans lang
        'collate'=>null, // collation de la colonne
        'tag'=>null, // tag à utiliser lors de la créaiton de l'élément formulaire
        'complex'=>null, // défini les tags complexes à utiliser (pour relation et media)
        'attr'=>null, // attribut additionnel à ajouter à l'élément de formulaire
        'include'=>null, // force l'inclusion de la colonne lors d'un loop insert ou delete
        'visible'=>true, // permet d'afficher ou non une colonne
        'editable'=>true, // permet de spécifier si une colonne est readOnly (donc ne peut pas être modifié après l'insertion)
        'visibleGeneral'=>true, // permet d'afficher une colonne dans general, doit être booléean et utiliser la validation de role dans l'attribut visible
        'required'=>false, // détermine si la colonne est requise
        'removeWhiteSpace'=>[ // détermine s'il faut enlever les whiteSpace lors du required ou du autocast
            'required'=>true,
            'cast'=>false],
        'preValidate'=>null, // règle de validation pour la colonne, données au moment du set (en provenance du post par exemple)
        'validate'=>null, // règle de validation pour la colonne, données tel qu'inséré dans le post
        'compare'=>null, // règle de validation gérant la comparaison avec d'autres champs
        'pattern'=>null, // règle de validation spécifique pour la validation pattern en html
        'direction'=>null, // direction par défaut
        'date'=>null, // défini si la colonne est de type date, un format doit y être inscrit
        'relation'=>null, // défini la relation pour la colonne, donne accès à la méthode relation
        'relationSortKey'=>true, // si la relation est sort par clé automatiquement
        'relationIndex'=>true, // si la relation est indexé (donc si les clés sont string transforme en index) -> attention si une valeur contient un caractère non url ou - ça ca causer des problèmes
        'enum'=>null, // défini la relation comme simple (enum)
        'set'=>null, // défini la relation comme multiple (set)
        'media'=>null, // défini le nombre maximal de media que contient la colonne
        'duplicate'=>true, // défini si la cellule doit être dupliqué
        'version'=>null, // paramétrage pour version de média
        'export'=>true, // défini si la colonne est exportable
        'exportSeparator'=>', ', // séparateur si plusieurs valeurs (tableau)
        'exists'=>true, // la colonne doit existé ou une erreur est envoyé, la valeur par défaut est prise ici, pour changer pour une colonne il faut le faire au niveau de la row/table/db
        'check'=>null, // envoie une exception si le tableau d'attribut ne contient pas les slices de check, voir makeAttr
        'onSet'=>null, // callable pour onSet, appelé à chaque set de valeur
        'onGet'=>null, // callable pour onGet, appelé pour avoir la version get d'une valeur
        'onMakeAttr'=>null, // callback sur onMakeAttr lors de la création de la colonne
        'onDuplicate'=>null, // callback sur duplication
        'onExport'=>null, // callback lors de l'exporation
        'onInsert'=>null, // callback sur insertion
        'onUpdate'=>null, // callback sur update
        'onCommit'=>null, // callack sur insertion ou update
        'excerpt'=>75, // longueur de l'excerpt
        'permission'=>[ // tableau des permissions
            '*'=>[
                'nullPlaceholder'=>true]]
    ];


    // replaceMode
    protected static $replaceMode = []; // défini les colonnes à ne pas merger récursivement


    // dynamique
    protected $name = null; // nom de la colonne
    protected $relation = null; // objet de relation de la colonne


    // construct
    // construit l'objet colonne
    final public function __construct(string $name,Table $table,array $attr)
    {
        $this->setName($name);
        $this->setLink($table,true);
        $this->makeAttr($attr);

        return;
    }


    // toString
    // retourne la nom de la colonne
    final public function __toString():string
    {
        return $this->name();
    }


    // invoke
    // appel de l'objet, renvoie vers pair
    final public function __invoke(...$args)
    {
        return $this->pair(...$args);
    }


    // onMakeAttr
    // callback avant de mettre les attributs dans la propriété attr
    protected function onMakeAttr(array $return):array
    {
        if(!empty($return['onMakeAttr']) && static::isCallable($return['onMakeAttr']))
        $return = $return['onMakeAttr']($return);

        if(!empty($return['relation']) && static::isCallable($return['relation']))
        $return['relation'] = $return['relation']($this);

        return $return;
    }


    // onCheckAttr
    // callback dès que les attributs ont été set
    // permet d'envoyer des exceptions si un attribut n'est pas de bon type pour la colonne
    final protected function onCheckAttr()
    {
        return $this;
    }


    // onInsert
    // méthode à ajouter dans une classe qui étend
    // la valeur sera donné en premier argument


    // onCommit
    // méthode à ajouter dans une classe qui étend
    // la valeur sera donné en premier argument
    // peut servir de remplacement à onInsert et onUpdate, mais a moins de priorité


    // onUpdate
    // callback pour onUpdate, une cellule est donné en argument et retourné
    // méthode peut être étendu
    // possible aussi d'utiliser la méthode onCommit, onCommit a moins de priorité que onUpdate
    // si la méthode de col ne retourne pas la cellule, la valeur sera set dans la cellule
    // méthode public car utilisé par cell


    // onSet
    // permet de formater une valeur complexe vers le simple, par exemple lors d'une insertion ou mise à jour
    // cell est fourni en troisième argument si c'est une update
    // méthode publique car utilisé par cell
    protected function onSet($return,array $row,?Cell $cell=null,array $option)
    {
        return $this->attrCallback('onSet',false,$return,$row,$cell,$option);
    }


    // onGet
    // permet de formater une valeur simple vers un type plus complexe, par exemple lors d'un affichage
    // méthode publique car utilisé par cell et table
    protected function onGet($return,array $option)
    {
        return $this->attrCallback('onGet',true,$return,$option);
    }


    // onDuplicate
    // callback sur duplication
    protected function onDuplicate($return,array $row,Cell $cell,array $option)
    {
        return $this->attrCallback('onDuplicate',false,$return,$row,$cell,$option);
    }


    // onExport
    // callback sur exportation
    // doit retourner un tableau
    final protected function onExport(string $type,$value=null,Cell $cell,?array $option=null):array
    {
        $return = [];

        if(in_array($type,['col','cell'],true))
        {
            $separator = $this->getAttr('exportSeparator');

            if($type === 'col')
            $value = $this->label();

            $return = $this->attrCallback('onExport',false,[$value],$type,$cell,(array) $option);

            if(!is_array($return))
            $return = (array) $return;

            foreach ($return as $key => $value)
            {
                $return[$key] = Base\Str::cast($value,$separator);
            }
        }

        else
        static::throw();

        return $return;
    }


    // onCellInit
    // callback lancé lorsqu'une cellule est passé dans setInitial
    // par défaut, renvoie vers onCellSet
    // méthode publique car appelé via cell
    final protected function onCellInit(Cell $cell)
    {
        $this->onCellSet($cell);

        return $this;
    }


    // onCellSet
    // callback lancé lorsqu'une cellule change via la méthode set
    // méthode publique car appelé via cell
    protected function onCellSet(Cell $cell)
    {
        return $this;
    }


    // onDelete
    // callback pour onDelete, une cellule est donné en argument et retourné
    // méthode peut être étendu, ou utilise la config onDelete
    // envoie une exception si l'argument n'est pas une cellule
    // méthode public car utilisé par cell
    protected function onDelete(Cell $return,array $option)
    {
        return;
    }


    // onCommitted
    // callback après une insertion ou mise à jour réussie
    // la nouvelle cellule est donné en argument
    // ne retourne rien
    protected function onCommitted(Cell $cell,bool $insert=false,array $option)
    {
        if($this->hasCommittedCallback('onCommitted'))
        {
            $callback = $this->getCommittedCallback('onCommitted');
            $callback($cell,$insert,$option);
        }

        $this->clearCommittedCallback();
        $this->clearException();

        return;
    }


    // cast
    // retourne la valeur cast
    final public function _cast():string
    {
        return $this->name();
    }


    // isLinked
    // retourne vrai si la colonne est lié à l'objet db
    final public function isLinked():bool
    {
        return ($this->hasDb() && $this->table()->isColLinked($this))? true:false;
    }


    // alive
    // retourne vrai si la colonne existe dans la base de données
    final public function alive():bool
    {
        return ($this->db()->showTableColumnField($this->table(),$this) === $this->name())? true:false;
    }


    // isIgnored
    // retourne vrai si la colonne est ignoré
    final public function isIgnored():bool
    {
        return ($this->getAttr('ignore') === true)? true:false;
    }


    // isPrimary
    // retourne vrai si la colonne est la clé primaire
    final public function isPrimary():bool
    {
        return ($this->getAttr('group') === 'primary')? true:false;
    }


    // isKindInt
    // retourne vrai si la colonne est de kind int
    final public function isKindInt():bool
    {
        return ($this->getAttr('kind') === 'int')? true:false;
    }


    // isKindChar
    // retourne vrai si la colonne est de kind char
    final public function isKindChar():bool
    {
        return ($this->getAttr('kind') === 'char')? true:false;
    }


    // isKindText
    // retourne vrai si la colonne est de kind text
    final public function isKindText():bool
    {
        return ($this->getAttr('kind') === 'text')? true:false;
    }


    // acceptsNull
    // retourne vrai si la colonne accepte null
    final public function acceptsNull():bool
    {
        return ($this->getAttr('null') === true)? true:false;
    }


    // hasAttrInclude
    // retourne vrai si la colonne a l'attribut include a true
    final public function hasAttrInclude():bool
    {
        return ($this->getAttr('include') === true)? true:false;
    }


    // isIncluded
    // retourne vrai si l'inclusion de la colonne doit être forcé lors des loop insert ou delete
    final public function isIncluded(string $type,bool $required=true):bool
    {
        $return = false;

        if(!$this->isPrimary())
        {
            if($this->hasAttrInclude())
            $return = true;

            elseif($required === true && $this->isRequired())
            $return = true;

            elseif($type === 'insert' && $this->hasOnInsert())
            $return = true;

            elseif($type === 'update' && $this->hasOnUpdate())
            $return = true;
        }

        return $return;
    }


    // isRequired
    // retourne vrai si la colonne est requise
    final public function isRequired():bool
    {
        return ($this->getAttr('required') === true)? true:false;
    }


    // isStillRequired
    // retourne vrai si la colonne est toujours requise, donc la valeur fournit en argument est vide
    // utilise la méthode validate isReallyEmpty pour déterminer si une valeur est vide
    final public function isStillRequired($value):bool
    {
        $return = false;

        if($this->isRequired())
        {
            $value = $this->value($value);
            $removeWhiteSpace = $this->shouldRemoveWhiteSpace('required');
            $return = Base\Vari::isReallyEmpty($value,$removeWhiteSpace);
        }

        return $return;
    }


    // shouldRemoveWhiteSpace
    // retourne vrai si la colonne doit retirer les whiteSpace
    // un type doit être fourni
    final public function shouldRemoveWhiteSpace(string $key):bool
    {
        return ($this->getAttr(['removeWhiteSpace',$key]) === true)? true:false;
    }


    // isExportable
    // retourne vrai si la colonne est exportable
    final public function isExportable():bool
    {
        return ($this->getAttr('export') === true && $this->isVisibleGeneral())? true:false;
    }


    // hasCompare
    // retourne vrai si la colonne a des paramètres de comparaison
    final public function hasCompare():bool
    {
        return (!empty($this->getAttr('compare')))? true:false;
    }


    // showDetailsMaxLength
    // retourne vrai s'il faut afficher le max length de la colonne dans les détails
    public function showDetailsMaxLength():bool
    {
        return (is_int($this->length()))? true:false;
    }


    // isDate
    // retourne vrai si la colonne est de type date
    final public function isDate():bool
    {
        return (!empty($this->getAttr('date')))? true:false;
    }


    // isRelation
    // retourne vrai si la colonne est de type relation
    final public function isRelation():bool
    {
        return (!empty($this->getAttr('relation')))? true:false;
    }


    // canRelation
    // retourne vrai si la colonne peut avoir un objet colRelation
    final public function canRelation():bool
    {
        return true;
    }


    // isEnum
    // retourne vrai si la colonne est de type relation enum
    public function isEnum():bool
    {
        return false;
    }


    // isSet
    // retourne vrai si la colonne est de type relation set
    public function isSet():bool
    {
        return false;
    }


    // isMedia
    // retourne vrai si la colonne est de type media
    final public function isMedia():bool
    {
        return (is_int($this->getAttr('media')))? true:false;
    }


    // valueExcerpt
    // créer une version résumé de la valeur si la longueur dépasse l'attribut excerpt
    final public function valueExcerpt($return,?array $option=null)
    {
        $option = Base\Arr::plus(['mb'=>true,'stripTags'=>true],$option);
        $excerpt = $this->getAttr('excerpt');

        if(is_int($excerpt))
        {
            if(is_array($return))
            $return = Base\Arr::valuesExcerpt($excerpt,$return,true,$option);

            elseif(is_string($return))
            $return = Base\Html::excerpt($excerpt,$return,$option);
        }

        return $return;
    }


    // hasDefault
    // retourne vrai si la colonne a une valeur par défaut
    final public function hasDefault():bool
    {
        return (isset($this->attr['default']) || $this->acceptsNull())? true:false;
    }


    // hasNullDefault
    // retourne vrai si la colonne a une valeur par défaut null
    final public function hasNullDefault():bool
    {
        return ($this->hasDefault() && !isset($this->attr['default']))? true:false;
    }


    // hasNullPlaceholder
    // retourne vrai si la colonne a un placeholder NULL, utiliser dans formComplex
    final public function hasNullPlaceholder():bool
    {
        return ($this->acceptsNull() && $this->hasPermission('nullPlaceholder') && $this->table()->hasPermission('nullPlaceholder'))? true:false;
    }


    // hasNotEmptyDefault
    // retourne vrai si la colonne a une valeur par défaut qui n'est pas vide
    final public function hasNotEmptyDefault()
    {
        return ($this->hasDefault() && !empty($this->getAttr('default')))? true:false;
    }


    // hasOnInsert
    // retourne vrai si la colonne a une méthode onInsert ou onCommit
    // ou une callable dans attr onInsert ou onCommit
    final public function hasOnInsert():bool
    {
        $return = false;

        if(method_exists($this,'onInsert') || method_exists($this,'onCommit'))
        $return = true;

        elseif(!empty($this->attr['onInsert']) || !empty($this->attr['onCommit']))
        $return = true;

        return $return;
    }


    // hasOnUpdate
    // retourne vrai si la colonne a une méthode onUpdate ou onCommit
    // ou une callable dans attr onUpdate ou onCommit
    final public function hasOnUpdate():bool
    {
        $return = false;

        if(method_exists($this,'onUpdate') || method_exists($this,'onCommit'))
        $return = true;

        elseif(!empty($this->attr['onUpdate']) || !empty($this->attr['onCommit']))
        $return = true;

        return $return;
    }


    // attrPermissionRolesObject
    // retourne les rôles courant
    final public function attrPermissionRolesObject():Main\Roles
    {
        return $this->db()->roles();
    }


    // value
    // retourne la valeur ou la valeur par défaut si value est true
    final public function value($return=true)
    {
        if($return instanceof Cell)
        $return = $return->value();

        elseif($return === true)
        $return = $this->default();

        return $return;
    }


    // get
    // retourne la valeur après être passé dans onGet
    final public function get($return=true,?array $option=null)
    {
        $return = $this->value($return);
        $option = (array) $option;
        $return = $this->onGet($return,$option);

        return $return;
    }


    // export
    // retourne la valeur pour l'exportation, nécessite une cellule
    // doit retourner un tableau
    final public function export(Cell $cell,?array $option=null):array
    {
        return $this->onExport('col',null,$cell,$option);
    }


    // exportOne
    // retourne la valeur pour l'exportation, nécessite une cellule
    // retourne la première valeur du tableau export
    final public function exportOne(Cell $cell,?array $option=null)
    {
        $return = null;
        $array = $this->export($option);

        if(!empty($array))
        $return = current($array);

        return $return;
    }


    // placeholder
    // retourne le placeholder ou le label, si value n'est pas string
    final public function placeholder($value=null):?string
    {
        return (is_string($value))? $value:$this->label();
    }


    // isSearchable
    // retourne vrai si la colonne est cherchable
    final public function isSearchable():bool
    {
        return ($this->getAttr('search') === true && $this->isVisibleGeneral())? true:false;
    }


    // isSearchTermValid
    // retourne vrai si le terme de la recherche est valide pour la colonne
    // valeur peut être scalar, un tableau à un ou plusieurs niveau
    // si c'est un tableau la longueur totale de l'ensemble des termes est considéré
    final public function isSearchTermValid($value):bool
    {
        $return = false;
        $minLength = $this->searchMinLength();

        if(is_array($value))
        $value = Base\Arrs::implode('',$value);

        if(is_string($value) && strlen($value) >= $minLength)
        $return = true;

        return $return;
    }


    // searchMinLength
    // retourne la longueur de recherche minimale pour la colonne
    // si l'attribut de la colonne est null, prend l'attribut de la table
    final public function searchMinLength():int
    {
        return $this->getAttr('searchMinLength') ?? $this->table()->getAttr('searchMinLength');
    }


    // isOrderable
    // retourne vrai si la colonne est ordonnable
    final public function isOrderable():bool
    {
        return ($this->getAttr('order') === true && $this->isVisibleGeneral())? true:false;
    }


    // isFilterable
    // retourne vrai si la colonne est cherchable
    final public function isFilterable():bool
    {
        return ($this->canRelation() && $this->getAttr('filter',true) === true && $this->isVisibleGeneral())? true:false;
    }


    // isFilterEmptyNotEmpty
    // retourne vrai s'il faut afficher empty not empty dans le filtre
    final public function isFilterEmptyNotEmpty():bool
    {
        return ($this->getAttr('filterEmptyNotEmpty') === true)? true:false;
    }


    // isVisible
    // retourne vrai si la colonne est visible, sinon elle est caché
    // la valeur doit être fourni, gère validate, session et row
    final public function isVisible($value=true,?array $attr=null,?Main\Session $session=null):bool
    {
        $return = false;
        $return = $this->isVisibleCommon($attr);

        if($return === true)
        {
            $visible = $this->getAttr('visible');

            if(is_array($visible) && !empty($visible))
            {
                $cell = ($value instanceof Cell)? $value:null;
                $validateVisible = $visible['validate'] ?? null;
                $sessionVisible = $visible['session'] ?? null;
                $rowVisible = $visible['row'] ?? null;

                if(!empty($validateVisible))
                {
                    $value = $this->value($value);

                    if(!Base\Validate::is($validateVisible,$value))
                    $return = false;
                }

                if(!empty($cell))
                {
                    if($return === true && is_string($sessionVisible) && !empty($session))
                    {
                        if(!$session->$sessionVisible($this,$cell))
                        $return = false;
                    }

                    if($return === true && is_string($rowVisible))
                    {
                        if(!$cell->row()->$rowVisible())
                        $return = false;
                    }
                }
            }
        }

        return $return;
    }


    // isVisibleGeneral
    // retourne vrai si la colonne est visible en general
    final public function isVisibleGeneral(?array $attr=null):bool
    {
        $return = false;
        $visible = $this->getAttr('visibleGeneral');

        if($visible === true)
        $return = $this->isVisibleCommon($attr);

        return $return;
    }


    // isVisibleCommon
    // retourne vrai si la colonne est visible, sinon elle est caché
    // la valeur n'est pas considéré, gère role
    // méthode protégé utilisé par isVisible et isVisibleGeneral
    final protected function isVisibleCommon(?array $attr=null):bool
    {
        $return = false;
        $visible = $this->getAttr('visible');
        $tag = $this->tag($attr);

        if($visible === true)
        $return = true;

        if(Base\Html::isHiddenTag($tag) || $visible === false)
        $return = false;

        elseif(is_array($visible) && !empty($visible))
        $return = $this->roleValidateCommon($visible);

        return $return;
    }


    // roleValidateCommon
    // méthode progété utilisé par isVisibleCommon et readOnly
    final protected function roleValidateCommon(array $value):bool
    {
        $return = true;
        $roleVal = $value['role'] ?? null;

        if(!empty($roleVal))
        {
            $role = $this->db()->role();
            if(!$role->validate($roleVal))
            $return = false;
        }

        return $return;
    }


    // isEditable
    // retourne vrai si la colonne est édaitable, si non donc pas de modification après insertion
    final public function isEditable():bool
    {
        $return = false;
        $editable = $this->getAttr('editable');

        if($editable === true)
        $return = true;

        elseif(is_array($editable) && !empty($editable))
        $return = $this->roleValidateCommon($editable);

        return $return;
    }


    // filterMethod
    // retourne la méthode à utiliser pour filtrer
    final public function filterMethod():string
    {
        return $this->getAttr('filterMethod',true);
    }


    // direction
    // retourne la direction par défaut de la colonne
    final public function direction(bool $lower=false):string
    {
        $return = $this->db()->syntaxCall('getOrderDirection',$this->getAttr('direction'));

        if($lower === true)
        $return = strtolower($return);

        return $return;
    }


    // tag
    // retourne la tag liée à la colonne, tel que paramétré
    // retourne la tag complex si complex est true
    // envoie une erreur si le retour n'est pas string
    public function tag(?array $attr=null,bool $complex=false):string
    {
        $return = null;

        if($attr === null || empty($attr['tag']))
        {
            $key = ($complex === true)? 'complex':'tag';
            $return = $this->getAttr($key);
        }

        if(empty($return))
        {
            $attr = Base\Arr::plus($this->attr(),$attr);
            $return = ColSchema::formTag($attr);
        }

        return $return;
    }


    // isPlainTag
    // retourne vrai si la tag est plain
    final public function isPlainTag(?array $attr=null,bool $complex=false):bool
    {
        return (!$this->isEditable() || $this->tag($attr,$complex) === 'div')? true:false;
    }


    // isFormTag
    // retourne vrai si la tag lié à la colonne en est une de formulaire
    final public function isFormTag(?array $attr=null,bool $complex=false):bool
    {
        return Base\Html::isFormTag($this->tag($attr,$complex));
    }


    // pair
    // si value est string c'est une méthode pouvant avoir des arguments
    final public function pair($value=null,...$args)
    {
        $return = $this;

        if(is_string($value))
        $return = $return->$value(...$args);

        return $return;
    }


    // rulePreValidate
    // retourne les paramètres de pré-validation de la colonne
    // si lang est true, retourne les textes plutôt que les règles de pré-validation
    // note, la pré-validation est facultatif et affecte la valeur au moment du set (par exemple données en provenance de post)
    // validation est la valeur avant l'insertion dans la base de données
    final public function rulePreValidate(bool $lang=false):array
    {
        return $this->rulePreValidateCommon('preValidate',$lang);
    }


    // ruleValidate
    // retourne les paramètres de validation de la colonne
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function ruleValidate(bool $lang=false):array
    {
        return $this->rulePreValidateCommon('validate',$lang);
    }


    // rulePreValidateCommon
    // méthode commune utilisé par rulePreValidate et ruleValidate
    final public function rulePreValidateCommon(string $type,bool $lang=false):array
    {
        $return = $this->getAttr($type);

        if(!is_array($return))
        $return = (array) $return;

        $method = $type.'Closure';
        $closure = $this->$method();
        if(!empty($closure))
        {
            $key = $closure('lang');
            if(!empty($key))
            $return[$key] = $closure;
        }

        if($lang === true && !empty($return))
        {
            $lang = $this->db()->lang();
            $return = $this->rulesWrapClosure('lang',$return);
            $return = $lang->validates($return,null,$this->ruleLangOption());
        }

        return $return;
    }


    // preValidateClosure
    // retourne la closure pour la prévalidation
    public function preValidateClosure():?\Closure
    {
        return null;
    }


    // validateClosure
    // retourne la closure pour la validation
    public function validateClosure():?\Closure
    {
        return null;
    }


    // attrCompare
    // retourne le tableau des attributs pour compare
    // les colonnes de comparaison non existantes ne créent pas d'erreur
    // la clé doit être un symbol de comparaison valide
    final public function attrCompare():array
    {
        $return = [];
        $attr = $this->getAttr('compare');
        $table = $this->table();

        if(!is_array($attr))
        $attr = (array) $attr;

        foreach ($attr as $symbol => $col)
        {
            if(Base\Validate::isCompareSymbol($symbol) && !empty($col) && $table->hasCol($col))
            $return[$symbol] = $table->col($col);
        }

        return $return;
    }


    // ruleCompare
    // retourne les paramètres de comparaison de la colonne
    // si lang est true, retourne les textes plutôt que les règles de comparaison
    final public function ruleCompare(bool $lang=false):array
    {
        $return = $this->attrCompare();

        if(!empty($return) && $lang === true)
        {
            $lang = $this->db()->lang();

            foreach ($return as $symbol => $col)
            {
                $return[$symbol] = $col->label();
            }

            $return = $lang->compares($return,null,$this->ruleLangOption());
        }

        return $return;
    }


    // ruleRequired
    // retourne le paramètre required, si la colonne est requise
    // si lang est true, retourne le texte plutôt que la string
    final public function ruleRequired(bool $lang=false):?string
    {
        $return = null;

        if($this->isRequired())
        {
            if($lang === true)
            {
                $lang = $this->db()->lang();
                $return = $lang->required(true,null,$this->ruleLangOption());
            }

            else
            $return = 'required';
        }

        return $return;
    }


    // ruleUnique
    // retourne le paramètre unique, si la colonne doit être unique
    // si lang est true, retourne le texte plutôt que la string
    final public function ruleUnique(bool $lang=false,$notIn=null):?string
    {
        $return = null;

        if($this->shouldBeUnique())
        {
            if($lang === true)
            {
                $lang = $this->db()->lang();
                $notIn = ($notIn === null)? true:$notIn;
                $return = $lang->unique($notIn,null,$this->ruleLangOption());
            }

            else
            $return = 'unique';
        }

        return $return;
    }


    // ruleEditable
    // retourne editable, si la colonne n'est pas éditable (après insertion)
    // si lang est true, retourne le texte plutôt que la string
    final public function ruleEditable(bool $lang=false):?string
    {
        $return = null;

        if($this->isEditable() === false)
        {
            if($lang === true)
            {
                $lang = $this->db()->lang();
                $return = $lang->editable(true,null,$this->ruleLangOption());
            }

            else
            $return = 'editable';
        }

        return $return;
    }


    // ruleMaxLength
    // retourne la règle pour de validation pour la longueur maximale de la colonne
    // si lang est true, retourne le texte plutôt que le tableau
    final public function ruleMaxLength(bool $lang=false)
    {
        $return = null;
        $length = $this->length();

        if(is_int($length))
        {
            $return = ['maxLength'=>$length];

            if($lang === true)
            {
                $lang = $this->db()->lang();
                $return = $lang->validate($return,null,$this->ruleLangOption());
            }
        }

        return $return;
    }


    // rules
    // retourne les paramètres requis, de pré-validation et de validation de la colonne
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function rules(bool $lang=false,bool $preValidate=false):array
    {
        $return = [];
        $rules = [];

        $rules['exception'] = $this->ruleException($lang);
        $rules['required'] = $this->ruleRequired($lang);
        $rules['unique'] = $this->ruleUnique($lang);
        $rules['editable'] = $this->ruleEditable($lang);

        if($preValidate === true)
        $rules['preValidate'] = $this->rulePreValidate($lang);

        $rules['validate'] = $this->ruleValidate($lang);
        $rules['compare'] = $this->ruleCompare($lang);
        $rules['pattern'] = $this->rulePattern($lang);

        foreach ($rules as $key => $value)
        {
            if(!empty($value))
            $return[$key] = $value;
        }

        return $return;
    }


    // rulePattern
    // retourne le nom du premier pattern trouvé, si existant
    // priorité est pattern, prevalidate et finalement validate
    // peut retourner null, string ou array
    final public function rulePattern(bool $lang=false)
    {
        $return = null;

        foreach (['pattern','preValidate','validate'] as $v)
        {
            $pattern = null;

            if($v === 'pattern')
            {
                $pattern = $this->getAttr('pattern');
                if($pattern === false)
                break;
            }

            elseif($v === 'preValidate')
            $pattern = $this->rulePreValidate();

            elseif($v === 'validate')
            $pattern = $this->ruleValidate();

            if(!empty($pattern))
            {
                $return = Base\Validate::patternKey($pattern);
                if(!empty($return))
                break;
            }
        }

        if($lang === true && !empty($return) && is_string($return))
        {
            $lang = $this->db()->lang();
            $return = $lang->validate([$return],null,$this->ruleLangOption());
        }

        return $return;
    }


    // rulesWrapClosure
    // enrobe les closures dans une autre closure pour y spécifier le contexte
    final protected function rulesWrapClosure(string $context,array $return,$value=null):array
    {
        foreach ($return as $k => $v)
        {
            if($v instanceof \Closure)
            {
                $return[$k] = function() use($context,$v,$value) {
                    return $v($context,$value);
                };
            }
        }

        return $return;
    }


    // ruleLangOption
    // retourne le tableau pour chemin alternatif dans lang
    // est utilisé par validate, compare, required et unique
    final public function ruleLangOption():array
    {
        return ['path'=>['tables',$this->tableName(),$this->name()]];
    }


    // pattern
    // retourne la valeur du premier pattern trouvé, si existant
    final public function pattern():?string
    {
        return Base\Validate::pattern($this->rulePattern());
    }


    // preValidatePrepare
    // méthode pouvant être étendu qui prépare la valeur avant de prévalider
    public function preValidatePrepare($return)
    {
        return $return;
    }


    // preValidate
    // valide une valeur de colonne contre les règles de pré-validation
    // dans tous les cas, retourne true si vide -> le test required se fait plus tard
    // retourne true si ok, sinon retourne un tableau avec les détails sur les pré-validations non passés
    // les règles de pré-validation ne s'applique pas si la valeur est celle par défaut ou null, si null est accepté
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function preValidate($value,bool $lang=false)
    {
        return $this->triggerValidate($value,$this->rulePreValidate(),true,$lang);
    }


    // validate
    // valide une valeur de colonne contre les règles de validation
    // retourne true si ok, sinon retourne un tableau avec les détails sur les validations non passés
    // les règles de validation ne s'applique pas si la valeur est celle par défaut ou null, si null est accepté
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function validate($value,bool $lang=false)
    {
        return $this->triggerValidate($value,$this->ruleValidate(),false,$lang);
    }


    // triggerValidate
    // utilisé par preValidate et validate comme c'est le même code
    final protected function triggerValidate($value,array $rules,bool $ignoreEmpty=false,bool $lang=false)
    {
        $return = true;
        $acceptsNull = $this->acceptsNull();

        if(!empty($rules))
        {
            $value = $this->value($value);
            $isNull = ($value === null && $acceptsNull === true);
            $isDefault = ($this->hasDefault() && $value === $this->default());
            $isEmpty = ($ignoreEmpty === true && Base\Vari::isReallyEmpty($value));

            if($isNull === false && $isDefault === false && $isEmpty === false)
            {
                $rules = $this->rulesWrapClosure('validate',$rules,$value);
                $return = Base\Validate::isAndCom($rules,$value);

                if($lang === true && is_array($return))
                {
                    $lang = $this->db()->lang();
                    $return = $lang->validates($return,null,$this->ruleLangOption());
                }
            }
        }

        return $return;
    }


    // compare
    // fait les test de comparaison sur la colonne
    // le tableau row, contenant toutes les données de la ligne doit être fourni
    // si lang est true, retourne le message d'erreur
    final public function compare($value,$row=[],bool $lang=false)
    {
        $return = true;

        if($value instanceof Cell)
        $value = $value->value();

        if($row instanceof Row)
        $row = $row->value();

        if($this->hasCompare() && is_array($row) && !empty($row) && !Base\Vari::isReallyEmpty($value))
        {
            $error = [];
            $attr = $this->attrCompare();

            foreach ($attr as $symbol => $col)
            {
                $name = $col->name();
                if(array_key_exists($name,$row) && !Base\Vari::isReallyEmpty($row[$name]))
                {
                    if(!Base\Validate::compare($value,$symbol,$row[$name]))
                    $error[$symbol] = ($lang === true)? $col->label():$col;
                }
            }

            if(!empty($error))
            {
                $return = $error;

                if($lang === true)
                {
                    $lang = $this->db()->lang();
                    $return = $lang->compares($return,null,$this->ruleLangOption());
                }
            }
        }

        return $return;
    }


    // isUnique
    // retourne vrai si la valeur est unique dans la colonne
    final public function isUnique($value,$notIn=null):bool
    {
        return (empty($this->duplicate($value,$notIn)))? true:false;
    }


    // unique
    // fait le test unique sur la colonne, la valeur doit être fournie en premier argument
    // si lang est true, retourne le texte indiquant que le champ doit être unique
    final public function unique($value,$notIn=null,bool $lang=false)
    {
        $return = true;

        if($this->shouldBeUnique())
        {
            $duplicate = $this->duplicate($value,$notIn);

            if(!empty($duplicate))
            $return = $this->ruleUnique($lang,$duplicate);
        }

        return $return;
    }


    // duplicate
    // retourne un tableau avec les ids de ligne qui ont une valeur dupliqués pour la colonne
    // si la colonne accepte null, null n'est pas considéré comme une valeur dupliqué
    // possible de mettre une valeur notIn
    final public function duplicate($value,$notIn=null):array
    {
        $return = [];

        if($value instanceof Cell)
        $value = $value->value();

        if(!($value === null && $this->acceptsNull()))
        {
            $table = $this->table();
            $primary = $table->primary();
            $db = $table->db();
            $where = [[$this,'=',$value]];

            if(!empty($notIn))
            $where[] = [$primary,'notIn',$notIn];

            $return = $db->selectColumns($primary,$table,$where);
        }

        return $return;
    }


    // distinctMethod
    // méthode protégé utilisé par distinct et distinctCount
    final protected function distinctMethod(string $method,$notEmpty=true,$where=null,$order=null)
    {
        $return = null;
        $table = $this->table();
        $primary = $table->primary();
        $db = $table->db();
        $name = $this->name();
        $where = (array) $where;

        if($notEmpty === true)
        $where[] = [$name,true];

        if($order === null)
        $order = [$primary=>'asc'];

        $return = $db->$method($this,$table,$where,$order);

        return $return;
    }


    // distinct
    // retourne un tableau des valeurs distincts pour la colonne
    // par défaut ne retourne pas les valeurs distinctes vides
    final public function distinct($notEmpty=true,$where=null,$order=null):array
    {
        return $this->distinctMethod('selectDistinct',$notEmpty,$where,$order);
    }


    // distinctCount
    // retourne le nombre de valeur distinctes trouvés dans la colonne
    final public function distinctCount($notEmpty=true,$where=null,$order=null):int
    {
        return $this->distinctMethod('selectCountDistinct',$notEmpty,$where,$order);
    }


    // replace
    // permet de faire un remplacement sur toutes les valeurs d'une colonne
    // si where est true, met primary >= 1
    final public function replace($from,$to,$where=null):?int
    {
        $db = $this->db();
        $table = $this->table();
        $set = [];
        $set[] = [$this,'replace',$from,$to];

        if(empty($where))
        $where = $table->whereAll();

        $return = $db->update($table,$set,$where);

        return $return;
    }


    // required
    // fait le test required sur la colonne, la valeur doit être fournie en premier argument
    // retourne true si le test passe, sinon retourne la string required
    // si lang est true, retourne le texte indiquant que le champ est requis
    final public function required($value,bool $lang=false)
    {
        return ($this->isStillRequired($value))? $this->ruleRequired($lang):true;
    }


    // completeValidation
    // fait les test required, validate et unique sur la colonne
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function completeValidation($value,$row=[],bool $lang=false)
    {
        $array = [];
        $array['exception'] = $this->exception($lang);
        $array['required'] = $this->required($value,$lang);
        $array['validate'] = $this->validate($value,$lang);
        $array['compare'] = $this->compare($value,$row,$lang);
        $array['unique'] = function() use($value,$lang) {
            return $this->unique($value,null,$lang);
        };
        $array['editable'] = true;

        return $this->makeCompleteValidation($array);
    }


    // makeCompleteValidation
    // méthode utilisé par com et cell pour générer le retour de completeValidation
    // unique doit être une closure, sera seulement appelé s'il n'y a pas d'autres erreurs
    final public function makeCompleteValidation(array $array)
    {
        $return = true;
        $error = [];

        if(!empty($array['exception']) && is_string($array['exception']))
        $error[] = $array['exception'];

        if(empty($error))
        {
            if(!empty($array['required']) && is_string($array['required']))
            $error[] = $array['required'];

            if(!empty($array['editable']) && is_string($array['editable']))
            $error[] = $array['editable'];

            if(!empty($array['validate']) && is_array($array['validate']))
            $error = Base\Arr::append($error,$array['validate']);

            if(!empty($array['compare']) && is_array($array['compare']))
            $error = Base\Arr::append($error,$array['compare']);

            if(empty($error) && !empty($array['unique']) && $array['unique'] instanceof \Closure)
            {
                $unique = $array['unique']();
                if(is_string($unique))
                $error[] = $unique;
            }
        }

        if(!empty($error))
        $return = $error;

        return $return;
    }


    // setName
    // change le nom de la colonne après validation
    final protected function setName(string $name):self
    {
        if(ColSchema::is($name))
        $this->name = $name;

        else
        static::throw($name,'needsLowerCaseFirstChar','invalidChars');

        return $this;
    }


    // name
    // retourne le nom de la colonne
    final public function name():string
    {
        return $this->name;
    }


    // nameStripPattern
    // retourne le nom de la colonne sans le pattern
    final public function nameStripPattern(?array $pattern=null):?string
    {
        return ColSchema::stripPattern($this->name(),$pattern);
    }


    // langCode
    // retourne le code de langue à partir du nom
    final public function langCode():?string
    {
        return ColSchema::langCode($this->name());
    }


    // makeAttr
    // merge le tableau de propriété dbAttr avec le tableau static config et le tableau config de row
    // les clés avec valeurs null dans static config ne sont pas conservés
    // les règles de validation de config s'append sur celles de dbAttr, ne remplace pas
    // lance onMakeAttr avant d'écrire dans la propriété
    // le merge est unidimensionnel, c'est à dire que les valeurs tableaux sont écrasés et non pas merge
    // si l'attribut contient la clé du type, ceci aura priorité sur tout le reste (dernier merge)
    final protected function makeAttr($dbAttr,bool $config=true):void
    {
        $table = $this->table();
        $db = $table->db();
        $name = $this->name();
        $defaultAttr = $db->colAttr($name);
        $tableAttr = $table->colAttr($name);
        $baseAttr = [];
        $callable = static::getInitCallable();

        if($config === true)
        {
            foreach (static::$config as $key => $value)
            {
                if($value !== null || !array_key_exists($key,$dbAttr))
                $baseAttr[$key] = $value;
            }
        }

        $attr = $callable(static::class,$dbAttr,$baseAttr,$defaultAttr,$tableAttr);
        $attr['group'] = ColSchema::group($attr,true);

        $attr = $this->onMakeAttr($attr);

        $this->checkAttr($attr);
        $this->attr = $attr;
        $this->onCheckAttr();

        return;
    }


    // checkAttr
    // fait un check sur les attributs, vérifie type, kind, group, priority et check
    final protected function checkAttr(array $attr):self
    {
        if(empty($attr['type']) || !is_string($attr['type']))
        static::throw($this,'invalidType');

        if(empty($attr['group']) || !is_string($attr['group']))
        static::throw($this,'invalidGroup');

        if(empty($attr['priority']) || !is_int($attr['priority']))
        static::throw($this,'invalidPriority');

        if(!empty($attr['check']) && is_array($attr['check']) && !Base\Arr::hasSlices($attr['check'],$attr))
        static::throw($this,$this->table(),'checkFailed');

        return $this;
    }


    // attrCallback
    // appele la callable lié à un attribut
    // la valeur est passé dans value avant l'envoie à la méthode (donc la cellule est transformé)
    final protected function attrCallback(string $key,bool $value=false,$return=null,...$args)
    {
        $call = $this->attrParseCallable($key);

        if(!empty($call))
        {
            if(!empty($call['args']))
            $args = Base\Arr::append($call['args'],$args);

            if($value === true)
            $return = $this->value($return);

            $return = $call['callable']($return,...$args);
        }

        return $return;
    }


    // attrParseCallable
    // retourne un tableau pour une callable dans les attributs
    // retourne null ou un tableau à deux arguments: callable et args
    final public function attrParseCallable(string $key):?array
    {
        $return = null;
        $attr = $this->getAttr($key);

        if(!empty($attr) && is_array($attr))
        {
            if(static::isCallable($attr))
            $return = ['callable'=>$attr,'args'=>[]];

            elseif(static::isCallable(current($attr)))
            {
                $callable = current($attr);
                unset($attr[key($attr)]);
                $args = array_values($attr);
                $return = ['callable'=>$callable,'args'=>$args];
            }
        }

        return $return;
    }


    // priority
    // retourne le code de priorité de la colonne
    final public function priority():int
    {
        return $this->getAttr('priority');
    }


    // setPriority
    // retourne le code de priorité de la colonne pour onSet
    final public function setPriority():int
    {
        return $this->getAttr('setPriority');
    }


    // type
    // retourne le type de la colonne
    final public function type():string
    {
        return $this->getAttr('type');
    }


    // kind
    // retourne le kind de la colonne
    final public function kind():string
    {
        return $this->getAttr('kind');
    }


    // group
    // retourne le groupe, utiliser pour lier à la bonne classe de la cellule
    final public function group():string
    {
        return $this->getAttr('group');
    }


    // length
    // retourne la length de la colonne, si spécifié
    final public function length():?int
    {
        return $this->getAttr('length');
    }


    // unsigned
    // retourne bool si la colonne est non signé
    // retourne null si la colonne n'est pas de kind int
    final public function unsigned():?bool
    {
        return ($this->isKindInt())? (($this->getAttr('unsigned') === true)? true:false):null;
    }


    // shouldBeUnique
    // retourne vrai si la valeur de la colonne doit être unique
    final public function shouldBeUnique():bool
    {
        return ($this->getAttr('unique') === true)? true:false;
    }


    // default
    // retourne la valeur par défaut de la colonne
    // préférable d'appeler hasDefault avant pour être certain qu'il y a réelement un attribut défaut de spécifié
    // retourne aussi int 0 et string vide si pas d'attribut défaut spécifié et le type est int, char ou text
    // si retour est string et contient un /, passe dans lang
    // par défaut retourne null
    final public function default()
    {
        $return = null;

        if($this->hasDefault())
        {
            $return = $this->attr['default'];

            if(is_string($return) && strpos($return,'/') !== false)
            $return = $this->db()->lang()->safe($return) ?? $return;

            elseif(static::isCallable($return))
            $return = $return($this);
        }

        elseif(empty($this->attr['null']))
        $return = $this->kindDefault();

        return $return;
    }


    // kindDefault
    // retourne la valeur par défaut selon le kind
    final public function kindDefault()
    {
        return ColSchema::kindDefault($this->kind());
    }


    // autoCast
    // gère le bon type a donné au valeur vide
    // gère le cast des valeurs après le callback onSet
    // pour numérique, transforme la virgule en comma
    // le cast de string serialize les objets, lit les resources et json_encode les array
    final public function autoCast($return)
    {
        $kind = $this->kind();
        $removeWhiteSpace = $this->shouldRemoveWhiteSpace('cast');

        if(is_array($return) || is_object($return))
        $return = Base\Obj::cast($return);

        if(Base\Vari::isReallyEmpty($return,$removeWhiteSpace))
        {
            if($this->acceptsNull())
            $return = null;

            else
            $return = ColSchema::kindDefault($kind);
        }

        else
        {
            if(is_array($return) && $this->isRelation())
            $return = Base\Set::str($return);

            $str = Base\Str::cast($return);

            if($removeWhiteSpace === true)
            $str = Base\Str::removeWhiteSpace($str);

            if($kind === 'int')
            $return = Base\Integer::cast($return) ?? $str;

            elseif($kind === 'float')
            $return = Base\Floating::cast($return) ?? $str;

            elseif(in_array($kind,['char','text'],true))
            $return = $str;
        }

        return $return;
    }


    // insertCallable
    // retourne la valeur après l'avoir passé dans la méthode onInsert ou attr ou onCommit, si existante
    // si pas de méthode, retourne la valeur tel quelle
    final public function insertCallable($return,array $row,array $option)
    {
        if(method_exists($this,'onInsert'))
        $return = $this->onInsert($return,$row,$option);

        elseif(method_exists($this,'onCommit'))
        $return = $this->onCommit($return,$row,null,$option);

        elseif(!empty($this->attr['onInsert']))
        $return = $this->attr['onInsert']($this,$return,$row,null,$option);

        elseif(!empty($this->attr['onCommit']))
        $return = $this->attr['onCommit']($this,$return,$row,null,$option);

        return $return;
    }


    // updateCallable
    // retourne la cellule après l'avoir passé dans la méthode ou attr onUpdate ou onCommit, si existante
    // si pas de méthode, retourne la cellule tel quel
    final public function updateCallable(Cell $return,array $option):Cell
    {
        $value = $return;

        if(method_exists($this,'onUpdate'))
        $value = $this->onUpdate($return,$option);

        elseif(method_exists($this,'onCommit'))
        $value = $this->onCommit($return->value(),$return->row()->get(),$return,$option);

        elseif(!empty($this->attr['onUpdate']))
        $value = $this->attr['onUpdate']($this,$return,$option);

        elseif(!empty($this->attr['onCommit']))
        $value = $this->attr['onCommit']($this,$return->value(),$return->row()->get(),$return,$option);

        if($value !== $return)
        $return->set($value);

        return $return;
    }


    // insert
    // clearCommittedCalllback
    // passe la valeur dans onInsert, si existant
    // ensuite onSet et autoCast
    final public function insert($return,array $row,?array $option=null)
    {
        $option = (array) $option;
        $this->clearCommittedCallback();
        $this->clearException();
        $return = $this->value($return);
        $row = Base\Obj::cast($row);
        $return = $this->onSet($return,$row,null,$option);
        $return = $this->insertCallable($return,$row,$option);
        $return = $this->autoCast($return);

        return $return;
    }


    // patternType
    // retourne le pattern type à partir du nom de la colonne
    final public function patternType():?string
    {
        return ColSchema::patternType($this->name());
    }


    // label
    // retourne le label de la colonne
    // pattern permet de remplacer le label dans une string contenant d'autres caractères
    final public function label($pattern=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = $this->db()->lang();
        $path = $this->getAttr('label');
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,null,$lang,$option);
        else
        $return = $obj->colLabel($this->name(),$this->tableName(),$lang,$option);

        return $return;
    }


    // description
    // retourne la description de la colonne
    // pattern permet de remplacer le label dans une string contenant d'autres caractères
    public function description($pattern=null,?array $replace=null,?string $lang=null,?array $option=null):?string
    {
        $return = null;
        $obj = $this->db()->lang();
        $path = $this->getAttr('description');
        $option = Base\Arr::plus($option,['pattern'=>$pattern]);

        if(!empty($path))
        $return = $obj->same($path,$replace,$lang,$option);
        else
        $return = $obj->colDescription($this->name(),$this->tableName(),$replace,$lang,$option);

        return $return;
    }


    // details
    // retourne un tableau de détail en lien avec la colonne
    // les détails sont pour la plupart généré automatiquement
    public function details(bool $lang=true):array
    {
        $return = [];
        $details = $this->makeDetails();

        if($this->isRequired())
        {
            $required = $this->ruleRequired($lang);
            if(!empty($required))
            $return[] = $required;
        }

        if($this->shouldBeUnique())
        {
            $unique = $this->ruleUnique($lang);
            if(!empty($unique))
            $return[] = $unique;
        }

        if($this->showDetailsMaxLength())
        {
            $maxLength = $this->ruleMaxLength($lang);
            if(!empty($maxLength))
            $return[] = $maxLength;
        }

        if(!empty($details))
        $return = Base\Arr::append($return,$details);

        return $return;
    }


    // makeDetails
    // méthode à étendre pour ajouter des détails en lien avec la colonne
    final public function makeDetails():array
    {
        return [];
    }


    // collation
    // retourne la collation de la colonne
    final public function collation():?string
    {
        return $this->getAttr('collate');
    }


    // formAttr
    // retourne les attributs de formulaires avec un tableau d'attributs en argument facultatif
    // si la colonne est requise, ajoute la propriété required dans les attributs
    // les attributs fournis en arguments ont priorités sur les valeurs provenant de la db comme name et length
    final public function formAttr(?array $attr=null,bool $complex=false):array
    {
        $return = (array) $this->getAttr('attr');

        if(is_array($attr))
        {
            $data = Base\Attr::data($attr);
            if(!empty($data))
            $attr = Base\Arr::replace($attr,$data);

            $return = Base\Arr::replace($return,$attr);
        }

        if(array_key_exists('tag',$return))
        unset($return['tag']);

        $tag = $this->tag($attr,$complex);

        if($this->isFormTag($attr,$complex))
        {
            $isTextTag = Base\Html::isTextTag($tag);
            $isInputMethod = Base\Html::isInputMethod($tag);
            $isHiddenTag = Base\Html::isHiddenTag($tag);

            if(!array_key_exists('data-required',$return) && $this->isRequired())
            $return['data-required'] = true;

            if($isHiddenTag === false)
            {
                if($isTextTag === true || $isInputMethod === true)
                {
                    $pattern = $this->rulePattern();
                    if(!array_key_exists('data-pattern',$return) && !empty($pattern))
                    $return['data-pattern'] = $pattern;
                }

                if($isInputMethod === true)
                {
                    $length = $this->length();
                    if(!array_key_exists('maxlength',$return) && is_int($length))
                    $return['maxlength'] = $length;
                }
            }

            if(!array_key_exists('name',$return))
            $return['name'] = $this->name();
        }

        return $return;
    }


    // form
    // génère un élément de formulaire pour la colonne
    // si value est true, utilise la valeur par défaut, sinon met la valeur
    // possible de merge un tableau attribut sur celui de la colonne
    final public function form($value=true,?array $attr=null,?array $option=null):string
    {
        $return = '';
        $value = $this->value($value);
        $tag = $this->tag($attr);
        $attr = $this->formAttr($attr);
        $return = Base\Html::$tag($value,$attr,$option);

        return $return;
    }


    // formHidden
    // génère un élément de formulaire pour la colonne
    // force que le type de formulaire soit hidden
    // différence: valeur par défaut est null, et non pas la valeur par défaut (true)
    final public function formHidden($value=null,?array $attr=null,?array $option=null):string
    {
        return $this->form($value,Base\Arr::plus($attr,['tag'=>'inputHidden']),$option);
    }


    // formPlaceholder
    // génère un élément de formulaire pour la colonne
    // comme la méthode form, mais le deuxième argument est une string pour le placeholder
    // si placeholder est null, utilise label
    final public function formPlaceholder($value=true,?string $placeholder=null,?array $attr=null,?array $option=null):string
    {
        return $this->form($value,Base\Arr::plus($attr,['placeholder'=>$this->placeholder($placeholder)]),$option);
    }


    // emptyPlaceholder
    // retourne le placeholder à utiliser si value est vide ('' ou null)
    final public function emptyPlaceholder($value):?string
    {
        $return = null;

        if(is_object($value))
        $value = Base\Obj::cast($value);

        if(in_array($value,['',null,[]],true))
        {
            $return = '-';

            if($value === null && $this->hasNullPlaceholder())
            $return = 'NULL';
        }

        return $return;
    }


    // formWrap
    // génère la colonne dans un formWrap incluant le label et l'élément de formulaire
    final public function formWrap(?string $wrap=null,$pattern=null,$value=true,?array $attr=null,?array $replace=null,?array $option=null):string
    {
        return $this->makeFormWrap('form',false,$wrap,$pattern,$value,$attr,$replace,$option);
    }


    // formPlaceholderWrap
    // génère la colonne dans un formWrap incluant le label et l'élément de formulaire avec le placeholder
    // un id commun au label et élément de formulaire sera automatiquement ajouté
    // les formWrap sont définis dans les config de la classe base/html
    // si placeholder est null, utilise label
    final public function formPlaceholderWrap(?string $wrap=null,$pattern=null,$value=true,?string $placeholder=null,?array $attr=null,?array $replace=null,?array $option=null):string
    {
        return $this->formWrap($wrap,$pattern,$value,Base\Arr::plus($attr,['placeholder'=>$this->placeholder($placeholder)]),$replace,$option);
    }


    // makeFormWrap
    // méthode protégé utilisé par formWrap et formComplexWrap
    // un id commun au label et élément de formulaire sera automatiquement ajouté
    // les formWrap sont définis dans les config de la classe base/html
    final protected function makeFormWrap(string $method,bool $complex=false,?string $wrap=null,$pattern=null,$value=true,?array $attr=null,?array $replace=null,?array $option=null):string
    {
        $return = '';
        $label = $this->label($pattern);
        $id = null;

        if($this->hasFormLabelId($attr,$complex))
        {
            $id = Base\Attr::randomId($attr['name'] ?? $this->name());
            $attr = Base\Arr::plus($attr,['id'=>$id]);
        }

        if(!empty($attr['placeholder']) && $attr['placeholder'] === true)
        $attr['placeholder'] = $this->placeholder($attr['placeholder']);

        $form = $this->$method($value,$attr,$option);

        if(is_string($form))
        $return = Base\Html::formWrapStr($label,$form,$wrap,$replace,$id);

        return $return;
    }


    // hasFormLabelId
    // retourne vrai si l'élément de formulaire de la colonne doit avoir un id dans le label
    public function hasFormLabelId(?array $attr=null,bool $complex=false):bool
    {
        $return = false;
        $tag = $this->tag($attr,$complex);

        if(Base\Html::isTextTag($tag))
        $return = true;

        return $return;
    }


    // com
    // permet d'insérer de la com à partir d'une colonne
    // la com sera inséré dans la row ou la table dépendamment si l'argument cell est founri
    final public function com($value,?Cell $cell=null,?string $type=null,?array $replace=null):self
    {
        $value = [$this->name()=>$value];

        if(is_array($value) && !empty($value))
        {
            if(!empty($cell))
            {
                $row = $cell->row();
                $row->updateCom($value,$type,null,$replace);
            }

            else
            {
                $table = $this->table();
                $table->insertCom($value,$type,null,$replace);
            }
        }

        return $this;
    }


    // setCommittedCallback
    // set le callback à appeler après un commit, insert ou update
    final public function setCommittedCallback(string $key,callable $callback,?Cell $cell=null):void
    {
        if(!empty($cell))
        $cell->setCommittedCallback($key,$callback);

        else
        $this->callback[$key] = $callback;

        return;
    }


    // htmlExcerpt
    // fonction pour faire un résumé sécuritaire
    // removeLineBreaks, removeUnicode, excerpt par length (rtrim et suffix), trim, stripTags, encode (specialChars)
    // mb est true par défaut
    final public function htmlExcerpt(?int $length,$value=true,?array $option=null):string
    {
        $return = '';
        $value = Base\Str::cast($this->value($value));
        $return = Base\Html::excerpt($length,$value,$option);

        return $return;
    }


    // htmlOutput
    // output une string html de façon sécuritaire
    // removeLineBreaks, removeUnicode, trim et encode (specialchars)
    // mb est true par défaut
    final public function htmlOutput($value=true,?array $option=null):string
    {
        $return = '';
        $value = Base\Str::cast($this->value($value));
        $return = Base\Html::output($value,$option);

        return $return;
    }


    // htmlUnicode
    // removeLineBreaks, trim et convert (specialchars)
    // conserve unicode
    final public function htmlUnicode($value=true,?array $option=null):string
    {
        $return = '';
        $value = Base\Str::cast($this->value($value));
        $return = Base\Html::unicode($value,$option);

        return $return;
    }


    // htmlReplace
    // retourne le tableau de remplacement, utilisé par la méthode html
    final public function htmlReplace($value=true,?array $option=null):array
    {
        $return = [];
        $option = (array) $option;
        $option['cell'] = ($value instanceof Cell)? $value:null;
        $value = $this->value($value);

        $return['name'] = $this->name();
        $return['label'] = $this->label();
        $return['tableLabel'] = $this->table()->label();
        $return['value'] = $value;
        $return['get'] = $this->onGet($value,$option);
        $return['output'] = $this->htmlOutput($value);

        if($this->isRelation())
        $return['get'] = $this->relation()->get($value,false,true,$option);

        return $return;
    }


    // htmlStr
    // retourne une string html avec les valeurs entre paranthèses remplacés
    // remplace name, label, value, get et output
    // la valeur doit être fournie en argument
    final public function htmlStr($value=true,string $return,?array $option=null):string
    {
        $replace = $this->htmlReplace($value,$option);
        $replace = Base\Obj::cast($replace);
        $replace = Base\Arr::keysWrap('%','%',$replace);
        $return = Base\Str::replace($replace,$return);

        return $return;
    }


    // relation
    // retourne l'instance de colRelation
    // une exception sera envoyé si la colonne n'est pas une relation
    final public function relation():ColRelation
    {
        $return = $this->relation;

        if(empty($return))
        $return = $this->relation = ColRelation::newOverload($this);

        return $return;
    }


    // primaries
    // retourne les clés primaries qui réponde à la requête
    final public function primaries($where,...$args):array
    {
        return $this->db()->selectPrimaries($this->table(),[$this->name()=>$where],...$args);
    }


    // countPrimaries
    // retourne le count des clés primaries qui réponde à la requête
    final public function countPrimaries($where,...$args):?int
    {
        return $this->db()->selectCount($this->table(),[$this->name()=>$where],...$args);
    }


    // cell
    // retourne la classe de la cell si existante
    final public function cell():?string
    {
        return $this->getAttr('cell');
    }


    // alter
    // alter la colonne
    final public function alter():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // drop
    // drop la colonne
    final public function drop():self
    {
        static::throw('notAllowed');

        return $this;
    }


    // isFilterEmptyNotEmptyValue
    // retourne vrai si la valeur est pour un filtre empty/not empty
    final public static function isFilterEmptyNotEmptyValue($value):bool
    {
        return (in_array($value,['00','01'],true))? true:false;
    }


    // initReplaceMode
    // retourne le tableau des clés à ne pas merger recursivement
    final public static function initReplaceMode():array
    {
        return static::$replaceMode ?? [];
    }


    // getOverloadKeyPrepend
    // retourne le prepend de la clé à utiliser pour le tableau overload
    final public static function getOverloadKeyPrepend():?string
    {
        return (static::class !== self::class && !Base\Fqcn::sameName(static::class,self::class))? 'Col':null;
    }
}

// init
Col::__init();
?>