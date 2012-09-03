Google Voice Library
=================

Google Voice API is a PHP based library for managing texts.

Quick start
------------

Clone the repo, `git clone git://github.com/Geczy/google-voice-library.git`, or [download the latest release](https://github.com/Geczy/google-voice-library/zipball/master).

```php
<?php
include_once('class.googlevoice.php');

$googleVoice = new \Geczy\Voice\GoogleVoiceLibrary('username', 'password');
$params = array(
	'history' => true, /* All messages in a conversation? */
	'onlyNew' => false, /* Just unread messages? */
);

$messages = $googleVoice->getInbox($params);

var_dump($messages);
```

Features
------------

### Retrieve your inbox

```php
<?php
$messages = $googleVoice->getInbox($params);
```

### Send a text

```php
<?php
$params = array(
	'history' => true, /* All messages in a conversation? */
	'onlyNew' => false, /* Just unread messages? */
);

$googleVoice->sendText(8002029393, 'Hello, world!');
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
$googleVoice->markRead('message_id');
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