<?php
require_once('config.php');
require_once('functions.php');
session_start();

//セッションチェック
if (!isset($_SESSION['USER'])) {
    header('Location:'.SITE_URL.'index.php');
    exit;
}

//セッション情報を取得
$user = $_SESSION['USER'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //CSRF対策
    setToken();

    $pdo = connectDb();

    //古いゴミを物理削除
    $sql_delete = "DELETE FROM tasks
                   WHERE deleted_at < now() - INTERVAL 30 DAY
                   AND user_id = :user_id";
    $stmt = $pdo->prepare($sql_delete);
    $stmt->execute(array(":user_id" => $user['id']));

    //１.通常のタスク処理
    $sql = "SELECT * FROM tasks
            WHERE user_id = :user_id
            AND deleted_at IS NULL
            ORDER BY sort_order ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user['id']));
    $tasks = $stmt->fetchAll();

    //２.ゴミ箱内のタスク取得処理
    $sql_trash = "SELECT * FROM tasks
                  WHERE user_id = :user_id
                  AND deleted_at IS NOT NULL
                  AND deleted_at > now() - INTERVAL 30 DAY
                  ORDER BY deleted_at DESC";
    $stmt = $pdo->prepare($sql_trash);
    $stmt->execute(array(":user_id" => $user['id']));
    $trash_tasks = $stmt->fetchAll();

    unset($pdo);
} else {
    //CSRF対策
    checkToken();

    //変数定義
    $user_id = $user['id'];

    $error = array();

    //タスクのステータス更新処理
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        //変数定義(Javascriptから受け取る。)
        $task_id = $_POST['task_id'];
        $status = $_POST['status'];
        $task_ids = $_POST['task_ids']; //Javascriptから受け取った配列

        $pdo = connectDb();
        $sql = "UPDATE tasks SET status = :status, updated_at = now()
                WHERE id = :task_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":status" => $status, ":task_id" => $task_id, ":user_id" => $user['id']));

        //sort_order更新(移動先カラムの全タスク)
        //$task_ids = ['3', '7', '1', '5'] → インデックスがそのままsort_order
        foreach($task_ids as $order => $id) {
            $sql_order = "UPDATE tasks SET sort_order = :sort_order, updated_at = now()
                          WHERE id = :id
                          AND user_id = :user_id";
            $stmt = $pdo->prepare($sql_order);
            $stmt->execute(array(":sort_order" => $order, ":id" => $id, ":user_id" => $user['id']));
        }

        unset($pdo);
        exit;
        //ゴミ箱にタスクを入れる。(論理削除)
    } elseif (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
        $task_id = $_POST['task_id'];
        $pdo = connectDb();
        //deleted_atに現在時刻を入れることで「ゴミ箱入り」とする。
        $sql = "UPDATE tasks SET deleted_at = now(), updated_at = now()
                WHERE id = :task_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":task_id" => $task_id, ":user_id" => $user['id']));
        exit;
        //復元処理(ステータス更新と同時にdeleted_atをNULLに戻す。)
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore_task') {
        $task_id = $_POST['task_id'];
        $status = $_POST['status']; //移動先のカラムのステータス
        $pdo = connectDb();
        $sql = "UPDATE tasks SET status = :status, deleted_at = NULL, updated_at = now()
                WHERE id = :task_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":status" => $status, ":task_id" => $task_id, ":user_id" => $user['id']));
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'get_trash') {
        $pdo = connectDb();
        $sql_trash = "SELECT * FROM tasks
                      WHERE user_id = :user_id
                      AND deleted_at IS NOT NULL
                      AND deleted_at > now() - INTERVAL 30 DAY
                      ORDER BY deleted_at DESC";
        $stmt = $pdo->prepare($sql_trash);
        $stmt->execute(array(":user_id" => $user['id']));
        $trash_tasks = $stmt->fetchAll();
        unset($pdo);

        //JSON形式でJavascriptに返す。
        header('Content-Type: application/json');
        echo json_encode($trash_tasks);
        exit;
    } else {
        //タスク新規登録
        //変数定義(ユーザ入力から受け取る。)
        $title = $_POST['title'];
        $status = $_POST['status'];
        $sort_order = $_POST['sort_order'];

        $pdo = connectDb();
        $sql = "INSERT INTO tasks (user_id, title, status, sort_order, created_at, updated_at)
                VALUES (:user_id, :title, :status, :sort_order, now(), now())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":user_id" => $user_id, ":title" => $title, ":status" => $status, ":sort_order" => $sort_order));

        //新規登録したタスクのIDを取得
        $task_id = $pdo->lastInsertId();

        unset($pdo);

        //JSON形式でIDを返す。
        header('Content-Type: application/json');
        echo json_encode(array("task_id" => $task_id));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HOME | <?php echo SERVER_NAME; ?></title>
        <!--Bootstrap-->
        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <!--自作CSS-->
        <link rel="stylesheet" href="./css/style.css">
        <!--JQuery-->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.13.0/Sortable.min.js"></script>
    </head>
    
    <body>
        <!--ヘッダー-->
        <div class="header">
            <div class="d-flex align-items-center gap-2">
                <div class="logo-icon">T</div>
                <span class="logo-text"><?php echo SERVICE_SHORT_NAME; ?></span>
            </div>
            <div class="d-flex align-items-center gap-4">
                <a href="logout.php" class="nav-link-custom">ログアウト</a>
            </div>
        </div>

        <!--ヒーローエリア-->
        <div class="container">
            <img src="./image/new york.jpeg" alt="様々な建物が横一列に並んでいる" class="fit-picture" referrerpolicy="same-origin" />
            <h5 class="board-title">タスク管理</h5>
            <div class="board">
                <div class="board-column" data-status="today">
                    <label>本日のタスク</label>
                    <div class="task-container"><!--共通の処理-->
                        <?php foreach($tasks as $task): ?>
                            <?php if ($task['status'] === 'today'): ?>
                            <div class="task-item d-flex gap-2 mb-2" data-id="<?php echo $task['id']; ?>">
                                <span class="drag-handle" style="cursor: grab;">⠿</span>
                                <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" value="<?php echo xss($task['title']); ?>" />
                                <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!--新規入力欄-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column" data-status="doing">
                    <label>進行中</label>
                    <div class="task-container"><!--共通の処理-->
                        <?php foreach($tasks as $task): ?>
                            <?php if ($task['status'] === 'doing'): ?>
                            <div class="task-item d-flex gap-2 mb-2" data-id="<?php echo $task['id']; ?>">
                                <span class="drag-handle" style="cursor: grab;">⠿</span>
                                <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" value="<?php echo xss($task['title']); ?>" />
                                <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!--新規入力欄-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column" data-status="waiting">
                    <label>待機中</label>
                    <div class="task-container"><!--共通の処理-->
                        <?php foreach($tasks as $task): ?>
                            <?php if ($task['status'] === 'waiting'): ?>
                            <div class="task-item d-flex gap-2 mb-2" data-id="<?php echo $task['id']; ?>">
                                <span class="drag-handle" style="cursor: grab;">⠿</span>
                                <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" value="<?php echo xss($task['title']); ?>" />
                                <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!--新規入力欄-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column" data-status="done">
                    <label>完了</label>
                    <div class="task-container"><!--共通の処理-->
                        <?php foreach($tasks as $task): ?>
                            <?php if ($task['status'] === 'done'): ?>
                            <div class="task-item d-flex gap-2 mb-2" data-id="<?php echo $task['id']; ?>">
                                <span class="drag-handle" style="cursor: grab;">⠿</span>
                                <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" value="<?php echo xss($task['title']); ?>" />
                                <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!--新規入力欄-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column" data-status="tomorrow">
                    <label>明日のタスク</label>
                    <div class="task-container"><!--共通の処理-->
                        <?php foreach($tasks as $task): ?>
                            <?php if ($task['status'] === 'tomorrow'): ?>
                            <div class="task-item d-flex gap-2 mb-2" data-id="<?php echo $task['id']; ?>">
                                <span class="drag-handle" style="cursor: grab;">⠿</span>
                                <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" value="<?php echo xss($task['title']); ?>" />
                                <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!--新規入力欄-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

            </div><!--board-->
        </div><!--container-->

        <!--ゴミ箱アイコン(右下固定)-->
        <div id="trash-icon">🗑️</div>

        <!--ゴミ箱モーダル-->
        <div id="trash-overlay" class="trash-overlay">
            <div class="trash-modal">
                <div class="trash-modal-header">
                    <h6>ゴミ箱</h6>
                    <button id="trash-close">×</button>
                </div>
                <div class="trash-modal-body">
                    <div id="trash-container">
                        <?php foreach($trash_tasks as $task): ?>
                            <div class="trash-item" data-id="<?php echo $task['id']; ?>" data-status="<?php echo xss($task['status']); ?>">
                                    <span><?php echo xss($task['title']); ?></span>
                                    <button class="restore-btn">復元</button>
                            </div>
                        <?php endforeach; ?>
                    </div><!--trash-container-->
                </div><!--trash-modal-header-->
            </div><!--trash-modal-->
        </div><!--trash-overlay-->

        <script>
            //Enterキーで行を追加
            $(document).on('keydown', '.task-input', function(e) {
                //変換中のEnterキーは無視
                if (e.isComposing || e.keyCode === 229) {
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();

                    const val = $(this).val();

                    if (val !== '') {
                        //どのカラムを取得するか。
                        const status = $(this).closest('.board-column').data('status');
                        //そのカラムの現在のタスク数をソート順として使う。
                        const sort_order = $(this).closest('.task-container').find('.task-item').length;

                        //ここで$taskItemに現在のtask-itemを保存しておく。
                        const $taskItem = $(this).parent();

                        //AjaxでPHPにデータを送信
                        $.post('home.php', {
                            title: val,
                            status: status,
                            sort_order: sort_order,
                            token: '<?php echo xss($_SESSION['sstoken']); ?>'
                        }, function(response) {
                            //PHPから返ってきたtask_idをtask-itemにセット
                            $taskItem.attr('data-id', response.task_id);
                        }, 'json');
                    }

                    //新規タスク行を追加
                    const newItem = `
                                <div class="task-item d-flex gap-2 mb-2">
                                    <span class="drag-handle" style="cursor: grab;">⠿</span>
                                    <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                                    <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                                </div>`;
                    //現在のタスクの直下に追加
                    $(this).parent().after(newItem);
                    //追加した要素のinputにフォーカス
                    $(this).parent().next().find('input').focus();
                }
            });
            //ゴミ箱ボタンで削除処理
            $(document).on('click', '.delete-btn', function() {
                //同じカラム内のタスク欄のみを取得
                const $container = $(this).closest('.task-container');
                const $taskItem = $(this).closest('.task-item');
                const task_id = $taskItem.data('id');

                //data-idがない場合(未登録の新規入力欄)は入力内容のみクリア
                if (!task_id) {
                    if ($container.find('.task-item').length === 1) {
                        $container.find('.task-input').val('');
                    } else {
                        $taskItem.remove();
                    }
                    return;
                }

                //DBに登録済みのタスクはソフトデリート
                $.post('home.php', {
                    action: 'soft_delete',
                    task_id: task_id,
                    token: '<?php echo xss($_SESSION['sstoken']); ?>'
                }, function() {
                    //画面からも削除
                    //タスクが1行のみの場合は入力内容だけ削除
                    if ($container.find('.task-item').length === 1) {
                        $container.find('.task-input').val('');
                        $taskItem.removeAttr('data-id');
                    } else {
                        $taskItem.remove();
                    }
                });
            });
            //ドラッグ&ドロップでタスク行を操作
            // document.querySelectorAll で .task-container をすべて取得し、1つずつ処理
            document.querySelectorAll('.task-container').forEach(function(taskList) {
                Sortable.create(taskList, {
                    group: 'shared', //列を跨いだ移動
                    animation: 150,
                    handle: '.drag-handle', //アイコンを掴んだ時だけドラッグ可能
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        //移動したtask-itemのIDを取得
                        const task_id = $(evt.item).data('id');
                        //移動先のカラムのステータスを取得
                        const new_status = $(evt.item).closest('.board-column').data('status');

                        //移動先カラムの全task-itemを順番通りに取得し、IDの配列を作る。
                        const taskIds = [];
                        $(evt.to).find('.task-item').each(function() {
                            //data-idがあるもの(DBに登録済み)だけを対象にする。
                            const id = $(this).data('id');
                            if (id) {
                                taskIds.push(id);
                            }
                        });

                        //AjaxでPHPにステータス更新を送信
                        $.post('home.php', {
                            action: 'update_status',
                            task_id: task_id,
                            status: new_status,
                            task_ids: taskIds, //配列で送る。
                            token: '<?php echo xss($_SESSION['sstoken']); ?>'
                        })
                    },
                });
            });

            //ゴミ箱アイコンクリックでモーダルを開く。
            $('#trash-icon').on('click', function() {
                //１.Ajaxでゴミ箱の中身を取得
                $.post('home.php', {
                    action: 'get_trash',
                    token: '<?php echo xss($_SESSION['sstoken']); ?>'
                }, function(response) {
                    //２.#trash-containerの中身を一度空にする。
                    $('#trash-container').empty();

                    //３.取得したタスクを1件ずつHTMLとして追加する。
                    if (response.length === 0) {
                        //ゴミ箱が空の場合
                        $('#trash-container').append('<p class="text-center">ゴミ箱は空です。</p>');
                    } else {
                        $.each(response, function(index, task) {
                            //taskは{id: 8, title: "テスト", status: "tomorrow", ...}という形
                            const item = `
                                <div class="trash-item" data-id="${task.id}" data-status="${task.status}">
                                    <span>${task.title}</span>
                                    <button class="restore-btn">復元</button>
                                </div>`;
                            $('#trash-container').append(item);
                        });
                    }

                    //４.モーダルを表示
                    $('#trash-overlay').css('display', 'flex');
                }, 'json'); //PHPからJSON形式で受け取る。
            });

            //×ボタンでモーダルを閉じる。
            $('#trash-close').on('click', function() {
                $('#trash-overlay').hide();
            });

            //オーバーレイクリックでモーダルを閉じる。
            $('#trash-overlay').on('click', function(e) {
                if ($(e.target).is('#trash-overlay')) {
                    $('#trash-overlay').hide();
                }
            });

            //ゴミ箱内のタスクを復元ボタン押下で復元
            $(document).on('click', '.restore-btn', function() {
                const $trashItem = $(this).closest('.trash-item');
                const task_id = $trashItem.data('id');
                const status = $trashItem.data('status');

                $.post('home.php', {
                    action: 'restore_task',
                    task_id: task_id,
                    status: status,
                    token: '<?php echo xss($_SESSION['sstoken']); ?>'
                }, function() {
                    location.reload();
                });
            });
        </script>
    </body>
</html>