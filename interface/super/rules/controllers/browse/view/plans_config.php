<?php
// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
?>

<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['webroot'] . '/library/css/jquery-ui-1.8.21.custom.css'?>" />
<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/common.css') ?>" />
<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/ui.multiselect.css') ?>" />

<style type="text/css">
 	.cdr-mappings
 	{
 		display: table;
 		margin-left:15px;
 	}
 	
 	.cdr-buttons-class
 	{
 		float:right;
 	}
 </style>

<script language="javascript" src="<?php js_src('/cdr-multiselect/jquery.min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/jquery-ui.min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/plugins/localisation/jquery.localisation-min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/plugins/scrollTo/jquery.scrollTo-min.js') ?>"></script>
<script language="javascript" src="<?php js_src('/cdr-multiselect/ui.multiselect.js') ?>"></script>
<script language="javascript" src="<?php js_src('list.js') ?>"></script>
<script language="javascript" src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>


<script type="text/javascript">
	$(document).ready(function() {
		$("#cdr-plans").load('<?php library_src('RulesPlanMappingEventHandlers.php') ?>');
		
	    $.post(
	    	'<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers.php?action=getNonCQMPlans'; ?>'
	    ).success(function(resp) {
	        var data = $.parseJSON(resp);

	        $.each(data, function(idx, obj) {
	        	$('<option id="' + obj.plan_id + '" value="' + obj.plan_id + '">' + obj.plan_title + '</option>')
	        		.insertAfter('#select_plan')
	        		.insertBefore('#divider');
	        });	        
	    });

		$("#cdr-plans-select").change(function(){
			var selected_plan = this.value;
			$("#cdr_rules").empty();
			
			if (selected_plan != 'select_plan') {
				$("#cdr_hide_show-div").show();	

			    $.post
			    	(
				    	'<?php echo  _base_url() . 
				    			'/library/RulesPlanMappingEventHandlers.php?action=getRulesInAndNotInPlan&plan_id='; ?>' 
				    	+ selected_plan								
					)
					.success(function(resp) {
				        var data = $.parseJSON(resp);
				        
				        $('#cdr_rules')
				        	.append('<select id="cdr_rules_select" class="multiselect" multiple="multiple" name="cdr_rules_select[]"/>');
				        
				        $.each(data, function(idx, obj) {  		
							if (obj.selected  == "true") {
								$("#cdr_rules_select")
									.append(
										$('<option value="' + obj.rule_id + '" selected="selected" init_value="selected">' + obj.rule_title + '</option>')
									);
							} else {
								$("#cdr_rules_select")
									.append(
										$('<option value="' + obj.rule_id + '" init_value="not-selected">' + obj.rule_title + '</option>')
									);
							}								
						});

				        $("#cdr_rules_select").multiselect({dividerLocation: 0.45});
			     	});	
		     				    
			} else {
				$("#cdr_hide_show-div").hide();
			}		
		});

		$("#cdr-status-deactivate").click(function() {
			$("#cdr-status-deactivate").attr("disabled", true);
			$("#cdr-status-activate").removeAttr("disabled");
		});

		$("#cdr-status-activate").click(function() {
			$("#cdr-status-activate").attr("disabled", true);
			$("#cdr-status-deactivate").removeAttr("disabled");
		});

		$("#cdr-button-cancel").click(function() {
			alert('cancel')
		});
		
		$("#cdr-button-submit").click(function() {
			var postData = 
            {
                "bid":"test",
                "location1":"1"
            }
			var dataString = JSON.stringify(postData);

			$.ajax({
		        type: "POST",
		        dataType: "json",
		        url: "<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers.php?action=commitChanges'; ?>",
		        data: {myData:dataString},
		        contentType: "application/json; charset=utf-8",
		        success: function(data){
		            alert('Items added');
		        },
		        error: function(e){
		            console.log(e.message);
		        }
			});
		});
		
	});
</script>

<div class="cdr-mappings">
	<br/>
	<div><b>View Plan Rules</b></div>
	<br/>
	<div id="cdr_mappings_form-div" class="cdr-form">
		<!--<form action="library/RulesPlanMappingEventHandlers.php?action=commitChanges" method="post">-->
			<div class="cdr-plans">
				Plan:
				<select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class">
					<option id="select_plan" value="select_plan">- SELECT PLAN -</option>
					<option id="divider" value="divider" disabled/>
					<option value="add_new_plan">ADD NEW PLAN</option>
				</select>
			</div>	
			<div id="cdr_hide_show-div" style="display: none;">
				<div class="plan-status_div">
					<label class="plan-status-label">Status:</label>
					<button id='cdr-status-activate' disabled>Activate</button>
		      		<button id='cdr-status-deactivate'>Deactivate</button>
				</div>
				<br/>
				<div id="cdr_rules" class="cdr-rules-class"></div>   	
		      	
		      	<div id="cdr_buttons_div" class="cdr-buttons-class">
		      		<button id='cdr-button-cancel'>Cancel</button>
		      		<button id='cdr-button-submit'>Submit</button>
		      	</div>
	      	</div>

	<!--</form>-->
	</div>
</div>

