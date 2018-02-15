<?php

$home_url = 'total.php?month='. date("Y-m");
$weekday = array( "日", "月", "火", "水", "木", "金", "土" );

function check_session() {
  session_start();
  if (isset($_SESSION['user_id'])) {
    return $_SESSION['user_id'];
  } else {
    header('Location:login.php?message=time_out'); exit;
  }
}

function input_text($id, $class, $name, $value, $type) {
  print '<input type="'. $type .'" id="'. $id .'" class="'. $class .'"
          name="'. $name .'"value="'. htmlentities($value) .'"/>';
}

function input_textarea($id, $class, $name, $value) {
  print '<textarea id="'. $id .'" class="'. $class .'" name="'. $name .'">' .
          htmlentities($value) .'</textarea>';
}

?>
