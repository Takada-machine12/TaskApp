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
            FROM users 
            WHERE email = :email 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":email" => $email));
    $first_user = $stmt->fetch();

    return $first_user ? true : false;
}

//メールアドレスとパスワードからuserを検索する。
function getUser($email, $password, $pdo) {
    $sql = "SELECT * 
            FROM users
            WHERE email = :email
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":email" => $email));
    $login_user = $stmt->fetch();

    //password_verify()でハッシュと照合
    if ($login_user && password_verify($password, $login_user['password'])) {
        return $login_user;
    }

    return false;
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

//オートログイン セットアップ
function setup_auto_login($user_id, $pdo) {
    //ランダムなキーを生成(トークンと同じ)
    $c_key = sha1(uniqid(mt_rand(), true));

    //有効期限を1年後に設定
    $expire = date('Y-m-d H:i:s', time()+3600*24*365);

    //DBに保存
    $sql = "INSERT INTO auto_login (user_id, c_key, expire, created_at, updated_at)
            VALUES (:user_id, :c_key, :expire, now(), now())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id, ":c_key" => $c_key, ":expire" => $expire));

    //ブラウザのCookieにも同じキーを保存
    setcookie('TASKAPP', $c_key, time()+3600*24*365, '/develop/TaskApp/web/');
}

//オートログイン デリート
function delete_auto_login($c_key) {
    //DBから削除
    $pdo = connectDb();
    $sql = "DELETE FROM auto_login WHERE c_key = :c_key";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':c_key' => $c_key));
    unset($pdo);

    //Cookieを削除(有効期限を過去にすることで削除扱いになる。)
    setcookie('TASKAPP', '', time()-86400, '/');
}

//ユーザーIDからuserを検索
function getUserbyUserId($user_id, $pdo) {
    $sql = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id));
    $user = $stmt->fetch();

    return $user ? $user : false;
}
?>