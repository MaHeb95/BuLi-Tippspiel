<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 04.09.17
 * Time: 20:02
 */


function create_season($name, $start_time=NULL) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".season (name, start_time) VALUES (:name, FROM_UNIXTIME(:start_time))");
    $statement->bindValue(':name', $name, PDO::PARAM_STR);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function update_season_start_time($season_id) {
    require("config.php");

    $statement = $pdo->prepare(
        "SELECT start_time FROM ".$db_name.".matchday 
         WHERE (season_id = :season_id AND start_time IS NOT NULL)
         ORDER BY start_time ASC
         LIMIT 1");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->execute();
    $start_time = $statement->fetch(PDO::FETCH_ASSOC)['start_time'];

    if ($start_time !== NULL) {
        $start_time = strtotime($start_time);
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".season SET start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
    $statement->bindValue(':id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $statement->execute();
}

function get_season_ids() {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".season ORDER BY start_time ASC, name ASC");
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $season) {
        $id_list[] = $season['id'];
    }

    return $id_list;
}

function get_seasons($ids) {
    require("config.php");

    $seasons = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".season WHERE id = :id");
        $statement->execute(array('id' => $id));
        $seasons[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $seasons;
}

function create_matchday($season_id, $name, $start_time=NULL) {
    require("config.php");

    $statement = $pdo->prepare("INSERT INTO ".$db_name.".matchday (season_id, name, start_time) VALUES (:season_id, :name, FROM_UNIXTIME(:start_time))");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->bindValue(':name', $name, PDO::PARAM_STR);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $result = $statement->execute();

    return $result;
}

function update_matchday_start_time($matchday_id) {
    require("config.php");

    $statement = $pdo->prepare(
        "SELECT start_time FROM ".$db_name.".match 
         WHERE (matchday_id = :matchday_id AND start_time IS NOT NULL)
         ORDER BY start_time ASC
         LIMIT 1");
    $statement->bindValue(':matchday_id', $matchday_id, PDO::PARAM_INT);
    $statement->execute();
    $start_time = $statement->fetch(PDO::FETCH_ASSOC)['start_time'];

    if ($start_time !== NULL) {
        $start_time = strtotime($start_time);
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".matchday SET start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
    $statement->bindValue(':id', $matchday_id, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $statement->execute();
}

function get_matchday_ids($season_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".matchday WHERE season_id = :season_id
        ORDER BY start_time ASC, name ASC");
    $statement->bindValue(':season_id', $season_id, PDO::PARAM_INT);
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $matchday) {
        $id_list[] = $matchday['id'];
    }

    return $id_list;
}

function get_matchdays($ids) {
    require("config.php");

    $matchdays = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT * FROM ".$db_name.".matchday WHERE id = :id");
        $statement->execute(array('id' => $id));
        $matchdays[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $matchdays;
}

function create_match($matchday_id, $url=NULL, $home_team=NULL, $guest_team=NULL, $start_time=NULL) {
    require("config.php");

    if ($url !== NULL) {
        $match_info = parse_match_url($url);
        $home_team = $match_info['home_team'];
        $guest_team = $match_info['guest_team'];
        $start_time = $match_info['start_time'];
    }

    // check if matchday_id exists
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".matchday WHERE id = :id");
    $statement->execute(array('id' => $matchday_id));
    $matchday = $statement->fetch(PDO::FETCH_ASSOC);

    if ($matchday == false) {
        return $matchday;
    }

    // write information to database
    $statement = $pdo->prepare("INSERT INTO ".$db_name.".match (matchday_id, home_team, guest_team, start_time, url) VALUES (:matchday_id, :home_team, :guest_team, FROM_UNIXTIME(:start_time), :url)");
    $result = $statement->execute(array('matchday_id' => $matchday_id, 'home_team' => $home_team, 'guest_team' => $guest_team, 'start_time' => $start_time, 'url' => $url));

    return $result;
}

function delete_match($match_id) {
    require("config.php");

    $statement = $pdo->prepare("DELETE FROM ".$db_name.".match WHERE id=:id");
    $statement->bindValue(':id', $match_id, PDO::PARAM_INT);
    return $statement->execute();
}

function update_match($match_id, $start_time=NULL, $home_goals=NULL, $guest_goals=NULL, $finished=NULL) {
    require("config.php");
    require("bet.php");

    // get match information
    $statement = $pdo->prepare("SELECT * FROM ".$db_name.".match WHERE id = :id");
    $statement->execute(array('id' => $match_id));
    $match = $statement->fetch(PDO::FETCH_ASSOC);

    if ($match == false) {
        return $match;
    }

    if ($match['url'] !== NULL) {
        $match_info = parse_match_url($match['url']);

        if ($home_goals === NULL) {
            $home_goals = $match_info['home_goals'];
        }
        if ($guest_goals === NULL) {
            $guest_goals = $match_info['guest_goals'];
        }
        if ($start_time === NULL) {
            $start_time = $match_info['start_time'];
        }
        if ($finished === NULL) {
            $finished = $match_info['finished'];
            if ($finished === NULL) {
                $finished = $match['finished'];
            }
        }
    }

    if ($finished) {
        if ($home_goals > $guest_goals) {
            $winner = 1;
        } elseif ($home_goals < $guest_goals) {
            $winner = 2;
        } else {
            $winner = 0;
        }
    } else {
        $winner = NULL;
    }

    $statement = $pdo->prepare("UPDATE ".$db_name.".match SET home_goals=:home_goals, guest_goals=:guest_goals, 
        finished=:finished, winner=:winner, start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
    $statement->bindValue(':id', $match_id, PDO::PARAM_INT);
    $statement->bindValue(':home_goals', $home_goals, PDO::PARAM_INT);
    $statement->bindValue(':guest_goals', $guest_goals, PDO::PARAM_INT);
    $statement->bindValue(':finished', $finished, PDO::PARAM_BOOL);
    $statement->bindValue(':winner', $winner, PDO::PARAM_INT);
    $statement->bindValue(':start_time', $start_time, PDO::PARAM_INT);
    $result = $statement->execute();

    //update points
    foreach(all_users() AS $user) {
        var_dump($user['id']);
        check_points($user['id'],$match_id);
    }


    return $result;

    // if finished, also call the function that gives points for this match
}

function get_match_ids($matchday_id) {
    require("config.php");

    $statement = $pdo->prepare("SELECT id FROM ".$db_name.".match WHERE matchday_id = :matchday_id
         ORDER BY start_time ASC");
    $statement->bindValue(':matchday_id', $matchday_id, PDO::PARAM_INT);
    $statement->execute();

    $id_list = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $match) {
        $id_list[] = $match['id'];
    }

    return $id_list;
}

function get_matches($ids) {
    require("config.php");

    $matches = [];

    foreach ($ids as $id) {
        $statement = $pdo->prepare("SELECT *, start_time - NOW() AS start FROM ".$db_name.".match WHERE id = :id");
        $statement->execute(array('id' => $id));
        $matches[$id] = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $matches;
}

function parse_match_url($url) {
    if (strpos($url, 'soccer24.com/match/') !== false) {
        return parse_soccer24($url);
    }
    if (strpos($url, 'flashscore.de/spiel/') !== false) {
        return parse_soccer24($url);
    }
    return NULL;
}

function parse_soccer24($url) {
    $return = array();

    $html = file_get_contents($url);

    libxml_use_internal_errors(TRUE); //disable libxml errors

    $doc = new DOMDocument();
    $doc->loadHTML($html);

    libxml_clear_errors(); //remove errors for yucky html

    $xpath = new DOMXPath($doc);

    $return['home_team'] = $xpath->query("//td[contains(@class, 'tname-home logo-enable')]/span[contains(@class, 'tname')]/a")[0]->nodeValue;
    $return['guest_team'] = $xpath->query("//td[contains(@class, 'tname-away logo-enable')]/span[contains(@class, 'tname')]/a")[0]->nodeValue;
    $score = $xpath->query("//td[contains(@class, 'current-result')]/span[contains(concat(' ', @class, ' '), ' scoreboard ')]");

    if ($score->length) {
        $return['home_goals'] = $score[0]->nodeValue;
        $return['guest_goals'] = $score[1]->nodeValue;
    } else {
        $return['home_goals'] = NULL;
        $return['guest_goals'] = NULL;
    }

    $var = $doc->getElementsByTagName('script')[7]->nodeValue;

    foreach(preg_split("/((\r?\n)|(\r\n?))/", $var) as $line){
        if (strpos($line, 'var game_utime ') !== false) {
            $return['start_time'] = (int)explode(';', explode(' = ', trim($line))[1])[0];
        }
        if (strpos($line, 'var event_stage_type_id ') !== false) {
            $status = (int)explode(';', explode(' = ', trim($line))[1])[0];
            if ($status == 3) {
                $return['finished'] = true;
            } else {
                $return['finished'] = false;
            }
        }
    }

    return $return;
}

//var_dump(get_seasons(get_season_ids()))
//var_dump(get_matchdays(get_matchday_ids(1)));
//var_dump(get_matches(get_match_ids(1)));

//update_matchday_start_time(1);
//update_season_start_time(1);


//create_season('test', strtotime('31.03.2017 15:00'));
//var_dump(get_season_ids());
//create_matchday(get_season_ids()[0], 'Test');
//var_dump(get_matchday_ids(1));

//create_match(1, 'http://www.flashscore.de/spiel/UowH4tyj');
//create_match(1, 'http://www.flashscore.de/spiel/h0pxfpON');
//create_match(1, 'http://www.flashscore.de/spiel/AiowtGBS');

//foreach (get_match_ids(1) as $id) {
//    update_match($id);
//}

//var_dump(get_matches(get_match_ids(1)));


?>