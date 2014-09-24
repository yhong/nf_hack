<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */

namespace Nayuda;

abstract class Session extends Core {

	protected $host;
	protected $cookieTime;
	protected $security;

	/**
	 * contruct method
	 *
	 * @param string $base The base path for the Session
	 * @param boolean $start Should session be started right now
	 * @access public
	 */
	public function __construct() {
		$this->host = ENV('HTTP_HOST');
		$this->cookieTime = time();
		$this->security = GET_CONFIG("session", "security");
	}

	/**
	 * Session start
	 *
	 * @param string $name Variable name to check for
	 * @return boolean True if variable is there
	 * @access public
	 */
	public function start($session_name = null) {
		if (function_exists('session_write_close')) {
			session_write_close();
		}
/*
		ini_set('session.cookie_secure', 1);

		switch ($this->security) {
			case 'high':
				$this->cookieTime = 0;
				ini_set('session.referer_check', $this->host);

			break;
			case 'medium':
				$this->cookieTime = 7 * 86400;
				ini_set('session.referer_check', $this->host);

			break;
			case 'low':
			default:
				$this->cookieTime = 788940000;
			break;
		}
*/
		//if (empty($_SESSION)) {
        ini_set('session.use_trans_sid', 0);
        ini_set('session.cookie_lifetime', $this->cookieTime);
	//	}

		/*
		if (headers_sent()) {
			if (empty($_SESSION)) {
				//$_SESSION = array();
			}
			return false;
		} elseif (!isset($_SESSION)) {
			session_cache_limiter ("must-revalidate");
			$result = session_start();
			header ('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
			return $result;
		} else {
			$result = session_start();
			return $result;
		}
		*/
		session_name($session_name);
		session_start();

        // for multi-domain
        if(!$_SESSION){
             if(COOKIE('sess_key')){
                 session_id(COOKIE('sess_key'));
                 session_start($session_name);
             }else{
                 return null;
             }
        }else{
             COOKIE('sess_key', session_id());
        }
	}

	/**
	* Open the session
	* @return bool
	*/
	abstract public function open();

	/**
	* Close the session
	* @return bool
	*/
	abstract public function close();

	/**
	* Read the session
	* @param int session id
	* @return string string of the sessoin
	*/
	abstract public function read($key);

	/**
	* Write the session
	* @param int session id
	* @param string data of the session
	*/
	abstract public function write($key, $data);

	/**
	* Destoroy the session
	* @param int session id
	* @return bool
	*/
	abstract public function destroy($key);

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
	abstract public function gc($max);

    public function __destruct(){
            session_write_close();
    }
}
?>
