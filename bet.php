<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 23:54
 */

function create_bet($user_id, $match_id, $bet) {
    require("config.php");

    $statement = $pdo->prepare("SELECT start_time - NOW() FROM soccer_pool.match WHERE id='".$match_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['start_time - NOW()'];
    $start_time = (int) $val;

    if ($start_time<0) {
        return False;
    }else {

        $statement = $pdo->prepare("SELECT * FROM soccer_pool.bet WHERE match_id='".$match_id."'");
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if( ! $row)
        {
            $statement = $pdo->prepare("INSERT INTO soccer_pool.bet (user_id, match_id, bet, time) VALUES (:user_id, :match_id, :bet, NOW())");
            $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $statement->bindValue(':match_id', $match_id, PDO::PARAM_INT);
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        } else {
            $statement = $pdo->prepare("UPDATE soccer_pool.bet SET bet=:bet, time=NOW() WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':bet', $bet, PDO::PARAM_INT);
            $result = $statement->execute();
        }
    }
    return $result;
}

function check_points($user_id, $match_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT finished FROM soccer_pool.match WHERE id='".$match_id."'");
    $statement->execute();
    $val = $statement->fetch(PDO::FETCH_ASSOC)['finished'];
    $finished = (int) $val;
    if ($finished == 0) {
        return False;
    } else {

        $statement = $pdo->prepare("SELECT winner FROM soccer_pool.match  WHERE id ='".$match_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['winner'];
        $winner = (int) $val;

        $statement = $pdo->prepare("SELECT bet FROM soccer_pool.bet  WHERE match_id ='".$match_id."' AND user_id ='".$user_id."'");
        $statement->execute();
        $val = $statement->fetch(PDO::FETCH_ASSOC)['bet'];
        $bet = (int) $val;

        if ($winner == $bet) {
            $points = 1;
            $statement = $pdo->prepare("UPDATE soccer_pool.bet SET points=:points WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $result = $statement->execute();
        } else {
            $points = 0;
            $statement = $pdo->prepare("UPDATE soccer_pool.bet SET points=:points WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $result = $statement->execute();
        }
    }
    return $result;
}

/*function submitted_matchday($user_id, $matchday) {
    require ("config.php");
    require ("match.php");
    $val = get_match_ids($matchday);
    foreach ($val AS $match_id) {
        submitted($user_id, $match_id);
    }
    return $result;
}*/

function submitted($user_id, $match_id) {
    require ("config.php");

    $submitted = 1;
    $statement = $pdo->prepare("UPDATE soccer_pool.bet SET submitted=:submitted WHERE match_id='".$match_id."' AND user_id='".$user_id."'");
    $statement->bindValue(':submitted', $submitted, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

//var_dump(create_bet(1,2,1));
//var_dump(check_points(1,1));
var_dump(submitted(1,1));
?>

