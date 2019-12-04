<?php
function feeds_create() {
    $dbs = include ('config/db.php');
    $lnk = mysqli_connect($dbs['host'], $dbs['user'], $dbs['pass']);
    if (!$lnk) {
        die('Could not connect<br>');
    }

    // Trying to set 'le_db' as current database
    $db_selected = mysqli_select_db($lnk, 'le_db');
    if ($db_selected) {
        $chktbl = mysqli_query($lnk, "SELECT created_at FROM le_db.feeds where created_at != 'null' LIMIT 1;");
        if (!$chktbl) {
            $createfeeds = "
CREATE TABLE `le_db`.`feeds` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64),
    `email` VARCHAR(64) NOT NULL,
    `comment` VARCHAR(1024) NOT NULL,
    `created_at` DATE NOT NULL,
    PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = cp1251
COLLATE cp1251_general_ci;
";

            if (mysqli_query($lnk, $createfeeds)) {
                #echo "fl37_feedback table created successfully<br>\n";
            } else {
                #echo 'Error creating fl37_feedback table<br>\n';
            }
        }
        #else echo "fl37_feedback table is already there<br>";
    }

    mysqli_close($lnk);
}
?>