<?php
/*
*  ログイン処理ページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// リクエストメソッド確認
if (get_request_method() !== 'POST') {
   // POSTでなければトップページへリダイレクト
   header('Location: ./ec_top.php');
   exit;
}

// セッション開始
session_start();

// 既にログイン済みの場合、セッション変数にログイン済みフラグを格納
if (isset($_SESSION['user_id']) === TRUE) {
    $_SESSION['login_already_flag'] = TRUE;
    header('Location: ./ec_top.php');
    exit;
}

// POST値取得
$input = array(
    'user_name' => get_post_data('user_name'),  // ユーザ名
    'password' => get_post_data('password')     // パスワード
);

// ユーザ名をCookieへ保存
setcookie('user_name', $input['user_name'], time() + 60 * 60 * 24 * 365);

// DBに接続
$link = open_db_connection();

// ユーザ名とパスワードからuser_idを取得するSQL
$sql = 'SELECT user_id, user_name, password FROM ec_user
        WHERE user_name = \'' . $input['user_name'] . '\' AND password = \'' . $input['password'] . '\'';

// SQLを実行しユーザデータを配列で取得
$user_data = get_db_data($link, $sql);

// DBの接続解除
close_db_connection($link);

// 登録データを取得できたか確認
if (isset($user_data[0]['user_id'])) {
    // セッション変数にuser_idを保存
    $_SESSION['user_id'] = $user_data[0]['user_id'];

    // ID:「admin」 パスワード:「admin」でログインした場合、管理側の商品管理ページに遷移する。
    if ($user_data[0]['user_name'] === 'admin' && $user_data[0]['password'] === 'admin') {
        header('Location: ./ec_manager_item.php');
        exit;
    }

    // ログイン済みユーザのホームページへリダイレクト
    header('Location: ./ec_account.php');
    exit;
} else {
    // セッション変数にログインのエラーフラグを保存
    $_SESSION['login_err_flag'] = TRUE;

    // ログインページへリダイレクト
    header('Location: ./ec_top.php');
    exit;
}