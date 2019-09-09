<?php
declare(strict_types=1);

/*
 * This file is part of the QuidPHP package.
 * Website: https://quidphp.com
 * License: https://github.com/quidphp/orm/blob/master/LICENSE
 */

namespace Quid\Test\Orm;
use Quid\Orm;
use Quid\Base;

// colSchema
// class for testing Quid\Orm\ColSchema
class ColSchema extends Base\Test
{
    // trigger
    public static function trigger(array $data):bool
    {
        // construct

        // is
        assert(!Orm\ColSchema::is('123abc'));
        assert(Orm\ColSchema::is('a123abc'));
        assert(!Orm\ColSchema::is([1,2,34]));
        assert(!Orm\ColSchema::is('-csrf-'));

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

        // patternType
        assert(Orm\ColSchema::patternType('name_fr') === 'fr');
        assert(Orm\ColSchema::patternType('name_frz') === null);
        assert(Orm\ColSchema::patternType('test_id') === 'enum');
        assert(Orm\ColSchema::patternType('test_ids') === 'set');

        // langCode
        assert(Orm\ColSchema::langCode('name_en') === 'en');
        assert(Orm\ColSchema::langCode('name_za') === null);

        // panel
        assert(Orm\ColSchema::panel('name_fr') === 'fr');
        assert(Orm\ColSchema::panel('name_frz') === null);
        assert(Orm\ColSchema::panel('test_id') === 'relation');
        assert(Orm\ColSchema::panel('test_ids') === 'relation');

        // table
        assert(Orm\ColSchema::table('user') === null);
        assert(Orm\ColSchema::table('session_james') === null);
        assert(Orm\ColSchema::table('session_id') === 'session');
        assert(Orm\ColSchema::table('table_ids') === 'table');

        // possible
        assert(Orm\ColSchema::possible('name') === ['name_en','name_fr','name_id','name_ids']);
        assert(Orm\ColSchema::possible('name',true) === ['name_en','name_id','name_ids']);

        // group
        assert(Orm\ColSchema::group(['date'=>true,'kind'=>'int']) === 'date');
        assert(Orm\ColSchema::group(['relation'=>true,'kind'=>'int']) === 'relation');
        assert(Orm\ColSchema::group(['media'=>true,'kind'=>'int']) === 'media');
        assert(Orm\ColSchema::group(['key'=>'primary','kind'=>'int']) === 'primary');
        assert(Orm\ColSchema::group(['kind'=>'int']) === 'int');
        assert(Orm\ColSchema::group(['kind'=>'char']) === 'char');
        assert(Orm\ColSchema::group(['kind'=>'float']) === 'float');

        // prepareAttr
        $attr = ['test'=>2,'Field'=>'id','Type'=>'int(11) unsigned','Null'=>'NO','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','unsigned'=>true,'length'=>11,'null'=>null,'key'=>'primary','required'=>true,'group'=>'primary','validate'=>['int','>='=>0,'<='=>4294967294,'maxLength'=>11]]);
        $attr = ['Field'=>'id','Type'=>'int(11) unsigned','Null'=>'YES','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','unsigned'=>true,'length'=>11,'null'=>null,'default'=>null,'key'=>'primary','required'=>true,'group'=>'primary','validate'=>['int','>='=>0,'<='=>4294967294,'maxLength'=>11]]);
        $attr = ['Field'=>'name','Type'=>'varchar(55)','Null'=>'YES','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'varchar','kind'=>'char','search'=>true,'length'=>55,'null'=>true,'default'=>null,'group'=>'char','validate'=>['string','maxLength'=>55]]);
        $attr = ['Field'=>'name','Type'=>'varchar(55)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'varchar','kind'=>'char','search'=>true,'length'=>55,'null'=>false,'group'=>'char','validate'=>['string','maxLength'=>55]]);
        $attr = ['Field'=>'content','Type'=>'text','Null'=>'YES','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'text','kind'=>'text','search'=>true,'length'=>65535,'null'=>true,'default'=>null,'group'=>'text','validate'=>['string','maxLength'=>65535]]);
        $attr = ['Field'=>'dateAdd','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        $attr['priority'] = 10;
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'int','kind'=>'int','length'=>11,'null'=>false,'group'=>'int','priority'=>10,'validate'=>['int','>='=>-2147483647,'<='=>2147483647,'maxLength'=>11]]);
        $attr = ['Field'=>'name_fr','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr)['panel'] === 'fr');
        $attr = ['Field'=>'user_id','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr)['relation'] === 'user');
        assert(Orm\ColSchema::prepareAttr($attr)['enum'] === true);
        $attr = ['Field'=>'user_ids','Type'=>'int(11)','Null'=>'No','Key'=>'','Default'=>null];
        assert(Orm\ColSchema::prepareAttr($attr)['set'] === true);
        $attr = ['Field'=>'price','Type'=>'float','Null'=>'YES','Key'=>'','Default'=>null,'Extra'=>''];
        assert(Orm\ColSchema::prepareAttr($attr) === ['type'=>'float','kind'=>'float','null'=>true,'default'=>null,'group'=>'float','validate'=>['float']]);
        $attr = ['test'=>2,'Field'=>'id','Type'=>'blob','Null'=>'NO','Key'=>'PRI','Default'=>null,'Extra'=>'auto_increment'];
        assert(Orm\ColSchema::prepareAttr($attr) === null);
        $attr = ['Field'=>'price','Type'=>'float unsigned','Null'=>'YES','Key'=>'','Default'=>null,'Extra'=>''];
        assert(Orm\ColSchema::prepareAttr($attr)['validate'] = ['float','>='=>0]);

        // parseType
        assert(Orm\ColSchema::parseType('tinyint(1) unsigned') === ['type'=>'tinyint','kind'=>'int','unsigned'=>true,'length'=>1]);
        assert(Orm\ColSchema::parseType('char(25)') === ['type'=>'char','kind'=>'char','search'=>true,'length'=>25]);
        assert(Orm\ColSchema::parseType('varchar(25)') === ['type'=>'varchar','kind'=>'char','search'=>true,'length'=>25]);
        assert(Orm\ColSchema::parseType('tinytext') === ['type'=>'tinytext','kind'=>'text','search'=>true,'length'=>255]);
        assert(Orm\ColSchema::parseType('float') === ['type'=>'float','kind'=>'float']);
        assert(Orm\ColSchema::parseType('enum') === null);
        assert(Orm\ColSchema::parseType('float unsigned')['unsigned'] === true);

        // parseValidate
        assert(Orm\ColSchema::parseValidate(['type'=>'varchar','kind'=>'char','search'=>true,'length'=>25]) === ['string','maxLength'=>25]);
        assert(Orm\ColSchema::parseValidate(['type'=>'int','kind'=>'int','length'=>11]) === ['int','>='=>-2147483647,'<='=>2147483647,'maxLength'=>11]);
        assert(Orm\ColSchema::parseValidate(['type'=>'float','kind'=>'float']) === ['float']);

        // parseValidateInt
        assert(Orm\ColSchema::parseValidateInt(['type'=>'int']) === ['>='=>-2147483647,'<='=>2147483647]);

        // parseValidateUnsigned
        assert(Orm\ColSchema::parseValidateUnsigned(['type'=>'float','unsigned'=>true]) === ['>='=>0]);

        // kindDefault
        assert(Orm\ColSchema::kindDefault('char') === '');
        assert(Orm\ColSchema::kindDefault('int') === 0);
        assert(Orm\ColSchema::kindDefault('text') === '');

        // kindTag
        assert(Orm\ColSchema::kindTag('int') === 'inputText');
        assert(Orm\ColSchema::kindTag('char') === 'inputText');
        assert(Orm\ColSchema::kindTag('text') === 'textarea');

        // formTag
        assert(Orm\ColSchema::formTag(['kind'=>'int','tag'=>'textarea']) === 'textarea');
        assert(Orm\ColSchema::formTag(['kind'=>'int']) === 'inputText');
        assert(Orm\ColSchema::formTag(['kind'=>'float']) === 'inputText');
        assert(Orm\ColSchema::formTag([]) === null);

        // textLength
        assert(Orm\ColSchema::textLength('text') === 65535);
        assert(Orm\ColSchema::textLength('textz') === null);

        return true;
    }
}
?>