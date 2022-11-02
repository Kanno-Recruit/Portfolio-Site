<!DOCTYPE HTML>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品管理</title>
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
                    <a href="./ec_manager_user.php"><h3>ユーザ管理ページへ</h3></a>
                    <a href="./ec_logout.php"><h3>ログアウト</h3></a>
                </div>
                <!-- floatの解除 -->
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!-- メインコンテンツ部分 -->
    <div class="main-box">
        <h1>商品管理ページ</h1>
        <!-- 入力フォーム部分 -->
        <!-- 画像ファイルを送信可能にする場合、enctype="multipart/form-data"とします -->
        <form enctype="multipart/form-data" action="ec_manager_item.php" method="POST">
            <!-- 送信可能な画像データサイズは、100万バイト = 1メガバイトとします -->
            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
            <ul style="list-style: none; padding-left: 0;">
                <li>名前: <input name="name" type="text" /></li>
                <li>値段: <input name="price" type="text" /></li>
                <li>個数: <input name="stock" type="text" /></li>
                <!-- accept でJPEGとPNGのみ選択できるよう制限しています -->
                <li><input name="userfile" type="file" accept="image/png, image/jpeg" value="ファイルを選択" /></li>
                <li>
                    <select name="public" size="1">
                        <option value="0" selected>非公開</option>
                        <option value="1">公開</option>
                        <!-- <option value="test">入力テスト用</option> -->
                    </select>
                </li>
                <li><input type="submit" name="insert" value="■□■□■商品追加■□■□■" /></li>
            </ul>
        </form>
        <!-- 各種メッセージの表示部分 -->
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
        <!-- 登録商品の一覧をテーブルで表示します -->
        <!-- 在庫情報と公開ステータスの変更が可能です -->
        <div style="width: 100%; border: 1px solid #000000; margin: 10px 0px;"></div>
        <h2>商品情報変更</h2>
        <label for="item_table">商品一覧</label>
        <table name="item_table">
            <tr>
                <th>商品画像</th>
                <th>商品名</th>
                <th>値段</th>
                <th>在庫数</th>
                <th>公開ステータス</th>
                <th>商品の削除</th>
            </tr>
            <?php foreach ($item_data as $value) { ?>
                <tr>
                    <td>
                        <div class="image-box">
                            <img src="images/<?php print to_entity($value['img_name']); ?>" alt="<?php print to_entity($value['name']); ?>">
                        </div>
                    </td>
                    <td><?php print to_entity($value['name']); ?></td>
                    <td><?php print to_entity($value['price']); ?>円</td>
                    <td>
                        <form action="ec_manager_item.php" method="POST">
                            <input type="hidden" name="update_id" value="<?php print to_entity($value['id']); ?>">
                            <input type="number" name="new_num" min="0" value="<?php print to_entity($value['stock']); ?>">個
                            <input type="submit" name="update_stock" size="10" value="変更">
                        </form>
                    </td>
                    <td>
                        <form action="ec_manager_item.php" method="POST">
                            <input type="hidden" name="update_id" value="<?php print to_entity($value['id']); ?>">
                            <?php if ((int)$value['public'] === 1) { ?>
                                <!-- 公開ステータスが「公開」の場合 -->
                                <p>公開</p>
                                <input type="submit" name="to_public" value="公開 → 非公開">
                            <?php } else if ((int)$value['public'] === 0) { ?>
                                <!-- 公開ステータスが「非公開」の場合 -->
                                <p>非公開</p>
                                <input type="submit" name="to_non_public" value="非公開 → 公開">
                            <?php } ?>
                        </form>
                    </td>
                    <td>
                        <form action="ec_manager_item.php" method="POST">
                            <input type="hidden" name="update_id" value="<?php print to_entity($value['id']); ?>">
                            <input type="submit" name="delete_item" value="商品を削除する">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>