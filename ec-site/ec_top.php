<?php
/*
*  ログインページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();

// セッション変数からログインエラーフラグを確認し、取得
if (isset($_SESSION['login_err_flag']) === TRUE) {      // 1. ユーザ名またはパスワードが合致しない
    $login_err_flag = $_SESSION['login_err_flag'];
    // エラー表示は1度だけのため、フラグをFALSEへ変更
    $_SESSION['login_err_flag'] = FALSE;
} else if (isset($_SESSION['login_already_flag'])) {    // 2. 既にログイン済みである
    $login_already_flag = $_SESSION['login_already_flag'];
    // エラー表示は1度だけのため、フラグをFALSEへ変更
    $_SESSION['login_already_flag'] = FALSE;
} else {
    // セッション変数が存在しなければエラーフラグはFALSE
    $login_err_flag = FALSE;
}

// Cookie情報からユーザ名を取得
if (isset($_COOKIE['user_name']) === TRUE) {
    $user_name = $_COOKIE['user_name'];
} else {
    $user_name = '';
}

// 特殊文字をHTMLエンティティに変換
$email = to_entity($user_name);

// HTML部分の読み込み
include_once '../../include/view/ec_top_view.php';