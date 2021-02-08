<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Mail;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailController extends Controller {
   
   public function basic_email() {
      $data = array('name'=>"Bhavin Solanki");
   
      Mail::send(['text'=>'mail'], $data, function($message) {
         $message->to('lr.testdemo@gmail.com', 'Logic Rays')->subject
            ('Laravel Basic Testing Mail');
         $message->from('support@dev.halal.masumparvej.me','Bhavin Solanki');
      });

      echo "Basic Email Sent. Check your inbox.";
   }

   public function html_email() {
      $data = array('name'=>"Bhavin Solanki");
      Mail::send('mail', $data, function($message) {
         $message->to('lr.testdemo@gmail.com', 'Logic Rays')->subject
            ('Laravel HTML Testing Mail');
         $message->from('support@dev.halal.masumparvej.me','Bhavin Solanki');
      });
      echo "HTML Email Sent. Check your inbox.";
   }

   public function attachment_email() {
      $data = array('name'=>"Bhavin Solanki");
      Mail::send('mail', $data, function($message) {
         $message->to('lr.testdemo@gmail.com', 'Logic Rays')->subject
            ('Laravel Testing Mail with Attachment');
         $message->attach('C:\laravel-master\laravel\public\uploads\image.png');
         $message->attach('C:\laravel-master\laravel\public\uploads\test.txt');
         $message->from('xyz@gmail.com','Bhavin Solanki');
      });
      echo "Email Sent with attachment. Check your inbox.";
   }

}