<?php
function users_create() {
    $dbs = include ('config/db.php');
    $lnk = mysqli_connect($dbs['host'], $dbs['user'], $dbs['pass']);
    if (!$lnk) {
        die('<br>Could not connect<br>');
    }

    // Trying to set 'le_db' as current database
    $db_selected = mysqli_select_db($lnk, 'le_db');
    if ($db_selected) {
        //if db exists check dose not work for me.
        $chktbl = mysqli_query($lnk, "SELECT user_id FROM le_db.users where user_id != 'null' LIMIT 1;");
        if (!$chktbl) {
            $createusers = "
CREATE TABLE `le_db`.`users` (
    `user_id` INT NOT NULL AUTO_INCREMENT,
    `user_name` VARCHAR(64) NOT NULL,
    `user_email` VARCHAR(64) NOT NULL,
    `user_password` VARCHAR(72) NOT NULL,
PRIMARY KEY (`user_id`),
UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC),
UNIQUE INDEX `user_name_UNIQUE` (`user_name` ASC),
UNIQUE INDEX `user_email_UNIQUE` (`user_email` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = cp1251;
";

            if (mysqli_query($lnk, $createusers)) {
                echo "<br>users table created successfully<br>\n";
            } else {
                #echo 'Error creating table<br>\n';
            }
        }

        #else echo "table is already there<br>";
    }

    mysqli_close($lnk);
}
?>