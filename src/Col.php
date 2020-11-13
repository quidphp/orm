<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package <https://quidphp.com>
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Orm;
use Quid\Base;
use Quid\Base\Html;
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
    protected static array $config = [
        'ignore'=>null, // défini si la colonne est ignoré
        'group'=>null, // spécifie le groupe de la colonne
        'cell'=>null, // détermine la class a utilisé pour la cell, si null laisse le loop de dbclasse faire son oeuvre
        'length'=>null, // longueur de la colonne
        'unique'=>null, // détermine si la valeur de la colonne doit être unique
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
        'tag'=>null, // tag à utiliser lors de la créaiton de l'élément formulaire
        'complex'=>null, // défini les tags complexes à utiliser (pour relation et media)
        'attr'=>null, // attribut additionnel à ajouter à l'élément de formulaire
        'include'=>null, // force l'inclusion de la colonne lors d'un loop insert ou delete
        'visible'=>true, // permet d'afficher ou non une colonne
        'editable'=>true, // permet de spécifier si une colonne est readOnly (donc ne peut pas être modifié après l'insertion)
        'visibleGeneral'=>true, // permet d'afficher une colonne dans general, doit être booléean et utiliser la validation de role dans l'attribut visible
        'required'=>false, // détermine si la colonne est requise
        'removeWhiteSpaceRequired'=>true, // détermine s'il faut enlever les whiteSpace lors du required
        'removeWhiteSpaceCast'=>false, // détermine s'il faut enlever les whiteSpace lors du autocast
        'preValidate'=>null, // règle de validation pour la colonne, données au moment du set (en provenance du post par exemple)
        'validate'=>null, // règle de validation pour la colonne, données tel qu'inséré dans le post
        'compare'=>null, // règle de validation gérant la comparaison avec d'autres champs
        'pattern'=>null, // règle de validation spécifique pour la validation pattern en html
        'direction'=>null, // direction par défaut
        'duplicate'=>true, // défini si la cellule doit être dupliqué
        'export'=>true, // défini si la colonne est exportable
        'exportSeparator'=>', ', // séparateur si plusieurs valeurs (tableau)
        'exists'=>true, // la colonne doit existé ou une erreur est envoyé, la valeur par défaut est prise ici, pour changer pour une colonne il faut le faire au niveau de la row/table/db
        'keyboard'=>null, // défini le keyboard à utiliser pour le champ (inputmode)
        'relationSortKey'=>true, // si la relation est sort par clé automatiquement
        'relationIndex'=>true, // si la relation est indexé (donc si les clés sont string transforme en index) -> attention si une valeur contient un caractère non url ou - ça ca causer des problèmes
        'check'=>null, // envoie une exception si le tableau d'attribut ne contient pas les slices de check, voir makeAttr
        'onAttr'=>null, // callback lors de la création de la colonne, génération des attributs
        'onGet'=>null, // callable pour onGet, appelé pour avoir la version get d'une valeur
        'onSet'=>null, // callable pour onSet, appelé à chaque set de valeur
        'onPreValidate'=>null, // callback pour la prévalidation, doit retourner une closure
        'onValidate'=>null, // callback pour la validation, doit retourner une closure, envoie des arguments supplémentaires comme pour onSet
        'onDuplicate'=>null, // callback sur duplication
        'onDelete'=>null, // callback sur suppression de la ligne
        'onExport'=>null, // callback lors de l'exporation
        'onInsert'=>null, // callback sur insertion
        'onUpdate'=>null, // callback sur update
        'onCommit'=>null, // callack sur insertion ou update
        'permission'=>[ // tableau des permissions
            '*'=>[
                'nullPlaceholder'=>true]]
    ];


    // replaceMode
    protected static array $replaceMode = []; // défini les colonnes à ne pas merger récursivement


    // dynamique
    protected string $name; // nom de la colonne
    protected ?ColSchema $schema = null; // objet du schema de la colonne
    protected ?ColRelation $relation = null; // objet de relation de la colonne


    // construct
    // construit l'objet colonne
    final public function __construct(string $name,Table $table,ColSchema $colSchema,int $priority)
    {
        $this->setName($name);
        $this->setSchema($colSchema);
        $this->setLink($table,true);
        $this->makeAttr($colSchema);
        $this->makePriority($priority);
    }


    // toString
    // retourne la nom de la colonne
    final public function __toString():string
    {
        return $this->name();
    }


    // invoke
    // appel de l'objet, renvoie vers getAttr
    final public function __invoke(...$args)
    {
        return $this->getAttr(...$args);
    }


    // onInsert
    // méthode à ajouter dans une classe qui étend
    // la valeur sera donné en premier argument


    // onUpdate
    // callback pour onUpdate, une cellule est donné en argument et retourné
    // méthode peut être étendu
    // possible aussi d'utiliser la méthode onCommit, onCommit a moins de priorité que onUpdate
    // si la méthode de col ne retourne pas la cellule, la valeur sera set dans la cellule


    // onCommit
    // méthode à ajouter dans une classe qui étend
    // la valeur sera donné en premier argument
    // peut servir de remplacement à onInsert et onUpdate, mais a moins de priorité


    // onAttr
    // callback avant de mettre les attributs dans la propriété attr
    protected function onAttr(array $return):array
    {
        return $return;
    }


    // onGet
    // permet de formater une valeur simple vers un type plus complexe, par exemple lors d'un affichage
    protected function onGet($return,?Cell $cell=null,array $option)
    {
        return $return;
    }


    // onSet
    // permet de formater une valeur complexe vers le simple, par exemple lors d'une insertion ou mise à jour
    // cell est fourni en troisième argument si c'est une update
    protected function onSet($return,?Cell $cell=null,array $row,array $option)
    {
        return $return;
    }


    // onPreValidate
    // callback qui permet de retourner la closure pour la prévalidation
    public function onPreValidate():?\Closure
    {
        return null;
    }


    // onValidate
    // callback qui permet de retourner la closure pour la validation
    public function onValidate():?\Closure
    {
        return null;
    }


    // onDuplicate
    // callback sur duplication
    protected function onDuplicate(Cell $return,array $option)
    {
        return $return;
    }


    // onDelete
    // callback pour onDelete, une cellule est donné en argument
    // envoie une exception si l'argument n'est pas une cellule
    protected function onDelete(Cell $return,array $option)
    {
        return $return;
    }


    // onExport
    // callback sur exportation
    // doit retourner un tableau
    final protected function onExport($value=null,Cell $cell,string $type,?array $option=null):array
    {
        $return = [$value];

        if(!in_array($type,['col','cell'],true))
        static::throw();

        $separator = $this->getAttr('exportSeparator');

        if($type === 'col')
        $value = $this->label();

        $return = [$value];

        $callable = $this->getAttr('onExport');
        if(!empty($callable))
        $return = $callable($return,$type,$cell,$option);

        if(!is_array($return))
        $return = (array) $return;

        return Base\Arr::map($return,fn($value) => Base\Str::cast($value,$separator));
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
    }


    // attrOrMethodCall
    // utilisé pour appeler en priorité la callable dans attr
    // sinon appele la méthode de la classe
    protected function attrOrMethodCall(string $type,...$args)
    {
        $return = null;
        $callable = $this->getAttr($type);

        if(!empty($callable))
        $return = $callable($this,...$args);

        elseif($this->hasMethod($type))
        $return = $this->$type(...$args);

        return $return;
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
        return $this->hasDb() && $this->table()->isColLinked($this);
    }


    // alive
    // retourne vrai si la colonne existe dans la base de données
    final public function alive():bool
    {
        return $this->db()->showTableColumnField($this->table(),$this) === $this->name();
    }


    // isIgnored
    // retourne vrai si la colonne est ignoré
    final public function isIgnored():bool
    {
        return $this->getAttr('ignore') === true;
    }


    // isPrimary
    // retourne vrai si la colonne est la clé primaire
    public function isPrimary():bool
    {
        return false;
    }


    // hasAttrInclude
    // retourne vrai si la colonne a l'attribut include a true
    final public function hasAttrInclude():bool
    {
        return $this->getAttr('include') === true;
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
        return $this->getAttr('required') === true;
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
        return $this->getAttr('removeWhiteSpace'.ucfirst($key)) === true;
    }


    // isExportable
    // retourne vrai si la colonne est exportable
    final public function isExportable():bool
    {
        return $this->getAttr('export') === true && $this->isVisibleGeneral();
    }


    // hasCompare
    // retourne vrai si la colonne a des paramètres de comparaison
    final public function hasCompare():bool
    {
        return !empty($this->getAttr('compare'));
    }


    // isDate
    // retourne vrai si la colonne est de type date
    public function isDate():bool
    {
        return false;
    }


    // isRelation
    // retourne vrai si la colonne est de type relation
    public function isRelation():bool
    {
        return false;
    }


    // canRelation
    // retourne vrai si la colonne peut avoir un objet colRelation
    public function canRelation():bool
    {
        return true;
    }


    // isMedia
    // retourne vrai si la colonne est de type media
    public function isMedia():bool
    {
        return false;
    }


    // hasDefault
    // retourne vrai si la colonne a une valeur par défaut
    final public function hasDefault():bool
    {
        return $this->getAttr('default') !== null || $this->schema()->hasDefault();
    }


    // hasNullDefault
    // retourne vrai si la colonne a une valeur par défaut null
    final public function hasNullDefault():bool
    {
        return $this->schema()->hasNullDefault() || ($this->hasDefault() && $this->getAttr('default') === null);
    }


    // hasNotEmptyDefault
    // retourne vrai si la colonne a une valeur par défaut qui n'est pas vide
    final public function hasNotEmptyDefault()
    {
        return $this->schema()->hasNotEmptyDefault() || ($this->hasDefault() && !empty($this->getAttr('default')));
    }


    // hasNullPlaceholder
    // retourne vrai si la colonne a un placeholder NULL, utiliser dans formComplex
    final public function hasNullPlaceholder():bool
    {
        return $this->schema()->acceptsNull() && $this->hasPermission('nullPlaceholder') && $this->table()->hasPermission('nullPlaceholder');
    }


    // hasOnInsert
    // retourne vrai si la colonne a une méthode onInsert ou onCommit
    // ou une callable dans attr onInsert ou onCommit
    final public function hasOnInsert():bool
    {
        $return = false;

        if($this->hasMethod('onInsert') || $this->hasMethod('onCommit'))
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

        if($this->hasMethod('onUpdate') || $this->hasMethod('onCommit'))
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
    // attention, cette méthode retourne le défaut si c'est true -> ceci cause confusion avec une colonne ou on voudrait que true se transforme en 1, j'ai ajouté un fix dans la méthode insert
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
        $return = $this->attrOrMethodCall('onGet',$return,null,$option);

        return $return;
    }


    // export
    // retourne la valeur pour l'exportation, nécessite une cellule
    // doit retourner un tableau
    final public function export(Cell $cell,?array $option=null):array
    {
        return $this->onExport(null,$cell,'col',$option);
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
        $return = null;

        if($value === true || $value === null)
        $value = $this->label();

        if(is_string($value))
        $return = $value;

        return $return;
    }


    // isSearchable
    // retourne vrai si la colonne est cherchable
    final public function isSearchable():bool
    {
        return $this->getAttr('search') === true && $this->isVisibleGeneral();
    }


    // isSearchTermValid
    // retourne vrai si le terme de la recherche est valide pour la colonne
    // valeur peut être scalar, un tableau à un ou plusieurs niveau
    // si c'est un tableau la longueur totale de l'ensemble des termes est considéré
    final public function isSearchTermValid($value):bool
    {
        $minLength = $this->searchMinLength();

        if(is_array($value))
        $value = Base\Arrs::implode('',$value);

        return is_string($value) && strlen($value) >= $minLength;
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
        return $this->getAttr('order') === true && $this->isVisibleGeneral();
    }


    // isFilterable
    // retourne vrai si la colonne est cherchable
    final public function isFilterable():bool
    {
        return $this->canRelation() && $this->getAttr('filter',true) === true && $this->isVisibleGeneral();
    }


    // isFilterEmptyNotEmpty
    // retourne vrai s'il faut afficher empty not empty dans le filtre
    final public function isFilterEmptyNotEmpty():bool
    {
        return $this->getAttr('filterEmptyNotEmpty') === true;
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

        if(Html::isHiddenTag($tag) || $visible === false)
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
            $return = $this->schema()->formTag($attr);
        }

        return $return;
    }


    // isPlainTag
    // retourne vrai si la tag est plain
    final public function isPlainTag(?array $attr=null,bool $complex=false):bool
    {
        return !$this->isEditable() || $this->tag($attr,$complex) === 'div';
    }


    // isFormTag
    // retourne vrai si la tag lié à la colonne en est une de formulaire
    final public function isFormTag(?array $attr=null,bool $complex=false):bool
    {
        return Html::isFormTag($this->tag($attr,$complex));
    }


    // rulePreValidate
    // retourne les paramètres de pré-validation de la colonne
    // si lang est true, retourne les textes plutôt que les règles de pré-validation
    // note, la pré-validation est facultatif et affecte la valeur au moment du set (par exemple données en provenance de post)
    // validation est la valeur avant l'insertion dans la base de données
    final public function rulePreValidate(bool $lang=false):array
    {
        return $this->ruleValidateCommon($this->getAttr('preValidate'),'onPreValidate',$lang);
    }


    // ruleSchemaValidate
    // retourne les paramètres de validation du schéma de la colonne
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function ruleSchemaValidate(bool $lang=false):array
    {
        return $this->ruleValidateCommon($this->schema()->validate(),null,$lang);
    }


    // ruleValidate
    // retourne les paramètres de validation de la colonne
    // si lang est true, retourne les textes plutôt que les règles de validation
    final public function ruleValidate(bool $lang=false):array
    {
        return $this->ruleValidateCommon($this->getAttr('validate'),'onValidate',$lang);
    }


    // ruleValidateCombined
    // retourne un tableau qui combine les règles de validation du schéma et la colonne
    final public function ruleValidateCombined(bool $lang=false):array
    {
        return Base\Arr::merge($this->ruleSchemaValidate($lang),$this->ruleValidate($lang));
    }


    // ruleValidateCommon
    // méthode commune utilisé par rulePreValidate, ruleSchemaValidate et ruleValidate
    final protected function ruleValidateCommon($return,?string $method,bool $lang=false):array
    {
        if(!is_array($return))
        $return = (array) $return;

        if(!empty($method))
        {
            $closure = $this->attrOrMethodCall($method);
            if(!empty($closure))
            {
                $key = $closure('lang',null,$this);
                if(!empty($key))
                $return[$key] = $closure;
            }
        }

        if($lang === true && !empty($return))
        {
            $lang = $this->db()->lang();
            $return = $this->rulesWrapClosure('lang',$return);
            $return = $lang->validates($return,null,$this->ruleLangOption());
        }

        return $return;
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
                $notIn ??= true;
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

        $rules['schemaValidate'] = $this->ruleSchemaValidate($lang);
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
            $pattern = $this->ruleValidateCombined();

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
    // injecte aussi l'objet col et d'autres arguments
    final protected function rulesWrapClosure(string $context,array $return,$value=null,?Cell $cell=null,?array $row=null):array
    {
        foreach ($return as $k => $v)
        {
            if($v instanceof \Closure)
            $return[$k] = fn() => $v($context,$value,$this,$cell,$row);
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
    final public function validate($value,bool $lang=false,?Cell $cell=null,?array $row=null)
    {
        return $this->triggerValidate($value,$this->ruleValidateCombined(),false,$lang,$cell,$row);
    }


    // triggerValidate
    // utilisé par preValidate et validate comme c'est le même code
    final protected function triggerValidate($value,array $rules,bool $ignoreEmpty=false,bool $lang=false,?Cell $cell=null,?array $row=null)
    {
        $return = true;
        $acceptsNull = $this->schema()->acceptsNull();

        if(!empty($rules))
        {
            $value = $this->value($value);
            $isNull = ($value === null && $acceptsNull === true);
            $isDefault = ($this->hasDefault() && $value === $this->default());
            $isEmpty = ($ignoreEmpty === true && Base\Vari::isReallyEmpty($value));

            if($isNull === false && $isDefault === false && $isEmpty === false)
            {
                $rules = $this->rulesWrapClosure('validate',$rules,$value,$cell,$row);
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
    final public function compare($value,bool $lang=false,?Cell $cell=null,?array $row=null)
    {
        $return = true;

        if($value instanceof Cell)
        $value = $value->value();

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
        return empty($this->duplicate($value,$notIn));
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

        if(!($value === null && $this->schema()->acceptsNull()))
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
    final public function completeValidation($value,bool $lang=false,?Cell $cell=null,?array $row=null)
    {
        $array = [];
        $array['exception'] = $this->exception($lang);
        $array['required'] = $this->required($value,$lang);
        $array['validate'] = $this->validate($value,$lang,$cell,$row);
        $array['compare'] = $this->compare($value,$lang,$cell,$row);
        $array['unique'] = fn() => $this->unique($value,null,$lang);
        $array['editable'] = true;

        return $this->makeCompleteValidation($array);
    }


    // makeCompleteValidation
    // méthode utilisé par com et cell pour générer le retour de completeValidation
    // required sera seulement appelé s'il n'y pas d'autres erreurs
    // unique doit être une closure, sera seulement appelé s'il n'y a pas d'autres erreurs
    final public function makeCompleteValidation(array $array)
    {
        $return = true;
        $error = [];

        if(!empty($array['exception']) && is_string($array['exception']))
        $error[] = $array['exception'];

        if(empty($error))
        {
            if(!empty($array['editable']) && is_string($array['editable']))
            $error[] = $array['editable'];

            if(!empty($array['validate']) && is_array($array['validate']))
            $error = Base\Arr::merge($error,$array['validate']);

            if(!empty($array['compare']) && is_array($array['compare']))
            $error = Base\Arr::merge($error,$array['compare']);

            if(empty($error) && !empty($array['required']) && is_string($array['required']))
            $error[] = $array['required'];

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
        if(Base\Validate::isCol($name))
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


    // setSchema
    // permet de lier un objet colSchema à la colonne
    final protected function setSchema(ColSchema $schema):void
    {
        $this->schema = $schema;
    }


    // schema
    // retourne l'objet colSchema
    final public function schema():ColSchema
    {
        return $this->schema;
    }


    // makeAttr
    // merge le tableau de propriété dbAttr avec le tableau static config et le tableau config de row
    // les clés avec valeurs null dans static config ne sont pas conservés
    // les règles de validation de config s'append sur celles de dbAttr, ne remplace pas
    // lance onAttr avant d'écrire dans la propriété
    // le merge est unidimensionnel, c'est à dire que les valeurs tableaux sont écrasés et non pas merge
    // si l'attribut contient la clé du type, ceci aura priorité sur tout le reste (dernier merge)
    final protected function makeAttr($colSchema,bool $config=true):void
    {
        $table = $this->table();
        $db = $table->db();
        $name = $this->name();
        $defaultAttr = $db->colAttr($name);
        $tableAttr = $table->colAttr($name);
        $baseAttr = [];
        $callable = static::getInitCallable();

        if($config === true)
        $baseAttr = static::$config;

        $attr = $callable(static::class,$baseAttr,$defaultAttr,$tableAttr);
        $attr = $this->prepareAttr($attr,$colSchema);
        $this->attr = $attr;
        $this->attr = $this->attrOrMethodCall('onAttr',$attr);
        $this->checkAttr();
    }


    // prepareAttr
    // permet de faire des ajustements aux attribus après le merge, mais avant l'écriture dans la propriété
    protected function prepareAttr(array $return,ColSchema $schema):array
    {
        // default
        if(!isset($return['default']))
        $return['default'] = $schema->default();

        // unique
        $unique = $schema->unique();
        if(isset($return['unique']) && is_bool($return['unique']))
        {
            if($return['unique'] === false && $unique === true)
            static::throw($this,'shouldBeUnique');
        }

        else
        $return['unique'] = $unique;

        // length
        $length = $schema->length();
        if(is_int($length))
        {
            if(!isset($return['length']))
            $return['length'] = $length;

            elseif($return['length'] > $length)
            static::throw($this,'invalidLength',$return['length'],'biggerThan',$length);
        }

        // search
        if(!isset($return['search']) && $schema->isKindCharOrText())
        $return['search'] = true;

        return $return;
    }


    // checkAttr
    // fait un check sur les attributs
    final protected function checkAttr():self
    {
        $attr = $this->attr;

        $check = $attr['check'] ?? null;
        $this->schema()->checkStructure($this,$check);

        return $this;
    }


    // makePriority
    // sauvegarde la priorité de la colonne dans les attributs
    final protected function makePriority(int $priority):void
    {
        $attrPriority = $this->getAttr('priority');
        $priority = (is_int($attrPriority))? $attrPriority:$priority;
        $this->setAttr('priority',$priority);
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


    // length
    // retourne la longueur de la colonne
    final public function length():?int
    {
        return $this->getAttr('length');
    }


    // group
    // retourne le groupe de la colonne, à spécifier par des classes qui étendent
    final public function group():?string
    {
        return $this->getAttr('group');
    }


    // shouldBeUnique
    // retourne vrai si la valeur de la colonne doit être unique
    final public function shouldBeUnique():bool
    {
        return $this->getAttr('unique') === true;
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
        $return = $this->schema()->kindDefault();

        return $return;
    }


    // autoCast
    // gère le bon type a donné au valeur vide
    // gère le cast des valeurs après le callback onSet
    // pour numérique, transforme la virgule en comma
    // le cast de string serialize les objets, lit les resources et json_encode les array
    final public function autoCast($return)
    {
        $schema = $this->schema();
        $kind = $schema->kind();
        $removeWhiteSpace = $this->shouldRemoveWhiteSpace('cast');

        if(is_array($return) || is_object($return))
        $return = Base\Obj::cast($return);

        if(Base\Vari::isReallyEmpty($return,$removeWhiteSpace))
        {
            if($schema->acceptsNull())
            $return = null;

            else
            $return = $schema->kindDefault();
        }

        else
        {
            if($this->isRelation())
            $return = $this->autoCastRelation($return);

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


    // autoCastRelation
    // utilisé pour faire l'auto cast d'une relation
    final protected function autoCastRelation($return)
    {
        if(is_array($return))
        $return = Base\Set::str($return);

        elseif($return === false && !$this->relation()->exists(0))
        $return = null;

        return $return;
    }


    // insertCallable
    // retourne la valeur après l'avoir passé dans la méthode onInsert ou attr ou onCommit, si existante
    // si pas de méthode, retourne la valeur tel quelle
    final protected function insertCallable($return,array $row,array $option)
    {
        if(!empty($this->attr['onInsert']) || $this->hasMethod('onInsert'))
        $return = $this->attrOrMethodCall('onInsert',$return,$row,$option);

        elseif(!empty($this->attr['onCommit']) || $this->hasMethod('onCommit'))
        $return = $this->attrOrMethodCall('onCommit',$return,null,$row,$option);

        return $return;
    }


    // updateCallable
    // retourne la cellule après l'avoir passé dans la méthode ou attr onUpdate ou onCommit, si existante
    // si pas de méthode, retourne la cellule tel quel
    final protected function updateCallable(Cell $return,array $option):Cell
    {
        $value = $return;

        if(!empty($this->attr['onUpdate']) || $this->hasMethod('onUpdate'))
        $value = $this->attrOrMethodCall('onUpdate',$return,$option);

        elseif(!empty($this->attr['onCommit']) || $this->hasMethod('onCommit'))
        {
            $row = $return->row()->get();
            $value = $this->attrOrMethodCall('onCommit',$return->value(),$return,$row,$option);
        }

        if($value !== $return)
        $return->set($value);

        return $return;
    }


    // insert
    // clearCommittedCalllback
    // passe la valeur dans onInsert, si existant, ensuite onSet et autoCast
    // ajouter un fix pour l'insertion d'une colonne relation ou l'on voudrait que true se tranforme en 1
    // normalement à l'insertion true se transforme en défaut
    final public function insert($return,array $row,?array $option=null)
    {
        $option = Base\Arr::plus(['valueDefault'=>false],$option);
        $this->clearCommittedCallback();
        $this->clearException();

        // fix pour pouvoir insérer une colonne relation avec un bool qui se transforme en 0/1
        // valueDefault a été rajouté car sinon une colonne included avec valeur true (default) devenait 1, ce qui causait une erreur hors de relation
        if($option['valueDefault'] === false && is_bool($return) && $this->isRelation() && ($this->hasNullDefault() || !$this->hasDefault()))
        {
            $return = $this->autoCastRelation($return);
            if(is_bool($return))
            $return = Base\Boolean::toInt($return);
        }

        $return = $this->value($return);
        $row = Base\Obj::cast($row);
        $return = $this->attrOrMethodCall('onSet',$return,null,$row,$option);
        $return = $this->insertCallable($return,$row,$option);
        $return = $this->autoCast($return);

        return $return;
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


    // keyboard
    // retourne le keyboard à utiliser pour le champ (input mode)
    final public function keyboard():?string
    {
        return $this->getAttr('keyboard');
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
            $isTextTag = Html::isTextTag($tag);
            $isInputMethod = Html::isInputMethod($tag);
            $isHiddenTag = Html::isHiddenTag($tag);

            if(!array_key_exists('data-required',$return) && $this->isRequired())
            $return['data-required'] = true;

            if($isHiddenTag === false)
            {
                if($isTextTag === true || $isInputMethod === true)
                {
                    $keyboard = $this->keyboard();
                    $pattern = $this->rulePattern();

                    if(!array_key_exists('data-pattern',$return) && !empty($pattern))
                    $return['data-pattern'] = $pattern;

                    if(!array_key_exists('inputmode',$return) && !empty($keyboard))
                    $return['inputmode'] = $keyboard;
                }

                if($isInputMethod === true)
                {
                    $length = $this->length();
                    if(!array_key_exists('maxlength',$return) && is_int($length))
                    $return['maxlength'] = $length;
                }
            }

            if(array_key_exists('placeholder',$return) && $return['placeholder'] !== null)
            $return['placeholder'] = $this->placeholder($return['placeholder']);

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
        $value = $this->value($value);
        $tag = $this->tag($attr);
        $attr = $this->formAttr($attr);

        if(!Html::isFormTag($tag))
        $value = Html::xss($value);

        return Html::$tag($value,$attr,$option);
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

        if(Base\Vari::isReallyEmpty($value))
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

        if($this->hasFormLabelId($attr,$complex) || (!empty($attr['id']) && $attr['id'] === true))
        {
            $id = Base\Attr::randomId($attr['name'] ?? $this->name());
            $attr = Base\Arr::plus($attr,['id'=>$id]);
        }

        $form = $this->$method($value,$attr,$option);

        if(is_string($form))
        $return = Html::formWrapStr($label,$form,$wrap,$replace,$id);

        return $return;
    }


    // hasFormLabelId
    // retourne vrai si l'élément de formulaire de la colonne doit avoir un id dans le label
    public function hasFormLabelId(?array $attr=null,bool $complex=false):bool
    {
        $tag = $this->tag($attr,$complex);
        return Html::isTextTag($tag);
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
    final public function setCommittedCallback(string $key,\Closure $closure,?Cell $cell=null):void
    {
        if(!empty($cell))
        $cell->setCommittedCallback($key,$closure);

        else
        $this->callback[$key] = $closure;
    }


    // htmlExcerpt
    // fonction pour faire un résumé sécuritaire
    // removeLineBreaks, removeUnicode, excerpt par length (rtrim et suffix), trim, stripTags, encode (specialChars)
    // mb est true par défaut
    final public function htmlExcerpt(?int $length,$value=true,?array $option=null):string
    {
        $value = Base\Str::cast($this->value($value));
        return Html::excerpt($length,$value,$option);
    }


    // htmlOutput
    // output une string html de façon sécuritaire
    // removeLineBreaks, removeUnicode, trim et encode (specialchars)
    // mb est true par défaut
    final public function htmlOutput($value=true,?array $option=null):string
    {
        $value = Base\Str::cast($this->value($value));
        return Html::output($value,$option);
    }


    // htmlXss
    // permet de retirer les tags et attributs dangereux tout en conservant le maximum d'html
    final public function htmlXss($value=true):string
    {
        $value = Base\Str::cast($this->value($value));
        return Html::xss($value);
    }


    // htmlUnicode
    // removeLineBreaks, trim et convert (specialchars)
    // conserve unicode
    final public function htmlUnicode($value=true,?array $option=null):string
    {
        $value = Base\Str::cast($this->value($value));
        return Html::unicode($value,$option);
    }


    // relation
    // retourne l'instance de colRelation
    // une exception sera envoyé si la colonne n'est pas une relation
    final public function relation():ColRelation
    {
        if(empty($this->relation))
        $this->relation = ColRelation::newOverload($this);

        return $this->relation;
    }


    // primaries
    // retourne les clés primaries qui réponde à la requête
    final public function primaries($where,...$args):array
    {
        return $this->db()->selectPrimaries($this->table(),[[$this->name(),'findInSet',$where]],...$args);
    }


    // countPrimaries
    // retourne le count des clés primaries qui réponde à la requête
    final public function countPrimaries($where,...$args):?int
    {
        return $this->db()->selectCount($this->table(),[[$this->name(),'findInSet',$where]],...$args);
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
        return in_array($value,['00','01'],true);
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