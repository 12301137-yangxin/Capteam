<?php require_once('config/tank_config.php'); ?>
<?php require_once('session.php'); ?>
<?php
 
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$colname_Recordset1 = "%";
if (isset($_GET['select4'])) {
	$colname_Recordset1 = $_GET['select4'];
}

$colcreate_Recordset1 = "%";
if (isset($_GET['create_by'])) {
	$colcreate_Recordset1 = $_GET['create_by'];
}

$colmonth_Recordset1 = date("m");
if (isset($_GET['textfield'])) {
	$colmonth_Recordset1 = $_GET['textfield'];
}

$colyear_Recordset1 = date("Y");
if (isset($_GET['select_year'])) {
	$colyear_Recordset1 = $_GET['select_year'];
}

$YEAR = $colyear_Recordset1;
$MONTH = $colmonth_Recordset1;

if ($colyear_Recordset1 == "--"){
	$startday = "1975-09-23";
	$endday = "3000-13-31";
} else if ($colmonth_Recordset1 == "--"){
	$startday = $colyear_Recordset1."-01-01";
	$endday = $colyear_Recordset1."-12-31";
} else {
	$startday = $colyear_Recordset1."-".$colmonth_Recordset1."-01";
	$endday = $colyear_Recordset1."-".$colmonth_Recordset1."-31";
}

$colprt_Recordset1 = "";
if (isset($_GET['select_prt'])) {
	$colprt_Recordset1 = $_GET['select_prt'];
}

if (isset($_GET['select_st'])) {
	switch($_GET['select_st'])
	{
		case "":$_SESSION['ser_status'] = ""; break;
		case "������":$colstatus_Recordset1=1;  break;
		case "�ѹ���":$colstatus_Recordset1=2;  break;
		case "������":$colstatus_Recordset1=3;  break;
		case "�����":$colstatus_Recordset1=4;  break;
		case "������":$colstatus_Recordset1=5;  break;
		case "δ��ʼ":$colstatus_Recordset1=6;  break;
	}
} else {
	$colstatus_Recordset1 = 1; 
}

$colproject_Recordset1 = "";
if (isset($_GET['select_project'])) {
	$colproject_Recordset1 = $_GET['select_project'];
}

$colstage_Recordset1 = "";
if (isset($_GET['select_stage'])) {
	$colstage_Recordset1 = $_GET['select_stage'];
}

$searchby = "";
$colinputtitle_Recordset1 = "";
$colinputtag_Recordset1 = "";
if (isset($_GET['searchby'])) {
	$searchby= $_GET['searchby'];
	if($searchby == "tit"){
		$colinputtitle_Recordset1 =  $_GET['inputval'];
	} else if($searchby == "tag"){
		$colinputtag_Recordset1 = $_GET['inputval'];
	}  
}

$sortlist = "csa_plan_st";
if (isset($_GET['sort'])) {
	$sortlist = $_GET['sort'];
}

$orderlist = "ASC";
if (isset($_GET['order'])) {
  $orderlist= $_GET['order'];
}

$date = date('Y-m-d');
$filename = $multilingual_global_excelfile.$date.".csv";

header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment;filename=$filename");
header('Cache-Control: max-age=0');


$coltouser = GetSQLValueString($colname_Recordset1, "int");
$colcreateuser = GetSQLValueString($colcreate_Recordset1, "int");
$colstatus = GetSQLValueString($colstatus_Recordset1, "int");


$colprt = GetSQLValueString("%%" . str_replace("%","%%",$colprt_Recordset1) . "%%", "text");
$colproject = GetSQLValueString($colproject_Recordset1, "int");
$colstage = GetSQLValueString($colstage_Recordset1, "int");
$colinputtitle = GetSQLValueString("%%" . str_replace("%","%%",$colinputtitle_Recordset1) . "%%", "text");
$colinputtag = GetSQLValueString("%%" . str_replace("%","%%",$colinputtag_Recordset1) . "%%", "text");
$cc_tome = '"uid":"'.$_SESSION['MM_uid'].'"';
$cc_tome = GetSQLValueString("%%" . str_replace("%","%%",$cc_tome) . "%%", "text");

		$where = "";
		
		$where=' WHERE';
		//ִ����
		if($colname_Recordset1 <> '%' )
		{
			$where.= " tk_task.csa_to_user = $coltouser AND ";
		}
		//���ȼ�
		if(!empty($colprt_Recordset1))
		{
			$where.= " tk_task.csa_priority LIKE $colprt AND ";
		}
		//����״̬
		if(!empty($colstatus_Recordset1))
		{
			$where.= " tk_task.csa_status = $colstatus AND ";
		}
		//������Ŀ
		if(!empty($colproject_Recordset1))
		{
			$where.= " tk_task.csa_project = $colproject AND ";
		}
		//�����׶�
		if(!empty($colstage_Recordset1))
		{
			$where.= " tk_task.csa_project_stage = $colstage AND ";
		}
		//�������
		if(!empty($colinputtitle_Recordset1))
		{
			$where.= " tk_task.csa_text LIKE $colinputtitle AND ";
		}
		//�����ǩ
		if(!empty($colinputtag_Recordset1))
		{
			$where.= " tk_task.csa_tag LIKE $colinputtag AND ";
		}
		//������
		if($colcreate_Recordset1 <> '%')
		{
			$where.= " tk_task.csa_from_user = $colcreateuser AND ";
		}
		//������
		if($pagetabs == "cctome")
		{
			$where.= " tk_task.csa_testto LIKE $cc_tome AND ";
		}

//������ѯ���
mysql_select_db($database_tankdb, $tankdb);
$query_Recordset1 = sprintf("SELECT  (@rownum := @rownum+1) AS rowno, 
							csa_text, 
							tk_display_name1, 
							tk_display_name2,
							task_status,
							csa_priority,
							csa_plan_hour,
							csa_plan_st,
							csa_plan_et,				
							project_name_prt,
							stage_name_prt,
							csa_last_update 
							
							FROM 
							( 
								SELECT 
								csa_text,
								tk_user1.tk_display_name as tk_display_name1, 
								tk_user2.tk_display_name as tk_display_name2,
								tk_status.task_status,
								csa_priority,
								csa_plan_hour,
								csa_plan_st,csa_plan_et,				
								tk_project.project_name as project_name_prt,
								tk_stage.tk_stage_title as stage_name_prt,
								csa_last_update 
								FROM tk_task  
								inner join tk_project on tk_task.csa_project=tk_project.id 
								inner join tk_stage on tk_task.csa_project_stage=tk_stage.stageid 							
								inner join tk_user as tk_user1 on tk_task.csa_to_user=tk_user1.uid 
								inner join tk_user as tk_user2 on tk_task.csa_from_user=tk_user2.uid 				
								inner join tk_status on tk_task.csa_status=tk_status.id 
								
								$where 
								  ((tk_task.csa_plan_st <=%s
								 AND tk_task.csa_plan_et >=%s)
								 OR (tk_task.csa_plan_st <=%s
								 AND tk_task.csa_plan_et >=%s)
								 OR (tk_task.csa_plan_st >=%s
								 AND tk_task.csa_plan_et <=%s)
								 OR (tk_task.csa_plan_st <=%s
								 AND tk_task.csa_plan_et >=%s)) 
									
								 AND csa_del_status=1
								 
								ORDER BY %s %s 
							) as a, 
							
							(SELECT @rownum:=0) as b ",
							
							GetSQLValueString($startday , "text"),
							GetSQLValueString($startday , "text"),
							GetSQLValueString($endday , "text"),
							GetSQLValueString($endday , "text"),
							GetSQLValueString($startday , "text"),
							GetSQLValueString($endday , "text"),
							GetSQLValueString($startday , "text"),
							GetSQLValueString($endday , "text"),
							GetSQLValueString($sortlist, "defined", $sortlist, "NULL"),
							GetSQLValueString($orderlist, "defined", $orderlist, "NULL")
							);
						
$stmt  = mysql_query($query_Recordset1, $tankdb) or die(mysql_error());
  
// ��PHP�ļ������php://output ��ʾֱ������������
$fp = fopen('php://output', 'a');
  
// ���Excel������Ϣ
$head = array("���","����","ָ�ɸ�","����","״̬","���ȼ�","������","�ƻ���ʼʱ��","�ƻ����ʱ��","������Ŀ","�����׶�","������ʱ��");


// ������ͨ��fputcsvд���ļ����
fputcsv($fp, $head);
  
// ������
$cnt = 0;
// ÿ��$limit�У�ˢ��һ�����buffer����Ҫ̫��Ҳ��Ҫ̫С
$limit = 100000;

while($row=mysql_fetch_assoc($stmt)){ 
  
    $cnt ++;
   if ($limit == $cnt) { //ˢ��һ�����buffer����ֹ�������ݹ����������
        ob_flush();
        flush();
        $cnt = 0;
    }
    foreach ($row as $i => $v) {
        $rows[$i] =  strip_tags(iconv('utf-8', 'gbk', $v));
    }
    fputcsv($fp, $rows);
}

?>