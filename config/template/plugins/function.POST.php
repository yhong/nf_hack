<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.POST.php
 * Type:     function
 * Name:     POST
 * Purpose:  get partial template file 
 * -----------------------------------------------------------------------
 */
function smarty_function_POST($params, &$smarty)
{
    return POST($params["name"]);
}
?> 
