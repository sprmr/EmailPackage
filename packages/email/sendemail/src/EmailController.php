<?php
namespace Email\SendEmail;

use DB;
use Mail;
use Schema;
use App\Http\Controllers\Controller;
use Email\SendEmail\SendMailable;
use Illuminate\Database\Schema\Blueprint;

class EmailController extends Controller
{


    //
     public function sendMail($email_to,$email_sub,$email_msg,$email_remarks=NULL,$login_url=NULL,$template=NULL){
     	if(!Schema::hasTable('email_logs')){
            Schema::create('email_logs', function (Blueprint $table) {
				    $table->increments('id');
				    $table->integer('status')->default(0);
				    $table->text('error_message')->nullable();
				    $table->string('to_address', 255);
				    $table->string('mail_subject', 255);
				    $table->text('mail_message');
				    $table->string('mail_remarks', 255)->nullable();
            $table->string('login_url', 255)->nullable();
            $table->string('template', 255)->nullable();
				    $table->integer('attempt')->default(0);
				    $table->dateTime('created_at');
				    $table->dateTime('updated_at');
				});
     	}
     	if(!Schema::hasTable('completed_email_logs')){
     		Schema::create('completed_email_logs', function (Blueprint $table) {
				    $table->increments('id');
				    $table->string('to_address', 255);
				    $table->string('mail_subject', 255);
				    $table->text('mail_message');
				    $table->string('mail_remarks', 255)->nullable();
            $table->string('login_url', 255)->nullable();
            $table->string('template', 255)->nullable();
				    $table->integer('attempt');
				    $table->dateTime('created_at');
				   
				});

     	}
     	if(!Schema::hasTable('failed_email_logs')){
            Schema::create('failed_email_logs', function (Blueprint $table) {
				    $table->increments('id');
				    $table->integer('status');
				    $table->text('error_message');
				    $table->string('to_address', 255);
				    $table->string('mail_subject', 255);
				    $table->text('mail_message');
				    $table->string('mail_remarks', 255)->nullable();
            $table->string('login_url', 255)->nullable();
            $table->string('template', 255)->nullable();
				    $table->dateTime('created_at');
				    
				});
     	}
     	if(!Schema::hasTable('email_cron_logs')){
     		Schema::create('email_cron_logs', function (Blueprint $table) {
				    $table->increments('id');
				    $table->string('cron_name', 255);
				    $table->string('frequency', 255);
				    $table->dateTime('last_run');
				    
				});
     	}
     
     
     	$email_log=DB::table('email_logs')->insertGetId([
            'to_address'=>$email_to,
            'mail_subject'=>$email_sub,
            'mail_message'=>$email_msg,
            'mail_remarks'=>$email_remarks,
            'login_url'=>$login_url,
            'template'=>$template,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s')
           ]); 
         
         if($email_log) {
            $emailcron_logs=DB::table('email_cron_logs')
                        ->select('*')
                        ->get();

            if(!$emailcron_logs->isEmpty()){
                foreach($emailcron_logs as $email_cron ){
                  $nextrun=date('Y-m-d H:i',strtotime($email_cron->frequency, strtotime($email_cron->last_run)));
                  $currentTime=date('Y-m-d H:i');
                  if($currentTime>$nextrun){
                  
                   $subject='Cron Failure Email';
                   $message="Cron <b>".$email_cron->cron_name."</b> failed.";
                   //Mail to Admin
                    if(Schema::hasTable('users')){
                     $users=DB::table('users')
                          ->select('*')
                          ->where('role_id','=',1)
                          ->get();
                      
                      // Mail::to('surandra@netgensolutions.com')->send(new SendMailable($subject,$message));
                      foreach($users as $admin){
                        if($admin->email){
                           if(filter_var($admin->email, FILTER_VALIDATE_EMAIL)){
                            
                              
                              try {
                                 $mail=Mail::to($admin->email)->send(new SendMailable($subject,$message));  
                              } catch(\Exception $e) {
                                  dump('Sorry,mail could not be sent to '.$admin->email);
                              }
                           }
                         
                        }

                      }
                    }
                  
                 

                  }

                }
    
            }
           return true;
         }else{
           return false;
         }
         die;

       
     }
}
