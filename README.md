phpgooglevoice
==============

A PHP based class of Google Voice API to manage SMS.

Forked __phpgooglevoice__ http://code.google.com/p/phpgooglevoice/

This class only supported sending SMS.

This fork adds the following.


0. sendSMS(Phone number, Message, ID)


1. getSMS(Params)
  * Get inbox unread messages.
  * Params array
    * ['history']		Do you want to include the history else just first and last. Defaults to false.
    * ['onlyNew']		Do you want to include only new messages. Defaults to true.
    * ['page']		Page number if more then one available. Defaults to 1.
  * Returns array:
    * ['unread']		Total unread sms messages anywhere.
    * ['total']		Total messages in inbox.
    * ['texts']
      * ['from']	Name, if in contact list.
      * ['number']	Phone number.
      * ['firstText']	Body of first message.
      * ['date']	Date Time of message.
      * ['lastText']	Body of last message.
      * ['history']	Key value is the unique ID of Message thread.
        * ['from']	Name, if in contact list.
        * ['time']	Date Time of message.
        * ['message']	Body of message.

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
  $messages = $gv->getNewSMS();
  
  echo "Curent Inbox<br>";
  echo "Unread count: {$messages['unread']} In inbox: {$messages['total']}<br><br>";
  
  foreach ($messages['texts'] as $id=>$text){
  	echo "++ {$text['from']}<br>";
	echo "++++ #: {$text['number']}<br>";
	echo "++++ Last Message @: {$text['date']}<br>";
	echo "++++ Last Message : {$text['lastText']}<br>";
	echo "++++ ID: {$id}<br><br>";
		
	foreach($text['history'] as $message){
		echo "++++++++ Form: {$message['from']} @: {$message['time']}<br>"; 
		echo "++++++++ : {$message['message']}<br><br>"; 
	}	 
   	$gv->archive($id);
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



