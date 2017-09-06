<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 23:54
 */

function create_bet($user_id, $match_id, $bet) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO soccer_pool.bet ($user_id, $match_id, $bet) VALUES (:user_id, :match_id, :bet)");
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
    $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
    //$statement->bindValue(':time', $time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function check_points($user_id, $match_id, $bet, $points) {
    require("config.php");

    $stat1 = $pdo->prepare("SELECT winner FROM soccer_pool.match  WHERE match_id = $match_id AND user_id = $user_id");
    $stat2 = $pdo->prepare("SELECT bet FROM soccer_pool.bet  WHERE match_id = $match_id");
    if ($stat1 == $stat2)
    
    $statement->bindValue(':time', $time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function create_submitted() {
    require("config.php");

}

var_dump(create_bet(1,1,2));

?>

