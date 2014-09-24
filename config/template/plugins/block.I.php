<?php
/*
 * Smarty plugin
 * ----------------------------------------------------------------------
 * File:     block.I.php
 * Type:     block
 * Name:     I
 * Purpose:  supporting International /config/lang/[lang].xml
 * -----------------------------------------------------------------------
 */
function smarty_block_I($params, $content, &$smarty, &$repeat)
{
    return I($content);
}
?> 
