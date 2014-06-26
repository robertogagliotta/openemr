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
	$message = "test";
	echo "<script type='text/javascript'>alert('$message');</script>";

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
				"WHERE lst_opt.option_id NOT IN " .
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
	//TODO: implement code to add a new plan with its corresponding rules
	
	return null;
}

function deletePlan($plan_id) {
	
}

function togglePlanStatus($plan_id, $isActive) {
	
}

function submitChanges($plan_id, $RuleTransactionList) {
	
}

?>