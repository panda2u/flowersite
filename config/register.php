<?php
function goSignUp() {
#include ('config/login.php');
$db = include ('config/db.php');
    # (post['key'], session['key']) 
    setToSessionIfPosted('login', 'uname');
    setToSessionIfPosted('email', 'umail');
    setToSessionIfPosted('user_password', 'upass');

    if (!empty($_SESSION['uname']) && !empty($_SESSION['umail']) && !empty($_SESSION['upass'])) {
        #if such_email available
        $link = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']) or die("There is no link");

        // check email
        $mailquery = mysqli_query($link, "
SELECT user_email FROM ".$db['db'].".users
WHERE user_email = "."'".$_SESSION['umail']."'
LIMIT 1;") or die("mysqli_query died at mailquery");
        $gotmail = mysqli_fetch_row($mailquery);

        // check name
        $namequery = mysqli_query($link, "
SELECT user_name FROM ".$db['db'].".users
WHERE user_name = "."'".$_SESSION['uname']."'
LIMIT 1;") or die("mysqli_query died at namequery");
        $gotname = mysqli_fetch_row($namequery);

        // email or name already exist
        if ($_SESSION['umail'] == $gotmail[0]) {
            $_SESSION['mailExist'] = true;
            #echo "mailExist<br>";
            showForm('SignUp');
            mysqli_close($link);
        }

        elseif ($_SESSION['uname'] == $gotname[0]) {
            $_SESSION['nameExist'] = true;
            #echo "nameExist<br>";
            showForm('SignUp');
            mysqli_close($link);
        }

        // register new user
        elseif (empty($gotmail[0]) && empty($gotname[0]) && !$_SESSION['mailExist'] && !$_SESSION['nameExist']) {
            // email&name are available, writing user to db
            $hashedpass = password_hash($_SESSION['upass'], PASSWORD_BCRYPT);
            try {
                $statement = $link->prepare("
INSERT INTO ".$db['db'].".users (user_name, user_email, user_password)
VALUES (?, ?, ?)");
                $statement->bind_param("sss", $_SESSION['uname'], $_SESSION['umail'], $hashedpass);
                $statement->execute();

    $_SESSION['logged_user_id'] = mysqli_query($link,
"SELECT * FROM ".$db['db'].".users
WHERE user_email = "."'".$_SESSION['umail']."'
 LIMIT 1;") or die("mysqli_query died at logged_user email");
                
                $_SESSION['displayedname'] = $_SESSION['uname'];
                
                // start session cut timer here
                //
                $_SESSION['last_action'] = time();
                
                showForm('LogOut');
                mysqli_close($link);
            } catch (mysqli_sql_exception $e) {
                echo $e;
                mysqli_close($link);
                #session_unset();
                #exit;
            }
        }
    }
}
?>