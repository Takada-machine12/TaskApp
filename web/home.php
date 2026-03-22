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
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.13.0/Sortable.min.js"></script>
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
        <div class="container">
            <img src="./image/new york.jpeg" alt="様々な建物が横一列に並んでいる" class="fit-picture" referrerpolicy="same-origin" />
            <h5 class="board-title">タスク管理</h5>
            <div class="board">
                <div class="board-column">
                    <label>本日のタスク</label>
                    <div class="task-container"><!--共通の処理-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column">
                    <label>進行中</label>
                    <div class="task-container"><!--共通の処理-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column">
                    <label>待機中</label>
                    <div class="task-container"><!--共通の処理-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column">
                    <label>完了</label>
                    <div class="task-container"><!--共通の処理-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

                <div class="board-column">
                    <label>明日のタスク</label>
                    <div class="task-container"><!--共通の処理-->
                        <div class="task-item d-flex gap-2 mb-2">
                            <span class="drag-handle" style="cursor: grab;">⠿</span>
                            <input type="text" name="title" class="form-control task-input" placeholder="新規タスク" />
                            <button class="btn btn-sm btn-outline-danger delete-btn">🗑️</button>
                        </div>
                    </div><!--task-container-->
                </div><!--board-column-->

            </div><!--board-->
        </div><!--container-->

        <script>
            //Enterキーで行を追加
            $(document).on('keydown', '.task-input', function(e) {
                //変換中のEnterキーは無視
                if (e.isComposing || e.keyCode === 229) {
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();

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

                //タスクが1行のみの場合は入力内容だけ削除
                if ($container.find('.task-item').length === 1) {
                    $container.find('.task-input').val('');
                } else {
                    //2行目以上の場合は行ごと削除
                    $(this).closest('.task-item').remove();
                }
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
                        //確認用に今だけログ出力
                        console.log('タスクが移動しました。');
                    },
                });
            });
        </script>
    </body>
</html>