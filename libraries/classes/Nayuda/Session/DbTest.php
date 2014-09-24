<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Session;
use Nayuda\Session;

class DbTest extends Session {

	/**
	* a database connection resource
	* @var resource
	*/
	private $_sessDb;
	private $_sessDbConn;
	
	/**
	 * contruct method
	 *
	 * @param string $base The base path for the Session
	 * @param boolean $start Should session be started right now
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
        session_set_save_handler(array($this, "open"),
                                 array($this, "close"),
                                 array($this, "read"),
                                 array($this, "write"),
                                 array($this, "destroy"),
                                 array($this, "gc")
                               );
	}

	/**
	* Open the session
	* @return bool
	*/
	public function open() {
		$dsn = GET_CONFIG("database", "dsn");
		$id = GET_CONFIG("database", "id");
		$password = GET_CONFIG("database", "password");

        try{
		    $this->_sessDb = new \Nayuda\DB\Manage($dsn, $id, $password);

        }catch(PDOException $err){
            print $err;
		}
	}

	/**
	* Close the session
	* @return bool
	*/
	public function close() {
		return $this->_sessDb = null;
	}

	/**
	* Read the session
	* @param int session id
	* @return string string of the sessoin
	*/
	public function read($key) {
		$record = $this->_sessDb->selectOne("session", "value", array("sesskey"=>$key));
		return $record["value"];
	}

	/**
	* Write the session
	* @param int session id
	* @param string data of the session
	*/
	public function write($key, $sessData) {
		$data = array("sesskey"=>$key, 
                      "expire"=>$this->cookieTime, 
                      "value"=>$sessData, 
                      "user_id"=>SESSION('auth_people_id'),
                      "admin_id"=>SESSION('auth_admin_id')
                     );

		$record = $this->_sessDb->selectOne("session", "value", array("sesskey"=>$key));

        if($record){
            return $this->_sessDb->update("session", $data, "sesskey");
        }else{
            return $this->_sessDb->insert("session", $data);
        }
	}

	/**
	* Destoroy the session
	* @param int session id
	* @return bool
	*/
	public function destroy($key) {
		return $this->_sessDb->delete("session", array("sesskey"=>$key));
	}

	/**
	* Garbage Collector
	* @param int life time (sec.)
	* @return bool
	* @see session.gc_divisor      100
	* @see session.gc_maxlifetime 1440
	* @see session.gc_probability    1
	* @usage execution rate 1/100
	*        (session.gc_probability/session.gc_divisor)
	*/
	public function gc($max) {
		return $this->_sessDb->delete("session", 
			array("expire" => array("<", ($this->cookieTime - $max)))
		);
	}

    public function __destruct(){
        parent::__destruct();
    }
}
?>
