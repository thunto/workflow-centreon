<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	
	if (!isset($oreon))
	  exit();
	
	$debug = 0;
	
	unset ($host_status);
	unset ($service_status);
	
	//$mem_befor = memory_get_usage() / 1024 / 1024 ;
	//print "  befor : " . $mem_befor . "Mo";


	function get_program_data($log, $status_proc){
	  $pgr_nagios_stat = array();
	  $pgr_nagios_stat["program_start"] = $log['1'];
	  $pgr_nagios_stat["nagios_pid"] = $log['2'];
	  $pgr_nagios_stat["daemon_mode"] = $log['3'];
	  $pgr_nagios_stat["last_command_check"] = $log['4'];
	  $pgr_nagios_stat["last_log_rotation"] = $log['5'];
	  $pgr_nagios_stat["enable_notifications"] = $log['6'];
	  $pgr_nagios_stat["execute_service_checks"] = $log['7'];
	  $pgr_nagios_stat["accept_passive_service_checks"] = $log['8'];
	  $pgr_nagios_stat["enable_event_handlers"] = $log['9'];
	  $pgr_nagios_stat["obsess_over_services"] = $log['10'];
	  $pgr_nagios_stat["enable_flap_detection"] = $log['11'];
	  $pgr_nagios_stat["process_performance_data"] = $log['13'];
	  $pgr_nagios_stat["status_proc"] = $status_proc;
	  return ($pgr_nagios_stat);
	}
		
	function get_host_data($log){
	  $host_data["host_name"] = $log['1'];
	  $host_data["current_state"] = $log['2'];
	  $host_data["last_check"] = $log['3'];
	  $host_data["last_state_change"] = $log['4'];
	  $host_data["problem_has_been_acknowledged"] = $log['5'];
	  $host_data["time_up"] = $log['6'];
	  $host_data["time_down"] = $log['7'];
	  $host_data["time_unrea"] = $log['8'];
	  $host_data["last_notification"] = $log['9'];
	  $host_data["current_notification_number"] = $log['10'];
	  $host_data["notifications_enabled"] = $log['11'];
	  $host_data["event_handler_enabled"] = $log['12'];
	  $host_data["active_checks_enabled"] = $log['13'];
	  $host_data["flap_detection_enabled"] = $log['14'];
	  $host_data["is_flapping"] = $log['15'];
	  $host_data["percent_state_change"] = $log['16'];
	  $host_data["scheduled_downtime_depth"] = $log['17'];
	  $host_data["failure_prediction_enabled"] = $log['18'];
	  $host_data["process_performance_data"] = $log['19'];
	  $host_data["plugin_output"] = $log['20'];
	  return ($host_data);
	}
	
	function get_service_data($log){
	  $svc_data["host_name"] = $log[1];
	  $svc_data["service_description"] = $log[2];
	  $svc_data["current_state"] = $log[3];
	  $svc_data["current_attempt"] = $log[4];
	  $svc_data["stat_type"] = $log[5];
	  $svc_data["last_check"] = $log[6];
	  $svc_data["next_check"] = $log[7];
	  $svc_data["check_type"] = $log[8];
	  $svc_data["active_checks_enabled"] = $log[9];
	  $svc_data["passive_checks_enabled"] = $log[10];
	  $svc_data["event_handler_enabled"] = $log[11];
	  $svc_data["last_state_change"] = $log[12];
	  $svc_data["problem_has_been_acknowledged"] = $log[13];
	  $svc_data["last_hard_state_change"] = $log[14];
	  $svc_data["ok"] = $log[15];
	  $svc_data["warning"] = $log[16];
	  $svc_data["unknown"] = $log[17];
	  $svc_data["critical"] = $log[18];
	  $svc_data["last_notification"] = $log[19];
	  $svc_data["current_notification_number"] = $log[20];
	  $svc_data["notifications_enabled"] = $log[21];
	  $svc_data["check_latency"] = $log[22];
	  $svc_data["check_execution_time"] = $log[23];
	  $svc_data["flap_detection_enabled"] = $log[24];
	  $svc_data["is_flapping"] = $log[25];
	  $svc_data["percent_state_change"] = $log[26];
	  $svc_data["scheduled_downtime_depth"] = $log[27];
	  $svc_data["failure_prediction_enabled"] = $log[28];
	  $svc_data["process_performance_data"] = $log[29];
	  $svc_data["obsess_over_service"] = $log[30];
	  $svc_data["plugin_output"] = $log[31];
	  $svc_data["total_running"] = $log[15]+$log[16]+$log[17]+$log[18];
	  return ($svc_data);
	}

	$t_begin = microtime_float();

	// Open File
	if (file_exists($oreon->Nagioscfg["status_file"])){
		$log_file = fopen($oreon->Nagioscfg["status_file"], "r");
	 	$status_proc = 1;
	} else {
	  	$log_file = 0;
	  	$status_proc = 0;
	}
	
	// init table
	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();
	
	// Read 
	
	$lca =& $oreon->user->lcaHost;
	$version = $oreon->user->get_version();
	
	// Stats
	$oreon->status_graph_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
	$oreon->status_graph_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => "0");
	
	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
		    

	if ($version == 1){
	  if ($log_file)
	    while ($str = fgets($log_file))		{
	      	// set last update 
	     	$last_update = date("d-m-Y h:i:s");
	      	if (!preg_match("/^\#.*/", $str)){		// get service stat
				$log = split(";", $str);
				if (preg_match("/^[\[\]0-9]* SERVICE[.]*/", $str)){
		  			if (array_search($log["1"], $lca)){
						$service_status[$log["1"]."_".$log["2"]] = get_service_data($log);
			    		$tab_host_service[$log["1"]][$log["2"]] = "1";
			 		} else if (!strcmp($log[1], "Meta_Module")){
			    		$metaService_status[$log["2"]] = get_service_data($log);
		  			}
				} else if (preg_match("/^[\[\]0-9]* HOST[.]*/", $str)){ // get host stat
		  			if (array_search($log["1"], $lca)){
		    			$host_status[$log["1"]] = get_host_data($log);
		    			$tab_host_service[$log["1"]] = array();
		  			}
				} else if (preg_match("/^[\[\]0-9]* PROGRAM[.]*/", $str))
		  			$program_data = get_program_data($log, $status_proc);
		  	}
	      	unset($str);
		}
	} else {
		if ($log_file)
	    	while ($str = fgets($log_file)) {
	      		$last_update = date("d-m-Y h:i:s");
	      		if (!preg_match("/^\#.*/", $str)){
					if (preg_match("/^service/", $str)){
				  		$log = array();
				  		while ($str2 = fgets($log_file))
		          			if (!strpos($str2, "}")){      
			      				if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
									$svc_data[$tab[1]] = $tab[2];	
			    			} else
			      				break;
			      		if (strstr("Meta_Module", $svc_data['host_name'])){
			      			$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
			      			$metaService_status[$svc_data["service_description"]] = $svc_data;
			      		} else {
			      			if (isset($svc_data['host_name']) && array_search($svc_data['host_name'], $lca)
								&&
								(($search && $search_type_host == 1 &&  strpos(strtolower($svc_data['host_name']), strtolower($search)) !== false)								
								||
								($search &&$search_type_service == 1 && strpos(strtolower($svc_data['service_description']), strtolower($search)) !== false) 
								||
								($search_type_service == NULL && $search_type_host == NULL)
								||
								 !$search
								))
								{
				      			$svc_data["current_state"] = $tab_status_svc[$svc_data['current_state']];
				      			$service_status[$svc_data["host_name"] . "_" . $svc_data["service_description"]] = $svc_data;
				      			$tab_host_service[$svc_data["host_name"]][$svc_data["service_description"]] = "1";
				      			$oreon->status_graph_service[$svc_data['current_state']]++;
								/*
								if(strpos($svc_data['host_name'], 'forum') !== false)
									echo "---<br>";
								*/
			      			}
			      		}
					} else if (preg_match("/^host/", $str)){ // get host stat
						$host_data = array();
			  			while ($str2 = fgets($log_file))
			    		if (!strpos($str2, "}")){
			      			if (preg_match("/([A-Za-z0-9\_\-]*)\=(.*)[\ \t]*/", $str2, $tab))
								$host_data[$tab[1]] = $tab[2];
			    		} else
			      			break;
			      		if (isset($host_data['host_name']) && array_search($host_data['host_name'], $lca)){
				      		$host_data["current_state"] = $tab_status_host[$host_data['current_state']];
							$host_status[$host_data["host_name"]] = $host_data;
							$oreon->status_graph_host[$host_data['current_state']]++;
			      		}
					} else if (preg_match("/^program/", $str)){
		          		while ($str2 = fgets($log_file))
		            		if (!strpos($str2, "}")){
		              			if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab))
		                			$pgr_nagios_stat[$tab[1]] = $tab[2];
		            		} else
		              			break;
		          		unset($log);
		        	} else if (preg_match("/^info/", $str)){
		          		while ($str2 = fgets($log_file))
		            		if (!strpos($str2, "}")){
		            	  		if (preg_match("/([A-Za-z0-9\_\-]*)\=([A-Za-z0-9\_\-\.\,\(\)\[\]\ \=\%\;\:]+)/", $str2, $tab))
		                			$pgr_nagios_stat[$tab[1]] = $tab[2];
		            		} else
		              			break;
		        	}
					unset($str);	
	      		}
	    	}
	}
	$row_data = array();
	if (isset($_GET["o"]) && $_GET["o"] == "svcSch" && !isset($_GET["sort_types"])){
		$_GET["sort_types"] = "next_check";
		$_GET["order"] = "SORT_ASC";
	}
	
	if (isset($_GET["sort_types"]) && $_GET["sort_types"]){
	  foreach ($service_status as $key => $row)
	    $row_data[$key] = $row[$_GET["sort_types"]];
	  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $service_status) : array_multisort($row_data, SORT_DESC, $service_status);
	}
	if (isset($_GET["sort_typeh"]) && $_GET["sort_typeh"]){
	  foreach ($host_status as $key => $row)
	    $row_data[$key] = $row[$_GET["sort_typeh"]];
	  !strcmp(strtoupper($_GET["order"]), "SORT_ASC") ? array_multisort($row_data, SORT_ASC, $host_status) : array_multisort($row_data, SORT_DESC, $host_status);
	}
	
	if ($debug){ ?>
	 <textarea cols='200' rows='50'><? print_r($host_status);print_r($service_status);?></textarea><?	
	}
	
	//$mem_after = memory_get_usage() / 1024 / 1024 ;
	//$peak = memory_get_peak_usage() / 1024 / 1024;
	//$mem = $mem_after - $mem_befor;
	
	//print "After : " .$mem_after . "Mo # ".$mem."Mo";

?> 