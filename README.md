phpgooglevoice
==============

A php based class of Google Voice API to Manage sms.

Forked __phpgooglevoice__ http://code.google.com/p/phpgooglevoice/

This class onley supported sending SMS.

* Send_SMS(Phone_number, Message)

This Fork adds the fallowing.


1. Get_NEW_SMS()
  * Get inbox unread messages.
  * Porvides aray:
    * ["Last_Message"]["Message"] Body of message.
    * ["Last_Message"]["Sender"]  Name, if in contact list.
    * ["Last_Message"]["Time"]    Time of message.
    * ["Phone_Num"]               Phone number.
    * ["SMS_ID"]                  Uniquic ID of Message thread.


2.  Mark_Read(ID)
  * Marks message as read 
  

3.  Archive(ID)                     
  * Archives the  message


Sample code to display all new SMS then arcive
```php
<?php
  require 'class.googlevoice.php';
  $gv = new GoogleVoice("GmailAccount@gmail.com", "GmailPassword");  

  echo "Curent Inbox<br>";
  
  $messages = $gv->Get_NEW_SMS();
  
  foreach($messages as $message){
   echo "Recived Message: ".$message["Last_Message"]["Message"]."<br>";
   echo "++ Form ".$message["Last_Message"]["Sender"]."<br>";
   echo "+++ #: ".$message["Phone_Num"]."<br>";
   echo "+++ @: ".$message["Last_Message"]["Time"]."<br>";
   echo "+++ ID: ".$message["SMS_ID"]."<br><br>";
	 
   $gv->Archive($message["SMS_ID"]);
  }
?>
```

Sample code to Send new SMS
```php
<?php
  require 'class.googlevoice.php';
  $gv = new GoogleVoice("GmailAccount@gmail.com", "GmailPassword");
  $gv->Send_SMS("PhoneNumber", "TextMsg");
?>  
```



