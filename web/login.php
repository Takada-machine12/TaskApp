<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>TaskApp</title>
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
                <span class="logo-text">TaskApp</span>
            </div>
        </div>

        <!--ヒーローエリア-->
        <div class="hero">
            <div class="login-card">
                <h5>ログイン</h5>
                <p>アカウントにサインインする</p>

                <form action="home.php" method="post">

                    <label class="form-label-custom">メールアドレス</label>
                    <input type="email" name="email" class="form-control-custom" placeholder="example@mail.com" value="" />

                    <label class="form-label-custom">パスワード</label>
                    <input type="password" name="password" class="form-control-custom" placeholder="●●●●●●●●●" value="" />

                    <input type="checkbox" name="auto_login" class="form-check-input" id="autoLogin" value="1">ログイン状態を保持する
                    <button type="submit" class="btn-login-nav btn-login">ログイン</button>
                </form><!--form-->
            </div>
        </div><!--container-->
    </body>
</html>