<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 18:26
 */

//Tragt hier eure Verbindungsdaten zur Datenbank ein
$db_host = 'localhost';
$db_name = 'test';
$db_user = 'root';
$db_password = 'root';
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);