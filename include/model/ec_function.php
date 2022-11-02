<?php

// 関数の宣言
/*
    -------------------- MVCモデル全体で使用する関数 --------------------
*/

/**
* DBハンドルを取得しDBに接続開始
* @return obj $link DBハンドル
*/
function open_db_connection() {
    // データベースに接続する
    if (!$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
        die('DB接続エラー: ' . mysqli_connect_error());
    }

    // 文字コードセット
    mysqli_set_charset($link, DB_CHARACTER_SET);

    return $link;
}

/**
* DBとの接続解除
* @param obj $link DBハンドル
*/
function close_db_connection($link) {
    mysqli_close($link);
}

/**
* トランザクションを開始する
* @param obj $link DBハンドル
*/
function start_transaction($link) {
    mysqli_autocommit($link, false);    // トランザクション開始(オートコミットをオフ）
}

/**
* トランザクションの成否判定を行い、終了する
* @param str $link DBハンドル
* @param array $err_msg_sql SQLエラーを格納した配列
* @return bool トランザクションの成否判定
*/
function end_transaction($link, $err_msg_sql) {
    // トランザクション成否判定
    if (count($err_msg_sql) === 0) {
        // 成功時は処理を確定させる
        mysqli_commit($link);
        return TRUE;
    } else {
        // 失敗時は処理を取り消しロールバックさせる
        mysqli_rollback($link);
        return FALSE;
    }
}

/**
* 特殊文字をHTMLエンティティに変換する
* @param str $string 特殊文字を含んだ文字列
* @return str 特殊文字をHTMLエンティティに変換した文字列
*/
function to_entity($string) {
    return htmlspecialchars($string, ENT_QUOTES, HTML_CHARACTER_SET);
}

/**
* Cookieを無効化する
* @param str $cookie_name クッキーの名前
*/
function unset_cookie($cookie_name) {
    // sessionに利用しているCookieの有効期限を過去に設定することで無効化する
    setcookie($cookie_name, '', time() - 70000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
    );
}

/**
 * リクエストメソッドを取得する
 * @return str GET/POST/PUTなど
 */
function get_request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * POSTデータを取得する
 * @param str $key 配列キー
 * @return str POSTデータの値
 */
function get_post_data($key) {
    $str = '';
    if (isset($_POST[$key]) === TRUE) {
        $str = $_POST[$key];
    }
    return $str;
}

/**
* 商品データテーブルから商品データを取得する
* @param obj $link DBハンドル
* @param str $sql SQL文
* @return array $data 商品一覧データ（連想配列）
*/
function get_db_data($link, $sql) {
    $data = []; // テーブルデータ格納用配列 

    // SQLを実行する
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_array($result)) {
            // DBデータを1行ずつ配列として格納
            $data[] = $row;
        }
        // 結果セットを解放
        mysqli_free_result($result);
    } else {
        // データベース処理に失敗した場合FALSEを返す
        $data[] = FALSE;
    }

    return $data;
}

/*
    -------------------- 商品管理ページ（ec_manager_item）で使う関数 --------------------
*/

/**
* (ec_manager_item)商品追加フォームの入力内容にエラーが無いか確認する
* @param array $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_insert_item($input) {
    // 変数の宣言
    $err_msg = [];  // エラー文格納用配列

    // 入力エラーの判定を行う
    // 1. 商品名が入力されていない
    if ($input['name'] === NULL || $input['name'] === '') {
        $err_msg[] = '商品名を入力してください';
    }
    // 2. 値段の入力が適切でない
    if ($input['price'] === NULL || $input['price'] === '') {       // a) 空欄である
        $err_msg[] = '値段を入力してください';
    } else if (preg_match("/^[0-9]+$/", $input['price']) !== 1) {   // b) 数字以外が混じっている
        $err_msg[] = '値段は数値形式で入力してください';
    } else if ((int)$input['price'] < 0) {                          // c) 0以下の数値を入力した
        $err_msg[] = '値段は0以上の整数を入力してください';
    }
    // 3. 個数の入力が適切でない
    if ($input['stock'] === NULL || $input['stock'] === '') {     // a) 空欄である
        $err_msg[] = '個数を入力してください';
    } else if (preg_match("/^[0-9]+$/", $input['stock']) !== 1) {  // b) 数字以外が混じっている
        $err_msg[] = '個数は数値形式で入力してください';
    } else if((int)$input['stock'] < 0){                           // c) 0以下の数値を入力した
        $err_msg[] = '個数は0以上の整数を入力してください';
    }
    // 4. 公開ステータスに「1=公開」か「0=非公開」以外の数値が入力されている
    if ($input['public'] !== '0' && $input['public'] !== '1') {
        $err_msg[] = '公開ステータスは「公開」か「非公開」のみ選択できます';
    }
    // 5. 画像ファイルが適切に選択されていない
    if ($input['img_name'] === NULL || $input['img_name'] === '') {                       // a) 未選択である
        $err_msg[] = '画像ファイルを選択してください';
    } else if (preg_match("/^.+?\.[jpg|jpeg|png]/", $input['img_name']) !== 1) { // b) ファイル形式が不適切である
        $err_msg[] = '画像ファイルはJPEGかPNGのみ選択できます';
    }

    return $err_msg;
}

/**
* (ec_manager_item)フォームに入力された新商品の情報をデータベースに追加する
* @param obj $link DBハンドル
* @param obj $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function insert_new_item($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // データベースに新たな商品データを追加する
    // SQLを設定
    $sql = 'INSERT INTO ec_item(name, price, img_name, public, created_date, updated_date)
            VALUES (\'' . $input['name'] . '\', '
                        . $input['price'] . ', \''
                        . $input['img_name'] . '\', '
                        . $input['public'] . ', \''
                        . $input['date'] . '\', \''
                        . $input['date'] . '\')';

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    // 追加された商品のIDを取得する（AUTO_INCREMENTに連動）
    // 商品IDの最大値を取得するSQLを設定
    $sql = 'SELECT MAX(id) AS new_id FROM ec_item';

    // SQL実行
    if ($result = mysqli_query($link, $sql)) {
        // SQL結果を連想配列で取得
        $row = mysqli_fetch_array($result);
        // 追加商品のIDを取得
        $new_id = $row['new_id'];
    } else {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }
    // 結果セットを解放する
    mysqli_free_result($result);

    // 追加商品の在庫データを追加する
    // SQLを設定
    $sql = 'INSERT INTO ec_stock(item_id, stock, created_date, updated_date)
            VALUES ('. $new_id . ', ' . $input['stock'] . ', \'' . $input['date'] . '\', \'' . $input['date'] . '\')';

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/**
* (ec_manager_item)個数変更フォームの入力内容にエラーが無いか確認する
* @param array $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_update_item($input) {
    // 変数の宣言
    $err_msg = [];  // エラー文格納用配列

    // 個数の入力にエラーが無いか判定を行う
    if ($input['new_num'] === NULL || $input['new_num'] === '') {   // a) 空欄である
        $err_msg[] = '個数を入力してください';
    } else if (preg_match("/^[0-9]+$/", $input['new_num']) !== 1) { // b) 数字以外が混じっている
        $err_msg[] = '個数は数値形式で入力してください';
    } else if ((int)$input['new_num'] < 0) {                        // c) 0以下の数値を入力した
        $err_msg[] = '個数は0以上の整数を入力してください';
    }

    return $err_msg;
}

/**
* (ec_manager_item)データベース上の商品の在庫情報を更新する
* @param obj $link DBハンドル
* @param obj $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function update_stock($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // 在庫テーブルのデータを更新する
    // SQLを設定
    $sql = 'UPDATE ec_stock SET stock = ' . $input['new_num'] . ', updated_date = \'' . $input['date'] . '\' 
            WHERE item_id = ' . $input['update_id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    // 商品一覧テーブルの更新日時を更新する
    // SQLを設定
    $sql = 'UPDATE ec_item SET updated_date = \'' . $input['date'] . '\' 
            WHERE id = ' . $input['update_id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/**
* (ec_manager_item)商品の公開ステータスを切り替える
* @param obj $link DBハンドル
* @param obj $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function switch_public($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // 商品一覧テーブルの公開ステータスを切り替える
    // SQLを設定
    if ((int)$input['public'] === 0) {
        // 「非公開 → 公開」ボタンが押された場合は「公開」に変更
        $sql = 'UPDATE ec_item SET public = 1 WHERE id = ' . $input['update_id'];
    } else if ((int)$input['public'] === 1) {
        // 「公開 → 非公開」ボタンが押された場合は「非公開」に変更
        $sql = 'UPDATE ec_item SET public = 0 WHERE id = ' . $input['update_id'];
    }

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/**
* (ec_manager_item)データベース上の商品データを1件削除する
* @param obj $link DBハンドル
* @param obj $input 削除対象商品のデータをまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function delete_item($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // 在庫テーブルの商品データを削除する
    // SQLを設定
    $sql = 'DELETE FROM ec_stock WHERE item_id = ' . $input['id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    // 商品一覧テーブルのデータを削除する
    // SQLを設定
    $sql = 'DELETE FROM ec_item WHERE id = ' . $input['id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/*
    -------------------- 商品一覧ページ（ec_index）で使う関数 --------------------
*/

/**
* (ec_index)「カートに入れる」ボタンを押された商品が購入可能な状態か判定する
* @param array $input 選択された商品のデータ（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_index($input) {
    // 変数の宣言
    $err_msg = [];  // エラー文格納用配列

    // 「カートに入れる」ボタンを押された商品が購入可能か判定する
    // 1. 公開ステータスが「公開」になっていない
    if ((int)$input['public'] !== 1) {
        $err_msg[] = '公開ステータスが「公開」になっていない商品はカートに入れられません';
    }
    // 2. 在庫が1以下 = 売り切れている
    if ((int)$input['stock'] < 1) {
        $err_msg[] = '選択した商品が既に在庫切れのためカートに入れられませんでした';
    }

    return $err_msg;
}

/**
* (ec_index)「カートに入れる」ボタンが押された商品をカートテーブルに追加する
* @param obj $link DBハンドル
* @param obj $input カート追加内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function insert_cart($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列 

    // ショッピングカートテーブルに商品データを追加する
    // SQLを設定
    $sql = 'INSERT INTO ec_cart(user_id, item_id, amount, created_date, updated_date)
            VALUES (' . $input['user_id'] . ', '
                      . $input['id'] . ', '
                      . $input['amount'] . ', \''
                      . $input['date'] . '\', \''
                      . $input['date'] . '\')';

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/*
    -------------------- ショッピングカートページ（ec_cart）で使う関数 --------------------
*/

/**
* (ec_cart)個数変更フォームの入力内容にエラーが無いか確認する
* @param array $input フォーム入力内容をまとめた配列（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_update_cart($input) {
    // 変数の宣言
    $err_msg = [];  // エラー文格納用配列

    // 個数の入力にエラーが無いか判定を行う
    if ($input['new_num'] === NULL || $input['new_num'] === '') {   // a) 空欄である
        $err_msg[] = '個数を入力してください';
    } else if (preg_match("/^[0-9]+$/", $input['new_num']) !== 1) { // b) 数字以外が混じっている
        $err_msg[] = '個数は数値形式で入力してください';
    } else if ((int)$input['new_num'] < 1) {                        // c) 1以下の数値を入力した
        $err_msg[] = '個数は1以上の整数を入力してください';
    }

    return $err_msg;
}

/**
* (ec_cart)カートに入っている商品の情報を更新する
* @param obj $link DBハンドル
* @param obj $input 更新内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function update_cart($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // 在庫テーブルのデータを更新する
    // SQLを設定
    $sql = 'UPDATE ec_cart SET amount = ' . $input['new_num'] . ', updated_date = \'' . $input['date'] . '\' 
            WHERE item_id = ' . $input['update_id'] . ' AND user_id = ' . $input['user_id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/**
* (ec_cart)ショッピングカート上の商品データを1件削除する
* @param obj $link DBハンドル
* @param obj $input 削除対象商品のデータをまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function delete_cart($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列

    // ショッピングカートテーブルの商品データを削除する
    // SQLを設定
    $sql = 'DELETE FROM ec_cart 
            WHERE item_id = ' . $input['delete_id'] . ' AND user_id = ' . $input['user_id'];

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}

/**
* (ec_cart)「購入」ボタン押下時、ショッピングカートの商品がすべて購入可能な状態か判定する
* @param array $input ショッピングカート一覧と在庫情報テーブルを結合したデータ（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_purchase($input) {
    // 変数の宣言
    $err_msg = [];  // エラー文格納用配列

    // 「カートに入れる」ボタンを押された商品すべてが購入可能か判定する
    foreach ($input as $value) {
        // 1. 商品の数に関するエラー
        if ((int)$value['stock'] === 0) {                           // a) 売り切れている
            $err_msg[] = '売り切れ中の商品が選択されています：【' . $value['name']. '】';
        } else if ((int)$value['amount'] > (int)$value['stock']) {  // b) カートの点数が在庫数より多い
            $err_msg[] = '注文数が在庫数より多い商品があります：【' . $value['name']. '】';
        }
        // 2. 公開ステータスが「非公開」の商品がカートに入っている
        if ((int)$value['public'] !== 1) {
            $err_msg[] = '非公開の商品は購入することができません: 【' . $value['name'] . '】';
        }
    }

    return $err_msg;
}

/*
    -------------------- ユーザ登録ページ（ec_register）で使う関数 --------------------
*/

/**
* (ec_register)ユーザ登録フォームに入力されたユーザ名とパスワードが使用可能か確認する
* @param array $input ユーザ登録フォームの入力データ（連想配列）
* @return array $err_msg エラー文を格納した配列
*/
function check_submit_correctness_register($input) {
    // 変数の宣言
    $err_msg = [];          // エラー文格納用配列

    // ユーザ登録フォームに入力されたユーザ名とパスワードの書式を確認する
    // 1. ユーザ名に関するエラー
    if ($input['user_name'] === NULL || $input['user_name'] === '') {       // a) 空欄である
        $err_msg[] = 'ユーザ名を入力してください';
    } else if (preg_match('/^[a-zA-Z0-9]+$/', $input['user_name']) !== 1) { // b) 全角文字が含まれている
        $err_msg[] = 'ユーザ名には記号を除く半角英数字のみ使用可能です';
    } else if (mb_strlen($input['user_name']) < 6) {                        // c) 文字数が6以下
        $err_msg[] = 'ユーザ名は6文字以上で入力してください';
    } else if ((int)$input['exist_result'] !== 0) {                         // d) 同じユーザ名が既に登録されている
        $err_msg[] = '入力されたユーザ名は既に使用されています';
    }

    // 2. パスワードに関するエラー
    if ($input['password'] === NULL || $input['password'] === '') {         // a) 空欄である
        $err_msg[] = 'パスワードを入力してください';
    } else if (preg_match('/^[a-zA-Z0-9]+$/', $input['password']) !== 1) {  // b) 全角文字が含まれている
        $err_msg[] = 'パスワードには記号を除く半角英数字のみ使用可能です';
    } else if (mb_strlen($input['password']) < 6) {                         // c) 文字数が6以下
        $err_msg[] = 'パスワードは6文字以上で入力してください';
    }

    return $err_msg;
}

/**
* (ec_register)ユーザ一覧テーブルに新しいユーザを追加する
* @param obj $link DBハンドル
* @param obj $input カート追加内容をまとめた配列（連想配列）
* @return array $err_msg_sql SQLエラー文を格納する配列
*/
function insert_new_user($link, $input) {
    // 変数の宣言
    $err_msg_sql = [];  // SQLエラー文を格納する配列 

    // ユーザ一覧テーブルに新しいユーザデータを追加する
    // SQLを設定
    $sql = 'INSERT INTO ec_user(user_name, password, created_date, updated_date)
            VALUES (\'' . $input['user_name'] . '\',
                    \'' . $input['password'] . '\',
                    \'' . $input['date'] . '\',
                    \'' . $input['date'] . '\')';

    // SQL実行
    if (mysqli_query($link, $sql) !== TRUE) {
        $err_msg_sql[] = 'SQL失敗: ' . $sql;
    }

    return $err_msg_sql;
}