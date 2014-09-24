<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\DB;
use PDO;

class Connect extends \Nayuda\Core{
	
   /**
    * variables of databases
    * @var string 
    * @see Nayuda_DB_Connect::DBConnect()
    */
	protected $dsn;

    /**
     * DB Connection object
     * @access protected
     * @var string
     */
	protected $db_conn;

    /**
     * @access public
     * @var string Table Name
     */
	protected $tb_name;

    /**
     * @access public
     * @var string Whole query statement
     */
	protected $query;

	protected $type = array();
	protected $data = array();
	protected $order_by;
	protected $limit;
	protected $arr_field = array();		// table field information
	protected $setPrepare;
	
    /**
     * @access public
     * @var string field statement
     */
	public $field_kind;

    /**
     * @access public
     * @var string value statement
     */
	public $value_list;

    /**
     * @access public
     * @var string Where statement
     */
	public $whereis;  
	
    /**
     * @access protected
     * @var string DB::result()
     */
	protected $input_query_result; 


	/**
     * Connect to a datadase
     *
     * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
     * @param string $dsn The DSN value of database
     * @param string $id The user id value of database
     * @param string $password The password value of database
     * @access public
     * @see Nayuda_DB_Connect::$dsn
     */
	function __construct($dsn, $id, $password){
		$this->dsn  = $dsn;

		try{
		    $this->db_conn = new PDO($dsn, $id, $password);
		
		    // prepare for the syntax
		    $this->setPrepare = false;
		
		}catch(PDOException $err){
			echo $err;
		}
	}

	/**
	 * return the table name which is setted in config
     *
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return object connection object
	 */
	public function getConResource(){
		return $this->db_conn;
	}
	
	/**
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string $dsn DSN name
	 * @return null
	 */
	public function setDSN($dsn){
		$this->dsn = $dsn;

	}
	
	/**
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param null
	 * @return string dsn name
	 */
	public function getDSN(){
		return $this->dsn;

	}

	/**
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string $tbname table name
	 * @return null
	 */
	public function setTbName($tbname){
		$this->type = array();
		$this->data = array();
		//$this->whereis = null;
		$this->tb_name = $tbname;

	}
	
	/**
	 * @access public
     *
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string table name
	 */
	public function getTbName(){
		return $this->tb_name;

	}

	/**
	 * Setup the field and value
     *
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @access public
	 */
	public function addField($field, $value){
		$this->data = array_merge($this->data, array($field=>$value));

		$this->setPrepare = true;
	}
	
	/**
	 * set the field and value
     *
	 * @example type=>true : string  
     * @example type=>true : number
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @access public
	 * @TODO : drop this function
	 */
	public function addFieldAndType($field, $value, $type){
		$this->data = array_merge($this->data, array($field=>$value));

		if($type == true){
			array_push($this->type, "text");
		}else{
			array_push($this->type, "integer");
		}
		$this->setPrepare = true;
	}

	/**
	 * Setup field name 
     *
     * @example "alias"=>"dbfieldname"
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string[] $arrFieldValue Field name
	 */
	public function setField($sFieldValue){
		$this->field_kind = $sFieldValue;
	}

	/**
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
     * @param string[] $arrValue array of target fields
	 */
	public function setFieldAndValue($arrValue){
		$field_kind = "";
		$value_list = "";

		foreach($arrValue as $key=>$val) {
			 $field_kind.= $key.", ";
			 $value_list.= ":".$key.", ";
		}

		$this->field_kind =  substr($field_kind, 0, -2);
		$this->value_list =  substr($value_list, 0, -2);

		$this->setPrepare = true;
	}

	public function setFieldByArray($arrFieldValue){
		$field_kind = "";

		for($i=0;$i<count($arrFieldValue);$i++){
			$field_kind.=$arrFieldValue[$i].",";
		}

		$field_kind = substr($field_kind, 0, -1);

		$this->field_kind = $field_kind;
	}
	
	public function setFieldByValue($value){
		$this->field_kind = $value;
	}
	
	public function setFieldAndValueForWhere($arrValue){
		$field_kind = "";
		$value_list = "";

		foreach($arrValue as $key=>$val) {
			$field_kind.= $key.", ";
			$value_list.= $key." = :".$key." and ";
		}

		$this->field_kind =  substr($field_kind, 0, -2);
		$this->value_list =  substr($value_list, 0, -5);

		$this->setPrepare = true;
	}
	
	public function setFieldAndValueForSet($arrValue){
		$field_kind = "";
		$value_list = "";

		foreach($arrValue as $key=>$val) {
				 $field_kind.= $key.", ";
				 $value_list.= $key." = :".$key.", ";
		}

		$this->field_kind =  substr($field_kind, 0, -2);
		$this->value_list =  substr($value_list, 0, -2);

		$this->setPrepare = true;
	}
	
	public function setType($arrTypeValue){
		$this->type = $arrTypeValue;
	}
	
	/**
	 * Concaterate the column with ','
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string Return the field character
	 */
	public function getField(){
		return $this->field_kind;
	}

	/**
	 * setup the filter string
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string $inputValue where statement
	 * @return string Query string
	 */
	public function setWhere($sValue){
		$this->whereis = $sValue;
	}

	/**
	 * returning the filter string
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string Concaterated string with '`' character
	 */
	public function getWhere(){
		return $this->whereis;
	}
	
	/**
	 * Setup the query string 
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string $value Query statement
	 */
	public function setQuery($value){
		$this->query = $value;
	}

	/**
	 * returning the query string
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string Query statement
	 */
	public function getQuery(){
		return $this->query;
	}
	
	/**
	 * Set the "Order by" 
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param string $value Query statement
	 */
	public function setOrderBy($value){
		$this->order_by= $value;
	}

	/**
	 * Return the "Order by"
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string order by statement
	 */
	public function getOrderBy(){
		return $this->order_by;
	}
	
	
	public function setAddwhereis($value){
		if($this->whereis){
			$this->whereis .= " and ".$value;
		}else{
			$this->whereis = $value;
		}
	}
	
	/* 
	 * Executing query(check the page or list)
	 * @access private
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @param integer $from Start index number
     * @param integer $count Count rows for listing
	 * @return null
	 */
	public function exe_query($from, $count){
		if(!$count || $count == 0){
			$this->executeQuery();
		}else{
			$this->executeLimitQuery($from, $count);
		}
	}
	
	/**
	 * Executing query
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 */
	public function executeQuery(){ 
		try{
			$this->input_query_result = $this->db_conn->exec($this->query);

		}catch(PDOException $err){
			echo $err;
		}
	}

	/**
	 * Return the result of query
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 * @return string the number of the record in query
	*/
	public function getQueryResult(){ 
		return $this->input_query_result;
	}

	/**
	 * Return data of row which returned the result of query
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 */
	public function DBClose(){
		$this->db_conn->disconnect();
	}
	
	/**
	 * Get last id which inputed at last time
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 */
	public function lastInsertID(){
		return $this->db_conn->lastInsertId();
	}
	
	/**
	 * Setup the limit statement
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 */
	public function setLimit($rows, $offset){
		if($rows != null && $rows != 0){
			$rows = ",".$rows;
		}
		$this->limit = $offset.$rows;
	}
	
	/**
	 * Get the limit statement
	 * @access public
	 * @author YoungHoon Hong(Eric.Hong81@nayuda.com)
	 */
	public function getLimit(){
		return $this->limit;
	}
}
?>
