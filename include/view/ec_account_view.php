<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ホーム</title>
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
                    <a href="./ec_cart.php"><h3>カートを確認する</h3></a>
                    <a href="./ec_logout.php"><h3>ログアウト</h3></a>
                </div>
                <!-- aタグのfloat解除 -->
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!-- メインコンテンツ部分 -->
    <div class="main-box">
        <h1>アカウントページ</h1>
        <p>ようこそ<?php print $user_name; ?>さん</p>
        <form action="ec_logout.php" method="post">
            <input type="submit" value="ログアウト">
        </form>
    </div>
</body>
</html>