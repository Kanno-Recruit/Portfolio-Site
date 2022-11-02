<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <style>
        /* ブラウザのデフォルト余白打ち消し */
        body {
            margin: 0px;
            padding: 0px;
        }
        /* メインコンテンツ部分のクラス */
        .main-box {
            width: 100%;
            text-align: center;
        }
        /* 入力フォーム部分のクラス */
        input {
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- メインコンテンツ部分 -->
    <div class="main-box">
        <h1>ECサイト ログインページ</h1>
        <!-- 入力フォーム部分 -->
        <form action="ec_login.php" method="post">
            <label for="user_name">ユーザ名</label>
            <input type="text" id="user_name" name="user_name" value="<?php print $user_name; ?>">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" value="">
            <input type="submit" value="ログイン">
        </form>
        <!-- ユーザ登録ページへ遷移するURL -->
        <p>もしくは</p>
        <p><a href="./ec_register.php">新規ユーザ登録</a></p>
        <!-- 入力エラー表示部分 -->
        <?php if ($login_err_flag === TRUE) { ?>
            <p style="color: red;">ユーザ名またはパスワードが違います</p>
        <?php } ?>
        <?php if ($login_already_flag === TRUE) { ?>
            <p style="color: red;">既にログイン中です</p>
            <p><a href="./ec_account.php">アカウントページへ移動</a></p>
        <?php } ?>
    </div>
</body>
</html>