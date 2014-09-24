<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.GET_CONFIG.php
 * Type:     function
 * Name:     GET_CONFIG
 * Purpose:   refrence the code in the file of %app_dir%/config/config.xml
 * -----------------------------------------------------------------------
 */
function smarty_function_category_relation($params, &$smarty)
{
	$flag = false;
	foreach ($params["arr_data"] as $row){
		if($params["examkind"] == $row["examkind"]){
			if($params["examkind2"] == $row["examkind2"]){
				$flag = true;
				break;
			}
		}
	}
	$y_value = "";
	if($flag == true){
		$y_value = "selected";
	}
	$html = "<select onChange='".$params["js_func"]."(".$params["examkind"].",".$params["examkind2"].", this.value)'>";
	$html .= "<option value=''></option>";
	$html .= "<option value='Y' ".$y_value.">설정</option>";
	$html .= "</select>";			
    return $html;
}
?> 
