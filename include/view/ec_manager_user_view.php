<!DOCTYPE HTML>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ管理</title>
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
        th, tr, td {
            border: solid black 1px;
            padding: 0px 10px;
        }
        table {
            border: solid black 1px;
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
                    <a href="./ec_manager_item.php"><h3>商品管理ページへ</h3></a>
                    <a href="./ec_logout.php"><h3>ログアウト</h3></a>
                </div>
                <!-- floatの解除 -->
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!-- メインコンテンツ部分 -->
    <div class="main-box">
        <h1>ユーザ管理ページ</h1>
        <!-- 各種メッセージの表示部分 -->
        <!-- SQLエラー -->
        <?php if (count($err_msg_sql) !== 0) { ?>
            <?php foreach ($err_msg_sql as $value) { ?>
                <p style="color: red;"><?php print to_entity($value); ?><p>
            <?php } ?>
        <?php } ?>
        <!-- 登録ユーザの一覧をテーブルで表示します -->
        <!-- 在庫情報と公開ステータスの変更が可能です -->
        <div style="width: 100%; border: 1px solid #000000; margin: 10px 0px;"></div>
        <label for="user_table">登録ユーザ一覧</label>
        <table name="user_table">
            <tr>
                <th>ユーザ名</th>
                <th>登録日時</th>
            </tr>
            <?php foreach ($user_data as $value) { ?>
                <tr>
                    <td><?php print to_entity($value['user_name']); ?></td>
                    <td><?php print to_entity($value['created_date']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>