<?php
require_once( dirname(__FILE__) . "/../../../globals.php" );
require_once( $GLOBALS['srcdir'] . "/log.inc");
require_once( $GLOBALS['srcdir'] . "/sql.inc");

$action = $_GET["action"];
switch ($action) {
	case "getNonCQMPlans":
		$plans = getNonCQMPlans();

		echo json_encode($plans);
		
		break;
		
	case "getRulesOfPlan":
		$rules = getRulesInPlan($_GET["plan_id"]);
		
		$rules_list = array();
		foreach ($rules as $key => $value) {
			$rule_info = array('rule_id'=>$key, 'rule_title'=>$value);
			array_push($rules_list,$rule_info);
		}
		
		echo json_encode($rules_list);
		
		break;
	
	case "getRulesNotInPlan":
		$rules = getRulesNotInPlan($_GET["plan_id"]);
		
		$rules_list = array();
		foreach ($rules as $key => $value) {
			$rule_info = array('rule_id'=>$key, 'rule_title'=>$value);
			array_push($rules_list,$rule_info);
		}
		
		echo json_encode($rules_list);
		
		break;
		
	case "getRulesInAndNotInPlan":
		$rules = getRulesInPlan($_GET["plan_id"]);
		
		$rules_list = array();
		foreach ($rules as $key => $value) {
			$rule_info = array('rule_id'=>$key, 'rule_title'=>$value, 'selected'=>'true');
			array_push($rules_list,$rule_info);
		}		
		
		$rules = getRulesNotInPlan($_GET["plan_id"]);
		foreach ($rules as $key => $value) {
			$rule_info = array('rule_id'=>$key, 'rule_title'=>$value, 'selected'=>'false');
			array_push($rules_list,$rule_info);
		}
		
		echo json_encode($rules_list);
		
		break;
		
	case "commitChanges":
		$data = json_decode(file_get_contents('php://input'), true);
		
		$plan_id = $data['plan_id'];
		$added_rules = $data['added_rules'];
		$removed_rules = $data['removed_rules'];
		$plan_name = $data['plan_name'];
		
		if ($plan_id == 'add_new_plan') {
			try {
				$plan_id = addNewPlan($plan_name, $added_rules);
			} catch (Exception $e) {
				if ($e->getMessage() == "002") {
					//Plan Name Taken
					$status = array('status_code'=>'002', 'status_message'=>'Plan Name Already Exists!', 'plan_id'=>$plan_id, 'plan_title'=>$plan_name);
					echo json_encode($status);
						
				} else if ($e->getMessage() == "003") {
					//Already in list options
					$status = array('status_code'=>'003', 'status_message'=>'Plan Already in list_options', 'plan_id'=>$plan_id, 'plan_title'=>$plan_name);
					echo json_encode($status);
											
				} else {
					$status = array('status_code'=>'001', 'status_message'=>$e->getMessage(), 'plan_id'=>$plan_id, 'plan_title'=>$plan_name);
					echo json_encode($status);
									}
					
				break;
			}
		} else if (strlen($plan_id) > 0) {
			submitChanges($plan_id, $added_rules, $removed_rules);
		}
		
		$status = array('status_code'=>'000', 'status_message'=>'Success', 'plan_id'=>$plan_id, 'plan_title'=>$plan_name);
		echo json_encode($status);
		
		break;
		
	case "deletePlan":
		$plan_id = $_GET["plan_id"];
		$plan_pid = $_GET["plan_pid"];
		deletePlan($plan_id, $plan_pid);
		
		break;

       case "togglePlanStatus":
                $dataToggle  = json_decode(file_get_contents('php://input'), true);

                $plan_id_toggle = $dataToggle['selected_plan'];
                $plan_pid_toggle = $dataToggle['selected_plan_pid'];
                $active_inactive = $dataToggle['plan_status'];
                if ($active_inactive == 'deactivate') {
                     $nm_flag = 0;
                   } else {
                     $nm_flag = 1;
                   }
                try {
                       togglePlanStatus($plan_id_toggle, $nm_flag);
                } catch (Exception $e) {
                     if ($e->getMessage() == "007")
                       {
                        $code_back = "007";
                        echo json_encode($code_back);
                       }
                     if  ($e->getMessage() == "002") {
                         $code_back = "002";
                         echo json_encode($code_back);
                  }
	       	}
               break;
           
	case "getPlanStatus":
		$plan_id = $_GET["plan_id"];
		$plan_pid = $_GET["plan_pid"];
		
		$isPlanActive = isPlanActive($plan_id, $plan_pid);
		
		$isPlanActive = ($isPlanActive) ? 1 : 0	;
		
		$plan_status = array('plan_id'=>$plan_id, 'plan_pid'=>$plan_pid, 'is_plan_active'=>$isPlanActive);
		echo json_encode($plan_status);
		
		break;
		
	default:
		break;
}


//Helper Functions
function getNonCQMPlans() {	
	$plans = array();
	
	$sql_st = "SELECT DISTINCT list_options.title, clin_plans_rules.plan_id, clin_plans.pid " .
				"FROM `list_options` list_options " . 
				"JOIN `clinical_plans` clin_plans ON clin_plans.id = list_options.option_id " .				
				"JOIN `clinical_plans_rules` clin_plans_rules ON clin_plans_rules.plan_id = list_options.option_id " .
				"JOIN `clinical_rules` clin_rules ON clin_rules.id = clin_plans_rules.rule_id " .
				"WHERE (clin_rules.cqm_flag = 0 or clin_rules.cqm_flag is NULL) and clin_plans.pid = 0 and list_options.list_id = ?;";
	$result = sqlStatement($sql_st, array('clinical_plans'));

	while($row = sqlFetchArray($result)) {
		$plan_id = $row['plan_id'];
		$plan_pid = $row['pid'];
		$plan_title = $row['title'];
		
		$plan_info = array('plan_id'=>$plan_id, 'plan_pid'=>$plan_pid, 'plan_title'=>$plan_title);
		array_push($plans, $plan_info);
	}
	return $plans;
}

function getRulesInPlan($plan_id) {
	$rules = array();
	
	$sql_st = "SELECT lst_opt.option_id as rule_option_id, lst_opt.title as rule_title " .
				"FROM `clinical_plans_rules` cpr " .
				"JOIN `list_options` lst_opt ON lst_opt.option_id = cpr.rule_id " .
				"WHERE cpr.plan_id = ?;";
	$result = sqlStatement($sql_st, array($plan_id));
	
	while($row = sqlFetchArray($result)) {
		$rules[$row['rule_option_id']] = $row['rule_title'];
	}
	
	return $rules;
}

function getRulesNotInPlan($plan_id) {
	$rules = array();
	
	$sql_st = "SELECT lst_opt.option_id as rule_option_id, lst_opt.title as rule_title " .
				"FROM `clinical_rules` clin_rules " .
				"JOIN `list_options` lst_opt ON lst_opt.option_id = clin_rules.id " .
				"WHERE clin_rules.cqm_flag = 0 AND clin_rules.amc_flag = 0 AND lst_opt.option_id NOT IN " .
					"( " .
					"SELECT lst_opt.option_id " .
					"FROM `clinical_plans_rules` cpr " .
					"JOIN `list_options` lst_opt ON lst_opt.option_id = cpr.rule_id " .
					"WHERE cpr.plan_id = ?" .
					"); ";
	$result = sqlStatement($sql_st, array($plan_id));
	
	while($row = sqlFetchArray($result)) {
		$rules[$row['rule_option_id']] = $row['rule_title'];
	}
	
	return $rules;
}

function addNewPlan($plan_name, $plan_rules) {
	//Validate if plan name already exists
	$sql_st = "SELECT `option_id` " .
				"FROM `list_options` " .
				"WHERE `list_id` = 'clinical_plans' AND `title` = ?;";
	$res = sqlStatement($sql_st, array($plan_name));
	$row = sqlFetchArray($res);
	if ($row['option_id'] != NULL) {
		throw new Exception("002");
	}
	
	//Generate Plan Id
	$plan_id = generatePlanID();
	
	
	//Validate if plan id already exists in list options table
	$sql_st = "SELECT `option_id` " .
				"FROM `list_options` " .
				"WHERE `option_id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id));
	$row = sqlFetchArray($res);
	if ($row != NULL) {
		//001 = plan name taken
		throw new Exception("003");
	}	
	
	//Add plan into clinical_plans table
	$sql_st = "INSERT INTO `clinical_plans` (`id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group`) " . 
				"VALUES (?, 0, 1, 0, '');";
	$res = sqlStatement($sql_st, array($plan_id));	
	
	
	//Get sequence value
	$sql_st = "SELECT MAX(`seq`) AS max_seq " .
				"FROM `list_options` " .
				"WHERE `list_id` = 'clinical_plans'; ";
	$res = sqlStatement($sql_st, null);
	$max_seq = 0;
	
	if ($res != NULL) {
		while($row = sqlFetchArray($res)) {
			$max_seq = $row['max_seq'];
		}
		$max_seq += 10;
	}
	
	
	//Insert plan into list_options table
	$sql_st = "INSERT INTO `list_options` " .
				"(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) " .
				"VALUES ('clinical_plans', ?, ?, ?, 0, 0, '', '', '');";
	$res = sqlStatement($sql_st, array($plan_id, $plan_name, $max_seq));	
	
	
	//Add rules to plan
	addRulesToPlan($plan_id, $plan_rules);
	
	return $plan_id;
}

function deletePlan($plan_id, $plan_pid) {
	$sql_st = "DELETE FROM `clinical_plans` WHERE `clinical_plans`.`id` = ? AND `clinical_plans`.`pid` = ?;";
	$res = sqlStatement($sql_st, array($plan_id, $plan_pid));
	
	$sql_st = "DELETE FROM `list_options` WHERE `list_id` = 'clinical_plans' AND `option_id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id));
	
	$sql_st = "DELETE FROM `clinical_plans_rules` WHERE `plan_id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id, $plan_pid));
}

function togglePlanStatus($plan_id, $nm_flag) {
         $pid_val = 0;
         $sql_st = "UPDATE clinical_plans SET " .
                   "normal_flag = ? ".
                   "WHERE id = ? AND pid = ? ";
         sqlStatement($sql_st, array($nm_flag, $plan_id, $pid_val));
         if ($nm_flag = 0)
           {
             $nm_chk = 1;
           } 
         if ($nm_flag = 1)
           {
             $nm_chk = 0;
           }
           $sql_check = "SELECT `id` " .
                              "FROM `clinical_plans` " .
                              "WHERE ((`id` = ?) AND (`pid` = ?) AND (`normal_flag` = ?));";
         $res_chk = sqlStatement($sql_check, array($plan_id, $pid_val, $nm_chk));
         $row_chk = sqlFetchArray($res_chk);
          if ($row_chk == $plan_id)
            {
              throw new Exception("002");
            }
          else
            {
               throw new Exception("007");
            }
}

function submitChanges($plan_id, $added_rules, $removed_rules) {
	//add
	if (sizeof($added_rules) > 0) {
		addRulesToPlan($plan_id, $added_rules);
	}
	
	//remove
	if (sizeof($removed_rules) > 0) {
		removeRulesFromPlan($plan_id, $removed_rules);
	}
	
}

function addRulesToPlan($plan_id, $list_of_rules) {
	$sql_st = "INSERT INTO `clinical_plans_rules` (`plan_id`, `rule_id`) " .
				"VALUES (?, ?);";
	
	foreach ($list_of_rules as $rule) {
		$res = sqlStatement($sql_st, array($plan_id, $rule));
	}
}

function removeRulesFromPlan($plan_id, $list_of_rules) {
	$sql_st = "DELETE FROM `clinical_plans_rules` " .
				"WHERE `plan_id` = ? AND `rule_id` = ?;";

	foreach ($list_of_rules as $rule) {
		$res = sqlStatement($sql_st, array($plan_id, $rule));
	}
}

function generatePlanID() {	
	$plan_id = 1;
	$sql_st = "SELECT MAX(SUBSTR(clin_plans.id, 1, LOCATE('_plan', clin_plans.id)-1)) as max_planid " .
			"FROM `clinical_plans` clin_plans " .
			"WHERE clin_plans.id like '%_plan' AND SUBSTR(clin_plans.id, 1, LOCATE('_plan', clin_plans.id)) REGEXP '[0-9]+'; ";
	$res = sqlStatement($sql_st, null);
	
	if ($res != NULL) {
		while($row = sqlFetchArray($res)) {
			$plan_id = $row['max_planid'];
		}
		$plan_id += 1;
	}
	
	$plan_id = $plan_id . '_plan';
	
	return $plan_id;
}

function isPlanActive($plan_id, $plan_pid) {
	$sql_st = "SELECT `normal_flag` " . 
				"FROM `clinical_plans` " . 
				"WHERE `id` = ? AND `pid` = ?;";

	$res = sqlStatement($sql_st, array($plan_id, $plan_pid));

	$row = sqlFetchArray($res);
	if ($row['normal_flag'] == 1) {
		return true;
	} else {		
		return false;
	}
}

?>
