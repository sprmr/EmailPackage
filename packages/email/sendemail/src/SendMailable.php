<?php


namespace Email\SendEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMailable extends Mailable
{   

    use Queueable, SerializesModels;
    public $subject;
    public $msg;
    public $url;
    public $template;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$msg,$url=NULL,$template=NULL)
    {
       
        $this->subject = $subject;
        $this->msg = $msg;
        $this->url = $url;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         if($this->template){
            return $this->subject(__($this->subject))->markdown('emails.'.$this->template)->with('msg',$this->msg)->with('link',$this->url);
         }else{
            return $this->subject(__($this->subject))->markdown('sendemail::send_mail')->with('msg',$this->msg); 
         }
         
    }
}