<?php

    require_once ('SunDB.php'); // Call 'SunDB' class

    $db = new SunDB(['driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'dbname'=> 'test', 'username' => 'test', 'password' => '1234', 'charset' => 'utf8']); // Don't forget to change dbname, username, and password

    //$db = new SunDB(['driver' => 'sqlite', 'url' => 'test.sqlite']);

    // Example for Select Query (Single Row)
    $select = $db->select('users', ['id', 'name', 'surname'])->orderBy('id', 'desc')->first()->run(); // add "->first()", "->last()", "->random()"  before "->run"
    echo 'ID: '. $select['id'].'<br>'.'Name: '.$select['name'].'<br>'.'Surname: '.$select['surname'];


    /*
    // Example for Select Query (Single Row)
    $select = $db->select('users', ['id', 'name', 'surname'])->orderBy('id', 'desc')->run();
    $result = $select[0];
    echo 'ID: '. $result['id'].'<br>'.'Name: '.$result['name'].'<br>'.'Surname: '.$result['surname'];
    */


    /*
    // Example for Select Query (Single Row)
    $select = $db->select('users', ['id', 'name', 'surname'])->orderBy('id', 'desc')->run();
    foreach ($select as $rows) { 
        echo 'ID: '. $rows['id'].'<br>'.'Name: '.$rows['name'].'<br>'.'Surname: '.$rows['surname'];
        echo '<br><br>';
    }
    */


    /*
    // Example for Select Query (Multiple Row)
    $cols = ['id', 'name', 'surname'];
    $select = $db->select('users', $cols)->orderBy('id', 'desc')->run(); // or add "->all()" before "->run"
    // $select = $db->select('users', $cols)->orderBy('id', 'desc')->all()->run();
    if ($select) {
        echo '<b>'.$db->rowCount().' rows found.</b><br><br>';
        foreach ($select as $rows) {
            echo 'ID: '. $rows['id'].'<br>'.'Name: '.$rows['name'].'<br>'.'Surname: '.$rows['surname'];
            echo '<br><br>';
        }
    }
    */


    /*
    // Example for Where Null / Not Null
    $select = $db->select('users')->whereNull('surname')->run();
    // $select = $db->select('users')->whereNotNull('surname')->run();
    if ($select) {
        echo '<b>'.$db->rowCount().' rows found.</b><br><br>';
        foreach ($select as $rows) {
            echo 'ID: '. $rows['id'].'<br>'.'Name: '.$rows['name'];
            echo '<br><br>';
        }
    }
    */


    /*
    // Example for Where Raw (not validated, don't put user input in the raw string)
    $select = $db->select('users')->whereRaw('id > 5 AND id < 10')->run();
    if ($select) {
        echo '<b>'.$db->rowCount().' rows found.</b>';
    }
    */


    /*
    // Example for Exists (checks without fetching the whole result set)
    $exists = $db->select('users')->where('id', '1', '=')->exists();
    echo $exists ? 'User exists!' : 'User not found!';
    */


    /*
    // Example for Pagination (page, rows per page)
    $select = $db->select('users')->orderBy('id', 'asc')->paginate(1, 5)->run();
    echo '<b>'.$db->totalCount().' total rows.</b><br><br>';
    if ($select) {
        foreach ($select as $rows) {
            echo 'ID: '. $rows['id'].'<br>'.'Name: '.$rows['name'];
            echo '<br><br>';
        }
    }
    */


    /*
    // Example for Join Method (adjust 'orders'/'user_id' to a real second table in your schema)
    $select = $db->select('users', ['users.id', 'users.name', 'orders.total'])
                 ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                 ->run();
    // or ->join('orders', 'users.id', '=', 'orders.user_id', 'inner')
    // or ->rightJoin('orders', 'users.id', '=', 'orders.user_id')
    // or ->innerJoin('orders', 'users.id', '=', 'orders.user_id')
    if ($select) {
        foreach ($select as $rows) {
            print_r($rows);
        }
    }
    */


    /*
    // Example for Insert Query with Keys and Values
    $data = ['id' => Null, 'name' => rand(1111,9999), 'surname' => rand(1111,9999)];
    $insert = $db->insert('users', $data)->run();
    if ($insert) {
        echo 'Record inserted successfully! ID: '.$db->lastInsertId();
    }
    */


    /*
    // Example for Insert Query Only with Values
    $data = [Null, rand(1111,9999), rand(1111,9999)]; // don't forget to send parameters in same order created in the table
    $insert = $db->insert('users', $data)->run();
    if ($insert) {
        echo 'Record inserted successfully! ID: '.$db->lastInsertId();
    }
    */


    /*
    // Example for Insert Multiple Rows in One Query
    $rows = [
        ['id' => Null, 'name' => rand(1111,9999), 'surname' => rand(1111,9999)],
        ['id' => Null, 'name' => rand(1111,9999), 'surname' => rand(1111,9999)]
    ];
    $insert = $db->insertMany('users', $rows)->run();
    if ($insert) {
        echo 'Records inserted successfully!';
    }
    */


    /*
    // Example for Transactions
    $db->beginTransaction();
    try {
        $db->insert('users', ['id' => Null, 'name' => rand(1111,9999), 'surname' => rand(1111,9999)])->run();
        $db->commit();
        echo 'Committed successfully!';
    } catch (Exception $e) {
        $db->rollback();
        echo 'Failed, rolled back: '.$e->getMessage();
    }
    */


    /*
    // Example for Update Query
    $data = ['name' => rand(1111,9999), 'surname' => rand(1111,9999)];
    $update = $db->update('users', $data)->where('id', '1', '=')->run();
    if ($db->rowCount() > 0) {
        echo $db->rowCount().' records updated successfully!';
    } else {
        echo 'Update failed!';
    }
    */


    /*
    // Example for Delete Query
    $delete = $db->delete('users')->where('id', '1', '=')->run();
    if ($db->rowCount() > 0) {
        echo $db->rowCount().' records deleted successfully!';
    } else {
        echo 'Delete failed!';
    }
    */

?>
