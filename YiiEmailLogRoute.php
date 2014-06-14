<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ilya
 * Date: 06.07.13
 * Time: 10:09
 * To change this template use File | Settings | File Templates.
 */

class YiiEmailLogRoute extends CEmailLogRoute
{
    /**
     * Sends an email.
     * @param string $email single email address
     * @param string $subject email subject
     * @param string $message email content
     */
    protected function sendEmail($email,$subject,$message)
    {
        $message = "URL: ".(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : false)."<br>"
            . "REQUEST_URI: ".(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false)."<br>"
            . "HTTP_REFERER: ".(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false)."<br><br>"
            . $message;

        $headers=$this->getHeaders();
        if($this->utf8)
        {
            $headers[]="MIME-Version: 1.0";
            $headers[]="Content-type: text/plain; charset=UTF-8";
            $subject='=?UTF-8?B?'.base64_encode($subject).'?=';
        }
        if(($from=$this->getSentFrom())!==null)
        {
            $matches=array();
            preg_match_all('/([^<]*)<([^>]*)>/iu',$from,$matches);
            if(isset($matches[1][0],$matches[2][0]))
            {
                $name=$this->utf8 ? '=?UTF-8?B?'.base64_encode(trim($matches[1][0])).'?=' : trim($matches[1][0]);
                $from=trim($matches[2][0]);
                $headers[]="From: {$name} <{$from}>";
            }
            else
                $headers[]="From: {$from}";
            $headers[]="Reply-To: {$from}";
        }

        // mail($email,$subject,$message,implode("\r\n",$headers));

        // наполняем сообщение дополнительной информацией
        $message .= "\n\n\n".'*** $_GET***'."\n".print_r($_GET, true);
        $message .= "\n\n\n".'*** $_POST***'."\n".print_r($_POST, true);
        $message .= "\n\n\n".'*** $_SERVER***'."\n".print_r($_SERVER, true);

        // задаем красивый вывод
        $message = "<pre>".$message."</pre>";

        Yii::import('application.extensions.yii-mail.YiiMailMessage');
        Yii::import('application.extensions.yii-mail.YiiMail');
        $mailMessage = new YiiMailMessage;
        // $message->view = 'registrationFollowup';
        //userModel is passed to the view
        // $message->setBody(array('userModel'=>$userModel), 'text/html');
        $mailMessage->setBody($message, 'text/html', 'UTF-8');
        $mailMessage->subject = Yii::app()->name.' Error : '.$subject;
        foreach(Yii::app()->params['adminErrorEmail'] as $item)
        {
            $mailMessage->addTo($item);
        }
        $mailMessage->from = Yii::app()->params['senderForMail'];
        Yii::app()->mail->send($mailMessage);
    }

    public function init()
    {

    }
}