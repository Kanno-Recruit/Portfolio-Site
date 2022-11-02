<?php
/*
*  購入結果ページ
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
$sum = 0;           // ショッピングカートの合計金額

// 決済処理を実行する
// 商品更新用データを設定する
$input = array(
    'date' => date('Y-m-d H:i:s'),      // カート決済時の日付
    'user_id' => $_SESSION['user_id']   // ユーザID
);

// カートに入っている商品一覧データを取得する
// SQL設定
$sql = 'SELECT ec_cart.id, ec_item.id AS item_id, ec_item.name, ec_item.price,
                ec_item.public, ec_cart.amount, ec_stock.stock, ec_item.img_name
        FROM ec_cart
        JOIN ec_item ON ec_cart.item_id = ec_item.id
        JOIN ec_stock ON ec_stock.item_id = ec_item.id
        WHERE ec_cart.user_id = ' . $input['user_id'];

// SQL実行
$purchase_data = get_db_data($link, $sql);
if ($purchase_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

// トランザクション開始
start_transaction($link);

// 購入されたすべての商品の在庫数を更新する
foreach ($purchase_data as $value) {
    // 在庫更新用データを設定する
    $input['update_id'] = $value['item_id'];                // 商品ID（在庫更新）
    $input['delete_id'] = $value['item_id'];                // 商品ID（ショッピングカート削除）
    $input['new_num'] = $value['stock'] - $value['amount']; // 在庫数から注文数を引いた値

    // 商品ごとの支払い金額を合計金額に加算する
    $sum += $value['price'] * $value['amount']; 

    // 在庫データを更新する
    $err_msg_sql = array_merge($err_msg_sql, update_stock($link, $input));

    // ショッピングカートから商品を削除する
    delete_cart($link, $input);
}

// トランザクション終了
$transaction_result = end_transaction($link, $err_msg_sql);

// トランザクション成功時はメッセージを表示
if ($transaction_result === TRUE) {
    $success_msg = array(
        'caption' => '以下の商品を購入しました',
        'price' => 'お支払い金額: ' . $sum . '円'
    );
}

// DBの接続解除
close_db_connection($link);

// HTML部分の読み込み
include_once '../../include/view/ec_result_view.php';