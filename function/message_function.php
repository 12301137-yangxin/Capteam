<?php
	$version = "1.3.3b";
	$maxRows = 30;
	$tasklevel = 0;
	mysql_select_db($database_tankdb,$tankdb);

    //ɾ��ĳ����Ϣ
    public function delete_message($mid)
    {
        global $tankdb;
        global $database_tankdb;
        $deleteSQL = sprintf("DELETE FROM tk_message WHERE meid=%s",GetSQLValueString($mid, "int"));
        mysql_select_db($database_tankdb, $tankdb);
        $Result1 = mysql_query($deleteSQL, $tankdb) or die(mysql_error());
    }
    //��ĳ����Ϣ��Ϊ�Ѷ�
    public function update_message($mid,$status1,$status2)
    {
        global $tankdb;
        global $database_tankdb;
        //1��ʾδ����0��ʾ�Ѷ�,2��ʾ���Ѷ�
        //��status1��״̬�ĳ�status2
        $deleteSQL = sprintf("UPDATE tk_message set tk_mess_status = %s WHERE meid=%s and tk_mess_status = %s",GetSQLValueString($status2, "int"),GetSQLValueString($mid, "int"),GetSQLValueString($status1, "int"));
        mysql_select_db($database_tankdb, $tankdb);
        $Result1 = mysql_query($deleteSQL, $tankdb) or die(mysql_error());
    }	
	
?>