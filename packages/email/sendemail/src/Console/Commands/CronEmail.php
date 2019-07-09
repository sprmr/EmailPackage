<?php

namespace Email\SendEmail\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Mail;
use Email\SendEmail\SendMailable;

class CronEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'testing:email';
    protected $signature = 'command:sendemail';

    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron to send emails.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   

        $all_logs=DB::table('email_logs')
                ->select('*')
                ->get();
        $server_error=[500, 502, 503, 504,401];
        if(!$all_logs->isEmpty()){
            foreach($all_logs as $log){
                if($log->attempt ==0){
                    if($log->to_address){
                       try {
                           $send= Mail::to($log->to_address)->send(new SendMailable($log->mail_subject,$log->mail_message,$log->login_url,$log->template));
                           $completed_email=DB::table('completed_email_logs')->insertGetId([
                                        'to_address'=>$log->to_address,
                                        'mail_subject'=>$log->mail_subject,
                                        'mail_message'=>$log->mail_message,
                                        'mail_remarks'=>$log->mail_remarks,
                                        'login_url'=>$log->login_url,
                                        'template'=>$log->template,
                                        'attempt'=>$log->attempt +1,
                                        'created_at'=>date('Y-m-d H:i:s'),
                                       ]);  
                            $del_email_log = DB::table('email_logs')->where('id',$log->id)->delete();
                        
                     
                          // throw  new \Exception('user is not created',400);
                        } catch(\Exception $e) {
                          
                           $status=$e->getCode();
                           $error_message=$e->getMessage();
                           $update_emailLog= DB::table('email_logs')
                                ->where('id',$log->id)  
                                 ->update(array('attempt' => $log->attempt +1 ,'updated_at'=>date('Y-m-d H:i:s'),'status'=>$status,'error_message'=>$error_message));
                        }

                    }

                }elseif($log->attempt==1 || $log->attempt == 2){
                    // if(in_array($log->status, $server_error)){
                    // mail to admin
                    // }else{
                        if($log->to_address){
                           try {
                               $send= Mail::to($log->to_address)->send(new SendMailable($log->mail_subject,$log->mail_message,$log->login_url,$log->template));
                               $completed_email=DB::table('completed_email_logs')->insertGetId([
                                            'to_address'=>$log->to_address,
                                            'mail_subject'=>$log->mail_subject,
                                            'mail_message'=>$log->mail_message,
                                            'mail_remarks'=>$log->mail_remarks,
                                            'login_url'=>$log->login_url,
                                            'template'=>$log->template,
                                            'attempt'=>$log->attempt +1,
                                            'created_at'=>date('Y-m-d H:i:s'),
                                           ]);  
                                $del_email_log = DB::table('email_logs')->where('id',$log->id)->delete();
                            } catch(\Exception $e) {
                              
                               $status=$e->getCode();
                               $error_message=$e->getMessage();
                               $update_emailLog= DB::table('email_logs')
                                    ->where('id',$log->id)  
                                     ->update(array('attempt' => $log->attempt +1 ,'updated_at'=>date('Y-m-d H:i:s'),'status'=>$status,'error_message'=>$error_message));
                            }

                        }
                     
                   // }

                }elseif($log->attempt < 5){
                    
                    $new_time=date('Y-m-d H:i',strtotime('+10 minutes', strtotime($log->updated_at)));
                    
                    $new_mail_time=date('Y-m-d H:i');
                    
                     if($new_mail_time==$new_time){
                         if($log->to_address){
                           try {
                               $send= Mail::to($log->to_address)->send(new SendMailable($log->mail_subject,$log->mail_message,$log->login_url,$log->template));
                               $completed_email=DB::table('completed_email_logs')->insertGetId([
                                            'to_address'=>$log->to_address,
                                            'mail_subject'=>$log->mail_subject,
                                            'mail_message'=>$log->mail_message,
                                            'mail_remarks'=>$log->mail_remarks,
                                            'login_url'=>$log->login_url,
                                            'template'=>$log->template,
                                            'attempt'=>$log->attempt +1,
                                            'created_at'=>date('Y-m-d H:i:s'),
                                           ]);  
                                $del_email_log = DB::table('email_logs')->where('id',$log->id)->delete();
                            } catch(\Exception $e) {
                              
                               $status=$e->getCode();
                               $error_message=$e->getMessage();
                               $update_emailLog= DB::table('email_logs')
                                    ->where('id',$log->id)  
                                     ->update(array('attempt' => $log->attempt +1 ,'updated_at'=>date('Y-m-d H:i:s'),'status'=>$status,'error_message'=>$error_message));
                            }

                        }

                     }
                }elseif($log->attempt==5){
                    $failed_email=DB::table('failed_email_logs')->insertGetId([
                                        'status'=>$log->status,
                                        'error_message'=>$log->error_message,
                                        'to_address'=>$log->to_address,
                                        'mail_subject'=>$log->mail_subject,
                                        'mail_message'=>$log->mail_message,
                                        'mail_remarks'=>$log->mail_remarks,
                                        'login_url'=>$log->login_url,
                                        'template'=>$log->template,
                                        'created_at'=>date('Y-m-d H:i:s'),
                                       ]);  
                    $del_email_log = DB::table('email_logs')->where('id',$log->id)->delete();
                    
                }

                $cron_logs=DB::table('email_cron_logs')
                        ->select('*')
                        ->where('cron_name','=','command:sendemail')
                        ->get();
               
                if($cron_logs->isEmpty()){
                  $email_cron_log=DB::table('email_cron_logs')->insertGetId([
                            'cron_name'=>'command:sendemail',
                            'frequency'=>'+5 minutes',
                            'last_run'=>date('Y-m-d H:i:s'),
                           
                           ]);

                }else{
                    $update_emailCron= DB::table('email_cron_logs')
                                     ->where('cron_name','=','command:sendemail')
                                     ->update(array('last_run' =>date('Y-m-d H:i:s') ));
                }

             
            }
            dump("cron completed");
        }
     


    }



   
}
