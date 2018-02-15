<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>家計簿</title>
<script type="text/javascript">
  function switch_himoku_kubun(){
    var select = document.getElementById('txt_himoku')
    while (0 < select.childNodes.length) {
  		select.removeChild(select.childNodes[0]);
  	}
    if (document.frm_1.rdo_income.checked == true) {
      select.innerHTML = document.frm_1.option_value_1.value;
    } else {
      select.innerHTML = document.frm_1.option_value_2.value;
    }
  }
</script>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<style type="text/css">
  #tbl_1, #tbl_2 {
    width: 350px;}
  #tbl_1 {
    margin-top: 10px;}
  #tbl_2 {
    margin-top: 5px;}
  #tbl_1 td {
    padding: 5px 5px 5px 5px;}
  #tbl_1 tr td:nth-child(1) {
    width: 70px; text-align: center; background:
    linear-gradient(to left, #afafaf, #d1d1d1);}
  #rdo_income {
    margin-left: 10px;}
  #rdo_expense {
    margin-left: 25px;}
  #txt_date {
    height: 20px; width: 150px;}
  #txt_himoku {
    height: 32px; width: 162px;}
  #txt_kingaku {
    height: 20px; width: 150px;}
  #txt_bikou {
    height: 50px; width: 94%; vertical-align: middle;}
  #btn_register {
    height: 40px; width: 100%;}
  #btn_delete {
    height: 40px; width: 100%; color: red;}
  #lbl_error_msg {
    margin: 8px 0 0 5px; color: red ; font-weight: bold;}
</style>
</head>
<body>
<?php
  require './module/dns.php';
  require './module/common.php';
  $user_id = check_session();
?>
<?php

  // ＤＢ接続
  try {
    $db = new PDO($dsn['host'], $dsn['user'], $dsn['password']);
  } catch (PDOException $e) {
    die('Connection Failed: ' . $e->getMessage());
  }

  if (isset($_POST['action'])) {

    // 不正なリクエストが受信した場合は
    // 強制的にログアウトする（CSRF対策）
    if (session_id() != $_POST['token']) {
      header('Location:login.php?message=bad_request'); exit;
    }

    $kakei_id = $_POST['kakei_id'];
    $himoku_kubun = $_POST['himoku_kubun'];
    $date = $_POST['date'];
    $himoku_id = $_POST['himoku_id'];
    $kingaku = $_POST['kingaku'];
    $bikou = $_POST['bikou'];

    // エラーメッセージを初期化する
    $error_msg = '';

    switch ($_POST['action']) {

      // 登録処理
      case 'register':

        // 入力チェック処理
        if (strtotime($date) === false) {
          $error_msg = '日付を入力してください'; break;
        }
        if (is_numeric($himoku_id) === false) {
          $error_msg = '費目を入力してください'; break;
        }
        if (is_numeric($kingaku) === false) {
          $error_msg = '金額を入力してください'; break;
        } elseif ($kingaku < 0) {
          $error_msg = '金額は ￥0 以上で入力してください'; break;
        } elseif ($kingaku > 999999999) {
          $error_msg = '金額は ￥999,999,999 以下で入力してください'; break;
        }
        if (mb_strlen($bikou) > 50) {
          $error_msg = '備考は 50 文字以内で入力してください'; break;
        }

        if ($kakei_id == 0) {

          // 家計IDの採番
          $sql = 'select ifnull(max(kakei_id), 0) + 1 as kakei_id
                  from data_kakei where user_id = :user_id';
          $st = $db->prepare($sql);
          $st->bindParam(':user_id', $_SESSION['user_id']);
          $st->execute();
          $row = $st->fetch(PDO::FETCH_ASSOC);
          $kakei_id = $row['kakei_id'];

          // INSERT文の生成
          $sql = 'insert into data_kakei
                  (user_id, kakei_id, date, himoku_id, kingaku, bikou) values
                  (:user_id, :kakei_id, :date, :himoku_id, :kingaku, :bikou)';

        } else {

          // UPDATE文の生成
          $sql = 'update data_kakei set
                      date = :date, himoku_id = :himoku_id,
                      kingaku = :kingaku, bikou = :bikou
                  where (user_id = :user_id) and (kakei_id = :kakei_id)';

        }
        $st = $db->prepare($sql);
        $st->bindParam(':user_id', $user_id);
        $st->bindParam(':kakei_id', $kakei_id);
        $st->bindParam(':date', $date);
        $st->bindParam(':himoku_id', $himoku_id);
        $st->bindParam(':kingaku', $kingaku);
        $st->bindParam(':bikou', $bikou);
        $st->execute();
        break;

      // 削除処理
      case "delete":

        $sql = 'delete from data_kakei
                where (user_id = :user_id) and (kakei_id = :kakei_id)';
        $st = $db->prepare($sql);
        $st->bindParam(':user_id', $user_id);
        $st->bindParam(':kakei_id', $kakei_id);
        $st->execute();
        break;
    }

    // 登録処理（もしくは削除処理）に成功した場合は
    // 家計画面へ遷移する
    if ($error_msg == '') {
      header("Location:" . $home_url); exit;
    }

  } else {

    if (isset($_GET['kakei_id'])) {

      // 既存データの読み込み
      $sql = 'select kakei_id, himoku_kubun, date, data_kakei.himoku_id, kingaku, bikou
              from data_kakei
                  inner join mst_himoku on
                      data_kakei.himoku_id = mst_himoku.himoku_id
              where (user_id = :user_id) and (kakei_id = :kakei_id)';
      $st = $db->prepare($sql);
      $st->bindParam(':user_id', $user_id);
      $st->bindParam(':kakei_id', $_GET['kakei_id']);
      $st->execute();
      $row = $st->fetch(PDO::FETCH_ASSOC);

      $kakei_id = $row['kakei_id'];
      $himoku_kubun = $row['himoku_kubun'];
      $date = $row['date'];
      $himoku_id = $row['himoku_id'];
      $kingaku = $row['kingaku'];
      $bikou = $row['bikou'];

    } else {

      // 新規データの初期値
      $kakei_id = "0";
      if (isset($_GET['himoku_kubun'])) {
        $himoku_kubun = $_GET['himoku_kubun'];
      } else {
        $himoku_kubun = "1";
      }
      $date = date("Y-m-d");
      if (isset($_GET['himoku_id'])) {
        $himoku_id = $_GET['himoku_id'];
      } else {
        $himoku_id = "";
      }
      $kingaku = "";
      $bikou = "";

    }
  }

  // 費目マスタを区分別に取得する
  $option_value_1 = '<option value=""></option>';
  $option_value_2 = '<option value=""></option>';
  $sql = 'select himoku_kubun, himoku_id, himoku_name
          from mst_himoku
          order by sort';
  $st = $db->prepare($sql);
  $st->bindParam(':himoku_kubun', $himoku_kubun);
  $st->execute();
  $rows = $st->fetchAll();
  foreach ($rows as $row) {
    $value = '<option value="'. htmlentities($row['himoku_id']) .'"';
    if ($himoku_id == $row['himoku_id']) {$value .= ' selected';}
    $value .= '>'. htmlentities($row['himoku_name']) .'</option>';
    if ($row['himoku_kubun'] == 0) {
      $option_value_1 .= $value;
    } else {
      $option_value_2 .= $value;
    }
  }

?>
<div id="contents">
<div id="navi">
  <a id="lnk_home" href="<?php print $home_url ?>">ＨＯＭＥ</a>
  <a id="lnk_logout" href="login.php">ログアウト</a>
</div>
<form name="frm_1" action="input.php" method="post">
<table id="tbl_1" class="g_tbl_1">
  <tr>
    <td>区　分</td>
    <td>
      <input id="rdo_income" name="himoku_kubun" type="radio" value="0"
       onChange="switch_himoku_kubun()"
       <?PHP if ($himoku_kubun == "0") {print "checked";} ?>>収入
      <input id="rdo_expense" name="himoku_kubun" type="radio" value="1"
       onChange="switch_himoku_kubun()"
       <?PHP if ($himoku_kubun == "1") {print "checked";} ?>>支出
    </td>
  </tr>
  <tr>
    <td>日　付</td>
    <td><?php input_text("txt_date", "g_txt_1", "date", $date, "date"); ?></td>
  </tr>
  <tr>
    <td>費　目</td>
    <td><select id="txt_himoku" class="g_txt_1", name="himoku_id">
      <?php
      if ($himoku_kubun == "0") {
        print $option_value_1;
      } else {
        print $option_value_2;
      }
    ?></select>
    </td>
  </tr>
  <tr>
    <td>金　額</td>
    <td><?php input_text("txt_kingaku", "g_txt_1", "kingaku", $kingaku, "number"); ?></td>
  </tr>
  <tr>
    <td>備　考</td>
    <td><?php input_textarea("txt_bikou", "g_txt_1", "bikou", $bikou); ?></td>
  </tr>
</table>
  <table id="tbl_2">
    <tr>
      <?php
        if ($kakei_id != "0") {
          print '<td>';
          print '<button id="btn_delete" class="g_btn_1" type="submit" name="action" value="delete">削除</button>';
          print '</td>';
        }
      ?>
      <td>
        <button id="btn_register" class="g_btn_1" type="submit" name="action" value="register">登録</button>
      </td>
    </tr>
  </table>
  <?php
    if (isset($error_msg)) {
      print '<div id="lbl_error_msg">'. htmlentities($error_msg) .'</div>';
    }
  ?>
  <input type="hidden" name="kakei_id" value="<?php print htmlentities($kakei_id); ?>">
  <input type="hidden" name="token" value="<?php print session_id(); ?>">
  <input type="hidden" id="option_value_1" value="<?php print str_replace('selected', '', htmlentities($option_value_1)); ?>">
  <input type="hidden" id="option_value_2" value="<?php print str_replace('selected', '', htmlentities($option_value_2)); ?>">
</form>
</div>
</body>
</html>
