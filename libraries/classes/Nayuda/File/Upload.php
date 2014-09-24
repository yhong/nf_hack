<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\File;

class Upload extends Nayuda\Core{
	
	private $mTmpDir;
	private $mTargetDir;
	private $mFilename;
	private $mArrFile;
	private $mLimitFilesPerDir;
	
	function __construct($filename){
		$this->mTmpDir = TMP_UPLOADS_PATH;
		$this->mTargetDir = UPLOADS_PATH;
		$this->mFilename = $filename;
		$this->mArrFile = explode(".", $_POST["filename"]);
		$this->mLimitFilesPerDir = 100; //the maximum count of files in directory
	}
	
	/**
     * sort directory and search the directory which is the most high order of alphabet
     * @access private
     */
	private function return_lastdir($file_path){
	
			if (is_dir($file_path)){
				//$dir_handle  = opendir($file_path);
				$object = scandir($file_path);
				
				$object = $this->except_special_dir($object);
				
				if(is_array($object) && count($object)){
					rsort($object);
					$latest_dir = $object[0]; // number of directory
					return $latest_dir;
				}else{
					$this->mkpath($file_path.DS."0");
					if(is_dir($file_path.DS."0")){
						return "0";
					}else{
						return false;
					}
				}
			}else{
				return false;
			}					
		}
	private function except_special_dir($array_data){
		$tmp_arr = array(); 
		if(is_array($array_data)){
			foreach($array_data as $dir){
				if($dir != '.' && $dir != '..'){
					array_push($tmp_arr, $dir);
				}
			}
			return $tmp_arr;
		}else{
			echo "Except_special_dir: error!";
			exit;
		}
	}
		
	/**
     * return how many files in current directory
     * @access private
     */
	private function return_filecount($file_path){
			$file_count = 0;
			foreach(scandir($file_path) as $file) {
				if ($file != '.' && $file != '..' && !is_dir($file_path.DS.$file)) {
					$file_count = $file_count + 1;
				}
			}
			return $file_count;
	}
		
	/**
     * if there are no path then make it.
     * @access private
     */
	private function mkpath($path){
			$dirs=array();
			$path=preg_replace('/(\/){2,}|(\\\){1,}/','/',$path);
			$dirs=explode("/",$path);
			$path="";
			foreach ($dirs as $element){
				$path.=$element."/";
				if(!is_dir($path)){ 
					if(!mkdir($path)){ 
						return false; 
					}
				}   
	 		}
		}
		
	private function makeAndCheckPath($file_path){
			if(!is_dir($file_path)){
				$this->mkpath($file_path);
				if(!is_dir($file_path)){
					echo 'Could not make directory';
					exit;
				}
			}
	}
		
		
	private function getUploadPath($baseDir){
			if(($latest_dir = $this->return_lastdir($baseDir))!= null){	// return current directory
				$work_dir = $baseDir.DS.$latest_dir;
							
				// if there is full of value in directory then add 1 more to parent directory
				if($this->return_filecount($work_dir) >= $this->mLimitFilesPerDir){
					$latest_dir = $latest_dir+1;	// add 1 more to directory name
					$work_dir = $baseDir."/".($latest_dir);	
								
					$this->makeAndCheckPath($work_dir);
				}
				return $latest_dir;
			}else{
				return null;
			}
	}

	public function getExt(){
		if(count($this->mArrFile)<2){
			die("Upload the normal file, please!");
		}else{
			return strtolower($this->mArrFile[1]);
		}
	}
	
	/**
     * if $ext is false, then return the value without file extension
     * @access public
     */
	public function getFilename($ext = false){
		if(count($this->mArrFile)<2){
			die("Upload the normal file, please!");
		}else{
			if(!$ext){
				return $this->mArrFile[0];
			}else{
				return $this->mFilename;
			}
		}
	}
	
	/**
     * if $ext is false, then return the value without file extension
     * @access public
     */
	public function getFilenameWithCrypt($ext = false){
		if(count($this->mArrFile)<2){
			die("Upload the normal file, please!");
		}else{
			$filename = md5($this->marrfile[0].mktime().GET_CONFIG("session", "salt"));
			if(!$ext){
				return $filename;
			}else{
				return $filename.'.'.$this->mArrFile[1];
			}
		}
	}
	
	public function getTargetPath(){
		$basepath = date('Y')."/".date('m')."/".date('d');
		
		$this->makeAndCheckPath(UPLOADS_PATH.DS.$basepath);

		return  $basepath."/".$this->getUploadPath(UPLOADS_PATH.DS.$basepath);
	}
}
?>
