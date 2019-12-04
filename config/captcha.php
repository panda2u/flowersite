<?php
function captcha_handler() {
    $db = include ('config/db.php');
    $_SESSION['isCommented'] = false;
    $_SESSION['$captPictures'] = [];

    if (!$_SESSION['isAuthorized']) {
        $i = 1;
        do {
            $num[$i] = mt_rand(0,9);
            $_SESSION['$captPictures'][$i] = "<img src='img/captcha/".$num[$i].".jpg' border='0' align='top' vspace='5px' height='30px' style ='margin: 2px'>";
            $i++;
        } while ($i < 6);
            $captcha = $num[1].$num[2].$num[3].$num[4].$num[5];
    }

    // TODO: Move 4 blocks of code below to a separate function setIfPosted().
    // So we can call setIfPosted('uname', 'user_name');
    if (isset($_POST['user_name'])) {
        $uname = $_POST['user_name'];
        if ($uname == '') {
            unset($uname);
        }
    }

    // TODO: same as above.
    if (isset($_POST['user_email'])) {
        $umail = $_POST['user_email'];
        if ($umail == '') {
            unset($umail);
        }
    }

    // TODO: same as above.
    if (isset($_POST['comment'])) {
        $ucomm = $_POST['comment'];
        if ($ucomm == '') {
            unset($ucomm);
        }
    }

    // TODO: same as above.
    if (isset($_POST['ucaptcha'])) {
        $ucapt = $_POST['ucaptcha'];
        if ($ucapt == '') {
            unset($ucapt);
        }
    }

    if ((!empty($uname) && !empty($umail) && !empty($ucapt) && $_POST['ucaptcha'] == $ucapt && !empty($ucomm)) || 
        ($_SESSION['isAuthorized'] && !empty($ucomm))) {
        try {
            $link = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']) or die("There is no link");

            $statement = $link->prepare("INSERT INTO fl37_feedback(created_at, name, email, comment) VALUES (NOW(), ?, ?, ?)");
            $statement->bind_param("sss", $uname, $umail, $ucomm); // s = string
            $statement->execute(); // Execute the statement.
            $_SESSION['isCommented'] = true;
        } catch (mysqli_sql_exception $e) {
            exit;
        }

        $link->close();
    }

    else {
        //#echo "<br>\nPlease fill the form, all fields are required.";
    }
}
?>