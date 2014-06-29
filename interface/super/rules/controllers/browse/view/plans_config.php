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
<link type="text/css" rel="stylesheet" href="<?php css_src('cdr-multiselect/plans_config.css') ?>" />

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

		$("#cdr-plans-select").change(function() {
			$loadRules($('#cdr-plans-select').find('option:selected').attr('id'));
		});

		$("#cdr-status-deactivate").click(function() {
			$("#cdr-status-deactivate").attr("disabled", true);
			$('#cdr-status-deactivate').text('Inactive');

			$("#cdr-status-activate").removeAttr("disabled");
			$('#cdr-status-activate').text('Activate');

			$("#cdr-button-submit").attr('disabled', true); 
			

			//TODO: Implement deactivate button
		});

		$("#cdr-status-activate").click(function() {
			$("#cdr-status-activate").attr("disabled", true);
			$('#cdr-status-activate').text('Active');
			
			
			$("#cdr-status-deactivate").removeAttr("disabled");
			$('#cdr-status-deactivate').text('Deactivate');


			$("#cdr-button-submit").attr('disabled', false); 

			//TODO: Implement activate button
		});

		$("#cdr-button-cancel").click(function() {
			if (confirm('Are you sure you want to cancel your changes?')) {
				$loadRules($('#cdr-plans-select').find('option:selected').attr('id'));
	        }
		});

		$("#delete_plan").click(function() {
			if (confirm('Are you sure you want to delete this plan?')) {
				var selected_plan = $('#cdr-plans-select').find('option:selected').attr('id');
				
				$.post
		    	(
			    	'<?php echo  _base_url() . 
			    			'/library/RulesPlanMappingEventHandlers.php?action=deletePlan&plan_id='; ?>' + selected_plan								
				)
				.success(function(resp) {
					alert('Plan Deleted!');
					location.reload();    
			    })
			    .error(function(error) {
				    console.log(error);
					alert('Error while deleting the plan!');
			    });
	        }
		});
		
		$("#cdr-button-submit").click(function() {			
			var plan_id = $('#cdr-plans-select').find('option:selected').attr('id');
			var plan_name = $('#cdr-plans-select').find('option:selected').text();
			var is_new_plan = false;

			if (plan_id == 'add_new_plan') {
				plan_name = $("#new_plan_name").val();
				is_new_plan = true;
			}
			
			var new_selected = new Array;
			var new_unselected = new Array;

			$('#cdr_rules_select option').each(function() {				
				if ($(this).attr('selected') && ($(this).attr('init_value') == 'not-selected')) {
					new_selected.push($(this).val());
					
				} else if (!$(this).attr('selected') && ($(this).attr('init_value') == 'selected')) {
					new_unselected.push($(this).val());
				}
				
			});

			//Validate
			if (new_selected.length == 0 && new_unselected.length == 0) {
				alert('No Changes Detected');
				return;
			} else if (is_new_plan && plan_name.length == 0) {
				alert('Plan Name Missing');
				return;
			} 

			$("body").addClass("loading");
			
			var postData = 
	            {
			        "plan_id": plan_id,
	                "added_rules": new_selected,
	                "removed_rules": new_unselected,
	                "plan_name" : plan_name
	            }
			var dataString = JSON.stringify(postData);
			
			$.ajax({
		        type: "POST",
		        dataType: "json",
		        url: "<?php echo  _base_url() . '/library/RulesPlanMappingEventHandlers.php?action=commitChanges'; ?>",
		        data: dataString,
		        contentType: "application/json; charset=utf-8",
		        success: function(resp){
			        if (is_new_plan) {
			           	alert('Plan Added Successfully!');
			           	location.reload();
			           	
			        } else {
			           	alert('Plan Updated Successfully!');
			            $("body").removeClass("loading");
			            $loadRules(plan_id);
			        }
		        },
		        error: function(xhr, status, e){
		            console.log(xhr);
		            if (is_new_plan) {
			           	alert('Error while adding new plan!');			           	
			        } else {
			           	alert('Error while updating the plan!');
			        }
			        
		            $("body").removeClass("loading");
		        }
			});			
		});
	});

	$loadRules = function(selected_plan){		
		$("#cdr_rules").empty(selected_plan);
		$('#new_plan_container').empty();
		
		if (selected_plan != 'select_plan') {
			$("#cdr_hide_show-div").show();	
			$("#delete_plan").show();
			$("body").addClass("loading");
		    $.post
		    	(
			    	'<?php echo  _base_url() . 
			    			'/library/RulesPlanMappingEventHandlers.php?action=getRulesInAndNotInPlan&plan_id='; ?>' + selected_plan								
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
		            $("body").removeClass("loading");
		     	});

		    if (selected_plan == 'add_new_plan') {
		    	$("#delete_plan").hide();
				$newPlan();
			} 
					    
		} else {
			$("#cdr_hide_show-div").hide();
			$("#delete_plan").hide();
		}		
	}

	$newPlan = function() {
		$('#new_plan_container')
			.append('<label>Plan Name: </label>')
			.append('<input id="new_plan_name" type="text" name="new_plan_name">');
	}
</script>

<div class="cdr-mappings">
	<br/>
	<div><b>View Plan Rules</b></div>
	<br/>
	<div id="cdr_mappings_form-div" class="cdr-form">
		<div class="cdr-plans">
			Plan:
			<select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class">
				<option id="select_plan" value="select_plan">- SELECT PLAN -</option>
				<option id="divider" value="divider" disabled/>
				<option id="add_new_plan" value="add_new_plan">ADD NEW PLAN</option>
			</select>
			<input title="Delete Plan" id="delete_plan" class="delete_button" type="image" style="display: none;"/>
		</div>	
		<div id="new_plan_container"></div>
		<div id="cdr_hide_show-div" style="display: none;">
			<div class="plan-status_div">
				<label class="plan-status-label">Status:</label>
				<button id='cdr-status-activate' disabled>Active</button>
	      		<button id='cdr-status-deactivate'>Deactivate</button>
			</div>
			<br/>
			<div id="cdr_rules" class="cdr-rules-class"></div>   	
	      	
	      	<div id="cdr_buttons_div" class="cdr-buttons-class">
	      		<button id='cdr-button-cancel'>Cancel</button>
	      		<button id='cdr-button-submit'>Submit</button>
	      	</div>
      	</div>
	</div>
</div>

<div class="modal"></div>
