<?php
class RuleTransaction {
	public $ruleid;
	public $transactionType;
}

function getNonCQMPlans() {
	$result = mysql_query("call get_noncqm_cdr_plans('clinical_plans')");
	if ($result === FALSE) {
		die(mysql_error());
	}

	$plans = array();
	
	while($row = mysql_fetch_array($result)) {
		$plans[$row['plan_id']] = $row['title'];
	}
	
	return $plans;
}

function getRulesOfPlan($listOfPlans) {
	//TODO: implement code to return rules for each plan in the input
	
	return null;
}

function getRulesNotInPlan($listOfPlans) {
	//TODO: implement code to return rules that are not part of each plan in the input
	
	return null;
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