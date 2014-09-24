<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.SERVER.php
 * Type:     function
 * Name:     SERVER
 * Purpose:  get partial template file 
 * -----------------------------------------------------------------------
 */
function smarty_function_SERVER($params, &$smarty)
{
    return SERVER($params["name"]);
}
?> 
