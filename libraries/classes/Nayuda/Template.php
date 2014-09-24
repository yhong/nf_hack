<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda;

abstract class Template extends Core{

	/*
     * Set variable
     * @abstract
     */
	abstract public function assign($id, $value = null);
	
    /*
	 * Set layout
     * @abstract
     */
	abstract public function setLayout($sLayoutName, $sSubLayoutName = null);

    /*
     * Get block
     * @abstract
     */
    abstract public function blockFetch($file);

    /*
	 * Display for layout
     * @abstract
     */
	abstract public function errorDisplay($tpl_name);

    /*
	 * display for layout
     * @abstract
     */
	abstract public function display($tpl_name);
}
?>
