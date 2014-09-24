<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.COOKIE.php
 * Type:     function
 * Name:     COOKIE
 * Purpose:  get partial template file 
 * -----------------------------------------------------------------------
 */
function smarty_function_COOKIE($params, &$smarty)
{
    return COOKIE($params["name"]);
}
?> 
