<!DOCTYPE HTML>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧</title>
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
            width: 180px;
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
            margin: auto;
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
                    <a href="./ec_cart.php"><h3>カートを確認する</h3></a>
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
        <h1>商品一覧ページ</h1>
        <!-- 各種メッセージの表示部分 -->
        <!-- カート追加に関するエラー -->
        <?php if (count($err_msg) !== 0) { ?>
            <?php foreach ($err_msg as $value) { ?>
                <p style="color: red;"><?php print to_entity($value); ?><p>
            <?php } ?>
        <?php } ?>
        <!-- SQLエラー -->
        <?php if (count($err_msg_sql) !== 0) { ?>
            <?php foreach ($err_msg_sql as $value) { ?>
                <p style="color: red;"><?php print to_entity($value); ?><p>
            <?php } ?>
        <?php } ?>
        <!-- 登録一覧表示部分 -->
        <table>
            <tr>
            <?php foreach ($item_data as $value) { ?>
                <?php if ((int)$value['public'] === 1) { ?>
                    <!-- 公開ステータスが「1」=TRUEの商品だけ表示する -->
                    <td>
                        <div class="image-box">
                            <img src="images/<?php print to_entity($value['img_name']); ?>" alt="<?php print to_entity($value['name']); ?>">
                        </div>
                        <p><?php print to_entity($value['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><?php print to_entity($value['price'], ENT_QUOTES, 'UTF-8'); ?>円</p>
                        <?php if ((int)$value['stock'] > 0) { ?>
                            <!-- 在庫数が1以上であれば「カートに追加」ボタンを表示する -->
                            <p>
                                <form action="./ec_index.php" method="POST">
                                    <input type="hidden" name="selected_id" value="<?php print to_entity($value['id']); ?>">
                                    <input type="submit" name="into_cart" value="■□■カートに入れる■□■">
                                </form>
                            </p>
                        <?php } else if ((int)$value['stock'] < 1){ ?>
                            <!-- 在庫数が1以下の場合は「売り切れ」と表示する -->
                            <p style="color: red;">売り切れ</p>
                        <?php } ?>
                    </td>
                <?php } ?>
            <?php } ?>
            </tr>
        </table>
    </div>
</body>
</html>