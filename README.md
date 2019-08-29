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
- *Dynamic singleton*: Table, Col, Row and Cell objects can only exist once for a same source.

## Overview
**Quid\Orm** contains more than 20 classes and traits. Here is an overview:
- [Cell](src/Cell.php) | Class to represent an existing cell within a row
- [Cells](src/Cells.php) | Class for a collection of many cells within a same row
- [Classe](src/Classe.php) | Class required to identify which class needs to be used by the different ORM components of a database
- [Col](src/Col.php) | Class to represent an existing column within a table
- [ColRelation](src/ColRelation.php) | Class to access the relation data of a column
- [ColSchema](src/ColSchema.php) | Class used to parse the information schema of a column
- [Cols](src/Cols.php) | Class for a collection of many columns within a same table
- [Db](src/Db.php) | Class used to query the database and to link the results to the different ORM components
- [Exception](src/Exception.php) | Class used for a database query exception
- [History](src/History.php) | Class used to store the history of requests made to the PDO object
- [Pdo](src/Pdo.php) | Class used to query the database using the PDO object
- [PdoSql](src/PdoSql.php) | Class used to build an sql query in a object-oriented way, not linked to the ORM components
- [Relation](src/Relation.php) | Class that is extended by ColRelation and Relation
- [Row](src/Row.php) | Class to represent an existing row within a table
- [Rows](src/Rows.php) | Class for a collection of many rows within a same table
- [RowsIndex](src/RowsIndex.php) | Class for a collection of many rows within different tables (keys are indexed)
- [Schema](src/Schema.php) | Class that provides a schema for a database with tables and columns information
- [Sql](src/Sql.php) | Class used to build a sql query in a object-oriented way, uses the DB class (linked to the ORM components)
- [Table](src/Table.php) | Class to represent an existing table within a database
- [TableClasse](src/TableClasse.php) | Class required to identify which class needs to be used by the different ORM components of a table
- [TableRelation](src/TableRelation.php) | Class to access the relation data of a table
- [Tables](src/Tables.php) | Class for a collection of many tables within a same database
- [_colCell](src/_colCell.php) | Trait that provides common methods for Col and Cell objects
- [_dbAccess](src/_dbAccess.php) | Trait that grants database access to the class using
- [_tableAccess](src/_tableAccess.php) | Trait that grants table access to the class using