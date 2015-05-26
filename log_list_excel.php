<?php require_once('config/tank_config.php'); ?>
<?php require_once('session.php'); ?>
<?php require_once('function/log_function.php'); ?>
<?php require_once('function/user_function.php'); ?>
<?php require_once('function/project_function.php'); ?>
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
				
//<!--���ô����·�--> 
$colmonth_Recordset1 = date("m");
if (isset($_GET['textfield'])) {
	$colmonth_Recordset1 = $_GET['textfield'];
}

//<!--���ô������--> 
$colyear_Recordset1 = date("Y");
if (isset($_GET['select_year'])) {
	$colyear_Recordset1 = $_GET['select_year'];
}
$YEAR = $colyear_Recordset1;
$MONTH = $colmonth_Recordset1;

//<!--ѡ���·ݱ���ѡ��ݣ�ѡ��ݿ��Բ�ѡ�·�--> 
//<!--�����Ϊ--����Ϊ���������������·�Ϊ�գ��������һ�������--> 
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


//<!--ѡ��ĳһ��Ŀ--> 
$colproject_Recordset1 = "";
if (isset($_GET['select_project'])) {
	$colproject_Recordset1 = $_GET['select_project'];
}

//���������׼
$sortlist = "tk_log_time";
if (isset($_GET['sort'])) {
	$sortlist = $_GET['sort'];
}

//���� ����
$orderlist = "ASC";
if (isset($_GET['order'])) {
  $orderlist= $_GET['order'];
}
$colproject = GetSQLValueString($colproject_Recordset1, "int");
$where = "";
//������Ŀ
if(!empty($colproject_Recordset1))
{
	$where.= "and  tk_project.id = $colproject ";
}
//��ǰ�û�id
$user_id = $_SESSION['MM_uid']; 

$date = date('Y-m-d');
$filename = $multilingual_global_excellog.$date.".csv";

// ���Excel�ļ�ͷ���ɰ�user.csv������Ҫ���ļ���
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment;filename=$filename");
header('Cache-Control: max-age=0');
  

$R=1;		  
$log_arr = array();
if($sortlist=='project'){
	mysql_select_db($database_tankdb, $tankdb);
	$query_Recordset_project = sprintf("SELECT id, project_name,project_text, project_start, project_end, project_to_user, project_lastupdate, project_create_time FROM tk_project inner join tk_team on tk_team.tk_team_pid=tk_project.id WHERE tk_team_uid = %s AND tk_team_del_status=1 $where ORDER BY tk_project.project_name %s",GetSQLValueString($user_id, "int"),
							GetSQLValueString($orderlist, "defined", $orderlist, "NULL"));
	$Recordset_project = mysql_query($query_Recordset_project, $tankdb) or die(mysql_error());
	$row_Recordset_project = mysql_fetch_assoc($Recordset_project);
	do{
    	$project_id = $row_Recordset_project['id'];
        //������Ŀid�ҵ���Ӧ����Ŀlog
        mysql_select_db($database_tankdb, $tankdb);
        $query_Recordset_log = sprintf("SELECT * FROM tk_log WHERE ((tk_log_class=1 and tk_log_type= %s ) or (tk_log_class=4 and tk_log_type in (SELECT distinct docid FROM tk_document WHERE tk_document.tk_doc_pid in (SELECT id FROM tk_project WHERE project_del_status=1 and tk_project.id = %s ) and tk_document.tk_doc_del_status=1 and tk_doc_create !=0))) and (tk_log_time <=%s AND tk_log_time >=%s) ", 
							GetSQLValueString($project_id, "int"),
							GetSQLValueString($project_id, "int"),
							GetSQLValueString($endday , "text"),
							GetSQLValueString($startday , "text"));
        $Recordset_log1 = mysql_query($query_Recordset_log, $tankdb) or die(mysql_error()); 
        while($row_Recordset_log1 = mysql_fetch_assoc($Recordset_log1)){
        	$log_arr[$row_Recordset_log1['logid']]['id'] =  $R++;
        	$log_arr[$row_Recordset_log1['logid']]['user'] =  get_user_disName($row_Recordset_log1['tk_log_user']);
        	$log_arr[$row_Recordset_log1['logid']]['time'] =  $row_Recordset_log1['tk_log_time'];
        	$log_arr[$row_Recordset_log1['logid']]['action'] =  $row_Recordset_log1['tk_log_action'];
        	$log_arr[$row_Recordset_log1['logid']]['pid'] =  get_pid_by_option($row_Recordset_log1['tk_log_class'],$row_Recordset_log1['tk_log_type']);
			//������Ŀ�ҵ���Ӧ�Ľ׶�
			mysql_select_db($database_tankdb, $tankdb);
			$query_stage ="SELECT * FROM tk_stage WHERE  tk_stage_pid= $project_id and tk_stage_delestatus = 1";
			$stageRS = mysql_query($query_stage, $tankdb) or die(mysql_error());
			$row_stage = mysql_fetch_assoc($stageRS);

			do{
				$stage_id = $row_stage['stageid'];
				//���ݽ׶�id�ҵ���Ӧ�Ľ׶�log
				mysql_select_db($database_tankdb, $tankdb);
				$query_Recordset_log = sprintf("SELECT * FROM tk_log WHERE tk_log_class=2 and tk_log_type =%s   
				and (tk_log_time <=%s AND tk_log_time >=%s) ", 
												 GetSQLValueString($stage_id, "int"),
								GetSQLValueString($endday , "text"),
								GetSQLValueString($startday , "text"));
				$Recordset_log2 = mysql_query($query_Recordset_log, $tankdb) or die(mysql_error());  

				while($row_Recordset_log2 = mysql_fetch_assoc($Recordset_log2)){
					$log_arr[$row_Recordset_log2['logid']]['id'] = $R++;
					$log_arr[$row_Recordset_log2['logid']]['user'] = get_user_disName($row_Recordset_log1['tk_log_user']);
					$log_arr[$row_Recordset_log2['logid']]['time'] =  $row_Recordset_log2['tk_log_time'];
					$log_arr[$row_Recordset_log2['logid']]['action'] =  $row_Recordset_log2['tk_log_action'];
        	$log_arr[$row_Recordset_log2['logid']]['pid'] =  get_pid_by_option($row_Recordset_log2['tk_log_class'],$row_Recordset_log2['tk_log_type']);
					
					mysql_select_db($database_tankdb, $tankdb);
					$query_task ="SELECT * FROM tk_task WHERE  csa_project= $project_id and csa_del_status = 1";
					$taskRS = mysql_query($query_task, $tankdb) or die(mysql_error());
					$row_task = mysql_fetch_assoc($taskRS);
					do{
						$task_id = $row_task['tid'];
						//���ݽ׶�id�ҵ���Ӧ�Ľ׶�log
						mysql_select_db($database_tankdb, $tankdb);
						$query_Recordset_log = sprintf("SELECT * FROM tk_log WHERE tk_log_class=3 and tk_log_type= %s  
						and (tk_log_time <=%s AND tk_log_time >=%s) ", 
														 GetSQLValueString($task_id, "int"),
										GetSQLValueString($endday , "text"),
										GetSQLValueString($startday , "text"));
						$Recordset_log3 = mysql_query($query_Recordset_log, $tankdb) or die(mysql_error());   
						while($row_Recordset_log3 = mysql_fetch_assoc($Recordset_log3)){
							$log_arr[$row_Recordset_log3['logid']]['id'] =  $R++;
							$log_arr[$row_Recordset_log3['logid']]['user'] =  get_user_disName($row_Recordset_log1['tk_log_user']);
							$log_arr[$row_Recordset_log3['logid']]['time'] =  $row_Recordset_log3['tk_log_time'];
							$log_arr[$row_Recordset_log3['logid']]['action'] =  $row_Recordset_log3['tk_log_action'];
        	$log_arr[$row_Recordset_log3['logid']]['pid'] =  get_pid_by_option($row_Recordset_log3['tk_log_class'],$row_Recordset_log3['tk_log_type']);
						}       
					}while($row_task = mysql_fetch_assoc($taskRS));
				}        
			}while($row_stage = mysql_fetch_assoc($stageRS));
        }
	}while($row_Recordset_project = mysql_fetch_assoc($Recordset_project));
}else if($sortlist=='tk_log_user'){
	mysql_select_db($database_tankdb, $tankdb);
	$query_Recordset2 = sprintf("SELECT  * FROM tk_user WHERE tk_user_del_status=1 AND tk_user.uid in ( select distinct tk_team_uid from tk_team,tk_project WHERE tk_team_del_status=1 AND project_del_status=1 AND tk_team_pid in (SELECT id FROM tk_project inner join tk_team on tk_team.tk_team_pid=tk_project.id WHERE tk_team_uid = %s AND project_del_status=1 AND tk_team_del_status=1 ) ) ORDER BY  tk_display_name ASC",GetSQLValueString($user_id, "int"));

	$Recordset2 = mysql_query($query_Recordset2, $tankdb) or die(mysql_error());
	$row_Recordset2 = mysql_fetch_assoc($Recordset2);
	do{
				$uid = $row_Recordset2['uid'];
				mysql_select_db($database_tankdb, $tankdb);
				$query_Recordset_log = sprintf("SELECT * FROM tk_log WHERE tk_log_user = %s  
				and (tk_log_time <=%s AND tk_log_time >=%s)  and ((tk_log_class=1 and tk_log_type in (SELECT id FROM tk_project WHERE project_del_status=1 $where )) or (tk_log_class=4 and tk_log_type in (SELECT distinct docid FROM tk_document WHERE tk_document.tk_doc_pid in (SELECT id FROM tk_project WHERE project_del_status=1 $where) and tk_document.tk_doc_del_status=1 and tk_doc_create !=0)) or (tk_log_class=2 and tk_log_type in (SELECT stageid FROM tk_stage,tk_project WHERE tk_stage.tk_stage_pid=tk_project.id and tk_stage.tk_stage_delestatus=1 and tk_project.project_del_status=1 $where ))  or (tk_log_class=3 and tk_log_type in (SELECT tid FROM tk_task,tk_project WHERE tk_task.csa_project=tk_project.id and tk_task.csa_del_status=1 and tk_project.project_del_status=1  $where )))
								 
								ORDER BY %s %s", 
												 GetSQLValueString($uid, "int"),
								GetSQLValueString($endday , "text"),
								GetSQLValueString($startday , "text"),
								GetSQLValueString($sortlist, "defined", $sortlist, "NULL"),
								GetSQLValueString($orderlist, "defined", $orderlist, "NULL"));
					$Recordset_log = mysql_query($query_Recordset_log, $tankdb) or die(mysql_error());   
					while($row_Recordset_log = mysql_fetch_assoc($Recordset_log)){
					$log_arr[$row_Recordset_log['logid']]['id'] =  $R++;
					$log_arr[$row_Recordset_log['logid']]['user'] =  get_user_disName($row_Recordset_log1['tk_log_user']);
					$log_arr[$row_Recordset_log['logid']]['time'] =  $row_Recordset_log['tk_log_time'];
					$log_arr[$row_Recordset_log['logid']]['action'] =  $row_Recordset_log['tk_log_action'];
        	$log_arr[$row_Recordset_log1['logid']]['pid'] =  get_pid_by_option($row_Recordset_log['tk_log_class'],$row_Recordset_log['tk_log_type']);
				}
	}while($row_Recordset2 = mysql_fetch_assoc($Recordset2));
}else{
		mysql_select_db($database_tankdb, $tankdb);
        $query_Recordset_log = sprintf("SELECT * FROM tk_log WHERE (tk_log_time <=%s AND tk_log_time >=%s) and  tk_log_user in (SELECT  uid FROM tk_user WHERE tk_user_del_status=1 AND tk_user.uid in ( select distinct tk_team_uid from tk_team,tk_project WHERE tk_team_del_status=1 AND project_del_status=1 AND tk_team_pid in (SELECT id FROM tk_project inner join tk_team on tk_team.tk_team_pid=tk_project.id WHERE tk_team_uid = %s AND project_del_status=1 AND tk_team_del_status=1  $where ) ))  and ((tk_log_class=1 and tk_log_type in (SELECT id FROM tk_project WHERE project_del_status=1 $where )) or (tk_log_class=4 and tk_log_type in (SELECT distinct docid FROM tk_document WHERE tk_document.tk_doc_pid in (SELECT id FROM tk_project WHERE project_del_status=1 $where) and tk_document.tk_doc_del_status=1 and tk_doc_create !=0)) or (tk_log_class=2 and tk_log_type in (SELECT stageid FROM tk_stage,tk_project WHERE tk_stage.tk_stage_pid=tk_project.id and tk_stage.tk_stage_delestatus=1 and tk_project.project_del_status=1 $where ))  or (tk_log_class=3 and tk_log_type in (SELECT tid FROM tk_task,tk_project WHERE tk_task.csa_project=tk_project.id and tk_task.csa_del_status=1 and tk_project.project_del_status=1  $where )))
							 
							ORDER BY %s %s", 							
							GetSQLValueString($endday , "text"),
							GetSQLValueString($startday , "text"),
							GetSQLValueString($user_id, "int"),
							GetSQLValueString($sortlist, "defined", $sortlist, "NULL"),
							GetSQLValueString($orderlist, "defined", $orderlist, "NULL"));

        $Recordset_log = mysql_query($query_Recordset_log, $tankdb) or die(mysql_error());  

        while($row_Recordset_log = mysql_fetch_assoc($Recordset_log)){
        	$log_arr[$row_Recordset_log['logid']]['id'] =  $R++;
        	$log_arr[$row_Recordset_log['logid']]['user'] =  get_user_disName($row_Recordset_log['tk_log_user']);
        	$log_arr[$row_Recordset_log['logid']]['time'] =  $row_Recordset_log['tk_log_time'];
        	$log_arr[$row_Recordset_log['logid']]['action'] =  $row_Recordset_log['tk_log_action'];
        	$log_arr[$row_Recordset_log['logid']]['pid'] = get_pid_by_option($row_Recordset_log['tk_log_class'],$row_Recordset_log['tk_log_type']);
		}
}
 
// ��PHP�ļ������php://output ��ʾֱ������������
$fp = fopen('php://output', 'a');
  
// ���Excel������Ϣ
$head = array("���","�û�","����","ʱ��","������Ŀ");
 
// ������ͨ��fputcsvд���ļ����
fputcsv($fp, $head);
  
// ������
$cnt = 0;
// ÿ��$limit�У�ˢ��һ�����buffer����Ҫ̫��Ҳ��Ҫ̫С
$limit = 100000;



// ����ȡ�����ݣ����˷��ڴ�

foreach($log_arr as $key => $val){ 
    $cnt ++;
   if ($limit == $cnt) { //ˢ��һ�����buffer����ֹ�������ݹ����������
        ob_flush();
        flush();
        $cnt = 0;
    }
		$tt = array( $val['id'],
					strip_tags(iconv('utf-8', 'gbk',  $val['user'])),
					strip_tags(iconv('utf-8', 'gbk',  $val['action'])),
					$val['time'],
					strip_tags(iconv('utf-8', 'gbk',  $val['pid'])));
    fputcsv($fp, $tt);
}

?>