<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 04.09.17
 * Time: 20:02
 */

//require_once("config.php");

function create_match($matchday_id, $url=NULL, $home_team=NULL, $guest_team=NULL, $start_time=NULL) {
    require("config.php");
    $error = false;

    if ($url !== NULL) {
        $match_info = parse_match_url($url);
        $home_team = $match_info['home_team'];
        $guest_team = $match_info['guest_team'];
        $start_time = $match_info['start_time'];
    }

    // check if matchday_id exists
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM soccer_pool.matchday WHERE id = :id");
        $result = $statement->execute(array('id' => $matchday_id));
        $matchday = $statement->fetch();

        if ($matchday == false) {
            echo 'Dieser Tippspieltag existiert nicht.<br>';
            $error = true;
        }
    }

    // write information to database
    if (!$error) {
        $statement = $pdo->prepare("INSERT INTO soccer_pool.match (matchday_id, home_team, guest_team, start_time, url) VALUES (:matchday_id, :home_team, :guest_team, FROM_UNIXTIME(:start_time), :url)");
        $result = $statement->execute(array('matchday_id' => $matchday_id, 'home_team' => $home_team, 'guest_team' => $guest_team, 'start_time' => $start_time, 'url' => $url));

        if($result) {
            echo 'Das Spiel wurde erfolgreich eingetragen.</br>';
            echo $home_team . ' ' . '-' . ' '. $guest_team . '</br>';
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten.<br>';
        }
    }
}

function get_match_ids($matchday_id) {
    require("config.php");
    $error = false;

    // check if matchday_id exists
    if (!$error) {
        $statement = $pdo->prepare("SELECT id FROM soccer_pool.match WHERE matchday_id = :matchday_id");
        $statement->bindValue(':matchday_id', $matchday_id, PDO::PARAM_INT);
        $statement->execute();
        //$result = $statement->execute(array('matchday_id' => $matchday_id));
        //$matches = $statement->fetch();

        $id_list = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $match) {
            $id_list[] = $match['id'];
        }

        return $id_list;
    }
}

function update_match($match_id, $start_time=NULL, $home_goals=NULL, $guest_goals=NULL, $finished=NULL) {
    require("config.php");
    $error = false;

    // get match information
    if (!$error) {
        $statement = $pdo->prepare("SELECT * FROM soccer_pool.match WHERE id = :id");
        $result = $statement->execute(array('id' => $match_id));
        $match = $statement->fetch();

        if ($match == false) {
            echo 'Dieses Spiel existiert nicht.<br>';
            $error = true;
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

    if (!$error) {
        if ($start_time !== NULL) {
            $statement = $pdo->prepare("UPDATE soccer_pool.match SET start_time=FROM_UNIXTIME(:start_time) WHERE id=:id");
            $result = $statement->execute(array('id' => $match_id, 'start_time' => $start_time));
        }

        $statement = $pdo->prepare("UPDATE soccer_pool.match SET home_goals=:home_goals, guest_goals=:guest_goals, finished=:finished, winner=:winner WHERE id=:id");
        $statement->bindValue(':id', $match_id, PDO::PARAM_INT);
        $statement->bindValue(':home_goals', $home_goals, PDO::PARAM_STR);
        $statement->bindValue(':guest_goals', $guest_goals, PDO::PARAM_STR);
        $statement->bindValue(':finished', $finished, PDO::PARAM_BOOL);
        $statement->bindValue(':winner', $winner, PDO::PARAM_INT);
        $result = $statement->execute();
        //$result = $statement->execute(array('id' => $match_id, 'home_goals' => $home_goals,
        //    'guest_goals' => $guest_goals, 'finished' => $finished, 'winner' => $winner));

        if($result) {
            echo 'Das Spiel wurde erfolgreich eingetragen.</br>';
            echo $match['home_team'] . ' ' . $home_goals . '-' . $guest_goals . ' '. $match['guest_team'] . '</br>';
        } else {
            echo 'Beim Abspeichern ist leider ein Fehler aufgetreten.<br>';
        }
    }

    // if finished, also call the function that gives points for this match
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

//create_match(1, 'http://www.flashscore.de/spiel/UowH4tyj');
//create_match(1, 'http://www.flashscore.de/spiel/h0pxfpON');
//create_match(1, 'http://www.flashscore.de/spiel/AiowtGBS');

foreach (get_match_ids(1) as $id) {
    update_match($id);
}

?>