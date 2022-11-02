<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザ登録</title>
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
    <h1>ECサイト ユーザ登録ページ</h1>
    <?php if (isset($transaction_result) !== TRUE || $transaction_result === FALSE) { ?>
        <!-- 登録処理が完了した時以外は入力フォームを表示する -->
        <!-- 入力フォーム部分 -->
        <form action="ec_register.php" method="post">
            <label for="user_name">ユーザ名</label>
            <input type="text" id="user_name" name="user_name" value="<?php print to_entity($input['user_name']); ?>">
            <label for="password">パスワード</label>
            <input type="text" id="password" name="password" value="<?php print to_entity($input['password']); ?>">
            <input type="submit" value="登録">
        </form>
        <!-- トップページへ遷移するURL -->
        <p><a href="./ec_top.php">トップページへ戻る</a></p>
        <!-- 各種メッセージの表示部分 -->
        <!-- 入力エラー -->
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
    <?php } else if ($transaction_result === TRUE) { ?>
        <!-- 登録処理が完了した時は成功メッセージとトップページ遷移用URLを表示する -->
        <!-- トランザクション成功メッセージ -->
        <p style="color: blue;"><?php print to_entity($success_msg); ?><p>
        <!-- トップページへ遷移するURL -->
        <p><a href="./ec_top.php">トップページへ戻る</a></p>
    <?php } ?>
    </div>
</body>
</html>