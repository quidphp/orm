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
use Quid\Orm;

// colSchema
// class for testing Quid\Orm\ColSchema
class ColSchema extends Base\Test
{
    // trigger
    final public static function trigger(array $data):bool
    {
        // prepare
        $db = Orm\Db::inst();
        $table = 'ormCol';
        assert($db->truncate($table) instanceof \PDOStatement);
        assert($db->inserts($table,['id','active','name','password','email','dateAdd','userAdd','dateModify','userModify'],[1,1,'james','james','james@gmail.com',10,11,12,13],[2,2,'james2','james2','james2@gmail.com',20,21,22,23]) === [1,2]);
        $tb = $db[$table];
        $col = $tb['name']->schema();
        $id = $tb->cols()->get('id')->schema();
        $dateAdd = $tb->cols()->get('dateAdd')->schema();
        $dateModify = $tb['dateModify']->schema();
        $email = $tb->cols()->get('email')->schema();
        $userId = $tb['user_id']->schema();

        // construct

        // isUnsigned
        assert($col->isUnsigned() === null);
        assert($dateAdd->isUnsigned());
        assert($dateModify->isUnsigned() === false);

        // acceptsNull
        assert($col->acceptsNull());
        assert(!$id->acceptsNull());

        // isKindInt
        assert(!$col->isKindInt());
        assert($dateAdd->isKindInt());

        // isKindChar
        assert($col->isKindChar());
        assert(!$dateAdd->isKindChar());

        // isKindText
        assert(!$col->isKindText());

        // isKindCharOrText
        assert($col->isKindCharOrText());
        assert(!$dateAdd->isKindCharOrText());

        // hasDefault

        // hasNullDefault

        // hasNotEmptyDefault

        // unique
        assert($col->unique() === true);
        assert($dateAdd->unique() === false);

        // checkStructure

        // type
        assert($col->type() === 'varchar');
        assert($id->type() === 'int');
        assert($dateAdd->type() === 'int');

        // kind
        assert($col->kind() === 'char');
        assert($id->kind() === 'int');
        assert($dateAdd->kind() === 'int');

        // name
        assert($col->name() === 'name');

        // nameStripPattern
        assert($col->nameStripPattern() === null);

        // nameLangCode
        assert($col->nameLangCode() === null);

        // default
        assert($col->default() === null);

        // length
        assert($dateAdd->length() === 11);
        assert($col->length() === 100);

        // collation
        assert($email->collation() === 'utf8mb4_general_ci');
        assert($dateAdd->collation() === null);

        // validate
        assert(count($email->validate()) === 2);
        assert(count($dateAdd->validate()) === 4);

        // relation
        assert($email->relation() === null);

        // kindDefault
        assert($email->kindDefault() === '');
        assert($dateAdd->kindDefault() === 0);

        // patternType
        assert($col->patternType() === null);
        assert($userId->patternType() === 'enum');

        // formTag
        assert($col->formTag() === 'inputText');

        // hasPattern
        assert(Orm\ColSchema::hasPattern('session_id'));
        assert(Orm\ColSchema::hasPattern('name_fr'));
        assert(!Orm\ColSchema::hasPattern('name_de'));

        // isRelation
        assert(!Orm\ColSchema::isRelation('user'));
        assert(!Orm\ColSchema::isRelation('session_james'));
        assert(Orm\ColSchema::isRelation('session_id'));
        assert(Orm\ColSchema::isRelation('table_ids'));

        // pattern
        assert(Orm\ColSchema::pattern('name_fr') === ['fr','*_fr']);
        assert(Orm\ColSchema::pattern('test_id') === ['enum','*_id']);

        // addPattern
        assert(Orm\ColSchema::addPattern('de','james') === 'james_de');
        assert(Orm\ColSchema::addPattern('de_*','james') === 'de_james');
        assert(Orm\ColSchema::addPattern('fr','james') === 'james_fr');
        assert(Orm\ColSchema::addPattern('enum','james') === 'james_id');

        // stripPattern
        assert(Orm\ColSchema::stripPattern('name_fr') === 'name');
        assert(Orm\ColSchema::stripPattern('name_de') === null);
        assert(Orm\ColSchema::stripPattern('test_id') === 'test');

        // patternTypeFromName
        assert(Orm\ColSchema::patternTypeFromName('name_fr') === 'fr');
        assert(Orm\ColSchema::patternTypeFromName('name_frz') === null);
        assert(Orm\ColSchema::patternTypeFromName('test_id') === 'enum');
        assert(Orm\ColSchema::patternTypeFromName('test_ids') === 'set');

        // langCode
        assert(Orm\ColSchema::langCode('name_en') === 'en');
        assert(Orm\ColSchema::langCode('name_za') === null);

        // table
        assert(Orm\ColSchema::table('user') === null);
        assert(Orm\ColSchema::table('session_james') === null);
        assert(Orm\ColSchema::table('session_id') === 'session');
        assert(Orm\ColSchema::table('table_ids') === 'table');

        // possible
        assert(Orm\ColSchema::possible('name') === ['name_en','name_fr','name_id','name_ids']);
        assert(Orm\ColSchema::possible('name',true) === ['name_en','name_id','name_ids']);

        // prepareAttr
        $attr = ['test'=>2,'Field'=>'id','Type'=>'int(11) unsigned','Null'=>'NO','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','unsigned'=>true,'length'=>11,'name'=>'id','null'=>null,'key'=>'primary','validate'=>['int','>='=>0,'<='=>4294967294,'maxLength'=>11]]);
        $attr = ['Field'=>'id','Type'=>'int(11) unsigned','Null'=>'YES','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','unsigned'=>true,'length'=>11,'name'=>'id','null'=>null,'default'=>null,'key'=>'primary','validate'=>['int','>='=>0,'<='=>4294967294,'maxLength'=>11]]);
        $attr = ['Field'=>'name','Type'=>'varchar(55)','Null'=>'YES','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'varchar','kind'=>'char','length'=>55,'name'=>'name','null'=>true,'default'=>null,'validate'=>['string','maxLength'=>55]]);
        $attr = ['Field'=>'name','Type'=>'varchar(55)','Collation'=>'utf8','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'varchar','kind'=>'char','length'=>55,'name'=>'name','null'=>false,'collate'=>'utf8','validate'=>['string','maxLength'=>55]]);
        $attr = ['Field'=>'content','Type'=>'text','Null'=>'YES','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'text','kind'=>'text','length'=>65535,'name'=>'content','null'=>true,'default'=>null,'validate'=>['string','maxLength'=>65535]]);
        $attr = ['Field'=>'dateAdd','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','length'=>11,'name'=>'dateAdd','null'=>false,'validate'=>['int','>='=>-2147483647,'<='=>2147483647,'maxLength'=>11]]);
        $attr = ['Field'=>'name_fr','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        $attr = ['Field'=>'user_id','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr)['relation'] === 'user');
        assert(Orm\ColSchema::prepareAttr($attr)['enum'] === true);
        $attr = ['Field'=>'user_ids','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr)['set'] === true);
        $attr = ['Field'=>'price','Type'=>'float','Null'=>'YES','Key'=>'','Default'=>null,'Extra'=>''];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'float','kind'=>'float','name'=>'price','null'=>true,'default'=>null,'validate'=>['float']]);
        $attr = ['test'=>2,'Field'=>'id','Type'=>'blob','Null'=>'NO','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === null);
        $attr = ['Field'=>'price','Type'=>'float unsigned','Null'=>'YES','Key'=>'','Default'=>null,'Extra'=>''];
        assert(Orm\ColSchema::prepareAttr($attr)['validate'] = ['float','>='=>0]);

        // parseType
        assert(Orm\ColSchema::parseType('tinyint(1) unsigned') === ['type'=>'tinyint','kind'=>'int','unsigned'=>true,'length'=>1]);
        assert(Orm\ColSchema::parseType('char(25)') === ['type'=>'char','kind'=>'char','length'=>25]);
        assert(Orm\ColSchema::parseType('varchar(25)') === ['type'=>'varchar','kind'=>'char','length'=>25]);
        assert(Orm\ColSchema::parseType('tinytext') === ['type'=>'tinytext','kind'=>'text','length'=>255]);
        assert(Orm\ColSchema::parseType('float') === ['type'=>'float','kind'=>'float']);
        assert(Orm\ColSchema::parseType('enum') === null);
        assert(Orm\ColSchema::parseType('float unsigned')['unsigned'] === true);

        // parseValidate
        assert(Orm\ColSchema::parseValidate(['type'=>'varchar','kind'=>'char','length'=>25]) === ['string','maxLength'=>25]);
        assert(Orm\ColSchema::parseValidate(['type'=>'int','kind'=>'int','length'=>11]) === ['int','>='=>-2147483647,'<='=>2147483647,'maxLength'=>11]);
        assert(Orm\ColSchema::parseValidate(['type'=>'float','kind'=>'float']) === ['float']);

        // parseValidateInt
        assert(Orm\ColSchema::parseValidateInt(['type'=>'int']) === ['>='=>-2147483647,'<='=>2147483647]);

        // parseValidateUnsigned
        assert(Orm\ColSchema::parseValidateUnsigned(['type'=>'float','unsigned'=>true]) === ['>='=>0]);

        // kindTag
        assert(Orm\ColSchema::kindTag('int') === 'inputText');
        assert(Orm\ColSchema::kindTag('char') === 'inputText');
        assert(Orm\ColSchema::kindTag('text') === 'textarea');

        // textLength
        assert(Orm\ColSchema::textLength('text') === 65535);
        assert(Orm\ColSchema::textLength('textz') === null);

        return true;
    }
}
?>