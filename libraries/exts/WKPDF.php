<?hh
        // Automated configuration. Modify these if they fail. (they shouldn't ;) )
        //$GLOBALS['WKPDF_BASE_PATH']=str_replace(str_replace('\\','/',getcwd().'/'),'',dirname(str_replace('\\','/',__FILE__))).'/';
        $GLOBALS['WKPDF_BASE_PATH']="/var/www/crei/var/pdf";
        $GLOBALS['WKPDF_BASE_SITE']='http://'.$_SERVER['SERVER_NAME'].'/pdf';

        /**
         * @author Christian Sciberras
         * @see <a href="http://code.google.com/p/wkhtmltopdf/">http://code.google.com/p/wkhtmltopdf/</a>
         * @copyright 2010 Christian Sciberras / Covac Software.
         * @license LGPL
         * @example
         *   <font color="#008800"><i>//-- Create sample PDF and embed in browser. --//</i></font><br>
         *   <br>
         *   <font color="#008800"><i>// Include WKPDF class.</i></font><br>
         *   <font color="#0000FF">require_once</font>(<font color="#FF0000">'wkhtmltopdf/wkhtmltopdf.php'</font>);<br>
         *   <font color="#008800"><i>// Create PDF object.</i></font><br>
         *   <font color="#EE00EE">$pdf</font>=new <b>WKPDF</b>();<br>
         *   <font color="#008800"><i>// Set PDF's HTML</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">set_html</font>(<font color="#FF0000">'Hello &lt;b&gt;Mars&lt;/b&gt;!'</font>);<br>
         *   <font color="#008800"><i>// Convert HTML to PDF</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">render</font>();<br>
         *   <font color="#008800"><i>// Output PDF. The file name is suggested to the browser.</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">output</font>(<b>WKPDF</b>::<font color="#EE00EE">$PDF_EMBEDDED</font>,<font color="#FF0000">'sample.pdf'</font>);<br>
         * @version
         *   0.0 Chris - Created class.<br>
         *   0.1 Chris - Variable paths fixes.<br>
         *   0.2 Chris - Better error handlng (via exceptions).<br>
         * <font color="#FF0000"><b>IMPORTANT: Make sure that there is a folder in %LIBRARY_PATH%/tmp that is writable!</b></font>
         * <br><br>
         * <b>Features/Bugs/Contact</b><br>
         * Found a bug? Want a modification? Contact me at <a href="mailto:uuf6429@gmail.com">uuf6429@gmail.com</a> or <a href="mailto:contact@covac-software.com">contact@covac-software.com</a>...
         *   guaranteed to get a reply within 2 hours at most (daytime GMT+1).
         */
        class WKPDF {
                /**
                 * Private use variables.
                 */
                private string $html='';
                private string $cmd='';
                private string $tmp='';
                private string $pdf='';
                private string $status='';
                private string $orient='Portrait';
                private string $size='A4';
                private bool $toc=false;
                private int $copies=1;
                private bool $grayscale=false;
                private string $title='';
                private string $cpu='';
                /**
                 * Advanced execution routine.
                 * @param string $cmd The command to execute.
                 * @param string $input Any input not in arguments.
                 * @return array An array of execution data; stdout, stderr and return "error" code.
                 */
                private function _pipeExec(string $cmd, ?string $input='') {
                    $pipes = array();
                    $proc = proc_open($cmd." &", array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')), $pipes);
                    fwrite($pipes[0], $input);
                    fclose($pipes[0]);
                    $stdout=stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $stderr=stream_get_contents($pipes[2]);
                    fclose($pipes[2]);

                    $proc_status = proc_get_status($proc);
                    exec('kill -9 '.$proc_status['pid']);
                    $rtn=proc_close($proc);

                    return array(
                        'stdout'=>$stdout,
                        'stderr'=>$stderr,
                        'return'=>$rtn
                    );
                }
                /**
                 * Function that attempts to return the kind of CPU.
                 * @return string CPU kind ('amd64' or 'i386').
                 */
                 /*
                private function _getCPU(){
                        if(self::$cpu==''){
                                if(`grep -i amd /proc/cpuinfo`!=''){
                                     self::$cpu='amd64';
                                }
                                elseif(`grep -i intel /proc/cpuinfo`!='')       self::$cpu='i386';
                                else throw new Exception('WKPDF couldn\'t determine CPU ("'.`grep -i vendor_id /proc/cpuinfo`.'").');
                        }
                        return self::$cpu;
                }
                */
                /**
                 * Force the client to download PDF file when finish() is called.
                 */
                public $PDF_DOWNLOAD='D';
                /**
                 * Returns the PDF file as a string when finish() is called.
                 */
                public $PDF_ASSTRING='S';
                /**
                 * When possible, force the client to embed PDF file when finish() is called.
                 */
                public $PDF_EMBEDDED='I';
                /**
                 * PDF file is saved into the server space when finish() is called. The path is returned.
                 */
                public $PDF_SAVEFILE='F';
                /**
                 * PDF generated as landscape (vertical).
                 */
                public $PDF_PORTRAIT='Portrait';
                /**
                 * PDF generated as landscape (horizontal).
                 */
                public $PDF_LANDSCAPE='Landscape';
                /**
                 * Constructor: initialize command line and reserve temporary file.
                 */
                public function __construct(){
                        //$this->cmd=$GLOBALS['WKPDF_BASE_PATH'].'wkhtmltopdf-'.self::_getCPU();
                        $this->cmd='/usr/bin/wkhtmltopdf';
                        //$this->cmd='/var/www/crei/wkhtmltopdf-amd64';
                        if(!file_exists($this->cmd))throw new Exception('WKPDF static executable "'.htmlspecialchars($this->cmd,ENT_QUOTES).'" was not found.');
                        do{
                                //$this->tmp=$GLOBALS['WKPDF_BASE_PATH'].'tmp/'.mt_rand().'.html';
                                $this->tmp=$GLOBALS['WKPDF_BASE_PATH'].'/'.mt_rand().'.html';
                        } while(file_exists($this->tmp));
                }
                /**
                 * Set orientation, use constants from this class.
                 * By default orientation is portrait.
                 * @param string $mode Use constants from this class.
                 */
                public function set_orientation(string $mode) : void {
                        $this->orient = $mode;
                }
                /**
                 * Set page/paper size.
                 * By default page size is A4.
                 * @param string $size Formal paper size (eg; A4, letter...)
                 */
                public function set_page_size(string $size) : void {
                        $this->size = $size;
                }
                /**
                 * Whether to automatically generate a TOC (table of contents) or not.
                 * By default TOC is disabled.
                 * @param boolean $enabled True use TOC, false disable TOC.
                 */
                public function set_toc(bool $enabled) : void {
                        $this->toc=$enabled;
                }
                /**
                 * Set the number of copies to be printed.
                 * By default it is one.
                 * @param integer $count Number of page copies.
                 */
                public function set_copies(int $count) : void {
                        $this->copies=$count;
                }

                /**
                 * Whether to print in grayscale or not.
                 * By default it is OFF.
                 * @param boolean True to print in grayscale, false in full color.
                 */
                public function set_grayscale(bool $mode) : void {
                        $this->grayscale=$mode;
                }
                /**
                 * Set PDF title. If empty, HTML <title> of first document is used.
                 * By default it is empty.
                 * @param string Title text.
                 */
                public function set_title(string $text) : void {
                        $this->title=$text;
                }
                /**
                 * Set html content.
                 * @param string $html New html content. It *replaces* any previous content.
                 */
                public function set_html(string $html) : void {
                        $this->html=$html;
                        file_put_contents($this->tmp, $html);
                }
                /**
                 * Returns WKPDF print status.
                 * @return string WPDF print status.
                 */
                public function get_status() : string {
                        return $this->status;
                }
                /**
                 * Attempts to return the library's full help.
                 * @return string WKHTMLTOPDF HTML help.
                 */
                 /*
                public function get_help() : string {
                        $tmp=self::_pipeExec('"'.$this->cmd.'" --extended-help');
                        return $tmp['stdout'];
                }
                */
                /**
                 * Convert HTML to PDF.
                                o' --lowquality'  
                 */
                public function render($web) : void {
                    //$web=$GLOBALS['WKPDF_BASE_SITE'].$GLOBALS['WKPDF_BASE_PATH'].'tmp/'.basename($this->tmp);
                    //$web=$GLOBALS['WKPDF_BASE_SITE'].'/'.basename($this->tmp);
                    //$web="/var/www/crei/var/pdf/".basename($this->tmp);
                    $pdfData = $this->_pipeExec(
                            '/usr/bin/xvfb-run -a -s "-screen 0, 1024x680x24" '.$this->cmd.' -q'
                           // .(($this->copies>1)?' --copies '.$this->copies:'')                    // number of copies
                            .' --zoom 2.5'                                                          // zoom
                            .' --lowquality' 
                            .' --margin-bottom 19.8'  
                            .' --orientation '.$this->orient                                        // orientation
                            .' --page-size '.$this->size                                            // page size
                           // .($this->toc?' --toc':'')                                             // table of contents
                            .($this->grayscale?' --grayscale':'')                                   // grayscale
                            .(($this->title!='')?' --title "'.$this->title.'"':'')                  // title
                            .' "'.$web.'" -'                                                        // URL and optional to write to STDOUT
                    );

                    if(strpos(strtolower($pdfData['stderr']),'error')!==false)throw new Exception('WKPDF system error: <pre>'.$pdfData['stderr'].'</pre>');
                    if($pdfData['stdout']=='')throw new Exception('WKPDF didn\'t return any data. <pre>'.$pdfData['stderr'].'</pre>');
                    if(((int)$pdfData['return'])>1)throw new Exception('WKPDF shell error, return code '.(int)$pdfData['return'].'.');
                    $this->status=$pdfData['stderr'];
                    $this->pdf=$pdfData['stdout'];
                    unlink($this->tmp);
                }
                /**
                 * Return PDF with various options.
                 * @param string $mode How two output (constants from this same class).
                 * @param string $file The PDF's filename (the usage depends on $mode.
                 * @return string|boolean Depending on $mode, this may be success (boolean) or PDF (string).
                 */
                public function output(string $mode, ?string $file=null){
                        switch($mode){
                                case $this->PDF_DOWNLOAD:
                                        if(!headers_sent()){
                                                header('Content-Description: File Transfer');
                                                header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                                                header('Pragma: public');
                                                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                                                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                                                // force download dialog
                                                header('Content-Type: application/force-download');
                                                header('Content-Type: application/octet-stream', false);
                                                header('Content-Type: application/download', false);
                                                header('Content-Type: application/pdf', false);
                                                // use the Content-Disposition header to supply a recommended filename
                                                header('Content-Disposition: attachment; filename="'.basename($file).'";');
                                                header('Content-Transfer-Encoding: binary');
                                                header('Content-Length: '.strlen($this->pdf));
                                                echo $this->pdf;
                                        }else{
                                                throw new Exception('WKPDF download headers were already sent.');
                                        }
                                        break;
                                case $this->PDF_ASSTRING:
                                        return $this->pdf;
                                        break;
                                case $this->PDF_EMBEDDED:
                                        if(!headers_sent()){
                                                $this->pdf = str_replace('#00', '', $this->pdf);
                                                header('Content-Type: application/pdf');
                                                header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                                                header('Pragma: public');
                                                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                                                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                                                header('Content-Length: '.strlen($this->pdf));
                                                header('Content-Disposition: inline; filename="'.basename($file).'";');
                                                echo $this->pdf;
                                        }else{
                                                throw new Exception('WKPDF embed headers were already sent.');
                                        }
                                        break;
                                case $this->PDF_SAVEFILE:
                                        return file_put_contents($file,$this->pdf);
                                        break;
                                default:
                                        throw new Exception('WKPDF invalid mode "'.htmlspecialchars($mode,ENT_QUOTES).'".');
                        }
                        return false;
                }
        }
