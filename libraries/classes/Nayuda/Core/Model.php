<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Core;
use Nayuda\Core;
use PDO;

class Model extends Core{
	protected $_oDb     = null; // DB object variable
	protected $_dbConn  = null; // DB member object variable
	protected $_name    = null;	// table name
	protected $_pk      = null;	// primary key
	protected $_isJoin  = false;// join
	protected $_order   = null;	// order
	protected $_group   = null;	// group
	protected $_field   = "*";	// field
	protected $_where   = null;	// where
	protected $_query   = null;	// query
	protected $_limit   = null;	// limit
	protected $_join    = null;	// join
	protected $_alias   = null;	// join
	
   	public function __construct($config = null) {
		$this->makeConnection($config);
	}

	protected function makeConnection($config){
		// make connection 
		if($config){
			$dsn = $config['dsn'];
			$id = $config['id'];
			$password = $config['password'];
		}else{
			$dsn = GET_CONFIG("database", "dsn");
			$id = GET_CONFIG("database", "id");
			$password = GET_CONFIG("database", "password");
		}

        try{
		    $this->_dbConn = new PDO($dsn, $id, $password);
        }catch(PDOException $err){
            print $err;
		}
	}

	/**
	 * getConResource()
	 * return the table name which is setted in config
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param void
	 *
	 * @return $db_conn : connection object
	*/
	public function getConResource(){
		return $this->_dbConn;
	}

	/**
	 * setTableName()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : table name
	 *
	 * @return object
	*/
	public function setTableName($value){
		$this->_name = $value;

		return $this;
	}

	/**
	 * getTableName()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
     *
	 * @return $value : table name
	*/
	public function getTableName(){
		return $this->_name;
	}

	/**
	 * setAlias()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : table alias name
	 *
	 * @return void
	*/
	public function setAlias($value){
		$this->_alias = $value;

		return $this;
	}

	/**
	 * setOrder()
	 * set the "Order by" 
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : query string
	 *
	 * @return object
	*/
	public function setOrder($value){
        if($value == ""){
            $this->_order = "";
        }else if($value == null){
            $this->_order = $this->_pk." asc";
        }else{
            if($this->_order){
                $this->_order .= ",".$value;
            }else{
                $this->_order = $value;
            }
        }

		return $this;
	}

	/**
	 * setGroup()
	 * set the "Group by" 
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : query string
	 *
	 * @return object
	*/
	public function setGroup($value){
		$this->_group= $value;

		return $this;
	}

	/**
	 *  
	 * setWhere()
	 * set the "where" 
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : string
	 *
	 * @return object
	*/
	public function setWhere($value = null){
        if($value == null){
            $this->_where = "";          
        }else{
            if($this->_where){
                $this->_where .= " and ".$value;
            }else{
                $this->_where = $value;
            }
        }

		return $this;
	}

	/**
	 * setField()
	 * field name ("alias"=>"dbfieldname")
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $arrFieldValue : field name(array)
	 *
	 * @return object
	*/
	public function setField($value){
		if(is_array($value)){
			if($this->_field == '*'){
				$this->_field = '';
			}

			foreach($value as $item){
				$this->_field .= $item.',';
			}	
			$this->_field = substr($this->_field, 0, -1);
		}else{	
			$this->_field = $value;
		}
		return $this;
	}

	/**
	 * setLimit()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $offset : start offset
	 * @param $rows : list count
	 *
	 * @return object
	*/
	public function setLimit($offset, $rows = null){
		$dsn = GET_CONFIG("database", "dsn");

        if(substr($dsn, 0, 5) == "mysql"){ //mysql
            if($rows != null && $rows != 0){
                $rows = ",".$rows;
            }
            $this->_limit = $offset.$rows;
        }else{ // mssql
            $this->_field = "top ".$rows." ". $this->_field;
            $where = "";

            if($this->_where){
                $where = " where ".$this->_where;
            }

            if($this->_where){
                $this->_where .= " and ";
            }
            $group = "";
            if($this->_group){
                $group = " group by ".$this->_group;
            }

            $order = "";
            if($this->_order){
                $order = " order by ".$this->_order;
            }

            $this->_where .= "(".$this->_pk." not in (select top ".$offset." ".$this->_pk." from ".$this->_name." ".$where." ".$group." ".$order."))";
        }

		return $this;
	}

	/**
	 * getQuery()
	 * return the query string
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param void
	 *
	 * @return $query : query string
	*/
	public function getQuery(){
		return $this->_query;
	}

	/**
	 * select()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return row
	*/
	public function select($theLimit=null){
		$group = "";
		if($this->_group){
			$group = "group by ".$this->_group;
		}

		$order = "";
		if($this->_order){
			$order = "order by ".$this->_order;
		}

		$where = "";
		if($this->_where){
			$where = " where ".$this->_where;
		}

        $field = $this->_name.".*";
        if($this->_field){
            $field = $this->_field;
        }

		$limit = "";

		$dsn = GET_CONFIG("database", "dsn");
        if(substr($dsn, 0, 5) == "mysql"){ //mysql
            if($theLimit == null){
                if($this->_limit){
                    $limit = "limit ".$this->_limit;
                }
            }else if($theLimit == 1){
                    // fetch()
            }else{
                    $limit = "limit ".$theLimit;
            }
        }else{
            if($theLimit == null){
                if($this->_limit){
                    $field = "TOP ".$theLimit." ".$field;
                }
            }else if($theLimit == 1){
                    // fetch()
            }else{
                    $field = "TOP ".$theLimit." ".$field;
            }
        }
		$this->_query = "select ".$field." from ".$this->_name.' '.$this->_alias.' '.$this->_join." ".$where.' '.$group.' '.$order.' '.$limit;

		try{
			$pResult = $this->_dbConn->prepare($this->_query);
			if(!$pResult->execute()){
				return false;
			}

            if($theLimit == 1){
                return $pResult->fetch();
            }else{
                return $pResult->fetchAll();
            }

		}catch(PDOException $err){
			print $err;
		}
	}


	/**
	 * insert()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return row
	*/
	public function insert($data){
		$arrValues = array();

        for($i=0; $i<count($data); $i++){
			$arrValues[] .= "?";
		}

		$sValues = implode(",", $arrValues);
        $sField = implode(",", array_keys($data));

		$this->_query = "insert into ".$this->_name."(".$sField.") values(".$sValues.")";

		try{
			$pResult = $this->_dbConn->prepare($this->_query);

            $arrValue = array_values($data);
			if(!$pResult->execute($arrValue)){
                
                if(IS_DEVELOPER_MODE() == true){
                    $sFieldForDebug = implode(",", array_keys($data));
                    $sValueForDebug = implode(",", array_values($data));

                    die("[INSERT ERROR] insert into ".$this->_name."(".$sFieldForDebug.") values(".$sValueForDebug.")<br/>\n");
                }else{
                    die("Insert Error!");
                }
				return false;

			}else{
                // return the inserted id
				return $this->_dbConn->lastInsertId();
			}
		}catch(PDOException $err){
			print $err;
		}
	}


	/**
	 * update()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return row
	*/
	public function update($data, $key=null){
		$sField = "";
		$arrValue = array();
		$where = "";

		if($this->_where){
			$where = " where ".$this->_where;
		}
 
        if($key){
			$where = " where ".$this->_pk."=".$key;
        }

		foreach($data as $key=>$value){
            if($value[0] == '&'){
                $sField .= $key."=".substr($value, 1).",";
            }else{
                $sField .= $key."=?,";
                $arrValue[] = $value;
            }
		}

		$sField = substr($sField, 0, -1);
		$this->_query = "update ".$this->_name." set ".$sField." ".$where;

		try{
			$pResult = $this->_dbConn->prepare($this->_query);
			if(!$pResult->execute($arrValue)){
                if(IS_DEVELOPER_MODE() == true){

                    reset($data);
                    $sField = "";
                    foreach($data as $key=>$value){
                        if($value[0] == '&'){
                            $sField .= $key."=".substr($value, 1).",";
                        }else{
                            $sField .= $key."=".$value.",";
                        }
                    }
                    $sFieldForDebug = substr($sField, 0, -1);
                    die("[UPDATE ERROR] update ".$this->_name." set ".$sFieldForDebug." ".$where);
                }else{
                    die("Update Error!");
                }
				return false;
			}
			return $pResult;

		}catch(PDOException $err){
			print $err;
		}
	}

	/**
	 * delete()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return row
	*/
	public function delete($key=null){
		$where = "";
		if($this->_where){
			$where = " where ".$this->_where;
		}

        if($key){
			$where = " where ".$this->_pk."=".$key;
        }

		$this->_query = "delete from ".$this->_name." ".$where;
		try{
			$pResult = $this->_dbConn->prepare($this->_query);
			return $pResult->execute();

		}catch(PDOException $err){
			print $err;
		}
	}

	/**
	 * getCount()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return count
	*/
	public function getCount(){
		$where = "";
		if($this->_where){
			$where = " where ".$this->_where;
		}

		$this->_query = "select count(*) from ".$this->_name.' '.$this->_alias.' '.$this->_join." ".$where;

		try{
			$pResult = $this->_dbConn->prepare($this->_query);
			if(!$pResult->execute()){
				return false;
			}
			return $pResult->fetchColumn();

		}catch(PDOException $err){
			print $err;
		}
	}

	/**
	 * getTotalCount()
	 * @access public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param null
	 *
	 * @return $values : return count
	*/
	public function getTotalCount(){
        $where = "";
        if($this->_where){
            $where = " where ".$this->_where;
        }

        $cntField = "";
        if($this->_group){
            $cntField = "count(distinct ".$this->_group.")";
        }else{
            $cntField = "count(*)";
        }
        $this->_query = "select ".$cntField." from ".$this->_name.' '.$this->_alias.' '.$this->_join.' '.$where;

		try{
			$pResult = $this->_dbConn->prepare($this->_query);
			if(!$pResult->execute()){
				return false;
			}
			return $pResult->fetchColumn();

		}catch(PDOException $err){
			print $err;
		}
	}

	public function reset(){
        $this->_query = "";
		$this->setTableName($this->_name)
			->setOrder('')
			->setWhere(null)
			->setField("");
        $this->_join = "";
        $this->_isJoin = false;
	}

	public function setJoin($key, $objModel, $target_key=null, $type=null){
		$sJoin = "";

		switch(strtolower($type)){
			case 'equ':
			    $sJoin = "JOIN";	
			    break;
			case 'left':
			    $sJoin = "LEFT JOIN";	
			    break;
			case 'right':
			    $sJoin = "RIGHT JOIN";	
			    break;
			case 'inner':
			    $sJoin = "INNER JOIN";	
			    break;
			default:
			    $sJoin = "JOIN";	
		}

		$tb_part = "";
		$alias = "";
		$oAlias = "";

		if($this->_alias){
			$alias = $this->_alias;
		}else{
			$alias = $this->_name;
		}

		if($objModel->_alias){
			$oAlias = $objModel->_alias;
		}else{
			$oAlias = $objModel->_name;
		}

		$tb_part .= $sJoin." ".$objModel->_name.' '.$objModel->_alias." ON ";

		if($target_key){
			$tb_part .= $key." = ".$target_key;
		}else{
			$tb_part .= $alias.".".$key." = ".$oAlias.".".$objModel->_pk;
		}

		$this->_join .= " ".$tb_part;
        $this->_isJoin = true;

		return $this;
	}

	public function __destruct() {
       	// disconnect from the DB
		$this->_oDbConn = null;
		$this->_oDb = null;
   	}
}
?>
