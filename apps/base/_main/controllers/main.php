<?php
use Nayuda\Core\Controller;

class main extends Controller{
	function __construct($arrParam) {
		 parent::__construct($arrParam);
		 $this->oTpl->setLayout("default");
	}
	function __destruct() {
       parent::__destruct();
   	}
	
   	public function index(){
		$this->oTpl->setView("main/index");
   	}
}
?>
