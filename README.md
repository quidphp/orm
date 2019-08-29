# Quid\Orm
[![Release](https://img.shields.io/github/v/release/quidphp/orm)](https://packagist.org/packages/quidphp/orm)
[![License](https://img.shields.io/github/license/quidphp/orm)](https://github.com/quidphp/orm/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/quidphp/orm)](https://www.php.net)
[![Style CI](https://styleci.io/repos/203672588/shield)](https://styleci.io)
[![Code Size](https://img.shields.io/github/languages/code-size/quidphp/orm)](https://github.com/quidphp/orm)

## About
**Quid\Orm** is a PHP library that provides database abstraction using PDO and an easy-to-use Object-Relational Mapper (ORM). It is part of the [QuidPHP](https://github.com/quidphp/project) package and can also be used standalone. 

## License
**Quid\Orm** is available as an open-source software under the [MIT license](LICENSE).

## Installation
**Quid\Orm** can be easily installed with [Composer](https://getcomposer.org). It is available on [Packagist](https://packagist.org/packages/quidphp/orm).
``` bash
$ composer require quidphp/orm
```

## Requirement
**Quid\Orm** requires the following:
- PHP 7.2+ with PDO and pdo_mysql

## Dependency
**Quid\Orm** has the following dependency:
- [Quid\Main](https://github.com/quidphp/main)
- [Quid\Base](https://github.com/quidphp/base)

## Testing
**Quid\Orm** testsuite can be run by creating a new [Quid\Project](https://github.com/quidphp/project). All tests and assertions are part of the [Quid\Test](https://github.com/quidphp/test) repository.

## Comment
**Quid\Orm** code is commented and all methods are explained. However, the method and property comments are currently written in French.

## Convention
**Quid\Orm** is built on the following conventions:
- *Traits*: Traits filenames start with an underscore (_).
- *Coding*: No curly braces are used in a IF statement if the condition can be resolved in only one statement.
- *Type*: Files, function arguments and return types are strict typed.
- *Config*: A special $config static property exists in all classes. This property gets recursively merged with the parents' property on initialization.
- *Dynamic singleton*: Table, Col, Row and Cell objects can only exists once for a same source.

## Overview
**Quid\Orm** contains more than 20 classes and traits. Here is an overview:
- [Cell](src/Cell.php)
- [Cells](src/Cells.php)
- [Classe](src/Classe.php)
- [Col](src/Col.php)
- [ColRelation](src/ColRelation.php)
- [ColSchema](src/ColSchema.php)
- [Cols](src/Cols.php)
- [Db](src/Db.php)
- [Exception](src/Exception.php)
- [History](src/History.php)
- [Pdo](src/Pdo.php)
- [PdoSql](src/PdoSql.php)
- [Relation](src/Relation.php)
- [Row](src/Row.php)
- [Rows](src/Rows.php)
- [RowsIndex](src/RowsIndex.php)
- [Schema](src/Schema.php)
- [Sql](src/Sql.php)
- [Table](src/Table.php)
- [TableClasse](src/TableClasse.php)
- [TableRelation](src/TableRelation.php)
- [Tables](src/Tables.php)
- [_colCell](src/_colCell.php)
- [_dbAccess](src/_dbAccess.php)
- [_tableAccess](src/_tableAccess.php)