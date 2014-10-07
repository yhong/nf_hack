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

class Email extends Core {
    protected $mailTo = "";
    protected $mailFrom = "";
    protected $replyTo = "";
    protected $subject = "";
    protected $plainMessage = "";
    protected $htmlMessage = "";
    protected $charSet = "";

    function __construct(){
        //define the receiver of the email
        $this->mailTo = 'youraddress@example.com';
        $this->mailFrom = 'webmaster@example.com';
        $this->replyTo = 'webmaster@example.com';
        $this->charSet = GET_CONFIG("site", "charset");
    }

    public function setMailTo($value){
        $this->mailTo = $value;
    }

    public function setMailFrom($value){
        $this->mailFrom = $value;
    }

    public function setReplyTo($value){
        $this->replyTo = $value;
    }

    public function setSubject($value){
        $this->subject = $value;
    }

    public function setPlainMessage($value){
        $this->plainMessage = $value;
    }

    public function setHtmlMessage($value){
        $this->htmlMessage = $value;
    }

    public function getMailTo(){
        return $this->mailTo;
    }

    public function getMailFrom(){
        return $this->mailFrom;
    }

    public function getReplyTo(){
        return $this->replyTo;
    }

    public function getSubject(){
        return $this->subject;
    }

    public function getPlainMessage(){
        return $this->plainMessage;
    }

    public function getHtmlMessage(){
        return $this->htmlMessage;
    }

    public function sendAsPlain(){
        //define the headers we want passed. Note that they are separated with \r\n
        $headers = "From: ".$this->getMailFrom()."\r\nReply-To: ".$this->getReplyTo();
        //send the email
        $mail_sent = @mail($this->getMailTo(), $this->getSubject(), $this->getPlainMessage(), $headers );
        //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
        echo $mail_sent ? true : false;
    }

    public function sendAsHtml(){
        //create a boundary string. It must be unique
        //so we use the MD5 algorithm to generate a random hash
        $random_hash = md5(date('r', time()));
        //define the headers we want passed. Note that they are separated with \r\n
        $headers = "From: ".$this->getMailFrom()."\r\nReply-To: ".$this->getReplyTo();
        //add boundary string and mime type specification
        $headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
        //define the body of the message.
        ob_start(); //Turn on output buffering
        ?>
        --PHP-alt-<?php echo $random_hash; ?> 
        Content-Type: text/plain; charset="<?php echo $this->charSet?>"
        Content-Transfer-Encoding: 7bit

        <?php echo $this->getPlainMessage()?>

        --PHP-alt-<?php echo $random_hash; ?> 
        Content-Type: text/html; charset="<?php echo $this->charSet?>"
        Content-Transfer-Encoding: 7bit

        <?php echo $this->getHtmlMessage()?>

        --PHP-alt-<?php echo $random_hash; ?>--
        <?php
        //copy current buffer contents into $message variable and delete current output buffer
        $message = ob_get_clean();
        //send the email
        $mail_sent = @mail($this->getMailTo(), $this->getSubject(), $this->getMessage(), $headers );
        //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed" 
        echo $mail_sent ? true : false;
    }


    public function sendAsAttach($fileContent, $fileName){
        //create a boundary string. It must be unique
        //so we use the MD5 algorithm to generate a random hash
        $random_hash = md5(date('r', time()));
        //define the headers we want passed. Note that they are separated with \r\n
        $headers = "From: ".$this->getMailFrom()."\r\nReply-To: ".$this->getReplyTo();
        //add boundary string and mime type specification
        $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
        //read the atachment file contents into a string,
        //encode it with MIME base64,
        //and split it into smaller chunks
        // $fileContent = file_get_contents('attachment.zip')
        $attachment = chunk_split(base64_encode($fileContent));
        //define the body of the message.
        ob_start(); //Turn on output buffering
        ?>
        --PHP-mixed-<?php echo $random_hash; ?> 
        Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

        --PHP-alt-<?php echo $random_hash; ?> 
        Content-Type: text/plain; charset="<?php echo $this->charSet?>"
        Content-Transfer-Encoding: 7bit

        <?php echo $this->getPlainMessage()?>

        --PHP-alt-<?php echo $random_hash; ?> 
        Content-Type: text/html; charset="<?php echo $this->charSet?>"
        Content-Transfer-Encoding: 7bit

        <?php echo $this->getHtmlMessage()?>

        --PHP-alt-<?php echo $random_hash; ?>--

        --PHP-mixed-<?php echo $random_hash; ?> 
        Content-Type: application/zip; name="<?php echo $fileName?>" 
        Content-Transfer-Encoding: base64 
        Content-Disposition: attachment 

        <?php echo $attachment; ?>
        --PHP-mixed-<?php echo $random_hash; ?>--

        <?php
        //copy current buffer contents into $message variable and delete current output buffer
        $message = ob_get_clean();
        //send the email
        $mail_sent = @mail( $this->getMailTo, $this->getSubject(), $this->getMessage(), $headers );
        //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
        echo $mail_sent ? true : false;
    }
}
