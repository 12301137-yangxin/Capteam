<?php require_once('function/user_function.php'); ?>
<?php
	$version = "1.3.3b";
	$maxRows = 30;
	$tasklevel = 0;
	mysql_select_db($database_tankdb,$tankdb);

	//��ȡ�������������
	public function get_task_events($userid){
		$sql = "select * from tk_task where csa_to_user='$userid'";
		$query = mysql_query($sql);
		while($row=mysql_fetch_array($query)){
			$data[] = array(
				'id' => $row['tid'],
				'title' => $row['csa_text'],
				'start' => $row['csa_plan_et'],
				'end' => $row['csa_plan_et'],
				'url' => $row['url'],
				'allDay' => TRUE,
				// 'color' => $row['color']
			);
		}
		return $data;
	}

    //��ȡ�����ճ̵�����
    public function get_person_events($userid){
        $sql = "select * from tk_schedule where uid='$userid'";
        $query = mysql_query($sql);
        while($row=mysql_fetch_array($query)){
            if($row['is_allday'] ==0){
                $allday = FALSE;
            }else{
                $allday = TRUE;
            }
            $data[] = array(
            'id' => $row['id'],
            'title' => $row['name'],
            'start' => $row['start_time'],
            'end' => $row['end_time'],
            'url' => $row['url'],
            'color' => '#008573',
            'allDay' => $allday
            );
        }
        return $data;
    }

    //��ȡ���������ճ̵�����
    public function get_person_all_events($userid){
        //����û��ĸ����ճ���Ϣ
        $sql = "select * from tk_schedule where uid='$userid'";
        $query = mysql_query($sql);
        while($row=mysql_fetch_array($query)){
            if($row['is_allday'] ==0){
                $allday = FALSE;
            }else{
                $allday = TRUE;
            }
            $data[] = array(
            'id' => $row['id'],
            'title' => '[����]'.$row['name'],
            'start' => $row['start_time'],
            'end' => $row['end_time'],
            'url' => $row['url'],
            'color' => '#008573',
            'allDay' => $allday
            );
        }

        //����û���������Ϣ
        $sql = "select * from tk_task where csa_to_user=$userid";
        $query = mysql_query($sql);
        while($row=mysql_fetch_array($query)){
            $data[] = array(
                'id' => $row['tid'],
                'title' => '[����]'.$row['csa_text'],
                'start' => $row['csa_plan_et'],
                'end' => $row['csa_plan_et'],
                'url' => $row['url'],
                'allDay' => TRUE,
                // 'color' => '#1874CD'
            );
        }
        //���ﻹ��Ҫ��ӿ�ҵ��Ϣ

        return $data;
    }

    //��ȡ�Ŷ��¼�������
    public function get_team_events($project_id){
        //��ø���Ŀ�����г�Ա
        $user_arr = get_user_select_by_project($project_id);
        foreach($user_arr as $key => $val){ 
            //����û�id
            $userid = $val['uid'];
            //����û��ڱ���Ŀ�е�������Ϣ
            $sql = "select * from tk_task where csa_to_user=$userid and csa_project=$project_id";
            $query = mysql_query($sql);
            while($row=mysql_fetch_array($query)){
                $data[] = array(
                    'id' => $row['tid'],
                    'title' => '[����]-['.$val['name'].']'.$row['csa_text'],
                    'start' => $row['csa_plan_et'],
                    'end' => $row['csa_plan_et'],
                    'url' => $row['url'],
                    'allDay' => TRUE,
                    // 'color' => '#1874CD'
                );
            }
            //����û��ĸ����ճ�
            $sql = "select * from tk_schedule where uid='$userid'";
            $query = mysql_query($sql);
            while($row=mysql_fetch_array($query)){
                if($row['is_allday'] ==0){
                    $allday = FALSE;
                }else{
                    $allday = TRUE;
                }
                $data[] = array(
                'id' => $row['id'],
                'title' => '[����]-['.$val['name'].']'.$row['name'],
                'start' => $row['start_time'],
                'end' => $row['end_time'],
                'url' => $row['url'],
                'color' => '#008573',
                'allDay' => $allday
                );
            }
            //���ﻹ��Ҫ���ÿ����Ա�Ŀ�ҵ��Ϣ

        }   
        return $data;
    }
}

?>