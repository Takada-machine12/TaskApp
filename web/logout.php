<?php
require_once('config.php');
require_once('functions.php');
session_start();

//オートログインのCookieがあれば削除
if (isset($_COOKIE['TASKAPP'])) {
    $auto_login_data = $_COOKIE['TASKAPP'];

    //Cookieを削除
    delete_auto_login($_COOKIE['TASKAPP']);
}

//セッションを完全に破棄する。
$_SESSION = array(); //セッション変数を空にする。

//Cookieの無効化
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/');
}
session_destroy();  //セッション自体を破棄する。
unset($pdo);

//TOPページへリダイレクト
header('Location:'.SITE_URL.'index.php');
exit;
?>