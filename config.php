<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 18:26
 */

//Tragt hier eure Verbindungsdaten zur Datenbank ein
$db_host = 'localhost';
$db_name = 'soccer_pool';
$db_user = 'root';
$db_password = 'root';
$db_charset = 'utf8';
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=$db_charset", $db_user, $db_password);
$db = new mysqli($db_host,$db_user,$db_password,$db_name);

require_once ('database.php');
