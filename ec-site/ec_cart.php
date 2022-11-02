<?php
/*
*  ショッピングカートページ
*/

// 設定ファイル読み込み
require_once '../../include/conf/ec_const.php';
// 関数ファイル読み込み
require_once '../../include/model/ec_function.php';

// セッション開始
session_start();

// 非ログインの場合、ログインページへリダイレクト
if (isset($_SESSION['user_id']) !== TRUE) {
    header('Location: ./ec_top.php');
    exit;
}

// DBに接続
$link = open_db_connection();

// 変数の宣言
$err_msg = [];      // 入力エラーチェック用配列
$err_msg_sql = [];  // SQLエラーチェック用配列
$cart_data = [];    // カート内商品データ用配列
$cart_sum = 0;      // カート内商品の合計金額

// 各種ボタンが押されたら処理を実行する
if (get_request_method() === 'POST') {
    if (get_post_data('update_amount')) {
        // 「変更」ボタンが押されたらカートの個数を更新する
        // 変数の宣言
        $input = array(
            'date' => date('Y-m-d H:i:s'),          // データ更新時の日付
            'update_id' => get_post_data('update_id'),     // 更新対象の商品ID
            'new_num' => get_post_data('new_num')          // 新たな在庫数
        );

        // 入力エラーの確認をする
        $err_msg = check_submit_correctness_update_cart($input);

        if (count($err_msg) === 0) {
            // 入力エラーが0件の場合のみ処理を実行
            // トランザクション開始
            start_transaction($link);

            // ユーザIDをセッションから取得
            $input['user_id'] = $_SESSION['user_id'];

            // 更新したいカート内商品のデータを取得
            $sql = 'SELECT ec_item.name, ec_cart.amount
                    FROM ec_item JOIN ec_cart ON ec_item.id = ec_cart.item_id
                    WHERE ec_cart.item_id = ' . $input['update_id'] . ' AND ec_cart.user_id = ' . $input['user_id'];

            $data = get_db_data($link, $sql);
            if ($data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

            // 更新対象の商品データを配列に挿入
            $input['name'] = $data[0]['name'];

            // DBの在庫データを更新する
            $err_msg_sql = array_merge($err_msg_sql, update_cart($link, $input));
            
            // トランザクション終了
            $transaction_result = end_transaction($link, $err_msg_sql);

            // トランザクション成功時はメッセージを表示
            if ($transaction_result === TRUE) {
                $success_msg = '商品【' . $input['name'] . '】の個数を【' . $input['new_num']. '個】に変更しました';
            }
        }
    } else if (get_post_data('delete_cart')){
        // 「商品を削除」ボタンが押されたらショッピングカート更新処理を実行する
        // 変数の宣言
        $input = array(
            'delete_id' => get_post_data('delete_id')     // 更新対象の商品ID
        );

        // ユーザIDをセッションから取得
        $input['user_id'] = $_SESSION['user_id'];

        // 削除する商品の名前を取得
        $sql = 'SELECT name FROM ec_item WHERE id = ' . $input['delete_id'];
        $data = get_db_data($link, $sql);
        if ($data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

        // 削除する商品の名前を配列に挿入
        $input['name'] = $data[0]['name'];

        // トランザクション開始
        start_transaction($link);

        // 商品データを削除する
        $err_msg_sql = array_merge($err_msg_sql, delete_cart($link, $input));

        // トランザクション終了
        $transaction_result = end_transaction($link, $err_msg_sql);

        // トランザクション成功時はメッセージを表示
        if ($transaction_result === TRUE) {
            $success_msg = 'ショッピングカートから商品【' . $input['name'] . '】を削除しました';
        }
    } else if (get_post_data('purchase')) {
        // 「購入」ボタンが押されたら決済処理を実行する
        // カートに入っている商品一覧データを取得する
        // SQL設定
        $sql = 'SELECT ec_cart.id, ec_item.id AS item_id, ec_item.name, ec_item.price,
                       ec_item.public, ec_cart.amount, ec_stock.stock
                FROM ec_cart
                JOIN ec_item ON ec_cart.item_id = ec_item.id
                JOIN ec_stock ON ec_stock.item_id = ec_item.id
                WHERE ec_cart.user_id = ' . $_SESSION['user_id'];

        // SQL実行
        $purchase_data = get_db_data($link, $sql, $err_msg_sql);
        if ($purchase_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

        // ショッピングカートに入っている商品がすべて購入可能な状態か確認する
        $err_msg = check_submit_correctness_purchase($purchase_data);

        if (count($err_msg) === 0) {
            // カート内のすべての商品が購入可能である場合は購入結果ページへリダイレクト
            header('Location: ./ec_result.php');
        }
    }
}

// ユーザがショッピングカートに入れている商品一覧を取得する
// SQLを設定
$sql = 'SELECT ec_cart.id, ec_cart.item_id, ec_cart.amount, ec_item.name, ec_item.price, ec_item.img_name 
        FROM ec_cart
        JOIN ec_item ON ec_cart.item_id = ec_item.id
        WHERE ec_cart.user_id = ' . $_SESSION['user_id'];

// SQL実行
$cart_data = get_db_data($link, $sql, $err_msg_sql);
if ($cart_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

// DBの接続解除
close_db_connection($link);

// ユーザがショッピングカートに入れている商品の合計金額を取得する
if (count($cart_data) !== 0) {
    foreach ($cart_data as $value) {
        $cart_sum += $value['price'] * $value['amount'];
    }
}

// HTML部分の読み込み
include_once '../../include/view/ec_cart_view.php';