<?php
//データベースに接続する。
function connectDb() {
    $param = 'mysql:dbname='.DB_NAME.';host='.DB_HOST;
    try {
        $pdo = new PDO($param, DB_USER, DB_PASSWORD); //DB接続
        $pdo->query('SET NAMES utf8'); //文字コード指定

        return $pdo;
    } catch (PDOException $error) {
        echo $error->getMessage();

        exit;
    }
}

//メールアドレスの存在チェック
function checkEmail($email, $pdo) {
    $sql = "SELECT * 
            FROM user 
            WHERE email = :email 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":email" => $email));
    $fitst_user = $stmt->fetch();

    return $fitst_user ? true : false;
}

//メールアドレスとパスワードからuserを検索する。
function getUser($email, $password, $pdo) {
    $sql = "SELECT * 
            FROM user 
            WHERE email = :email AND BINARY password = :password 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":email" => $email, ":password" => $password));
    $login_user = $stmt->fetch();

    return $login_user ? $login_user : false;
}

//XSS対策
function xss($original_str) {
    return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");
}

//トークンを発行する処理
function setToken() {
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['sstoken'] = $token;
}

//トークンをチェックする処理
function checkToken() {
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken']) != $_POST['token']) {
        echo '<html>
                <head>
                    <meta charset="utf-8">
                </head>
                <body>
                    不正なアクセスです。
                </body>
            </html>';
        
        exit;
    }
}
?>