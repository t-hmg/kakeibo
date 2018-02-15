<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>一覧</title>
<script type="text/javascript" src="./script/fixed_midashi.js"></script>
<script type="text/javascript">
    window.onload = function () {FixedMidashi.create();};
</script>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<style type="text/css">
  #tbl_1 {margin-top:10px; width: 100%; table-layout: fixed;}
  #tbl_1 th:nth-child(1) {width: 18%;}
  #tbl_1 th:nth-child(2) {width: 6%;}
  #tbl_1 td:nth-child(2) {text-align: center;}
  #tbl_1 th:nth-child(3) {width: 20%;}
  #tbl_1 th:nth-child(4) {width: 20%;}
  #tbl_1 td:nth-child(4) {text-align: right;}
  #date {text-align: center;}
  #no_data {height: 40px; text-align: left;}
  @media screen and (max-width:640px) {
    #tbl_1 th:nth-child(2), #tbl_1 td:nth-child(2),
    #tbl_1 th:nth-child(5), #tbl_1 td:nth-child(5) {display:none;}
  }
</style>
</head>
<body>
<?php
  require './module/dns.php';
  require './module/common.php';
  $user_id = check_session();
?>
<?php

  $himoku_kubun = isset($_GET['himoku_kubun']) ? $_GET['himoku_kubun'] : "";
  $himoku_id = isset($_GET['himoku_id']) ? $_GET['himoku_id'] : "";
  $month = isset($_GET['month']) ? $_GET['month'] : "";

  // ＧＥＴ引数に意図しない値が設定されている場合は
  // 強制的にログアウトする
  $arg_err = false;
  list($yy, $mm) = explode('-', $month);
  if (checkdate($mm, 1, $yy) === false) {$arg_err = true;}
  if ($arg_err === true) {
    header('Location:login.php?message=bad_request'); exit;
  }

  list($yy, $mm) = explode('-', $month);
  if (checkdate($mm, 1, $yy) === false) {
    header('Location:login.php?message=bad_request'); exit;
  }

  // データ抽出期間を求める
  $date_start = $month . '-01';
  $date_end = date('Y-m-t', strtotime($date_start));

  // ＤＢ接続
  try {
    $db = new PDO($dsn['host'], $dsn['user'], $dsn['password']);
  } catch (PDOException $e) {
    die('Connection Failed: ' . $e->getMessage());
  }

  // データを抽出する
  $sql = 'select kakei_id, date, himoku_name, kingaku, bikou
          from data_kakei
              inner join mst_himoku on
                  data_kakei.himoku_id = mst_himoku.himoku_id
          where (user_id = :user_id) and (date >= :date_start) and (date < :date_end) ';
  if ($himoku_kubun == "") {
    $sql.= 'and (data_kakei.himoku_id = :himoku_id) ';
  } else {
    $sql.= 'and (himoku_kubun = :himoku_kubun) ';
  }
  $sql.= 'order by date desc, kakei_id desc';
  $st = $db->prepare($sql);
  $st->bindParam(':user_id', $user_id);
  if ($himoku_kubun == "") {
    $st->bindParam(':himoku_id', $himoku_id);
  } else {
    $st->bindParam(':himoku_kubun', $himoku_kubun);
  }
  $st->bindParam(':date_start', $date_start);
  $st->bindParam(':date_end', $date_end);
  $st->execute();
  $rows = $st->fetchAll();

?>
<div id="contents">
<div id="navi">
  <a id="lnk_home" href="<?php print $home_url ?>">ＨＯＭＥ</a>
  <a id="lnk_logout" href="login.php">ログアウト</a>
</div>
<table id="tbl_1" class="g_tbl_1" style="float:left;" _fixedhead="rows:1; cols:0">
  <tr>
    <th>日付</th>
    <th>曜</th>
    <th>費目</th>
    <th>金額</th>
    <th>備考</th>
  </tr>
  <?php
    if (count($rows) >= 1) {
      foreach ($rows as $row) {
        print '<tr>';
        print '<td id="date">'. date('Y/m/d', strtotime($row['date'])) .'</td>';
        print '<td>('. $weekday[date('w', strtotime($row['date']))] .')</td>';
        print '<td>'. htmlentities($row['himoku_name']) .'</td>';
        print '<td><a href="input.php?kakei_id='.
               htmlentities($row['kakei_id']) . '">￥'.
               number_format(htmlentities($row['kingaku'])) .'</a></td>';
        print '<td>'. htmlentities($row['bikou']) .'</td>';
        print '</tr>';
      }
    } else {
      print '<td id="no_data" colspan="3">対象のデータがありません</td>';
    }
  ?>
</table>
</div>
</body>
</html>
