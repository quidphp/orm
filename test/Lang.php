<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Author: Pierre-Philippe Emond <emondpph@gmail.com>
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 * Readme: https://github.com/quidphp/orm/blob/master/README.md
 */

namespace Quid\Test\Orm;
use Quid\Base;

// lang
// class for testing Quid\Orm\Lang
class Lang extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $boot = $data['boot'];
        $lang = $boot->lang();
        $lang->changeLang('fr');
        $frFile = $boot->getAttr('assert/langFile/fr');

        // onChange

        // direction
        assert($lang->direction('DESC') === 'Descendant');

        // dbLabel
        assert($lang->replace($frFile) === $lang);
        assert($lang->dbLabel('assert') === 'Well');

        // dbDescription
        assert($lang->dbDescription('assert') === 'OK');

        // tableLabel
        assert($lang->tableLabel('user') === 'Utilisateur');
        assert($lang->tableLabel('user','en') === 'User');

        // tableDescription

        // colLabel
        assert($lang->colLabel('username','user') === "Nom d'utilisateur");
        assert($lang->colLabel('session_id','user') === 'Session');

        // colDescription

        // rowLabel
        assert($lang->rowLabel(2,'user') === 'Utilisateur #2');
        assert($lang->rowLabel(2,'user',null,'en') === 'User #2');

        // rowDescription
        assert($lang->rowDescription(3,'user') === null);

        // panelLabel

        // panelDescription

        // validate
        assert($lang->validate(['Doit être unique #6']) === 'Doit être unique #6');
        assert($lang->validate(['email']) === 'Doit être un courriel valide (x@x.com)');
        assert($lang->validate(['>'=>2]) === 'Doit être plus grand que 2');
        assert($lang->validate(['>'=>2],'en') === 'Must be larger than 2');
        assert($lang->validate(['string']) === 'Doit être une chaîne');
        assert($lang->validate(['strMaxLength'=>4]) === 'Doit avoir une longueur maximale de 4 caractères');
        assert($lang->validate(['arrCount'=>4],'en') === 'Array count must be 4');
        assert($lang->validate(['strLength'=>4]) === 'Doit être une chaîne avec 4 caractères');
        assert($lang->validate(['strLength'=>1]) === 'Doit être une chaîne avec 1 caractère');
        assert($lang->validate(['numberMinLength'=>3]) === 'Doit avoir une longueur minimale de 3 caractères');
        assert($lang->validate(['maxLength'=>1]) === 'Doit avoir une longueur maximale de 1 caractère');
        assert($lang->validate(['reallyEmpty']) === 'Doit être vide (0 permis)');
        assert($lang->validate(['closure']) === 'Doit passer le test de la fonction anynonyme');
        assert($lang->validate(['instance'=>\DateTime::class]) === 'Doit être une instance de DateTime');
        assert($lang->validate(['uriPath'],'en') === 'Must be a valid uri path');
        assert($lang->validate(['intCastNotEmpty']) === 'Doit être un chiffre entier non vide');
        assert($lang->validate(['scalarNotBool']) === 'Doit être chaîne scalaire non booléenne');
        assert($lang->validate(['extension'=>['jpg','png']]) === "L'extension du fichier doit être: jpg, png");
        assert($lang->validate(['maxFilesize'=>'5 Ko']) === 'La taille du fichier doit être plus petite que 5 Ko');
        assert($lang->validate(array('test'=>2,'ok'=>'what')) === null);
        assert($lang->validate(array(null)) === null);
        assert($lang->validate(array(false)) === null);
        assert(count($lang->validate()) === 116);

        // validates
        assert($lang->validates(['alpha','!'=>3,'>'=>2])[1] === 'Ne doit pas être égal à 3');
        assert($lang->validates(['alpha','!'=>3,'>'=>2],'en')[1] === 'Must be different than 3');
        assert($lang->validates(['maxLength'=>45]) === ['Doit avoir une longueur maximale de 45 caractères']);
        assert($lang->validates([['maxLength'=>55]]) === ['Doit avoir une longueur maximale de 55 caractères']);
        assert($lang->validates([['maxLength'=>45],null,false]) === ['Doit avoir une longueur maximale de 45 caractères']);
        assert($lang->validates([['maxLength'=>45,'ok'=>2],false]) === array());
        
        // compare
        assert($lang->compare(['>'=>'james']) === 'Doit être plus grand que james');
        assert($lang->compare(['>'=>'james','<'=>'test']) === null);
        assert(count($lang->compare(null,null,['path'=>['table','what']])) === 11);

        // compares
        assert($lang->compares(['>'=>'james','<'=>'test'])[1] === 'Doit être plus petit que test');

        // required
        assert($lang->required(true) === 'Ne peut pas être vide');
        assert($lang->required(true,'en') === 'Cannot be empty');
        assert(count($lang->required()) === 2);

        // unique
        assert($lang->unique(true) === 'Doit être unique');
        assert($lang->unique(4) === 'Doit être unique (#4)');
        assert($lang->unique('what','en') === 'Must be unique (what)');
        assert($lang->unique([2,3,'what',4]) === 'Doit être unique (#2, #3, what, #4)');
        assert(count($lang->unique()) === 2);

        // editable
        assert($lang->editable(true) === 'Ne peut pas être modifié');
        assert($lang->editable(true,'en') === 'Cannot be modified');
        assert(count($lang->editable()) === 2);

        // pathAlternate
        assert($lang->pathAlternate('required',null) === 'required');

        // pathAlternateTake
        assert(count($lang->pathAlternateTake('validate')) === 116);
        assert(count($lang->pathAlternateTake('compare')) === 11);
        assert(count($lang->pathAlternateTake('compare',null,['table','what'])) === 11);
        assert(count($lang->pathAlternateTake('required')) === 2);
        assert(count($lang->pathAlternateTake('unique')) === 2);

        // pathAlternateValue

        // cleanup
        $lang->changeLang('en');
        assert($lang->allLang() === Base\Lang::all());

        return true;
    }
}
?>