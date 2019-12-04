<?php
function goLogout() {
    if (!empty($_SESSION['displayedname'])) {
        unset($_SESSION['displayedname']);
        unset($_POST['hiddenlogout']);
        unset($_SESSION['last_action']);
        showForm('LogIn');

        foreach($_COOKIE as $ind => $value) {
            setcookie($ind, '', time() - 3 * 3601, "/");
            unset($_COOKIE[$ind]);
        }
        
        $_SESSION['isAuthorized'] = 0;
        session_unset();
        session_destroy();
        
        $_SESSION['displayedname'] = 0;
        echo '<meta http-equiv="REFRESH" content="0;url=http://localhost:8080">';
    }
}
?>