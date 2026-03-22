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
            <div class="d-flex align-items-center gap-4">
                <a href="index.php" class="nav-link-custom">ログアウト</a>
            </div>
        </div>

        <!--ヒーローエリア-->
        <h5>タスク</h5>
        <div class="board">
            <div class="board-column">
                <label>本日のタスク</label>
                <input type="text" name="title" class="form-control" placeholder="新規タスク" />
            </div>

            <div class="board-column">
                <label>進行中</label>
                <input type="text" name="title" class="form-control" placeholder="新規タスク" />
            </div>

            <div class="board-column">
                <label>待機中</label>
                <input type="text" name="title" class="form-control" placeholder="新規タスク" />
            </div>

            <div class="board-column">
                <label>完了</label>
                <input type="text" name="title" class="form-control" placeholder="新規タスク" />
            </div>

            <div class="board-column">
                <label>明日のタスク</label>
                <input type="text" name="title" class="form-control" placeholder="新規タスク" />
            </div>

        </div><!--board-->
    </body>
</html>