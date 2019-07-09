<?php

Route::get('/newtest/testing', function(){
	echo 'Hello testing package!';
});
Route::get('/newtest/send-mail/{email}/{sub}/{msg}/{remarks}', 'Email\SendEmail\EmailController@sendMail');