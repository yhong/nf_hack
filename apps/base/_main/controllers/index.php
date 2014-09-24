<?php
use Nayuda\Core\Controller;

class index extends Controller{
	function __construct($arrParam) {
		 parent::__construct($arrParam);
		 $this->oTpl->setLayout("default");
	}
   	
   	public function index(){
		$this->oTpl->setView("index");
   	}
}
?>
