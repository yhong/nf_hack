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
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://framework.nayuda.com for more information.
 *
 * @category   Main
 * @package    Main
 * @copyright  Copyright (c) 2012 Nayuda Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (version_compare(phpversion(), '5.3.0', '<')===true) {
    echo  '<div style="font:12px/1.35em arial, helvetica, sans-serif;">
<div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
<h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">
It looks like you have an invalid PHP version.</h3></div><p>\'Nayuda Framework\' supports PHP 5.3.0 or newer.
<a href="http://framework.nayuda.com" target="">Find out</a> how to install</a>
 \'Nayuda Framework\' using PHP-CGI as a work-around.</p></div>';
    exit;
}

// developer mode
if (isset($_SERVER['NAYUDA_DEVELOPER_MODE'])) {
}

/**
 * Error reporting
 */
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

// setting basic directories
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

define('NAYUDA_ROOT', dirname(__DIR__));

if(!defined("WEB_ROOT")){
    define("WEB_ROOT", __FILE__);
}

// set the home directory for app
if(!defined("APP_ROOT")){
    define("APP_ROOT", NAYUDA_ROOT.DS."apps");
}

// blocking the connect from cli
if (isset($_SERVER['HTTP_HOST']) === TRUE) {
          $host = $_SERVER['HTTP_HOST'];
}else{
        exit;
}

// get uri information
$arrHost = explode(".", $_SERVER["HTTP_HOST"]);
$_GET["sub_domain"] = $arrHost[0];

if($_SERVER["REQUEST_URI"] == '/'){
    $_GET["url"] = "index/index";
}else{  
    $url = substr($_SERVER["REQUEST_URI"], 1);
    $url = explode('?', $url);

    $_GET["url"] = $url[0];
}

// global settings
$globalFile = APP_ROOT.'/global.php';
if (!file_exists($globalFile)) {
    echo $globalFile." was not found";
    exit;
}
require_once $globalFile;

// Loader
new Nayuda\System\Loader(GET("url"));
?>
