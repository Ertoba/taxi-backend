<?php

/**
 * This allows integration of other email service providers. especially those that do not use SMTP but use a REST API
 * 
 * $to: variable that holds the email destination
 * $subject: Subject of the email
 * $message: message body of the email
 * 
 * $smtp_test: Array holds test data passed from the settings page when the "Test SMTP settings" button is clicked
 * $smtp_test['host'] - smtp host to test;
 * $smtp_test['username'] - username of host to test
 * $smtp_test['password'] - password of host to test
 * 
 * Constants:
 * SMTP_HOST - Host SMTP address of the email host as entered in the settings page
 * SMTP_PORTNUM - Host SMTP port number as entered in the settings page
 * SMTP_USERNAME - users account username on the host as entered in the settings page
 * SMTP_PWD - Users password on the host as entered in the settings page
 * MAIL_SENDER - sender email address as entered in the settings page
 * 
 * Return value
 * 
 * Resturn an array in the format. 
 * ['response'=>1,'message'=> 'Message has been sent'];
 * 
 * response property: 1 = mail sent successfully, 0 - Error sending mail
 * message property: description of the outcome of sending the email * 
 * 
 */


//['response'=>1,'message'=> 'Message has been sent']; //whenever you return this array, it overrides the default sendMail() function.