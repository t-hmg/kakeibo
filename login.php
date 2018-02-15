<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ログイン</title>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<style type="text/css">
  #tbl_1 {
    margin: 10px 10px 10px 10px;
    height: 30px;}
  #tbl_1 tr {
    height: 50px;}
  #tbl_1 td:nth-child(1) {
    width: 90px;}
  #txt_login, #txt_pwd {
    width: 150px;}
  #btn_login {
     width: 160px; height: 40px;}
  #lbl_message {
    color: red ; font-weight: bold;}
</style>
</head>
<body>
<?php
  require './module/dns.php';
  require './module/common.php';
  session_start();
?>
<?php
  if (isset($_POST['action']) && $_POST['action'] == 'login') {

    // ＤＢ接続
    try {
      $db = new PDO($dsn['host'], $dsn['user'], $dsn['password']);
    } catch (PDOException $e) {
      die('Connection Failed: ' . $e->getMessage());
    }

    // ユーザ認証処理
    $auth = false;
    $sql = 'select count(*) as result from mst_user
            where (user_id = :user_id) and (password = :password) and (state = 0)';
    $st = $db->prepare($sql);
    $st->bindParam(':user_id', $_POST['user_id']);
    $st->bindParam(':password', $_POST['password']);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row['result'] == 1) {
      $auth = true;
    }

    // ユーザ認証に成功した場合は
    // 家計画面へ遷移する
    if ($auth == true) {
      $_SESSION['user_id'] = $_POST['user_id'];
      header("Location:" . $home_url); exit;
    }

  }

  // 必要に応じてメッセージを表示する
  $message = '';
  if (isset($_GET['message'])) {
    switch ($_GET['message']) {
      case 'time_out':
        $message = 'セッションがタイムアウトもしくは無効になりました。<br/>
                    再度ログインしてください。';
        break;
      case 'bad_request':
        $message = '不正なリクエストが送信されたため処理を中止しました。';
        break;
    }
  }

?>
<?php
  session_destroy();
?>
<form name="frm_1" action="login.php" method="post">
  <table id="tbl_1">
    <tr>
      <td>ログインID</td>
      <td>
        <input type="text" id="txt_login" class="g_txt_1" name="user_id">
      </td>
    </tr>
    <tr>
      <td>パスワード</td>
      <td>
        <input type="password" id="txt_pwd" class="g_txt_1" name="password">
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <button type="submit" id="btn_login" class="g_btn_1"  name="action" value="login">ログイン
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span id="lbl_message"><?php print $message; ?></span>
      <td>
    </tr>
  </table>
</form>
</body>
</html>
