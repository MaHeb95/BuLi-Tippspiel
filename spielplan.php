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


$seasonmenu = $matchdaymenu = null;
if (isset($_GET["season"]) && is_numeric($_GET["season"])) {
    $seasonmenu = $_GET["season"];
}


?>

    <script type="text/javascript">
        /**
         * You can have a look at https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/with * for more information on with() function.
         */
        function autoSubmit()
        {
            with (window.document.form) {
                /**
                 * We have if and else block where we check the selected index for Seasonegory(season) and * accordingly we change the URL in the browser.
                 */
                if (season.selectedIndex === 0) {
                    window.location.href = 'spielplan.php';
                } else {
                    window.location.href = 'spielplan.php?season=' + season.options[season.selectedIndex].value;
                }
            }
        }
    </script>
</head>
<body>
<?php
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<form class="form" id="form" name="form" method="post" action="<?php echo $actual_link; ?>">
    <fieldset>
        <p class="bg">
            <label for="season">Wähle eine Saison</label> <!-- Season SELECTION -->
            <!--onChange event fired and function autoSubmit() is invoked-->
            <select id="season" name="season" onchange="autoSubmit();">
                <option value="">-- Wähle eine Saison --</option>
                <?php
                //select Season. Seasons are with parent_id=0
                $sql = "select id,name from soccer_pool.season ";
                $result = dbQuery($sql);
                while ($row = dbFetchAssoc($result)) {
                    echo ("<option value=\"{$row['id']}\" " . ($seasonmenu == $row['id'] ? " selected" : "") . ">{$row['name']}</option>");
                }
                ?>
            </select>
        </p>
        <?php
        //check whether Season was really selected and Season id is numeric
        if ($seasonmenu != '' && is_numeric($seasonmenu)) {
            ////select sub-categories categories for a given Season id
            $sql = "select id,name from soccer_pool.matchday where season_id=" . $seasonmenu;
            $result = dbQuery($sql);
            if (dbNumRows($result) > 0) {
                ?>
                <p class="bg">
                    <label for="matchday">Wähle einen Spieltag</label>
                    <select id="matchday" name="matchday">
                        <option value="">-- Wähle einen Spieltag --</option>
                        <?php
                        //POPULATE DROP DOWN WITH Matchday FROM A GIVEN Season
                        while ($row = dbFetchAssoc($result)) {
                            echo ("<option value=\"{$row['id']}\" " . ($matchdaymenu == $row['id'] ? " selected" : "") . ">{$row['name']}</option>");
                        }
                        ?>
                    </select>
                </p>
                <?php
            }
        }
        ?>
        <p><input name="submit" value="Submit" type="submit" /></p>
    </fieldset>
</form>

<?php
//when submit button is pressed then Season id and Matchday id are displayed to the user
if (isset($_POST['submit'])) {
    if (isset($_POST['matchday'])) {
        $seasonmenu = $_POST['season'];
    }
    if (isset($_POST['matchday']) && is_numeric($_POST['matchday'])) {
        $matchdaymenu = $_POST['matchday'];
    }
    if (isset($_POST['matchday']) && is_numeric($_POST['matchday'])) {
        echo 'Season Id: ' . $seasonmenu . ' -> ' . 'Matchday Id: ' . $matchdaymenu;
    } else if (isset($_POST['matchday'])) {
        echo 'Season Id: ' . $seasonmenu;
    }
}

var_dump(get_matches(get_match_ids($matchdaymenu)));
?>


</body>
</html>