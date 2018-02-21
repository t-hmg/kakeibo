function draw_piechart(graph_id, table_id, total_id) {
  var graph = document.getElementById(graph_id);
  var table = document.getElementById(table_id);
  var total = document.getElementById(total_id);
  // 合計金額
  var kingaku_total = Number(total.innerHTML.replace(/[￥|,]/g, ""));
  // 円グラフのプロットデータを配列に格納する
  var graph_data = [];
  for (var i = 1; i < table.rows.length; i++) {
     var row = table.rows[i];
     // 色
     var color = row.cells[0].childNodes[0].style.backgroundColor;
     // 費目
     var himoku = row.cells[0].childNodes[1].innerHTML;
     // 金額
     var kingaku = row.cells[1].childNodes[0].innerHTML;
     kingaku = Number(kingaku.replace(/[￥|,]/g, ""));
     // 金額割合（％）
     var parcent = kingaku / kingaku_total;
     parcent = Math.round(parcent * 10000) / 100;
     // 配列に要素（色・費目・金額割合）を追加する
     graph_data.push({color:color, himoku:himoku, parcent: parcent});
   }
   // 配列を金額割合（％）の降順で並び替える
   graph_data.sort(function(a,b){
     return (a["parcent"] - b["parcent"]) * -1;
   });
   // 円グラフの扇形を描画する
   var canvas = document.getElementById(graph_id);
   var context = canvas.getContext("2d");
   var cw= graph.clientWidth;　
   var ch= graph.clientHeight;　
   var label_info = [];
   var angle_ruikei = 0;
   for (i = 0; i < graph_data.length; i++) {
     var angle = 360 * (graph_data[i]["parcent"] / 100);
     var angle_strat = (angle_ruikei - 90) * Math.PI / 180;
     var angle_end = (angle_ruikei + angle - 90) * Math.PI / 180;
     context.beginPath () ;
     context.arc(cw/2, ch/2, ch/2, angle_strat, angle_end, false);
     context.lineTo(cw/2, ch/2);
     context.fillStyle = graph_data[i]["color"];
     context.fill() ;
     // 金額割合が５％以上の費目は円グラフ上にラベルを表示させる必要があるため
     // ラベルのプロットデータを配列に格納する
     if (graph_data[i]["parcent"] >= 5) {
       var end = (Math.PI * 2) * (graph_data[i]["parcent"] / 100);
       var off = 4.0;
       var x = Math.cos(angle_strat+end/2)*cw/off+cw/2;
       var y = Math.sin(angle_strat+end/2)*cw/off+ch/2;
       label_info.push({label:graph_data[i]["himoku"], x:x, y:y, font:"8pt Hiragino Kaku Gothic ProN", fillStyle:"black"});
       label_info.push({label:graph_data[i]["parcent"] + "%", x:x, y:y + 15, font:"7pt Hiragino Kaku Gothic ProN", fillStyle:"black"});
     }
     angle_ruikei = angle_ruikei + angle;
   }
   // 円グラフ上にラベルを描画する
   for (i = 0; i < label_info.length; i++) {
     context.font = label_info[i]["font"];
     context.fillStyle = label_info[i]["fillStyle"];
     context.fillText(label_info[i]["label"], label_info[i]["x"] - 12, label_info[i]["y"] - 5);
   }
 }
