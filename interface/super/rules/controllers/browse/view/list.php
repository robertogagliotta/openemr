<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>

<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/themes/ui-lightness/jquery-ui.css" />
<link type="text/css" rel="stylesheet" href="<?php css_src('/cdr-multiselect/common.css') ?>" />
<link type="text/css" rel="stylesheet" href="<?php css_src('/cdr-multiselect/ui.multiselect.css') ?>" />

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
    var list = new list_rules();
    list.init();
</script>


<script type="text/javascript">
	$(document).ready(function() {
		$("#cdr-plans").load('/interface/super/rules/library/RulesPlanMappingEventHandlers.php');
		
	    $.post(
	   		'/interface/super/rules/library/RulesPlanMappingEventHandlers.php?action=getNonCQMPlans'
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
				$("#cdr-button").show();

			    $.post
			    	('/interface/super/rules/library/RulesPlanMappingEventHandlers.php?action=getRulesInAndNotInPlan&plan_id=' + selected_plan)
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

				        $("#cdr_rules_select").multiselect({sortable: false});
			     	});	
		     				    
			} else {
				$("#cdr-button").hide();
			}		
		});

		$("#cdr-button").click(function() {
			
		});
    });
    
</script>

<table class="header">
  <tr>
        <td class="title"><?php echo out( xl( 'Rules Configuration' ) ); ?></td>
        <td>
            <a href="index.php?action=edit!summary" class="iframe_medium css_button" onclick="top.restoreSession()">
                <span><?php echo out( xl( 'Add new' ) ); ?></span>
            </a>
        </td>
  </tr>
</table>

<div class="cdr-mappings">
	<br/>
	<div><b>View Rules Plan Mappings</b></div>
	
	<div class="cdr-form">
		<!--<form action="library/RulesPlanMappingEventHandlers.php?action=commitChanges" method="post">-->
			<div class="cdr-plans">
				Plan:
				<select id="cdr-plans-select" name="cdr-plans-select" class="cdr-plans-select-class">
					<option id="select_plan" value="select_plan">- SELECT PLAN -</option>
					<option id="divider" value="divider" disabled/>
					<option value="add_new_plan">ADD NEW PLAN</option>
				</select>
			</div>	
	
			<div id="cdr_rules" class="cdr-rules-class"></div>   	
	      	
	      	<div id="cdr_buttons_div" class="cdr-buttons-class">
	      		<button id='cdr-button' style="display: none;">Commit</button>
	      	</div>

	<!--</form>-->
	</div>
</div>

<div class="rule_container text">
    <div class="rule_row header">
        <span class="rule_title header_title"><?php echo out( xl( 'Name' ) ); ?></span>
        <span class="rule_type header_type"><?php echo out( xl( 'Type' ) ); ?></span>
    </div>
</div>

<!-- template -->
<div class="rule_row data template">
    <span class="rule_title"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></span>
    <span class="rule_type"><a href="index.php?action=detail!view" onclick="top.restoreSession()"></a></span>
</div>

