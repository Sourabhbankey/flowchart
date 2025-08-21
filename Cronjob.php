<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cronjob extends CI_Controller
{


  public function sendDailyEmail()
{
    $userEmail = 'yashi.edumeta@gmail.com';
    $subject = 'Test';
    $config = array(
        'protocol'  => 'smtp',             // Use SMTP
        'smtp_host' => 'ssl', // SMTP server
        'smtp_port' => 465,               // Port for SSL
        'smtp_user' => 'yashi.edumeta@gmail.com', // Your email
        'smtp_pass' => 'edumeta.123', // Your email password
        'mailtype'  => 'html',            // HTML email
        'charset'   => 'utf-8',
        'priority'  => '1',
        'newline'   => "\r\n",            // Newline
    );

    // Load the email library with the updated config
    $this->load->library('email', $config);
    $this->email->set_newline("\r\n");

    // Set email details
    $this->email->from('yashi.edumeta@gmail.com', 'Your Name');
    $this->email->to($userEmail);
    $this->email->subject($subject);

    $data = array('userName' => 'Test');
    $body = $this->load->view('birthday.php', $data, TRUE);
    $this->email->message($body);

    // Attempt to send the email
    if ($this->email->send()) {
        echo 'Email sent successfully.';
    } else {
        // Print the error message for debugging
        echo 'Failed to send email: ' . $this->email->print_debugger();
    }
}

}