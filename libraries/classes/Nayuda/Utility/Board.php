<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Utility;
use Nayuda\Core;

class Board extends Core {
    protected $tableName = "";
    protected $baseUrl = "";

    protected $nEndNum = 0;
    protected $sPaging = "";


	function __construct($table_name){
        $this->tableName = $table_name;
    }

    public function setBaseUrl($value){
        $this->baseUrl = $value;
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }

   	public function getList(){
        $bbModel = getModel('board_board');
		$bbModel->setWhere("table_name='".$this->tableName."'");
        $objPage = new Pager($bbModel->getTotalCount(), GET("Page"), GET("PageG"));
        $objPage->setIteration("<li>&lt;&lt;</li>", "<li>&gt;&gt;</li>");
        $objPage->PageCal();

        $fields = array(
                "BB_UID", 
                "if(BB_NAME != '_', BB_NAME, (select name from member where uid = board_board.member_id)) as WRITER", 
                "BB_SUBJECT", 
                "BB_NAME",
                "BB_HIT",
                "BB_THREAD",
                "DATE_FORMAT(BB_DATE, '%Y-%m-%d') as BB_DATE" 
         );
        $bbModel->setField($fields)
               ->setOrder("BB_PID ASC, BB_THREAD ASC")
               ->setLimit($objPage->getStartNum(), $objPage->getPagePerRow());
        $res = $bbModel->select();

        $this->nEndNum = $bbModel->getTotalCount() - $objPage->getStartNum();
        $this->sPaging = $objPage->viewPageNumber($this->baseUrl."?name=".$this->tableName."&q=".GET('q')."&b=".GET('b')."&");

        return $res;
   	}

    public function getListInfo(){
        return array("end_num"=>$this->nEndNum, "paging"=>$this->sPaging);
    }

    public function viewData($uid){
        $bbModel = getModel('board_board');
        $fields = array(
                        "BB_UID", 
                        "BB_PID", 
                        "BB_NAME", 
                        "BB_ROOT", 
                        "BB_SUBJECT", 
                        "BB_CONTENT", 
                        "BB_THREAD", 
                        "BB_HIT", 
                        "member_id",
                        "DATE_FORMAT(BB_DATE, '%Y-%m-%d') as BB_DATE" 
         );
        $bbModel->setField($fields)
               ->setWhere("table_name='".$this->tableName."' and BB_UID=".$uid);
        $res = $bbModel->select(1);
		
		// increase hit number
		$bbModel->reset();
		$bb_hit = $res["BB_HIT"] + 1;
		$bbModel->update(array("BB_HIT" => $bb_hit), $uid);

        return $res;
     }
	
	
	 public function modify($uid){
        $bbModel = getModel('board_board');
        $fields = array(
                        "BB_UID", 
                        "BB_NAME", 
                        "BB_SUBJECT", 
                        "BB_CONTENT", 
			"BB_HIT", 
                        "DATE_FORMAT(BB_DATE, '%Y-%m-%d') as BB_DATE" 
         );
        $bbModel->setField($fields)
               ->setWhere("table_name='".$this->tableName."' and BB_UID=".$uid);
        $res = $bbModel->select(1);
		
		// increase hit number
		$bbModel->reset();
		$bb_hit = $res["BB_HIT"] + 1;
		$bbModel->update(array("BB_HIT" => $bb_hit), $uid);

        return $res;
    }
	
	public function modifyAction(){
		$bbModel = getModel('board_board');
		$bbModel->setWhere("BB_UID=".POST("uid"));
		$bbModel->update(array("BB_SUBJECT"=>POST("title"), "BB_CONTENT"=>POST("content")), POST("uid"));
	}
	
	public function deleteAction($uid){
		$bbModel = getModel('board_board');
		$bbModel->delete($uid);
	}
	
	public function reply($uid){	
		$bbModel = getModel('board_board');
		$bbModel->setWhere("BB_UID=".$uid);
		$data = $bbModel->select(1);
		
		$this->oTpl->assign("reply_data", $data);
		$this->oTpl->assign("reply_uid", $data["BB_UID"]);
		$this->oTpl->setView("customer/qna_write");
	}
	
	public function write(){
		// This is not public service
		if(!SESSION("auth_people_uid")){
		   GO("/auth/login");
		}
	}
	
	public function writeAction(){
		$bbModel = getModel('board_board');

		$BB_PID = 0.0;
        $BB_ROOT = 0;

		$bbModel->setField("max(BB_UID) as max_uid");
		$bdata2 = $bbModel->select(1);
		$max_uid = (int)$bdata2["max_uid"] + 1;
		
		$bbModel->reset();
		if(!POST("reply_uid")){ // new
			$bbModel->setField("min(BB_PID) as min_pid");
			$bbModel->setWhere("table_name='".$this->tableName."'");
			$bdata = $bbModel->select(1);
			
			if($bdata["min_pid"]){
				$BB_PID = (double)$bdata["min_pid"] - 1;
			}else{
				$BB_PID = 9999999.0000;
			}
			$BB_THREAD = 0;
			$BB_ROOT = 1;

		}else{ // reply
			$bbModel->setWhere("BB_UID=".POST("reply_uid"));
			$bbModel->setWhere("table_name='".$this->tableName."'");

			$data = $bbModel->select(1);
			$DPID = $data["BB_PID"];
			$UPID = (double)$data["BB_PID"] + 1.0000;
			
            if($data["BB_ROOT"] == 1){
                $BB_ROOT = 1;
                $bbModel->update(array("BB_ROOT"=>0));
            }

			$bbModel->reset();
			$bbModel->setWhere("BB_PID > " . $DPID . " AND BB_PID < " . $UPID);
			$bbModel->update(array("BB_PID" => "&BB_PID + 0.0001"));
			
			$BB_PID			= (double)$data["BB_PID"] + 0.0001;
			$BB_THREAD		= (double)$data["BB_THREAD"] + 1;
		}
	
		$memModel = getModel("member");
		$memModel->setWhere("member_id='".SESSION("auth_people_id")."'");
		$user_data = $memModel->select(1);
		
		$bbModel->reset();
		$data = array(
			"BB_UID" => $max_uid,
			"BB_PID" => $BB_PID,
			"BB_THREAD" => $BB_THREAD,
			"BB_ROOT" => $BB_ROOT,
			"BB_NAME"=>"_",
			"table_name" => $this->tableName,
			"BB_SUBJECT" => POST("title"),
			"BB_CONTENT" => POST("content"),
			"BB_HIT" => 0,
			"BB_IP"=>SERVER("REMOTE_ADDR"),
			"BB_AGENT"=>SERVER('HTTP_USER_AGENT'),
			"member_id"=>$user_data["uid"]
		);
		$bbModel->insert($data);
	}
}
