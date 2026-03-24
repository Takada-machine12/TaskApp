<?php
require_once('config.php');
require_once('functions.php');
session_start();

//Cookieがあれば自動ログイン処理
if (!empty($_COOKIE['TASKAPP'])) {
    $pdo = connectDb();

    //CookieのキーとDBを照合(有効期限も確認)
    $sql = "SELECT * FROM auto_login WHERE c_key = :c_key AND expire > now()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":c_key" => $_COOKIE['TASKAPP']));
    $auto_login_data = $stmt->fetch();

    if ($auto_login_data) {
        //ユーザー情報を取得してセッションにセット
        $login_user = getUserbyUserId($auto_login_data['user_id'], $pdo);

        $_SESSION['USER'] = $login_user;
        session_regenerate_id(true);
        unset($pdo);
        header('LOcation:'.SITE_URL.'home.php');
        exit;
    }
    unset($pdo);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //初めて画面にアクセスした時の処理
} else {
    //フォームからサブミットされた時の処理
    //入力されたニックネーム、メールアドレス、パスワードを受け取り変数に入れる。
    $email = $_POST['email'];
    $password = $_POST['password'];

    //エラー定義
    $error = array();

    //[メールアドレス]入力/形式チェック
    if ($email == '') {
        $error['email'] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'このメールアドレスは正しくないメールアドレスです。';
    }

    //[パスワード]入力チェック
    if ($password == '') {
        $error['password'] = 'パスワードを入力してください。';
    }

    //上記が問題なければ存在チェックを行う。
    if (empty($error)) {
        //データベースに接続する。
        $pdo = connectDb();

        if (!getUser($email, $password, $pdo)) {
            $error['email'] = 'メールアドレスまたはパスワードが間違っています。';
            unset($pdo);
        } else {
            //すべてのエラーがない場合の処理
            //ログインに成功した場合、セッションにユーザ情報を保存する。
            $login_user = getUser($email, $password, $pdo);
            $_SESSION['USER'] = $login_user;
            $_SESSION['just_logged_in'] = true;

            //自動ログインチェックボックスがONの場合Cookieをクリア
            if (isset($_COOKIE['TASKAPP'])) {
                delete_auto_login($_COOKIE['TASKAPP']);
            }

            //チェックボックスONなら新しくセット
            if (!empty($_POST['auto_login'])) {
                setup_auto_login($login_user['id'], $pdo);
            }

            //ログイン後に毎回セッションIDを書き換える。
            session_regenerate_id(true);

            //HOME画面に遷移
            header('Location: '.SITE_URL.'home.php');
            unset($pdo);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ログイン | <?php echo SERVER_NAME; ?></title>
        <!--Bootstrap-->
        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <!--自作CSS-->
        <link rel="stylesheet" href="./css/style.css">
        <!--JQuery-->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    
    <body>
        <!--ヘッダー-->
        <div class="header">
            <div class="d-flex align-items-center gap-2">
                <div class="logo-icon">T</div>
                <span class="logo-text"><?php echo SERVICE_SHORT_NAME; ?></span>
            </div>
        </div>

        <!--ヒーローエリア-->
        <div class="container">
            <div class="hero">
                <div class="login-card">
                    <h5>ログイン</h5>
                    <p>アカウントにサインインする</p>

                    <form method="post">

                        <div class="form-group <?php echo !empty($error['email']) ? "has-error" : ''; ?>">
                            <label class="form-label-custom">メールアドレス</label>
                            <input type="email" name="email" class="form-control-custom" placeholder="example@mail.com" value="<?php echo xss($email ??''); ?>" />
                            <span class="help-block"><?php if (!empty($error['email'])) echo xss($error['email']); ?></span>
                        </div>

                        <div class="form-group <?php echo !empty($error['password']) ? "has-error" : ''; ?>">
                            <label class="form-label-custom">パスワード</label>
                            <input type="password" name="password" class="form-control-custom" placeholder="●●●●●●●●●" value="" />
                            <span class="help-block"><?php if (!empty($error['password'])) echo xss($error['password']); ?></span>
                        </div>

                        <div class="form-group">
                            <input type="checkbox" name="auto_login" class="form-check-input" id="autoLogin" value="1">
                            <label for="autoLogin">ログイン状態を保持する</label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-login-nav btn-login">ログイン</button>
                        </div>
                    </form><!--form-->
                </div><!--login-card-->
            </div><!--hero-->
        </div><!--container-->
    </body>
</html>