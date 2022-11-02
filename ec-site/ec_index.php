<?php
/*
*  商品一覧ページ
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
$item_data = [];    // 商品データ配列

// 各種ボタンが押されたら処理を実行する
if (get_request_method() === 'POST') {
    if (get_post_data('into_cart')) {
        // 「カートに入れる」ボタンが押されたらカートテーブル追加処理を実行する
        // 変数の宣言
        $input = array(
            'date' => date('Y-m-d H:i:s'),                  // データ更新時の日付
            'selected_id' => get_post_data('selected_id')   // 追加商品のID
        );

        // カートに入れる商品のデータを取得する
        $sql = 'SELECT ec_item.id, ec_item.name, ec_item.price, ec_stock.stock, ec_item.public
                FROM ec_item
                JOIN ec_stock ON ec_stock.item_id = ec_item.id
                WHERE ec_item.id = ' . $input['selected_id'];

        $selected_data = get_db_data($link, $sql);
        if ($selected_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

        // 「カートに入れる」ボタンを押した商品が購入可能か確認する
        $err_msg = check_submit_correctness_index($selected_data[0]);

        if (count($err_msg) === 0) {
            // エラーが無い場合のみ処理を実行する
            // トランザクション開始
            start_transaction($link);

            // ユーザIDをセッションから取得
            $input['user_id'] = $_SESSION['user_id'];

            // 対象商品のデータを配列に挿入
            $input['id'] = $selected_data[0]['id'];     // 商品ID
            $input['name'] = $selected_data[0]['name']; // 商品名

            // 「カートに入れる」ボタンを押した商品が既にカート内に存在しているか確認
            $sql = 'SELECT EXISTS(
                        SELECT * FROM ec_cart
                        WHERE user_id = ' . $input['user_id'] . ' AND item_id = ' . $input['id'] . ')
                        AS flag';

            $exist_result = get_db_data($link, $sql);
            if ($exist_result[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }
            
            // ショッピングカートに商品を追加する
            if ((int)$exist_result[0]['flag'] === 0){
                // ショッピングカートに選択商品が存在しない場合、新規で1つ追加する
                // 個数は1に設定
                $input['amount'] = 1;
                // ショッピングカート追加処理
                $err_msg_sql = array_merge($err_msg_sql, insert_cart($link, $input));
            } else if ((int)$exist_result[0]['flag'] === 1){
                // ショッピングカートに選択商品が存在する場合、対象商品の個数を1つ増やす
                // カートの商品データを取得する
                // SQLを設定
                $sql = 'SELECT item_id, amount FROM ec_cart
                        WHERE user_id = ' . $input['user_id'] . ' AND item_id = ' . $input['id'];

                // SQL実行
                $cart_data = get_db_data($link, $sql);
                if ($cart_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

                // カートの更新データを配列に挿入
                $input['update_id'] = $cart_data[0]['item_id'];     // 商品ID
                $input['new_num'] = $cart_data[0]['amount'] + 1;    // 個数

                // ショッピングカート更新処理
                $err_msg_sql = array_merge($err_msg_sql, update_cart($link, $input));
            }

            // トランザクション終了
            $transaction_result = end_transaction($link, $err_msg_sql);

            // トランザクション成功時のみページ遷移を実行
            if ($transaction_result === TRUE) {
                // セッションにトランザクション成功メッセージを保持させる
                $_SESSION['cart_insert_msg'] = '【' . $input['name'] . '】をカートに追加しました';

                // ショッピングカートページへリダイレクト
                header('Location: ./ec_cart.php');
            }
        }
    }
}
// すべての商品データを取得する
// SQLを設定
$sql = 'SELECT ec_item.id, ec_item.name, ec_item.price, ec_stock.stock, ec_item.img_name, ec_item.public
        FROM ec_item
        JOIN ec_stock ON ec_stock.item_id = ec_item.id';

// SQL実行
$item_data = get_db_data($link, $sql, $err_msg_sql);
if ($item_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

// DBの接続解除
close_db_connection($link);

// HTML部分の読み込み
include_once '../../include/view/ec_index_view.php';