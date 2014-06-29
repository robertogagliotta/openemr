<?php

require_once( dirname(__FILE__) . "/../../../globals.php" );
require_once( $GLOBALS['srcdir'] . "/log.inc");
require_once( $GLOBALS['srcdir'] . "/sql.inc");

if ($_GET["action"] == "getNonCQMPlans") {
	$plans = getNonCQMPlans();
	
	echo '[';
	$i = 0;
	foreach ($plans as $key => $value) {
		if ($i > 0) {
			echo ", ";
		}
		
		echo '{ "plan_id":"' . $key . '" , "plan_title":"' . $value . '" }';
		$i++;
	}
	echo ']';
} else if ($_GET["action"] == "getRulesOfPlan") {
	$rules = getRulesInPlan($_GET["plan_id"]);
	
	echo '[';
	$i = 0;
	foreach ($rules as $key => $value) {
		if ($i > 0) {
			echo ", ";
		}
	
		echo '{ "rule_id":"' . $key . '" , "rule_title":"' . $value . '" }';
		$i++;
	}
	echo ']';
} else if ($_GET["action"] == "getRulesNotInPlan") {
	$rules = getRulesNotInPlan($_GET["plan_id"]);
	
	echo '[';
	$i = 0;
	foreach ($rules as $key => $value) {
		if ($i > 0) {
			echo ", ";
		}
	
		echo '{ "rule_id":"' . $key . '" , "rule_title":"' . $value . '" }';
		$i++;
	}
	echo ']';
	
} else if ($_GET["action"] == "getRulesInAndNotInPlan") {
	$rules = getRulesInPlan($_GET["plan_id"]);

	echo '[';
	
	$i = 0;
	foreach ($rules as $key => $value) {
		if ($i > 0) {
			echo ", ";
		}

		echo '{ "rule_id":"' . $key . '" , "rule_title":"' . $value . '" , "selected":"true" }';
		$i++;
	}
	
	$rules = getRulesNotInPlan($_GET["plan_id"]);
	foreach ($rules as $key => $value) {
		if ($i > 0) {
			echo ", ";
		}
	
		echo '{ "rule_id":"' . $key . '" , "rule_title":"' . $value . '" , "selected":"false" }';
		$i++;
	}
	
	echo ']';
	
} else if ($_GET["action"] == "commitChanges") {
	$data = json_decode(file_get_contents('php://input'), true);
	
	$plan_id = $data['plan_id'];
	$added_rules = $data['added_rules'];
	$removed_rules = $data['removed_rules'];
	$plan_name = $data['plan_name'];
	
	if ($plan_id == 'add_new_plan') {
		$plan_id = addNewPlan($plan_name, $added_rules);
	} else if (strlen($plan_id) > 0) {
		submitChanges($plan_id, $added_rules, $removed_rules);
	}	
	
	echo '{ "plan_id":"' . $plan_id . '" , "plan_title":"' . $plan_name . '" }';
	
} else if ($_GET["action"] == "deletePlan") {
	deletePlan($_GET["plan_id"]);
}



function getNonCQMPlans() {	
	$plans = array();
	
	$sql_st = "SELECT DISTINCT list_options.title, clin_plans_rules.plan_id " .
				"FROM `list_options` list_options " . 
				"JOIN `clinical_plans_rules` clin_plans_rules ON clin_plans_rules.plan_id = list_options.option_id " .
				"JOIN `clinical_rules` clin_rules ON clin_rules.id = clin_plans_rules.rule_id " .
				"WHERE (clin_rules.cqm_flag = 0 or clin_rules.cqm_flag is NULL) and list_options.list_id = ?;";
	$result = sqlStatement($sql_st, array('clinical_plans'));
	
	while($row = sqlFetchArray($result)) {
		$plans[$row['plan_id']] = $row['title'];
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
				"WHERE clin_rules.cqm_flag = 0 AND lst_opt.option_id NOT IN " .
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
	//$plan_id = strtolower(preg_replace('/\s+/', '_', $plan_name)) . '_plan';
	
	/** plan_id start **/
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
	
	/** plan_id end **/
	
	
	/** clinical_plans start **/
	$sql_st = "INSERT INTO `clinical_plans` (`id`, `pid`, `normal_flag`, `cqm_flag`, `cqm_measure_group`) " . 
				"VALUES (?, 0, 1, 0, '');";
	$res = sqlStatement($sql_st, array($plan_id));
	
	/** clinical_plans end **/
	
	
	/** list_options start **/
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
	
	$sql_st = "INSERT INTO `list_options` " .
				"(`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) " .
				"VALUES ('clinical_plans', ?, ?, ?, 0, 0, '', '', '');";
	$res = sqlStatement($sql_st, array($plan_id, $plan_name, $max_seq));
	
	/** list_options end **/
	
	
	/** rules start **/
	addRulesToPlan($plan_id, $plan_rules);
	
	/*
	sleep(3);
	$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
	$txt = $plan_id;
	fwrite($myfile, $txt);
	fclose($myfile);
	*/
	
	return $plan_id;
}

function deletePlan($plan_id) {
	$sql_st = "DELETE FROM `clinical_plans` WHERE `clinical_plans`.`id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id));
	
	$sql_st = "DELETE FROM `list_options` WHERE `list_id` = 'clinical_plans' AND `option_id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id));
	
	$sql_st = "DELETE FROM `clinical_plans_rules` WHERE `plan_id` = ?;";
	$res = sqlStatement($sql_st, array($plan_id));
}

function togglePlanStatus($plan_id, $isActive) {
	//TODO:
	
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

?>