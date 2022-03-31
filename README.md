# QuidPHP/Orm
[![Release](https://img.shields.io/github/v/release/quidphp/orm)](https://packagist.org/packages/quidphp/orm)
[![License](https://img.shields.io/github/license/quidphp/orm)](https://github.com/quidphp/orm/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/quidphp/orm)](https://www.php.net)
[![Style CI](https://styleci.io/repos/203672588/shield)](https://styleci.io)
[![Code Size](https://img.shields.io/github/languages/code-size/quidphp/orm)](https://github.com/quidphp/orm)

## About
**QuidPHP/Orm** is a PHP library that provides database abstraction using PDO and an easy-to-use Object-Relational Mapper (ORM). It is part of the [QuidPHP](https://github.com/quidphp/project) package and can also be used standalone. 

## License
**QuidPHP/Orm** is available as an open-source software under the [MIT license](LICENSE).

## Documentation
**QuidPHP/Orm** documentation is being written. Once ready, it will be available at [QuidPHP/Docs](https://github.com/quidphp/docs).

## Installation
**QuidPHP/Orm** can be easily installed with [Composer](https://getcomposer.org). It is available on [Packagist](https://packagist.org/packages/quidphp/orm).
``` bash
$ composer require quidphp/orm
```
Once installed, the **Quid\Orm** namespace will be available within your PHP application.

## Requirement
**QuidPHP/Orm** requires the following:
- PHP 7.4, 8.0 or 8.1 with these extensions:
    - PDO
    - pdo_mysql
    - And all PHP extensions required by [quidphp/base](https://github.com/quidphp/base)
- Mysql (>= 8.0) or MariaDB (>= 10.5) database

## Dependency
**QuidPHP/Orm** has the following dependencies:
- [quidphp/base](https://github.com/quidphp/base) - Quid\Base - PHP library that provides a set of low-level static methods
- [quidphp/main](https://github.com/quidphp/main) - Quid\Main - PHP library that provides a set of base objects and collections 

All dependencies will be resolved by using the [Composer](https://getcomposer.org) installation process.

## Comment
**QuidPHP/Orm** code is commented and all methods are explained. However, most of the comments are written in French.

## Convention
**QuidPHP/Orm** is built on the following conventions:
- *Dynamic singleton*: Table, Col, Row and Cell objects can only exist once for a same source.
- *Traits*: Traits filenames start with an underscore (_).
- *Type*: Files, function arguments and return types are strict typed.
- *Config*: A special $config static property exists in all classes. This property gets recursively merged with the parents' property on initialization.
- *Coding*: No curly braces are used in a IF statement if the condition can be resolved in only one statement.

## Overview
**QuidPHP/Orm** contains 45 classes and traits. Here is an overview:
- [CatchableException](src/CatchableException.php) - Class used for a catchable database query exception
- [Cell](src/Cell.php) - Class to represent an existing cell within a row
- [Cells](src/Cells.php) - Class for a collection of many cells within a same row
- [CellsIndex](src/CellsIndex.php) - Class for a collection of cells within different tables (keys are indexed)
- [CellsMap](src/CellsMap.php) - Root class for a collection of cells
- [Classe](src/Classe.php) - Class required to identify which class needs to be used by the different ORM components
- [Col](src/Col.php) - Class to represent an existing column within a table
- [ColRelation](src/ColRelation.php) - Class to access the relation data of a column
- [ColSchema](src/ColSchema.php) - Class used to parse the information schema of a column
- [Cols](src/Cols.php) - Class for a collection of many columns within a same table
- [ColsIndex](src/ColsIndex.php) - Class for a collection of cols within different tables (keys are indexed)
- [ColsMap](src/ColsMap.php) - Root class for a collection of cols
- [Db](src/Db.php) - Class used to query the database and to link the results to the different ORM components
- [Exception](src/Exception.php) - Class used for a database query exception
- [History](src/History.php) - Class used to store the history of requests made to the database object
- [Lang](src/Lang.php) - Extended class for an object containing language texts related to the database
    - [En](src/Lang/En.php) - English language content used by this namespace
    - [Fr](src/Lang/Fr.php) - French language content used by this namespace
- [Map](src/Map.php) - Root class for a collection of cells, cols or rows
- [Operation](src/Operation.php) - Abstract class used for a complex operation on the database
    - [Delete](src/Operation/Delete.php) - Class used for a delete operation on a table row
    - [Insert](src/Operation/Insert.php) - Class used for a insert operation on a table
    - [Truncate](src/Operation/Truncate.php) - Class used for a truncate operation on a table
    - [Update](src/Operation/Update.php) - Class used for an update operation on a table row
- [Pdo](src/Pdo.php) - Class used to query the database using the PDO object
- [PdoSql](src/PdoSql.php) - Class used to build an sql query in a object-oriented way, not linked to ORM components
- [Relation](src/Relation.php) - Abstract class that is extended by ColRelation and Relation
- [Row](src/Row.php) - Class to represent an existing row within a table
- [RowOperation](src/RowOperation.php) - Abstract class used for a complex operation on a table row
- [Rows](src/Rows.php) - Class for a collection of many rows within a same table
- [RowsIndex](src/RowsIndex.php) - Class for a collection of rows within different tables (keys are indexed)
- [RowsMap](src/RowsMap.php) - Root class for a collection of rows
- [Schema](src/Schema.php) - Class that provides a schema for a database with tables and columns information
- [Sql](src/Sql.php) - Class used to build a sql query in a object-oriented way, uses the DB class (linked to the ORM components)
- [Syntax](src/Syntax.php) - Abstract class with static methods to generate SQL syntax
    - [Mysql](src/Syntax/Mysql.php) - Class with static methods to generate MySQL syntax strings (compatible with MySQL and MariaDB)
- [Table](src/Table.php) - Class to represent an existing table within a database
- [TableClasse](src/TableClasse.php) - Class required to identify which class needs to be used by the different ORM components of a table
- [TableOperation](src/TableOperation.php) - Abstract class used for a complex operation on a database table
- [TableRelation](src/TableRelation.php) - Class to access the relation data of a table
- [Tables](src/Tables.php) - Class for a collection of many tables within a same database
- [_colCell](src/_colCell.php) - Trait that provides common methods for Col and Cell objects
- [_dbAccess](src/_dbAccess.php) - Trait that grants database access to the class using
- [_mapIndex](src/_mapIndex.php) - Trait that grants common methods for indexed collections (cols, cells, rows)
- [_tableAccess](src/_tableAccess.php) - Trait that grants table access to the class using

## Testing
**QuidPHP/Orm** contains 26 test classes:
- [CatchableException](test/CatchableException.php) - Class for testing Quid\Orm\CatchableException
- [Cell](test/Cell.php) - Class for testing Quid\Orm\Cell
- [Cells](test/Cells.php) - Class for testing Quid\Orm\Cells
- [CellsIndex](test/CellsIndex.php) - Class for testing Quid\Orm\CellsIndex
- [Classe](test/Classe.php) - Class for testing Quid\Orm\Classe
- [Col](test/Col.php) - Class for testing Quid\Orm\Col
- [ColRelation](test/ColRelation.php) - Class for testing Quid\Orm\ColRelation
- [ColSchema](test/ColSchema.php) - Class for testing Quid\Orm\ColSchema
- [Cols](test/Cols.php) - Class for testing Quid\Orm\Cols
- [ColsIndex](test/ColsIndex.php) - Class for testing Quid\Orm\ColsIndex
- [Db](test/Db.php) - Class for testing Quid\Orm\Db
- [Exception](test/Exception.php) - Class for testing Quid\Orm\Exception
- [History](test/History.php) - Class for testing Quid\Orm\History
- [Lang](test/Lang.php) - Class for testing Quid\Orm\Lang
- [Pdo](test/Pdo.php) - Class for testing Quid\Orm\Pdo
- [PdoSql](test/PdoSql.php) - Class for testing Quid\Orm\PdoSql
- [Row](test/Row.php) - Class for testing Quid\Orm\Row
- [Rows](test/Rows.php) - Class for testing Quid\Orm\Rows
- [RowsIndex](test/RowsIndex.php) - Class for testing Quid\Orm\RowsIndex
- [Schema](test/Schema.php) - Class for testing Quid\Orm\Schema
- [Sql](test/Sql.php) - Class for testing Quid\Orm\Sql
- [Syntax](test/Syntax.php) - Class for testing Quid\Orm\Syntax
- [Table](test/Table.php) - Class for testing Quid\Orm\Table
- [TableClasse](test/TableClasse.php) - Class for testing Quid\Orm\TableClasse
- [TableRelation](test/TableRelation.php) - Class for testing Quid\Orm\TableRelation
- [Tables](test/Tables.php) - Class for testing Quid\Orm\Tables

**QuidPHP/Orm** testsuite can be run by creating a new [QuidPHP/Assert](https://github.com/quidphp/assert) project.