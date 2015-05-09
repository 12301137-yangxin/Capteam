<?php require_once('config/tank_config.php'); ?>
<?php require_once('session_unset.php'); ?>
<?php require_once('session.php'); ?>
<?php require_once('dao.php'); ?>
<?php
//创建Message数据库操作实体类
$message_dao_obj = new message_dao();
//获得当前页面的url
$currentPage = $_SERVER["PHP_SELF"];
//设置消息最大显示行数为30行
$maxRows_Recordset1 = 30;
//设置当前显示页面为0
$pageNum_Recordset1 = 0;
//获取当前显示页面
if (isset($_GET['pageNum_Recordset1'])) {
  $pageNum_Recordset1 = $_GET['pageNum_Recordset1'];
}
$startRow_Recordset1 = $pageNum_Recordset1 * $maxRows_Recordset1;


$colname_Recordset1 = "-1";
if (isset($_GET['select1'])) {
  $colname_Recordset1 = $_GET['select1'];
}

$new_msgid = $_SESSION['MM_msg'];

$user_id= $_SESSION['MM_uid'];

//删除选中的消息
$frm_tag = $_POST['item']; 
for($i=0;$i<count($frm_tag);$i++){ 
  $message_dao_obj->delete_message($frm_tag[$i]);
} 


mysql_select_db($database_tankdb, $tankdb);
$query_Recordset1 = sprintf("SELECT * FROM tk_message inner join tk_user on tk_message.tk_mess_fromuser=tk_user.uid WHERE tk_mess_touser = %s ORDER BY meid DESC", GetSQLValueString($_SESSION['MM_uid'], "int"));
$query_limit_Recordset1 = sprintf("%s LIMIT %d, %d", $query_Recordset1, $pageNum_Recordset1, $maxRows_Recordset1);
$Recordset1 = mysql_query($query_limit_Recordset1, $tankdb) or die(mysql_error());
$row_Recordset1 = mysql_fetch_assoc($Recordset1);

$query_Recordset2 = sprintf("SELECT meid FROM tk_message WHERE tk_mess_touser = %s ORDER BY tk_mess_time DESC", GetSQLValueString($_SESSION['MM_uid'], "int"));
$query_limit_Recordset2 = sprintf("%s LIMIT %d, %d", $query_Recordset2, $maxRows_Recordset1, 1);
$Recordset2 = mysql_query($query_limit_Recordset2, $tankdb) or die(mysql_error());
$row_Recordset2 = mysql_fetch_assoc($Recordset2);

$deleteSQL = sprintf("DELETE FROM tk_message WHERE tk_mess_touser = %s AND meid < %s", 
                     GetSQLValueString($_SESSION['MM_uid'], "int"), 
                       GetSQLValueString($row_Recordset2['meid'], "int"));
$deleteResult1 = mysql_query($deleteSQL, $tankdb) or die(mysql_error());

if($pageNum_Recordset1==0 && $row_Recordset1['meid']<>null){
$message_id = $row_Recordset1['meid'];

$_SESSION['MM_msg'] = $message_id;	
$updateSQL = "UPDATE tk_user SET tk_user_message='$message_id' WHERE uid='$user_id'";
$Result1 = mysql_query($updateSQL, $tankdb) or die(mysql_error());
}

if (isset($_GET['totalRows_Recordset1'])) {
  $totalRows_Recordset1 = $_GET['totalRows_Recordset1'];
} else {
  $all_Recordset1 = mysql_query($query_Recordset1);
  $totalRows_Recordset1 = mysql_num_rows($all_Recordset1);
}

$totalPages_Recordset1 = ceil($totalRows_Recordset1/$maxRows_Recordset1)-1;

$queryString_Recordset1 = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_Recordset1") == false && 
        stristr($param, "totalRows_Recordset1") == false) {
      array_push($newParams, $param);
    }
  }
	  if (count($newParams) != 0) {
		$queryString_Recordset1 = "&" . htmlentities(implode("&", $newParams));
	  }
	}
	$queryString_Recordset1 = sprintf("&totalRows_Recordset1=%d%s", $totalRows_Recordset1, $queryString_Recordset1);
	?>
	<script language="javascript">
		//该方法实现全选和取消全选
		function check_all(){
			var a=document.getElementsByName('all_items');//获得全选的框
			 for (j=0;j<a.length;j++){
				 if(a[j].checked == true){
					 var b=document.getElementsByName('item[]');//获得所有的checkbox变量
					 for (i=0;i<b.length;i++){
						 b[i].checked=true;
					 }
				 }else{
					 var b=document.getElementsByName('item[]');//获得所有的checkbox变量
					 for (i=0;i<b.length;i++){
						 b[i].checked=false;
					 }
				 }
			  }
		}

</script>

  <?php require('head.php'); ?>
  <br />
  <div class="pagemargin">
  <?php if ($totalRows_Recordset1 > 0) { // Show if recordset not empty ?>
   
      <table  class="table table-striped table-hover glink" width="98%" >
        <thead>
          <tr>
            <th><input type="checkbox" name="all_items" id=="all_items" onclick="check_all()"></th>  <!-- 全选多选框 -->
            <th><span class="font_big18 fontbold breakwordsfloat_left"><?php echo $multilingual_message; ?></span></th>
            <th><?php echo $multilingual_message_time; ?></th>
          </tr>
        </thead>
		<tbody>

        <form name="form1" id="form1" method="post">
        <?php do { ?>
          <tr>
            <td><input type="checkbox" name="item[]" value=<?php echo $row_Recordset1['meid']; ?> ></td>  <!-- 删除多选框 -->
            <td class="task_title5">
            <div  class="text_overflow_450  <?php if($row_Recordset1['tk_mess_status'] == 1) {echo "fontbold"; } ?>">
                <a href="user_view.php?recordID=<?php echo $row_Recordset1['tk_mess_fromuser']; ?>">
			        <?php echo $row_Recordset1['tk_display_name']; ?></a> <!-- 显示用户 -->
			        <?php echo $row_Recordset1['tk_mess_title']; ?>   <!-- 显示内容 -->
			        <?php $message_dao_obj->update_message_read($row_Recordset1['meid']); ?>   <!-- 显示内容 -->
			 </div>
			 </td>
            
            <td><?php echo $row_Recordset1['tk_mess_time']; ?>&nbsp; </td>   <!-- 显示时间 -->
          </tr>
          <?php } while ($row_Recordset1 = mysql_fetch_assoc($Recordset1)); ?>
          <input type="submit" value="删除">
          </form>

		  </tbody>
      </table>
      <?php if ( $totalRows_Recordset1 > $maxRows_Recordset1) { ?>
      <table  width="98%">
          <tr>
              <td align="center" class="gray">
                  <?php echo $multilingual_message_nomore;  ?>
              </td>
          </tr>
      </table>
      <?php } ?>
<!--
    <table class="rowcon" border="0" align="center">
      <tr>
        <td>  <table border="0">
          <tr>
            
            <td valign="bottom">
              <table border="0">
                <tr>
                  <td><?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $currentPage, 0, $queryString_Recordset1); ?>"><?php echo $multilingual_global_first; ?></a>
                    <?php } // Show if not first page ?></td>
                  <td><?php if ($pageNum_Recordset1 > 0) { // Show if not first page ?>
                    <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $currentPage, max(0, $pageNum_Recordset1 - 1), $queryString_Recordset1); ?>"><?php echo $multilingual_global_previous; ?></a>
                    <?php } // Show if not first page ?></td>
                  <td><?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $currentPage, min($totalPages_Recordset1, $pageNum_Recordset1 + 1), $queryString_Recordset1); ?>"><?php echo $multilingual_global_next; ?></a>
                    <?php } // Show if not last page ?></td>
                  <td><?php if ($pageNum_Recordset1 < $totalPages_Recordset1) { // Show if not last page ?>
                    <a href="<?php printf("%s?pageNum_Recordset1=%d%s", $currentPage, $totalPages_Recordset1, $queryString_Recordset1); ?>"><?php echo $multilingual_global_last; ?></a>
                    <?php } // Show if not last page ?></td>
                </tr>
              </table>
            </td>
            
            
          </tr>
        </table></td>
        <td align="right" valign="bottom"><?php echo ($startRow_Recordset1 + 1) ?> <?php echo $multilingual_global_to; ?> <?php echo min($startRow_Recordset1 + $maxRows_Recordset1, $totalRows_Recordset1) ?> (<?php echo $multilingual_global_total; ?> <?php echo $totalRows_Recordset1 ?>)</td>
      </tr>
    </table>
-->
    <?php } else { // Show if recordset empty ?>  
  <div class="alert alert-warning" style="margin:6px;">
    <table>
	<tr>
	<td valign="top">
	<?php echo $multilingual_message_nomsg; ?>
	</td>

	</tr>
	</table>
	</div>
  </div>
  </div>
<?php } // Show if recordset empty ?>  
  <p>&nbsp;</p>
  </div><!--pagemargin结束 -->
  <?php require('foot.php'); ?>
  
</body>
</html>
<?php
mysql_free_result($Recordset1);
?>
