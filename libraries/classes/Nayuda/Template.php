<?hh
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2013 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda;

abstract class Template extends Core {
    /*
     * Set variable
     * @abstract
     */
	abstract public function assign(string $id, ?string $value = null) : void;
	
    /*
	 * Set layout
     * @abstract
     */
	abstract public function setLayout(string $sLayoutName, ?string $sSubLayoutName = null) : void;

    /*
     * Get block
     * @abstract
     */
    abstract public function blockFetch(string $file) : string;

    /*
	 * Display for layout
     * @abstract
     */
	abstract public function errorDisplay(string $tpl_name) : void;

    /*
	 * display for layout
     * @abstract
     */
	abstract public function display(string $tpl_name, ?string $cacheId, ?string $compileId) : void;
}
