<?php
/*
*  ログイン済みユーザのホームページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();

// セッション変数からuser_id取得
if (isset($_SESSION['user_id']) === TRUE) {
    $user_id = $_SESSION['user_id'];
} else {
    // 非ログインの場合、ログインページへリダイレクト
    header('Location: ./ec_top.php');
    exit;
}

// DBに接続
$link = open_db_connection();

// user_idからユーザ名を取得するSQL
$sql = 'SELECT user_name FROM ec_user WHERE user_id = ' . $user_id;
// SQL実行し登録データを配列で取得
$data = get_db_data($link, $sql);

// DBの接続解除
close_db_connection($link);

// ユーザ名を取得できたか確認
if (isset($data[0]['user_name'])) {
    // ユーザ名を取得できたら変数に代入
    $user_name = $data[0]['user_name'];
} else {
    // ユーザ名が取得できない場合、ログアウト処理へリダイレクト
    header('Location: ./ec_logout.php');
    exit;
}
 
// ログイン済みユーザのアカウントページ表示
include_once '../../include/view/ec_account_view.php';