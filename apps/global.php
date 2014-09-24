<?php
/**
 * Nayuda
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@nayuda.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Globalnto to newer
 * versions in the future. If you wish to customize Globalnto for your
 * needs please refer to http://framework.nayuda.com for more information.
 *
 * @category   Main
 * @package    Main
 * @copyright  Copyright (c) 2012 Nayuda Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if ( !defined('APP_ROOT') || !defined('WEB_ROOT')){ 
	exit('You access the unauthorized page'); 
}

define("MAIN_DOMAIN", "http://www.nayuda.com");

/**
 * web resources
 */
define("JS_URL", "http://js.nayuda.com");
define("CSS_URL", "http://css.nayuda.com");
define("IMG_URL", "http://img.nayuda.com");

/**
 * var path & creation
 */
define("VAR_ROOT", NAYUDA_ROOT.DS."var");
if(!is_dir(VAR_ROOT)){
    die("Please make the directory, '".VAR_ROOT."'");
}

/**
 * library path 
 */
define("LIBRARIE_DIR", "libraries");
define("CLASS_DIR", "classes");
define("MODULE_DIR", "modules");
define("EXT_DIR", "exts");

define("LIB_PATH", NAYUDA_ROOT.DS.LIBRARIE_DIR);

/**
* Set include path
*/
$paths[] = LIB_PATH;
$paths[] = LIB_PATH.DS.CLASS_DIR;
$paths[] = LIB_PATH.DS.MODULE_DIR;
$paths[] = LIB_PATH.DS.EXT_DIR;
$paths[] = LIB_PATH.DS.EXT_DIR.DS."pear";
set_include_path(implode(PS, $paths));

include_once "functions.php";

/**
 * define for timing function
 */
define("SECOND", 1);
define("MINUTE", 60 * SECOND);
define("HOUR", 60 * MINUTE);
define("DAY", 24 * HOUR);
define("WEEK", 7 * DAY);
define("MONTH", 30 * DAY);
define("YEAR", 365 * DAY);


/**
 * fetch app with apps/apps.xml
 */
define("MAIN_APP_NAME", "_main");

if(count(GET()) > 0){
    if(GET("sub_domain") == "www"){
        GET("sub_domain", MAIN_APP_NAME);
    }
	$appInfo = GET_APP_INFO(GET("sub_domain"));
	define("APP_NAME", $appInfo["location"].DS.GET("sub_domain"));
}else{
	if(!defined("APP_NAME")){ 
	    define("APP_NAME", "base".DS.MAIN_APP_NAME);
	}
}
define("APP_PATH", APP_ROOT.DS.APP_NAME);

/**
 * controller path
 */
define("CONTROLLER_DIR", "controllers");
define("CONTROLLER_PATH", APP_PATH.DS.CONTROLLER_DIR);

/**
 * model path
 */
define("MODEL_DIR", "models");
define("MODEL_PATH", APP_PATH.DS.MODEL_DIR);


/**
 * helper path
 */
define("HELPER_DIR", "helpers");
define("HELPER_PATH", APP_PATH.DS.HELPER_DIR);

/**
 * view path
 */
define("VIEW_DIR", "views");
define("VIEW_PATH", APP_PATH.DS.VIEW_DIR);

/**
 * configure path
 */
define("CONFIG_DIR", "config");
define("APP_CONFIG_PATH", APP_PATH.DS.CONFIG_DIR);
define("CONFIG_PATH", NAYUDA_ROOT.DS.CONFIG_DIR);

/**
 * language description path
 */
define("LANG_DIR", "lang");
define("CONFIG_LANG_PATH", APP_CONFIG_PATH.DS.LANG_DIR);

/**
 * template path
 */
define("TPL_BLOCK_DIR", "blocks");
define("TPL_BLOCK_PATH", VIEW_PATH.DS.TPL_BLOCK_DIR);
define("TPL_ERROR_DIR", "errors");
define("TPL_ERROR_PATH", VIEW_PATH.DS.TPL_ERROR_DIR);
define("TPL_LAYOUT_DIR", "layouts");
define("TPL_LAYOUT_PATH", VIEW_PATH.DS.TPL_LAYOUT_DIR);
define("TPL_SCRIPT_DIR", "scripts");
define("TPL_SCRIPT_PATH", VIEW_PATH.DS.TPL_SCRIPT_DIR);
define("TPL_COMPILE_PATH", VAR_ROOT.DS."compile");
define("TPL_CACHE_PATH", VAR_ROOT.DS."cache");

if(!is_dir(TPL_COMPILE_PATH)){
    mkdir(TPL_COMPILE_PATH);
    /*
}else{
    $dir_stat = substr(sprintf("%o", fileperms(TPL_COMPILE_PATH)), -4);
    if(substr($dir_stat, 1, 3) !="777"){
        die("Please, check the permission(777) of '".TPL_COMPILE_PATH."'");
    }
    */
}
if(!is_dir(TPL_CACHE_PATH)){
    mkdir(TPL_CACHE_PATH);
}
define("FILE_DIR", "files");
define("TMP_FILE_DIR", "uploads");

/**
 * file icon path
 */
define("FILE_ICON_URL", IMG_URL.DS."file_icon");

//define("TPL_DIR", "template");
//define("TPL_CONFIG_PATH", NAYUDA_ROOT.DS.CONFIG_DIR.DS.TPL_DIR);
/*
// Temporary directory
define("TMP_DIR", "tmp");
define("TMP_PATH", NAYUDA_ROOT.DS."var".DS.TMP_DIR);

// Upload path
define("UPLOAD_DIR", "uploads");
define("TMP_UPLOADS_PATH", TMP_ROOT.DS.TMP_FILE_DIR);
define("UPLOADS_PATH", WEB_ROOT.DS.FILE_DIR);
*/

/**
 * Session
 */  
define("SESSION_DIR", "sessions");
define("SESSION_PATH", VAR_ROOT.DS.SESSION_DIR);
if(!is_dir(SESSION_PATH)){
    mkdir(SESSION_PATH);
}

$objSession = null;
if(GET_CONFIG("session", "save_type") == "db"){
	$objSession = new Nayuda\Session\Db();
}else{
	$objSession = new Nayuda\Session\File();
}
// session start

session_set_cookie_params(0, "/");
ini_set("session.save_path", SESSION_PATH);
ini_set("session.cookie_domain", ".nayuda.com");
ini_set("session.save_handler", "user");
ini_set("session.gc_probability", 0); 

$objSession->start("SESSION_NAYUDA");

/**
 * loading template library
 */
require('smarty'.DS.'Smarty.class.php');
?>
