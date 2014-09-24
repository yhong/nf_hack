<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     function.GET_BLOCK.php
 * Type:     function
 * Name:     GET_BLOCK
 * Purpose:  get partial template file 
 * -----------------------------------------------------------------------
 */
function smarty_function_GET_BLOCK($params, &$smarty)
{
    $objTpl = Nayuda\Template\Smarty::getInstance();
    return $objTpl->blockFetch($params["name"]);
}
?> 
