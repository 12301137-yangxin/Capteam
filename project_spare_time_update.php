<?php
	
//$uid=$_SESSION["MM_uid"];
$start=$row_DetailRS1['project_start'];
$end=$row_DetailRS1['project_end'];
$length=floor((strtotime($end)-strtotime($start))/86400);

$fp = fopen("project_spare_time_data.json", "w"); 
fwrite($fp,"{\r\n");

for($i=0;$i<$length;$i++){//ÿ��
	for($j=0;$j<24;$j++){//ÿ��Сʱ
	
		$grade=0;
		
		foreach($user_arr as $key => $val){ 
			$uid=$val["uid"];
			//���ҿγ�
			mysql_select_db($database_tankdb,$tankdb);	

			$date = date('Y-m-d',strtotime("$start +$i days"));
			$hstart = sprintf('%02d', $j).":00:00";
			$hend = sprintf('%02d', $j).":59:59";

			//�����ݿ��ȡ�γ���Ϣ
			$selCSSQL = "SELECT * FROM tk_course_schedule WHERE cs_uid=$uid";
			$RS1 = mysql_query($selCSSQL, $tankdb) or die(mysql_error());
			$CSinfo = mysql_fetch_assoc($RS1);
			$csid = $CSinfo['cs_id'];

			$selCourseSQL = "SELECT * FROM tk_course WHERE course_csid = $csid";
			$RS2 = mysql_query($selCourseSQL, $tankdb) or die(mysql_error());

			//�жϸ������Ǳ�ѧ�ڵĵڼ��ܵ��ܼ�
			$datearr = explode("-", $date); //��������ʱ��ʹ�á�-���ָ������
			$year = $datearr[0]; //��ȡ���
			$month = sprintf('%02d', $datearr[1]); //��ȡ�·�
			$day = sprintf('%02d', $datearr[2]); //��ȡ����
			$hour = $minute = $second = 0; //Ĭ��ʱ�����Ϊ0
			$trans_date = mktime($hour, $minute, $second, $month, $day, $year); //��ʱ��ת����ʱ���
			$dayofweek =  date("w", $trans_date); //��ȡ����ֵ
			$lastday = date('Y-m-d',strtotime("$date Sunday"));
			//echo $lastday." ";
			$this_Mon = date('Y-m-d',strtotime("$lastday -6 days"));//�õ�ָ�����������ܵ���һ����
			//echo $this_Mon." ";
			//echo $CSinfo['cs_firstday']." ";
			$week_num = (strtotime($this_Mon)-strtotime($CSinfo['cs_firstday']))/86400/7 + 1;//�����ǵڼ���
			//echo $week_num."<br>";
			//echo $week_num;

			if($week_num >= 1)//�ڿ�ѧ����֮ǰ
			{
				//�ж��ڸ���ʱ����Ƿ��пγ�
				while($row=mysql_fetch_assoc($RS2))
				{
					if($week_num>=$row['course_startweek'] && $week_num<=$row['course_endweek'])
					{
						if($row['course_day']==$dayofweek)
						{
							if($hstart <= $row['course_starttime'] && $hend> $row['course_starttime'])
							{
								//$src = $src.$row['course_name']."(".$row['course_starttime']."~".$row['course_endtime'].");";
								$grade=$grade+20;
							}
							else if($hend >= $row['course_endtime'] && $hstart < $row['course_endtime'])
							{
								//$src = $src.$row['course_name']."(".$row['course_starttime']."~".$row['course_endtime'].");";
								$grade=$grade+20;
							}
							else if($hstart > $row['course_starttime'] && $hend < $row['course_endtime'])
							{
								//$src = $src.$row['course_name']."(".$row['course_starttime']."~".$row['course_endtime'].");";
								$grade=$grade+20;
							}
										//$grade=$grade+20;
						}
					}
				}
			}
			
			//���Ҹ����ճ�
			 $sql = "select * from tk_schedule where uid=".$uid." and ( 
							(end_time<= '".date("Y-m-d H:i:s",strtotime($date." ".$hend))."' and  
							start_time>= '".date("Y-m-d H:i:s",strtotime($date." ".$hstart))."') or 
							(end_time>= '".date("Y-m-d H:i:s",strtotime($date." ".$hend))."' and  
							start_time<= '".date("Y-m-d H:i:s",strtotime($date." ".$hstart))."')or 
							(end_time>= '".date("Y-m-d H:i:s",strtotime($date." ".$hstart))."' and
							end_time<= '".date("Y-m-d H:i:s",strtotime($date." ".$hend))."' and  
							start_time<= '".date("Y-m-d H:i:s",strtotime($date." ".$hstart))."')or 
							(start_time>= '".date("Y-m-d H:i:s",strtotime($date." ".$hstart))."' and 
							start_time<= '".date("Y-m-d H:i:s",strtotime($date." ".$hend))."' and  
							end_time>= '".date("Y-m-d H:i:s",strtotime($date." ".$hend))."')
							)";
            $query = mysql_query($sql);
            $row=mysql_num_rows($query);
			$grade=$grade+$row*10;
			
			//��������
			$sql = "select * from tk_task where csa_to_user=".$uid." and csa_plan_st<= '".$date."' and  
							csa_plan_et>= '".$date."'";
			$query = mysql_query($sql);
            $row=mysql_num_rows($query);
			$grade=$grade+$row*5;
					
		}
		if($grade>0){
			fwrite($fp,"\"".strtotime($date." ".$hstart)."\":".$grade.",\r\n");
		}
	}
}
fwrite($fp,"\"".strtotime("$start -1 days")."\":0\r\n}");
fclose($fp); 

?>