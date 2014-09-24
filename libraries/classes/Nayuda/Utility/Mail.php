<?php
/**
 * Nayuda Framework (http://framework.nayuda.com/)
 *
 * @link    https://github.com/yhong/nf for the canonical source repository
 * @copyright Copyright (c) 2003-2014 Nayuda Inc. (http://www.nayuda.com)
 * @license http://framework.nayuda.com/license/new-bsd New BSD License
 */
namespace Nayuda\Utility;
use Nayuda\Core;

class Mail extends Core {
    
    protected $smtp_host = "localhost";
    protected $port = "25";
    protected $type = "text/html";
    protected $charSet = GET_CONFIG("site", "charset");

	function __construct(){
    }

    public function setSmtp($value){
        $this->smtp_host = $value;
    }

    public function setPort($value){
        $this->port = $value;
    }

    /** @smtp Sending Mail
     *
     * @param $fromName sender
     * @param $fromEmail sender's mail address
     * @param $toName receiver
     * @param $toEmail receiver's mail address
     * @param $subject title of mail
     * @param $contents content of mail
     * @param $isDebug debugging mode(0:Off, 1:On)
     * @return sendmail_flag Success(true), Fail(false) 
     */ 
     function sendMail($fromName, $fromEmail, $toName, $toEmail, $subject, $contents, $isDebug=0){
        //Open Socket
        $fp = @fsockopen($this->smtp_host, $this->port, $errno, $errstr, 1);

        if($fp){
            //Connection and Greetting
            $returnMessage = fgets($fp, 128);
            if($isDebug){
                echo "CONNECTING MSG:".$returnMessage."\n";
            }
            fputs($fp, "HELO YA\r\n");
            $returnMessage = fgets($fp, 128);
            if($isDebug){
                echo "GREETING MSG:".$returnMessage."\n";
            }
            fputs($fp, "MAIL FROM: <".$fromEmail.">\r\n");
            $returnvalue[0] = fgets($fp, 128);
            fputs($fp, "rcpt to: <".$toEmail.">\r\n");
            $returnvalue[1] = fgets($fp, 128);
            if($isDebug){
                echo "returnvalue:";
                print_r($returnvalue);
            }

            //Data
            fputs($fp, "data\r\n");
            $returnMessage = fgets($fp, 128);
            if($isDebug)
                echo "data:".$returnMessage;
            fputs($fp, "Return-Path: ".$fromEmail."\r\n");
            fputs($fp, "From: ".$fromName." <".$fromEmail.">\r\n");
            fputs($fp, "To: <".$toEmail.">\r\n");
            $subject = "=?".$this->charSet."?B?".base64_encode($subject)."?=";
            fputs($fp, "Subject: ".$subject."\r\n");
            fputs($fp, "Content-Type: ".$this->type."; charset=\"".$this->charSet."\"\r\n");
            fputs($fp, "Content-Transfer-Encoding: base64\r\n");
            fputs($fp, "\r\n");
            $contents= chunk_split(base64_encode($contents));
            fputs($fp, $contents);
            fputs($fp, "\r\n");
            fputs($fp, "\r\n.\r\n");
            $returnvalue[2] = fgets($fp, 128);

            //Close Connection
            fputs($fp, "quit\r\n");
            fclose($fp);

            //Message
            if (preg_match("/^250/", $returnvalue[0])&&preg_match("/^250/", $returnvalue[1])&&preg_match("/^250/", $returnvalue[2])){
                $sendmail_flag = true;
            }else {
                $sendmail_flag = false;
                echo "NO :".$errno.", STR : ".$errstr;
            }
        }
        if (! $sendmail_flag){
            echo "메일 보내기 실패";
        }
        return $sendmail_flag;
    }
}

?>
