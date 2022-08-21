# PHP PDO Database Class

SunDB is a PHP PDO database class that utilizes PDO and prepared statements (MySQL, MariaDB, MSSQL, SQLite, etc).

<hr>

### Table of Contents

- **[Initialization](#initialization)**
- **[Insert Query](#insert-query)**
- **[Update Query](#update-query)**
- **[Delete Query](#delete-query)**
- **[Select Query](#select-query)**
- **[Where Method](#where-method)**
- **[Ordering Method](#ordering-method)**
- **[Limiting Method](#limiting-method)**
- **[Grouping Method](#grouping-method)**
- **[Having Method](#having-method)**
- **[Raw SQL Queries](#raw-sql-queries)**
- **[Backup Database](#backup-database)**
- **[Helper Methods](#helper-methods)**

### Installation

To utilize this class, first import SunDB.php into your project, and require it.
SunDB requires PHP 5.5+ to work.

```php
require_once ('SunDB.php');
```

### Initialization

Simple initialization with utf8 charset set by default:
```php
$db = new SunDB(null, 'host', 'username', 'password', 'dbName');
```

Advanced initialization:
```php
$db = new SunDB(['driver' => 'mysql',
                 'host' => 'host',
                 'port' => 3306,
                 'dbname'=> 'dbName',
                 'username' => 'username', 
                 'password' => 'password',
                 'charset' => 'utf8'
                ]);
```
Url, Port and Charset parameters are optional.

You can use mysql, mssql, and sqlite driver types for Driver parameter.

If you want use MSSQL:
```php
$db = new SunDB(['driver' => 'mssql',
                 'host' => 'serverName',
                 'dbname' => 'dbName',
                 'username' => 'username',
                 'password' => 'password'
                ]);
```

If you want use SQLite:
```php
$db = new SunDB(['driver' => 'sqlite',
                 'url' => 'fileName.sqlite'
                ]);
```

Also it's possible to use already connected PDO object:
```php
$pdo = new PDO('mysql:dbname=sample;host=localhost', 'username', 'password');
$db = new SunDB($pdo);
```

### Insert Query

Simple example
```php
$data = [
    'column1' => 'Value 1',
    'column2' => 'Value 2',
    'column3' => 'Value 3'
];
$insert = $db->insert('tableName', $data)->run();
// Gives: INSERT INTO tableName (column1, column2, column3) VALUES ('Value 1', 'Value 2', 'Value3');

if ($insert) {
    echo 'Record inserted successfully! ID: '.$db->lastInsertId();
}
```

Insert with functions use
```php
$data = [
    'column1' => 'Value 1',
    'column2' => true, // or false
    'column3' => 'Value 3',
    'column4' => $db->func('sha1', 'stringText'),
    //Supported functions date, sha1, md5, base64, ceil, floor, round, etc.
    //'columnX' => $db->func('date', 'now'),
    //'columnX' => $db->func('date', 'Y-m-d'),
    //'columnX' => $db->func('date', 'H:i:s'),
    //Supported intervals [s]econd, [m]inute, [h]hour, [d]day, [M]onth, [Y]ear
];

$insert = $db->insert('tableName', $data)->run();
// Gives: INSERT INTO tableName (...) VALUES (...);

//if ($insert) {
if ($db->rowCount() > 0) {
    echo 'Record inserted successfully! ID: '.$db->lastInsertId();
} else {
    echo 'Insert failed!';
}
```

### Update Query

```php
$data = [
    'column1' => 'Value 1',
    'column2' => 'Value 2',
    'column3' => 'Value 3'
];

$update = $db->update('tableName', $data)
             ->where('column', 'value', '=')
             ->run();
// Gives: UPDATE tableName SET column1='Value 1', column2='Value 2', column3='Value 3' WHERE column=value;

//if ($update) {
if ($db->rowCount() > 0) {
    echo $db->rowCount().' records updated successfully!';
} else {
    echo 'Update failed!';
}
```

`update()` also support order by and limit parameters:
```php
$update = $db->update('tableName', $data)
             ->where('column', 'value', '>')
             ->orderBy('column', 'desc')
             ->limit(2)
             ->run();
// Gives: UPDATE tableName SET ... WHERE column > value ORDER BY column DESC LIMIT 2;
```

### Delete Query

```php
$delete = $db->delete('tableName')
             ->where('column', 'value', '>')
             ->run();
// Gives: DELETE FROM tableName WHERE column > value;

//if ($delete) {
if ($db->rowCount() > 0) {
    echo $db->rowCount().' records deleted successfully!';
} else {
    echo 'Delete failed!';
}
```

### Select Query

After any select function calls returned rows is stored in an array/object
```php
$select = $db->select('tableName')->run(); //contains an array/object of all records
// Gives: SELECT * FROM tableName;

$select = $db->select('tableName')->limit(4)->run(); //contains an array/object of X records
// Gives: SELECT * FROM tableName LIMIT 4;
```

or select with custom columns set

```php
$cols = ['column1', 'column2', 'column3'];
$select = $db->select('tableName', $cols)->run();
// Gives: SELECT column1,column2,column3 FROM tableName;

if ($select) {
    foreach ($select as $rows) { 
        print_r($rows);
    }
}
```

or select just one row

```php
$select = $db->select('tableName')->where('column', 'value', '=')->run();
// Gives: SELECT * FROM tableName WHERE column='value';

echo $select[0]['column'];
```

or select one column value or function result

```php
$select = $db->select('tableName', ['column'])->limit(1)->run();
// Gives: SELECT column FROM tableName LIMIT 1;

echo $select[0]['column'];

$select = $db->select('tableName', ['count(*) as total'])->run();
// Gives: SELECT count(*) as total FROM tableName;

echo $select[0]['total'];
```

### Where Method

`where()` or `orWhere()` methods allow you to specify `where` condition of the query. This method is supported by `select`, `update` and `delete` queries, and uses prepared statements (also bind parameters).

```php
$select = $db->select('tableName')
             ->where('column', 'value', '=')
             ->run();
// Gives: SELECT * FROM tableName WHERE column='value';
```

```php
$select = $db->select('tableName')
             ->where('column1', 'value1', '>')
             ->where('column2', 'value2', '<')
             ->run();
// Gives: SELECT * FROM tableName WHERE column1 > value1 AND column2 < value2;
```

BETWEEN / NOT BETWEEN:
```php
$select = $db->select('tableName')
             ->where('column', [date("Y-m-d"), date("Y-m-d")], 'between');
             ->run();
// Gives: SELECT * FROM tableName WHERE column BETWEEN 'YYYY-mm-dd' AND 'YYYY-mm-dd';

$select = $db->select('tableName')
             ->where('column', [date("Y-m-d"), date("Y-m-d")], 'not between');
             ->run();
// Gives: SELECT * FROM tableName WHERE column NOT BETWEEN 'YYYY-mm-dd' AND 'YYYY-mm-dd';
```

IN / NOT IN:
```php
$select = $db->select('tableName')
             ->where('column', [1, 2, 3, 'a', 'b'], 'in');
             ->run();
// Gives: SELECT * FROM tableName WHERE column IN (1, 2, 3, 'a', 'b');

$select = $db->select('tableName')
             ->where('column', [1, 2, 3, 'a', 'b'], 'not in');
             ->run();
// Gives: SELECT * FROM tableName WHERE column NOT IN (1, 2, 3, 'a', 'b');
```

OR CASE
```php
$select = $db->select('tableName')
             ->where('column1', 'value1', '=')
             ->orWhere('column2', 'value2', '=')
             ->run();
// Gives: SELECT * FROM tableName WHERE column1='value1' OR column2='value2';
```

AND and OR CASE
```php
$select = $db->select('tableName')
             ->where('column1', 'value1', '=')
             ->where('column2', 'value2', '=')
             ->orWhere('column3', 'value3', '=')
             ->run();
// Gives: SELECT * FROM tableName WHERE column1='value1' AND column2='value2' OR column3='value3';
```

```php
$select = $db->select('tableName')
             ->orWhere('column1', 'value1', '=')
             ->where('column2', 'value2', '=')
             ->where('column3', 'value3', '=')
             ->run();
// Gives: SELECT * FROM tableName WHERE column1='value1' AND column2='value2' AND column3='value3';
```

Also you can use raw where conditions:
```php
$select = $db->select('tableName')
             ->where('column1 >= value1 AND column2 < value2');
             ->run();
// Gives: SELECT * FROM tableName WHERE column1 >= value1 AND column2 < value2;
```

Find the total number of rows affected:
```php
$total = $db->rowCount();
echo "{$total} rows affected.";
```

Find the total number of rows in table:
```php
$total = $db->tableCount('tableName');
echo "{$total} rows found.";
```

### Ordering Method

`orderBy()` method allows you to specify `order by` condition of the query. This method is supported by `select`, `update` and `delete` queries.

```php
$select = $db->select('tableName', ['column1', 'column2'])
             ->orderBy('column1', 'asc')
             ->orderBy('column2', 'desc')
             ->orderBy("RAND ()")
             ->run();
// Gives: SELECT column1,column2 FROM tableName ORDER BY column1 ASC, column2 DESC, RAND ();
```

### Limiting Method

`limit()` method allows you to specify `limit` condition of the query. This method is supported by `select`, `update` and `delete` queries.

```php
$select = $db->select('tableName', ['column'])->limit(1)->run();
// Gives: SELECT column FROM tableName LIMIT 1;

echo $select[0]['column'];
```

```php
$delete = $db->delete('tableName')
             ->where('column', 'value', '>')
             ->orderBy('column', 'desc')
             ->limit(5)
             ->run();
// Gives: DELETE FROM tableName WHERE column > value ORDER BY column DESC limit 5;

//if ($delete) {
if ($db->rowCount() > 0) {
    echo $db->rowCount().' records deleted successfully!';
} else {
    echo 'Delete failed!';
}
```

### Grouping Method

`groupBy()` method allows you to specify `group by` condition of the query. This method is supported by only `select` query.

```php
$select = $db->select('tableName')
             ->groupBy('column')
             ->having('column')
             ->run();
// Gives: SELECT * FROM tableName GROUP BY column;
```

### Having Method

`having()` method allows you to specify `having` condition of the query. This method is supported by only `select` query.

```php
$select = $db->select('tableName')
             ->groupBy('column')
             ->having('column >= value')
             ->run();
// Gives: SELECT * FROM tableName GROUP BY column HAVING column >= value;
```

### Raw SQL Queries

Execute raw SQL queries:
```php
$select = $db->rawQuery("select column1,column2 from tableName where (column1='value1' && column2='value2')")
             ->limit(2)
             ->run();
// Gives: SELECT column1,column2 FROM tableName WHERE (column1='value1' && column2='value2') LIMIT 2;

foreach ($select as $rows) {
    print_r($rows);
}
```

or use prepared statements (bind parameters):
```php
$select = $db->rawQuery("select column1,column2 from tableName where (column1=? && column2=?)", ['value1', 'value2'])
             ->limit(2)
             ->run();
// Gives: SELECT column1,column2 FROM tableName WHERE (column1='value1' && column2='value2') LIMIT 2;

foreach ($select as $rows) {
    print_r($rows);
}
```

### Backup Database

Download the whole database (tables and records) as an SQL file:
```php
$db->backup('fileName', 'save');
// File: fileName.sql
```
Don't forget to use this code on an empty page. Otherwise, you can see an HTML page into an SQL file.

Show the whole database (tables and records) as an SQL query:
```php
$db->backup(null, 'show');
```

Download/Show the whole database (with exclude some tables) as an SQL query:
```php
$db->backup(null, 'save', ['table1', 'table2']); // or 'show' action
```

### Helper Methods

Get last executed SQL query:
```php
$db->showQuery();
```
This code will print the last executed query to the screen.
