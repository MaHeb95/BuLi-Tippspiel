<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 23:54
 */

function create_bet($user_id, $match_id, $bet) {
    require("config.php");

    $sql = "SELECT start_time - NOW() FROM soccer_pool.match WHERE id='".$match_id."'";
    if ($sql<0) {
        var_dump($sql);
        die();
    }else {

        if (NULL == ($sql2 = "SELECT bet FROM soccer_pool.bet WHERE match_id='".$match_id."'")) {

            $statement = $pdo->prepare("INSERT INTO soccer_pool.bet (user_id, match_id, bet, time) VALUES (:user_id, :match_id, :bet, NOW())");
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        }
        else {
            $statement = $pdo->prepare("UPDATE soccer_pool.bet SET user_id=:user_id, match_id=:match_id, bet=:bet, time=NOW()");
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        }

    }
    return $result;
}

function check_points($user_id, $match_id) {
    require("config.php");

    $sql = "SELECT finished FROM soccer_pool.match WHERE id=$match_id";
    if ($sql == 0)
    {
        var_dump('exit');
    } else {

        $stat1 = $pdo->prepare("SELECT winner FROM soccer_pool.match  WHERE id = $match_id");
        $stat2 = $pdo->prepare("SELECT bet FROM soccer_pool.bet  WHERE match_id = $match_id AND user_id = $user_id");

        if ($stat1 == $stat2) {
            $statement = $pdo->prepare("INSERT INTO soccer_pool.bet (points) VALUES (1)");
        }    else {
            $statement = $pdo->prepare("INSERT INTO soccer_pool.bet (points) VALUES (0)");
        }
        $result = $statement->execute();
    }

    return $result;
}

function create_submitted() {
    require("config.php");

}

var_dump(create_bet(1,1,1));

?>

