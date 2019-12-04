<?php
function db_create_if_none() {
    $db = include ('config/db.php');
    $lnk = mysqli_connect($db['host'], $db['user'], $db['pass']);
    #if ($lnk) { echo "Got link? Yes.<br>"; }
    if (!$lnk) { die('<br>Could not connect to db<br>'); }

    // Trying to set 'h402037_db' as current database
    $db_selected = mysqli_select_db($lnk, $db['db']);
    #echo "db_selected? ".(($db_selected) ? 'Yes<br>' : 'No<br>');
    if (!$db_selected) {
        #echo "\$db_select: db either doesn't exist, or it is unreachable.<br>";
        $create = "CREATE SCHEMA `le_db` DEFAULT CHARACTER SET cp1251;";
        if (mysqli_query($lnk, $create)) {
            #echo "Database h402037_db successfully created<br>\n";
            include ('config/createusers.php');
            users_create();
            include ('config/createfeeds.php');
            feeds_create();
        } else {
            #echo 'Error creating database<br>\n';
        }
    }
    mysqli_close($lnk);
}
?>