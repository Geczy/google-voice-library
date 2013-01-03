Google Voice Library
=================

Google Voice Library is a PHP based library for managing texts on Google Voice.

Quick start
------------

Clone the repo, `git clone git://github.com/Geczy/google-voice-library.git`, or [download the latest release](https://github.com/Geczy/google-voice-library/zipball/master).

```php
<?php
include_once('class.googlevoice.php');

$googleVoice = new \Geczy\Voice\GoogleVoiceLibrary('username', 'password');
$messages = $googleVoice->get_inbox();

var_dump($messages);
```

Features
------------

### Retrieve your inbox

```php
<?php
$params = array(
	'history' => true,   // All messages in a conversation?
	'onlyNew' => false,  // Just unread messages?
	'page'    => 1,      // Page of inbox to retrieve?
);

$messages = $googleVoice->get_inbox($params);
```

### Send a text

```php
<?php
$googleVoice->send_text(8002029393, 'Hello, world!');
```

### Archive a conversation

```php
<?php
$googleVoice->archive('message_id');
```

### Delete a conversation

```php
<?php
$googleVoice->delete('message_id');
```

### Mark a conversation as read

```php
<?php
$googleVoice->mark_read('message_id');
```

Example inbox response
------------

```php
<?php
array (size=3)
  'unread' => int 98
  'total' => int 1
  'texts' =>
	array (size=1)
	  '10addc3d5f181c34c94332c8d68b2373ac1df14n' =>
		array (size=5)
		  'from' => string 'Tawr' (length=4)
		  'number' => string '(555) 555-5555' (length=14)
		  'date' => string '9/3/12 6:22 PM' (length=14)
		  'text' => string 'Oh wow! Ill visit it later and judge your work. :3' (length=51)
		  'history' =>
			array (size=131)
			  0 =>
				array (size=3)
				  'from' => string 'Me' (length=2)
				  'time' => string '9/3/12 12:31 PM' (length=15)
				  'message' => string 'I didnt get any texts last night >:|' (length=37)
			  1 =>
				array (size=3)
				  'from' => string 'Me' (length=2)
				  'time' => string '9/3/12 12:32 PM' (length=15)
				  'message' => string 'Also youre going to be mad. I wake up and all my ivy is gone. ' (length=63)
			...
```

Bug tracker
-----------

Have a bug? Please create an issue here on GitHub!

https://github.com/Geczy/google-voice-library/issues

Copyright and License
---------------------

Copyright 2012 Matthew Gates

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this work except in
compliance with the License. You may obtain a copy of the License in the LICENSE file, or at:

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is
distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and limitations under the License.