<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.GET.php
 * Type:     function
 * Name:     GET
 * Purpose:  get partial template file 
 * -----------------------------------------------------------------------
 */
function smarty_function_GET($params, &$smarty)
{
    return GET($params["name"]);
}
?> 
