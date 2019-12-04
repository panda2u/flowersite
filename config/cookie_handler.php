<?php
function setOneCookie() {
  if (isset($_SESSION['displayedname']) && (!isset($_COOKIE['logged_user_id']) || $_COOKIE['logged_user_id'] == '')) {
    $tmp =  $_SESSION['displayedname'];
    setcookie('logged_user_id', $tmp, time() + 3600 + 3 * 3600); // one hour + 3 (GMT)
  } else {
    setcookie('logged_user_id', "", time() -10);
    $_SESSION['showLogin'] = true;
    $_SESSION['isAuthorized'] = false;
    $_SESSION['showLogOut'] = false;
    $_SESSION['showSignUp'] = false;
  }
}
?>