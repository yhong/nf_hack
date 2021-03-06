<?hh
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

class Model extends Core {
    protected $_oDb     = null; // DB object variable
    protected $_dbConn  = null; // DB member object variable
    protected $_name    = null;    // table name
    protected $_pk      = null;    // primary key
    protected $_isJoin  = false;// join
    protected $_order   = null;    // order
    protected $_group   = null;    // group
    protected $_field   = "*";    // field
    protected $_where   = null;    // where
    protected $_query   = null;    // query
    protected $_limit   = null;    // limit
    protected $_join    = null;    // join
    protected $_alias   = null;    // join
    
    public function __construct(?array<string, string> $config = null) : void {
        parent::__construct();
        $this->_dbConn = $this->getDbConnection($config);
    }

    protected function getDbConnection(?array<string, string> $config){
        $dbConn = null;

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
            $dbConn = new PDO($dsn, $id, $password);
        }catch(PDOException $err){
            print $err;
        }

        return $dbConn;
    }

    /**
     * getConResource()
     * return the table name which is setted in config
     * @access public
     * 
     * @author Younghoon Hong(yhhong@nayuda.com)
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
     * @author Younghoon Hong(yhhong@nayuda.com)
     * @param $value : table name
     *
     * @return object
    */
    public function setTableName(string $value) : Model {
        $this->_name = $value;
        return $this;
    }

    /**
     * getTableName()
     * @access public
     * 
     * @author Younghoon Hong(yhhong@nayuda.com)
     *
     * @return $value : table name
    */
    public function getTableName() : string {
        return $this->_name;
    }

    /**
     * setAlias()
     * @access public
     * 
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $value : table alias name
     *
     * @return void
    */
    public function setAlias(string $value) : Model {
        $this->_alias = $value;
        return $this;
    }

    /**
     * setOrder()
     * set the "Order by" 
     * @access public
     * 
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $value : query string
     *
     * @return object
    */
    public function setOrder(?string $value) : Model {
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $value : query string
     *
     * @return object
    */
    public function setGroup(string $value) : Model {
        $this->_group = $value;

        return $this;
    }

    /**
     *  
     * setWhere()
     * set the "where" 
     * @access public
     * 
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $value : string
     *
     * @return object
    */
    public function setWhere(?string $value = null) : Model {
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $arrFieldValue : field name(array)
     *
     * @return object
    */
    public function setField($value) : Model {
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param $offset : start offset
     * @param $rows : list count
     *
     * @return object
    */
    public function setLimit($offset, $rows = null) : Model {
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param void
     *
     * @return $query : query string
    */
    public function getQuery() : string {
        return $this->_query;
    }

    /**
     * select()
     * @access public
     * 
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param null
     *
     * @return $values : return row
    */
    public function select(?int $theLimit = null){
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

        // support the Multi Database
        $limit = "";
        $dbConn = null;
        $dsn = GET_CONFIG("database-select", "dsn");

        if($dsn){
            $config = array(
                "dsn" => GET_CONFIG("database-select", "dsn"),
                "id" => GET_CONFIG("database-select", "id"),
                "password" => GET_CONFIG("database-select", "password")
            );
            $dbConn = $this->getDbConnection($config);

        }else{
            $dsn = GET_CONFIG("database", "dsn");
            $dbConn = $this->_dbConn;
        }

        // support mssql for paging
        if(substr($dsn, 0, 5) == "mysql"){ 
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

        try {
            $pResult = $dbConn->prepare($this->_query);

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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param null
     *
     * @return $values : return row
    */
    public function insert(array<string, string> $data){
        $arrValues = array();

	$dataCnt = count($data);
        for($i=0; $i<$dataCnt; $i++){
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param null
     *
     * @return $values : return row
    */
    public function update(array<string, string> $data, ?string $key=null){
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
     * @param null
     *
     * @return $values : return row
    */
    public function delete(?string $key=null){
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
     * @author Younghoon Hong(yhhong81@nayuda.com)
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
            // support the Multi Database
            $dbConn = null;
            $dsn = GET_CONFIG("database-select", "dsn");
            if($dsn){
                $config = array(
                    "dsn" => GET_CONFIG("database-select", "dsn"),
                    "id" => GET_CONFIG("database-select", "id"),
                    "password" => GET_CONFIG("database-select", "password")
                );
                $dbConn = $this->getDbConnection($config);

            }else{
                $dbConn = $this->_dbConn;
            }

            $pResult = $dbConn->prepare($this->_query);

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
     * @author Younghoon Hong(yhhong81@nayuda.com)
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
            // support the Multi Database
            $dbConn = null;
            $dsn = GET_CONFIG("database-select", "dsn");
            if($dsn){
                $config = array(
                    "dsn" => GET_CONFIG("database-select", "dsn"),
                    "id" => GET_CONFIG("database-select", "id"),
                    "password" => GET_CONFIG("database-select", "password")
                );
                $dbConn = $this->getDbConnection($config);

            }else{
                $dbConn = $this->_dbConn;
            }

            $pResult = $dbConn->prepare($this->_query);
            if(!$pResult->execute()){
                return false;
            }
            return $pResult->fetchColumn();

        }catch(PDOException $err){
            print $err;
        }
    }

    public function reset() : void {
        $this->_query = "";
        $this->setTableName($this->_name)
             ->setOrder('')
             ->setWhere(null)
             ->setField("");
        $this->_join = "";
        $this->_isJoin = false;
    }

    public function setJoin(?string $key, Model $objModel, ?string $target_key=null, ?string $type=null) : Model {
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

    public function __destruct(){
        // disconnect from the DB
        $this->_oDb = null;
    }
}
