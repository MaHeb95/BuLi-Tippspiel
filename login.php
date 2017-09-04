<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 04.09.17
 * Time: 12:52
 */
session_start();
$pdo = new PDO('mysql:host=localhost; dbname=test', 'root', 'root');

if(isset($_GET['login'])) {
    $username = $_POST['username'];
    $passwort = $_POST['passwort'];

    $statement = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $result = $statement->execute(array('username' => $username));
    $user = $statement->fetch();

    //Überprüfung des Passworts
    if ($user !== false && password_verify($passwort, $user['passwort'])) {
        $_SESSION['userid'] = $user['id'];
        die('Login erfolgreich. Weiter zu <a href="start.php">internen Bereich</a>');
    } else {
        $errorMessage = "Username oder Passwort war ungültig<br>";
    }

}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
    </head>
<body>

<?php
    if(isset($errorMessage)) {
        echo $errorMessage;
    }
?>

<form action="?login=1" method="post">
    Name:<br>
    <input type="username" size="40" maxlength="250" name="username"><br><br>

    Dein Passwort:<br>
    <input type="password" size="40"  maxlength="250" name="passwort"><br>

    <input type="submit" value="Abschicken">
</form>
</body>
</html>