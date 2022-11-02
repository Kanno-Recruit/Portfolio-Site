<?php
/*
*  商品管理ページ
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

// 商品追加ボタンが押されリダイレクトした状態ならばメッセージ表示処理を実行する
if (isset($_SESSION['success_msg_insert']) === TRUE) {
    // セッションに引き継がせた処理成功メッセージを取得してセッションを破棄
    $success_msg = $_SESSION['success_msg_insert'];
    unset($_SESSION['success_msg_insert']);

    // トランザクションの成否判定フラグをTRUEに変更
    $transaction_result = TRUE;
}

// DBに接続
$link = open_db_connection();

// 変数の宣言
$err_msg = [];      // 入力エラーチェック用配列
$err_msg_sql = [];  // SQLエラーチェック用配列
$item_data = [];    // 商品データ配列

// 各種ボタンが押されたら対応した処理を実行する
if (get_request_method() === 'POST') {
    if (get_post_data('insert')) {
        // 「商品追加」ボタンが押されたら新規商品データの追加処理を実行
        // 変数の宣言
        $input = array(
            'date' => date('Y-m-d H:i:s'),                // データ追加時の日付
            'name' => get_post_data('name'),              // ドリンクの名前
            'price' => get_post_data('price'),            // ドリンクの値段
            'stock' => get_post_data('stock'),            // ドリンクの個数
            'img_name' => $_FILES['userfile']['name'],    // 画像のタイトル
            'public' => get_post_data('public')           // 公開ステータス
        );

        // 画像格納用ディレクトリのパスを指定
        $pass = (string)dirname(__FILE__) . '/images/' . $input['img_name'];

        // 入力エラーの確認をする
        $err_msg = check_submit_correctness_insert_item($input);
        
        if (count($err_msg) === 0) {
            // 入力エラーが0件の場合のみ処理を実行
            // トランザクション開始
            start_transaction($link);

            // 画像格納用ディレクトリに画像データを格納
            move_uploaded_file($_FILES['userfile']['tmp_name'], $pass);
            
            // DBに商品データを追加する
            $err_msg_sql = insert_new_item($link, $input);

            // トランザクション終了
            $transaction_result = end_transaction($link, $err_msg_sql);

            // トランザクション成功時の処理
            if ($transaction_result === TRUE) {
                // リダイレクトを挟むためセクションに成功メッセージを引き継がせる
                $_SESSION['success_msg_insert'] = '新しい商品【' . $input['name'] . '】を追加しました';

                // リロードによる多重入力を回避するためリダイレクトする
                header('Location: ./ec_manager_item.php');
                exit;
            }
        }
    } else if (get_post_data('update_stock')) {
        // 「変更」ボタンが押されたら在庫データ更新処理を実行する
        // 変数の宣言
        $input = array(
            'date' => date('Y-m-d H:i:s'),          // データ更新時の日付
            'update_id' => get_post_data('update_id'),     // 更新対象の商品ID
            'new_num' => get_post_data('new_num')          // 新たな在庫数
        );

        // 入力エラーの確認をする
        $err_msg = check_submit_correctness_update_item($input);

        if (count($err_msg) === 0) {
            // 入力エラーが0件の場合のみ処理を実行
            // トランザクション開始
            start_transaction($link);

            // 更新する商品のデータを取得
            $sql = 'SELECT * FROM ec_item WHERE id = ' . $input['update_id'];
            $data = get_db_data($link, $sql);
            if ($data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

            // 対象商品のデータを配列に挿入
            $input['name'] = $data[0]['name'];

            // DBの在庫データを更新する
            $err_msg_sql = array_merge($err_msg_sql, update_stock($link, $input));
            
            // トランザクション終了
            $transaction_result = end_transaction($link, $err_msg_sql);
            
            // トランザクション成功時はメッセージを表示
            if ($transaction_result === TRUE) {
                $success_msg = '登録商品【' . $input['name'] . '】の個数を【' . $input['new_num']. '個】に変更しました';
            }
        }
    } else if (get_post_data('to_public') || get_post_data('to_non_public')) {
        // 「非公開 → 公開」ボタンが押されたら対象商品の公開ステータスを変更する
        // 変数の宣言
        $input = array(
            'date' => date('Y-m-d H:i:s'),              // データ更新時の日付
            'update_id' => get_post_data('update_id')   // 更新対象の商品ID
        );

        // トランザクション開始
        start_transaction($link);

        // 更新する商品のデータを取得
        $sql = 'SELECT * FROM ec_item WHERE id = ' . $input['update_id'];
        $data = get_db_data($link, $sql);
        if ($data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }
        
        // 対象商品のデータを配列に挿入
        $input['public'] = $data[0]['public'];

        // 商品の公開ステータスを切り替える
        $err_msg_sql = array_merge($err_msg_sql, switch_public($link, $input));

        // トランザクション終了
        $transaction_result = end_transaction($link, $err_msg_sql);

        // トランザクション成功時はメッセージを表示
        if ($transaction_result === TRUE) {
            if ((int)$input['public'] === 0) {
                // 「非公開 → 公開」ボタンが押された場合
                $success_msg = '登録商品【' . $data[0]['name'] . '】の公開ステータスを【公開】に変更しました';
            } else if ((int)$input['public'] === 1) {
                // 「公開 → 非公開」ボタンが押された場合
                $success_msg = '登録商品【' . $data[0]['name'] . '】の公開ステータスを【非公開】に変更しました';
            }
        }
    } else if (get_post_data('delete_item')){
        // 「商品を削除」ボタンが押されたらデータベース更新処理を実行する
        // 変数の宣言
        $input = array(
            'update_id' => get_post_data('update_id')     // 更新対象の商品ID
        );

        // トランザクション開始
        start_transaction($link);

        // 削除する商品のデータを取得
        $sql = 'SELECT * FROM ec_item WHERE id = ' . $input['update_id'];
        $data = get_db_data($link, $sql);
        if ($data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

        // Auto IncrementをリセットするSQL
        // ALTER TABLE `tablename` auto_increment = 1;

        // 対象商品のデータを配列に挿入
        $input['id'] = $data[0]['id'];
        $input['name'] = $data[0]['name'];
        $input['img_name'] = $data[0]['img_name'];

        // 商品データを削除する
        $err_msg_sql = array_merge($err_msg_sql, delete_item($link, $input));
        
        // トランザクション終了
        $transaction_result = end_transaction($link, $err_msg_sql);

        // トランザクション成功時の処理
        if ($transaction_result === TRUE) {
            // 成功メッセージを表示する
            $success_msg = '登録商品【' . $input['name'] . '】のデータを削除しました';

            // ディレクトリに残っている対象商品の画像ファイルを削除する
            // 画像の保存先のパスを指定
            $pass = (string)dirname(__FILE__) . '/images/' . $input['img_name'];
            // 指定したパスのファイルを削除する
            if (unlink($pass) !== TRUE){
                $err_msg[] = '商品画像ファイル【' . $pass . '】の削除に失敗しました';
            }
        }
    }
}
// 商品データを取得する
$sql = 'SELECT ec_item.id, ec_item.name, ec_item.price, ec_stock.stock, ec_item.img_name, ec_item.public
        FROM ec_item
        JOIN ec_stock ON ec_stock.item_id = ec_item.id';

$item_data = get_db_data($link, $sql, $err_msg_sql);
if ($item_data[0] === FALSE) { $err_msg_sql[] = 'SQL失敗: ' . $sql; }

// DBの接続解除
close_db_connection($link);

// HTML部分の読み込み
include_once '../../include/view/ec_manager_item_view.php';