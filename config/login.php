<?php
function goLogin() {
$db = include ('config/db.php');
    setToSessionIfPosted('namemail', 'namemail');
    setToSessionIfPosted('login_password', 'upass');

    if (!empty($_SESSION['namemail']) && !empty($_SESSION['upass'])) {
        $link = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']) or die("There is no link");
        
    // Returns db-matching name or email
    // ((string)column_name [user_name, user_email], (mysqli_connect)link, (string)db_name)
    function SelectNameOrEmail($nameOrEmail, $link, $db_name) {
        $mailquery = mysqli_query($link, "
SELECT $nameOrEmail FROM ".$db_name.".users 
WHERE $nameOrEmail = "."'".$_SESSION['namemail']."' 
 LIMIT 1;") or die(" mysqli_query died at nameOrEmail");
        #echo "nameOrEmail: ".$nameOrEmail;
        return mysqli_fetch_row($mailquery)[0];
    }
    
    // Returns db-matching password for name or email
    // ((string)column_name [user_name, user_email], (mysqli_connect)link, (string)db_name)
    function SelectPass($nameOrEmail, $link, $db_name) {
        $passquery = mysqli_query($link, "
SELECT user_password FROM ".$db_name.".users
WHERE $nameOrEmail = "."'".$_SESSION['namemail']."' LIMIT 1;")
        or die("mysqli_query died at SelectPass");
        return mysqli_fetch_row($passquery)[0];
    }
  
    // as email
    $gotmail = SelectNameOrEmail('user_email', $link, $db['db']);
    if ($_SESSION['namemail'] == $gotmail) {
        $gotpassM = SelectPass('user_email', $link, $db['db']);
        if (password_verify($_SESSION['upass'], $gotpassM)) {
        # -logged in by Email

            // session ID
            $_SESSION['logged_user_sess_id'] = mysqli_query($link, "
SELECT * FROM ".$db['db'].".users
WHERE user_email = "."'".$_SESSION['namemail']."'
 LIMIT 1;") or die("mysqli_query died at logged_user");
            
            // getting displayedname
            $bymail = mysqli_query($link, "
SELECT user_name FROM ".$db['db'].".users
WHERE user_email = "."'".$_SESSION['namemail']."' 
 LIMIT 1;") or die("mysqli_query died as email");
        $_SESSION['displayedname'] = mysqli_fetch_row($bymail)[0];
        $_COOKIE['logged_user_id'] = $_SESSION['displayedname'];
            
            // start session cut timer here
            //
            $_SESSION['last_action'] = time();
            
            // switching form visibility
            showForm('LogOut');
            mysqli_close($link);
        }
        else { # wrong password
            $_SESSION['LogPassError'] = "Пароль указан неверно @ email";
            mysqli_close($link);
        }
    }

    // as name
    elseif ($_SESSION['namemail'] == SelectNameOrEmail('user_name', $link, $db['db'])) {

        $gotpassN = SelectPass('user_name', $link, $db['db']);
        if (password_verify($_SESSION['upass'], $gotpassN)) {
        # -logged in by Name
            
            // session ID
        $_SESSION['logged_user_sess_id'] = mysqli_query($link,
"SELECT * FROM ".$db['db'].".users WHERE user_name = "."'".$_SESSION['namemail']."' LIMIT 1;") or die("mysqli_query died at logged_user");
            
        $_SESSION['displayedname'] = $_SESSION['namemail'];
        $_COOKIE['logged_user_id'] = $_SESSION['displayedname'];
            
            // setting session cut timer here
            //
            $_SESSION['last_action'] = time();

            // switching form visibility
            showForm('LogOut');
            
            // calling session cut timer
            //
            #keepAuth(6);
            
            mysqli_close($link);
        }
        else { # wrong password
            $_SESSION['LogPassError'] = "Пароль указан неверно";
            mysqli_close($link);
            showForm('LogIn');
        }
    }
    else {
        $_SESSION['LogPassError'] = "Пользователь не найден";
        mysqli_close($link);
        showForm('LogIn');
    }
}

else {
    showForm('LogIn');
    }
}
?>