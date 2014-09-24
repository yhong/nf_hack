<?hh
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */

/**
 * File search if include file is existing
 *
 * @param string $file file for searching
 * @return bool is there file existing?
 */

function FILE_EXISTS_IN_PATH(string $file) : string {
	$paths = explode(PATH_SEPARATOR, ini_get("include_path"));
	foreach ($paths as $path) {
		$fullPath = $path . DIRECTORY_SEPARATOR . $file;

		if (file_exists($fullPath)) {
			return $fullPath;
		} elseif (file_exists($file)) {
			return $file;
		}
	}
	return "";
}

spl_autoload_register(
    function(string $class) : void {
        $classFile = "";
        //if(strstr($class, "_") != false){
            //$classFile = str_replace("_", DIRECTORY_SEPARATOR, $class);
        //}else{
            $classFile = str_replace("\\", DIRECTORY_SEPARATOR, $class);
        //}
        $classPathInfo = pathinfo($classFile);
        $classPath = $classPathInfo['dirname'].DIRECTORY_SEPARATOR;
        
        $include_file = $classPath.$classPathInfo["filename"].".php";
        if(!($inc_file = FILE_EXISTS_IN_PATH($include_file))){
            $include_file = strtolower($classPath.$classPathInfo["filename"]).".php";
            $inc_file = FILE_EXISTS_IN_PATH($include_file);
        }

        if (isset($_SERVER['NAYUDA_DEVELOPER_MODE'])) {
            $_GLOBALS["TRACKING_FILE"] .= "Loaded File : ".$classPath.$classPathInfo["filename"].".php<br>";
        }
        if(!$inc_file){
            if (isset($_SERVER['NAYUDA_DEVELOPER_MODE'])) {
                echo $_GLOBALS["TRACKING_FILE"]; 
                die($include_file." does not exist");
            }
        }
        include_once($inc_file);
    }
);

function getModel(string $model, ?string $alias=null, ?string $config=null){

	$modelPath = str_replace("_", DS, $model);
	$include_file = MODEL_PATH.DS.$modelPath.".php";

	if(!FILE_EXISTS_IN_PATH($include_file)){
		$include_file = APP_ROOT.DS."base".DS.MAIN_APP_NAME.DS.MODEL_DIR.DS.$modelPath.".php";
	}

	if(FILE_EXISTS_IN_PATH($include_file)){
		require_once($include_file);
		
		$modelName = "Model_".$model;
		$objModel = new $modelName($config);

		if($alias){
			$objModel->setAlias($alias);
		}
		return $objModel;

	}else{
		die($include_file." does not exist!");
	}

	return null;
}


function getHelper(string $helper, ?string $alias=null, ?string $config=null){
	$helperPath = str_replace("_", DS, $helper);
	$include_file = HELPER_PATH.DS.$helperPath.".php";

    if(!FILE_EXISTS_IN_PATH($include_file)){
       $include_file = APP_ROOT.DS."base".DS.MAIN_APP_NAME.DS.HELPER_DIR.DS.$helperPath.".php";
    }

    if(FILE_EXISTS_IN_PATH($include_file)){
		require_once($include_file);
		
		$helperName = "Helper_".$helper;
		$objHelper = new $helperName($config);

		if($alias){
			$objHelper->setAlias($alias);
		}
		return $objHelper;

	}else{
        die($include_file." does not exist!");
    }

	return null;
}

function STYLE_SHEET(string $path) : string {
	return '<link rel="stylesheet" type="text/css" href="'.$path.'.css">'; 
}

function REQUEST(string $index) : mixed {
    if(POST($index)){
        return POST($index);
    }else{
        return GET($index);
    }
}

function GET(?string $index = null, ?string $value = null) : mixed {
	if(is_null($index)){
		return $_GET;
	}else{
		if(array_key_exists($index, $_GET)){
            if(is_null($value)){
                if(is_array($_GET[$index])){
                    return $_GET[$index];
                }else{
                    return trim($_GET[$index]);
                }
                return null;
            }
        }
        $_GET[$index] = trim($value);
        return $value;
	}
}

function POST(?string $index = null, ?string $value = null) : mixed {
	if(is_null($index)){
		return $_POST;
	}else{
		if(array_key_exists($index, $_POST)){
            if(is_null($value)){
                if($_POST[$index] || $_POST[$index] == 0){
                    if(is_array($_POST[$index])){
                        return $_POST[$index];
                    }else{
                        return trim($_POST[$index]);
                    }
                }
                return null;
            }
        }
        $_POST[$index] = trim($value);
        return $value;
	}
}

function SESSION(?string $index = null, ?string $value = null) : mixed {
	if(is_null($index)){
		return $_SESSION;
	}else{
		if(array_key_exists($index, $_SESSION)){
            if(is_null($value)){
                if($_SESSION[$index] || $_SESSION[$index] == 0){
                    return $_SESSION[$index];
                }
                return null;
             }
        }
        $_SESSION[$index] = $value;
        return $value;
	}
}

function COOKIE(?string $index = null, ?string $value = null) : mixed{
	if(is_null($index)){
		return $_COOKIE;
	}else{
		if(array_key_exists($index, $_COOKIE)){
            if(is_null($value)){
                if($_COOKIE[$index] || $_COOKIE[$index] == 0){
                    return $_COOKIE[$index];
                }
                return null;
            }
        }
        $_COOKIE[$index] = $value;
        return $value;
	}
}

function SERVER(?string $index = null) : mixed {
	if(is_null($index)){
		return $_SERVER;
	}
	if(array_key_exists($index, $_SERVER)){
		if($_SERVER[$index] || $_SERVER[$index] == 0){
			return $_SERVER[$index];
		}
	}else{
		return null;
	}
}

function GO(string $page, int $sec=0) : void {
	echo "<meta http-equiv='refresh' content='".$sec."; url=".$page."'>";
	exit;
}

function ALERT(string $msg, bool $back=false, string $code="") : void {
    header("Content-Type: text/html; charset=".GET_CONFIG("site", "charset"));
	echo "<script>";
    echo "alert('".$msg."');";
    if($back){
        echo "history.back(-1);";
    }
    echo $code;
    echo "</script>";
}

/**
 * loading for environment information
 *
 * @param  string $key  key name
 * @return string environment text
 */
function ENV(string $key) : mixed {
    $server = SERVER();
	if ($key == "HTTPS") {
		if (SERVER()) {
			return (SERVER("HTTPS") == "on");
		}
		return (strpos(getenv("SCRIPT_URI"), "https://") === 0);
	}

	if ($key == "SCRIPT_NAME") {
		$cgi_mode = getenv("CGI_MODE");
		$script_url = getenv("SCRIPT_URL");
		if ($cgi_mode && isset($script_url)) {
			$key = "SCRIPT_URL";
		}
	}

	$val = null;
	if (SERVER($key)) {
		$val = SERVER($key);
	} elseif (getenv($key)) {
		$val = getenv($key);
	} elseif (getenv($key) !== false) {
		$val = getenv($key);
	}

	if ($key == "REMOTE_ADDR" && $val == getenv("SERVER_ADDR")) {
		$addr = getenv("HTTP_PC_REMOTE_ADDR");
		if ($addr != null) {
			$val = $addr;
		}
	}

	if ($val !== null) {
		return $val;
	}

	switch ($key) {
		case "SCRIPT_FILENAME":
			if (defined("SERVER_IIS") && SERVER_IIS === true){
				return str_replace("\\\\", "\\", env('PATH_TRANSLATED') );
			}
			break;

		case "DOCUMENT_ROOT":
			$offset = 0;
			if (!strpos(getenv("SCRIPT_NAME"), ".php")) {
				$offset = 4;
			}
			return substr(getenv("SCRIPT_FILENAME"), 0, strlen(getenv("SCRIPT_FILENAME")) - (strlen(getenv("SCRIPT_NAME")) + $offset));
			break;

		case "PHP_SELF":
			return r(getenv("DOCUMENT_ROOT"), '', getenv("SCRIPT_FILENAME"));
			break;

		case "CGI_MODE":
			return (PHP_SAPI == "cgi");
			break;

		case "HTTP_BASE":
			$host = getenv("HTTP_HOST");
			if (substr_count($host, '.') != 1) {
				return preg_replace ("/^([^.])*/i", null, getenv("HTTP_HOST"));
			}
			return '.' . $host;
			break;
	}
	return null;
}


/**
 * get microtime
 *
 * @return float 마이크로 시간
 */
function GET_MICROTIME() : float {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function GET_INSTANCE(string $sPointOfClass, array<string, string> $arrParams) {
	$target_chr = array(".", "-", "@", "#");
	$replace   = "_";
	$classname = str_replace($target_chr, $replace, $sPointOfClass);
	
	return new $classname($arrParams);
}

/**
 * Loading PEAR Libraries
 *
 * using:
 * LOAD_PEAR_CLASS('HTTP','Request');
 *
 * @param Set package and class name 
 */
function LOAD_PEAR_CLASS(string $package) : void {
	require_once($package.'.php');
}

/**
 * execute method which is in other controller
 *
 * using:
 * LOAD_CONTROLLER_MODULE("auth", "mainLoginForm")
 *
 * @param controller, method
 */
function LOAD_CONTROLLER_MODULE(string $controller, string $method, ?string $param = null) : Controller{
	require_once(CONTROLLER_PATH.DS.$controller."_controller.php");
	$class_name = $controller."_controller";
	
    $arrParam = array();
	$arrParam["controller"] = $controller;
	$arrParam["action"] = $method;
	$arrParam["param"] = $param;
	
	$oInstance = new $class_name($arrParam);
	return $oInstance->$method($param);
}

function GET_APP_INFO(string $appName) : array<string, string> {
    if($appName == "www"){
        $appName = MAIN_APP_NAME;
    }
	$xml = simplexml_load_file(APP_ROOT.DS."apps.xml");
	$node = $xml->$appName;

	if($node && $node->enable == "true"){
		foreach($node->depends as $element){
			foreach(array_keys(get_object_vars($element)) as $dependsApp){
				if($xml->$dependsApp->enable == "false" || !$xml->$dependsApp){
					die("app has dependency issue (".$dependsApp." of ".$appName.")");
				}
			}
		}
	}else{
		die($appName." does not be enabled!");
	}

    return array("enable"=>$node->enable, "location"=>$node->location);
}

/**
 * read environment value by parameter
 *
 * using:
 * GET_CONFIG("session", "name")
 *
 * @param Key,field
 */
function GET_CONFIG(string $sCategory, string $sAttributeName) : mixed {
	$config_file = CONFIG_PATH.DS."config.xml";
	if(is_file(APP_CONFIG_PATH.DS."config.xml")){
		$config_file = APP_CONFIG_PATH.DS."config.xml";
	}
	$xml = simplexml_load_file($config_file);

	$node = $xml->$sCategory;

    if($node){
        foreach($node->element as $element){
            if($element["name"] == $sAttributeName){
                    return $element["value"];
            }
        }
    }

	$config_file = CONFIG_PATH.DS."config.xml";
    if(!is_file($config_file)){
        die("config file(config.xml) does not exist!");
    }

	$xml = simplexml_load_file($config_file);
	$node = $xml->$sCategory;
    if($node){
        foreach($node->element as $element){
            if($element["name"] == $sAttributeName){
                    return $element["value"];
            }
        }
    }else{
        die($sCategory.".".$sAttributeName." does not exist in config.xml");
    }

    return "";
}

/**
 * Support multylanguage(LANG_PATH)
 */
function I(string $sId) : string {
	// [HTTP_ACCEPT_LANGUAGE] => ko-kr,ko;q=0.7,en-us;q=0.3
	$lang_list = explode(",", SERVER('HTTP_ACCEPT_LANGUAGE'));

    // local
	$selected_lang = "";
	foreach($lang_list as $value){
		$lang = explode(";", $value);
		$sub_lang = explode("-", $lang[0]);
		$browser_lang = $sub_lang[0];
		if(file_exists(CONFIG_LANG_PATH.DS.$browser_lang.".xml")){
			$selected_lang = $browser_lang;
			break;
		}
	}

	// checking the exist of language directory
	if($selected_lang == ""){
		// load default language
		$selected_lang =  GET_CONFIG("language", "type");
	}

    // check local language file
    $lang_file = CONFIG_LANG_PATH.DS.$selected_lang.".xml";
    if(is_file($lang_file)){
        $xml = simplexml_load_file($lang_file);
        foreach($xml->word as $element){
            if($element["id"] == $sId){
                    return (string)$element["value"];
            }
        }
    }

    // glabal
    $appInfo = GET_APP_INFO("_main");
	$selected_lang = "";
	foreach($lang_list as $value){
		$lang = explode(";", $value);
		$sub_lang = explode("-", $lang[0]);
		$browser_lang = $sub_lang[0];
		if(file_exists(APP_ROOT.DS.$appInfo["location"].DS."_main".DS.CONFIG_DIR.DS.LANG_DIR.DS.$browser_lang.".xml")){
			$selected_lang = $browser_lang;
			break;
		}
	}

	if($selected_lang == ""){
		// load default language
		$selected_lang =  GET_CONFIG("language", "type");
	}

    // check global language file
    $lang_file = APP_ROOT.DS.$appInfo["location"].DS."_main".DS.CONFIG_DIR.DS.LANG_DIR.DS.$selected_lang.".xml";
    if(is_file($lang_file)){
        $xml = simplexml_load_file($lang_file);
        foreach($xml->word as $element){
            if($element["id"] == $sId){
                    return (string)$element["value"];
            }
        }
    }
    return $sId;
}

function SET_PAGE(int $page, Model $model) : string{
	$nTotalCnt = $model->getCount();
	$pagePerRow = 10;
	$totalPage = ceil($nTotalCnt / $pagePerRow);
	$from = ($page - 1) * $pagePerRow;
	$count = $pagePerRow;

	$model->setLimit($from, $count);

	$next_page = strval(($page) + 1);
	if($next_page >= $totalPage){
		$next_page = "";
	}
	
	return $next_page;
}   	

function IS_DEVELOPER_MODE() : bool{
    if (isset($_SERVER["NAYUDA_DEVELOPER_MODE"])) {
        if($_SERVER["NAYUDA_DEVELOPER_MODE"] == "true"){
            return true;
        }
    }
    return false;
}
