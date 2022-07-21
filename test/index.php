<?php

    require_once ('SunDB.php'); // Call 'SunDB' class

    $db = new SunDB(['driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'dbname'=> 'test', 'username' => 'test', 'password' => '1234', 'charset' => 'utf8']); // Don't forget to change dbname, username, and password

    // Example for Select Query
    $cols = ['id', 'name', 'surname'];
    $select = $db->select('users', $cols)->orderBy('id', 'desc')->run();
    if ($select) {
        echo '<b>'.$db->rowCount().' rows found.</b><br><br>';
        foreach ($select as $rows) { 
            echo 'ID: '. $rows['id'].'<br>'.'Name: '.$rows['name'].'<br>'.'Surname: '.$rows['surname'];
            echo '<br><br>';
        }
    }


    /*
    // Example for Insert Query
    $data = ['id' => Null, 'name' => rand(1111,9999), 'surname' => rand(1111,9999)];
    $insert = $db->insert('users', $data)->run();
    if ($insert) {
        echo 'Record inserted successfully! ID: '.$db->lastInsertId();
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