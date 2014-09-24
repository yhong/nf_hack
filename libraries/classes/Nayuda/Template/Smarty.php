<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Template;

global $paths;
$paths[] = LIB_PATH.DS.EXT_DIR.DS."smarty".DS."sysplugins";
$paths[] = LIB_PATH.DS.EXT_DIR.DS."smarty".DS."plugins";
set_include_path(implode(PS, $paths));

class Smarty extends \Smarty{

	private $mLayoutName = null;
	private $mSubLayoutName = null;
	private $mTplExt = null;
	private $_tpl = null;

	// construction
	public final function __construct(){
        parent::__construct();
		//$this->_tpl = new Smarty();

        $this->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;
        //$this->setCaching(true);
        //$this->cache_lifetime = 3600;

        $main_app_info = GET_APP_INFO(MAIN_APP_NAME);

		$this->setTemplateDir(array(
                TPL_SCRIPT_PATH, 
                $this->getMainAppViewDir().DS.TPL_SCRIPT_DIR
        ));
		$this->setCompileDir(TPL_COMPILE_PATH);
		$this->setCacheDir(TPL_CACHE_PATH);
		$this->setConfigDir(NAYUDA_ROOT.DS.GET_CONFIG("template", "config"));

		$this->setPluginsDir(
            array(
                LIB_PATH.DS.EXT_DIR.DS."smarty".DS."plugins", 
                LIB_PATH.DS.EXT_DIR.DS."smarty".DS."sysplugins", 
                NAYUDA_ROOT.DS.GET_CONFIG("template", "plugin")
            )
        );
		$this->mTplExt = GET_CONFIG("template", "ext"); 
	}
    public static function getInstance($newInstance = null)
    {
        static $instance = null;

        if(isset($newInstance)){
            $instance = $newInstance;
        }

        if ($instance == null){
            $instance = new self();
        }
        return $instance;
    }

    private function getMainAppViewDir(){
        $main_app_info = GET_APP_INFO(MAIN_APP_NAME);
        return APP_ROOT.DS.$main_app_info["location"].DS.MAIN_APP_NAME.DS.VIEW_DIR;
    }

    private function getTplPath($path, $dir, $fileName){
        $sFile = $path.DS.$fileName.'.'.$this->mTplExt;
        if(!is_file($sFile)){
            $sFile = $this->getMainAppViewDir().DS.$dir.DS.$fileName.'.'.$this->mTplExt;
        }
        return $sFile;
    }

	// Set Layout
	public function setLayout($name, $subName = null){
		$this->mSubLayoutName = null;

		if($subName){
            $sSubLayoutFile = $this->getTplPath(TPL_LAYOUT_PATH, TPL_LAYOUT_DIR, $subName);
            $this->mSubLayoutName = "file:".$sSubLayoutFile;
		}

        $sLayoutFile = $this->getTplPath(TPL_LAYOUT_PATH, TPL_LAYOUT_DIR, $name);
		$this->mLayoutName = "file:".$sLayoutFile;
	}
	
    public function blockFetch($fileName){
        $sBlockFile = $this->getTplPath(TPL_BLOCK_PATH, TPL_BLOCK_DIR, $fileName);

        $sCacheId = SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL");
        $sCompileId = SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL");

        return $this->fetch($sBlockFile, $sCacheId, $sCompileId);
    }

    public function errorDisplay($tpl_name){
        $sCacheId = SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL");
        $sCompileId = SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL");

        $sErrorFile = $this->getTplPath(TPL_ERROR_PATH, TPL_ERROR_DIR, $fileName);
		$sContentOutput = $this->fetch("file:".$sErrorFile, $sCacheId, $sCompileId);

        $this->assign("MAIN_CONTENTS", $sContentOutput);
        $this->setDisplay($this->mLayoutName, $sCacheId, $sCompileId);
    }

    public function getTpl($tpl_name){
        $sCacheId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));
        $sCompileId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));

		return $this->fetch($tpl_name.'.'.$this->mTplExt, $sCacheId, $sCompileId);
    }

	// Display for layout
	public function setView($tpl_name){
        $sCacheId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));
        $sCompileId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));

		$tpl = $this->fetch($tpl_name.'.'.$this->mTplExt, $sCacheId, $sCompileId);

		if($this->mSubLayoutName){
			$this->assign("SUB_CONTENTS", $tpl);

			$stpl = $this->fetch($this->mSubLayoutName);
			$this->assign("MAIN_CONTENTS", $sTpl);
		}else{
			$this->assign("MAIN_CONTENTS", $tpl);
		}

		$this->display($this->mLayoutName, $sCacheId, $sCompileId);
	}

	// Display for layout
	public function getView($tpl_name){
        $sCacheId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));
        $sCompileId = md5(SERVER("SERVER_NAME")."_".SERVER("REDIRECT_URL"));

		$tpl = $this->fetch($tpl_name.'.'.$this->mTplExt, $sCacheId, $sCompileId);

		if($this->mSubLayoutName){
			$this->assign("SUB_CONTENTS", $tpl);

			$stpl = $this->fetch($this->mSubLayoutName);
			$this->assign("MAIN_CONTENTS", $sTpl);
		}else{
			$this->assign("MAIN_CONTENTS", $tpl);
		}

		return $this->fetch($this->mLayoutName);
	}
}
?>
