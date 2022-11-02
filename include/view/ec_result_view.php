<!DOCTYPE HTML>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>購入結果</title>
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
            display: inline-block;
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
        .result-box {
            text-align: center;
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
        <h1 style="text-align: center;">購入結果</h1>
        <!-- 各種メッセージの表示部分 -->
        <!-- トランザクション成功 -->
        <?php if ($transaction_result === TRUE) { ?>
            <div class="result-box">
                <p style="color: blue;"><?php print to_entity($success_msg['caption']); ?><p>
                <!-- 購入商品の一覧を表示する -->
                <table>
                    <tr>
                    <?php foreach ($purchase_data as $value) { ?>
                        <!-- 画像、名前、価格、個数をそれぞれ表示する -->
                        <td>
                            <div class="image-box">
                                <img src="images/<?php print to_entity($value['img_name']); ?>" alt="<?php print to_entity($value['name']); ?>">
                            </div>
                            <p><?php print to_entity($value['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><?php print to_entity($value['price'], ENT_QUOTES, 'UTF-8'); ?>円</p>
                            <p><?php print to_entity($value['amount'], ENT_QUOTES, 'UTF-8'); ?>個</p>
                        </td>
                    <?php } ?>
                    </tr>
                </table>
                <p style="color: blue;"><?php print to_entity($success_msg['price']); ?><p>
            </div>
        <?php } ?>
        <!-- 購入処理エラー -->
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
        <!-- 商品一覧ページへ遷移するURL -->
        <p style="text-align: center;"><a href="./ec_index.php">商品一覧ページへ戻る</a></p>
    </div>
</body>
</html>