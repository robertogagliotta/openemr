<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<script language="javascript" src="<?php js_src('list.js') ?>"></script>
<script language="javascript" src="<?php js_src('jQuery.fn.sortElements.js') ?>"></script>

<script type="text/javascript">
    var list = new list_rules();
    list.init();
</script>
<style type="text/css">
    .Table
    {
        display: table;
    }
    .Title
    {
        display: table-caption;
        text-align: center;
        font-weight: bold;
        font-size: larger;
    }
    .Heading
    {
        display: table-row;
        font-weight: bold;
        text-align: center;
    }
    .Row
    {
        display: table-row;
    }
    .Cell
    {
        display: table-cell;
        border: solid;
        border-width: thin;
        padding-left: 5px;
        padding-right: 5px;
    }
</style>

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

<div>
	<form>
		<div class="Table">
		    <div class="Heading">
		        <div class="Cell">
		            <p>Plans</p>
		        </div>
		        <div class="Cell">
		            <p></p>
		        </div>
		    </div>
		    <div class="Row">
		        <div class="Cell">
		            <p>Preventative Care</p>
		        </div>
		        <div class="Cell">
		            <p><button type="button">Edit</button></p>
		        </div>
		    </div>
		    <div class="Row">
		        <div class="Cell">
		            <p>Diabetes Mellitus</p>
		        </div>
		        <div class="Cell">
		            <p><button type="button">Edit</button></p>
		        </div>
		    </div>
		    <div class="Row">
		        <div class="Cell">
		            <p><button type="button">Add New Plan</button></p>
		        </div>
		        <div class="Cell">
		            <p></p>
		        </div>
		    </div>		    
		</div>	
	</form>
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

