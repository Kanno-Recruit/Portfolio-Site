<!DOCTYPE HTML>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ショッピングカート</title>
    <style>
        /* ブラウザのデフォルト余白打ち消し */
        body {
            margin: 0px;
            padding: 0px;
        }
        /* float解除用のクラス */
        .clear {
            clear: both; 
        }
        /* ヘッダー帯部分のクラス */
        .header-wrapper {
            height: 10vh;
        }
        .header-box {
            position: fixed;
            z-index: 10;
            height: 80px;
            width: 100%;
            color: #fff;
            background-color: rgba(80, 126, 164, 0.8);
        }
        .header-box p {
            display: inline-block;
            text-align: left;
            margin: 0 auto;
            line-height: 80px;
        }
        .header-link {
            float: right;
        }
        .header-link a {
            display: inline-block;
            text-decoration: none;
            float: left;
            text-align: center;
            height: 80px;
            width: 200px;
            color: #fff;
        }
        .header-link a:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transition: background-color 0.8s;   
        }
        .header-link h3 {
            margin: 0px;
            line-height: 80px;
        }
        /* メインコンテンツ部分のクラス */
        .main-box {
            padding: 0px 20px;
        }
        /* テーブル表示部分のクラス */
        li {
            padding: 2px 0px;
        }
        th, tr, td {
            border: solid black 1px;
            padding: 0px 10px;
        }
        table {
            border: solid black 1px;
            text-align: center;
        }
        img {
            max-height: 100%;
            max-width: 100%;
        }
        .image-box {
            height: 100px;
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- ヘッダー部分 -->
    <div class="header">
        <div class="header-wrapper">
            <div class="header-box">
                <p style="padding-left: 20px">ログイン中: <?php print $_COOKIE['user_name']; ?>さん</p>
                <!-- ページ遷移用リンクを配置します -->
                <div class="header-link">
                    <a href="./ec_index.php"><h3>商品一覧へ</h3></a>
                    <a href="./ec_account.php"><h3>アカウントページへ</h3></a>
                    <a href="./ec_logout.php"><h3>ログアウト</h3></a>
                </div>
                <!-- aタグのfloat解除 -->
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!-- メインコンテンツ部分 -->
    <div class="main-box">
        <h1>ショッピングカートページ</h1>
        <!-- 各種メッセージの表示部分 -->
        <!-- 「カートに追加」ボタンによって商品が追加された場合のメッセージ -->
        <?php if (isset($_SESSION['cart_insert_msg']) === TRUE) { ?>
            <p style="color: blue;"><?php print to_entity($_SESSION['cart_insert_msg']); ?><p>
            <!-- カート追加メッセージを表示したらセッションを破棄する -->
            <?php unset($_SESSION['cart_insert_msg']); ?>
        <?php } ?>
        <!-- トランザクション成功 -->
        <?php if ($transaction_result === TRUE) { ?>
            <p style="color: blue;"><?php print to_entity($success_msg); ?><p>
        <?php } ?>
        <!-- SQLエラー -->
        <?php if (count($err_msg_sql) !== 0) { ?>
            <?php foreach ($err_msg_sql as $value) { ?>
                <p style="color: red;"><?php print to_entity($value); ?><p>
            <?php } ?>
        <?php } ?>
        <!-- フォーム入力・処理実行に関するエラー -->
        <?php if (count($err_msg) !== 0) { ?>
            <?php foreach ($err_msg as $value) { ?>
                <p style="color: red;"><?php print to_entity($value); ?><p>
            <?php } ?>
        <?php } ?>
        <!-- カート内商品一覧の表示部分 -->
        <?php if (count($cart_data) === 0) { ?>
            <!-- カートに1個も商品が登録されていない場合はメッセージを表示 -->
            <p>カートに登録されている商品はありません</p>
        <?php } else { ?>
            <!-- カートに1個以上商品が登録されている場合は一覧テーブルを表示 -->
            <!-- 個数の変更が可能です -->
            <label for="cart_table">カートに入っている商品一覧</label>
            <table name="cart_table">
                <tr>
                    <th>商品画像</th>
                    <th>商品名</th>
                    <th>値段</th>
                    <th>個数</th>
                    <th>商品の削除</th>
                </tr>
                <?php foreach ($cart_data as $value) { ?>
                    <tr>
                        <td>
                            <div class="image-box">
                                <img src="images/<?php print to_entity($value['img_name']); ?>" alt="<?php print to_entity($value['name']); ?>">
                            </div>
                        </td>
                        <td><?php print to_entity($value['name']); ?></td>
                        <td><?php print to_entity($value['price']); ?>円</td>
                        <td>
                            <form action="ec_cart.php" method="POST">
                                <input type="hidden" name="update_id" value="<?php print to_entity($value['item_id']); ?>">
                                <input type="number" name="new_num" min="1" value="<?php print to_entity($value['amount']); ?>">個
                                <input type="submit" name="update_amount" size="10" value="変更">
                            </form>
                        </td>
                        <td>
                            <form action="ec_cart.php" method="POST">
                                <input type="hidden" name="delete_id" value="<?php print to_entity($value['item_id']); ?>">
                                <input type="submit" name="delete_cart" value="商品を削除する">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <p style="color: blue;">合計金額: <?php print to_entity($cart_sum); ?>円</p>
            <!-- 「購入」ボタン部分 -->
            <form action="ec_cart.php" method="post">
                <input type="submit" name="purchase" value="■□■購入■□■">
            </form>
        <?php } ?>
    </div>
</body>
</html>