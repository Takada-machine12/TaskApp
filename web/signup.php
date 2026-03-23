<?php
require_once('config.php');
require_once('functions.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //初めて画面にアクセスした時の処理
    //CSRF対策
    setToken();
} else {
    //フォームからサブミットされた時の処理
    //CSRF対策
    checkToken();

    //入力されたニックネーム、メールアドレス、パスワードを受け取り、変数に入れる。
    $nickname = $_POST['nickname'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    //エラー定義
    $error = array();

    //入力チェック
    if ($nickname == '') {
        $error['nickname'] = 'ニックネームを入力してください。';
    } elseif (mb_strlen($nickname) > 30) {
        $error['nickname'] = 'ニックネームは30文字以内で入力してください、';
    }

    //[パスワード]入力チェック
    if ($password == '') {
        $error['password'] = 'パスワードを入力してください。';
    } elseif (mb_strlen($password) > 255) {
        $error['password'] = 'パスワードは255文字以内で入力してください、';
    }

    //[メールアドレス]入力チェック
    if ($email = '') {
        $error['email'] = 'メールアドレスを入力してください。';
    } elseif (mb_strlen($email) > 200) {
        $error['email'] = 'メールアドレスは200文字以内で入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'このメールアドレスは正しくないメールアドレスです。';
    }

    //上記エラーがない場合の処理
    if (empty($error)) {
        //データベースに接続
        $pdo = connectDb();

        //[メールアドレス]存在チェック(重複登録を防ぐため)→DB側でもユニーク制約設定
        if (checkEmail($email, $pdo)) {
            $error['email'] = 'このメールアドレスは既に登録されています。';
            unset($pdo); //エラー時接続を切断
        } else {
            //データベースに新規登録
            $sql = "INSERT INTO user (nickname, password, email, created_at, updated_at)
                    VALUES (:nickname, :password, :email, now(), now())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":nickname" => $nickname, ":email" => $email, ":password" => $password));

            //新規登録者を管理者にメールで通知する。
            mb_language('japanese');
            mb_internal_encoding('UTF-8');

            $to = ADMIN_MAIL_ADDRESS;
            $mail_title = '【TaskApp】新規ユーザー登録がありました。';
            $mail_body = 'ニックネーム:'.$nickname.PHP_EOL;
            $mail_body.= 'メールアドレス:'.$email;

            mb_send_mail($to, $mail_title, $mail_body);

            //自動ログイン
            $first_user = getUser($email, $password, $pdo);
            $_SESSION['USER'] = $first_user;

            unset($pdo);

            //ログイン後に毎回セッションIDを書き換える。
            session_regenerate_id(true);

            //HOME画面に遷移
            header('Location:'.SITE_URL.'home.php');

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
        <title>新規登録 | <?php echo SERVER_NAME; ?></title>
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
                <div class="login-card-signup">
                    <h5>新規登録</h5>

                    <form method="post">
                        
                        <div class="form-group <?php echo !empty($error['nickname']) ? "has-error" : ''; ?>">
                            <label class="form-label-custom">ニックネーム</label>
                            <input type="text" name="nickname" class="form-control-custom" placeholder="ニックネーム" value="<?php echo xss($nickname ??''); ?>" />
                            <span class="help-block"><?php if (!empty($error['nickname'])) echo xss($error['nickname']); ?></span>
                        </div>

                        <div class="form-group <?php echo !empty($error['password']) ? "has-error" : ''; ?>">
                            <label class="form-label-custom">パスワード</label>
                            <input type="password" name="password" class="form-control-custom" placeholder="●●●●●●●●●" value="" />
                            <span class="help-block"><?php if (!empty($error['password'])) echo xss($error['password']); ?></span>
                        </div>

                        <div class="form-group <?php echo !empty($error['email']) ? "has-error" : ''; ?>">
                            <label class="form-label-custom">メールアドレス</label>
                            <input type="email" name="email" class="form-control-custom" placeholder="example@mail.com" value="" />
                            <span class="help-block"><?php if (!empty($error['email'])) echo xss($error['email']); ?></span>
                        </div>

                        <button type="submit" class="btn-signup-nav btn-signup">新規登録</button>
                        <input type="hidden" name="token" value="<?php echo xss($_SESSION['sstoken']); ?>">

                    </form><!--form-->
                </div><!--login-card-->
            </div><!--hero-->
        </div><!--container-->
    </body>
</html>