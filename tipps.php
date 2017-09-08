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


$seasonmenu = null;
$matchdaymenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}
if (isset($_GET['matchday']) && is_numeric($_GET['matchday'])) {
    $matchdaymenu = $_GET['matchday'];
}

if (trim($_POST["inputurl"]) !== "") {
    create_match($matchdaymenu, trim($_POST["inputurl"]));
}

$md_matches = null;
if ($matchdaymenu !== null) {
    $md_matches = get_matches(get_match_ids($matchdaymenu));
    foreach (get_match_ids(1) as $id) {
        $match = $md_matches[$id];
        if ((strtotime($match['start_time']) < time()) && (!isset($match['home_goals']) || !isset($match['guest_goals']))) {
            update_match($id);
        }
    }
    $md_matches = get_matches(get_match_ids($matchdaymenu));
}


?>
<html>
<head>
    <script type="text/javascript">
        /**
         * You can have a look at https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/with * for more information on with() function.
         */
        function autoSubmit_season()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (season.selectedIndex === 0) {
                    window.location.href = 'tipps.php';
                } else {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value;
                }
            }
        }

        function autoSubmit_matchday()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (matchday.selectedIndex === 0) {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value;
                } else {
                    window.location.href = 'tipps.php?season=' + season.options[season.selectedIndex].value + '&matchday=' + matchday.options[matchday.selectedIndex].value;
                }
            }
        }
    </script>
</head>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<form class="form" id="form" name="form" method="get" action="<?php echo $actual_link; ?>">
    <fieldset>
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col col-lg-3">
                    <p class="bg">
                        <label for="season">Wähle eine Saison</label> <!-- Season SELECTION -->
                        <!--onChange event fired and function autoSubmit() is invoked-->
                        <select class="form-control" id="season" name="season" onchange="autoSubmit_season();">
                            <option value="">-- Wähle eine Saison --</option>
                            <?php
                            $seasons = get_seasons(get_season_ids());
                            foreach ($seasons as $row) {
                                echo ("<option value=\"{$row['id']}\" " . ($seasonmenu == $row['id'] ? " selected" : "") . ">{$row['name']}</option>");
                            }
                            ?>
                        </select>
                    </p>
                </div>
                <?php
                //check whether Season was really selected and Season id is numeric
                if ($seasonmenu != '' && is_numeric($seasonmenu)) {
                    //select sub-categories categories for a given Season id
                    $matchdays = get_matchdays(get_matchday_ids($seasonmenu));
                    if (count($matchdays) > 0) {
                        ?>
                        <div class="col col-lg-3">
                            <p class="bg">
                                <label for="matchday">Wähle einen Spieltag</label>
                                <select class="form-control" id="matchday" name="matchday" onchange="autoSubmit_matchday();">
                                    <option value="">-- Wähle einen Spieltag --</option>
                                    <?php
                                    //POPULATE DROP DOWN WITH Matchday FROM A GIVEN Season
                                    foreach ($matchdays as $row) {
                                        echo ("<option value=\"{$row['id']}\" " . ($matchdaymenu == $row['id'] ? "selected" : "") . ">{$row['name']}</option>");
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </fieldset>
</form>


<?php
if(count($md_matches) > 0){
/*var_dump($md_matches);

$statement = ("SELECT submitted FROM ".$db_name.".bet ");*/
//!!!!! check if submitted
if (true) { ?>
<table class="table"> // Tabelle fürs tippen!
    <thead class="thead-inverse">
    <tr>
        <th style="text-align: center" colspan="1">Anstoss</th>
        <th style="text-align: center" colspan="3">Ansetzung</th>
        <th style="text-align: center" colspan="1">Ergebnis</th>
        <?php
        $statement = $pdo->prepare("SELECT username FROM soccer_pool.user WHERE id =" . $userid);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC)['username'];
        //var_dump($user);
        echo "<th style='text-align: center' colspan='7'>" . $user . "</th>";
        ?>
</tr>
</thead>
<tbody>
<?php
foreach ($md_matches AS $row) {
    echo "<tr>";
    //echo "<td>" . $row['id'] . "</td>";
    echo "<td style='text-align: center' colspan='1'>" . gmdate('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
    echo "<td style='text-align: center' colspan='3'>" . $row['home_team'] . " - " . $row['guest_team'] . "</td>";
    echo "<td style='text-align: center' colspan='1'>" . $row['home_goals'] . " - " . $row['guest_goals'] . "  I  <strong>" . $row['winner'] . "</strong></td>";
    echo "<td style='text-align: center' colspan='3'></td>";

    echo "<td style='text-align: center' colspan='1'>" ?>

        <form action="<?php echo $actual_link; ?>" method="post">
            <label for="inputbet"></label>
            <input type="number" class="form-control" name="<?php echo $row['id']; ?>" list="possibleBets" placeholder="" step="1" min="0" max="2" value="<?php echo $bet; ?>"
                <?php if ($row['start'] < 0) {echo "disabled";}?>>
            <datalist id="possibleBets">
                <option value="0">
                <option value="1">
                <option value="2">
            </datalist>
        </form>

        <?php //!!! bet INPUT
        "</td>";

    echo "<td style='text-align: center' colspan='3'></td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "<div class='col-md-3 col-md-offset-9'>";
echo "<button type='submit' class='btn btn-primary'>Tipps abgeben!</button>";
echo "</div>";
}
else {
?>

<table class="table"> // Tabelle zur Übersicht
    <thead class="thead-inverse">
    <tr>
        <th style="text-align: center" colspan="1">Anstoss</th>
        <th style="text-align: center" colspan="3">Ansetzung</th>
        <th style="text-align: center" colspan="1">Ergebnis</th>
        <?php
        $statement = ("SELECT * FROM " . $db_name . ".user ");
        foreach ($pdo->query($statement) as $row) {
            echo "<th style='text-align: center' colspan='1'>" . $row['username'] . "</th>";
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($md_matches AS $row) {
        echo "<tr>";
        //echo "<td>" . $row['id'] . "</td>";
        echo "<td style='text-align: center' colspan='1'>" . gmdate('d.m.Y - H:i', strtotime($row['start_time'])) . "</td>";
        echo "<td style='text-align: center' colspan='3'>" . $row['home_team'] . " - " . $row['guest_team'] . "</td>";
        echo "<td style='text-align: center' colspan='1'>" . $row['home_goals'] . " - " . $row['guest_goals'] . "  I  <strong>" . $row['winner'] . "</strong></td>";

        $statement = ("SELECT * FROM " . $db_name . ".user ");
        foreach ($pdo->query($statement) as $row) {
            //!!!!!!Eingetragene Tipps
            /*$stat2 = ("SELECT bet FROM ".$db_name.".bet WHERE match_id =" . $row['id'] ." AND user_id =" .$row['id']);
            $bet = $pdo->query($stat2);
    echo    "<th style='text-align: center' colspan='1'>" . $bet . "</th>";*/
            echo "<th style='text-align: center' colspan='1'>" . $row['username'] . "</th>";
        }
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
}
}
elseif(count($md_matches) == 0 && $md_matches !== null) {
    echo "<p class='lead'><em>Keine Spiele gefunden.</em></p>";
}?>


</body>
</html>