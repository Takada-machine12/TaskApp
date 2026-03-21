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
        <div class="container">
            <h2>ログイン</h2>
            <p>アカウントにサインインする</p>
            <form action="home.php" method="post">
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" class="form-control" placeholder="example@mail.com" value="" />
                    <span class="help-block"></span>
                </div>
                <div class="form-group">
                    <label>パスワード</label>
                    <input type="password" name="password" class="form-control" placeholder="●●●●●●●●●" value="" />
                    <span class="help-block"></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-success btn-block" value="ログイン">
                </div>
            </form><!--form-->
        </div><!--container-->
        <span class="typography_typography__Exx2D footer_copyright__WXbFd">&copy; TKD</span>
    </body>
</html>