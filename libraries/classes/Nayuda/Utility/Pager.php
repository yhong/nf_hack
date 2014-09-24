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

class Pager extends Core {
	protected $totalPageNum;	// total page
	protected $PagePerRow;		// the row number per page
	protected $limitPagenum;	// page number for display
	protected $startnum;		// start number of page
	protected $endnum;			// end number of page
	protected $PageG;
	protected $Page;
	protected $allpageG;
	protected $rowvalue;
	protected $strLink;

	protected $iterPrevPage;
	protected $iterNextPage;

	function __construct($totalnum, $Page, $PageG){
		$this->iterPrevPage = "<<";
		$this->iterNextPage = ">>";
		$this->PagePerRow = GET_CONFIG("site", "page_per_row");		
		$this->limitPagenum = GET_CONFIG("site", "limit_page_num");
		$this->setUpPage($Page, $PageG); //initialize page
		$this->rowvalue = $totalnum;
        $this->PageCal();
	}

	public function PageCal(){
		if($this->PagePerRow == 0){
			$this->totalPageNum = 0;
		}else{
			$this->totalPageNum = ceil($this->rowvalue / $this->PagePerRow);
		}

		$this->startnum = ($this->Page-1) * $this->PagePerRow;
		
		if($this->limitPagenum == 0){
			$this->allpageG = 0;
		}else{
			$this->allpageG = ceil($this->totalPageNum/$this->limitPagenum);
		}
	}

	protected function setUpPage($Page, $PageG){
		if(!$PageG){
			$this->setPageG(1);
		}else{
			$this->setPageG($PageG);
		}

		if(!$Page){
			$this->setPage(1);
		}else{
			$this->setPage($Page);
		}
	}

	public function getStartNum(){
		return $this->startnum;
	}

	public function setPage($value){
		$this->Page = $value;
	}

	public function setPageG($value){
		$this->PageG = $value;
	}

	public function getPage(){
		return $this->Page; 
	}

	public function getPageG(){
		return $this->PageG;
	}

		// 총 페이지수 얻어오기
	public function getTotalPageNum(){
		return $this->totalPageNum;
	}

	public function setLink($arrValue){
		while(list($key, $value) = each($arrValue)){
			$this->strLink .= $key.'='.$value.'&';
		}
		//$this->strLink = substr($this->strLink, 0, -1);
	}

	/**
    * Display the page list in the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param String  the link data
    * @return string of pageinfo
    * @access public
    */
	public function viewMainPageNumber($link){
		// print page
        $mainPageView = "";
		$this->startPage = ($this->PageG-1) * $this->limitPagenum + 1;
		$this->endPage = $this->startPage + $this->limitPagenum - 1;

		if($this->endPage >= $this->totalPageNum){
			$this->endPage = $this->totalPageNum;
		}

		for($i=$this->startPage; $i<=$this->endPage; $i++){
			
			if($this->Page == $i){
				$printPageNumber="<b><font style='color:blue'>".$i."</font></b>";
			}else{
				$printPageNumber=$i;
			}
			$mainPageView .= "<span class='page-item'><a href=".$link."Page=".$i."&PageG=".$this->PageG.">".$printPageNumber."</a></span>";
		}
		return $mainPageView;
	}
	
	/**
    * Display the page list in the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param void
    * @return string of pageinfo
    * @access public
    */
	public function getMainPageNumber(){
		
		// print page
		$this->startPage = ($this->PageG-1) * $this->limitPagenum + 1;
		$this->endPage = $this->startPage + $this->limitPagenum - 1;

		$output = array();
		if($this->endPage >= $this->totalPageNum){
			$this->endPage = $this->totalPageNum;
		}
		$index=0;
		for($i=$this->startPage; $i<=$this->endPage; $i++){
			$output[$index]["page"] = $i;
			$output[$index]["page_g"] = $this->PageG;
			
			if($this->Page == $i){
				$output[$index]["is_curr"] = "true";
			}else{
				$output[$index]["is_curr"] = "";
			}
			$index++;
		}
		return $output;
	}

	public function setIteration($iterPrevPage, $iterNextPage){
		$this->iterPrevPage = $iterPrevPage;
		$this->iterNextPage = $iterNextPage;
	}

	/**
    * display the link of the next page after the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param String  the link data
    * @return string of pageinfo
    * @access public
    */
	public function viewNextPageNumber($link){
		if($this->PageG >= $this->allpageG){
			echo "";
		}else{
			$nextpage = ($this->PageG) * $this->limitPagenum+1;
			$nextPageView = "<span class='page-item'><a href=".$link."Page=".$nextpage."&PageG=".($this->PageG+1).">".$this->iterNextPage."</a></span>";
		
			return $nextPageView;
		}
	}
	
	/**
    * get a array of the next page after the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param void
    * @return string of pageinfo
    * @access public
    */
	public function getNextPageNumber(){
		$output = array();
		if($this->PageG >= $this->allpageG){
			$output["page"] = "";
			$output["page_g"] = "";
			$output["title"] = "";
		}else{
			$nextpage = ($this->PageG) * $this->limitPagenum+1;
			$output["page"] = $nextpage;
			$output["page_g"] = $this->PageG+1;
			$output["title"] = $this->iterNextPage;

		return $output;
		}
	}

	/**
    * display the link of the previous page after the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param String  the link data
    * @return string of pageinfo
    * @access public
    */
	public function viewPrevPageNumber($link){
		// previous page
		if($this->PageG == 1){
			echo "";
		}else{
			if(($this->PageG - 1) == 1){
				$prevpage=1;
			}else{
				$prevpage=($this->PageG-2) * $this->limitPagenum+1;
			}
		
		$prevPageView = "<span class='page-item'><a href=".$link."&Page=".$prevpage."&PageG=".($this->PageG-1).">".$this->iterPrevPage."</a><span>";
		
		return $prevPageView;
		}
	}

	/**
    * get the array of the previous page after the current page list
    *
    * @author eric hong(eric.hong81@gmail.com)
    * @param String  the link data
    * @return string of pageinfo
    * @access public
    */
	public function getPrevPageNumber(){
		$output = array();
		
		if($this->PageG == 1){
			$output["page"] = "";
			$output["page_g"] = "";
			$output["title"] = "";
		}else{
			if(($this->PageG-1) == 1){
				$prevpage=1;
			}else{
				$prevpage=($this->PageG-2) * $this->limitPagenum+1;
			}
			
			$output["page"] = $prevpage;
			$output["page_g"] = $this->PageG-1;
			$output["title"] = $this->iterPrevPage;

		return $output;
		}
	}

	/**
	 *  
	 * display the page
	 * public
	 * 
	 * @author eric hong(eric.hong81@gmail.com)
	 * @param $value : value for modify
	 *
	 * @return nothing
	*/
	public function viewPageNumber($PageLink){
		$output1 = $this->viewPrevPageNumber($PageLink);
		$output2 = $this->viewMainPageNumber($PageLink);
		$output3 = $this->viewNextPageNumber($PageLink);

		return $output1.$output2.$output3;
	}

	/**
	 * setPagePerRow()
	 * set the row number per page
	 * public
	 * 
	 * @param $value : value of modify
	 *
	 * @return nothing
	*/
	public function setPagePerRow($value){
		$this->PagePerRow = $value;
	}

	/**
	 * getPagePerRow()
	 * get the row number per page
	 * public
	 * 
	 * @param nothing
	 *
	 * @return $PagePerRow : return value
	*/
	public function getPagePerRow(){
		return $this->PagePerRow;
	}
	
	/**
	 * setlimitPagenum()
	 * set limit number of the page
	 * public
	 * 
	 * @param $value : value for modify
	 *
	 * @return nothing
	*/
	public function setlimitPagenum($value){
		$this->limitPagenum = $value;
	}

	/**
	 * getlimitPagenum()
	 * get limit number of the page
	 * public
	 * 
	 * @param nothing
	 *
	 * @return $limitPagenum : return value
	*/
	public function getlimitPagenum(){
		return $this->limitPagenum;
	}
}
?>
