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

class File extends Session {

	private $_sessName;
	private $_sessPath;
	private $_sessFp;
	
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

		$this->_sessName = GET_CONFIG("session", "name");
		$this->_sessPath = TMP_SESSION_PATH;
	}

	/**
	 * Open the session
	 * @return bool
	 */
	public function open($save_path = null, $session_name = null) {
		if(!$this->_sessName){
			$this->_sessName = $save_path; 
		}

		if(!$this->_sessPath){
			$this->_sessPath = $session_name;
		}

		return true;
	}

	/**
	 * Close the session
	 * @return bool
	 */
	public function close() {
		flock($this->_sessFp, LOCK_UN);
		fclose($this->_sessFp);
		$fp=null;

		return(true); 
	}

	/**
	 * Read the session
	 * @param int session id
	 * @return string string of the sessoin
	 */
	public function read($key) {
		$sess_file = $this->_sessPath."/sess_".$key;

		if ($this->_sessFp = @fopen($sess_file, "r+")) {
			flock($this->_sessFp, LOCK_EX);
	
			$size = 0;
			if(file_exists($sess_file)){
				$size = filesize($sess_file);
				if($size > 0){
					$sess_data = fread($this->_sessFp, $size);
					return($sess_data);
				}else{
					return(""); 
				}
			}else{
				return(""); 
			}

		} else {
			return(""); // Must return "" here.
		} 
	}

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public function write($key, $data) {
		$sess_file = $this->_sessPath."/sess_".$key;

		if (!empty($this->_sessFp)) {
			fseek($this->_sessFp,0);
			return(fwrite($this->_sessFp, $data));

		} elseif ($this->_sessFp = @fopen($sess_file, "w")) {
			flock($this->_sessFp, LOCK_EX);
			return(fwrite($this->_sessFp, $data));

		} else {
			return(false);
		} 
	}

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function destroy($key) {
		$sess_file = $this->_sessPath."/sess_".$key;
		return(@unlink($sess_file)); 
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
		foreach (glob($this->_sessPath."/sess_*") as $filename) {
			if (filemtime($filename) + $maxlifetime < time()) {
				@unlink($filename);
			}
		}
		return true;
	}

    public function __destruct(){
        parent::__destruct();
    }
}
?>
