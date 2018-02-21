<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>家計</title>
<script type="text/javascript" src="./script/fixed_midashi.js"></script>
<script type="text/javascript" src="./script/pie_chart.js"></script>
<script type="text/javascript">
 window.onload = function () {
   FixedMidashi.create();
   draw_piechart("graph_1", "tbl_subtotal_1", "total_income");
   draw_piechart("graph_2", "tbl_subtotal_2", "total_expense");
 };
</script>
<link rel="stylesheet" type="text/css" href="./css/common.css">
<link rel="stylesheet" type="text/css" href="./css/total.css">
</head>
<body>
<script type="text/javascript" src="/dd-chrome/ext/vendor/js/jquery/jquery.min.js" charset="utf-8"></script>
<?php
  require './module/dns.php';
  require './module/common.php';
  $user_id = check_session();
?>
<?php
  $month = isset($_GET['month']) ? $_GET['month'] : "";
  // ＧＥＴ引数に意図しない値が設定されている場合は
  // 強制的にログアウトする
  $arg_err = false;
  list($yy, $mm) = explode('-', $month);
  if (checkdate($mm, 1, $yy) === false) {$arg_err = true;}
  if ($arg_err === true) {
    header('Location:login.php?message=bad_request'); exit;
  }
  // データ抽出期間を求める
  $date_start = date('Y-m-d', strtotime($month . '-01'));
  $date_end = date('Y-m-t', strtotime($date_start));
  // ＤＢ接続
  try {
    $db = new PDO($dsn['host'], $dsn['user'], $dsn['password']);
  } catch (PDOException $e) {
    die('Connection Failed: ' . $e->getMessage());
  }
  // 対象月の収入、支出の合計金額および
  // 費目毎の小計金額を求める
  $total_income = 0;
  $total_expense = 0;
  $subtotal_income = array();
  $subtotal_expense = array();
  $sql = 'select himoku_name, himoku_kubun, mst_himoku.himoku_id,
              ifnull(kingaku, 0) as subtotal, color
          from mst_himoku
              left join
                  (select himoku_id, sum(kingaku) as kingaku
                   from data_kakei
                   where (user_id = :user_id) and
                       (date >= :date_start) and (date <= :date_end)
                   group by himoku_id) as query_subtotal on
                  mst_himoku.himoku_id = query_subtotal.himoku_id
          order by sort';
  $st = $db->prepare($sql);
  $st->bindParam(':user_id', $user_id);
  $st->bindParam(':date_start', $date_start);
  $st->bindParam(':date_end', $date_end);
  $st->execute();
  $rows = $st->fetchAll();
  foreach ($rows as $row) {
    if ($row['himoku_kubun'] == 0) {
      $total_income += $row['subtotal'];
      $subtotal_income[] = array($row['himoku_name'], $row['subtotal'], $row['himoku_id'], $row['color']);
    } else {
      $total_expense += $row['subtotal'];
      $subtotal_expense[] = array($row['himoku_name'], $row['subtotal'], $row['himoku_id'], $row['color']);
    }
  }
?>
<div id="contents">
<div id="navi">
  <a id="lnk_logout" href="login.php">ログアウト</a>
</div>
<div class="box_1">
  <a id="lnk_prev" class="g_btn_1"
   href="total.php?month=<?php print date("Y-m", strtotime(htmlentities($date_start) . "-1 month")); ?>"><<</a>
  <span id="lbl_month"><?php print date("Y年m月", strtotime(htmlentities($date_start) ."")); ?></span>
  <a id="lnk_next" class="g_btn_1"
   href="total.php?month=<?php print date("Y-m", strtotime(htmlentities($date_start) . "+1 month")); ?>">>></a>
</div>
<div class="box_1">
  <table id="tbl_total">
    <tr>
      <td>収入</td><td/>
      <td>支出</td><td/>
      <td>収支</td>
    </tr>
    <tr>
      <?php
        print '<td><a id="total_income" href="list.php?himoku_kubun=0&month='.
               htmlentities($month) .'">￥'.
               number_format(htmlentities($total_income)). '</td><td>－</td>';
        print '<td><a id="total_expense" href="list.php?himoku_kubun=1&month='.
               htmlentities($month) .'">￥'.
               number_format(htmlentities($total_expense)). '</td><td>＝</td>';
        print '<td>￥'. number_format(htmlentities($total_income - $total_expense)) .'</td>';
      ?>
    </tr>
  </table>
</div>
<div class="box_1">
<div id="left_box">
  <canvas id="graph_1" width="340" height="240"></canvas>
  <table id="tbl_subtotal_1" class="g_tbl_1" _fixedhead="rows:1; cols:0" style="table-layout: fixed;width: 100%">
    <tr>
      <th>収入</th>
      <th>金額</th>
    </tr>
    <?php
      for ($i = 0; $i< count($subtotal_income); $i++) {
        print '<tr>';
        print '<td><div class="himoku_color" style="background-color:' .
              htmlentities($subtotal_income[$i][3]) . ';"></div>' .
              '<a href="input.php?himoku_kubun=0&himoku_id='.
              htmlentities($subtotal_income[$i][2]) .'">'.
              htmlentities($subtotal_income[$i][0]) .'</a></td>';
        print '<td><a href="list.php'.
              '?himoku_id='. htmlentities($subtotal_income[$i][2]) .
              '&month='. htmlentities($month) .'">￥'.
              number_format(htmlentities($subtotal_income[$i][1])) .'</a></td>';
        print '</tr>';
      }
    ?>
  </table>
</div>
<div id="right_box">
  <canvas id="graph_2" width="340" height="240"></canvas>
  <table id="tbl_subtotal_2" class="g_tbl_1" _fixedhead="rows:1; cols:0" style="table-layout: fixed;width: 100%">
    <tr>
      <th>支出</th>
      <th>金額</th>
    </tr>
    <?php
      for ($i = 0; $i< count($subtotal_expense); $i++) {
        print '<tr>';
        print '<td><div class="himoku_color" style="background-color:' .
              htmlentities($subtotal_expense[$i][3]) . ';"></div>' .
              '<a href="input.php?himoku_kubun=1&himoku_id='.
              htmlentities($subtotal_expense[$i][2]) .'">'.
              htmlentities($subtotal_expense[$i][0]) .'</a></td>';
        print '<td><a href="list.php'.
              '?himoku_id='. htmlentities($subtotal_expense[$i][2]) .
              '&month='. htmlentities($month) .'">￥'.
              number_format(htmlentities($subtotal_expense[$i][1])) .'</a></td>';
        print '</tr>';
      }
    ?>
  </table>
</div>
</div>
<?php
  // 入力画面の登録（または削除）実行後の遷移先画面を設定する
  $_SESSION['return_url'] = 'total.php?month=' . $month;
  // ナビケーションバー［ＨＯＭＥ］押下時の遷移先画面を設定する
  $_SESSION['home_url'] = 'total.php?month=' . $month;
?>
</body>
</html>
