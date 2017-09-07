<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05.09.17
 * Time: 12:21
 */

//Check Login
require ("view.nologin.php");

//Abfrage der Nutzer ID vom Login
$userid = $_SESSION['userid'];

//Ausgabe des internen Startfensters
require ("view.header.php");
require ("view.navbar.php");
require ("config.php");
require ("match.php");

//$db = new mysqli('localhost','root','root','soccer_pool');//set your database handler
$query = "SELECT id,name FROM season";
$result = $db->query($query);

while($row = $result->fetch_assoc()){
    $seasons[] = array("id" => $row['id'], "val" => $row['name']);
}

$query = "SELECT id, season_id, name FROM matchday";
$result = $db->query($query);

while($row = $result->fetch_assoc()){
    $matchdays[$row['season_id']][] = array("id" => $row['id'], "val" => $row['name']);
}

$jsonSeasons = json_encode($seasons);
$jsonMatchdays = json_encode($matchdays);

?>

<!docytpe html>
<html>

<head>
    <script type='text/javascript'>
        <?php
            echo "var seasons = $jsonSeasons; \n";
            echo "var matchdays = $jsonMatchdays; \n";
        ?>
        function loadSeasons(){
            var select = document.getElementById("seasonsSelect");
            select.onchange = updateMatchdays;
            for(var i = 0; i < seasons.length; i++){
                select.options[i] = new Option(seasons[i].val,seasons[i].id);
            }
        }
        function updateMatchdays() {
            var seasonSelect = this;
            var seasonid = this.value;
            var matchdaySelect = document.getElementById("matchdaysSelect");
            matchdaySelect.options.length = 0; //delete all options if any present
            for (var i = 0; i < matchdays[seasonid].length; i++) {
                matchdaySelect.options[i] = new Option(matchdays[seasonid][i].val, matchdays[seasonid][i].id);
            }
        }

    </script>

</head>

<body onload='loadSeasons()'>
    <select id='seasonsSelect'>
    </select>

    <select id='matchdaysSelect'>
    </select>

    <?php
        vardump(get_match_ids(1));
        die();
    ?>



</body>

</html>