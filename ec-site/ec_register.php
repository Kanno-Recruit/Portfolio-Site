<?php
/*
*  ユーザ新規登録ページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();

// DBに接続
$link = open_db_connection();

// 変数の宣言
$err_msg = [];      // 入力エラーチェック用配列
$err_msg_sql = [];  // SQLエラーチェック用配列

// POST値取得
$input = array(
    'user_name' => get_post_data('user_name'),  // ユーザ名
    'password' => get_post_data('password'),    // パスワード
    'date' => date('Y-m-d H:i:s')               // 登録処理を実行した日付
);

// 「登録」ボタンが押されたらユーザ登録処理を実行する
if (get_request_method() === 'POST') {
    // 入力されたユーザ名が既に登録されているか確認するSQL
    $sql = 'SELECT EXISTS(SELECT * FROM ec_user WHERE user_name = \'' . $input['user_name'] . '\')
            AS flag';

    // SQLを実行する
    $exist_result = get_db_data($link, $sql);
    if ($exist_result[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

    // 判定結果を配列に格納する
    $input['exist_result'] = $exist_result[0]['flag'];

    // 入力されたユーザ名とパスワードが使用可能であるか確認する
    $err_msg = check_submit_correctness_register($input);

    if (count($err_msg) === 0) {
        // 入力エラーが0件の場合のみテーブル追加処理を実行する
        // トランザクション開始
        start_transaction($link);
        
        // DBに新規ユーザデータを追加する
        $err_msg_sql = insert_new_user($link, $input);

        // トランザクション終了
        $transaction_result = end_transaction($link, $err_msg_sql);

        // トランザクション成功時はメッセージを表示
        if ($transaction_result === TRUE) {
            $success_msg = 'ユーザ登録が完了しました！トップページからログインしてください';
        }
    }
}

// DBの接続解除
close_db_connection($link);

// HTML部分の読み込み
include_once '../../include/view/ec_register_view.php';