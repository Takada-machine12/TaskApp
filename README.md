# TaskApp

## 概要
日々のタスクを簡単に管理できる、そんなツールとなっています。

## 作成経緯
Webの仕組みを実際に手を動かして理解できるようになるために作成しました。

## 機能一覧
- TOPページ（ログイン機能・自動ログイン機能）
- 新規ユーザー登録機能
- タスク登録・編集・削除機能
- ステータス管理機能（ドラッグ&ドロップでタスクを移動）
- ソフトデリート機能（削除ボタンを押すと自動的にゴミ箱に移動）
- 復元機能（30日以内は復元可能。30日経過でDBから自動削除）

## 技術スタック
- フロントエンド
　HTML/CSS, Javascript
- バックエンド
 　PHP
- DB
　MySQL
- ライブラリ/FW
　Bootstrap（CSSフレームワーク）, jQuery, SortableJS
- サーバー
　さくらのレンタルサーバー
- コード管理ツール
　Git
- エディタ
　VSCode
## DB設計
データベース名：taskmanager

**usersテーブル**

| **カラム名** | **型** | **NULL** | **自動採番** | **備考** |
| --- | --- | --- | --- | --- |
| id | int | × | ○ | PK |
| nickname | varchar(30) | × | × | — |
| email | varchar(200) | × | × | UNIQUE |
| password | varchar(255) | × | × | — |
| created_at | datetime | × | × | — |
| updated_at | datetime | ○ | × | — |

**tasksテーブル**

| **カラム名** | **型** | **NULL** | **自動採番** | **備考** |
| --- | --- | --- | --- | --- |
| id | int | × | ○ | PK |
| user_id | int | × | × | FK(users.id) |
| title | varchar(255) | × | × | タスク名 |
| status | enum | × | × | today/doing/waiting/done/tomorrow |
| sort_order | int | × | × | ドラッグ&ドロップの並び順 |
| created_at | datetime | × | × | — |
| updated_at | datetime | ○ | × | — |
| deleted_at | datetime | ○ | × | NULL=未削除 / 日時=削除済み |

**auto_loginテーブル**

| **カラム名** | **型** | **NULL** | **自動採番** | **備考** |
| --- | --- | --- | --- | --- |
| id | int | × | ○ | PK |
| user_id | int | × | × | FK(users.id) |
| c_key | varchar(255) | × | × | — |
| expire | datetime | × | × | — |
| created_at | datetime | × | × | — |
| updated_at | datetime | ○ | × | — |

## 画面構成
- index.php　　TOPページ（アプリ説明 + ログインフォーム）
- signup.php　　新規登録画面
- login.php　　ログイン画面
- home.php　　HOMEページ（ボードビュー）
