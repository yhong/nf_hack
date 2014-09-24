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
function smarty_function_GET_CONFIG($params, &$smarty)
{
        return GET_CONFIG($params["name"], $params["id"]);
}
?>

