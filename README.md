phpgooglevoice
==============

A PHP based class of Google Voice API to manage SMS.

Forked __phpgooglevoice__ http://code.google.com/p/phpgooglevoice/

This class only supported sending SMS.

* sendSMS(Phone_number, Message)

This fork adds the following.


1. getNewSMS()
  * Get inbox unread messages.
  * Provides array:
    * ["Last_Message"]["Message"] Body of message.
    * ["Last_Message"]["Sender"]  Name, if in contact list.
    * ["Last_Message"]["Time"]    Time of message.
    * ["Phone_Num"]               Phone number.
    * ["SMS_ID"]                  Unique ID of Message thread.


2.  markRead(ID)
  * Marks message as read


3.  archive(ID)
  * Archives the message

4.  delete(ID)
  * Delete the message


Sample code to display all new SMS then archive
```php
<?php
  require 'class.googlevoice.php';
  $gv = new GoogleVoice("GmailAccount@gmail.com", "GmailPassword");

  echo "Curent Inbox<br>";

  $messages = $gv->getNewSMS();

  foreach($messages as $message){
   echo "Received Message: ".$message["Last_Message"]["Message"]."<br>";
   echo "++ From ".$message["Last_Message"]["Sender"]."<br>";
   echo "+++ #: ".$message["Phone_Num"]."<br>";
   echo "+++ @: ".$message["Last_Message"]["Time"]."<br>";
   echo "+++ ID: ".$message["SMS_ID"]."<br><br>";

   $gv->archive($message["SMS_ID"]);
  }
?>
```

Sample code to send new SMS
```php
<?php
  require 'class.googlevoice.php';
  $gv = new GoogleVoice("GmailAccount@gmail.com", "GmailPassword");
  $gv->sendSMS("PhoneNumber", "TextMsg");
?>
```