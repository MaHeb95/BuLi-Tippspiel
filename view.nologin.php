﻿<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 12:45
 */
error_reporting(E_ERROR);
session_start();
if(!isset($_SESSION['userid'])) {
    require ("view.header.php");
    ?>
    <html>
    <body >
    <div class="container-fluid">
        <div class="parent">
            <h1 class="text-center">BuLi-Tippspiel 2017-18</h1>
            <p class="text-center">Bitte loggen Sie sich zuerst ein!</p>
            <p class="text-center"><a  class="btn  btn-primary"  href="login.php">Log in  »</a></p>
        </div>
    </div>
    </body>
    </html>
    <?php die();
}