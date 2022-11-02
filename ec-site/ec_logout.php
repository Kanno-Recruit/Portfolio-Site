<?php
/*
*  ログアウト処理ページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();
// セッション名取得 ※デフォルトはPHPSESSID
$session_name = session_name();
// セッション変数を初期化する
$_SESSION = [];
 
// ユーザのCookieに保存されているセッションIDを削除
if (isset($_COOKIE[$session_name])) {
    // sessionに関連する設定を取得
    $params = session_get_cookie_params();

    // Cookieを無効化
    unset_cookie($session_name);
}
 
// セッションIDを無効化
session_destroy();
// ログアウトの処理が完了したらログインページへリダイレクト
header('Location: ./ec_top.php');
exit;