<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Auth;
use Nayuda\Core;
use Nayuda\DB\Manage;

class LoginById extends Core{
	private $member_id;
	private $username;
	private $password;
	private $fullname;
	
	private $ok;
	private $salt;
	private $oDBmg;

	public function __construct($table_name){
		$dsn = GET_CONFIG("database", "dsn");
		$id = GET_CONFIG("database", "id");
		$password = GET_CONFIG("database", "password");
		$this->oDBmg = new Manage($dsn, $id, $password);

		$this->oDBmg->setTbName($table_name);
		$this->salt = GET_CONFIG("session", "salt");
		$this->member_id = "";
		$this->username = "Guest";
		$this->ok = false;
		
		return $this->ok;
	}
	
	public function checkSession(){
		if(SESSION()){
			return $this->check(SESSION('auth_username'), SESSION('auth_password'));
		}else{
			return false;
		}
	}

	public function login($member_id, $user_password){
	
		$check_query = "member_id = '".$member_id."' AND member_password = '".md5($user_password . $this->salt)."'";
		if($this->oDBmg->countConditionRecordRow($check_query) > 0){
			
			$this->fullname		= $this->oDBmg->getFieldValue("name", "member_id = '".$member_id."'");
			$this->member_id 	= $this->oDBmg->getFieldValue("member_id", "member_id = '".$member_id."'");
			$this->member_uid 	= $this->oDBmg->getFieldValue("uid", "member_id = '".$member_id."'");
			$this->username 	= $this->oDBmg->getFieldValue("name", "member_id = '".$member_id."'");
			$this->ok = true;
		
			SESSION('auth_fullname', $this->fullname);
			SESSION('auth_people_id', $this->member_id);
			SESSION('auth_people_uid', $this->member_uid);
			SESSION('auth_password', md5($user_password . $this->salt));
			SESSION('auth_admin_id', "");

			return true;
		}
		return false;
	}		

	public function check($member_id, $user_password){
		if($this->oDBmg->countConditionRecordRow("member_id = '".$member_id."'") == 1)
		{
			$db_password = $this->oDBmg->getFieldValue("member_password", "member_id = '".$member_id."'");

			if($db_password == $user_password)
			{	
				$this->fullname		= $this->oDBmg->getFieldValue("name", "member_id = '".$member_id."'");
				$this->member_id 	= $this->oDBmg->getFieldValue("member_id", "member_id = '".$member_id."'");
				$this->member_uid 	= $this->oDBmg->getFieldValue("uid", "member_id = '".$member_id."'");
				$this->username 	= $this->oDBmg->getFieldValue("name", "member_id = '".$member_id."'");
				$this->ok = true;
				return true;
			}
		}			
		return false;
	}
	
	public function logout(){
		$this->member_id = "";
		$this->username = "Guest";
		$this->ok = false;
		
		// clear session
		session_unset();
		session_destroy();

		if(!SESSION()){
			return true;
		}else{
			return false;
		}
		//$result = $this->checkSession();
	}
}
?>
