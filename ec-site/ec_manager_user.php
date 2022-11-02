<?php
/*
*  ユーザ管理ページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();

// 「admin」としてログインしていない場合、ログインページへリダイレクト
if (isset($_SESSION['user_id']) !== TRUE) {
    header('Location: ./ec_top.php');
    exit;
} else if ($_COOKIE['user_name'] !== 'admin') {
    header('Location: ./ec_account.php');
    exit;
}

// DBに接続
$link = open_db_connection();

// 変数の宣言
$err_msg_sql = [];  // SQLエラーチェック用配列
$user_data = [];    // 商品データ配列

// 商品データを取得する
$sql = 'SELECT user_name, created_date
        FROM ec_user';

$user_data = get_db_data($link, $sql, $err_msg_sql);
if ($user_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

// DBの接続解除
close_db_connection($link);

// HTML部分の読み込み
include_once '../../include/view/ec_manager_user_view.php';