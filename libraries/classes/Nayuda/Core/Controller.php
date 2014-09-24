<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Core;

use Nayuda\DB\Manage;
use Nayuda\Template\Smarty;
use Nayuda\Auth\Login;
use Nayuda\Core;

class Controller extends Core {
	protected $oDb = null; 		// DB object variable
	protected $oDbConn = null; 	// DB member object variable
	protected $oTpl = null; 	// DB template object variable
	protected $oLogin = null;
	protected $mArrParam = null;
	
   	public function __construct($arrParam = null) {
		$this->makeConnection();
		$this->setTemplate($arrParam);
		$this->setAuth();
	}

	protected function makeConnection(){
		// make connection 
		$dsn = GET_CONFIG("database", "dsn");
		$id = GET_CONFIG("database", "id");
		$password = GET_CONFIG("database", "password");

        try{
		    $this->oDb = new Manage($dsn, $id, $password);
		    $this->oDbConn = $this->oDb->getConResource();

        }catch(PDOException $err){
            print $err;
		}
	}

	protected function setTemplate($arrParam){
		// Template Object (loading by singleton)
		$this->oTpl = Smarty::getInstance();
		$this->oTpl->setLayout("blank");

        $this->oTpl->assign("MAIN_DOMAIN", GET_CONFIG("site", "domain"));

        if(SESSION()){
            $this->oTpl->assign("PEOPLE_ID", SESSION("auth_people_id"));
            $this->oTpl->assign("FULL_NAME", SESSION("auth_fullname"));
        }else{
            $this->oTpl->assign("PEOPLE_ID", "");
            $this->oTpl->assign("FULL_NAME", "");
        }

		$this->oTpl->assign("JS", JS_URL);
		$this->oTpl->assign("CSS", CSS_URL);
		$this->oTpl->assign("IMG", IMG_URL);
		$this->oTpl->assign("FILES", FILE_DIR);
		$this->oTpl->assign("FILE_ICON", FILE_ICON_URL);
		
		$this->mArrParam = $arrParam;
		$this->oTpl->assign("CONTROLLER", $arrParam["controller"]);
		$this->oTpl->assign("ACTION", $arrParam["action"]);
		if(array_key_exists("param", $arrParam)){
			$this->oTpl->assign("PARAM", $arrParam["param"]);
		}else{
			$this->oTpl->assign("PARAM", "");
		}
		
		// add the javascript and the style sheet
		$this->oTpl->assign("STYLE", "");
		$this->oTpl->assign("JAVASCRIPT", "");
	}

	protected function setAuth(){
		// login object
		$this->oLogin = new Login("user");
	}
	
	public function __destruct() {
       	// disconnect from the DB
		$this->oDbConn = null;
   	}
}
?>
